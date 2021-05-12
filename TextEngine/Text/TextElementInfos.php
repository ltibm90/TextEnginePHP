<?php
class TextElementInfos implements ArrayAccess
{
    private $inner = array();
	private $lastElement;
	public $AutoInitialize = true;
	public $Default;
	function __construct() {
		$this->Default = new TextElementInfo();
	}
	public function &offsetGet($offset) 
	{
		$info = null;
		if(empty($offset) || $offset == "#text") return $info; 
		if(isset($this->lastElement) && $this->lastElement->ElementName == mb_strtolower($offset))
		{
			return $this->lastElement;
		}
		if(!isset($this->inner[mb_strtolower($offset)]))
		{
			if($this->AutoInitialize)
			{
				$info = new TextElementInfo();
				$info->ElementName = mb_strtolower($offset);
				$this->inner[$info->ElementName] = &$info;
			}
		}
		else
		{
			$info = &$this->inner[mb_strtolower($offset)];
		}
		unset($this->lastElement);
		$this->lastElement = &$info;
        return $info;
    }
    public function offsetSet($offset, $value) 
	{
		if(is_null($value)) return;
		$info = $this->inner[mb_strtolower($offset)];
		if(isset($info))
		{
			if($info == $this->lastElement)
			{
				unset($this->lastElement);
				$this->Remove($info);
			}
		}
		$value->ElementName = mb_strtolower($offset);
		$this->inner[$value->ElementName] = &$value;
    }
    
    public function offsetExists($offset) {
        return isset($this->inner[mb_strtolower($offset)]);
    }
    
    public function offsetUnset($offset) {
        unset($this->inner[mb_strtolower($offset)]);
    }
	public function GetCount()
	{
		return count($this->inner);
	}
    public function Add(&$item)
    {
        $this->inner[] = &$item;
    }

    public function Clear()
    {
		unset($this->inner);
		$this->inner = array();
    }
	public function HasTagInfo($tagName)
	{
		return isset($this->inner[mb_strtolower($tagName)]);
	}
	public function GetElementFlags($tagName)
	{
		if (!$this->HasTagInfo($tagName)) return TextElementFlags::TEF_NONE;
		return $this[$tagName]->Flags;
	}

    public function Contains(&$item)
    {
       return in_array($item, $this->inner);
    }
    public function IndexOf(&$item)
    {
        return array_search($item, $this->inner);
    }

    public function Remove(&$item)
    {
        $num = $this->IndexOf($item);
		if($num >= 0)
		{
			$this->RemoveAt($num);
		}
		return false;
    }

    public function RemoveAt($index)
    {
        array_splice($this->inner, $index, 1);
    }
}