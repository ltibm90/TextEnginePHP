<?php
class ReturnEvulator extends BaseEvulator
{
	public function Render(&$tag, &$vars)
	{
		$cr = $this->ConditionSuccess($tag, 'if', $vars);
		if(!$cr) return null;
		$result = new TextEvulateResult();
		$result->Result = TextEvulateResult::EVULATE_RETURN;
		return $result;
	}
}
