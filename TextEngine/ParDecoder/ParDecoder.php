<?php

class ParDecoder extends PropertyBase
{
	public $Text;
	private $TextLength;
	private $pos;
	public $Items;
	public $SurpressError;
	public $OnGetFlags;
	public $OnSetFlags;
	public function __construct($text)
	{
		$this->TextLength = strlen($text);
		$this->Text = $text;
		$this->Items = new ParItem();
		$this->Items->ParName = "(";
		$this->Items->BaseDecoder = &$this;
		$this->Flags = PardecodeFlags::PDF_AllowMethodCall | PardecodeFlags::PDF_AllowSubMemberAccess | PardecodeFlags::PDF_AllowArrayAccess;
	}
	private $p_flags;
	public function Get_Flags()
	{
		if($this->OnGetFlags) return call_user_func_array($this->OnGetFlags, array());
		return $this->p_flags;
	}
	public function Set_Flags($value)
	{
		if($this->OnGetFlags && call_user_func_array($this->OnSetFlags, array($value))) return; 
		$this->p_flags = $value;;
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
					if($this->SurpressError)
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
		for ($i = $start; $i < $this->TextLength; $i++) {
			$cur = $this->Text[$i];
			$next = "\0";
			if ($i + 1 < $this->TextLength) {
				$next = $this->Text[$i + 1];
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
						$innerItems[]= $this->inner($value, $qutochar);
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
						if (($cur == "=" && $next == ">") || ($cur == "!" && $next == "=") || ($cur == ">" && $next == "=") || ($cur == "<" && $next == "=")) {
							$inner2->value = $cur . $next;
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
		if (!str_isnullorempty($value)) {
			$innerItems[] = $this->inner($value, $qutochar);
		}
		$this->pos = $this->TextLength;

		return $innerItems;
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
