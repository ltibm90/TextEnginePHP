<?php



class InnerItem extends PropertyBase
{
	const TYPE_STRING = 0;
	const TYPE_NUMERIC = 1;
	const TYPE_BOOLEAN = 2;
	const TYPE_VARIABLE = 3;
	public $value;
	public $quote;
	public $is_operator;
	public $type;
	public  function  IsParItem()
	{
		return false;
	}
}
class InnerGroup
{
	/** @var InnerItem[] */
	public $innerItems;
	/** @var ParItem */
	public $subPar;
	/** @var InnerGroup[] */
	public $innerGroups;
	public $innerGroupsCount;
	public $parent;
	public function IsGroup()
	{
		return true;
	}
	public function LastItem()
	{
		return $this->innerGroups[$this->innerGroupsCount - 1];
	}
}
