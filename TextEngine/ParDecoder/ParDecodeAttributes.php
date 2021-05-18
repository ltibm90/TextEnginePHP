<?php
class ParDecodeAttributes
{
	public $GlobalFunctions;
	public $Flags;
	public $AssignReturnType;
	public $SurpressError;
	public function __construct()
	{
		$this->Initialise();
	}
	protected function Initialise()
	{
		$this->GlobalFunctions = array('count', 'strlen', 'HTML::');
		$this->AssignReturnType = ParItemAssignReturnType::PIART_RETRUN_BOOL;
		$this->Flags = PardecodeFlags::PDF_AllowMethodCall | PardecodeFlags::PDF_AllowSubMemberAccess | PardecodeFlags::PDF_AllowArrayAccess;
		$this->SurpressError = false;
	}
}
