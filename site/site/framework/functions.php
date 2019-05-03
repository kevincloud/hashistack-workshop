<?php

//////////////////////////////////////////////////////////////////////////////////
// SANITIZATION
//////////////////////////////////////////////////////////////////////////////////

// include_once("sanitize.php");

//////////////////////////////////////////////////////////////////////////////////
// MARKDOWN
//////////////////////////////////////////////////////////////////////////////////

// include_once "markdown.php";

//////////////////////////////////////////////////////////////////////////////////
// GENERIC
//////////////////////////////////////////////////////////////////////////////////

function contains($t, $f)
{
	$r = false;
	if (stripos($t, $f) !== false) $r = true;
	return $r;
}

function base64url_encode($data)
{
	return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64url_decode($data)
{
	return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
}

function smartQuote($value, $forcetext=false)
{
	global $db;

	if (isBlank($value)) return "NULL";

    // Stripslashes
    if (get_magic_quotes_gpc()) {
        $value = stripslashes($value);
    }
    // Quote if not integer
    if (!is_numeric($value) || $forcetext === true) {
		//$unpacked = unpack('H*hex', $value);
		//$value = '0x' . $unpacked['hex'];
		$value = "'".str_replace("'", "''", $value)."'";
    }
	
    return $value;
}

function isBlank($item)
{
	if ($item == '0') {
		return false;
	}
	else {
		if (is_string($item)) $item = trim($item);
		if (empty($item)) return true;
		if (!isset($item)) return true;
	}
	return false;
}

function niceDate($d)
{
	if (!is_numeric($d)) $d = strtotime($d);
	return date("D, n/j/y", $d)." at ".date("g:ia", $d);
}

function justDate($d)
{
	if (!is_numeric($d)) $d = strtotime($d);
	return date("D, n/j/y", $d);
}


//////////////////////////////////////////////////////////////////////////////////
// STRINGS
//////////////////////////////////////////////////////////////////////////////////

function charClean($src = '')
{
	$src = str_replace("�", "'", $src);
	$src = str_replace("�", "'", $src);
	$src = str_replace("�", '"', $src);
	$src = str_replace("�", '"', $src);
	$src = str_replace("�", "-", $src);
	$src = str_replace("�","-",$src);
	$src = str_replace("�", "...", $src);
	return $src;
}

function auto_link($text)
{
	return ereg_replace("[[:alpha:]]+://[^<>[:space:]]+[[:alnum:]/]","<a target=\"_blank\" href=\"\\0\">\\0</a>", $text);
}

function match_urls($text)
{
	return ereg("[[:alpha:]]+://[^<>[:space:]]+[[:alnum:]/]", $text);
}

function strip_selected_tags($text, $tags = array())
{
    $args = func_get_args();
    $text = array_shift($args);
    $tags = func_num_args() > 2 ? array_diff($args,array($text))  : (array)$tags;
    foreach ($tags as $tag){
        while(preg_match('/<'.$tag.'(|\W[^>]*)>(.*)<\/'. $tag .'>/iusU', $text, $found)){
            //$text = str_replace($found[0],$found[2],$text);
			$text = str_replace($found[0],"",$text);
        }
    }
 
    return preg_replace('/(<('.join('|',$tags).')(|\W.*)\/>)/iusU', '', $text);
}

//////////////////////////////////////////////////////////////////////////////////
// FILE SYSTEM
//////////////////////////////////////////////////////////////////////////////////

function remove_dir($current_dir) {

	if($dir = @opendir($current_dir)) {
		while (($f = readdir($dir)) !== false) {
			if($f > '0' and filetype($current_dir.$f) == "file") {
				unlink($current_dir.$f);
			} elseif($f > '0' and filetype($current_dir.$f) == "dir") {
				remove_dir($current_dir.$f."\\");
			}
		}
		closedir($dir);
		rmdir($current_dir);
	}
}

function rrmdir($path)
{
  return is_file($path)?
    @unlink($path):
    array_map('rrmdir',glob($path.'/*'))==@rmdir($path)
  ;
}


//////////////////////////////////////////////////////////////////////////////////
// COLOR
//////////////////////////////////////////////////////////////////////////////////

function lightenColor($color, $percent)
{
	$color = str_replace("#", "", $color);

	$red = h2d(substr($color, 0, 2));
	$green = h2d(substr($color, 2, 2));
	$blue = h2d(substr($color, 4, 2));

	$red = intval($red + (($red / 100) * $percent));
	$green = intval($green + (($green / 100) * $percent));
	$blue = intval($blue + (($blue / 100) * $percent));

	if ($red > 255) $red = 255;
	if ($green > 255) $green = 255;
	if ($blue > 255) $blue = 255;

	$red = d2h($red);
	$green = d2h($green);
	$blue = d2h($blue);

	if (strlen($red) < 2) $red = "0".$red;
	if (strlen($green) < 2) $green = "0".$green;
	if (strlen($blue) < 2) $blue = "0".$blue;

	$newcolor = "#".$red.$green.$blue;

	if (strlen($newcolor) == 7) {
		return $newcolor;
	}
	else {
		return false;
	}
}

function darkenColor($color, $percent)
{
	$color = str_replace("#", "", $color);

	$red = h2d(substr($color, 0, 2));
	$green = h2d(substr($color, 2, 2));
	$blue = h2d(substr($color, 4, 2));

	$red = intval($red - (($red / 100) * $percent));
	$green = intval($green - (($green / 100) * $percent));
	$blue = intval($blue - (($blue / 100) * $percent));

	if ($red < 0) $red = 0;
	if ($green < 0) $green = 0;
	if ($blue < 0) $blue = 0;

	$red = d2h($red);
	$green = d2h($green);
	$blue = d2h($blue);

	if (strlen($red) < 2) $red = "0".$red;
	if (strlen($green) < 2) $green = "0".$green;
	if (strlen($blue) < 2) $blue = "0".$blue;

	$newcolor = "#".$red.$green.$blue;

	if (strlen($newcolor) == 7) {
		return $newcolor;
	}
	else {
		return false;
	}
}



?>