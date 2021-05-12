<?php
class TextElementInfo
{
	public $ElementName;
	public $CustomData = array();
	public $Flags = TextElementFlags::TEF_NONE;
	public $OnTagOpened;
	public $OnTagClosed;
	public $OnAutoCreating;
}