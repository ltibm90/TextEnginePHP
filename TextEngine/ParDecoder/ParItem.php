<?php
class ParItem extends InnerItem
{
	public static $globalFunctions = array('count', 'strlen', 'HTML::');

	public function __construct()
	{
		
	}
	public $BaseDecoder;
	public $ParName;
	/** @var ParItem */
	public $parent;

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
	public function GetParentUntil($name)
	{
		$parent = $this->parent;
		while ($parent != null && $parent->ParName == $name)
		{
			$parent = $parent->parent;
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
			$currentitemvalue = null;
			/* @var $current InnerItem */
			$current = $this->innerItems[$i];
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
                    $varnew = null;
                    if ($lastvalue != null)
					{
						$varnew = $lastvalue;
					}
					else
					{
						$varnew = $vars;
					}
					
                    if ($prevvalue != "")
					{
						if ($current->ParName == "(")
						{				
							if($this->BaseDecoder && $this->BaseDecoder->SurpressError)
							{
								try 
								{
									$currentitemvalue = ComputeActions::CallMethod($prevvalue, $subresult->result, $varnew, $localvars);	
								} 
								catch (Exception $e) 
								{
									$currentitemvalue = null;
								}
							}
							else
							{
								$currentitemvalue = ComputeActions::CallMethod($prevvalue, $subresult->result, $varnew, $localvars);	
							}

						}
						else if($current->ParName == "[")
						{
							$prop = ComputeActions::GetProp($prevvalue, $varnew);
						
							if (is_array($prop) || is_string($prop))
							{
								
								$indis = $subresult->result[0];
								if($indis != null)
								{
									 $currentitemvalue = $prop[$indis];
								}
								else
								{
									$currentitemvalue = null;
								}
                               
                            }
							else if (is_object($prop))
							{
								
							}


						}
					}
					else
					{
						if($current->ParName == "(")
						{
							$currentitemvalue = $subresult->result[0];
						}
						else if($current->ParName == "[")
						{
							$currentitemvalue = $subresult->result;
						}
						else if($current->ParName == "{")
						{
							$currentitemvalue = $subresult->result;
						}
					}

                }
				else
				{
					if(!$current->is_operator && $current->type == InnerItem::TYPE_VARIABLE &&  $next != null && $next->IsParItem())
					{
						$currentitemvalue = null;
					}
					else
					{
					
						$currentitemvalue = $current->value;


					}
					if ($current->type == InnerItem::TYPE_VARIABLE && ($next == null || !$next->IsParItem()) && ($xoperator == null || $xoperator->value != ".") )
					{
						
						if ($currentitemvalue == null || $currentitemvalue == "null")
						{

							$currentitemvalue = null;
						}
						else if ($currentitemvalue == "false")
						{
							$currentitemvalue = false;
						}
						else if ($currentitemvalue== "true")
						{
							$currentitemvalue = true;
						}
						else if (!$this->IsObject())
						{
							
							$currentitemvalue = ComputeActions::GetPropValue($current, $vars, $localvars);
							
						}
				
					}
				}
                if($unlemused)
				{

					$currentitemvalue = empty($currentitemvalue);
					$unlemused = false;
				}		

                if ($current->is_operator)
				{
					if($current->value == "!")
					{
						$unlemused = !$unlemused;
						$previtem = $current;
						continue;
					}
					if (($this->IsParItem() && $current->value == ",") || ($this->IsArray() && $current->value  == "=>" && ($waitvalue == null || $waitvalue == "")) || ($this->IsObject() && $current->value  == ":" && ($waitvalue == null || $waitvalue == "") ))
					{
						if ($waitop2 != "")
						{
							
							$lastvalue = ComputeActions::OperatorResult($waitvalue2, $lastvalue, $waitop2);
							$waitvalue2 = null;
							$waitop2 = "";
						}
						if ($waitop != "")
						{
							$lastvalue = ComputeActions::OperatorResult($waitvalue, $lastvalue, $waitop);
							$waitvalue = null;
							$waitop = "";
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

						$lastvalue = null;
						$xoperator = null;
						$previtem = $current;
						continue;
					}
					$opstr = $current->value;
                    if ($opstr == "||" || $opstr == "|" || $opstr == "or" || $opstr == "&&" || $opstr == "&" || $opstr == "and" || $opstr == "?")
					{
						if ($waitop2 != "")
						{
							$lastvalue = ComputeActions::OperatorResult($waitvalue2, $lastvalue, $waitop2);
							$waitvalue2 = null;
							$waitop2 = "";
						}
						if ($waitop != "")
						{
							$lastvalue = ComputeActions::OperatorResult($waitvalue, $lastvalue, $waitop);
							$waitvalue = null;
							$waitop = "";
						}

						$state =!empty($lastvalue);
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
							$lastvalue = null;
							$previtem = $current;
							continue;


						}
                        if ($opstr == "||" || $opstr == "|" || $opstr == "or")
						{
							if ($state)
							{
								$lastvalue = true;
								if ($opstr != "|")
								{
									$cr->result[] = true;
									return $cr;
								}
							}
							else
							{
								$lastvalue = false;
							}
						}
						else
						{
							if (!$state)
							{
								$lastvalue = false;
								if ($opstr != "&")
								{
									$cr->result[] = false;
									return $cr;
								}
							}
							else
							{
								$lastvalue = true;
							}
						}
						
                        $xoperator = $current;
                    }
					else
					{
						$xoperator = $current;
					}

					$previtem = $current;
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
								$lastvalue = ComputeActions::OperatorResult($waitvalue2, $lastvalue, $waitop2);
								$waitvalue2 = null;
								$waitop2 = "";
							}
							if ($waitop != "")
							{
								$lastvalue = ComputeActions::OperatorResult($waitvalue, $lastvalue, $waitop);
								$waitvalue = null;
								$waitop = "";
							}
						}

						if ($next != null && $next->IsParItem())
						{
						
							if ($xoperator->value == ".")
							{
								
								if($currentitemvalue)
								{
									$lastvalue = ComputeActions::GetProp($currentitemvalue, $lastvalue);
								}

							}
							else
							{
								if($waitop == "")
								{
									$waitop = $xoperator->value;
									$waititem = $current;
									$waitvalue = $lastvalue;
								}
								else if($waitop2 == "")
								{
									$waitop2 = $xoperator->value;
									$waititem2 = $current;
									$waitvalue2 = $lastvalue;
								}
								$lastvalue = null;
								
							}
							
							$xoperator = null;
							$previtem = $current;
							continue;
						}
						if ($xoperator->value == ".")
						{
							$lastvalue = ComputeActions::GetProp($currentitemvalue, $lastvalue);
						}
						else if ($nextop != "." && (($xoperator->value != "+" && $xoperator->value != "-") || $nextop == "" || (ComputeActions::PriotiryStopContains($nextop))))
						{
							$opresult = ComputeActions::OperatorResult($lastvalue, $currentitemvalue, $xoperator->value);
							$lastvalue = $opresult;
						}
						else
						{
							if($waitop == "")
							{
								$waitop = $xoperator->value;
								$waititem = $current;
								$waitvalue = $lastvalue;
								$lastvalue = $currentitemvalue;
							}
							else if($waitop2 == "")
							{
								$waitop2 = $xoperator->value;
								$waititem2 = $current;
								$waitvalue2 = $lastvalue;
								$lastvalue = $currentitemvalue;
							}
							$previtem = $current;
							continue;
						}
					}
					else
					{							
								
						$lastvalue = $currentitemvalue;
					}


				}

                $previtem = $current;
            }
			if ($waitop2 != "")
			{

				$lastvalue = ComputeActions::OperatorResult($waitvalue2, $lastvalue, $waitop2);
				$waitvalue2 = null;
				$waitop2 = "";
			}
			if ($waitop != "")
			{
				
				$lastvalue = ComputeActions::OperatorResult($waitvalue, $lastvalue, $waitop);
				$waitvalue = null;
				$waitop = "";
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

}
