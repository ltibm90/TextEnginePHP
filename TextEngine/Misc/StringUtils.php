<?php
class StringUtils
{
	public static function SplitLineWithQuote($text)
	{
		$all = [];
		$quotechar = '0';
		$start = 0;
		for ($i = 0; $i < strlen($text); $i++)
		{
			$cur = $text[$i];
			if ($quotechar == '0' && ($cur == "\"" || $cur == "'")) $quotechar = $cur;
			else if ($quotechar != '0' && $cur == $quotechar) $quotechar = '0';
			$nextN = $i + 1 < strlen($text) && $text[$i + 1] == "\n";
			if ($quotechar == '0' && ($cur == "\n" || ($cur == "\r")))
			{
				$all[] = substr($text, $start, $i - $start);
				if ($nextN) $i++;
				$start = $i + 1;
			}
		}
		if ($start < strlen($text)) $all[] = substr($text, $start);
		return $all;
	}
}