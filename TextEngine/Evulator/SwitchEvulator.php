<?php
class SwitchEvulator extends BaseEvulator
{
	public function Render(&$tag, &$vars)
	{
		$result = new TextEvulateResult();
		$value = $this->EvulateAttribute($tag->ElemAttr['c']);
		$default = null;
		$active = null;
	
		for($i = 0; $i < $tag->SubElementsCount; $i++)
		{
			$elem = $tag->SubElements[$i];
			if($elem->ElemName == 'default')
			{
				$default = $elem;
				continue;
			}
			else if($elem->ElemName != 'case')
			{
				continue;
			}
			if($this->EvulateCase($elem, $value))
			{
				$active = &$elem;
				break;
			}
		}
		if(!$active) $active = &$default;
		if(!$active) return $result;

		$cresult = $active->EvulateValue(0, 0, $vars);
		$result->TextContent .= $cresult->TextContent;
		if($cresult->Result == TextEvulateResult::EVULATE_RETURN)
		{
			$result->Result = TextEvulateResult::EVULATE_RETURN;
			return $result;
		}
		$result->Result = TextEvulateResult::EVULATE_TEXT;
		return $result;
	}
	/** @param $tag TextElement */
	protected function EvulateCase(&$tag, &$value)
	{
		$tagvalue = $tag->GetAttribute('v');
		return string_contains($tagvalue, $value, '|');
	}
}
