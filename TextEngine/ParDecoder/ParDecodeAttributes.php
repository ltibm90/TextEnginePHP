<?php
class ParDecodeAttributes
{
	public $GlobalFunctions;
	public $Flags;
	public $AssignReturnType;
	public $SurpressError;
	public function __construct()
	{
		$this->Iinitialise();
	}
	protected function Iinitialise()
	{
		$this->GlobalFunctions = array('count', 'strlen', 'HTML::');
		$this->AssignReturnType = ParItemAssignReturnType::PIART_RETRUN_BOOL;
		$this->Flags = PardecodeFlags::PDF_AllowMethodCall | PardecodeFlags::PDF_AllowSubMemberAccess | PardecodeFlags::PDF_AllowArrayAccess;
		$this->SurpressError = false;
	}
}
