<?php
class ForeachEvulator extends BaseEvulator
{
	public function Render(&$tag, &$vars)
	{
		$varname = $tag->GetAttribute('var');
		if(!$varname)
		{
			return null;
		}
		
		$inlist = $this->EvulateAttribute($tag->ElemAttr["in"]);
		if(!$inlist || !is_array($inlist)) return null;
		//$this->StorePreviousValue($varname);
		$this->CreateLocals();
		$total = 0;
		$result = new TextEvulateResult();
		foreach ($inlist as $index => $item) {
			$this->SetLocal($varname, $item);
			$this->SetLocalWR("loop_count", $total);
			$this->SetLocalWR("loop_key", $index);

			//$this->SetVar($varname, $item);
			//$this->SetVar('loop_count', $total);
			$cresult = $tag->EvulateValue(0, 0, $vars);
			if(!$cresult) continue;
			$result->TextContent .= $cresult->TextContent;
			if($cresult->Result == TextEvulateResult::EVULATE_RETURN)
			{
				$result->Result = TextEvulateResult::EVULATE_RETURN;
				//$this->RemoveVar($varname);
				$this->DestroyLocals();
				return $result;
			}
			else if($cresult->Result == TextEvulateResult::EVULATE_BREAK)
			{
				break;
			}
			$total++;
		}
		$this->DestroyLocals();
		//$this->RemoveVar($varname);
		$result->Result = TextEvulateResult::EVULATE_TEXT;
		return $result;
	}
}
