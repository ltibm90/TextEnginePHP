<?php
class ParTracerItem
{
	public $Name;
	public $Value;
	public $Type;
	public $Accessed;
	public $IsAssign;
	public function __construct($name, $type, &$value = null)
	{
		$this->Name = $name;
		$this->Type = $type;
		$this->Value = &$value;
	}
}
