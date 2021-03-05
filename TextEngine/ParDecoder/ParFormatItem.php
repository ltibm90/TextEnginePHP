<?php
class ParFormatItem extends PropertyBase
{
	public $ItemType;
	private $itemText;
	function GetItemText()
	{
		return $this->itemText;
	}
	function SetItemText($value)
	{
		$this->itemText = $value;
		unset($this->ParData);
		$this->ParData = null;
	}
	public $ParData;
}
