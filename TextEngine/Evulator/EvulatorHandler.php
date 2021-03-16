<?php
abstract class EvulatorHandler
{
	public function OnRenderPre(&$tag, &$vars) { return true; }
	public function OnRenderPost(&$tag, &$vars, &$result) { }
	public function OnRenderFinishPre(&$tag, &$vars, &$result) { return true; }
	public function OnRenderFinishPost(&$tag, &$vars, &$result) { }
}
