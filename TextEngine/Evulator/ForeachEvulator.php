<?php
class ForeachEvulator extends BaseEvulator
{
	public function Render(&$tag, &$vars)
	{
		$varname = $tag->GetAttribute('var');
		$in = $tag->GetAttribute('in');
		if(!$varname && !$in)
		{
			return null;
		}
		$inlist = $this->EvulateText($in);
		if(!$inlist || !is_array($inlist)) return null;

		//$this->StorePreviousValue($varname);
		$localVars = array();
		$_lv_index = $this->Evulator->LocalVariables->AddArray($localVars);
		$total = 0;
		$result = new TextEvulateResult();
		foreach ($inlist as $index => $item) {
			$localVars[$varname] = $item;
			$localVars["loop_count"] = $total;
			$localVars["loop_key"] = $index;
			//$this->SetVar($varname, $item);
			//$this->SetVar('loop_count', $total);
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
			$total++;
		}
		$this->Evulator->LocalVariables->RemoveAt($_lv_index);
		//$this->RemoveVar($varname);
		$result->Result = TextEvulateResult::EVULATE_TEXT;
		return $result;
	}
}
