<?php
class ArrayGroup
{
	public function __construct() 
	{
    }
    private $innerArray = array();
	private $length = 0;
	public function Length()
	{
		return $this->length;
	}
	public function AddArray(&$array)
	{
		if(!is_array($array)) return -1;
		$this->innerArray[] = &$array;
		$this->length++;
		return $this->length - 1;
	}
	public function RemoveArray(&$array)
	{
		if(!is_array($array)) return false;
		$key = array_search($array, $this->innerArray, true);
		if($key >= 0)
		{
			$this->RemoveAt($key);
		}
	}
	public function RemoveAt($index)
	{
		if($index > $this->Length() || $index < 0) return;
		$this->length--;
		array_splice($this->innerArray, $index, 1);
	}
	public function GetArray($index)
	{
		if($index > $this->Length() || $index < 0) return null;
		return $this->innerArray[$index];
	}
	public function KeyExistsInAll($key)
	{
		for($i = $this->length - 1; $i >= 0; $i--)
		{
			$curArray = $this->GetArray($i);
			if(array_key_exists($key, $curArray))
			{
				return true;
			}
		}
		return false;
	}
	public function GetSingleValueForAll($key)
	{
	
		for($i = $this->length - 1; $i >= 0; $i--)
		{
			$curArray = $this->GetArray($i);
			if(array_key_exists($key, $curArray))
			{
				
				return $curArray[$key];
			}
		}
		return null;
	}
	public function GetValue($key)
	{
		return $this->GetSingleValueForAll($key);
	}
	public function SetValue($name, &$object)
	{
		if($this->length == 0)
		{
			$arr = array();
			$this->AddArray($arr);
		}
		$this->innerArray[$this->length - 1][$name] = &$object;
		
	}
}
