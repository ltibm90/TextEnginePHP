<?php
if(!defined("TE_PATH_PREFIX")) define("TE_PATH_PREFIX", "");
if(!defined("TE_CUSTOM_EVULATOR_PATH")) define("TE_CUSTOM_EVULATOR_PATH", "");
if(!defined("TE_INCLUDEBASE")) define("TE_INCLUDEBASE", __DIR__);
require_once TE_PATH_PREFIX . 'Text/TextElementFlags.php';
require_once TE_PATH_PREFIX . 'Text/TextElementInfos.php';
require_once TE_PATH_PREFIX . 'Text/TextElementInfo.php';
require_once TE_PATH_PREFIX . 'Text/TextEvulator.php';
require_once TE_PATH_PREFIX . 'Text/TextElements.php';
require_once TE_PATH_PREFIX .'Text/TextElement.php';
require_once TE_PATH_PREFIX .'Text/TextEvulatorParser.php';
require_once TE_PATH_PREFIX .'Misc/Utils.php';
require_once TE_PATH_PREFIX .'Misc/EvualtorTypes.php';
require_once TE_PATH_PREFIX .'Misc/SavedMacros.php';
require_once TE_PATH_PREFIX .'Misc/ArrayGroup.php';
require_once TE_PATH_PREFIX . 'ParDecoder/ParDecoder.php';
require_once TE_PATH_PREFIX . 'ParDecoder/Inners.php';
require_once TE_PATH_PREFIX . 'ParDecoder/ParItem.php';
require_once TE_PATH_PREFIX . 'ParDecoder/ComputeActions.php';
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
