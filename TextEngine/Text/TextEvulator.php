<?php
class TextEvulator
{
	public $Text;
	/** @var TextElement */
	public $Elements;
	private	$Depth = 0;
	public $LeftTag = "{";
	public $RightTag = "}";
	public $NoParseTag = "noparse";
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
	public $AllowParseCondition = true;
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
	public $CustomDataSingle;
	public function __construct($text = null, $isfile = false)
	{
		$this->TagInfos = new TextElementInfos();
		$this->EvulatorTypes = new EvulatorTypesClass();
		$this->SavedMacrosList = new SavedMacros();
		$this->Elements = new TextElement();
		$this->LocalVariables = new ArrayGroup();
		$this->Elements->ElemName = "#document";
		$this->LocalVariables->AddArray($this->DefineParameters);
		if ($isfile) {
			$this->Text = file_get_contents( $text);
		} else {
			$this->Text = $text;
		}
		$this->InitStockTagOptions();
		$this->InitEvulator();
		$this->InitAmpMaps();
	}
	private function InitStockTagOptions()
	{
		//default flags
		$this->TagInfos["*"]->Flags = TextElementFlags::TEF_NONE;
		$this->TagInfos["elif"]->Flags = TextElementFlags::TEF_AutoClosedTag;
		$this->TagInfos["else"]->Flags = TextElementFlags::TEF_AutoClosedTag;
		$this->TagInfos["return"]->Flags = TextElementFlags::TEF_AutoClosedTag;
		$this->TagInfos["break"]->Flags = TextElementFlags::TEF_AutoClosedTag;
		$this->TagInfos["continue"]->Flags = TextElementFlags::TEF_AutoClosedTag;
		$this->TagInfos["include"]->Flags = TextElementFlags::TEF_AutoClosedTag | TextElementFlags::TEF_ConditionalTag;
		$this->TagInfos["cm"]->Flags = TextElementFlags::TEF_AutoClosedTag;
		$this->TagInfos["set"]->Flags = TextElementFlags::TEF_AutoClosedTag | TextElementFlags::TEF_ConditionalTag;
		$this->TagInfos["unset"]->Flags = TextElementFlags::TEF_AutoClosedTag | TextElementFlags::TEF_ConditionalTag;
		$this->TagInfos["if"]->IsNoAttributedTag = TextElementFlags::TEF_NoAttributedTag | TextElementFlags::TEF_ConditionalTag;
		
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
}