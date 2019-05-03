<?php

/*
 *  LANDINGPAGEHANDLER CLASS
 *  
 *  Options
 *  
 *  No options
 */

class LandingPageHandler extends BasePage
{
	private $Landing;
	
	public function Run()
	{
		$this->AddJavascript("/framework/js/landing.js");
		$this->BeginLanding();
		
		switch ($this->Action)
		{
			default:
				$this->LoadLandingPage($this->Action);
				break;
		}
		
		$this->EndLanding();
	}
	
	public function LoadLandingPage($pagename)
	{
		$pageid = 0;
		
		$sql = "select pageid from cc_store_landing_pages where pagetag = ".smartQuote($pagename);
		$row = $this->_db->get_row($sql);
		if (count($row) > 0)
		{
			echo "<div class=\"landing-area\">";
			echo "	<img src=\"/framework/img/BookSpecialWebBanner.jpg\" style=\"text-align:center;margin-bottom:10px;\" />";
			echo "	<ul class=\"product-list\">\n";
			$pageid = $row->pageid;
			$sql = "select pid, newid() from cc_store_landing_items where pageid = ".smartQuote($pageid)." order by newid()";
			$rs = $this->_db->get_results($sql);
			if (isset($rs))
			{
				foreach ($rs as $r)
				{
					$p = new Product($this->_db);
					try
					{
						$p->GetProduct($r->pid);
					}
					catch (Exception $e)
					{
					}
					echo $p->MinimalView();
				}
			}
			echo "	</ul>";
			echo "</div>";
			echo "<div class=\"landing-cart\">";
			echo "	<h2>CART</h2>";
			echo "	<div id=\"landing-cart-data\">";
			echo "	".$this->Cart->ShowLandingCart();
			echo "	</div>";
			echo "</div>";
		}
		else
			$this->Redirect("/");
		
	}
}




?>