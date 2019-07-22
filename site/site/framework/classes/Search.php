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
	private $ProductAPI;

	public $LastCount = 0;
	
	protected $_db;
	
	public function __construct()
	{
		global $productapi;
		
		$this->ProductAPI = $productapi;
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
				$label = str_replace("-", " ", $data);
				break;
			case "manufacturer":
				$label = "";
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
				// case "keyword":
				// 	$resultinfo = " results for <span style=\"font-weight:bold;\">".$data."</span>";
				// 	$resultnext = "/products/search/".($page + 1)."/".urlencode($data);
				// 	$resultprev = "/products/search/".($page - 1)."/".urlencode($data);
				// 	break;
				case "categories":
					$resultinfo = "";
					$resultnext = "/products/categories/".$data."/".($page + 1);
					$resultprev = "/products/categories/".$data."/".($page - 1);
					break;
				// case "manufacturer":
				// 	$resultinfo = " products for this manufacturer";
				// 	$resultnext = "/products/manufacturer/".$data;
				// 	$resultprev = "/products/manufacturer/".$data;
				// 	break;
				// case "new-releases":
				// case "popular":
				// 	$resultinfo = "";
				// 	$resultnext = "/products/".$querytype."/".($page + 1);
				// 	$resultprev = "/products/".$querytype."/".($page - 1);
				// 	break;
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
					// $p = new Product();
					try
					{
						$out .= $pg->ListView();
					}
					catch (Exception $e)
					{
						//$out .= $e->getMessage()." (".$pg->ProjectID.")";
					}
				}
				$shown++;
			}
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
	
	public function SelectProducts($querytype, $data)
	{
		$products = array();
		$added = 0;
		
		switch ($querytype)
		{
			case "keyword":
				break;
			case "categories":
				break;
			case "new-releases":
				break;
			case "popular":
				break;
		}
		
		$request = $this->ProductAPI."/category/".$data;
		$rs = null;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $request);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_HTTPGET, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$rs = json_decode(curl_exec($ch));
		curl_close($ch);

		if (count($rs) > 0)
		{
			foreach ($rs as $row)
			{
				$p = new Product();
				$p->GetProduct($row->ProductId);
				$products[] = $p;
			}
		}
		
		// $this->LastCount = count($products);
		
		// $rs = $this->_db->get_results($sql_apid);
		// if (isset($rs))
		// {
		// 	foreach ($rs as $row)
		// 	{
		// 		if (count($products[$row->projectid]->Formats) == 0)
		// 			$products[$row->projectid]->RootPID = $row->pid;
				
		// 		$url = Utilities::BeautifyURL($row->pname);
		// 		$products[$row->projectid]->Formats[] = new MiniProduct(
		// 					$row->pid, 
		// 					$row->pname, 
		// 					$row->manufacturer, 
		// 					$x, 
		// 					isBlank($row->isbn) ? $row->code : $x, 
		// 					$url
		// 				);
		// 	}
		// }

		// $this->LastCount = count($products);
		
		// if ($querytype != "keyword")
		// {
		// 	$rs = $this->_db->get_results($sql_npid);
		// 	if (isset($rs))
		// 	{
		// 		foreach ($rs as $row)
		// 		{
		// 			if (count($products[$row->projectid]->Formats) == 0)
		// 				$products[$row->projectid]->RootPID = $row->pid;
					
		// 			$url = Utilities::BeautifyURL($row->pname);
		// 			$products[$row->projectid]->Formats[] = new MiniProduct(
		// 						$row->pid, 
		// 						$row->pname, 
		// 						$row->manufacturer, 
		// 						$x, 
		// 						isBlank($row->isbn) ? $row->code : $x, 
		// 						$url
		// 					);
		// 		}
		// 	}
		// }
		
		return $products;
	}
}

?>