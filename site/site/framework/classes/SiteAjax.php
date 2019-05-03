<?php

/*
 *  SITEAJAX CLASS
 */

class SiteAjax extends AjaxHandler
{
	public function Process()
	{
		switch ($this->Action)
		{
			case "":
				break;
		}
		
		$this->Complete();
	}
}





?>