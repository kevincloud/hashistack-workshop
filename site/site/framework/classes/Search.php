<?php

/*
 *  HOMEPAGE CLASS
 *  
 *  Options
 *  
 *  pagination		Number of products to list per page
 */

class Search
{
	private $options = array();
	
	public $LastCount = 0;
	
	protected $_db;
	
	public function __construct($db)
	{
		$this->_db = $db;
		
		$this->options["pagination"] = 20;
	}
	
	public function SetOption($key, $value)
	{
		$this->options[$key] = $value;
	}
	
	public function GetOption($key)
	{
		return $this->options[$key];
	}
	
	public function ShowResults($products, $page, $querytype, $data)
	{
		$out = "";
		$shown = 0;
		$start = 0;
		$stop = 0;
		$pages = 1;
		$pagination = 20;
		$showing = "";
		$paginator = "";
		$label = "";
		$resultinfo = "";
		$resultnext = "";
		$resultprev = "";
		
		switch ($querytype)
		{
			case "keyword":
				$label = "Search Results";
				break;
			case "categories":
				$label = $this->_db->get_var("select category from pw_categories where id = ".smartQuote($data));
				break;
			case "manufacturer":
				$label = $this->_db->get_var("select p.author from cc_project as p inner join cc_people as c on (c.personalid = p.personalid) where c.rguid = ".smartQuote(base64url_decode($data)));
				break;
			case "popular":
				$label = "Popular Products";
				break;
			case "new-releases":
				$label = "New Releases";
				break;
		}
		
		$out .= "<div class=\"content\">\n";
		$out .= "	<h2>".$label."</h2>\n";
		
		if (count($products) == 0)
			$out .= "No products were found.";
		else
		{
			switch ($querytype)
			{
				case "keyword":
					$resultinfo = " results for <span style=\"font-weight:bold;\">".$data."</span>";
					$resultnext = "/products/search/".($page + 1)."/".urlencode($data);
					$resultprev = "/products/search/".($page - 1)."/".urlencode($data);
					break;
				case "categories":
					$resultinfo = "";
					$tmpcat = "";
					$sql = "exec s_catFindParentsByID ".$data;
					$rs = $this->_db->get_results($sql);
					if ($rs)
					{
						foreach ($rs as $row)
							$tmpcat .= "/".Utilities::BeautifyURL($row->category);
					}
					$resultnext = $tmpcat."/categories/".urlencode($data)."/".($page + 1);
					$resultprev = $tmpcat."/categories/".urlencode($data)."/".($page - 1);
					break;
				case "manufacturer":
					$resultinfo = " products for this manufacturer";
					$resultnext = "/products/manufacturer/".$data;
					$resultprev = "/products/manufacturer/".$data;
					break;
				case "new-releases":
				case "popular":
					$resultinfo = "";
					$resultnext = "/products/".$querytype."/".($page + 1);
					$resultprev = "/products/".$querytype."/".($page - 1);
					break;
			}
			
			$pagination = $this->GetOption("pagination");
			$start = $pagination * ($page - 1);
			$stop = ($pagination * $page) - 1;
			$pages = ceil(count($products) / $pagination);
			$shown = 0;
			
			if ($page == 1)
				$showing = count($products) <= $pagination ? "all" : "first ".$pagination;
			else
				$showing = ($start + 1)."-".(($stop+1) >= count($products) ? count($products) : ($stop+1));
			
			// CREATE PAGINATION BAR
			$paginator .= "<div class=\"searchnav\">\n";
			
			if ($page < $pages)
				$paginator .= "	<a href=\"".$resultnext."\"><div style=\"float:right;text-align:center;width:32px; height:32px;border-left:1px solid #ffffff;	font-family:'EntypoRegular';font-size:32px;line-height:24px;color:#ccc2a6;\">&aring;</div></a>\n";
			else
				$paginator .= "	<div style=\"float:right;text-align:center;width:32px; height:32px;border-left:1px solid #ffffff;\"></div>\n";
				
			$paginator .= "	<div style=\"float:right;text-align:center;height:32px; border-left:1px solid #ffffff;padding:0px 7px 0px 7px;\">Page ".$page." of ".$pages."</div>\n";

			if ($page > 1)
				$paginator .= "	<a href=\"".$resultprev."\"><div style=\"float:right;text-align:center;width:32px; height:32px;border-left:1px solid #ffffff;	font-family:'EntypoRegular';font-size:32px;line-height:24px;color:#ccc2a6;\">&acirc;</div></a>\n";
			else
				$paginator .= "	<div style=\"float:right;text-align:center;width:32px; height:32px;border-left:1px solid #ffffff;\"></div>\n";
			
			$paginator .= "	<div class=\"clearfloat\"></div>\n";
			$paginator .= "</div>\n";
			
			
			$out .= "<div>Showing <span style=\"font-weight:bold;\">".$showing."</span> of <span style=\"font-weight:bold;\">".count($products)."</span>".$resultinfo."</div>\n";
			$out .= $paginator;
			$out .= "	<ul class=\"product-list\">\n";
			foreach ($products as &$pg)
			{
				if ($shown >= $start && $shown <= $stop)
				{
					$p = new Product($this->_db);
					try
					{
						$p->GetProduct($pg->RootPID);
						$p->LoadFormats();
						if (count($pg->Formats) == 1)
							$out .= $p->ListView();
						else
							$out .= $p->SelectView($pg);
					}
					catch (Exception $e)
					{
						//$out .= $e->getMessage()." (".$pg->ProjectID.")";
					}
				}
				$shown++;
			}
			//exit();
			$out .= "	</ul>\n";
			$out .= $paginator;
		}
		
		$out .= "</div>\n";
		
		return $out;
	}
	
	public function SearchResults($keywords, $page)
	{
		$out = "";
		
		if (!isBlank($keywords))
		{
			$tmp = trim(str_replace("-", "", $keywords));
			if (strlen($tmp) == 10)
			{
				$sql = "select pid, pname from cc_product where isbn = ".smartQuote($tmp, true);
				$row = $this->_db->get_row($sql);
				if (count($row) > 0)
				{
					$nisbn = Utilities::ToISBN13($tmp);
					header("Location: /".Utilities::BeautifyURL($row->pname)."/products/".$row->pid."/".$nisbn."");
					exit();
				}
			}
			if (strlen($tmp) == 13)
			{
				$nisbn = Utilities::ToISBN10($tmp);
				$sql = "select pid, pname from cc_product where isbn = ".smartQuote($nisbn, true);
				$row = $this->_db->get_row($sql);
				if (count($row) > 0)
				{
					$nisbn = Utilities::ToISBN13($tmp);
					header("Location: /".Utilities::BeautifyURL($row->pname)."/products/".$row->pid."/".$tmp."");
					exit();
				}
			}
			$products = $this->SelectProducts("keyword", $keywords);
			$out .= $this->ShowResults($products, $page, "keyword", $keywords);
		}
		else
		{
			$out .= "Please enter some keywords.";
		}
		
		return $out;
	}
	
	public function Category($cid, $page)
	{
		$out = "";
		
		if (!isBlank($cid))
		{
			$products = $this->SelectProducts("categories", $cid);
			$out .= $this->ShowResults($products, $page, "categories", $cid);
		}
		else
		{
			$out .= "No products were found in this category.";
		}
		
		return $out;
	}
	
	public function Manufacturer($manufacturerid, $page)
	{
		$out = "";
		
		if (!isBlank($manufacturerid))
		{
			$products = $this->SelectProducts("manufacturer", $manufacturerid);
			$out .= $this->ShowResults($products, $page, "manufacturer", $manufacturerid);
		}
		else
		{
			$out .= "No products were found for this manufacturer.";
		}
		
		return $out;
	}
	
	public function NewReleases($num, $page)
	{
		$out = "";
		
		
		$products = $this->SelectProducts("new-releases", $num);
		$out .= $this->ShowResults($products, $page, "new-releases", $num);
		
		return $out;
	}
	
	public function EBookList($num, $page)
	{
		$out = "";
		
		$products = $this->SelectProducts("e-books", $num);
		$out .= $this->ShowResults($products, $page, "e-books", $num);
		
		return $out;
	}
	
	public function AudioBookList($num, $page)
	{
		$out = "";
		
		$products = $this->SelectProducts("audio-books", $num);
		$out .= $this->ShowResults($products, $page, "audio-books", $num);
		
		return $out;
	}
	
	public function SelectProducts($querytype, $data)
	{
		$products = array();
		$added = 0;
		
		$sql_main = "";
		$sql_apid = "";
		$sql_base = "";
		$sql_npid = "";
		$sql_ord1 = "";
		$sql_ord2 = "";
		
		switch ($querytype)
		{
			case "keyword":
				if (isBlank($data))
					return $products;
				$sql_main = "select a.projectid, a.listing, a.rank, a.pid ".
					"from ( ".
					"		select distinct isnull(case a.separate_product when 1 then p.projectid + 1000000 else p.projectid end, -a.pid) as projectid, ".
					"			a.separate_product as listing, k.[rank] * 10 as rank, case a.separate_product when 1 then a.pid else 0 end as pid ".
					"		from cc_product as a ".
					"			inner join freetexttable(cc_product, (pname, author), ".smartQuote($data, true).") k on a.pid = k.[key] ".
					"			left join cc_book as b on (b.isbnx = a.isbn and isnull(b.isbnx, '') != '') ".
					"			left join cc_project as p on (p.projectid = b.projectid and p.projectid is not null) ".
					"		where a.active = 1 and a.store = 1 ".
					"		union all ".
					"		select distinct isnull(case a.separate_product when 1 then p.projectid + 1000000 else p.projectid end, -a.pid) as projectid, ".
					"			a.separate_product as listing, k.[rank] * 5 as rank, case a.separate_product when 1 then a.pid else 0 end as pid ".
					"		from cc_product as a ".
					"			inner join freetexttable(cc_product, subtitle, ".smartQuote($data, true).") k on a.pid = k.[key] ".
					"			left join cc_book as b on (b.isbnx = a.isbn and isnull(b.isbnx, '') != '') ".
					"			left join cc_project as p on (p.projectid = b.projectid and p.projectid is not null) ".
					"		where a.active = 1 and a.store = 1 ".
					"		union all ".
					"		select distinct isnull(case a.separate_product when 1 then p.projectid + 1000000 else p.projectid end, -a.pid), ".
					"			a.separate_product, k.[rank] * 1, case a.separate_product when 1 then a.pid else 0 end ".
					"		from cc_product as a ".
					"			inner join freetexttable(cc_product, descr, ".smartQuote($data, true).") k on a.pid = k.[key] ".
					"			left join cc_book as b on (b.isbnx = a.isbn and isnull(b.isbnx, '') != '') ".
					"			left join cc_project as p on (p.projectid = b.projectid and p.projectid is not null) ".
					"		where a.active = 1 and a.store = 1 ".
					"	) as a ".
					"order by a.rank desc, a.projectid, a.listing";
				$sql_base = "select p.projectid, p.pubdate, a.pid, a.pname, a.subtitle, a.author, isnull(a.canbuy, 1) as canbuy, a.code, ".
					"	a.isbn, a.price, a.ordertype, a.binding, a.bdetails, a.discount, isnull(a.video_data, '') as video_data ".
					"from cc_product as a ".
					"	left join cc_book as b on (b.isbnx = a.isbn and isnull(b.isbnx, '') != '') ".
					"	left join cc_project as p on (p.projectid = b.projectid and p.projectid is not null) ".
					"where a.active = 1 and a.store = 1 ".
					"	and ( ".
					"			p.projectid in ( ".
					"				select distinct p.projectid as projectid ".
					"				from cc_product as a ".
					"					inner join freetexttable(cc_product, (pname, subtitle, author, descr), ".smartQuote($data, true).") k on a.pid = k.[key] ".
					"					left join cc_book as b on (b.isbnx = a.isbn and isnull(b.isbnx, '') != '') ".
					"					left join cc_project as p on (p.projectid = b.projectid and p.projectid is not null) ".
					"				where a.active = 1 and a.store = 1 and p.projectid is not null ".
					"				) ".
					"			or ".
					"			a.PID in ( ".
					"				select distinct a.pid as projectid ".
					"				from cc_product as a ".
					"					inner join freetexttable(cc_product, (pname, subtitle, author, descr), ".smartQuote($data, true).") k on a.pid = k.[key] ".
					"					left join cc_book as b on (b.isbnx = a.isbn and isnull(b.isbnx, '') != '') ".
					"					left join cc_project as p on (p.projectid = b.projectid and p.projectid is null) ".
					"				where a.active = 1 and a.store = 1 and p.projectid is null ".
					"			) ".
					"		) ";
				break;
			case "categories":
				$sql_main = "select distinct isnull(case a.separate_product when 1 then p.projectid + 1000000 else p.projectid end, -a.pid) as projectid, ".
					"	a.separate_product as listing, 1000 as rank, case a.separate_product when 1 then a.pid else 0 end as pid, a.datestamp ".
					"from cc_product as a ".
					"	inner join pw_catxref x on x.pid = a.pid ".
					"	inner join pw_categories c on (x.cid = c.id) ".
					"	left join cc_book as b on (b.isbnx = a.isbn and isnull(b.isbnx, '') != '') ".
					"	left join cc_project as p on (p.projectid = b.projectid and p.projectid is not null) ".
					"where a.active = 1 and c.id = ".smartQuote($data)." and a.store = 1 ".
					"order by a.datestamp desc, isnull(case a.separate_product when 1 then p.projectid + 1000000 else p.projectid end, -a.pid), ".
					"	a.separate_product";
				$sql_base = "select p.projectid, p.pubdate, a.pid, a.pname, a.subtitle, a.author, isnull(a.canbuy, 1) as canbuy, a.code, ".
					"	a.isbn, a.price, a.ordertype, a.binding, a.bdetails, a.discount, isnull(a.video_data, '') as video_data ".
					"from cc_product as a ".
					"	left join cc_book as b on (b.isbnx = a.isbn and isnull(b.isbnx, '') != '') ".
					"	left join cc_project as p on (p.projectid = b.projectid and p.projectid is not null) ".
					"where a.active = 1 and a.store = 1 ".
					"	and p.projectid in (	".
					"		select distinct isnull(p.projectid, -a.pid) as projectid ".
					"		from cc_product as a ".
					"			inner join pw_catxref x on x.pid = a.pid ".
					"			inner join pw_categories c on (x.cid = c.id) ".
					"			left join cc_book as b on (b.isbnx = a.isbn and isnull(b.isbnx, '') != '') ".
					"			left join cc_project as p on (p.projectid = b.projectid and p.projectid is not null) ".
					"		where a.active = 1 and c.id = ".smartQuote($data)." and a.store = 1 ";
				break;
			case "new-releases":
				$sql_main = "select ".($data > 0 ? "top ".$data : "")." a.datestamp, isnull(case a.separate_product when 1 then p.projectid + 1000000 else p.projectid end, -a.pid) as projectid, ".
					"	a.separate_product as listing, 1000 as rank, case a.separate_product when 1 then a.pid else 0 end as pid ".
					"from cc_product as a ".
					"	inner join cc_book as b on (b.isbnx = a.isbn and isnull(b.isbnx, '') != '') ".
					"	inner join cc_project as p on (p.projectid = b.projectid and p.projectid is not null) ".
					"where a.active = 1 and a.datestamp is not null and a.store = 1 and a.printtype not in ('Digital') ".
					"order by a.datestamp desc, isnull(case a.separate_product when 1 then p.projectid + 1000000 else p.projectid end, -a.pid), ".
					"	a.separate_product";
				$sql_base = "select ".($data > 0 ? "top ".$data : "")." p.projectid, p.pubdate, a.pid, a.pname, a.subtitle, a.author, isnull(a.canbuy, 1) as canbuy, a.code, ".
					"	a.isbn, a.price, a.ordertype, a.binding, a.bdetails, a.discount, isnull(a.video_data, '') as video_data ".
					"from cc_product as a ".
					"	left join cc_book as b on (b.isbnx = a.isbn and isnull(b.isbnx, '') != '') ".
					"	left join cc_project as p on (p.projectid = b.projectid and p.projectid is not null) ".
					"where a.active = 1 and a.datestamp is not null and a.store = 1 and a.printtype not in ('Digital')".
					"	and p.projectid in (	".
					"		select ".($data > 0 ? "top ".$data : "")." isnull(p.projectid, -a.pid) as projectid ".
					"		from cc_product as a ".
					"			inner join cc_book as b on (b.isbnx = a.isbn and isnull(b.isbnx, '') != '') ".
					"			inner join cc_project as p on (p.projectid = b.projectid and p.projectid is not null) ".
					"	where a.active = 1 and a.datestamp is not null and a.store = 1 and a.printtype not in ('Digital') ";
				$sql_ord1 = "order by a.datestamp desc ";
				$sql_ord2 = "order by a.datestamp desc ";
				break;
			case "popular":
				$sql_main = "select ".($data ? "top ".$data : "")." a.datestamp, isnull(case a.separate_product when 1 then p.projectid + 1000000 else p.projectid end, -a.pid) as projectid, ".
					"	a.separate_product as listing, 1000 as rank, case a.separate_product when 1 then a.pid else 0 end as pid ".
					"from cc_product as a ".
					"	inner join cc_book as b on (b.isbnx = a.isbn and isnull(b.isbnx, '') != '') ".
					"	inner join cc_project as p on (p.projectid = b.projectid and p.projectid is not null) ".
					"where a.active = 1 and a.datestamp is not null and a.store = 1 ".
					"order by a.datestamp desc, isnull(case a.separate_product when 1 then p.projectid + 1000000 else p.projectid end, -a.pid), ".
					"	a.separate_product";
				$sql_base = "select ".($data ? "top ".$data : "")." p.projectid, p.pubdate, a.pid, a.pname, a.subtitle, a.author, isnull(a.canbuy, 1) as canbuy, a.code, ".
					"	a.isbn, a.price, a.ordertype, a.binding, a.bdetails, a.discount, isnull(a.video_data, '') as video_data ".
					"from cc_product as a ".
					"	left join cc_book as b on (b.isbnx = a.isbn and isnull(b.isbnx, '') != '') ".
					"	left join cc_project as p on (p.projectid = b.projectid and p.projectid is not null) ".
					"where a.active = 1 and a.datestamp is not null and a.store = 1".
					"	and p.projectid in (	".
					"		select ".($data ? "top ".$data : "")." isnull(p.projectid, -a.pid) as projectid ".
					"		from cc_product as a ".
					"			inner join cc_book as b on (b.isbnx = a.isbn and isnull(b.isbnx, '') != '') ".
					"			inner join cc_project as p on (p.projectid = b.projectid and p.projectid is not null) ".
					"	where a.active = 1 and a.datestamp is not null and a.store = 1 ";
				$sql_ord1 = "order by a.datestamp desc ";
				$sql_ord2 = "order by a.datestamp desc ";
				break;
		}
		
		if ($querytype == "keyword")
		{
			$sql_apid = $sql_base."	".$sql_ord1." ".$sql_ord2;
			$sql_npid = $sql_base."	".$sql_ord1." ".$sql_ord2;
		}
		else
		{
			$sql_apid = $sql_base."	and p.projectid is not null ".$sql_ord1.") ".$sql_ord2;
			$sql_npid = $sql_base."	and p.projectid is null ".$sql_ord1.")".$sql_ord2;
		}
		
//echo $sql_main;
//exit();
		
		if ($sql_main != "")
		{
			$rs = $this->_db->get_results($sql_main);
			if (count($rs) > 0)
			{
				$added = 0;
				foreach ($rs as $row)
				{
					if (!array_key_exists(abs($row->projectid), $products))
					{
						$added++;
						$xpj = abs($row->projectid);
						$axp = new ProductGroup($added, ($xpj > 1000000 ? $xpj - 1000000 : $xpj), $row->rank);
						if ($row->listing == 1)
						{
							$axp->RootPID = $row->pid;
							$sql = "select p.projectid, p.pubdate, a.pid, a.pname, a.subtitle, a.author, isnull(a.canbuy, 1) as canbuy, a.code, ".
								"	a.isbn, a.price, a.ordertype, a.binding, a.bdetails, a.discount, isnull(a.video_data, '') as video_data ".
								"from cc_product as a ".
								"	left join cc_book as b on (b.isbnx = a.isbn and isnull(b.isbnx, '') != '') ".
								"	left join cc_project as p on (p.projectid = b.projectid and p.projectid is not null) ".
								"where a.active = 1 and a.store = 1 and a.pid = ".$row->pid;
							$rowx = $this->_db->get_row($sql);
							
							$url = Utilities::BeautifyURL($rowx->pname);
							$axp->Formats[] = new MiniProduct(
										$rowx->pid, 
										$rowx->pname, 
										$rowx->manufacturer, 
										$x, 
										isBlank($rowx->isbn) ? $rowx->code : $x, 
										$url
									);
						
						}
						$products[$xpj] = $axp;
					}
				}
			}
			
			$this->LastCount = count($products);
			
			$rs = $this->_db->get_results($sql_apid);
			if (isset($rs))
			{
				foreach ($rs as $row)
				{
					if (count($products[$row->projectid]->Formats) == 0)
						$products[$row->projectid]->RootPID = $row->pid;
					
					$url = Utilities::BeautifyURL($row->pname);
					$products[$row->projectid]->Formats[] = new MiniProduct(
								$row->pid, 
								$row->pname, 
								$row->manufacturer, 
								$x, 
								isBlank($row->isbn) ? $row->code : $x, 
								$url
							);
				}
			}
	
			$this->LastCount = count($products);
			
			if ($querytype != "keyword")
			{
				$rs = $this->_db->get_results($sql_npid);
				if (isset($rs))
				{
					foreach ($rs as $row)
					{
						if (count($products[$row->projectid]->Formats) == 0)
							$products[$row->projectid]->RootPID = $row->pid;
						
						$url = Utilities::BeautifyURL($row->pname);
						$products[$row->projectid]->Formats[] = new MiniProduct(
									$row->pid, 
									$row->pname, 
									$row->manufacturer, 
									$x, 
									isBlank($row->isbn) ? $row->code : $x, 
									$url
								);
					}
				}
			}
		}
		
		return $products;
	}
}

?>