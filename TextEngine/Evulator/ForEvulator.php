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
			$start = "0";
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
				$startAttr->ParData = new ParDecode($start);
				$startAttr->ParData->Decode();
			}
			$start = $this->EvulatePar($startAttr);
		}
		if($stepAttr != null)
		{
			if($stepAttr->ParData == null)
			{
				$stepAttr->ParData = new ParDecode($step);
				$stepAttr->ParData->Decode();
			}
			$step = $this->EvulatePar($stepAttr);
		}
		if($step === null || $step == 0)
		{
			$step = 1;
		}
		$to = $this->EvulateAttribute($toAttr);
		if(($start != 0 && !is_numeric($start)) || !is_numeric($step) || !is_numeric($to))
		{
			return null;
		}

		$localVars = array();
		$_lv_index = $this->Evulator->LocalVariables->AddArray($localVars);
		//$this->StorePreviousValue($varname);
		$result = new TextEvulateResult();

		for($i = $start; $i < $to; $i += $step)
		{
			$localVars[$varname] = $i;
			//$this->SetVar($varname, $i);
			$cresult = $tag->EvulateValue(0, 0, $vars);
			if(!$cresult) continue;
			$result->TextContent .= $cresult->TextContent;
			if($cresult->Result == TextEvulateResult::EVULATE_RETURN)
			{
				$result->Result = TextEvulateResult::EVULATE_RETURN;
				//$this->RemoveVar($varname);
				$this->Evulator->LocalVariables->RemoveAt($_lv_index);
				return $result;
			}
			else if($cresult->Result == TextEvulateResult::EVULATE_BREAK)
			{
				break;
			}
		}
		//$this->RemoveVar($varname);
		$this->Evulator->LocalVariables->RemoveAt($_lv_index);
		$result->Result = TextEvulateResult::EVULATE_TEXT;
		return $result;
	}
}
