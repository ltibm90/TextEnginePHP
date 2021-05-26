<?php
class DoEvulator extends BaseEvulator
{
	public function Render(&$tag, &$vars)
	{
		if (($tag->NoAttrib && empty($tag->Value)) || (!$tag->NoAttrib && empty($tag->GetAttribute("c")))) return null;
		$result = new TextEvulateResult();
		$result->Result = TextEvulateResult::EVULATE_TEXT;
		$this->CreateLocals();
		$loop_count = 0;
		do
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
			if ($this->Options->Max_DoWhile_Loop !== 0 && $loop_count - 1 > $this->Options->Max_DoWhile_Loop) break;
		} while ($this->ConditionSuccess($tag, '*', $vars));
		$this->DestroyLocals();
		return $result;
	}
}
