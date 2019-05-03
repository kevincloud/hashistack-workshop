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
		$out = "";
		
		$out .= "<div class=\"content\">\n";
		$out .= "	<div class=\"rotator\">\n";
		$out .= $this->GetNextAd("large");
		$out .= "	</div>\n";
		$out .= "	<aside class=\"right\">\n";
		$out .= "		<section class=\"popular\">\n";
		$out .= "			<h4>Popular Products</h4>\n";
		$out .= "			<ul class=\"product-grid-mini\">\n";
		$popular = $this->PopularProducts();
		foreach ($popular as $p)
		{
			$out .= "				<li class=\"product\">\n";
			$out .= "					<a href=\"/".$p->BeautifiedURL."/products/".$p->PID."/".$p->Identifier."\">\n";
			$out .= "						<img src=\"/products/images/".$p->PID."/large/".$p->Identifier.".jpg\" alt=\"".$p->EasyName."\" border=\"0\" />\n";
			$out .= "						<span class=\"product-details\">\n";
			$out .= "							<strong>".$p->Name."</strong>\n";
			$out .= "							by ".$p->Author."\n";
			$out .= "						</span>\n";
			$out .= "					</a>\n";
			$out .= "				</li>\n";
		}
		$out .= "			</ul>\n";
		$out .= "		</section>\n";
		$out .= "		<section class=\"new-releases\">\n";
		$out .= "			<h4>New Releases</h4>\n";
		$out .= "			<ul class=\"product-grid-mini\">\n";
		$newproducts = $this->NewProducts();
		foreach ($newproducts as $p)
		{
			$out .= "				<li class=\"product\">\n";
			$out .= "					<a href=\"/".$p->BeautifiedURL."/products/".$p->PID."/".$p->Identifier."\">\n";
			$out .= "						<img src=\"/products/images/".$p->PID."/large/".$p->Identifier.".jpg\" alt=\"".$p->EasyName."\" border=\"0\" />\n";
			$out .= "						<span class=\"product-details\">\n";
			$out .= "							<strong>".$p->Name."</strong>\n";
			$out .= "							by ".$p->Author."\n";
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
		for ($i = 1; $i <=6; $i++)
		{
			$out .= $this->GetNextAd("small");
		}
		$out .= "			</ul>\n";
		$out .= "		</section>\n";
		// ***INLINESQL***
		// $sql = "select top 1 NEWID(), p.pid, p.video_data from cc_product as p where active = 1 and isnull(video_data, '') != '' order by NEWID()";
		// $row = $this->_db->get_row($sql);
		// if (count($row) > 0)
		// {
		// 	$p = new Product($this->_db);
		// 	$p->GetProduct($row->pid);
		// 	$out .= "		<section class=\"wptv\">\n";
		// 	$out .= "			<h2>WPTV</h2>\n";
		// 	$out .= "			<p>See the latest book trailers and videos from the coffee industry:</p>\n";
		// 	$out .= "			".$row->video_data."\n";
		// 	$out .= "			<p><a class=\"green button\" href=\"".$p->Permalink()."\">More Information</a></p>\n";
		// 	$out .= "		</section>\n";
		// }
		$out .= "	</div>\n";
		$out .= "</div>\n";
		
		echo $out;
	}
	
	public function PopularProducts($num=NULL)
	{
		$titles = array();
		
		if ($num == NULL)
			$num = $this->Options["popularproducts"];
		
		// ***INLINESQL***
		// $sql = "select top ".smartQuote($num)." i.pid, p.pname, p.isbn, p.code, p.author, COUNT(i.pid) ".
		// 	"from cc_orders_items as i  ".
		// 	"	inner join cc_orders as o on (o.ordid = i.ordid) ".
		// 	"	inner join cc_product as p on (p.PID = i.pid) ".
		// 	"where i.ordid like 'CO%' ".
		// 	"	and o.orderdate > DATEADD(D, -".smartQuote($this->Options["popularrange"]).", GETDATE()) ".
		// 	"	and p.active = 1 ".
		// 	"	and p.ordertype = 'S' ".
		// 	"group by i.pid, p.pname, p.isbn, p.code, p.author ".
		// 	"order by COUNT(i.pid) desc";
		// $rs = $this->_db->get_results($sql);
		// if ($rs)
		// {
		// 	foreach ($rs as $row)
		// 	{
		// 		$x = Utilities::ToISBN13($row->isbn);
		// 		$url = Utilities::BeautifyURL($row->pname);
		// 		$titles[] = new MiniProduct($row->pid, $row->pname, $row->author, $row->isbn, $x, isBlank($row->isbn) ? $row->code : $x, $url);
		// 	}
		// }
		
		return $titles;
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
	
	private function GetNextAd($adtype)
	{
		$out = "";
		
		// ***INLINESQL***
		// $sql = "select top 1 a.advertid, a.adimage, a.pid, a.category, a.adguid, p.pname, p.isbn, p.author, p.code, p.binding, isnull(a.adspecial, '') as adspecial from cc_store_ads as a inner join cc_store_ads_viewed as v on (v.advertid = a.advertid) left join cc_product as p on (p.pid = a.pid) where a.adtype = ".smartQuote($adtype)." and a.active = 1 and getdate() >= a.startdate and getdate() < dateadd(d, 1, a.enddate) order by v.datestamp";
		// $row = $this->_db->get_row($sql);
		// if ($row)
		// {
		// 	$p = new Product($this->_db);
		// 	if (!isBlank($row->pid))
		// 	{
		// 		$p->GetProduct($row->pid);
		// 		$p->CalculateValues();
		// 	}
			
		// 	switch($adtype)
		// 	{
		// 		case "small":
		// 			$out .= "				<li class=\"product\">\n";
		// 			$out .= "					<div class=\"product-thumbnail\">\n";
		// 			$out .= "						<a href=\"".$p->FeaturedLink(mssql_guid_string($row->adguid))."\"><img src=\"".$p->ImageURL()."\" alt=\"".str_replace("-", " ", $p->BeautifiedURL)."\" border=\"0\" /></a>\n";
		// 			$out .= "					</div>\n";
		// 			$out .= "					<div class=\"product-details\">\n";
		// 			$out .= "						<ul>\n";
		// 			$out .= "							<li class=\"title\"><a href=\"".$p->FeaturedLink(mssql_guid_string($row->adguid))."\">".$p->ProductName."</a></li>\n";
		// 			if (!isBlank($p->AuthorID))
		// 				$out .= "							<li class=\"author\">by <a href=\"/products/author/".base64url_encode($p->AuthorID)."\">".$p->Author."</a></li>\n";
		// 			else
		// 				$out .= "							<li class=\"author\">by ".$p->Author."</li>\n";
		// 			if ($p->Rating > 0)
		// 			{
		// 				$out .= "							<li class=\"rating\">\n";
		// 				$out .= "								Rating: ".$p->ShowRating()."\n";
		// 				$out .= "							</li>\n";
		// 			}
		// 			$out .= "							<li class=\"format\">".$p->Format."</li>\n";
		// 			if ($p->CalculatedDiscount > 0)
		// 			{
		// 				$out .= "							<li class=\"reg-price\">Reg: ".money_format("%.2n", $p->Price)."</li>\n";
		// 				$out .= "							<li class=\"list-price\">Price: <strong>".money_format("%.2n", $p->CalculatedPrice)."</strong></li>\n";
		// 				$out .= "							<li class=\"savings\">Save ".round($p->CalculatedDiscount)."%</li>\n";
		// 			}
		// 			else
		// 			{
		// 				$out .= "							<li class=\"list-price\">Price: <strong>".money_format("%.2n", $p->Price)."</strong></li>\n";
		// 			}
		// 			$out .= "						</ul>\n";
		// 			$out .= "					</div>\n";
		// 			$out .= "				</li>\n";
		// 			break;
		// 		case "large":
		// 			if (isBlank($row->pid))
		// 			{
		// 				$out .= "		<a href=\"https://www.java-perks.com/Featured-Products/categories/".$row->category."\">\n";
		// 				$out .= "			<img src=\"/products/images/featured/".str_replace("-", "", mssql_guid_string($row->adguid))."/".$row->category.".jpg\" alt=\"Featured Product\" border=\"0\" />\n";
		// 				$out .= "		</a>\n";
		// 			}
		// 			else
		// 			{
		// 				$out .= "		<a href=\"".$p->FeaturedLink(mssql_guid_string($row->adguid))."\">\n";
		// 				$out .= "			<img src=\"/products/images/featured/".str_replace("-", "", mssql_guid_string($row->adguid))."/".$p->Identifier.".jpg\" alt=\"Featured Product\" border=\"0\" />\n";
		// 				$out .= "		</a>\n";
		// 			}
		// 			break;
		// 	}
		// 	$sql = "update cc_store_ads_viewed set counter = counter + 1, datestamp = getdate() where advertid = ".smartQuote($row->advertid);
		// 	$this->_db->query($sql);
		// }
		
		return $out;
	}
	
}



?>