<?php
class TextElementAttribute extends PropertyBase
{
	private $name;
	private $value;
	public $ParData;
	function Get_Name()
	{
		return $this->name;
	}
	function Set_Name($value)
	{
		$this->name = $value;
	}
	function Get_Value()
	{
		return $this->nalue;
	}
	function Set_Value($value)
	{
		$this->nalue = $value;
		//unset($this->ParData);
		$this->ParData = null;
	}
}
