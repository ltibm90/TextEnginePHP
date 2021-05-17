<?php
class MultiObject extends PropertyBase
{
	private $inner = array();
	public function Get_Count()
	{
		return count($this->inner);
	}
	public function Add(&$obj)
	{
		$this->inner[] = &$obj;
	}
	public function Clear()
	{
		$this->inner = [];
	}
	public function &Get($num)
	{
		$val = null;
		if ($num < 0 || $num >= $this->Count) return $val;
		$val = &$this->inner[$num];
		return $val;
	}
}
