<?php

abstract class ParTraceFlags
{
	const PTF_TRACE_PROPERTY = 1 << 0;
    const PTF_TRACE_METHOD = 1 << 1;
    const PTF_TRACE_INDIS = 1 << 2;
    const PTF_TRACE_ASSIGN = 1 << 3;
    const PTF_KEEP_VALUE = 1 << 4;
}
class ParTracer extends PropertyBase
{
	private $inner = array();
	public $Enabled;
	public $Flags;
	public function Get_Count()
	{
		return count($this->inner);
	}
	public function __construct()
	{
		$this->Flags = ParTraceFlags::PTF_KEEP_VALUE | ParTraceFlags::PTF_TRACE_METHOD | ParTraceFlags::PTF_TRACE_PROPERTY | ParTraceFlags::PTF_TRACE_ASSIGN | ParTraceFlags::PTF_TRACE_INDIS;
	}
	public function Clear()
	{
		$this->inner = [];
	}
	public function Add(&$item)
	{
		$this->inner[] = &$item;
	}
	public function &Get($id)
	{
		return $this->inner[$id];

	}
	public function &GetField($name)
	{
		$count = $this->Count;
		$item = null;
		for($i = 0; $i < $count; $i++)
		{
			unset($item);
			$item = &$this->inner[$i];
			if($item->Name == $name) return $item;
		}
		unset($item);
		$item = null;
		return $item;
	}
	public function &GetFields($name, $limit = 0)
	{
		$count = $this->Count;
		$list = array();
		$total = 0;
		for($i = 0; $i < $count; $i++)
		{
			unset($item);
			$item = &$this->inner[$i];
			if($item->Name == $name)
			{
				 $list[] = &$item;
				 $total++;
			}
			if($limit > 0 && $item >= $limit) break;
			
		}
		return $list;
	}
	public function HasFlag($flag)
	{
		return ($this->Flags & $flag) != 0;
	}
	public function HasTraceThisType($pt)
	{
		switch ($pt)
		{
			case PropType::Property:
				return $this->HasFlag(ParTraceFlags::PTF_TRACE_PROPERTY);
			case PropType::Indis:
				return $this->HasFlag(ParTraceFlags::PTF_TRACE_INDIS);
			case PropType::Method:
				return $this->HasFlag(ParTraceFlags::PTF_TRACE_METHOD);
		}
		return false;
	}
}
