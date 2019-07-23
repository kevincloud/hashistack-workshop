<?php

/*
 *  HOMEPAGE CLASS
 *  
 *  Options
 *  
 *  populartitles		Number of popular titles to list
 *  popularrange		Number of days back to determine popularity
 *  newreleases			Number of new releases to list
 */

class HomePageHandler extends BasePage
{
	public function Run()
	{
		$this->BeginPage();
		
		switch ($this->Action)
		{
			case "notfound":
				$this->NotFound();
				break;
			case "terms":
				$this->DisplayTerms();
				break;
			default:
				$this->Options["popularproducts"] = 5;
				$this->Options["popularrange"] = 90;
				$this->Options["newproducts"] = 5;
				$this->DisplayHomePage();
				break;
		}
		
		$this->EndPage();
	}
	
	private function NotFound()
	{
		$out = "";
		
		$out .= "<div class=\"content\">\n";
		$out .= "<h2>Oops!</h2>\n";
		$out .= "<p>Sorry, but the page you're looking for does not exist, or has been moved.</p>\n";
		$out .= "</div>\n";
		
		echo $out;
	}
	
	private function DisplayTerms()
	{
		$out = "";
		
		$out .= "<div class=\"content\">\n";
		// ***INLINESQL***
		// $sql = "select top 1 document_text from cc_documents_text where documentid = 31 order by revision_number desc";
		// $out .= $this->_db->get_var($sql);
		$out .= "</div>\n";
		
		echo $out;
	}
	
	public function DisplayHomePage()
	{
		$featured = array("BT0011", "BE0031", "BE0034", "BT0015");
		$out = "";
		
		$out .= "<div class=\"content\">\n";
		// $out .= "	<div class=\"rotator\">\n";
		// $out .= $this->GetNextAd();
		// $out .= "	</div>\n";
		$out .= "	<aside class=\"right\">\n";
		$out .= "		<section class=\"popular\">\n";
		$out .= "			<h4>Popular Products</h4>\n";
		$out .= "			<ul class=\"product-grid-mini\">\n";
		$popular = $this->PopularProducts();
		foreach ($popular as $p)
		{
			$out .= "				<li class=\"product\">\n";
			$out .= "					<a href=\"/products/".$p->PID."/".$p->Identifier."\">\n";
			$out .= "						<img src=\"/images/".$p->PID."/".$p->Identifier.".jpg\" alt=\"".$p->EasyName."\" border=\"0\" />\n";
			$out .= "						<span class=\"product-details\">\n";
			$out .= "							<strong>".$p->Name."</strong>\n";
			$out .= "							by ".$p->Manufacturer."\n";
			$out .= "						</span>\n";
			$out .= "					</a>\n";
			$out .= "				</li>\n";
		}
		$out .= "			</ul>\n";
		$out .= "		</section>\n";
		$out .= "		<section class=\"new-releases\">\n";
		$out .= "			<h4>New Products</h4>\n";
		$out .= "			<ul class=\"product-grid-mini\">\n";
		$newproducts = $this->NewProducts();
		foreach ($newproducts as $p)
		{
			$out .= "				<li class=\"product\">\n";
			$out .= "					<a href=\"/products/".$p->PID."/".$p->Identifier."\">\n";
			$out .= "						<img src=\"/images/".$p->PID."/".$p->Identifier.".jpg\" alt=\"".$p->EasyName."\" border=\"0\" />\n";
			$out .= "						<span class=\"product-details\">\n";
			$out .= "							<strong>".$p->Name."</strong>\n";
			$out .= "							by ".$p->Manufacturer."\n";
			$out .= "						</span>\n";
			$out .= "					</a>\n";
			$out .= "				</li>\n";
		}
		$out .= "			</ul>\n";
		$out .= "		</section>\n";
		$out .= "	</aside>\n";
		$out .= "	<div class=\"home-col\">\n";
		$out .= "		<section class=\"featured-titles\">\n";
		$out .= "			<h2>Featured Products</h2>\n";
//		$out .= "			<p>Highlighted products from a variety of categories.</p>\n";
		$out .= "			<ul class=\"product-grid\">\n";
		foreach ($featured as $f)
		{
			$p = new Product();
			$p->GetProduct($f);
			$out .= "				<li class=\"product\">\n";
			$out .= "					<a href=\"/products/".$p->PID."/product-name".$p->Identifier."\">\n";
			$out .= "						<img src=\"/images/".$p->PID."/".$p->Identifier.".jpg\" border=\"0\" />\n";
			$out .= "						<span class=\"product-details\">\n";
			$out .= "							<strong>".$p->ProductName."</strong>\n";
			$out .= "							by ".$p->Manufacturer."\n";
			$out .= "						</span>\n";
			$out .= "					</a>\n";
			$out .= "				</li>\n";
		}
		$out .= "			</ul>\n";
		$out .= "		</section>\n";

		// $p = new Product($this->_db);
		// $p->GetProduct($row->pid);
		// $out .= "		<section class=\"wptv\">\n";
		// $out .= "			<h2>WPTV</h2>\n";
		// $out .= "			<p>See the latest book trailers and videos from the coffee industry:</p>\n";
		// $out .= "			".$row->video_data."\n";
		// $out .= "			<p><a class=\"green button\" href=\"".$p->Permalink()."\">More Information</a></p>\n";
		// $out .= "		</section>\n";

		$out .= "	</div>\n";
		$out .= "</div>\n";
		
		echo $out;
	}
	
	public function PopularProducts($num=NULL)
	{
		$titles = array();
		
		if ($num == NULL)
			$num = $this->Options["popularproducts"];

		$list = array('BE0001', 'EM0016', 'KA0023', 'BE0027');
		foreach ($list as $i) {
			$p = new Product();
			$p->GetProduct($i);
			$items[] = new MiniProduct($p->PID, $p->ProductName, $p->Manufacturer, $p->Identifier);
		}

		return $items;
	}
	
	private function NewProducts()
	{
		$titles = array();
		$added = 0;
		$proceed = true;
		
		// ***INLINESQL***
		// $sql = "select top 10 p.pid, p.pname, p.isbn, p.code, p.author ".
		// 	"from cc_product as p ".
		// 	"where p.active = 1 ".
		// 	"	and p.ordertype = 'S' ".
		// 	"	and isnull(isbn, '') <> '' ".
		// 	"order by p.datestamp desc";
		// $rs = $this->_db->get_results($sql);
		// if ($rs)
		// {
		// 	foreach ($rs as $row)
		// 	{
		// 		$proceed = true;
		// 		foreach ($titles as $t)
		// 		{
		// 			if ($t->Name == $row->pname) $proceed = false;
		// 		}
				
		// 		if ($added >= $this->Options["newreleases"]) $proceed = false;
				
		// 		if ($proceed)
		// 		{
		// 			$x = Utilities::ToISBN13($row->isbn);
		// 			$url = Utilities::BeautifyURL($row->pname);
		// 			$titles[] = new MiniProduct($row->pid, $row->pname, $row->author, $row->isbn, $x, isBlank($row->isbn) ? $row->code : $x, $url);
		// 			$added++;
		// 		}
		// 	}
		// }
		
		return $titles;
	}
	
	private function GetNextAd()
	{
		$out = "";
		$pid = "EM0019";
		
		$p = new Product();
		$p->GetProduct($pid);
		
		$out .= "				<li class=\"product\">\n";
		$out .= "					<div class=\"product-thumbnail\">\n";
		$out .= "						<a href=\"/products/".$p->PID."/".$p->Identifier."\"><img src=\"/images/".$p->PID."/".$p->Identifier.".jpg\" alt=\"".str_replace("-", " ", $p->ProductName)."\" border=\"0\" /></a>\n";
		$out .= "					</div>\n";
		$out .= "					<div class=\"product-details\">\n";
		$out .= "						<ul>\n";
		$out .= "							<li class=\"title\"><a href=\"/products/".$p->PID."/".$p->Identifier."\">".$p->ProductName."</a></li>\n";
		$out .= "							<li class=\"author\">by ".$p->Manufacturer."</li>\n";
		$out .= "							<li class=\"list-price\">Price: <strong>".money_format("%.2n", $p->Price)."</strong></li>\n";
		$out .= "						</ul>\n";
		$out .= "					</div>\n";
		$out .= "				</li>\n";
		
		return $out;
	}
	
}



?>