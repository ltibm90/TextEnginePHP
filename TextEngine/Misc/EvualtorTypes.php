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
        if (is_null($offset)) {
            return;
        } else {

            $this->innerArray[$offset] = $value;
        }
    }
    public function offsetExists($offset) {
        return isset($this->innerArray[$offset]);
    }

    public function offsetUnset($offset) {
        unset($this->innerArray[$offset]);
    }

    public function offsetGet($offset) {
        return isset($this->innerArray[$offset]) ? $this->innerArray[$offset] : null;
    }
}
