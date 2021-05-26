<?php

class ParProperty
{
	public $Name;
	public $Type;
	public $IsAssign;
	public function __construct($name = '', $type = PropType::Property, $isassign = false)
	{
		$this->Name = $name;
		$this->Type = $type;
		$this->IsAssign = $isassign;
	}
}
