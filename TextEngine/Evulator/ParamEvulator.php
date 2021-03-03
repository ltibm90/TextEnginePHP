<?php
class ParamEvulator extends  BaseEvulator
{
	public function Render(&$tag, &$vars)
	{
		$result = new TextEvulateResult();
		if(!$tag->ElementType = TextElementType::Parameter)
		{
			return $result;
		}
		$result->Result = TextEvulateResult::EVULATE_TEXT;
		if($tag->ParData == null)
		{
			$tag->ParData = new ParDecoder($tag->ElemName);
			$tag->ParData->Decode();
		}
		$etresult = $this->EvulatePar($tag->ParData, $vars);
		if(is_array($etresult))
		{
			$etresult = $etresult[0];
		}
		$result->TextContent .= $etresult;
		return $result;
	}
}
