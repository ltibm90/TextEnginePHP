<?php

class ParDecoder
{
	public $Text;
	private $TextLength;
	private $pos;
	public $Items;
	public function __construct($text)
	{
		$this->TextLength = strlen($text);
		$this->Text = $text;
		$this->Items = new ParItem();
		$this->Items->ParName = "(";
	}

	public function Decode()
	{
		/** @var ParItem|InnerGroup $parentItem */
		$parentItem = $this->Items;
		$isopened = false;
		for ($i = 0; $i < $this->TextLength; $i++) {
			$cur = $this->Text[$i];
			$prev = '\0';
			if ($i - 1 >= 0) {
				$prev = $this->Text[$i - 1];
			}
			if (($prev != ')' && $prev != ']' && $prev != '}' ) && ($cur == '=' || $cur == '>' || $cur == '<' || $cur == '?' || $cur == ':')) {
				if ($isopened) {

					$item = new InnerItem();
					$item->IsOperator = true;
					if (($prev == '>' && $cur == '=') || ($prev == '<' && $cur == '=') || ($prev == '!' && $cur == '=') || ($prev == '=' && $cur == '>')) {
						$item->Value = $prev + $cur;
					} else {
						$item->Value = $cur;
					}
					$parentItem = $parentItem->parent;
					$isopened = false;
					$parentItem->innerItems[] = $item;
					$i--;

				} else {
					$item = new ParItem;
					$item->Parent = $parentItem;
					$item->ParName = "(";
					$parentItem->innerItems[] = $item;
					$parentItem = $item;
					$isopened = true;
				}
				continue;
			}
			if ($cur == '(' || $cur == '[' || $cur == '{') {
				$item = new ParItem();
				$item->parent = $parentItem;
				$item->ParName = $cur;
				$parentItem->innerItems[] = $item;
				$parentItem = $item;
				continue;
			} else if ($cur == ')' || $cur == ']' || $cur == '}') {
				if($isopened)
				{
					//$isopened = false;
				}
				$parentItem = $parentItem->parent;
				if ($parentItem == null) {
					throw new Exception("Syntax Error");
				}
				continue;
			}
			$result = $this->DecodeText($i, $isopened);
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
							$this->pos = $i - 1;
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
								$this->pos = $i - 1;
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
