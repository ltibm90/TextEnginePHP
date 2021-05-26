<?php
class TextParamEvulator extends BaseEvulator
{
	public function Render(&$tag, &$vars)
	{
		$result = new TextEvulateResult();
		$result->Result = TextEvulateResult::EVULATE_TEXT;
		for($i = 0; $i < $tag->GetSubElementsCount(); $i++)
		{
			unset($elem);
			$elem = &$tag->SubElements[$i];
			if($elem->ElementType == TextElementType::TextNode)
			{
				$result->TextContent .= $elem->Value;
			}
			else if($elem->ElementType == TextElementType::Parameter)
			{
				$result->TextContent .= $elem->EvulateValue()->TextContent;
			}
		}
		return $result;
	}
}
