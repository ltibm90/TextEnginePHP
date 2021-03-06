<?php


class CMEvulator extends BaseEvulator
{
	public function Render(&$tag, &$vars)
	{
		//$name = $tag->GetAttribute("__name");
		$name = $tag->ElemAttr->GetFirstKey();
		if(!isset($name) || empty($name)) return null;
		$cr = $this->ConditionSuccess($tag, 'if');
		if(!$cr) return null;
		if(!$name) return null;
		
		$element = $this->GetMacroElement($name);
		if($element)
		{
			$newelement = array();
			foreach ($element->ElemAttr as $key => $value) 
			{
				if($value->Name == "name") continue;
				//if(str_startswith($key, '__')) continue;
				//$newelement[$key] = $key;
				//$this->StorePreviousValue($key);
				//$this->Evulator->localVariables[$key] = $this->EvulateText($value);
				$newelement[$value->Name] = $this->EvulateAttribute($value, $vars);
			}
		
			foreach ($tag->ElemAttr as $key => $value) {
			
				if($value->Name == $name) continue;
				//if(str_startswith($key, '__')) continue;
				//$newelement[$key] = $key;
				//$this->StorePreviousValue($key);
				//$this->Evulator->localVariables[$key] = $this->EvulateText($value);
				$newelement[$value->Name] = $this->EvulateAttribute($value, $vars);
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

		//for($i = 0; $i < $this->Evulator->Elements->GetSubElementsCount; $i++)
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
