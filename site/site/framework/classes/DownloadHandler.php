<?php

/*
 *  DOWNLOADHANDLER CLASS
 *  
 *  Options
 *  
 *  No options
 */

class DownloadHandler extends BasePage
{
	public function Run()
	{
		switch ($this->Action)
		{
			case "download":
				$this->Download();
				break;
		}
	}
	
	public function Download()
	{
		set_time_limit(0);
		$productid = $this->PageVariables["id"];
		$id = base64url_decode($productid);
		$data = explode(":", $id);
		$type = $data[0];
		$uid = $data[1];
		$custguid = $data[3];
		
		if ($custguid != $this->Account->RowID)
		{
			$this->ShowError("<div>".$custguid."</div><div>".$this->Account->RowID."</div><div>".$id."</div>");
			return;
		}
		
		$filename = "http://s3.amazon.com/bucketname/item/".strtoupper(mssql_guid_string($productid));
			
		header("Content-type: application/zip");
		header("Content-Disposition: attachment; filename=my_file_name.ext");
		$handle = fopen($filename, 'rb'); 
		$buffer = ''; 
		while (!feof($handle))
		{
			$buffer = fread($handle, 4096);
			echo $buffer;
			ob_flush();
			flush();
		}
		fclose($handle);			
	}
	
	public function ShowError($msg)
	{
		$this->BeginPage();
		echo "<div class=\"content\">".$msg."</div>";
		$this->EndPage();
	}
}




?>