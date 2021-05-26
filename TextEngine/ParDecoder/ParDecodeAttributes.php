<?php
abstract class ParPropRestrictedType
{
	const PRT_RESTRICT_GET = 1 << 0;
	const PRT_RESTRICT_SET = 1 << 1;
	const PRT_RESTRICT_ALL = ParPropRestrictedType::PRT_RESTRICT_GET | ParPropRestrictedType::PRT_RESTRICT_SET;
}
class ParDecodeAttributes extends PropertyBase
{
	public $GlobalFunctions;
	public $Flags;
	public $AssignReturnType;
	public $SurpressError;
	public $RestrictedProperties;
	public $OnPropertyAccess;
	private $tracing;
	public function& Get_Tracing()
	{
		return $this->tracing;
	}
	public function __construct()
	{
		$this->Initialise();
		$this->tracing = new ParTracer();
	}
	protected function Initialise()
	{
		$this->GlobalFunctions = array('count', 'strlen', 'HTML::');
		$this->AssignReturnType = ParItemAssignReturnType::PIART_RETRUN_BOOL;
		$this->Flags = PardecodeFlags::PDF_AllowMethodCall | PardecodeFlags::PDF_AllowSubMemberAccess | PardecodeFlags::PDF_AllowArrayAccess;
		$this->SurpressError = false;
	}
}
