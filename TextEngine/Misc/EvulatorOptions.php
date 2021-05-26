<?php
class EvulatorOptions extends PropertyBase
{
	public $Max_For_Loop = 0;
	public $Max_DoWhile_Loop = 0;
	public $Max_Repeat_Loop = 0;
	public $Max_ForEach_Loop = 0;
	private $p_otherOptions;
	public function& Get_OtherOptions()
	{
		return $this->p_otherOptions;
	}
	public function& Set_OtherOptions(&$value)
	{
		$this->p_otherOptions = &$value;;
	}
    public function __construct() {
		$this->p_otherOptions = array();
    }
	public function& GetOption($name, $defaultV = null)
	{
		if(isset($this->OtherOptions[$name])) return $this->OtherOptions[$name];
		return $defaultV;
	}
	public function SetOptions($name, &$value)
	{
		$this->OtherOptions[$name] = &$value;
	}
}
