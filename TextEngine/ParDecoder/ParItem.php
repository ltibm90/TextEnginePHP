<?php
class ParItem extends InnerItem
{
	public function __construct()
	{
		
	}
	public $BaseDecoder;
	public $ParName;
	/** @var ParItem */
	public $Parent;

	/** @var InnerItem[] */
	public $innerItems;
	public $is_operator = false;

	public $value = null;

	public function IsObject()
	{
		return $this->ParName == '{';
	}
	public  function  IsParItem()
	{
		return true;
	}
	public function IsArray()
	{
		return $this->ParName == '[';
	}
	public function& Get_Attributes()
	{
		return $this->BaseDecoder->Attributes;
	}
	public function GetParentUntil($name)
	{
		$parent = $this->Parent;
		while ($parent != null && $parent->ParName == $name)
		{
			$parent = $parent->Parent;
		}
		return $parent;
	}
	/** @param $sender InnerItem
	 *	@return ComputeResult
	 */
	function Compute(&$vars = null, $sender = null, &$localvars = null)
	{
		$cr = new ComputeResult();
		$lastvalue = null;
		$xoperator = null;
		$previtem = null;
		$waititem = null;
		$waititem2 = null;
		$waitop = "";
		$waitvalue = null;
		$waitop2 = "";
		$waitvalue2 = null;
		$waitkey = "";
		$unlemused = false;
		$stopdoubledot = false;
		$innercount  = 0;
		$minuscount  = 0;
		$assigment = "";
        $lastPropObject = null;
        $waitAssigmentObject = null;
        $totalOp = 0;
		$propertyStr = '';
		if($this->innerItems && is_array($this->innerItems))
		{
			$innercount = count($this->innerItems);
		}

		if($this->IsObject())
		{
			$cr->result = new stdClass();
		}
		elseif ($this->IsArray())
		{
			$cr->result = array();
		}
		for ($i = 0; $i < $innercount; $i++)
		{
			unset($currentitemvalue);
			$currentitemvalue = null;
			/* @var $current InnerItem */
			unset($current);
			$current = &$this->innerItems[$i];
			if($stopdoubledot)
			{
				if($current->is_operator && $current->value == ":")
				{
					break;
				}
			}
			/* @var $next InnerItem */
			$next = null;
			$nextop = "";			
			if ($i + 1 < $innercount) $next = $this->innerItems[$i + 1];
			
                if ($next != null && $next->is_operator)
				{
					$nextop = $next->value;
				}

                if ($current->IsParItem())
				{
				
                    $subresult = $current->Compute($vars, $this, $localvars);
                    $prevvalue = "";
                    $previsvar = false;
                    if($previtem != null && !$previtem->is_operator && $previtem->value != null)
					{
						$previsvar = $previtem->type == InnerItem::TYPE_VARIABLE;
						$prevvalue = $previtem->value;

					}
					unset($varnew);
                    $varnew = null;
					$checkglobal = true;
                    if ($lastvalue != null)
					{
						$checkglobal = false;
						$varnew = &$lastvalue;
					}
					else
					{
						$varnew = &$vars;
					}
					
                    if ($prevvalue != "")
					{
						if ($current->ParName == "(")
						{				
							unset($lastPropObject);
							$lastPropObject = null;
							if($this->BaseDecoder->Attributes->Flags & PardecodeFlags::PDF_AllowMethodCall)
							{
								unset($iscalled);
								$iscalled = false;
								unset($currentitemvalue);
								if (strlen($propertyStr)== 0) $propertyStr = $prevvalue;
                                else $propertyStr .= '.' . $prevvalue;
                                $allowget = $this->AllowAccessProperty($propertyStr, PropType::Method);
								if($allowget && $this->BaseDecoder && $this->BaseDecoder->Attributes->SurpressError)
								{
									try 
									{
										$currentitemvalue = ComputeActions::CallMethod($prevvalue, $subresult->result, $varnew, $iscalled, $localvars, $this->BaseDecoder, $checkglobal);	
									} 
									catch (Exception $e) 
									{
										$currentitemvalue = null;
									}
								}
								else if($allowget)
								{
									$currentitemvalue = ComputeActions::CallMethod($prevvalue, $subresult->result, $varnew, $iscalled, $localvars, $this->BaseDecoder, $checkglobal);	
								}
							}
							else
							{
								//$currentitemvalue = null;
							}
							if($allowget) $this->AddToTrace($propertyStr, PropType::Method, $currentitemvalue, $iscalled);

						}
						else if($current->ParName == "[")
						{
							if($this->BaseDecoder->Attributes->Flags & PardecodeFlags::PDF_AllowArrayAccess)
							{
								if (strlen($propertyStr)== 0) $propertyStr = $prevvalue;
                                else $propertyStr .= '.' . $prevvalue;
								$allowget = $this->AllowAccessProperty($propertyStr, PropType::Indis);
								unset($lastPropObject);
								if($allowget) $lastPropObject = ComputeActions::GetPropEx($prevvalue, $varnew, $localvars);
								else $lastPropObject = null;
								unset($prop);
								if($lastPropObject)	$prop = &$lastPropObject->Value;
								else $prop = null;
								if (is_array($prop) || is_string($prop))
								{
									
									$indis = $subresult->result[0];
									unset($currentitemvalue);
									if($indis !== null)
									{
										 $currentitemvalue = $prop[$indis];
									}
									else
									{
										
										$currentitemvalue = null;
									}
									if(is_array($prop))
									{
										$lastPropObject->PropType = PropType::Indis;
										$lastPropObject->Indis = $indis;
										$lastPropObject->PropertyInfo = &$prop; 
										$lastPropObject->Value = &$currentitemvalue;
									}
									else
									{
										unset($lastPropObject);
										$lastPropObject = null;
									}



								   
								}
								else if (is_object($prop))
								{
									
								}
								if($allowget) $this->AddToTrace($propertyStr, PropType::Indis, $currentitemvalue, $lastPropObject != null && $lastPropObject->PropType != PropType::Empty);
							}
							else
							{
								//$currentitemvalue = null;
							}
		


						}
					}
					else
					{
						if($current->ParName == "(")
						{
							unset($currentitemvalue);
							$currentitemvalue = &$subresult->result[0];
						}
						else if($current->ParName == "[")
						{
							unset($currentitemvalue);
							$currentitemvalue = &$subresult->result;
						}
						else if($current->ParName == "{")
						{
							unset($currentitemvalue);
							$currentitemvalue = &$subresult->result;
						}
					}

                }
				else
				{
					if(!$current->is_operator && $current->type == InnerItem::TYPE_VARIABLE &&  $next != null && $next->IsParItem())
					{
						unset($currentitemvalue);
						$currentitemvalue = null;
					}
					else
					{
						if($previtem != null && $previtem->is_operator)
                        {
                            if($current->value == "+")
                            {
                                continue;
                            }
                            else if ($current->value == "-")
                            {
                                $minuscount++;
                                continue;
                            }


                        }
						unset($currentitemvalue);
						$currentitemvalue = $current->value;


					}
					if ($current->type == InnerItem::TYPE_VARIABLE && ($next == null || !$next->IsParItem()) && ($xoperator == null || $xoperator->value != ".") )
					{
						
						if ($currentitemvalue === null || $currentitemvalue == "null")
						{
							unset($currentitemvalue);
							$currentitemvalue = null;
						}
						else if ($currentitemvalue == "false")
						{
							unset($currentitemvalue);
							$currentitemvalue = false;
						}
						else if ($currentitemvalue== "true")
						{
							unset($currentitemvalue);
							$currentitemvalue = true;
						}
						else if (!$this->IsObject())
						{
							unset($lastPropObject);
							$propertyStr = $current->value;
							$allowget = $this->AllowAccessProperty($propertyStr, PropType::Property);
							if($allowget) $lastPropObject = ComputeActions::GetPropValue($current, $vars, $localvars);
							else $lastPropObject = null;
							unset($currentitemvalue);
							if(!$lastPropObject) $currentitemvalue = null;
							else $currentitemvalue = &$lastPropObject->Value;
							if($allowget) $this->AddToTrace($propertyStr, PropType::Property, $currentitemvalue, $lastPropObject != null && $lastPropObject->PropType != PropType::Empty);
						}
				
					}
				}
                if($unlemused)
				{

					unset($currentitemvalue);
					$currentitemvalue = empty($currentitemvalue);
					$unlemused = false;
				}		

                if ($current->is_operator)
				{
					$totalOp++;
					if($current->value == "!")
					{
						$unlemused = !$unlemused;
						unset($previtem);
						$previtem = &$current;
						continue;
					}
					if (($this->IsParItem() && $current->value == ",") || ($this->IsArray() && $current->value  == "=>" && ($waitvalue === null || $waitvalue == "")) || ($this->IsObject() && $current->value  == ":" && ($waitvalue === null || $waitvalue == "") ))
					{
						if ($waitop2 != "")
						{
							if($minuscount % 2 == 1) $lastvalue = ComputeActions::OperatorResult($lastvalue, -1, "*");
							$lastvalue = ComputeActions::OperatorResult($waitvalue2, $lastvalue, $waitop2);
							$waitvalue2 = null;
							$waitop2 = "";
							$minuscount = 0;
						}
						if ($waitop != "")
						{
							if($minuscount % 2 == 1) $lastvalue = ComputeActions::OperatorResult($lastvalue, -1, "*");
							$lastvalue = ComputeActions::OperatorResult($waitvalue, $lastvalue, $waitop);
							$waitvalue = null;
							$waitop = "";
							$minuscount = 0;
						}
						if ($current->value == ",")
						{
							if($this->IsObject())
							{
								$cr->result->$waitkey = $lastvalue;
							}
							else if(empty($waitkey) || !$this->IsArray())
							{
								$cr->result[] = $lastvalue;
							}
							else if($this->IsArray())
							{
								$cr->result[$waitkey] = $lastvalue;
							}
							$waitkey = "";
						}
						else
						{
							$waitkey = $lastvalue;
						}
						unset($lastvalue);
						$lastvalue = null;
						unset($xoperator);
						$xoperator = null;
						unset($previtem);
						$previtem = &$current;
						continue;
					}
					$opstr = $current->value;
					if ($waitAssigmentObject == null && ($opstr == "=" || $opstr == "+=" || $opstr == "-=" || $opstr == "*=" || $opstr == "/=" || $opstr == "^=" || $opstr == "&=" || $opstr == "|=" || $opstr == "<<=" || $opstr == ">>="|| $opstr == "%="))
					{
						if ($totalOp <= 1 && ($this->BaseDecoder->Attributes->Flags & PardecodeFlags::PDF_AllowAssigment) != 0)
						{
							unset($waitAssigmentObject);
							$waitAssigmentObject = &$lastPropObject;
							if($waitAssigmentObject) $waitAssigmentObject->FullName = $propertyStr;
							$propertyStr = '';
							$assigment = $opstr;
							unset($xoperator);
							unset($previtem);
							$xoperator = null;
							$previtem = null;
						}
						else
						{
							unset($xoperator);
							unset($previtem);
							$xoperator = null;
							$previtem = null;
						}
						continue;
					}
                    if ($opstr == "||" || /*$opstr == "|" || */ $opstr == "or" || $opstr == "&&" || /* $opstr == "&" ||*/ $opstr == "and" || $opstr == "?")
					{
						if ($waitop2 != "")
						{
							if($minuscount % 2 == 1) $lastvalue = ComputeActions::OperatorResult($lastvalue, -1, "*");
							$lastvalue = ComputeActions::OperatorResult($waitvalue2, $lastvalue, $waitop2);
							$waitvalue2 = null;
							$waitop2 = "";
							$minuscount = 0;
						}
						if ($waitop != "")
						{
							if($minuscount % 2 == 1) $lastvalue = ComputeActions::OperatorResult($lastvalue, -1, "*");
							$lastvalue = ComputeActions::OperatorResult($waitvalue, $lastvalue, $waitop);
							$waitvalue = null;
							$waitop = "";
							$minuscount = 0;
						}

						$state =!empty($lastvalue);
						unset($xoperator);
						$xoperator = null;
                        if ($opstr == "?")
						{
							if ($state)
							{
								$stopdoubledot = true;
							}
							else
							{
								for ($j = $i + 1; $j < $innercount; $j++)
                                {
									$item = $this->innerItems[$j];
									if ($item->is_operator && $item->value == ":")
									{
										$i = $j;
										break;
									}
								}
                            }
							unset($lastvalue);
							$lastvalue = null;
							unset($previtem);
							$previtem = &$current;
							continue;


						}
                        if ($opstr == "||" || /*$opstr == "|" ||*/ $opstr == "or")
						{
							if ($state)
							{			
								unset($lastvalue);
								$lastvalue = true;
								/*if ($opstr != "|")
								{*/
									$cr->result[] = true;
									return $cr;
								//}
							}
							else
							{
								unset($lastvalue);
								$lastvalue = false;
							}
						}
						else
						{
							if (!$state)
							{
								$lastvalue = false;
								/*if ($opstr != "&")
								{*/
									$cr->result[] = false;
									return $cr;
								//}
							}
							else
							{
								unset($lastvalue);
								$lastvalue = true;
							}
						}
						unset($xoperator);
						$xoperator = null;
                        //$xoperator = &$current;
                    }
					else
					{
						unset($xoperator);
						$xoperator = &$current;
					}
					unset($previtem);
					$previtem = &$current;
                    continue;
                }
				else
				{

					if ($xoperator != null)
					{
						if ( ComputeActions::PriotiryStopContains($xoperator->value))
						{

							if ($waitop2 != "")
							{
								if($minuscount % 2 == 1) $lastvalue = ComputeActions::OperatorResult($lastvalue, -1, "*");
								$lastvalue = ComputeActions::OperatorResult($waitvalue2, $lastvalue, $waitop2);
								$waitvalue2 = null;
								$waitop2 = "";
								$minuscount = 0;
							}
							if ($waitop != "")
							{
								if($minuscount % 2 == 1) $lastvalue = ComputeActions::OperatorResult($lastvalue, -1, "*");
								$lastvalue = ComputeActions::OperatorResult($waitvalue, $lastvalue, $waitop);
								$waitvalue = null;
								$waitop = "";
								$minuscount = 0;
							}
						}

						if ($next != null && $next->IsParItem())
						{
						
							if ($xoperator->value == ".")
							{
								if($this->BaseDecoder->Attributes->Flags & PardecodeFlags::PDF_AllowSubMemberAccess)
								{
									if($currentitemvalue)
									{
										$propertyStr .= '.' . $currentitemvalue;
										unset($lastPropObject);
										
										if($this->AllowAccessProperty($propertyStr, PropType::Property))
										{
											$lastPropObject = ComputeActions::GetProp($currentitemvalue, $lastvalue);
											unset($lastvalue);
											$lastvalue = &$lastPropObject->Value;
											$this->AddToTrace($propertyStr, PropType::Property, $lastvalue, $lastPropObject != null && $lastPropObject->PropType != PropType::Empty);
										}
										else
										{
											$lastPropObject = null;
                                            $lastvalue = null;
										}

	
									}
								}
								else
								{
								}


							}
							else
							{
								if($waitop == "")
								{
									$waitop = $xoperator->value;
									unset($waititem);
									$waititem = &$current;
									$waitvalue = $lastvalue;
								}
								else if($waitop2 == "")
								{
									$waitop2 = $xoperator->value;
									unset($waititem2);
									$waititem2 = &$current;
									$waitvalue2 = $lastvalue;
								}
								unset($lastvalue);
								$lastvalue = null;
								
							}
							unset($xoperator);
							$xoperator = null;
							unset($previtem);
							$previtem = &$current;
							continue;
						}
						if ($xoperator->value == ".")
						{
							$totalOp--;
							if($this->BaseDecoder->Attributes->Flags & PardecodeFlags::PDF_AllowSubMemberAccess)
							{
								$propertyStr .= '.' . $currentitemvalue;
								unset($lastPropObject);

								if($this->AllowAccessProperty($propertyStr, PropType::Property))
								{
									$lastPropObject = ComputeActions::GetProp($currentitemvalue, $lastvalue);
									unset($lastvalue);
									$lastvalue = &$lastPropObject->Value;
									$this->AddToTrace($propertyStr, PropType::Property, $lastvalue, $lastPropObject != null && $lastPropObject->PropType != PropType::Empty);
								}
								else
								{
									$lastPropObject = null;
									unset($lastvalue);
                                    $lastvalue = null;
								}
							}
							else
							{
								//$lastvalue = null;
							}
						}
						else if ($nextop != "." && (($xoperator->value != "+" && $xoperator->value != "-") || $nextop == "" || (ComputeActions::PriotiryStopContains($nextop))))
						{
							if($minuscount % 2 == 1) $currentitemvalue  = ComputeActions::OperatorResult($currentitemvalue , -1, "*");
							$opresult = ComputeActions::OperatorResult($lastvalue, $currentitemvalue, $xoperator->value);
							unset($lastvalue);
							$lastvalue = $opresult;
							$minuscount = 0;
						}
						else
						{
							if($waitop == "")
							{
								$waitop = $xoperator->value;
								unset($waititem);
								$waititem = &$current;
								$waitvalue = $lastvalue;
								unset($lastvalue);
								$lastvalue = &$currentitemvalue;
							}
							else if($waitop2 == "")
							{
								$waitop2 = $xoperator->value;
								unset($waititem2);
								$waititem2 = &$current;
								$waitvalue2 = $lastvalue;
								unset($lastvalue);
								$lastvalue = &$currentitemvalue;
							}
							unset($previtem);
							$previtem = &$current;
							continue;
						}
					}
					else
					{							
						unset($lastvalue);
						$lastvalue = &$currentitemvalue;
					}


				}
				unset($previtem);
                $previtem = &$current;
            }
			if ($waitop2 != "")
			{
				if($minuscount % 2 == 1) $lastvalue = ComputeActions::OperatorResult($lastvalue, -1, "*");
				$lastvalue = ComputeActions::OperatorResult($waitvalue2, $lastvalue, $waitop2);
				$waitvalue2 = null;
				$waitop2 = "";
				$minuscount = 0;
			}
			if ($waitop != "")
			{
				if($minuscount % 2 == 1) $lastvalue = ComputeActions::OperatorResult($lastvalue, -1, "*");
				$lastvalue = ComputeActions::OperatorResult($waitvalue, $lastvalue, $waitop);
				$waitvalue = null;
				$waitop = "";
				$minuscount = 0;
			}
			
            if ($waitAssigmentObject != null && $this->AllowAccessProperty($waitAssigmentObject->FullName, $waitAssigmentObject->PropType, true))
            {
                $assignResult = null;
                if($waitAssigmentObject->PropType != PropType::Empty && $waitAssigmentObject->PropertyInfo != null)
                {
                    try
                    {
                        $assignResult = ComputeActions::AssignObjectValue($waitAssigmentObject, $assigment, $lastvalue);
                    }
                    catch(Exception $e) 
                    {
					
                    }
                }
                switch ($this->BaseDecoder->Attributes->AssignReturnType)
                {
                    case ParItemAssignReturnType::PIART_RETURN_NULL:
						unset($lastvalue);
                        $lastvalue = null;
                        break;
                    case ParItemAssignReturnType::PIART_RETRUN_BOOL:
						unset($lastvalue);
                        $lastvalue = !empty($assignResult);
                        break;
                    case ParItemAssignReturnType::PIART_RETURN_ASSIGNVALUE_OR_NULL:
						unset($lastvalue);
                        if (!$assignResult) $lastvalue = null;
                        else $lastvalue = $assignResult->AssignedValue;
                        break;
                    case ParItemAssignReturnType::PIART_RETURN_ASSIGN_VALUE:
                        if ($assignResult) 
						{
							unset($lastvalue);
							$lastvalue = $assignResult->AssignedValue;
						}
                        break;
                }
				 if($assignResult) $this->AddToTrace($waitAssigmentObject->FullName, $waitAssigmentObject->PropType, $assignResult->AssignedValue, true, true);
            }			
			
			if($this->IsObject())
			{
				$cr->result->$waitkey = $lastvalue;
			}
			else if(empty($waitkey) || !$this->IsArray())
			{
				$cr->result[] = $lastvalue;
			}
			else if($this->IsArray())
			{
				$cr->result[$waitkey] = $lastvalue;
			}
			else
			{
				$cr->result[] = $lastvalue;
			}
            return $cr;
	}
	private function AllowAccessProperty($propStr, $type, $isassign = false)
	{
		if ($this->Attributes->RestrictedProperties != null && count($this->Attributes->RestrictedProperties) > 0)
		{

			if(isset($this->Attributes->RestrictedProperties[$propStr]))
			{
				$prt = $this->Attributes->RestrictedProperties[$propStr];
				if (($isassign && ($prt & ParPropRestrictedType::PRT_RESTRICT_SET) != 0) || (!$isassign && ($prt & ParPropRestrictedType::PRT_RESTRICT_GET) != 0)) return false;
			}
		}
		;
		return $this->Attributes->OnPropertyAccess == null || call_user_func_array($this->Attributes->OnPropertyAccess, array(new ParProperty($propStr, $type, $isassign)));
	}
	private function AddToTrace($propname, $type, &$value, $accessed = false, $isassign = false)
	{
		if (!$this->Attributes->Tracing->Enabled)
		{
			return;
		}
		$allowtrace = (!$isassign && $this->Attributes->Tracing->HasTraceThisType($type)) || ($isassign && $this->Attributes->Tracing->HasFlag(ParTraceFlags::PTF_TRACE_ASSIGN));
		if (!$allowtrace) return;
		$traceitem = new ParTracerItem($propname, $type);
		$traceitem->Accessed = $accessed;
		$traceitem->IsAssign = $isassign;
		if ($this->Attributes->Tracing->HasFlag(ParTraceFlags::PTF_KEEP_VALUE)) $traceitem->Value = &$value;
		$this->Attributes->Tracing->Add($traceitem);
	}

}
