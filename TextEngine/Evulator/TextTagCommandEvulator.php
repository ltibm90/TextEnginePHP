<?php
class TextTagCommandEvulator extends BaseEvulator
{
	public function Render(&$tag, &$vars)
	{
		$result = new TextEvulateResult();
		$result->Result = TextEvulateResult::EVULATE_NOACTION;
		$str = $tag->Value;
		if (empty($str)) return $result;
		$lines = StringUtils::SplitLineWithQuote($str, true);

		for ($i = 0; $i < count($lines); $i++)
		{
			$line = $lines[$i];
			$line = trim($line);
			if (empty($line)) continue;
			$this->EvulateText($line, $vars);
		}
		return $result;
	}
}
