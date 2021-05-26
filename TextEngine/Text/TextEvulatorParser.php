<?php


class TextEvulatorParser
{
	public $Text;
	private $pos = 0;
	private $TextLength;
	private $directclose = false;
	/** @var TextEvulator */
	public $Evulator;
	/** @param $baseevulator TextEvulator */
	public function __construct($baseevulator)
	{
		$this->Evulator = &$baseevulator;
	}
	private function OnTagOpened(&$element)
	{
		if($element->TagInfo && $element->TagInfo->OnTagOpened) call_user_func_array(array($element->TagInfo->OnTagOpened), $element);
		
	}
	public function Parse($baseitem, $text)
	{
		$this->Text = $text;
		$this->TextLength = strlen($this->Text);
		$this->Evulator->IsParseMode = true;
		unset($currenttag);
		if($baseitem == null)
		{
			$currenttag = &$this->Evulator->Elements;
		}
		else
		{
			$currenttag = &$baseitem;
		}
		$currenttag->BaseEvulator = $this->Evulator;
		for ($i = 0; $i < $this->TextLength; $i++) {
			unset($tag);
			$tag = $this->ParseTag($i, $currenttag);
			if($tag == null || empty($tag->ElemName))
			{
				$i = $this->pos;
				continue;
			}

			if (!$tag->SlashUsed) {
				$this->OnTagOpened($tag);
				if ($tag->HasFlag(TextElementFlags::TEF_AutoCloseIfSameTagFound))
				{
					unset($prev);
					$prev = &$this->GetNotClosedPrevTagByName($tag, $tag->ElemName);
					if ($prev != null && !$prev->Closed)
					{
						$prev->CloseState = TextElementClosedType::TECT_AUTOCLOSED;
						if($prev->TagInfo && $prev->TagInfo->OnTagClosed) call_user_func_array(array($prev->TagInfo->OnTagClosed), $prev);
						unset($currenttag);
						$currenttag = &$this->GetNotClosedPrevTag($prev);
						$tag->Parent = &$currenttag;
						if ($currenttag == null && $this->Evulator->ThrowExceptionIFPrevIsNull && !$this->Evulator->SurpressError)
						{
							$this->Evulator->IsParseMode = false;
							throw new Exception("Syntax Error");
						}
						else if($currenttag == null)
						{
							continue;
						}
					}
				}
				$currenttag->AddElement($tag);
				if ($tag->DirectClosed)
				{
				
					if($tag->TagInfo && $tag->TagInfo->OnTagClosed) call_user_func_array(array($tag->TagInfo->OnTagClosed), $tag);
					$this->Evulator->OnTagClosed($tag);
				}
			}

			if ($tag->SlashUsed) {
				unset($prevtag);
				$prevtag = &$this->GetNotClosedPrevTag($tag);
				//$alltags = $this->GetNotClosedPrevTagUntil($tag, $tag->elemName);
				$total = 0;
				/** @var TextElement $baseitem */
				unset($previtem);
				$previtem = null;
				while ($prevtag != null) {

					if (!$prevtag->NameEquals($tag->ElemName, true)) {
						$elem = new TextElement();
						$elem->BaseEvulator = $this->Evulator;
						$elem->ElemName = $prevtag->ElemName;
						$elem->ElemAttr = $prevtag->ElemAttr;
						$elem->Autoadded = true;

						$prevtag->CloseState = TextElementClosedType::TECT_CLOSED;
						if($prevtag->TagInfo && $prevtag->TagInfo->OnTagClosed) call_user_func_array(array($prevtag->TagInfo->OnTagClosed), $prevtag);
						$allowautocreation = !$elem->HasFlag(TextElementFlags::TEF_PreventAutoCreation) && ($elem->TagInfo->OnAutoCreating == null || $elem->TagInfo->OnAutoCreating($elem));
						if($allowautocreation)
						{
							
							if ($previtem != null) 
							{
								$previtem->Parent = &$elem;
								$elem->AddElement($previtem);
							}
							else
							{
								unset($currenttag);
								$currenttag = &$elem;
							}
							unset($previtem);
							$previtem = &$elem;
						}
	

					} else {
						if($prevtag->ElemName != $tag->ElemName)
						{
							$prevtag->AliasName = $tag->ElemName;
							//Alias
						}
						if ($previtem != null) {
							$previtem->Parent = &$prevtag->Parent;
							$previtem->Parent->AddElement($previtem);
						}
						else{
							unset($currenttag);
							$currenttag = &$prevtag->Parent;
						}

						$prevtag->CloseState = TextElementClosedType::TECT_CLOSED;
						break;
					}

					$t_tag = &$this->GetNotClosedPrevTag($prevtag);
					unset($prevtag);
					$prevtag = &$t_tag;
				}
				if (!$prevtag && $this->Evulator->ThrowExceptionIFPrevIsNull && !$this->Evulator->SurpressError) {
					$this->Evulator->IsParseMode = false;
					throw new Exception("Syntax Error");
				}
			} else if (!$tag->Closed) {
				unset($currenttag);
				$currenttag = &$tag;
			}


			$i = $this->pos;
		}
		$this->pos = 0;
		$this->Evulator->IsParseMode = false;
	}
	private function &GetNotClosedPrevTagByName(&$tag, $name)
	{
		$stag = &$this->GetNotClosedPrevTag($tag);
		while ($stag != null)
		{
			if ($stag->ElemName == name) return $stag;
			unset($stag);
			$stag = &$this->GetNotClosedPrevTag($stag);
		}
		$var_v = null;
		return $var_v ;
	}
	private function GetNotClosedPrevTagsUntil($tag, $name)
	{
		$array = array();
		unset($stag);
		$stag = &$this->GetNotClosedPrevTag($tag);
		while ($stag != null) {

			if ($stag->ElemName == $name) {
				$array[] = $stag;
				break;
			}
			$array[] = $stag;
			unset($stag);
			$stag = &$this->GetNotClosedPrevTag($stag);
		}
		return $array;
	}

	private function &GetNotClosedPrevTag($tag)
	{
		/** @var  $parent TextElement */
		unset($parent);
		$parent = &$tag->Parent;
		while ($parent != null) {
			if ($parent->Closed || $parent->ElemName == "#document") {
				return null;
			}
			return $parent;
		}
		$var_v = null;
		return var_v;
	}

	private function &GetNotClosedTag($tag, $name)
	{
		unset($parent);
		$parent = &$tag->Parent;
		while ($parent != null) {
			if ($parent->Closed) return null;
			if($parent->NameEquals($name))
			{
				return $parent;
			}
			unset($parent);
			$parent = &$parent->Parent;
		}
		$var_v = null;
		return var_v;
	}
	private function DecodeAmp($start, $decodedirect = true)
	{
		$current = '';
		for($i = $start; $i < $this->TextLength; $i++)
		{
			$cur = $this->Text[$i];
			if($cur == ';')
			{
				$this->pos = $i;
				if($decodedirect)
				{
					return array_value($current, $this->Evulator->AmpMaps);
				}
				else
				{
					return $current;
				}
			}
			if(!ctype_alpha($cur)) break;
			$current .= $cur;
		}
		$this->pos = $this->TextLength;
		return '&' . $current;
	}

	/** @param $parent TextElement */
	private function ParseTag($start, $parent = null)
	{
		$inspec = false;
		$tagElement = new TextElement();
		$tagElement->Parent = &$parent;
		$tagElement->BaseEvulator = $this->Evulator;
		$istextnode = false;
		$intag = false;
		$in_noparse = ($parent != null && ($parent->HasFlag(TextElementFlags::TEF_NoParse) || $parent->HasFlag(TextElementFlags::TEF_NoParse_AllowParam)));
		for ($i = $start; $i < $this->TextLength; $i++) {
			$cur = $this->Text[$i];
			$next = '\0';
			if ($i + 1 < $this->TextLength) {
				$next = $this->Text[$i + 1];
			}
			if($in_noparse && $cur == $this->Evulator->LeftTag && ($next != $this->Evulator->ParamChar || !$parent->HasFlag(TextElementFlags::TEF_NoParse_AllowParam)))
			{
				$istextnode = true;
				$tagElement->SetTextTag(true);
			}
			else
			{
				if (!$inspec) 
				{
					if ($cur == $this->Evulator->LeftTag) {
						if ($intag) {
							if($this->Evulator->SurpressError)
							{
								$tagElement->SetTextTag(true);
								$tagElement->Value = mb_substr($this->Text, $start, $i - $start);
								$this->pos = $i - 1;
								return $tagElement;
							}
							$this->Evulator->IsParseMode = false;
							throw  new Exception("Syntax Error");
						}
						$intag = true;
						continue;
					} 
					else if($this->Evulator->DecodeAmpCode && $cur == '&')
					{
						$ampcode = $this->DecodeAmp($i + 1, false);
						$i = $this->pos;
						$tagElement->SetTextTag(true);
						$tagElement->ElementType = TextElementType::EntityReferenceNode;
						if ($ampcode && $ampcode[0] == "&")
						{
							if($this->Evulator->SurpressError)
							{
								$tagElement->ElementType = TextElementType::TextNode;
							}
							else
							{
								$this->Evulator->IsParseMode = false;
								throw new Exception("Syntax Error");
							}
						}
						$tagElement->CloseState = TextElementClosedType::TECT_AUTOCLOSED;
						$tagElement->Value = $ampcode;
						return tagElement;
					}
					else 
					{
						if (!$intag) 
						{
							$istextnode = true;
							$tagElement->SetTextTag(true);
						}
					}
				}
				if (!$inspec && $cur == $this->Evulator->RightTag) 
				{
					if (!$intag)
					{
						if($this->Evulator->SurpressError)
						{
							$tagElement->SetTextTag(true);
							$tagElement->Value = mb_substr($this->Text, $start, $i - $start);
							$this->pos = $i - 1;
							return $tagElement;
						}
						$this->Evulator->IsParseMode = false;
						throw new Exception("Syntax Error");
					}
					$intag = false;
				}
			}
			$this->pos = $i;
			if (!$intag || $istextnode) {
				
				$tagElement->Value = $this->ParseInner($parent);
				if(!$in_noparse && $tagElement->ElementType == TextElementType::TextNode && empty($tagElement->Value))
				{
					return null;
				}
				$intag = false;
				if($this->directclose && $in_noparse)
				{
					$parent->AddElement($tagElement);
					$elem = new TextElement();
					$elem->Parent = $parent;

					$elem->ElemName = $parent->ElemName;
					$elem->SlashUsed = true;
					return $elem;
				}
				return $tagElement;
			}
			else {
				$this->ParseTagHeader($tagElement);
				if(empty($tagElement->ElemName)) return null;
				$intag = false;
				return $tagElement;

			}
		}
		return $tagElement;
	}

	/** @param $tagElement TextElement */
	private function ParseTagHeader(&$tagElement)
	{
		$inquot = false;
		$inspec = false;
		$current = '';
		$namefound = false;
		$inattrib = false;
		$firstslashused = false;
		$lastslashused = false;
		$currentName = '';
		$quoted = false;
		$quotchar = null;
		$initial =false;
		$istagattrib = false;
		$totalPar = 0;
		for ($i = $this->pos; $i < $this->TextLength; $i++) {
			$cur = $this->Text[$i];
			
			$next = '\0';
			$next2 = '\0';
			if ($inspec) {
				$inspec = false;
				$current .= $cur;
				continue;
			}
			if ($cur == "\\" && !$tagElement->ElementType == TextElementType::CommentNode) {
				if (!$namefound && !$tagElement->ElementType == TextElementType::Parameter) {
					if($this->Evulator->SurpressError) continue;
					throw new Exception('Syntax Error');
				}
				$inspec = true;
				continue;
			}
			if ($i + 1 < $this->TextLength) {
				$next = $this->Text[$i + 1];
			}
			if ($i + 2 < $this->TextLength) {
				$next2 = $this->Text[$i + 2];
			}
			if ($tagElement->ElementType == TextElementType::CDATASection)
			{
				if ($cur == ']' && $next == ']' && $next2 == $this->Evulator->RightTag)
				{
					$tagElement->Value = $current;
					$this->pos = $i += 2;
					return;
				}
				$current .= $cur;
				continue;
			}
			if ($this->Evulator->AllowXMLTag && $cur == '?' && !$namefound && strlen($current) == 0)
			{
				$tagElement->CloseState = TextElementClosedType::TECT_AUTOCLOSED;
				$tagElement->ElementType = TextElementType::XMLTag;
				continue;
			}
			if ($this->Evulator->SupportExclamationTag && $cur == '!' && !$namefound && strlen($current) == 0)
			{
				$tagElement->CloseState = TextElementClosedType::TECT_AUTOCLOSED;
				if ($i + 8 < $this->TextLength)
				{
					$mtn = substr($this->Text, $i, 8);
					if ($this->Evulator->SupportCDATA && $mtn == "![CDATA[")
					{
						$tagElement->ElementType = TextElementType::CDATASection;
						$tagElement->ElemName = "#cdata";
						$namefound = true;
						$i += 7;
						continue;
					}
				}
			}
			if ($cur == '\\' && $tagElement->ElementType != TextElementType::CommentNode)
			{
				if (!$namefound && $tagElement->ElementType != TextElementType::Parameter)
				{
					if($this->Evulator->SurpressError) continue;
					$this->Evulator->IsParseMode = false;
					throw new Exception("Syntax Error");
				}
				$inspec = true;
				continue;
			}
			if(!$initial && $cur == '!' && $next == '-' && $next2 == '-')
			{
				$tagElement->IsSummary = true;
				$tagElement->ElemName = '#summary';
				$tagElement->CloseState = TextElementClosedType::TECT_CLOSED;
				$tagElement->ElementType = TextElementType::CommentNode;
				$i += 2;
				continue;
			}
			if($tagElement->ElementType == TextElementType::CommentNode)
			{
				if ($cur == '-' && $next == '-' && $next2 == $this->Evulator->RightTag)
				{
					$tagElement->value = $current;
					$this->pos = $i + 2;
					return;
				}
				else
				{
					$current .=$cur;
				}
				continue;
			}
			$initial = true;
			if($this->Evulator->DecodeAmpCode && !$tagElement->IsSummary && $cur == '&') {
				$current .= $this->DecodeAmp($i + 1);
				$i = $this->pos;
				continue;
			}
			if(($tagElement->ElementType == TextElementType::Parameter && $this->Evulator->ParamNoAttrib) ||
			 ($namefound && $tagElement->NoAttrib) || ($istagattrib && $tagElement->HasFlag(TextElementFlags::TEF_TagAttribonly))
			)
			{
				if ($inquot && $quotchar == $cur)
				{
					$inquot = false;
				}
				else if (!$inquot && ($cur == "'" || $cur == "\""))
				{
					$inquot = true;
					$quotchar = $cur;
				}
				if(!$inquot && $cur == $this->Evulator->LeftTag && $tagElement->AllowIntertwinedPar)
				{
					$totalPar++;
				}
				if($inquot || $totalPar > 0 ||  (($cur != $this->Evulator->RightTag && $tagElement->ElementType == TextElementType::Parameter) || $cur != $this->Evulator->RightTag && ($cur != '/' && $next != $this->Evulator->RightTag || $tagElement->HasFlag(TextElementFlags::TEF_DisableLastSlash))))
				{
					if (!$inquot && $cur == $this->Evulator->RightTag && $totalPar > 0) $totalPar--;
					$current .= $cur;
					continue;
				}
			}
			if ($firstslashused && $namefound) {
				if ($cur != $this->Evulator->RightTag) {
					if ($cur == ' ' && $next != '\t' && $next != ' ') {
						if($this->Evulator->SurpressError) continue;
						$this->Evulator->IsParseMode = false;
						throw new Exception('Syntax Error');
					}
				}
			}
			if ($cur == "\"" ||$cur == "'" ) {
				if (!$namefound || empty($currentName)) {
					if($this->Evulator->SurpressError) continue;
					$this->Evulator->IsParseMode = false;
					throw  new Exception("Syntax Error");
				}
				if($inquot && $cur == $quotchar)
				{
					if($istagattrib)
					{
						$tagElement->TagAttrib = $current;
						$istagattrib = false;
					}
					else if (!$tagElement->HasFlag(TextElementFlags::TEF_TagAttribonly) && !empty($currentName)) {
						$tagElement->ElemAttr->SetAttribute($currentName, $current);
					}
					$currentName = '';
					$current = '';
					$inquot = false;
					$quoted = true;
					continue;
				}
				else if(!$inquot)
				{
					$quotchar = $cur;
					$inquot = true;
					continue;
				}
			

			}
			if (!$inquot) {
				if($cur == $this->Evulator->ParamChar && !$namefound && !$firstslashused)
				{
					$tagElement->IsParam = true;
					$tagElement->ElementType = TextElementType::Parameter;
					$tagElement->CloseState = TextElementClosedType::TECT_CLOSED;
					continue;
				}
				if ($cur == '/') {
					if (!$namefound && !empty($current)) {
						$namefound = true;
						$tagElement->ElemName = $current;
						$current = '';
					}
					if ($namefound) {
						if($next == $this->Evulator->RightTag && !$tagElement->HasFlag(TextElementFlags::TEF_DisableLastSlash))
						{
							$lastslashused = true;
						}
					} else {
						$firstslashused = true;
					}
					if($tagElement->HasFlag(TextElementFlags::TEF_DisableLastSlash))
					{
						$current .= $cur;
					}
					continue;
				}
				if ($cur ==  '=') {
					if ($namefound) {
						if($istagattrib)
						{
							$current .= $cur;
							continue;
						}
						if (empty($current)) {
							if($this->Evulator->SurpressError) continue;
							$this->Evulator->IsParseMode = false;
							throw new Exception('Syntax Error');
						}
						$currentName = $current;
						$current = '';
					} else {
						$namefound = true;
						$tagElement->ElemName = $current;
						$current = '';
						$istagattrib = true;
						//throw new Exception('Syntax Error');
						
					}
					continue;
				}
				if ($tagElement->ElementType == TextElementType::XMLTag)
				{
					if ($cur == '?' && $next == $this->Evulator->RightTag)
					{
						$cur = $next;
						$i++;
					}
				}
				if ($cur == $this->Evulator->RightTag) {
					if (!$namefound) {
						$tagElement->ElemName = $current;
						$current = '';
					}
					if($tagElement->NoAttrib)
					{
						$tagElement->Value = $current;
					}
					else if($istagattrib)
					{
						$tagElement->TagAttrib = $current;
						$istagattrib = false;
					}
					else if (!$tagElement->HasFlag(TextElementFlags::TEF_TagAttribonly) && !empty($currentName)) {
						$tagElement->ElemAttr->SetAttribute($currentName, $current);

					} else if (!$tagElement->HasFlag(TextElementFlags::TEF_TagAttribonly) && !empty($current)) {
						$tagElement->ElemAttr->SetAttribute($current, '');
					}
					$tagElement->SlashUsed = $firstslashused;
					if ($lastslashused) {
						$tagElement->CloseState = TextElementClosedType::TECT_DIRECTCLOSED;
					}
					$elname = mb_strtolower($tagElement->ElemName);
					
					if (($this->Evulator->TagInfos->GetElementFlags($elname) & TextElementFlags::TEF_AutoClosedTag) != 0)
					{
						$tagElement->CloseState = TextElementClosedType::TECT_AUTOCLOSED;
					}
					$this->pos = $i;
					return;
				}

				if ($cur == ' ') {
					if ($next == ' ' || $next == "\t" || $next == $this->Evulator->RightTag) continue;
					if (!$namefound && !empty($current)) {
						$namefound = true;
						$tagElement->ElemName = $current;
						$current = '';

					} else if ($namefound) {
						if($istagattrib)
						{
							$tagElement->TagAttrib = $current;
							$quoted = false;
							$currentName = '';
							$current = '';
							$istagattrib = false;
						}
						else if (!$tagElement->HasFlag(TextElementFlags::TEF_TagAttribonly) && !empty($currentName)) {
							$tagElement->ElemAttr->SetAttribute($currentName, $current);
							$current = '';
							$currentName = '';
							$quoted = false;
						} else if (!$tagElement->HasFlag(TextElementFlags::TEF_TagAttribonly) && !empty($current)) {
							$tagElement->ElemAttr->SetAttribute($current, '');
							$current = '';
							$quoted = false;
						}
					}
					continue;
				}
				if ($cur == $this->Evulator->LeftTag) {
					if (!$tagElement->AllowIntertwinedPar)
					{
						if($this->Evulator->SurpressError) continue;
						$this->Evulator->IsParseMode = false;
						throw new Exception('Syntax Error');						
					}
					$totalPar++;

				}
			}
			$current .= $cur;
		}
		$this->pos = $this->TextLength;
	}
	private function ParseInner(&$parent)
	{
		$text = '';
		$inspec = false;
		$nparsetext = '';
		$parfound = false;
		$in_noparse = ($parent != null && ($parent->HasFlag(TextElementFlags::TEF_NoParse) || $parent->HasFlag(TextElementFlags::TEF_NoParse_AllowParam)));
		$this->directclose = false;
		for ($i = $this->pos; $i < $this->TextLength; $i++) {
			$cur = $this->Text[$i];
			$next = ($i + 1 < $this->TextLength) ? $this->Text[$i + 1] : '\0';
			if ($inspec) {
				$inspec = false;
				$text .= $cur;
				continue;
			}
			if ($cur == "\\") 
			{
				if ($this->Evulator->SpecialCharOption == SpecialCharType::SCT_AllowedAll || ((($this->Evulator->SpecialCharOption & SpecialCharType::SCT_AllowedClosedTagOnly) != 0) && $next == $this->Evulator->RightTag)
					|| ((($this->Evulator->SpecialCharOption & SpecialCharType::SCT_AllowedNoParseWithParamTagOnly) != 0) && $in_noparse && $parent->HasFlag(TextElementFlags::TEF_NoParse_AllowParam)))
				{
					$inspec = true;
					continue;
				}
			}
			if($this->Evulator->AllowCharMap && $cur != $this->Evulator->LeftTag && $cur != $this->Evulator->RightTag && isset($this->Evulator->CharMap[$cur]))
			{
					if($parfound)
					{
						$nparsetext .= $this->Evulator->CharMap[$cur];
					}
					else
					{
						$text .= $this->Evulator->CharMap[$cur];
					}
					continue;
			}
			//if($this->Evulator->DecodeAmpCode && $cur == '&') {
				//$text .= $this->DecodeAmp($i + 1);
				//$i = $this->pos;
				//continue;
			//}
			if($this->Evulator->NoParseEnabled && $in_noparse)
			{
				if($parfound)
				{
					if($cur == $this->Evulator->LeftTag || $cur == "\r" || $cur == "\n" || $cur == "\t" || $cur == ' ')
					{
						$text .= $this->Evulator->LeftTag . $nparsetext;
						$parfound = ($cur == $this->Evulator->LeftTag);
						$nparsetext = '';
					}
					else if($cur == $this->Evulator->RightTag)
					{
						if(mb_strtolower($nparsetext) == '/' .  mb_strtolower($parent->ElemName))
						{
							$parfound = false;
							$this->pos = $i;
							$this->directclose = true;
							if ($this->Evulator->TrimStartEnd)
							{
								return trim($text);
							}
							return $text;
						}
						else
						{
							$text .= $this->Evulator->LeftTag . $nparsetext . $cur;
							$parfound = false;
							$nparsetext = '';
						}
						continue;
					}

				}
				else
				{
					if($cur == $this->Evulator->LeftTag)
					{
						if ($next == $this->Evulator->ParamChar && $parent->HasFlag(TextElementFlags::TEF_NoParse_AllowParam))
						{
							$this->pos = $i - 1;
                            $this->directclose = false;
							if ($this->Evulator->TrimStartEnd)
							{
								return trim($text);
							}
							return $text;
						}
						$parfound = true;
						continue;
					}
				}
			}
			else
			{
				if (!$inspec && $cur == $this->Evulator->LeftTag) {
					$this->pos = $i - 1;
					if ($this->Evulator->TrimStartEnd)
					{
						return trim($text);
					}
					return $text;
				}
			}
			if($parfound)
			{
				$nparsetext .= $cur;
			}
			else
			{
				if ($this->Evulator->TrimMultipleSpaces)
				{
					if ($cur == ' ' && $next == ' ') continue;
				}
				$text .= $cur;
			}
		}
		$this->pos = $this->TextLength;
		return $text;
	}
}
