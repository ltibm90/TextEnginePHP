<?php


class RepeatEvulator extends BaseEvulator
{
	public function Render(&$tag, &$vars)
	{
		$to = $this->EvulateAttribute($tag->ElemAttr['count']);
		if(!is_numeric($to))
		{
			return null;
		}
		$varname = 'current_repeat';
		
		//$this->StorePreviousValue($varname);
		$this->CreateLocals();
		$result = new TextEvulateResult();
		for($i = 0; $i < $to; $i++)
		{
			//$this->SetVar($varname, $i);
			$this->SetLocal($varname, $i);
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
		}
		//$this->RemoveVar($varname);
		$this->DestroyLocals();
		$result->Result = TextEvulateResult::EVULATE_TEXT;
		return $result;
	}
}
