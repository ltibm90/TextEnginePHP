<?php
class IfEvulator  extends  BaseEvulator
{
	public function Render(&$tag, &$vars)
	{
		if($this->Evulator->IsParseMode)
		{
			return Render_ParseMode($tag, $vars);
		}
		return $this->RenderDefault($tag, $vars);
	}
	public function Render_ParseMode(&$tag, &$vars)
	{
		$result = new TextEvulateResult();
		$conditionok = $this->ConditionSuccess($tag);
		$sil = false;
		for ($i = 0; $i < $tag->GetSubElementsCount(); $i++)
		{
			$sub = $tag->SubElements[$i];
			if (!$conditionok || $sil)
			{
				if(!$sil)
				{
					if (mb_strtolower($sub.ElemName) == "else")
					{
						$conditionok = true;
					}
					else if (mb_strtolower($sub.ElemName) == "elif")
					{
						$conditionok = $this.ConditionSuccess(sub);
					}
				}
				array_splice($tag->SubElements, $i, 1);
				$i--;
				continue;
			}
			else
			{
				if(mb_strtolower($sub->ElemName) == "else" || mb_strtolower($sub->ElemName) == "elif")
				{
					$sil = true;
					$i--;
					continue;
				}
				//sub.EvulateValue(0, 0, vars);
				$sub->Parent->AddElement($sub);
			}
		}
		array_splice($tag->Parent->SubElements, $tag->Index(), 1);
		$result->Result = TextEvulateResult::EVULATE_NOACTION;
		return $result;
	}
	public function RenderDefault(&$tag, &$vars)
	{
		$result = new TextEvulateResult();
		if($this->ConditionSuccess($tag))
		{
			$elseitem = $tag->GetSubElement('elif', 'else');
			if($elseitem)
			{
				$result->End = $elseitem->Index();
			}
			$result->Result = TextEvulateResult::EVULATE_DEPTHSCAN;
		}
		else
		{
			$elseitem = $tag->GetSubElement('elif', 'else');
			$total = 0;
			while ($elseitem != null)
			{
				if(mb_strtolower($elseitem->elemName) == 'else')
				{
					$result->Start = $elseitem->Index() + 1;
					$result->Result = TextEvulateResult::EVULATE_DEPTHSCAN;
					return $result;
				}
				else
				{

					if($this->ConditionSuccess($elseitem))
					{
						$result->Start = $elseitem->Index() + 1;
						$nextelse = $elseitem->NextElementWN('elif', 'else');
						if($nextelse)
						{
							$result->End = $nextelse->Index();
						}
						$result->Result = TextEvulateResult::EVULATE_DEPTHSCAN;
						return $result;
					}
				}
				$elseitem = $elseitem->NextElementWN('elif', 'else');
			}
			if(!$elseitem)
			{
				return $result;
			}
		}
		return $result;
	}

}
