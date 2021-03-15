<?php
class PHPEvulator extends BaseEvulator
{
	public function Render(&$tag, &$vars)
	{
		$result = new TextEvulateResult();
		$result->Result = TextEvulateResult::EVULATE_TEXT;
		$cr = $this->ConditionSuccess($tag, 'if');
		if(!$cr) return $result;
		$istext = $tag->ElemAttr->GetAttribute("@text", "0");
		$inner = $tag->InnerText();
		$values = array();
		for($i = 0; $i < $tag->ElemAttr->GetCount(); $i++)
		{
			unset($item);
			$item = &$tag->ElemAttr->Get($i);
			if($item->Name[0] == "@" || $item->Name == "if") continue;
			$values[$item->Name] = $this->EvulateAttribute($item, $vars);
		}
		if(count($values) > 0)
		{
			extract($values, EXTR_SKIP);
		}
		if($istext)
		{
			eval("\$result->TextContent = \"$inner\";");
		}
		else
		{
			$text = "";
			eval($inner);
			$result->TextContent = $text;
		}
		return $result;
	}
}
