<?php
namespace TextEngine;
class XPathFunctions
{
	public static $FunctionAliasses =
	[
		"contains" => "Contains",
		"lower-case" => "LowerCase",
		"upper-case" => "UpperCase",
		"text" => "Text",
		"starts-with" => "StartsWith",
		"ends-with" => "EndsWith",
		"position" => "Position",
		"last" => "Last",
	];
	public $BaseItem;
	public $TotalItems;
	public $ItemPosition;
	public function Contains($x, $y)
	{
		return strpos($x, $y) >= 0;
	}
	public function LowerCase($x)
	{
		return mb_strtolower($x);
	}
	public function UpperCase($x)
	{
		return strtoupper($x);
	}
	public function Text()
	{
		return $this->BaseItem->InnerText();
	}
	public function StartsWith($x, $y)
	{
		return str_startswith($x, $y);
	}
	public function EndsWith($x, $y)
	{
		return str_endsswith($x, $y);
	}
	public function Position()
	{
		return $this->BaseItem->Index();
	}
	public function Last()
	{
		return $this->TotalItems;
	}
	public function GetMetohdByName($callname)
	{
		if(!isset(self::$FunctionAliasses[$callname])) return null;
		$name = self::$FunctionAliasses[$callname];
		if (method_exists($this, $name)) 
		{
			$rmethod = new \ReflectionMethod($this, $name);
			if ($rmethod->isPublic()) {
				return $rmethod;
			}
		}
		return null;
	}
}