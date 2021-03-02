<?php
class ForEvulator extends BaseEvulator
{
	public function Render(&$tag, &$vars)
	{
		$varname = $tag->GetAttribute('var');
		$start = $tag->GetAttribute('start');
		$step = $tag->GetAttribute('step');
		if(!$start)
		{
			$start = "0";
		}
		if($step === null || $step == 0)
		{
			$step = 1;
		}	
		$to = $tag->GetAttribute('to');
		if(!$varname && !$step && !$to)
		{
			return null;
		}
		$start = $this->EvulateText($start);
		$step = $this->EvulateText($step);
		if($step === null || $step == 0)
		{
			$step = 1;
		}
		$to = $this->EvulateText($to);

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
