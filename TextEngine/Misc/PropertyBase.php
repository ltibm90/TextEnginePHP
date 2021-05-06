<?php
abstract class PropertyBase
{
	function __isset($prop)
	{
		if(method_exists($this, "Get_$prop") || method_exists($this, "Set_$prop")) return true;
		return isset($this->$prop);
	}
	function &__get($prop)
	{
		if(method_exists($this, "Get_$prop"))
		{
			$ret = 	call_user_func_array(array($this, "Get_$prop"), array());
			return $ret;
		}
		return $this->$prop;
	}
	function __set($prop, $value)
	{
		if(method_exists($this, "Set_$prop"))
		{
			call_user_func_array(array($this, "Set_$prop"), array($value));
			return;
		}
		$this->$prop = $value;
	}
}