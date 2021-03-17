<?php
function str_isnullorempty($string)
{
	return $string == null || $string == "";
}
function str_startswith_array($string, $search)
{
	usort($search, function ($a, $b) {
		return compare_length($a, $b);
	});
	foreach ($search as $index => $item) {
		if(str_startswith($string, $item))
		{
			return true;
		}
	}
	return false;
}
function str_startswith($string, $search)
{
	if(is_array($search))
	{
		return str_startswith_array($string, $search);
	}
	$slen = strlen($search);
	return substr($string, 0, $slen) == $search;
}
function str_endswith_array($string, $search)
{
	usort($search, function ($a, $b) {
		return compare_length($a, $b);
	});
	foreach ($search as $index => $item) {
		if(str_endswith($string, $item))
		{
			return true;
		}
	}
	return false;
}
function str_endswith($string, $search)
{
	if(is_array($search))
	{
		return str_endswith_array($string, $search);
	}
	$slentop = strlen($string);
	$slen = strlen($search);
	if ($slen > $slentop) return false;
	return substr($string, $slentop - $slen, $slen) == $search;
}
function array_value_exists($value, $array, $ignorecase = false)
{
	$nval = $value;
	if($ignorecase)
	{
		$nval = mb_strtolower($nval);
	}
	foreach ($array as $index => $item) {
		if($ignorecase)
		{
			if($nval == mb_strtolower($item)) return true;
		}
		else
		{
			if($nval == $item) return true;
		}
	}
	return false;
}
function array_setifnotarray(&$item)
{
	if(is_array($item)) return $item;
	return array($item);
}
function array_value($key, &$array, $default = null)
{
	if(!$array || !is_array($array)) return $default;
	if (array_key_exists($key, $array)) {
		return $array[$key];
	}
	return $default;
}
/**
 *	@param $input Search input
 *	@param $_values Searching values
 * @return bool
 */
function str_equalsany($input, $_values)
{
	for($i=1;$i<func_num_args();$i++)
	{
		if($input == func_get_arg($i)) return true;
	}
	return false;
}
function string_contains($find, $input, $delimiter = ',', $trim = true)
{
	$array = array_map(function ($item) use ($trim) {
		if ($trim) {
			return trim($item);
		}
		return $item;
	}, explode($delimiter, $input));
	foreach ($array as $index => $item) {
		if ($item == $find) return true;
	}
	return false;
}
class HTMLUTIL
{
	public static function toAttribute($vars, $exclude = array())
	{
		$sb = '';
		foreach ($vars as $index => $var) {
			if (array_key_exists($var->Name, $exclude)) continue;
			$sb .= ' ' . $var->Name . '="' . str_replace('"', '\\"', $var->Value) . '"';
		}
		return $sb;
	}
}