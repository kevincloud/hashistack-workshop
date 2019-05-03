<?php

/*
 *  _BLANK_HANDLER CLASS
 *  
 *  Options
 *  
 *  No options
 */

class _Blank_Handler extends BasePage
{
	public function Run()
	{
		$this->BeginPage();
		
		switch ($this->Action)
		{
			default:
				$this->DefaultView();
				break;
		}
		
		$this->EndPage();
	}
	
	public function DefaultView()
	{
		echo "";
	}
}




?>