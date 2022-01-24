<?php

class ComputeResult
{
	const RESULT_VALUE = 0;
	const RESULT_ARRAY = 1;
	const RESULT_OBJECT = 2;
	public $resultType;
	public $result;

	public function __construct()
	{
		$this->resultType = self::RESULT_VALUE;
	}
}

class ComputeActions
{
	public static function PriotiryStopContains($ops)
	{
		return str_equalsany($ops, "and", "&&", "||", "|", "==", "=", ">", "<", ">=", "!=", "<=", "or", "+", "-", ",", "=>", "?", ":");
	}

	public static function OperatorResult($item1, $item2, $operator)
	{
		if (is_object($item1) || is_object($item2)) 
		{
			if(is_object($item1) && $item2 == -1) return $item1;
			return null;
		}
		if(is_bool($item1)) $item1 = $item1 ? "1" : "";
		if(is_bool($item2)) $item2 = $item2 ? "1" : "";
		if($item1 === "" && $item2 === 0)
		{
			$item2 = "0";
		}
		else if($item2 === "" && $item1 === 0)
		{
			$item1 == "0";
		}
		if (($operator == "||" ||  $operator == "or" || $operator == "&&" || $operator == "and") || ((!is_numeric($item1) || !is_numeric($item2)) && ($operator == "&" || $operator == "|") )) {
			

			$lefstate = !empty($item1);
			$rightstate = !empty($item2);
			if ($operator == "||" || $operator == "|" || $operator == "or") {
				if ($lefstate != $rightstate) {
					return true;
				}
				return $lefstate;
			} else {
				if ($lefstate && $rightstate) {
					return true;
				}
				return false;
			}
		}
		if ($operator == '+') {
			if ((!is_numeric($item1) && !is_bool($item1)) || (!is_numeric($item2) && !is_bool($item2))) {
				$operator = '.';
			}
		}



		switch ($operator) {
			case '|':
				return $item1 | $item2;
			case '&':
				return $item1 & $item2;
			case '==':
				return $item1 == $item2;
			case '=':
				return $item1 == $item2;
			case '!=':
				return $item1 != $item2;
			case '>=':
				return $item1 >= $item2;
			case '<=':
				return $item1 <= $item2;
			case '>':
				return $item1 > $item2;
			case '<':
				return $item1 < $item2;
			case '+':
				return $item1 + $item2;
			case '-':
				return $item1 - $item2;
			case '*' :
				return $item1 * $item2;
			case '/':
				return $item1 / $item2;
			case '%':
				return $item1 % $item2;
			case '^':
				return pow($item1, $item2);
			case '.':
				return $item1 . $item2;
			case '<<':
				return $item1 << $item2;
			case '>>':
				return $item1 >> $item2;
		}
		if(!$item1) return $item2;
		return $item1;
	}
	public static function CallMethodDirect($object, $name, $params, &$iscalled)
	{
		if (is_array($object)) {
			
			$val = array_value($name, $object);
			if (is_callable($val)) {
				$iscalled = true;
				return call_user_func_array($val, $params);
			}
		} 
		else if (is_object($object)) 
		{

			if (method_exists($object, $name)) 
			{
				
				$rmethod = new ReflectionMethod($object, $name);
				if ($rmethod->isPublic()) {
					$iscalled = true;
					return $rmethod->invokeArgs($object, $params);
				}
			}
			else if(property_exists($object, $name))
			{
				$prop = $object->$name;
				if(is_callable($prop))
				{
					$iscalled = true;
					return call_user_func_array($prop, $params);
				}
			}
		}
		return null;
	}
	public static function CallMethodSingle($object, $name, $params, &$iscalled)
	{
		$iscalled = false;
		if (!$object) return null;
		if(is_object($object) && get_class($object) == "MultiObject")
		{
			for($i = 0; $i < $object->Count; $i++)
			{
				if($object->Get($i) == null) continue;
				$res = ComputeActions::CallMethodSingle($object->Get($i), $name, $params, $iscalled);
				if($iscalled) return $res;
			}
			return null;
		}
		return ComputeActions::CallMethodDirect($object, $name, $params, $iscalled);
	}
	public static function CallMethod($name, $params, &$vars, &$iscalled, &$localvars = nul, &$sender = null, $checkglobal = true)
	{
		$iscalled = false;
		if($checkglobal)
		{
			$dpos = strpos($name, '::');
			if ($dpos !== false && $sender !== null) {
				$clsname = substr($name, 0, $dpos);
				$method = substr($name, $dpos + 2);
				if ((array_value_exists($clsname . '::', $sender->Attributes->GlobalFunctions) || array_value_exists($name, $sender->Attributes->GlobalFunctions)) && method_exists($clsname, $method)) {
					$iscalled = true;
					return call_user_func_array($name, $params);
				}
			} 
			else if ($sender !== null && array_value_exists($name, $sender->Attributes->GlobalFunctions) && function_exists($name)) {
				
				return call_user_func_array($name, $params);
			}
		}
		return self::CallMethodSingle($vars, $name, $params, $iscalled);
	}
	/**  @param $item InnerItem */
	public static function &GetPropValue($item, &$vars, &$localvars = null)
	{

		$res = null;
		if ($localvars) 
		{
			$res = &self::GetPropValueDirect($item, $localvars);
		}
		if ($res === null || $res->PropType == PropType::Empty) {
			unset($res);
			$res = &self::GetPropValueDirect($item, $vars);
		}
		return $res;
	}

	/**  @param $item InnerItem */
	public static function &GetPropValueDirect($item, &$vars)
	{
		$name = $item->value;
		if ($name) {
			return self::GetProp($name, $vars);
		}
		return null;
	}
	public static function& GetPropEx($item, $vars, &$localvars = null)
	{
		$res = null;
		if ($localvars) 
		{
			$res = &self::GetProp($item, $localvars);
		}
		if ($res === null || $res->PropType == PropType::Empty) {
			unset($res);
			$res = &self::GetProp($item, $vars);
		}
	}
	/**  @param $item string */
	public static function& GetProp($item, $vars)
	{
		$pObject = new PropObject();
		if(is_object($vars) && get_class($vars) == "MultiObject")
		{
			for($i = 0; $i < $vars->Count; $i++)
			{
				if($vars->Get($i) === null) continue;
				$res = ComputeActions::GetProp($item, $vars->Get($i));
				if($res !== null || $res->PropType != PropType::Empty) return $res;
			}
			return $pObject;
		}
		if (is_array($vars)) 
		{
			$res = array_value($item, $vars);
			$pObject->Value = &$res;
			$pObject->PropType = PropType::Indis;
			$pObject->Indis = &$item;
			$pObject->PropertyInfo = &$vars;
			return $pObject;
		} 
		else if (is_object($vars)) 
		{
			if(get_class($vars) == "ArrayGroup")
			{
				unset($findArray);
				$findArray =  $vars->GetSingleValueForAllExtend($item);
				if($findArray->Found)
				{
					$pObject->Value = &$findArray->Value;
					$pObject->PropType = PropType::Indis;
					$pObject->Indis = &$item;
					$pObject->PropertyInfo = &$findArray->Array;
				}
			}
			else
			{
				if (property_exists($vars, $item)) 
				{
					$prop = new ReflectionProperty($vars, $item);
					if ($prop->isPublic()) {
						$pObject->Value = &$vars->$item;// $prop->getValue($vars);
						$pObject->PropType = PropType::Property;
						$pObject->PropertyInfo = &$prop;
						$pObject->Indis = &$vars;
					}
				}
			}

		}
		return $pObject;
	}

	/**  @param $item InnerItem */
	public static function PropExists($item, $vars)
	{
		if (is_array($vars)) {
			$val = array_value($item->value, $vars);
			if ($val && !is_callable($val)) {
				return true;
			}
		} 
		else if (is_object($item)) 
		{
			if(get_class($item) == "ArrayGroup")
			{
				return $item->KeyExistsInAll($item);
			}
			return property_exists($vars, $item);
		}
		return false;
	}

	public static function IsObjectOrArray(&$item)
	{
		return $item && is_object($item) || is_array($item);
	}
	public static function AssignObjectValue(&$probObj, $op, &$value)
	{
		$ar = new AssignResult();
		if ($probObj == null || $probObj->Indis === null) return $ar;
		if (strlen($op) > 1)
		{
			$value = ComputeActions::OperatorResult($probObj->Value, $value, substr($op, 0, strlen($op) - 1));
		}

		if ($probObj->PropType == PropType::Property)
		{

			unset($pi);
			$pi = &$probObj->PropertyInfo;

			if ($pi->isPublic())
			{
				 $pi->setAccessible(true);
				$pi->setValue($probObj->Indis, $value);
				$ar->AssignedValue = &$value;
				$ar->Success = true;
			}
		}
		else if ($probObj->PropType == PropType::Indis)
		{
			$probObj->PropertyInfo[$probObj->Indis] = $value;
			$ar->AssignedValue = &$value;
			$ar->Success = true;
		}
		return $ar;
	}
}
