<?php
if(!defined("TE_PATH_PREFIX")) define("TE_PATH_PREFIX", "");
if(!defined("TE_CUSTOM_EVULATOR_PATH")) define("TE_CUSTOM_EVULATOR_PATH", "");
require_once TE_PATH_PREFIX .'Misc/PropertyBase.php';
require_once TE_PATH_PREFIX .'Misc/SpecialCharType.php';
require_once TE_PATH_PREFIX .'Misc/IntertwinedBracketsStateType.php';
require_once TE_PATH_PREFIX .'Misc/StringUtils.php';
require_once TE_PATH_PREFIX .'Misc/MultiObject.php';
require_once TE_PATH_PREFIX .'Misc/AssignResult.php';
require_once TE_PATH_PREFIX . 'Text/TextElementAttribute.php';
require_once TE_PATH_PREFIX . 'Text/TextElementAttributes.php';
require_once TE_PATH_PREFIX . 'Text/TextElementFlags.php';
require_once TE_PATH_PREFIX . 'Text/TextElementInfos.php';
require_once TE_PATH_PREFIX . 'Text/TextElementInfo.php';
require_once TE_PATH_PREFIX . 'Text/TextEvulator.php';
require_once TE_PATH_PREFIX . 'Text/TextElements.php';
require_once TE_PATH_PREFIX .'Text/TextElement.php';
require_once TE_PATH_PREFIX .'Text/TextEvulatorParser.php';
require_once TE_PATH_PREFIX .'Text/TextElementClosedType.php';
require_once TE_PATH_PREFIX .'Misc/Utils.php';
require_once TE_PATH_PREFIX .'Misc/EvualtorTypes.php';
require_once TE_PATH_PREFIX .'Misc/SavedMacros.php';
require_once TE_PATH_PREFIX .'Misc/ArrayGroup.php';
require_once TE_PATH_PREFIX .'Misc/EvulatorOptions.php';
require_once TE_PATH_PREFIX . 'ParDecoder/PardecodeFlags.php';
require_once TE_PATH_PREFIX . 'ParDecoder/ParDecoder.php';
require_once TE_PATH_PREFIX . 'ParDecoder/Inners.php';
require_once TE_PATH_PREFIX . 'ParDecoder/ParItem.php';
require_once TE_PATH_PREFIX . 'ParDecoder/ParFormatType.php';
require_once TE_PATH_PREFIX . 'ParDecoder/ParFormatItem.php';
require_once TE_PATH_PREFIX . 'ParDecoder/ParFormat.php';
require_once TE_PATH_PREFIX . 'ParDecoder/ComputeActions.php';
require_once TE_PATH_PREFIX . 'ParDecoder/PropObject.php';
require_once TE_PATH_PREFIX . 'ParDecoder/ParDecodeAttributes.php';
require_once TE_PATH_PREFIX . 'ParDecoder/ParItemAssignReturnType.php';
require_once TE_PATH_PREFIX . 'ParDecoder/ParProperty.php';
require_once TE_PATH_PREFIX . 'ParDecoder/ParTracer.php';
require_once TE_PATH_PREFIX . 'ParDecoder/ParTracerItem.php';
require_once TE_PATH_PREFIX . 'XPathClasses/_XPathIncludes.php';

spl_autoload_register(function ($class) {

	if (!str_endswith($class, 'Evulator')) return;
	if(defined("TE_CUSTOM_EVULATOR_PATH") && !empty(TE_CUSTOM_EVULATOR_PATH))
	{
		if (file_exists( __DIR__ .  '/' .TE_CUSTOM_EVULATOR_PATH .'/' . $class . '.php')) {
			include_once  __DIR__ .  '/' .TE_CUSTOM_EVULATOR_PATH . '/' . $class . '.php';
		}
	}
	if (file_exists( __DIR__ . '/' . TE_PATH_PREFIX .'Evulator/' . $class . '.php')) {
		include_once  __DIR__ . '/' . TE_PATH_PREFIX . 'Evulator/' . $class . '.php';

	}

});
