<?php
class TexttagEvulator extends BaseEvulator
{
	public function Render(&$tag, &$vars)
	{
		$result = new TextEvulateResult();
		$result->Result = TextEvulateResult::EVULATE_TEXT;
		$result->TextContent = $tag->Value;
		return $result;
	}
}
