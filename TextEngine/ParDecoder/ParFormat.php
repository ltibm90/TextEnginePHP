<?php
class ParFormat extends PropertyBase
{
	function __construct($text = "")
	{
		$this->Text = $text;
		$this->ParAttributes = new ParDecodeAttributes();
		$this->ParAttributes->Flags = PardecodeFlags::PDF_AllowArrayAccess | PardecodeFlags::PDF_AllowMethodCall | PardecodeFlags::PDF_AllowSubMemberAccess;
		$this->ParAttributes->AssignReturnType = ParItemAssignReturnType::PIART_RETURN_ASSIGNVALUE_OR_NULL;

	}
	private $ptext;
	function Get_Text()
	{
		return $this->ptext;
	}
	function Set_Text($value)
	{
		$this->ptext = $value;
		$this->FormatItems = null;
	}
    private $FormatItems;
	public $SurpressError;
	public $Flags;
	public $ParAttributes;
	public function Apply(&$data = null)
	{
		if(empty($this->Text)) return $this->Text;
		if(!$this->FormatItems)
		{
			$this->ParseFromString($this->Text);
		}
        $text = '';
		for ($i = 0; $i < count($this->FormatItems); $i++)
		{
			unset($item);
			$item = &$this->FormatItems[$i];
			if($item->ItemType == ParFormatType::TextPar)
			{
				$text .= $item->ItemText;
				continue;
			}
			else if($item->ItemType == ParFormatType::FormatPar)
			{
				if($item->ParData == null)
				{
					$item->ParData = new ParDecoder($item->ItemText);
					$item->ParData->OnGetAttributes  = function() {return $this->ParAttributes;};
					$item->ParData->Decode();

				}
				$cr = $item->ParData->Items->Compute($data);
				if($cr && isset($cr->result[0]))
				{
					if(is_array($cr->result[0]))
					{
						$text .= "array";
					}
					else if(is_object($cr->result[0]))
					{
						$text .= "object";
					}
					else
					{
						$text .=  $cr->result[0];
					}

				}
			}
		}
		return $text;
	}
	public static function Format($s, &$data = null)
	{
		$pf = new ParFormat($s);
		return $pf->Apply($data);
	}
	public static function FormatEx($s, &$data = null, $onInitialise )
	{
		$pf = new ParFormat($s);
		if($onInitialise) call_user_func_array($onInitialise, array($pf->ParAttributes));
		return $pf->Apply($data);
	}
	private function ParseFromString($s)
	{
		$this->FormatItems = array();
		$text = '';
		$inpar = false;
		$openedPar = 0;
		$quotchar = '0';
		for ($i = 0; $i < strlen($s); $i++)
		{
			$cur = $s[$i];
			$next = "\0";
			if ($i + 1 < strlen($s)) $next = $s[$i + 1];
			if(!$inpar)
			{
				if($cur == '{' && $next == '{')
				{
					$i++;
					$text .= $cur;
					continue;
				}
				if ($cur == '{' && $next == '%')
				{
					$i += 1;
					if(!empty($text))
					{
						$pfitem = new ParFormatItem();
						$pfitem->ItemText = $text;
						$pfitem->ItemType = ParFormatType::TextPar;
						$this->FormatItems[] = &$pfitem;
						unset($pfitem);
						$text = '';
					}
					$inpar = true;
					continue;
				}
			}
			else
			{
				if($quotchar == '0' && ($cur == '\'' || $cur == '"'))
				{
					$quotchar = $cur;
				}
				else if ($quotchar != '0' && $cur == $quotchar) $quotchar = '0';
				if($cur == '{' && $quotchar == '0')
				{
					$openedPar++;
				}
				if($cur == '}')
				{
					if($openedPar > 0)
					{
						$openedPar--;
						$text .= $cur;
						continue;
					}
					if (!empty($text))
					{
						$pfitem = new ParFormatItem();
						$pfitem->ItemText = $text;
						$pfitem->ItemType = ParFormatType::FormatPar;
						$this->FormatItems[] = &$pfitem;
						unset($pfitem);
						$text = '';
					}
					$inpar = false;
					continue;
				}
			}
			$text .= $cur;
		}
		if(!empty($text))
		{
			$pfitem = new ParFormatItem();
			$pfitem->ItemText = $text;
			$pfitem->ItemType = ($inpar) ? ParFormatType::FormatPar : ParFormatType::TextPar;
			$this->FormatItems[] = &$pfitem;
			unset($pfitem);
		}

	}
}
