<?php
class PropObject
{
	public $Value;
	public $PropertyInfo;
	public $PropType;
	public $Indis;
	public $CustomData;
}
abstract class PropType
{
	const Empty = 0;
	const Property = 1;
	//Not used
	const Dictionary = 2;
	//Not used
	const KeyValues = 3;
	const Indis = 4;
}