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
		if (is_object($item1) || is_object($item2)) {
			return null;
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
		}
		if(!$item1) return $item2;
		return $item1;
	}

	public static function CallMethodSingle($object, $name, $params)
	{
		if (!$object) return null;
		
		if (is_array($object)) {
			
			$val = array_value($name, $object);
			if (is_callable($val)) {
				return call_user_func_array($val, $params);
			}
		} else if (is_object($object)) 
		{

			if (method_exists($object, $name)) 
			{
				
				$rmethod = new ReflectionMethod($object, $name);
				if ($rmethod->isPublic()) {
					return $rmethod->invokeArgs($object, $params);
				}
			}
			else if(property_exists($object, $name))
			{
				$prop = $object->$name;
				if(is_callable($prop))
				{
					return call_user_func_array($prop, $params);
				}
			}
		}
	}


	public static function CallMethod($name, $params, &$vars, &$localvars = null)
	{

		$dpos = strpos($name, '::');
		if ($dpos !== false) {
			$clsname = substr($name, 0, $dpos);
			$method = substr($name, $dpos + 2);
			if ((array_value_exists($clsname . '::', ParItem::$globalFunctions) || array_value_exists($name, ParItem::$globalFunctions)) && method_exists($clsname, $method)) {
				return call_user_func_array($name, $params);
			}
		} 
		else if (array_value_exists($name, ParItem::$globalFunctions) && function_exists($name)) {
			
			return call_user_func_array($name, $params);
		}
		return self::CallMethodSingle($vars, $name, $params);
	}
	/**  @param $item InnerItem */
	public static function GetPropValue($item, &$vars, &$localvars = null)
	{

		$res = null;
		if ($localvars) 
		{
			$res = self::GetPropValueDirect($item, $localvars);
		}
		if ($res === null) {
			$res = self::GetPropValueDirect($item, $vars);
		}
		return $res;
	}

	/**  @param $item InnerItem */
	public static function GetPropValueDirect($item, &$vars)
	{
		$name = $item->value;
		if ($name) {
			return self::GetProp($name, $vars);
		}
		return null;
	}

	/**  @param $item string */
	public static function GetProp($item, $vars)
	{

		if (is_array($vars)) 
		{
			return array_value($item, $vars);
		} 
		else if (is_object($vars)) 
		{
			if(get_class($vars) == "ArrayGroup")
			{
				return $vars->GetSingleValueForAll($item);
			}
			else
			{
				if (property_exists($vars, $item)) 
				{
					$prop = new ReflectionProperty($vars, $item);
					if ($prop->isPublic()) {
						return $prop->getValue($vars);
					}
				}
			}

		}
		return null;
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
}
