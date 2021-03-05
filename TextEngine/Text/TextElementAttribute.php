<?php
class TextElementAttribute extends PropertyBase
{
	private $name;
	private $nalue;
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
		return $this->value;
	}
	function Set_Value($value)
	{
		$this->value = $value;
		//unset($this->ParData);
		$this->ParData = null;
	}
}
