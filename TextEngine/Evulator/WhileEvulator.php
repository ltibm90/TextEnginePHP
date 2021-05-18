<?php
class WhileEvulator extends BaseEvulator
{
	public function Render(&$tag, &$vars)
	{
		if (($tag->NoAttrib && empty($tag->Value)) || (!$tag->NoAttrib && empty($tag->GetAttribute("c")))) return null;
		$result = new TextEvulateResult();
		$result->Result = TextEvulateResult::EVULATE_TEXT;
		$this->CreateLocals();
		$loop_count = 0;
		while ($this->ConditionSuccess($tag, "*", $vars))
		{
			$this->SetLocalWR("loop_count", $loop_count++);
			$cresult = $tag->EvulateValue(0, 0, $vars);
			if ($cresult == null) continue;
			$result->TextContent .= $cresult->TextContent;
			if ($cresult->Result == TextEvulateResult::EVULATE_RETURN)
			{
				$result->Result = TextEvulateResult::EVULATE_RETURN;
				$this->DestroyLocals();
				return $result;
			}
			else if ($cresult->Result == TextEvulateResult::EVULATE_BREAK)
			{
				break;
			}
		}
		$this->DestroyLocals();
		return $result;
	}
}
