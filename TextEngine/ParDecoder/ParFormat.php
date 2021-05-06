<?php
class ParFormat extends PropertyBase
{
	function __construct($text = "")
	{
		$this->Text = $text;
		$this->Flags = PardecodeFlags::PDF_AllowArrayAccess | PardecodeFlags::PDF_AllowMethodCall | PardecodeFlags::PDF_AllowSubMemberAccess;
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
					$item->ParData->OnGetFlags = function() {return $this->Flags;};
					$item->ParData->Decode();
					$item->ParData->SurpressError = $this->SurpressError;

				}
				$cr = $item->ParData->Items->Compute($data);
				if($cr && isset($cr->result[0]))
				{

					$text .=  $cr->result[0];
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
	private function ParseFromString($s)
	{
		$this->FormatItems = array();
		$text = '';
		$inpar = false;
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
				if($cur == '{')
				{
					if ($this->SurpressError)
					{
						continue;
					}
					throw new Exception("Syntax Error: Unexpected {");
				}
				if($cur == '}')
				{
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
