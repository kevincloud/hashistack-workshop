<?php
#
# Product Class
#

class Product {
	
	public $PID = 0;
	public $ProductName = "";
	public $Price = 0;
	public $Discount = 0;
	public $Manufacturer = "";
	public $Cost = 0;
	public $Image = "";
	public $Description = "";
	public $Taxable = true;
	public $Weight = 0;
	public $Unit = "";
	public $SetCount = 0;
	public $Categories = array();
	public $Identifier = "";
	
	/*
	 * Class Options
	 */
	private $Option_SeeAlso = 5;
	private $ProductAPI = "";
	private $ImageLocation = "";
	private $S3Bucket = "";
	
	
	/*
	 *	Function: 	__construct()
	 *	
	 *	Summary:	Class constructor to initialize the class.
	 *	
	 *	Parameters:	None
	 *	
	 *	Returns:	No return value
	 *	
	 */
	public function __construct()
	{
		global $productapi;
		global $ImageLocation;
		global $assetbucket;

		$this->ProductAPI = $productapi;
		$this->ImageLocation = $ImageLocation;
		$this->S3Bucket = $assetbucket;
	}
	
	/*
	 *	Function: 	GetProduct()
	 *	
	 *	Summary:	Populates the class with the product information.
	 *				If no product was found, an exception is thrown.
	 *				As such, the calling function needs to be able 
	 *				to handle exceptions.
	 *	
	 *	Parameters:	$pid (int) - The product ID assigned to this product.
	 *	
	 *	Returns:	No return value
	 *	
	 */
	public function GetProduct($pid, $override=false)
	{
		if (!isBlank($pid))
		{
			$request = $this->ProductAPI."/detail/".$pid;
			$rr = new RestRunner();
			$row = $rr->Get($request);

			$this->PID = $row[0]->ProductId;
			$this->ProductName = $row[0]->ProductName;
			$this->Price = $row[0]->Price;
			$this->Discount = $row[0]->Discount;
			$this->Manufacturer = $row[0]->Manufacturer;
			$this->Cost = $row[0]->Cost;
			$this->Image = $row[0]->Image;
			$this->Description = $row[0]->Description;
			$this->Taxable = $row[0]->Taxable;
			$this->Weight = $row[0]->Weight;
			$this->Unit = $row[0]->Unit;
			$this->SetCount = $row[0]->Count;
			$this->Categories = $row[0]->Categories;
			$this->Identifier = Utilities::BeautifyURL($this->ProductName);
}
		else
			throw new Exception("No product was specified.");
	}
		
	/*
	 *	Function: 	Permalink()
	 *	
	 *	Summary:	Creates a rewritten URL to direct the customer 
	 *				to the product referenced. 
	 *	
	 *	Parameters:	No parameters
	 *	
	 *	Returns:	string
	 *	
	 */
	public function Permalink()
	{
		return "/products/".$this->PID."/".$this->Identifier;
	}
	
	/*
	 *	Function: 	ImageURL()
	 *	
	 *	Summary:	Creates a rewritten URL to direct the browser 
	 *				to the product image. 
	 *	
	 *	Parameters:	No parameters
	 *	
	 *	Returns:	string
	 *	
	 */
	public function ImageURL()
	{
		return $this->S3Bucket."images/".$this->Image;
	}
	
	public function ShowImage()
	{
		$url = $this->S3Bucket."images/".$this->Image;
		
		$ch = curl_init();
		curl_setopt ($ch, CURLOPT_URL, $url);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 0);
		$image = curl_exec($ch);
		curl_close($ch);		
		
		header("Content-type: image/jpeg");
		header("Content-Disposition: attachment; filename=\"".$this->Image."\"");
		echo $image;
	}

	/*
	 *	Function: 	DetailView()
	 *	
	 *	Summary:	Constructs the portion of a web page to display 
	 *				detailed information about this product. This includes 
	 *				description, images, reviews, excerpts, video, image, 
	 *				and provides functions for the customer to add the item 
	 *				to their shopping cart.
	 *	
	 *	Parameters:	No parameters
	 *	
	 *	Returns:	string
	 *	
	 */
	public function DetailView()
	{
		$out = "";
		$first = true;
		$isthis = "";
		$excerptid = "";
				
		$out .= "<div id=\"fb-root\"></div>\n";
		$out .= "<script>(function(d, s, id) {\n";
		$out .= "  var js, fjs = d.getElementsByTagName(s)[0];\n";
		$out .= "  if (d.getElementById(id)) return;\n";
		$out .= "  js = d.createElement(s); js.id = id;\n";
		$out .= "  js.src = \"//connect.facebook.net/en_US/all.js#xfbml=1\";\n";
		$out .= "  fjs.parentNode.insertBefore(js, fjs);\n";
		$out .= "}(document, 'script', 'facebook-jssdk'));</script>\n";
		$out .= "<style>\n";
		$out .= "	aside.sidebar {\n";
		$out .= "		display: none;\n";
		$out .= "	}\n";
		$out .= "	div.content {\n";
		$out .= "		width: 100%;\n";
		$out .= "		padding: 25px;\n";
		$out .= "	}\n";
		$out .= "</style>\n";
		$out .= "<div class=\"content\">\n";
		$out .= "	<aside class=\"single right\">\n";
		$out .= "		<h4>Details</h4>\n";
		$out .= "		<div class=\"cart-details\">\n";
		$out .= "		<form action=\"/shop/cart/add\" method=\"post\">\n";
		$out .= "			<ul>\n";
		$out .= "				<li><input type=\"submit\" class=\"green button startnow\" value=\"Add to Cart\"></li>\n";
		$out .= "				<li>Quantity: <input name=\"cart_qty\" id=\"cart_qty\" type=\"text\" maxlength=\"3\" style=\"text-align:center;width:50px;\" value=\"1\"></li>\n";
		$out .= "				<li class=\"list-price\">Price: <strong>".money_format("%.2n", $this->Price)."</strong></li>\n";
		$out .= "			</ul>\n";
		$out .= "			<input type=\"hidden\" name=\"cart_pid\" value=\"".$this->PID."\">\n";
		$out .= "		</form>\n";
		$out .= "		</div>\n";
		$out .= "		<div class=\"product-details\">\n";
		$out .= "			<ul>\n";
		$out .= "			</ul>\n";
		$out .= "		</div>\n";
		$out .= "		<section class=\"popular\">\n";
		$out .= "			<h4>You May Also Enjoy</h4>\n";
		$out .= "			<ul class=\"product-grid-mini\">\n";
		$seealso = $this->SeeAlso();
		foreach ($seealso as $p)
		{
			$out .= "				<li class=\"product\">\n";
			$out .= "					<a href=\"/products/".$p->PID."/".$p->Identifier."\">\n";
			$out .= "						<img src=\"/products/images/".$p->PID."/large/".$p->Identifier.".jpg\" alt=\"".$p->EasyName."\" border=\"0\" />\n";
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
		$out .= "	<div class=\"single-product-thumbnails\" style=\"position:relative;\">\n";
		$out .= "		<img src=\"/images/".$this->PID."\" alt=\"".str_replace("-", " ", $this->Identifier)."\" border=\"0\" />\n";
		$out .= "	</div>\n";
		$out .= "	<article class=\"single-description\">\n";
		$out .= "		<h2>\n";
		$out .= "			".$this->ProductName."\n";
		$out .= "		</h2>\n";
		$out .= "		<div class=\"social-share\">\n";
		$out .= "			<a href=\"https://twitter.com/share\" class=\"twitter-share-button\" data-count=\"none\" data-via=\"Java-Perks\">Tweet</a>\n";
		$out .= "				<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=\"//platform.twitter.com/widgets.js\";fjs.parentNode.insertBefore(js,fjs);}}(document,\"script\",\"twitter-wjs\");</script>\n";
		$out .= "				&nbsp;\n";
		$out .= "				<div class=\"fb-like\" data-send=\"false\" data-layout=\"button_count\" data-width=\"100\" data-show-faces=\"false\"></div>\n";
		$out .= "		</div>\n";
		$out .= "		<p>By ".$this->Manufacturer."</p>\n";
		$out .= "		<p>";
		$out .= "			<div style=\"border:1px solid #AB8242;\">";
		$out .= "				<div style=\"color:#457E2E;background-color:#e4dac7;font-size:16px;font-weight:bold;padding:3px;\">Formats</div>";
		$out .= "			</div>";
		$out .= "		</p>\n";
		$out .= "	</article>\n";
		$out .= "	<div class=\"detail-tabs\">\n";
		$out .= "		<nav class=\"sub-tabs\">\n";
		$out .= "			<ul>\n";
		$out .= "				<li><a class=\"selected\" id=\"description-tab\">Description</a></li>\n";
		// $out .= "				<li><a id=\"excerpt-tab\">Excerpt</a></li>\n";
		// $out .= "				<li><a id=\"reviews-tab\">Reviews</a></li>\n";
		$out .= "			</ul>\n";
		$out .= "		</nav>\n";
		$out .= "		<section class=\"selected\" id=\"description-content\">\n";
		$out .= "			".$this->Description."\n";
		$out .= "		</section>\n";
		$out .= "	</div>\n";
		$out .= "</div>\n";
		
		if ($_SESSION["__cart__"]->LastError != "")
		{
			$out .= "<script type=\"text/javascript\">gMessageBox(\"Oops! An error was encountered\", \"".str_replace("\"", "\\\"", $_SESSION["__cart__"]->LastError)."\")</script>";
			$out .= "";
			$_SESSION["__cart__"]->LastError = "";
		}
		
		return $out;
	}
	
	/*
	 *	Function: 	SeeAlso()
	 *	
	 *	Summary:	Creates an array of products listed in the 
	 *				same category(ies) as this product.
	 *	
	 *	Parameters:	$num (int) - Number of items to return.
	 *	
	 *	Returns:	MiniProduct[] array
	 *	
	 */
	public function SeeAlso($num=NULL)
	{
		if ($num == NULL)
			$num = $this->Option_SeeAlso;
		
		$retval = array();
		
		// 	foreach ($rs as $row)
		// 	{
		// 		$x = Utilities::ToISBN13($row["isbn"]);
		// 		$url = Utilities::BeautifyURL($row["pname"]);
		// 		$format = Utilities::GetFormat($row["binding"], $row["bdetails"]);
		// 		$retval[] = new MiniProduct($row["pid"], $row["pname"], $row["author"], $row["isbn"], $x, isBlank($row["isbn"])?$row["code"]:$x, $url, $format);
		// 	}
		
		return $retval;
	}
	
	/*
	 *	Function: 	FloatView()
	 *	
	 *	Summary:	Constructs a page snippet to display basic information 
	 *				about this product. This is limited to image, price, 
	 *				rating, isbn, and a function to add to the cart. All
	 *  			items are floated to the left;
	 *	
	 *	Parameters:	No parameters
	 *	
	 *	Returns:	string
	 *	
	 */
	public function FloatView()
	{
		$out = "";
		
		$out .= "	<div class=\"product-details\">\n";
		$out .= "		<div class=\"image\"><a href=\"".$this->Permalink()."\"><img src=\"/images/".$this->PID."\" alt=\"".$this->ProductName."\" border=\"0\" /></a></div>\n";
		$out .= "		<div class=\"info-block\">\n";
		$out .= "			<div class=\"title\">".$this->ProductName."</div>\n";
		$out .= "			by ".$this->Manufacturer."<br>\n";
		$out .= "			".$this->Format."<br>\n";
		$out .= "			$".money_format("%.2n", $this->Price)."<br>\n";
		$out .= "		</div>\n";
		$out .= "		<form action=\"/shop/cart/add\" method=\"post\">\n";
		$out .= "			<input type=\"submit\" class=\"green button\" value=\"Add to Cart\"/>\n";
		$out .= "			<input type=\"hidden\" name=\"cart_pid\" value=\"".$this->PID."\"/>\n";
		$out .= "			<input type=\"hidden\" name=\"cart_qty\" value=\"1\"/>\n";
		$out .= "		</form>\n";
		$out .= "	</div>\n";
		
		
		return $out;
	}
	
	/*
	 *	Function: 	ListView()
	 *	
	 *	Summary:	Constructs a page snippet to display basic information 
	 *				about this product. This is limited to image, price, 
	 *				rating, isbn, and a function to add to the cart.
	 *	
	 *	Parameters:	No parameters
	 *	
	 *	Returns:	string
	 *	
	 */
	public function ListView()
	{
		$out = "";
		
		$out .= "<li class=\"product\">\n";
		$out .= "	<div class=\"product-thumbnail\">\n";
		$out .= "		<a href=\"/products/".$this->PID."/".$this->Identifier."\"><img src=\"/images/".$this->PID."/".$this->Identifier.".jpg\" alt=\"".$this->ProductName."\" border=\"0\" /></a>\n";
		$out .= "	</div>\n";
		$out .= "	<div class=\"cart-details\" style=\"width:140px;\">\n";
		$out .= "		<ul>\n";
		$out .= "			<li><a class=\"green button\" href=\"/shop/cart/add/".$this->PID."\">Add to Cart</a></li>\n";
		$out .= "			<li class=\"list-price\">Price: <strong>".money_format("%.2n", $this->Price)."</strong></li>\n";
		$out .= "		</ul>\n";
		$out .= "	</div>\n";
		$out .= "	<div class=\"product-details\">\n";
		$out .= "		<ul>\n";
		$out .= "			<li class=\"title\"><a href=\"/products/".$this->PID."/".$this->Identifier."\">".$this->ProductName."</a></li>\n";
		$out .= "			<li class=\"manufacturer\">by ".$this->Manufacturer."</li>\n";
		$out .= "		</ul>\n";
		$out .= "	</div>\n";
		$out .= "</li>\n";
		
		return $out;
	}
	
	/*
	 *	Function: 	MinimalView()
	 *	
	 *	Summary:	Constructs a page snippet to display basic information 
	 *				about this product. This is limited to image, price, 
	 *				and a function to add to the cart.
	 *	
	 *	Parameters:	No parameters
	 *	
	 *	Returns:	string
	 *	
	 */
	public function MinimalView()
	{
		$out = "";
		$out .= "<li class=\"landing\">\n";
		$out .= "	<div class=\"landing-thumbnail\">\n";
		$out .= "		<img src=\"/products/images/".$this->PID."/large/".$this->Identifier.".jpg\" alt=\"".$this->ProductName."\" border=\"0\" />\n";
		$out .= "	</div>\n";
		$out .= "	<div style=\"height:32px; overflow:hidden; text-overflow:ellipsis; margin-bottom:7px;\"><strong>".$this->ProductName."</strong></div>\n";
		$out .= "	<div style=\"margin-bottom:7px;\">Price: <strong>$".money_format("%.2n", $this->Price)."</strong></div>\n";
		$out .= "	<div style=\"margin-bottom:7px;\"><a class=\"green button\" href=\"javascript:itmAddToCart(".$this->PID.");\">Add to Cart</a></div>\n";
		$out .= "	<div style=\"margin-bottom:7px;\"><a href=\"javascript:itmMoreInfo(".$this->PID.");\">More Info</a></div>\n";
		$out .= "</li>\n";
		
		return $out;
	}
	
	/*
	 *	Function: 	SelectView()
	 *	
	 *	Summary:	Constructs a page snippet to display basic information 
	 *				about this product. This is limited to image, a list
	 *				of formats, and a function to add to the cart.
	 *	
	 *	Parameters:	$group (ProductGroup) - All formats available for this product
	 *	
	 *	Returns:	string
	 *	
	 */
	public function SelectView($group)
	{
		$out = "";
		
		$out .= "<li class=\"product\">\n";
		$out .= "	<div class=\"product-thumbnail\">\n";
		$out .= "		<a href=\"/products/".$this->PID."/".$this->Identifier."\"><img src=\"/products/images/".$this->PID."/large/".$this->Identifier.".jpg\" alt=\"".$this->ProductName."\" border=\"0\" /></a>\n";
		$out .= "	</div>\n";
		$out .= "	<div class=\"cart-details\">\n";
		$out .= "		<ul>\n";
		$out .= "			<li class=\"manufacturer\">by ".$this->Manufacturer."</li>\n";
		$out .= "		</ul>\n";
		$out .= "	</div>\n";
		$out .= "</li>\n";
		
		return $out;
	}
	
	/*
	 *	Function: 	TextAbstract()
	 *	
	 *	Summary:	Removes HTML formatting and provides a short 
	 *  			summary of the product.
	 *	
	 *	Parameters:	$words (int) - The number of words to return
	 *	
	 *	Returns:	string
	 *	
	 */
	public function TextAbstract($words=55)
	{
		preg_match("/^([^.!?\s]*[\.!?\s]+){0,".$words."}/", strip_tags($this->Description), $abstract);
		return $abstract[0]." &hellip; <a href=\"".$this->Permalink()."\">(more)</a>";
	}
}

class Promotions
{
	public $PromoCode = "";
	public $PromoType = "";
	public $Value = 0;
	public $DiscountPlus = 0;
	public $ValueMinimum = NULL;
	public $ValueMaximum = NULL;
	public $Quantity = NULL;
	public $IsGlobal = false;
	public $Automatic = false;
	public $FreeShipping = false;
	public $Limit = 0;
}

class MiniProduct
{
	public $PID = 0;
	public $Name = "";
	public $Manufacturer = "";
	public $EasyName = "";
	public $Identifier = "";
	
	/*
	 *	Function: 	__construct()
	 *	
	 *	Summary:	Class constructor to initialize the class.
	 *	
	 *	Parameters:	$pid (int) - Product ID
	 *				$name (string) - Full name of the product
	 *				$manufacturer (string) - Manufacturer Name
	 *				$id (string) - A product identifier (ISBN or Code)
	 *				$url (string) - Beautified product name for URLs
	 *	
	 *	Returns:	No return value
	 *	
	 */
	public function __construct($pid, $name, $manufacturer, $url)
	{
		$this->PID = $pid;
		$this->Name = $name;
		$this->Manufacturer = $manufacturer;
		$this->EasyName = str_replace("-", " ", $url);
		$this->Identifier = $url;
	}
	
	/*
	 *	Function: 	Permalink()
	 *	
	 *	Summary:	Creates a rewritten URL to direct the customer 
	 *				to the product referenced. 
	 *	
	 *	Parameters:	No parameters
	 *	
	 *	Returns:	string
	 *	
	 */
	public function Permalink()
	{
		return "/products/".$this->PID."/".$this->Identifier."";
	}
	
	/*
	 *	Function: 	ImageURL()
	 *	
	 *	Summary:	Creates a rewritten URL to direct the browser 
	 *				to the product image. 
	 *	
	 *	Parameters:	No parameters
	 *	
	 *	Returns:	string
	 *	
	 */
	// public function ImageURL()
	// {
	// 	return "https://s3-us-west-2.amazonaws.com/hc-workshop-2.0-static-images/".$this->Image;
	// }
}



class ProductGroup
{
	public $ID = 0;
	public $Rank = 0;
	public $ProductID = 0;
	public $RootPID = 0;
	
	/*
	 *	Function: 	__construct()
	 *	
	 *	Summary:	Class constructor to initialize the class.
	 *	
	 *	Parameters:	$id (int) - Incremented number
	 *				$projects (int) - Project ID used to group formats
	 *				$rank (int) - Search ranking; higher is more relevant
	 *	
	 *	Returns:	No return value
	 *	
	 */
	public function __construct($id, $productid, $rank)
	{
		$this->ID = $id;
		$this->ProductID = $productid;
		$this->Rank = $rank;
	}
}

class ProductReview
{
	public $ReviewID = 0;
	public $RowID = "";
	public $CustomerID = "";
	public $ReviewDate = "";
	public $DisplayName = "";
	public $Location = "";
	public $Status = "";
	public $Rating = 0;
	public $Title = "";
	public $Review = "";
	
	
}



?>
