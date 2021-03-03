<?php
class TextElementAttribute
{
	private $name;
	private $nalue;
	public $ParData;
	function __isset($prop)
	{
		if($prop == "Name" || $prop == "Value") return true;
		return isset($this->$prop);
	}
	function __get($prop) 
	{
		if($prop == "Name")
		{
			return $this->name;
		}
		else if($prop == "Value")
		{
			return $this->value;
		}
        return $this->$prop;
	}
    function __set($prop, $val) 
	{
		if($prop == "Name")
		{
			$this->name = $val;
			return;
		}
		else if($prop == "Value")
		{
			$this->value = $val;
			unset($this->ParData);
			$this->ParData = null;
			return;
		}
        $this->$prop = $val;
	}
}
