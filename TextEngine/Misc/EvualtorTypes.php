<?php
class EvulatorTypesClass implements ArrayAccess
{
	public $Param;
	public $GeneralType;
	public $Text;

    private $innerArray = array();

    public function __construct() {
		$this->Param = "ParamEvulator";
		$this->GeneralType = "GeneralEvulator";
		$this->Text = "TexttagEvulator";
    }

    public function offsetSet($offset, $value) {
        if (is_null($offset) || empty($offset)) {
            return;
        } else {

            $this->innerArray[$offset] = $value;
        }
    }
    public function offsetExists($offset) {
        return !empty($offset) && isset($this->innerArray[$offset]);
    }

    public function offsetUnset($offset) {
		if(empty($offset)) return;
        unset($this->innerArray[$offset]);
    }

    public function offsetGet($offset) {
		if(empty($offset)) return null;
        return isset($this->innerArray[$offset]) ? $this->innerArray[$offset] : null;
    }
}
