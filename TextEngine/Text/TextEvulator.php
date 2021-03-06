<?php
class TextEvulator extends PropertyBase
{
	private $p_SurpressError;
	public function Get_SurpressError()
	{
		return $this->p_SurpressError;
	}
	public function Set_SurpressError($value)
	{
		$this->ParAttributes->SurpressError = $value;
	}
	private $text;
	public function Get_Text()
	{
		return $this->text;
	}
	public function Set_Text($value)
	{
		$this->text = $value;
		$this->NeedParse = true;
	}
	public $Text;
	/** @var TextElement */
	public $Elements;
	private $NeedParse;
	private	$Depth = 0;
	public $LeftTag = "{";
	public $RightTag = "}";
	public $NoParseEnabled = true;
	public $ParamChar = '%';
	public $Aliasses = array();
	public $GlobalParameters = array();
	/** @var ArrayGroup */
	public $LocalVariables;
	public $ParamNoAttrib = false;
	public $DecodeAmpCode = false;
	public $AmpMaps = array();
	public $SupportCDATA = false;
	public $SupportExclamationTag = false;
	public $AllowXMLTag = true;
	public $TrimStartEnd = false;
	public $TrimMultipleSpaces = true;
	public $AllowParseCondition = false;
	public $ThrowExceptionIFPrevIsNull = true;
	/** @var array */
	public $DefineParameters = array();
	/** @var SavedMacros */
	public $SavedMacrosList;
	/** @var TextElementInfos */
	public $TagInfos;
	/** @var EvulatorTypesClass */
	public $EvulatorTypes;
	/** @var bool */
	public $IsParseMode;
	public $CustomDataDictionary = array();
	public $CharMap = array();
	public $CustomDataSingle;
	public $AllowCharMap;
	public $EvulatorHandler;
	public $SpecialCharOption;
	public $IntertwinedBracketsState;
	public $ParAttributes;
	public $ReturnEmptyIfTextEvulatorIsNull;
	public function &GetHandler()
	{
		$handler = null;
		if($this->EvulatorHandler)
		{
			if(!is_callable($this->EvulatorHandler)) return $this->EvulatorHandler;
			$handler = &call_user_func($this->EvulatorHandler);
		}
		return $handler;
	}
	public function ApplyCommandLineByLine()
	{
		$this->EvulatorTypes->Text = "TextTagCommandEvulator";
		$this->EvulatorTypes->Param = null;
		$this->ParAttributes->Flags |= PardecodeFlags::PDF_AllowAssigment;
	}
	public function __construct($text = null, $isfile = false)
	{
		$this->ParAttributes = new ParDecodeAttributes();
		$this->IntertwinedBracketsState = IntertwinedBracketsStateType::IBST_ALLOW_NOATTRIBUTED_AND_PARAM;
		$this->TagInfos = new TextElementInfos();
		$this->EvulatorTypes = new EvulatorTypesClass();
		$this->SavedMacrosList = new SavedMacros();
		$this->Elements = new TextElement();
		$this->LocalVariables = new ArrayGroup();
		$this->Elements->ElemName = "#document";
		$this->LocalVariables->AddArray($this->DefineParameters);
		$this->SpecialCharOption = SpecialCharType::SCT_AllowedAll;
		if ($isfile) {
			$this->Text = file_get_contents( $text);
		} else {
			$this->Text = $text;
		}
		$this->InitAll();
		if($isfile)
		{
			$this->SetDir(dirname($text));
		}
		$this->NeedParse = true;
	}
	public function InitAll()
	{
		$this->ClearAllInfos();
		$this->InitStockTagOptions();
		$this->InitEvulator();
		$this->InitAmpMaps();
	}
	private function InitStockTagOptions()
	{
		//default flags
		$this->TagInfos->Default->Flags = TextElementFlags::TEF_NONE;
		$this->TagInfos["elif"]->Flags = TextElementFlags::TEF_AutoClosedTag | TextElementFlags::TEF_NoAttributedTag;
		$this->TagInfos["else"]->Flags = TextElementFlags::TEF_AutoClosedTag;
		$this->TagInfos["return"]->Flags = TextElementFlags::TEF_AutoClosedTag;
		$this->TagInfos["break"]->Flags = TextElementFlags::TEF_AutoClosedTag;
		$this->TagInfos["continue"]->Flags = TextElementFlags::TEF_AutoClosedTag;
		$this->TagInfos["include"]->Flags = TextElementFlags::TEF_AutoClosedTag | TextElementFlags::TEF_ConditionalTag;
		$this->TagInfos["cm"]->Flags = TextElementFlags::TEF_AutoClosedTag;
		$this->TagInfos["set"]->Flags = TextElementFlags::TEF_AutoClosedTag | TextElementFlags::TEF_ConditionalTag;
		$this->TagInfos["unset"]->Flags = TextElementFlags::TEF_AutoClosedTag | TextElementFlags::TEF_ConditionalTag;
		$this->TagInfos["if"]->Flags = TextElementFlags::TEF_NoAttributedTag | TextElementFlags::TEF_ConditionalTag;
		$this->TagInfos["while"]->Flags = TextElementFlags::TEF_NoAttributedTag;
		$this->TagInfos["do"]->Flags = TextElementFlags::TEF_NoAttributedTag;
		$this->TagInfos["text"]->Flags = TextElementFlags::TEF_NoParse_AllowParam;
		
	}
	private function InitEvulator()
	{
		$this->EvulatorTypes->Param = "ParamEvulator";
		$this->EvulatorTypes->GeneralType = "GeneralEvulator";
		$this->EvulatorTypes->Text = "TexttagEvulator";
		$this->EvulatorTypes["if"] = "IfEvulator";
		$this->EvulatorTypes["for"] = "ForEvulator";
		$this->EvulatorTypes["foreach"] = "ForeachEvulator";
		$this->EvulatorTypes["switch"] = "SwitchEvulator";
		$this->EvulatorTypes["return"] = "ReturnEvulator";
		$this->EvulatorTypes["break"] = "BreakEvulator";
		$this->EvulatorTypes["continue"] = "ContinueEvulator";
		$this->EvulatorTypes["cm"] = "CMEvulator";
		$this->EvulatorTypes["macro"] = "MacroEvulator";
		$this->EvulatorTypes["noprint"] = "NoPrintEvulator";
		$this->EvulatorTypes["repeat"] = "RepeatEvulator";
		$this->EvulatorTypes["include"] = "IncludeEvulator";
		$this->EvulatorTypes["set"] = "SetEvulator";
		$this->EvulatorTypes["unset"] = "UnsetEvulator";
		$this->EvulatorTypes["while"] = "WhileEvulator";
		$this->EvulatorTypes["do"] = "DoEvulator";
		$this->EvulatorTypes["text"] = "TextParamEvulator";
	}
	private function InitAmpMaps()
	{
		$this->AmpMaps['nbsp'] = ' ';
		$this->AmpMaps['amp'] = '&';
		$this->AmpMaps['quot'] = '"';
		$this->AmpMaps['lt'] = '<';
		$this->AmpMaps['gt'] = '>';
	}
	public function Parse()
	{
		$parser = new TextEvulatorParser($this);
		$parser->Parse($this->Elements, $this->Text);
		$this->NeedParse = false;
	}
	public function ParseText($baselement, $text)
	{
		$parser = new TextEvulatorParser($this);
		$parser->Parse($baselement, $text);
	}
	public function OnTagClosed($element)
	{
		if (!$this->AllowParseCondition || !$this->IsParseMode || (!($element->GetTagFlags() & TextElementFlags::TEF_ConditionalTag) != 0)) return;
		$indis = $element->Index();
		$element->Parent->EvulateValue($indis, $indis + 1);
	}
	public function SetDir($dir)
	{
		$this->LocalVariables->SetValue("_DIR_", $dir);
	}
	public function ClearAllInfos()
	{
		$this->TagInfos->Clear();
		$this->EvulatorTypes->Clear();
		$this->AmpMaps = [];
		$this->EvulatorTypes->Param = null;
		$this->EvulatorTypes->Text = null;
		$this->EvulatorTypes->GeneralType = null;
	}
	public function ClearElements()
	{
		$this->Elements->SubElements->Clear();
		$this->Elements->ElemName = "#document";
		$this->Elements->ElementType = TextElementType::Document;
	}
	public function EvulateValue(&$vars = null, $autoparse = true)
	{
		if ($autoparse && $this->NeedParse) $this->Parse();
		return $this->Elements->EvulateValue(0, 0, $vars);
	}
}