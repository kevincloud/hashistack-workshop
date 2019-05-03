<?php

class Utilities
{
	public static function BeautifyURL($str)
	{
		$str = strip_tags($str);
	
		$str = preg_replace('/[^a-z0-9]+/i', ' ', $str);
		$str = preg_replace('/\s+/i', ' ', $str);
		$str = trim($str);
	
		$str = str_replace(' ', '-', $str);
		return $str;
	}
	
	public static function FormatGuid($id)
	{
		return "{".substr($id, 0, 8)."-".substr($id, 8, 4)."-".substr($id, 12, 4)."-".substr($id, 16, 4)."-".substr($id, 20)."}";
	}
}

?>