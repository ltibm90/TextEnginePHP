<?php


class CMEvulator extends BaseEvulator
{
	public function Render(&$tag, &$vars)
	{
		//$name = $tag->GetAttribute("__name");
		$name = key($tag->ElemAttr);
		if(!isset($name) || empty($name)) return null;
		$cr = $this->ConditionSuccess($tag, 'if');
		if(!$cr) return null;
		if(!$name) return null;
		$element = $this->GetMacroElement($name);
		if($element)
		{
			$newelement = array();
			$newelement = $element->ElemAttr;
			unset($newelement['name']);
			foreach ($tag->ElemAttr as $key => $value) {
				//if(str_startswith($key, '__')) continue;
				if($key == 'name') continue;
				//$newelement[$key] = $key;
				//$this->StorePreviousValue($key);
				//$this->Evulator->localVariables[$key] = $this->EvulateText($value);
				
				$newelement[$key] = $this->EvulateText($value, $vars);
			}
	
			$result = $element->EvulateValue(0, 0, $newelement);
			//foreach ($newelement as $index => $item) {
			//	$this->RemoveVar($index);
			//}
			return $result;
		}
		return null;
	}
	protected  function GetMacroElement($name)
	{

		//for($i = 0; $i < $this->Evulator->Elements->subElementsCount; $i++)
		//{
			/** @var $next TextElement */
		/*	$next = $this->Evulator->Elements->subElements[$i];
			if($next->elemName != 'macro') continue;
			if($next->GetAttribute('name') == $name) return $next;
		}*/
		//$elem = SavedMacros::GetMacro($name);

		return $this->Evulator->SavedMacrosList->GetMacro($name); 
	}
}
