<?php

abstract class BaseEvulator
{
	/** @var TextEvulator */
	protected $Evulator;
	protected $prevvalue = null;
	public function __construct(&$evulator)
	{
		$this->Evulator =& $evulator;
	}
	/** @param $tag TextElement
	 * @return TextEvulateResult
	 */
	public abstract function Render(&$tag, &$vars);
	function RenderFinish(&$tag, &$vars, &$latestResult){}
	protected function EvulateTextCustomParams($text, &$parameters)
	{
		$pardecoder = new ParDecoder($text);
		$pardecoder->Decode();
		$er = $pardecoder->Items->Compute($parameters);
		return $er->result;
	}
	protected function EvulateText($text, &$additionalparams = null)
	{
		if($additionalparams)
		{
			return $this->EvulateTextCustomParams($text, $additionalparams);
		}

		$pardecoder = new ParDecoder($text);
		$pardecoder->Decode();
		$er =  $pardecoder->Items->Compute($this->Evulator->GlobalParameters, null, $this->Evulator->LocalVariables);
		return $er->result[0];
	}
	protected function StorePreviousValue($varname)
	{
		//deprecated
		if(key_exists($varname, $this->Evulator->LocalVariables))
		{
			$this->prevvalue[$varname] = &$this->Evulator->LocalVariables[$varname];
		}
	}
	protected function DeleteOrRestoreAllPrevValues()
	{
		//deprecated
		if(!$this->prevvalue) return;
		foreach ($this->prevvalue as $index => $item) {
			$this->RemoveVar($index);
		}
	}
	protected function SetVar($varname, &$varvalue)
	{
		//deprecated
		$this->Evulator->LocalVariables[$varname] = $varvalue;
	}
	protected function RemoveVar($varname)
	{
		//deprecated
		$prev = array_value($varname, $this->prevvalue);
		if($prev !== null)
		{
			$this->SetVar($varname, $prev);
			unset($this->prevvalue[$varname]);
			return;
		}
		unset($this->Evulator->localVariables[$varname]);
	}
	protected function ConditionSuccess(&$tag, $attr = 'c')
	{
		$condition = ($tag->NoAttrib) ? $tag->Value : $tag->GetAttribute($attr);
		if($condition === null) return true;
		$res = $this->EvulateText($condition);
		return $res;
	}
}
