<?php

abstract class BaseEvulator extends PropertyBase
{
	/** @var TextEvulator */
	protected $Evulator;
	protected $prevvalue = null;
	private $localVars;
	private $localVarsId;
	public function __construct(&$evulator)
	{
		$this->Evulator = $evulator;
	}
	/** @param $tag TextElement
	 * @return TextEvulateResult
	 */
	public function& Get_Options()
	{
		return $this->Evulator->EvulatorTypes->Options;
	}
	protected function &CreatePardecode($text, $decode = true)
	{
		$pardecoder = new ParDecoder($text);
		if($decode) $pardecoder->Decode();
		$pardecoder->OnGetAttributes =  function() {return $this->Evulator->ParAttributes;};
		return $pardecoder;
	}
	public abstract function Render(&$tag, &$vars);
	function RenderFinish(&$tag, &$vars, &$latestResult){}
	protected function EvulateTextCustomParams($text, &$parameters)
	{
		$pardecoder = $this->CreatePardecode($text);
		$er = $pardecoder->Items->Compute($parameters);
		return $er->result;
	}
	protected function EvulatePar(&$pardecoder, &$additionalparams = null)
	{
		if($additionalparams == null)
		{
			$er = $pardecoder->Items->Compute($this->Evulator->GlobalParameters, null, $this->Evulator->LocalVariables);
		}
		else
		{
			$multi = new MultiObject();
			$multi->Add($additionalparams);
            $multi->Add($this->Evulator->GlobalParameters);
			$er = $pardecoder->Items->Compute($multi, null, $this->Evulator->LocalVariables);
		}
		return $er->result[0];
		
	}
	protected function EvulateText($text, &$additionalparams = null)
	{
		$pardecoder = $this->CreatePardecode($text);
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
			$attr->ParData = $this->CreatePardecode($attr->Value);
		}
		return $this->EvulatePar($attr->ParData, $additionalparams);
	}
	protected function ConditionSuccess(&$tag, $attr = '*', &$vars = null)
	{
		$pardecoder = null;
		if(($attr == null || $attr == '*') && $tag->NoAttrib)
		{
			if($tag->Value === null) return true;

			$pardecoder = &$tag->ParData;
			if($pardecoder == null)
			{
				$pardecoder = $this->CreatePardecode($tag->Value);
				$tag->ParData =& $pardecoder;
			}
		}
		else
		{
			if($attr == '*') $attr = 'c';
			$t_attr = &$tag->ElemAttr[$attr];
			if($t_attr == null || $t_attr->Value == null) return true;
			$pardecoder = &$tag->ParData;
			if($pardecoder == null)
			{
				$pardecoder = $this->CreatePardecode($t_attr->Value);
				$t_attr->ParData =& $pardecoder;
			}		
		}
		$res = $this->EvulatePar($pardecoder, $vars);
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
	protected function SetLocalWR($name, $value)
	{
		$this->localVars[$name] = $value;
	}
	protected function &GetLocal($name)
	{
		return $this->localVars[$name];
	}
	
}
