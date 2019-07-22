<?php

/*
 *  SEARCHHANDLER CLASS
 *  
 *  Options
 *  
 *  No options
 */

class SearchHandler extends BasePage
{
	public function Run()
	{
		$this->BeginPage();
		
		switch ($this->Action)
		{
			case "manufacturer":
				$this->ShowManufacturer();
				break;
			case "search":
				$this->ShowSearch();
				break;
			case "browser":
				$this->ShowCategory();
				break;
			case "comingsoon":
				$this->ComingSoon();
				break;
			case "popular":
				$this->Popular();
				break;
			case "books":
				$this->ShowNewReleases(200);
				break;
			case "newrelease":
			default:
				$this->ShowNewReleases(40);
				break;
		}
		
		$this->EndPage();
	}
	
	private function ShowManufacturer()
	{
		$manufacturerid = isset($this->PageVariables["id"]) ? trim($this->PageVariables["id"]) : "";
		$page = isset($this->PageVariables["page"]) ? intval($this->PageVariables["page"]) : 1;
	
		$s = new Search($this->_db);
		
		echo $s->Manufacturer($manufacturerid, $page);
	}
	
	private function ShowSearch()
	{
		$keywords = "";
		$page = 1;
		
		if ($this->Post)
		{
			$this->Redirect("/products/search/".$page."/".urlencode($this->PageVariables["keywords"]));
		}
		
		if (isset($this->PageVariables["keywords"]))
		{
			$keywords = $this->PageVariables["keywords"];
			$page = isset($this->PageVariables["page"]) ? intval($this->PageVariables["page"]) : 1;
		}
	
		$s = new Search($this->_db);
		
		echo $s->SearchResults($keywords, $page);
	}
	
	private function ShowCategory()
	{
		$catid = $this->PageVariables["category"];
		$page = isset($this->PageVariables["page"]) ? $this->PageVariables["page"] : 1;
	
		$s = new Search();
		
		echo $s->Category($catid, $page);
	}
	
	private function Popular()
	{
		$out = "";
		
		$out .= "<div class=\"content\">\n";
		$out .= "	<h2>Popular Products</h2>\n";
		$out .= "	<div class=\"breadcrumbs\">\n";
		$out .= "		<ul>\n";
		$out .= "			<li><a href=\"/\">Home</a></li>\n";
		$out .= "			<li><a href=\"/products/popular\">Popular Products</a></li>\n";
		$out .= "		</ul>\n";
		$out .= "	</div>\n";
		$out .= "	<ul class=\"product-list\">\n";
		
		$sql = "select top 20 i.pid, p.pname, p.isbn, p.code, p.author, COUNT(i.pid) ".
			"from cc_orders_items as i  ".
			"	inner join cc_orders as o on (o.ordid = i.ordid) ".
			"	inner join cc_product as p on (p.PID = i.pid) ".
			"where i.ordid like 'CO%' ".
			"	and o.orderdate > DATEADD(D, -90, GETDATE()) ".
			"	and p.active = 1 ".
			"	and p.ordertype = 'S' ".
			"group by i.pid, p.pname, p.isbn, p.code, p.author ".
			"order by COUNT(i.pid) desc";
		$rs = $this->_db->get_results($sql);
		if (count($rs) > 0)
		{
			foreach ($rs as $row)
			{
				$p = new Product($this->_db);
				try
				{
				$p->GetProduct($row->pid);
				}
				catch (Exception $e)
				{
					$out .= $e->getMessage();
				}
				$out .= $p->ListView();
			}
		}
		
		$out .= "	</ul>\n";
		$out .= "</div>\n";
		
		echo $out;
	}
	
	private function ComingSoon()
	{
		$out = "";
		
		$out .= "<div class=\"content\">\n";
		$out .= "	<h2>Coming Soon</h2>\n";
		$out .= "	<div class=\"breadcrumbs\">\n";
		$out .= "		<ul>\n";
		$out .= "			<li><a href=\"/\">Home</a></li>\n";
		$out .= "			<li><a href=\"/products/coming-soon\">Coming Soon</a></li>\n";
		$out .= "		</ul>\n";
		$out .= "	</div>\n";
		$out .= "	<ul class=\"product-list\">\n";
		
		$sql = "select pid from cc_product where ordertype = 'P' and active = 1 and printtype not in ('Digital') order by datestamp desc";
		$rs = $this->_db->get_results($sql);
		if (count($rs) > 0)
		{
			foreach ($rs as $row)
			{
				$p = new Product($this->_db);
				try
				{
				$p->GetProduct($row->pid);
				}
				catch (Exception $e)
				{
					$out .= $e->getMessage();
				}
				$out .= $p->ListView();
			}
		}
		
		$out .= "	</ul>\n";
		$out .= "</div>\n";
		
		echo $out;
	}
	
	private function ShowNewReleases($num=0)
	{
		$page = isset($this->PageVariables["page"]) ? intval($this->PageVariables["page"]) : 1;
		if (!$page)
			$page = 1;
		
		$s = new Search($this->_db);
		
		echo $s->NewReleases($num, $page);
	}
}




?>