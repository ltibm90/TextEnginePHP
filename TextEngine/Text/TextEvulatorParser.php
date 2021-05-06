<?php


class TextEvulatorParser
{
	public $Text;
	private $pos = 0;
	private $TextLength;
	private $in_noparse = false;
	private $noparse_tag = "";
	/** @var TextEvulator */
	public $Evulator;
	/** @param $baseevulator TextEvulator */
	public function __construct($baseevulator)
	{
		$this->Evulator = &$baseevulator;
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
				$currenttag->AddElement($tag);
				if ($tag->DirectClosed)
				{
					$this->Evulator->OnTagClosed($tag);
				}
			}

			if ($tag->SlashUsed) {
				$prevtag = $this->GetNotClosedPrevTag($tag);
				//$alltags = $this->GetNotClosedPrevTagUntil($tag, $tag->elemName);
				$total = 0;
				/** @var TextElement $baseitem */
				$previtem = null;
				while ($prevtag != null) {

					if (!$prevtag->NameEquals($tag->ElemName, true)) {
						$elem = new TextElement();
						$elem->BaseEvulator = &$this->Evulator;
						$elem->ElemName = $prevtag->ElemName;
						$elem->ElemAttr = $prevtag->ElemAttr;
						$elem->Autoadded = true;

						$prevtag->Closed = true;
						if ($previtem != null) {
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

						$prevtag->Closed = true;
						break;
					}
					$prevtag = $this->GetNotClosedPrevTag($prevtag);


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
		$this->in_noparse = false;
		$this->noparse_tag = "";
		$this->Evulator->IsParseMode = false;
	}
	private function GetNotClosedPrevTagUntil($tag, $name)
	{
		$array = array();
		$stag = $this->GetNotClosedPrevTag($tag);
		while ($stag != null) {

			if ($stag->ElemName == $name) {
				$array[] = $stag;
				break;
			}
			$array[] = $stag;
			$stag = $this->GetNotClosedPrevTag($stag);
		}
		return $array;
	}

	private function GetNotClosedPrevTag($tag)
	{
		/** @var  $parent TextElement */
		$parent = $tag->Parent;
		while ($parent != null) {
			if ($parent->Closed || $parent->ElemName == "#document") {
				return null;
			}
			return $parent;
		}

		return null;
	}

	private function GetNotClosedTag($tag, $name)
	{
		$parent = $tag->Parent;
		while ($parent != null) {
			if ($parent->Closed) return null;
			if($parent->NameEquals($name))
			{
				return $parent;
			}
			$parent = $parent->Parent;
		}
		return null;
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
		$tagElement->Parent = $parent;
		$tagElement->BaseEvulator= $this->Evulator;
		$istextnode = false;
		$intag = false;
		for ($i = $start; $i < $this->TextLength; $i++) {
			if($this->Evulator->NoParseEnabled && $this->in_noparse)
			{
				$istextnode = true;
				$tagElement->SetTextTag(true);
			}
			else
			{
				$cur = $this->Text[$i];
				if (!$inspec) {
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
						$tagElement->AutoClosed = true;
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
				if (!$inspec && $cur == $this->Evulator->RightTag) {
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
				
				$tagElement->Value = $this->ParseInner();
				if(!$this->in_noparse && $tagElement->ElementType == TextElementType::TextNode && empty($tagElement->Value))
				{
					return null;
				}
				$intag = false;
				if($this->in_noparse)
				{
					$parent->AddElement($tagElement);
					$elem = new TextElement();
					$elem->Parent = $parent;

					$elem->ElemName = $this->noparse_tag;
					$elem->SlashUsed = true;
					$this->in_noparse = false;
					$this->noparse_tag = "";
					return $elem;
				}
				return $tagElement;
			}
			else {
				$this->ParseTagHeader($tagElement);
				if(empty($tagElement->ElemName)) return null;
				$intag = false;
				if($this->Evulator->NoParseEnabled && ($tagElement->GetTagFlags() & TextElementFlags::TEF_NoParse) > 0)
				{
					$this->in_noparse = true;
					$this->noparse_tag = $tagElement->ElemName;
				}
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
				$tagElement->Closed = true;
				$tagElement->AutoClosed = true;
				$tagElement->ElementType = TextElementType::XMLTag;
				continue;
			}
			if ($this->Evulator->SupportExclamationTag && $cur == '!' && !$namefound && strlen($current) == 0)
			{
				$tagElement->Closed = true;
				$tagElement->AutoClosed = true;
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
				$tagElement->Closed = true;
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
				if(($cur != $this->Evulator->RightTag && $tagElement->ElementType == TextElementType::Parameter) || $cur != $this->Evulator->RightTag && ($cur != '/' && $next != $this->Evulator->RightTag || $tagElement->HasFlag(TextElementFlags::TEF_DisableLastSlash)))
				{
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
					$tagElement->Closed = true;
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
				if ($cur == $this->Evulator->LeftTag) {
					if($this->Evulator->SurpressError) continue;
					$this->Evulator->IsParseMode = false;
					throw new Exception('Syntax Error');
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
						$tagElement->DirectClosed = true;
						$tagElement->Closed = true;
					}
					$elname = mb_strtolower($tagElement->ElemName);
					
					if (($this->Evulator->TagInfos->GetElementFlags($elname) & TextElementFlags::TEF_AutoClosedTag) != 0)
					{
						$tagElement->Closed = true;
						$tagElement->AutoClosed = true;
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
			}
			$current .= $cur;
		}
		$this->pos = $this->TextLength;
	}
	private function ParseInner()
	{
		$text = '';
		$inspec = false;
		$nparsetext = '';
		$parfound = false;

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
				if ($this->Evulator->SpecialCharOption == SpecialCharType::SCT_AllowedAll ||  ($this->Evulator->SpecialCharOption == SpecialCharType::SCT_AllowedClosedTagOnly && $next == $this->Evulator->RightTag))
				{
					$inspec = true;
					continue;
				}
				$inspec = true;
				continue;
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
			if($this->Evulator->NoParseEnabled && $this->in_noparse)
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
						if(mb_strtolower($nparsetext) == '/' .  mb_strtolower($this->noparse_tag))
						{
							$parfound = false;
							$this->pos = $i;
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
