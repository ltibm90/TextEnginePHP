<?php

class ParDecoder extends PropertyBase
{
	public $Text;
	private $TextLength;
	private $pos;
	private $_attributes;
	public $Items;
	public $OnGetAttributes;
	public function __construct($text)
	{
		$this->TextLength = strlen($text);
		$this->Text = $text;
		$this->Items = new ParItem();
		$this->Items->ParName = "(";
		$this->Items->BaseDecoder = &$this;
		$this->Attributes->Flags = PardecodeFlags::PDF_AllowMethodCall | PardecodeFlags::PDF_AllowSubMemberAccess | PardecodeFlags::PDF_AllowArrayAccess;
	}
	public function &Get_Attributes()
	{
		$attribs = null;
		if($this->OnGetAttributes) $attribs = call_user_func_array($this->OnGetAttributes, array());
		if ($attribs != null) return $attribs;
		if ($this->_attributes == null) $this->_attributes = new ParDecodeAttributes();
		return $this->_attributes;
	}
	public function Decode()
	{
		/** @var ParItem|InnerGroup $parentItem */
		$parentItem = &$this->Items;
		for ($i = 0; $i < $this->TextLength; $i++) {
			$cur = $this->Text[$i];
			if ($cur == '(' || $cur == '[' || $cur == '{') {
				unset($item);
				$item = new ParItem();
				$item->Parent = &$parentItem;
				$item->ParName = $cur;
				$item->BaseDecoder = &$this;
				$parentItem->innerItems[] = $item;
				unset($parentItem);
				$parentItem = &$item;
				continue;
			} else if ($cur == ')' || $cur == ']' || $cur == '}') {
				$tempPar = &$parentItem->Parent;
				unset($parentItem);
				$parentItem = &$tempPar;
				unset($tempPar);
				if ($parentItem == null) {
					if($this->Attributes->SurpressError)
					{
						unset($parentItem);
						$parentItem = &$this->Items;
						continue;
					}
					throw new Exception("Syntax Error");
				}
				continue;
			}
			$result = $this->DecodeText($i);
			$totals = count($result);
			for($i = 0; $i < $totals; $i++)
			{
				$parentItem->innerItems[] = $result[$i];
			}
			$i = $this->pos;
		}

	}

	private function DecodeText($start, $autopar = false)
	{
		$inspec = false;
		$inquot = false;
		$qutochar = "\0";
		$innerItems = array();
		$value = '';
		$valDotEntered  = false;
		for ($i = $start; $i < $this->TextLength; $i++) {
			$cur = $this->Text[$i];
			$next = "\0";
			$next2 = "\0";
			if ($i + 1 < $this->TextLength) {
				$next = $this->Text[$i + 1];
			}
			if ($i + 2 < $this->TextLength) {
				$next2 = $this->Text[$i + 2];
			}
			if ($inspec) {
				$value .= $cur;
				$inspec = false;
				continue;
			}
			if ($cur == "\\") {
				$inspec = true;
				continue;
			}
			if (!$inquot) {
				if ($cur == ' ' || $cur == "\t") {
					continue;
				}
				if ($cur == "'" || $cur == "\"") {
					$inquot = true;
					$qutochar = $cur;
					continue;
				}
				if ($cur == '+' || $cur == '-' || $cur == '*' ||
					$cur == '/' || $cur == '%' || $cur == "!" ||
					$cur == "=" || $cur == "&" || $cur == '|' ||
					$cur == ')' || $cur == '(' || $cur == ',' ||
					$cur == "[" || $cur == "]" || $cur == '^' ||
					$cur == '<' || $cur == '>' || $cur == '{' ||
					$cur == '}' || ($cur == ':' && $next != ':') || $cur == '?' || $cur == ".") {

					if(!str_isnullorempty($value))
					{
						if($cur == '.' && !$valDotEntered  && is_numeric($value))
						{
							$valDotEntered = true;
							$value .= $cur;
							continue;
						}
						$innerItems[]= $this->inner($value, $qutochar);
						$valDotEntered = false;
						$value = "";
					}
					if ($cur == '[' || $cur == '(' || $cur == '{') {
						$this->pos = $i - 1;
						return $innerItems;
					}
					if($autopar && ($cur == '?' || $cur == ':' || $cur == '=' || $cur == '<' || $cur == '>' || ($cur == '!' && $cur == '=')))
					{

						if (($cur == '=' && $next == '>') || ($cur == '!' && $next == '=') || ($cur == '>' && $next == '=') || ($cur == '<' && $next == '='))
						{
							$this->pos = $i;
						}
						else
						{
							$this->pos = $i;
						}
						return $innerItems;
					}
					if ($cur != '(' && $cur != ')' && $cur != "[" && $cur != "]" && $cur != "{" && $cur != "}") {
						$inner2 = new InnerItem();
						$inner2->is_operator = true;
						if (($cur == "=" && $next == ">") || ($cur == "!" && $next == "=") || ($cur == ">" && $next == "=") || ($cur == "<" && $next == "=")
							|| ($cur == "+" && $next == "=") || ($cur == "-" && $next == "=")  || ($cur == "*" && $next == "=")  || ($cur == "/" && $next == "=")
							|| ($cur == "&" && $next == "=") || ($cur == "|" && $next == "=") || ($cur == "<" && $next == "<") || ($cur == ">" && $next == ">")) {
							if ($next2 == '=' && (($cur == '<' && $next == '<') || ($cur == '>' && $next == '>') || ($cur == '%' && $next == '=')))
							{
								$inner2->value = $cur . $next . $next2;
							}
							else
							{
								$inner2->value = $cur . $next;
							}
							$i++;
						} else if (($cur == "=" || $cur == "&" || $cur == '|') && $cur == $next) {
							$inner2->value = $cur . $next;
							$i++;
						} else {
							$inner2->value = $cur;
						}

						$innerItems[] = $inner2;
						$qutochar = "\0";
						 $valuestr = $inner2->value;
						 if ($valuestr == "=" ||$valuestr == "<=" || $valuestr == ">=" || $valuestr == "<" || $valuestr == ">" || $valuestr == "!=" || $valuestr == "==")
							{
								$this->pos = $i;
								return $innerItems;
							}

					} else {
						$this->pos = $i - 1;
						return $innerItems;
					}
					continue;
				}
			} else {
				if ($cur == $qutochar) {
					$inquot = false;
					continue;
				}
			}

			if ($cur == ':' && $next == ':') {
				$value .= ':';
				$i++;
			}
			$value .= $cur;
		}
		if (!str_isnullorempty($value)|| ($qutochar == "'" || $qutochar == "\"")) {
			$innerItems[] = $this->inner($value, $qutochar);
		}
		$this->pos = $this->TextLength;

		return $innerItems;
	}
	public function Compute(&$vars = null, &$localvars = null, $autodecode = true)
	{
		if ($autodecode && !empty($this->Text) && count($this->Items->InnerItems) == 0) $this->Decode();
		$secparam = null;
		return $this->Items->Compute($vars, $secparam , $localvars);
	}
	private function inner($current, $quotchar)
	{
		$inner = new InnerItem();
		$inner->value = $current;
		$inner->quote = $quotchar;
		$inner->type = InnerItem::TYPE_STRING;

		if ($inner->quote != "'" && $inner->quote != "\"") {
			if ($current == 'true' || $current == 'false') {
				$inner->type = InnerItem::TYPE_BOOLEAN;
			} else if (is_numeric($current)) {
				$inner->type = InnerItem::TYPE_NUMERIC;
			} else {
				$inner->type = InnerItem::TYPE_VARIABLE;
			}
		}
		return $inner;
	}
}
