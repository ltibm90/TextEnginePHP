<?php
class SetEvulator  extends  BaseEvulator
{
	public function Render(&$tag, &$vars)
	{
		$result = new TextEvulateResult();
		$result->Result = TextEvulateResult::EVULATE_NOACTION;
		if ($this->ConditionSuccess($tag, "if", $vars))
		{
			
			$defname = $tag->GetAttribute("name");
			if (empty($defname) || !ctype_alnum($defname)) return $result;
			$this->Evulator->DefineParameters[$defname] = $this->EvulateAttribute($tag->ElemAttr['value'], $vars);
		}
		return $result;
	}
}
