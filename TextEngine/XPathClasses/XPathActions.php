<?php
namespace TextEngine;
class XPathActions
{
    public static function XExpressionSuccess(&$item,  &$expressions, &$baselist = null, $curindex = -1, $totalcounts = -1)
    {
		$actions = new XPathActions();
		$actions->XPathFunctions = new XPathFunctions();
        $actions->XPathFunctions->BaseItem = &$item;
        if ($totalcounts != -1)
        {
            $actions->XPathFunctions->TotalItems = $totalcounts;
        }
        else
        {
            if (baselist != null)
            {
				$actions->XPathFunctions->TotalItems = count($baselist);
            }
            else
            {
				$actions->XPathFunctions->TotalItems = $item->Parent->GetSubElementsCount();
            }
        }
        if ($curindex != -1)
        {
            $actions->XPathFunctions->ItemPosition = $curindex;
        }
        else
        {
            $actions->XPathFunctions->ItemPosition = $item.Index();
        }
		$result = array();
		if(get_class($expressions) == "TextEngine\XPathExpression")
		{
			$result = $actions->EvulateActionSingle($expressions);
		}
        else if (in_array("TextEngine\IXPathExpressionItem", class_implements($expressions)) && $expressions->IsItemContainer())
        {
            $result = $actions->EvulateAction($expressions);
        }

		
        if ($result[0] == null || (is_bool($result[0]) && !$result[0]))
        {
			
            return false;
        }
        else if (is_numeric($result[0]))
        {

            //int c = (int)Convert.ChangeType(result[0], TypeCode.Int32) - 1;
			$c = intval($result[0]) - 1;
			$totalcount = 0;
            if ($totalcounts != -1)
            {
                $totalcount = $totalcounts;
            }
            else
            {
                if ($baselist != null)
                {
                    $totalcount = count(baselist);

                }
                else
                {
                    $totalcount = $item->Parent->GetSubElementsCount();
                }
            }
            if ($c < -1 || $c >= $totalcount)
            {
                return false;
            }
            else
            {
                return $c == $actions->XPathFunctions->ItemPosition;
            }

        }
        return true;
    }
    public static function Eliminate(&$items, &$expressions, $issecondary = true)
    {
        $total = 0;
        $totalcount = $items->GetCount();
		
        for ($i = 0; $i < $items->GetCount(); $i++)
        {

			$result = false;
            if ($issecondary)
            {
                $result = self::XExpressionSuccess($items[$i], $expressions, $items, $total, $totalcount);
            }
            else
            {
                $result = self::XExpressionSuccess($items[$i], $expressions);
            }
            if (!$result)
            {
				$items->RemoveAt($i);
                $i--;
                $total++;
                continue;
            }
            $total++;

        }
        return $items;
    }
    public $XPathFunctions;
    public function EvulateActionSingle(&$item, &$sender = null)
    {
		$curvalue = null;
        $previtem = null;
		
        $xoperator = null;
        $waitvalue = null;
        $waitop = null;
        $values = array();

        for ($j = 0; $j < count($item->XPathExpressionItems); $j++)
        {
			unset($curitem);
			unset($nextitem);
			$nextitem = null;
            $curitem = &$item->XPathExpressionItems[$j];
			if($j + 1 < count($item->XPathExpressionItems))
			{
				$nextitem = &$item->XPathExpressionItems[$j + 1];
			}
            $nextop = null;
			unset($nextExp);
            $nextExp = null;
	
            if ($nextitem != null && !$nextitem->IsSubItem())
            {
                $nextExp = &$nextitem;
                $nextop = ($nextExp != null && $nextExp->IsOperator()) ? $nextExp->GetValue() : null;
            }
			unset($expvalue);
            $expvalue = null;
			
            if ($curitem->IsSubItem())
            {
                $expvalue = $this->EvulateAction($curitem);
                if (!$previtem->IsSubItem())
                {
					unset($prevexp);
                    $prevexp = &$previtem;
                    if ($prevexp->IsOperator())
                    {
                        $expvalue = $expvalue[0];
                    }
                    else
                    {
                        if ($this->XPathFunctions != null)
                        {
                            if ($curitem->ParChar == '[')
                            {
                                $xitems = $this->XPathFunctions->BaseItem->FindByXPath($prevexp.GetValue());
                                if (count($xitems) > 0)
                                {
                                    $xitems = self::Eliminate($xitems, $curitem);
                                }
                                if (count($xitems) > 0)
                                {
                                    $expvalue = true;
                                }
                                else
                                {
                                    $expvalue = false;
                                }
                                if ($curvalue === null)
                                {
                                    $curvalue = $expvalue;
									unset($previtem);
                                    $previtem = &$curitem;
                                    continue;
                                }
                            }
                            else if ($curitem->ParChar == '(')
                            {
                                $method = $this->XPathFunctions->GetMetohdByName($prevexp->GetValue());
                                if ($method != null)
                                {
									$expvalue = $method->invokeArgs($this->XPathFunctions, $expvalue);
                                    if ($curvalue === null)
                                    {
                                        $curvalue = $expvalue;
										unset($previtem);
                                        $previtem = &$curitem;
                                        continue;
                                    }
                                }
                            }
                        }
                    }
                }
            }
            else
            {

				unset($expItem);
                $expItem = &$curitem;
                if ($nextitem != null && $nextitem->IsSubItem())
                {
					unset($previtem);
                    $previtem = &$curitem;
                    continue;
                }
                if ($expItem->IsOperator())
                {
                    if ($expItem->GetValue() == ",")
                    {
                        if ($waitop != null)
                        {
                            $curvalue = \ComputeActions::OperatorResult($waitvalue, $curvalue, $waitop);
							unset($waitvalue);
							unset($waitop);
                            $waitvalue = null;
                            $waitop = null;
                        }
						$values[] = $curvalue;
                        $curvalue = null;
						unset($xoperator);
                        $xoperator = null;
                        continue;
                    }
					
					$opstr = $expItem->GetValue();
                    if ($opstr == "||" || $opstr == "|" || $opstr == "or" || $opstr == "&&" || $opstr == "&" || $opstr == "and")
                    {
						if ($waitop != null)
                        {
                            $curvalue = \ComputeActions::OperatorResult($waitvalue, $curvalue, $waitop);
							unset($waitvalue);
							unset($waitop);
                            $waitvalue = null;
                            $waitop = null;
                        }
                        $state = !empty($curvalue);
                        if ($opstr == "||" || $opstr == "|" || $opstr == "or")
                        {
                            if ($state)
                            {
								$values[] = true;
                                return $values;
                            }
                            else $curvalue = null;
                        }
                        else
                        {
                            if (!$state)
                            {
								$values[] = false;
                                return $values;
                            }
                            else $curvalue = null;
                        }
						unset($xoperator);
                        $xoperator = null;
                    }
                    else
                    {
						
						unset($xoperator);
                        $xoperator = &$expItem;
                    }
					unset($previtem);
                    $previtem = &$curitem;
                    continue;
                }
                else
                {
                    if ($expItem->IsVariable())
                    {
                        if (str_startswith($expItem->GetValue(), "@"))
                        {

							$s = substr($expItem->GetValue(), 1);

                            if (($nextExp == null || !$nextExp->IsOperator()) && ($sender == null || !$sender->IsSubItem() || $sender->ParChar != '('))
                            {

                                $expvalue = $this->XPathFunctions->BaseItem->HasAttribute($s);
                            }
                            else
                            {
                                $expvalue = $this->XPathFunctions->BaseItem->GetAttribute($s);
                            }
					
                            if ($expvalue == null) $expvalue = false;
                        }
                        else
                        {
                            $items = $this->XPathFunctions->BaseItem->FindByXPath($expItem->GetValue());
                            if ($items->GetCount() == 0)
                            {
                                $expvalue = false;
                            }
                            else
                            {
                                $expvalue = $items[0]->Inner();
                            }
                        }
                    }
                    else
                    {
                        $expvalue = $expItem->GetValue();

                    }
					
                    if ($curvalue === null)
                    {
                        $curvalue = $expvalue;
						unset($previtem);
                        $previtem = &$curitem;
                        continue;
                    }


                }
            }
			
            if ($xoperator != null)
            {
                if (in_array($xoperator->GetValue(), XPathExpression::$priotirystop))
                {
                    if ($waitop != null)
                    {
                        $curvalue = \ComputeActions::OperatorResult($waitvalue, $curvalue, $waitop);
                        $waitvalue = null;
                        $waitop = null;
                    }
                }
                if (($xoperator->GetValue() != "+" && $xoperator->GetValue() != "-") || $nextop == null || in_array($nextop, XPathExpression::$priotirystop))
                {
					$old = $curvalue;
				
                    $curvalue = \ComputeActions::OperatorResult($curvalue, $expvalue, $xoperator->GetValue());
                }
                else
                {
                    $waitvalue = $curvalue;
                    $waitop = $xoperator.GetValue();
                    $curvalue = $expvalue;
                }
				unset($xoperator);
                $xoperator = null;
            }
			unset($previtem);
            $previtem = &$curitem;
        }
        if ($waitop != null)
        {
            $curvalue = \ComputeActions::OperatorResult($waitvalue, $curvalue, $waitop);
            $waitvalue = null;
            $waitop = null;
        }
		$values[] = $curvalue;
		
        return $values;
    }
    public function EvulateAction($item)
    {
        $values = array();
        for ($i = 0; $i < count($item->XPathExpressions); $i++)
        {
			unset($curExp);
			$curExp = &$item->XPathExpressions[$i];
			$results = $this->EvulateActionSingle($curExp, $item);
			array_push($values, ...$results);
        }
        return $values;
    }
}