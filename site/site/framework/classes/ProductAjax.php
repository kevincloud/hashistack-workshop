<?php

class ProductAjax extends AjaxHandler
{
	public function Process()
	{
		switch ($this->Action)
		{
			default:
				$this->DefaultView();
				break;
		}
		
		$this->Complete();
	}
	
	public function DefaultView()
	{
		echo "";
	}
}



?>