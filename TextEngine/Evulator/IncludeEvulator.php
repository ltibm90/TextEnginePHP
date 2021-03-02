<?php


class IncludeEvulator extends BaseEvulator
{
	public function Render(&$tag, &$vars)
	{
		if ($this->Evulator->IsParseMode)
		{
			return $this->Render_Parse($tag, $vars);
		}
		return $this->Render_Default($tag, $vars);
	}
	public function Render_Parse(&$tag, &$vars)
	{
		$loc =  $tag->GetAttribute('name');
		$loc = TE_INCLUDEBASE . "/" . $this->EvulateText($loc);
		if(!$this->ConditionSuccess($tag, "if") || !file_exists($loc)) return null;
		$xpath = $tag->GetAttribute("xpath");
		$xpathold = false;
		if (empty($xpath))
		{
			$xpath = $tag->GetAttribute("xpath_old");
			$xpathold = true;
		}
		$content = file_get_contents($loc);
		$result = new TextEvulateResult();
     
		if(empty($xpath))
		{
			$this->Evulator->ParseText($tag->Parent, $content);
		}
		else
		{
			$tempitem = new TextElement();
			$tempitem->ElemName = '#document';
			$this->Evulator->ParseText($tempitem, $content);
			$elems = new TextElements();
			if (!$xpathold)
			{
				$elems = $tempelem2->FindByXPath($xpath);
			}
			else
			{
				$elems = $tempelem2->FindByXPathOld($xpath);
			}
			for ($i = 0; $i < $elems->GetCount(); $i++)
			{
				$elems[$i]->Parent = &$tempelem;
				$tempelem->AddElement($elems[$i]);
			}
		}
		return $result;
	}
 	public function Render_Default(&$tag, &$vars)
	{
		$loc =  $tag->GetAttribute('name');	
		$loc = TE_INCLUDEBASE . '/' . $this->EvulateText($loc);

		if(!$this->ConditionSuccess($tag, "if") || !file_exists($loc)) return null;
		
		$parse = $tag->GetAttribute('parse', true);
				
		$content = file_get_contents($loc);
		$result = new TextEvulateResult();
		if($parse === 'false' || !$parse)
		{
			$result->Result = TextEvulateResult::EVULATE_TEXT;
			$result->TextContent = $content;
		}
		else
		{
			$xpath = $tag->GetAttribute("xpath");
            $xpathold = false;
			if (empty($xpath))
			{
				$xpath = $tag->GetAttribute("xpath_old");
				$xpathold = true;
			}
      
			$tempelem = new TextElement();
			$tempelem->ElemName = '#document';
			$tempelem->BaseEvulator = &$this->Evulator;
		

			$tempelem2 = new TextElement();
			$tempelem2->ElemName = '#document';
			$tempelem2->BaseEvulator = &$this->Evulator;		
			
			$this->Evulator->ParseText($tempelem2, $content);
			if(empty($xpath))
			{
				$tempelem = &$tempelem2;
			}
			else
			{
				$elems = new TextElements();
				if (!$xpathold)
				{
					$elems = $tempelem2->FindByXPath($xpath);
				}
				else
				{
					$elems = $tempelem2->FindByXPathOld($xpath);
				}
				for ($i = 0; $i < $elems->GetCount(); $i++)
				{
					$elems[$i]->Parent = &$tempelem;
					$tempelem->AddElement($elems[$i]);
				}
			}
			$cresult = $tempelem->EvulateValue(0, 0,$vars);
			$result->TextContent .= $cresult->TextContent;
			if($cresult->Result == TextEvulateResult::EVULATE_RETURN)
			{
				$result->Result = TextEvulateResult::EVULATE_RETURN;
				return $result;
			}
			$result->Result = TextEvulateResult::EVULATE_TEXT;
		}
		return $result;
	}

}
