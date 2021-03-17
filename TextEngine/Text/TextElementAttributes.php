<?php
class TextElementAttributes implements ArrayAccess, Iterator
{
    private $inner = array();
	private $lastElement;
	public $AutoInitialize = true;
	public function &offsetGet($offset) 
	{
		$item = null;
		$index = $this->GetIndex($offset);
		if($index == -1) return $item;
		$item = &$this->inner[$index];
        return $item;
    }
    public function offsetSet($offset, $value) 
	{
		if($value == null)
		{
			$value = new TextElementAttribute();
			$value->Name = $offset;
		}
		$this->inner[] = &$value;
    }
    
    public function offsetExists($offset) {
        return $this->GetIndex($offset) >= 0;
    }
    
    public function offsetUnset($offset) {
        unset($this->inner[mb_strtolower($offset)]);
    }
	public function HasAttribute($name)
	{
		return $this->offsetExists($name);
	}
	public function &Get($index)
	{
		return $this->inner[$index];
	}
	public function GetAttribute($name, $default = null)
	{
		$index = $this->GetIndex($name);
		if($index == -1)
		{
			return $default;
		}
		$item = &$this->inner[$index];

		return $item->Value;
	}
	public function SetAttribute($name, $value)
	{
		$index = $this->GetIndex($name);
		if($index >= 0)
		{
			$item = &$this->inner[$index];
			$item->Value = $value;
			return;
		}
		$item = new TextElementAttribute();
		$item->Name = $name;
		$item->Value = $value;
		$this->inner[] =& $item;
	}
	public function RemoveAttribute($name)
	{
		$index = $this->GetIndex($name);
		if($index < 0) return false;
		$this->RemoveAt($index);
		return true;
	}
	public function GetFirstKey()
	{
		if($this->GetCount() > 0)
		{
			return $this->inner[0]->Name;
		}
		return null;
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

    public function Contains(&$item)
    {
       return in_array($item, $this->inner);
    }
    public function IndexOf(&$item)
    {
        return array_search($item, $this->inner); 
    }
	public function GetIndex($name)
	{
		for($i = 0; $i < $this->GetCount(); $i++)
		{
			unset($item);
			$item = &$this->inner[$i];
			
			if(mb_strtolower($name) == mb_strtolower($item->Name))
			{

				return $i;
			}
		}
		return -1;
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
	private $lastIndex = 0;
	public function key() {
		return $this->lastIndex;
	}

	public function current() {
		return $this->inner[$this->lastIndex];
	}

	public function next() 
	{
		$this->lastIndex++;
	}

	public function rewind() {
		$this->lastIndex = 0;
	}

	public function seek($position) {
		$this->lastIndex = $position;
	}

	public function valid() {
		$count = $this->GetCount();

		return $count > 0 && ($this->lastIndex >= 0 && $this->lastIndex < $count);
	}
}