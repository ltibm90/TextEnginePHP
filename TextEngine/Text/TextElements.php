<?php
class TextElements implements ArrayAccess, Iterator
{
    private $inner = array();
    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->inner[] = &$value;
        } else {
            $this->inner[$offset] = &$value;
        }
    }
    
    public function offsetExists($offset) {
        return isset($this->inner[$offset]);
    }
    
    public function offsetUnset($offset) {
        unset($this->inner[$offset]);
    }
    
    public function &offsetGet($offset) {
		$item = null;
		if(!isset($this->inner[$offset]))
		{
			return $item;
		}
		$item = &$this->inner[$offset];
        return $item;
    }

    public function SortItems()
    {
		usort($this->inner, array('TextElements','CompareTextElements'));
    }
    private static function CompareTextElements(&$a, &$b)
    {
        if ($a->Depth() == $b->Depth())
        {
            if ($a->Index() > $b->Index())
            {
                return 1;
            }
            else if ($b->Index() > $a->Index())
            {
                return -1;
            }
            return 0;
        }
		if ($a->Depth() > $b->Depth())
        {

            $depthfark = abss($a->Depth() - $b->Depth());
            $next = $a;
            for ($i = 0; $i < $depthfark; $i++)
            {
                $next = $next->Parent;
            }
            return CompareTextElements($next, $b);
        }
        else
        {
            $depthfark = abss($a->Depth() - $b->Depth());
            $next = $b;
            for ($i = 0; i < $depthfark; $i++)
            {
                $next = $next->Parent;
            }
            return CompareTextElements($a, $next);
        }
    }
	public function GetCount()
	{
		return count($this->inner);
	}

    public function AddRange($items)
    {
		for($i = 0; $i < $items->GetCount(); $i++)
		{
			unset($current);
			$current = &$items[$i];
			$this->inner[] = &$current;
		}
    }
    public function Add(&$item)
    {
        $item->Index_old = $this->GetCount();
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
    public function FindByXPath(&$xblock)
    {
		$elements = new TextElements();
        for ($j = 0; $j < $this->GetCount(); $j++)
        {
			unset($elem);
			$elem = &$this->inner[$j];
            $nextelems = $elem->FindByXPathBlock($xblock);
            for ($k = 0; $k < $nextelems->GetCount(); $k++)
            {
                if ($elements->Contains($nextelems[$k])) continue;
                $elements->Add($nextelems[$k]);
            }
        }
        return $elements;
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