<?php

/*
 *  PRODUCTHANDLER CLASS
 *  
 *  Options
 *  
 *  No options
 */

class ProductHandler extends BasePage
{
	public function Run()
	{
		$this->AddJavascript("https://www.google.com/jsapi");
		$this->AddJavascript("/framework/js/product.js");
		$this->Cart->Checkout = false;
		
		$product = new Product();
		$pid = $this->PageVariables["pid"];
		if (!isBlank($pid))
		{
			try
			{
				$product->GetProduct($pid);
				$this->SetTitle($product->ProductName.": ".$product->Manufacturer.": Java Perks");
				$this->SetPageName($product->ProductName.": ".$product->Manufacturer.": Java Perks");
				$this->AddMetaData("property", "og:image", $product->ImageURL());
			}
			catch (Exception $ex)
			{
				$this->SetAction("notfound");
			}
		}
		
		$this->BeginPage();
		
		switch ($this->Action)
		{
			case "notfound":
				echo "<div class=\"content\">The item you're looking for does not exist.</div>";
				break;
			default:
				if (isBlank($pid))
				{
					echo "<div class=\"content\">The item you're looking for does not exist.</div>";
				}
				else
				{
					echo $product->DetailView();
				}
				break;
		}
		
		$this->EndPage();
	}
	
	
}




?>