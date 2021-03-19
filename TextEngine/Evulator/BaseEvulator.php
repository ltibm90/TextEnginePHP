<?php

abstract class BaseEvulator
{
	/** @var TextEvulator */
	protected $Evulator;
	protected $prevvalue = null;
	private $localVars;
	private $localVarsId;
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
	protected function EvulatePar(&$pardecoder, &$additionalparams = null)
	{
		if($pardecoder->SurpressError != $this->Evulator->SurpressError)
		{
			$pardecoder->SurpressError = $this->Evulator->SurpressError;
		}
		$index = -1;
		if($additionalparams != null)
		{

			$index = $this->Evulator->LocalVariables->AddArray($additionalparams);
		}
		$er =  $pardecoder->Items->Compute($this->Evulator->GlobalParameters, null, $this->Evulator->LocalVariables);
		if($index >= 0)
		{
			$this->Evulator->LocalVariables->RemoveAt($index);
		}
		return $er->result[0];
		
	}
	protected function EvulateText($text, &$additionalparams = null)
	{
		$pardecoder = new ParDecoder($text);
		$pardecoder->Decode();
		return $this->EvulatePar($pardecoder, $additionalparams);
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
	protected function EvulateAttribute(&$attr, &$additionalparams = null)
	{
		if($attr == null || empty($attr->Value)) return null;
		if($attr->ParData == null)
		{
			$attr->ParData = new ParDecoder($attr->Value);
			$attr->ParData->Decode();
		}
		return $this->EvulatePar($attr->ParData, $additionalparams);
	}
	protected function ConditionSuccess(&$tag, $attr = 'c')
	{
		$pardecoder = null;
		if($tag->NoAttrib)
		{
			if($tag->Value == null) return true;
			$pardecoder = &$tag->ParData;
			if($pardecoder == null)
			{
				$pardecoder = new ParDecoder($tag->Value);
				$pardecoder->Decode();
				$tag->ParData =& $pardecoder;
			}
		}
		else
		{
			$t_attr = &$tag->ElemAttr[$attr];
			if($t_attr == null || $t_attr->Value == null) return true;
			$pardecoder = &$tag->ParData;
			if($pardecoder == null)
			{
				$pardecoder = new ParDecoder($t_attr->Value);
				$pardecoder->Decode();
				$t_attr->ParData =& $pardecoder;
			}		
		}
		$res = $this->EvulatePar($pardecoder);
		return $res;
	}
	protected function SetKeyValue($name, $value)
	{
		$this->Evulator->DefineParameters[$name] = $value;
	}
	protected function UnsetKey($name)
	{
		unset($this->Evulator->DefineParameters[$name]);
	}
	
	protected function CreateLocals()
	{
		if ($this->localVars) return;
		$this->localVars = array();
		$this->localVarsId = $this->Evulator->LocalVariables->AddArray($this->localVars);
	}
	protected function DestroyLocals()
	{
		if (!$this->localVars) return;
		$this->Evulator->LocalVariables->RemoveAt($this->localVarsId);
		$this->localVars = null;
		
	}
	protected function SetLocal($name, &$value)
	{
		$this->localVars[$name] = &$value;
	}
	protected function &GetLocal($name)
	{
		return $this->localVars[$name];
	}
	
}
