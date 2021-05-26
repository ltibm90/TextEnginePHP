<?php
class ForEvulator extends BaseEvulator
{
	public function Render(&$tag, &$vars)
	{
		$varname = $tag->GetAttribute('var');
		$startAttr = &$tag->ElemAttr['start'];
		$start = null;
		if($startAttr != null) $start = $startAttr->Value;
		$stepAttr = &$tag->ElemAttr['step'];
		$step = 1;
		if($stepAttr != null) $step = $stepAttr->Value;
		if(!$start)
		{
			$start = 0;
		}
		if($step === null || $step == 0)
		{
			$step = 1;
		}	
		$toAttr = $tag->ElemAttr['to'];
		if(!$varname && !$step && (!$toAttr || !$toAttr->Value))
		{
			return null;
		}
		if($startAttr != null)
		{
			if($startAttr->ParData == null)
			{
				$startAttr->ParData = $this->CreatePardecode($start);
			}
			$start = $this->EvulatePar($startAttr, $vars);
		}
		if($stepAttr != null)
		{
			if($stepAttr->ParData == null)
			{
				$stepAttr->ParData = $this->CreatePardecode($step);
			}
			$step = $this->EvulatePar($stepAttr, $vars);
		}
		if($step === null || $step == 0)
		{
			$step = 1;
		}
		$to = $this->EvulateAttribute($toAttr, $vars);
		if(($start != 0 && !is_numeric($start)) || !is_numeric($step) || !is_numeric($to))
		{
			return null;
		}
		$this->CreateLocals();
		//$this->StorePreviousValue($varname);
		$result = new TextEvulateResult();
		$loop_count = 0;
		for($i = $start; $i < $to; $i += $step)
		{

			$this->SetLocalWR($varname, $i);
			//$this->SetVar($varname, $i);
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
			if ($this->Options->Max_For_Loop !== 0 && $loop_count++ > $this->Options->Max_For_Loop) break;
		}
		//$this->RemoveVar($varname);
		$this->DestroyLocals();
		$result->Result = TextEvulateResult::EVULATE_TEXT;
		return $result;
	}
}
