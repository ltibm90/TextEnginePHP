<?php
class UnsetEvulator  extends  BaseEvulator
{
	public function Render(&$tag, &$vars)
	{
		$result = new TextEvulateResult();
		$result->Result = TextEvulateResult::EVULATE_NOACTION;
		if ($this->ConditionSuccess($tag, "if", $vars))
		{
			$defname = $tag->GetAttribute("name");
			if (empty($defname)) return $result;
			unset($this->Evulator->DefineParameters[$defname]);
		}
		return $result;
	}
}
