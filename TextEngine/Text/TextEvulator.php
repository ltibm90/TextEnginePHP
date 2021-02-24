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
		$this->TagInfos["elif"]->IsAutoClosedTag = true;
		$this->TagInfos["else"]->IsAutoClosedTag = true;
		$this->TagInfos["return"]->IsAutoClosedTag = true;
		$this->TagInfos["break"]->IsAutoClosedTag = true;
		$this->TagInfos["continue"]->IsAutoClosedTag = true;
		$this->TagInfos["include"]->IsAutoClosedTag = true;
		$this->TagInfos["cm"]->IsAutoClosedTag = true;
		$this->TagInfos["set"]->IsAutoClosedTag = true;
		$this->TagInfos["unset"]->IsAutoClosedTag = true;
		$this->TagInfos["if"]->IsNoAttributedTag = true;
		$this->TagInfos["if"]->IsConditionalTag = true;
		$this->TagInfos["include"]->IsConditionalTag = true;
		$this->TagInfos["set"]->IsConditionalTag = true;
		$this->TagInfos["set"]->IsConditionalTag = true;
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
		if (!$this->AllowParseCondition || !$this->IsParseMode || (!$this->TagInfos->HasTagInfo($element->ElemName) || !$this->TagInfos[$element->ElemName]->IsConditionalTag)) return;
		$indis = $element->Index();
		$element->Parent->EvulateValue($indis, $indis + 1);
	}
}