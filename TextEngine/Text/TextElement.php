<?php
abstract class TextElementType
{
	const ElementNode = 1;
	const AttributeNode = 2;
	const TextNode = 3;
	const CDATASection = 4;
	const EntityReferenceNode = 5;
	const CommentNode = 8;
	const Document = 9;
	const Parameter = 16;
	const XMLTag = 17;
}
class TextElement
{
	private $elemName;
	function __get($prop) 
	{
		if($prop == "ElemName")
		{
			return $this->elemName;
		}
        return $this->$prop;
	}
    function __set($prop, $val) 
	{
		if($prop == "ElemName")
		{
			$this->elemName = $val;
			$this->NoAttrib = false;
			if ($this->BaseEvulator != null && (($this->BaseEvulator->TagInfos->GetElementFlags($val) & TextElementFlags::TEF_NoAttributedTag) != 0))
			{
				$this->NoAttrib = true;
			}
			return;
		}
        $this->$prop = $val;
	}
	/** @var array */
	public $ElemAttr = array();
	/** @var TextEvulator */
	public $BaseEvulator;
	public $Closed;
	public $Value;
	/** @var TextElement[] */
	public $SubElements;
	public $SubElementsCount = 0;
	public $SlashUsed;
	/** @var TextElement */
	public $Parent;
	/** @var bool */
	public $DirectClosed;
	public $AutoAdded;
	public $IsParam = false;
	public $IsSummary = false;
	/** @var string */
	public $AliasName;
	public $AutoClosed;
	public $NoAttrib;
	/** @var int */
	public $Index_old;
	/** @var string */
	public $TagAttrib;
	
	public $ElementType = TextElementType::ElementNode;

	public function __construct()
	{
		$this->SubElements = new TextElements();
	}
	public function Depth()
	{
		$parent = $this->Parent;
		$total = 0;
		while ($parent != null && $parent->ElemName != "#document")
		{
			$total++;
			$parent = $parent->Parent;
		}
		return $total;
	}
	public function Index()
	{	
		if(!$this->Parent) return -1;
		$total = 0;
		for($i = 0; $i < $this->Parent->SubElementsCount; $i++)
		{
			
			unset($current);
			$current = &$this->Parent->SubElements[$i];
			if($current == $this)
			{
				return $i;
			}
		}
		return -1;
	}
	/** @param $element TextElement */
	public function AddElement(&$element)
	{
		$this->SubElements->Add($element);
		$element->Index_old = $this->SubElementsCount;
		$this->SubElementsCount++;

	}
	public function HasAttribute($name)
	{
		return isset($this->ElemAttr[$name]);
	}
	public function GetAttribute($name, $default = null)
	{
		return array_value($name, $this->ElemAttr, $default);
	}

	public function SetAttribute($name, $value)
	{
		$this->ElemAttr[$name] = $value;
	}

	public function NameEquals($name, $matchalias = false)
	{
		if (strtolower($this->ElemName) == strtolower($name)) return true;
		if ($matchalias) {
			if (array_key_exists($name, $this->BaseEvulator->Aliasses)) {
				$alias = $this->BaseEvulator->Aliasses[$name];
				if (!is_array($alias)) {
					if ($alias == $this->ElemName) return true;
				} else {
					if (array_value_exists(strtolower($this->ElemName), $alias)) return true;
				}
			}
		}
		return false;
	}

	public function SetInner($text)
	{
		$this->BaseEvulator->Text = $text;
		$this->SubElements = new TextElements();
		$this->SubElementsCount = 0;
		$this->BaseEvulator->Parse($this);
		return $this;
	}
	public function FirstChild()
	{
		if($this->SubElements && $this->SubElementsCount > 0)
		{
			return $this->SubElements[0];
		}
		return null;
	}
	public function LastChild()
	{
		if($this->SubElements && $this->SubElementsCount > 0)
		{
			return $this->SubElements[$this->SubElementsCount - 1];
		}
		return null;
	}
	public function Outer($outputformat = false)
	{
		if ($this->ElemName == '#document') {
			return $this->Inner();
		}
		if ($this->ElemName == '#text') {
			return $this->Value;
		}
		if ($this->ElementType == TextElementType::CommentNode) {
			return $this->BaseEvulator->LeftTag . '--' . $this->value . '--' . $this->BaseEvulator->RightTag;
		}
		$text = '';
		$additional = '';
		if ($this->TagAttrib) {
			$additional .= '=' . $this->TagAttrib;
		}
		if ($this->ElementType == TextElementType::Parameter) {
			$text .= $this->BaseEvulator->LeftTag . $this->BaseEvulator->ParamChar . $this->ElemName . HTMLUTIL::toAttribute($this->ElemAttr) . $this->BaseEvulator->RightTag;
		}
		else 
		{
			if ($this->AutoAdded) {
				if (!$this->SubElements) return '';
			}
			$text .= $this->BaseEvulator->LeftTag . $this->ElemName . $additional . (($this->NoAttrib && $this->ElementType == TextElementType::ElementNode) ? ' ' . $this->Value : HTMLUTIL::toAttribute($this->ElemAttr));
			if ($this->DirectClosed) {
				$text .= " /" . $this->BaseEvulator->RightTag;
			} else if ($this->AutoClosed) {
				$text .= $this->BaseEvulator->RightTag;
			} else {
				$text .= $this->BaseEvulator->RightTag;
				$text .= $this->Inner($outputformat);
				$eName = $this->ElemName;
				if (!empty($this->AliasName)) {
					$eName = $this->AliasName;
				}
				$text .= $this->BaseEvulator->LeftTag . '/' . $eName . $this->BaseEvulator->RightTag;
			}
		}
		return $text;
	}
	public function HeaderText($outputformat = false)
	{
		if ($this->AutoAdded && $this->SubElementsCount == 0) return "";
		$depth = $this->Depth();
        $text = '';
		if ($outputformat)
		{
			$text .= str_repeat('\t', $depth);
		}
		if ($this->ElementType == TextElementType::XMLTag)
		{
			$text .= $this->BaseEvulator->LeftTag . "?" . $this->ElemName . HTMLUTIL::toAttribute($this->ElemAttr) . "?" . $this->BaseEvulator->RightTag;
		}
		if ($this->ElementType == TextElementType::Parameter)
		{
			$text .= $this->BaseEvulator->LeftTag . $this->BaseEvulator->ParamChar . $this->ElemName . HTMLUTIL::toAttribute($this->ElemAttr) . $this->BaseEvulator->RightTag;
		}
		else if ($this->ElementType == TextElementType::ElementNode)
		{
			$additional = '';
			if (!empty($this->TagAttrib))
			{
				$additional .= '=' . $this->TagAttrib;
			}
			$text .= $this->BaseEvulator->LeftTag . $this->ElemName . $additional . (($this->NoAttrib) ? ' ' . $this->Value : HTMLUTIL::toAttribute($this->ElemAttr));
			if ($this->DirectClosed)
			{
				$text .= " /" . $this->BaseEvulator->RightTag;
			}
			else if ($this->AutoClosed)
			{
				$text .= $this->BaseEvulator->RightTag;
			}
			else
			{
				$text .= $this->BaseEvulator->RightTag;
			}
		}
		else if ($this->ElementType == TextElementType::CDATASection)
		{
			$text .= $this->BaseEvulator->LeftTag + "![CDATA[" + $this->Value + "]]" + $this->BaseEvulator->RightTag;
		}
		else if ($this->ElementType == TextElementType::CommentNode)
		{
			$text .= $this->BaseEvulator->LeftTag + "--" + $this->Value + "--" + $this->BaseEvulator->RightTag;
		}
		if ($outputformat && $this->FirstChild() && $this->FirstChild()->ElemName != "#text")
		{
			$text .= '\r\n';
		}
		return $text;
	}
	public function Footer($outputformat = false)
	{
		if ($this->SlashUsed || $this->DirectClosed || $this->AutoClosed) return null;
		$text = '';
		if ($this->ElementType == TextElementType::ElementNode)
		{
			if ($outputformat)
			{
				if ($this->LastChild() && $this->LastChild()->ElemName != "#text")
				{
					$text .= str_repeat('\t', $this->Depth());
				}
			}
			$eName = $this->ElemName;
			if (!empty($this->AliasName))
			{
				$eName = $this->AliasName;
			}
			$text .= $this->BaseEvulator->LeftTag . '/' + $eName . $this->BaseEvulator->RightTag;
		}
		if ($outputformat)
		{
			$text .= "\r\n";
		}
		return $text;
	}
	public function Inner($outputformat = false)
	{
		$text = '';
		if ($this->ElementType == TextElementType::CommentNode || $this->ElementType == TextElementType::XMLTag)
		{
			return $text;
		}
		if ($this->ElemName == '#text' || $this->ElementType == TextElementType::CDATASection) {
			if ($this->ElementType == TextElementType::EntityReferenceNode)
			{
				$text .= "&" + $this->Value + ";";
				return $text;
			}
			return $this->Value;
		}
		if (!$this->SubElements) return $text;
		foreach ($this->SubElements as $index => $subElement) {
			if ($subElement->ElemName == '#text') 
			{
				$text .= $subElement->Inner($outputformat);
			} 
			else if ($this->ElementType == TextElementType::CDATASection) 
			{
				$text .= $subElement->HeaderText();
			} 
			else if ($this->ElementType == TextElementType::CommentNode) 
			{
				$text .= $subElement->Outer($outputformat);
			} 
			else if ($this->ElementType == TextElementType::Parameter) 
			{
				//$text .= $this->BaseEvulator->LeftTag . $this->BaseEvulator->ParamChar . $subElement->ElemName . HTMLUTIL::toAttribute($subElement->elemAttr) . $this->BaseEvulator->RightTag;
				$text .= $subElement->HeaderText();
			} 
			else 
			{
				$text .= $subElement->HeaderText($outputformat);
				$text .= $subElement->Inner($outputformat);
				$text .= $subElement->Footer($outputformat);
			}
		}
		return $text;
	}

	public function PreviousElementWN($name)
	{
		$prev = $this->PreviousElement();
		while ($prev != null) {
			if ($prev->ElementType == TextElementType::Parameter  || $prev->ElemName == '#text') {
				$prev = $prev->PreviousElement();
				continue;
			}
			
			if (preg_grep( "/$prev->ElemName/i" ,  func_get_args() )) {
				return $prev;
			}
			$prev = $prev->PreviousElement();
		}
		return null;
	}

	public function NextElementWN($name)
	{
		$next = $this->NextElement();
		while ($next != null) {
			if ($next->ElementType == TextElementType::Parameter || $next->ElemName == '#text') {
				$next = $next->NextElement();
				continue;
			}
			if (preg_grep( "/$next->ElemName/i" ,  func_get_args() )) {
				return $next;
			}
			$next = $next->NextElement();
		}
		return null;
	}

	public function PreviousElement()
	{
		if ($this->Index() - 1 >= 0) {
			return $this->parent->SubElements[$this->Index() - 1];
		}
		return null;
	}

	public function NextElement()
	{
		if ($this->Index() + 1 < $this->parent->SubElementsCount) {
			return $this->parent->SubElements[$this->Index() + 1];
		}
		return null;
	}

	public function GetSubElement($name)
	{

		for ($i = 0; $i < $this->SubElementsCount; $i++) {
			$ename = $this->SubElements[$i]->ElemName;
			if (preg_grep( "/$ename/i" ,  func_get_args() )) {
				return $this->SubElements[$i];
			}
		}
		return null;
	}

	public function InnerText()
	{
		if ($this->ElemName == '#text' ||  $this->ElementType == TextElementType::CDATASection) {
			if ($this->ElementType == TextElementType::EntityReferenceNode)
            {
				return array_value($this.Value, $this->BaseEvulator->AmpMaps);
			}
			return $this->Value;
		}
		$text = '';
		if (!$this->SubElements) return $text;
		foreach ($this->subElements as $index => $subElement) 
		{
			if ($subElement->ElemName == '#text' ||  $subElement->ElementType == TextElementType::CDATASection) 
			{
				if ($subElement->ElementType == TextElementType::EntityReferenceNode)
				{
					$text .= array_value($subElement.Value, $this->BaseEvulator->AmpMaps);
				}
				else
				{
					$text .= $subElement->Value;
				}
			} 
			else 
			{
				$text .= $subElement->InnerText();
			}

		}
		return $text;
	}

	/** @return TextEvulateResult */
	public function EvulateValue($start = 0, $end = 0, &$vars = null)
	{
		$result = new TextEvulateResult();
		$result->Result = TextEvulateResult::EVULATE_TEXT;
		if ($this->ElementType == TextElementType::CommentNode)
		{
			return null;
		}
		if ($this->ElemName == '#text') {
			if($this->BaseEvulator->EvulatorTypes->Text != "" && class_exists($this->BaseEvulator->EvulatorTypes->Text))
			{
				$evulator = new $this->BaseEvulator->EvulatorTypes->Text($this->BaseEvulator);
				return $evulator->Render($this, $vars);
			}
			$result->TextContent = $this->Value;
			return $result;
		}

		if ($this->ElementType == TextElementType::Parameter) 
		{
			$pclass = $this->BaseEvulator->EvulatorTypes->Param;
	
			if($pclass && class_exists($pclass))
			{

				$evulator = new $pclass($this->BaseEvulator);
				$vresult = $evulator->Render($this, $vars);
				$result->Result = $vresult->Result;
				if ($vresult->Result == TextEvulateResult::EVULATE_TEXT) {
					$result->TextContent .= $vresult->TextContent;
				}
				$result->Result = TextEvulateResult::EVULATE_TEXT;

				return $result;
			}
			return null;
		}
		if ($end == 0) $end = $this->SubElementsCount;
		for ($i = $start; $i < $end; $i++) {
			$subElement = $this->SubElements[$i];
			//$className = $subElement->ElemName . 'Evulator';
			$className = '';
			if($subElement->ElemName != "#text")
			{
				$className = $this->BaseEvulator->EvulatorTypes[strtolower($subElement->ElemName)];
				if(!$className)
				{
					$className = $this->BaseEvulator->EvulatorTypes->GeneralType;
				}
			}
			if ($subElement->ElementType == TextElementType::Parameter) 
			{
				$className = $this->BaseEvulator->EvulatorTypes->Param;

			}
				
			if (!empty($className) && class_exists($className)) 
			{
				
				$evulatorObj = new $className($this->BaseEvulator);
				unset($vresult);
				$vresult = $evulatorObj->Render($subElement, $vars);
				if (!$vresult)
				{
					$evulatorObj->RenderFinish($subElement, $vars, $vresult);
					continue;
				}
				if ($vresult->Result == TextEvulateResult::EVULATE_DEPTHSCAN) {
					$nresult = $subElement->EvulateValue($vresult->Start, $vresult->End, $vars);
					unset($vresult);
					$vresult = &$nresult;
				}
				$evulatorObj->RenderFinish($subElement, $vars, $vresult);
				if(!$vresult) continue;
			
			}
			else 
			{
				$vresult = $subElement->EvulateValue(0, 0, $vars);
				if (!$vresult) continue;
				//$vresult = new TextEvulateResult();
				//$vresult->Result = TextEvulateResult::EVULATE_TEXT;
				//$vresult->TextContent = $subElement->Outer();
			}
			if ($vresult->Result == TextEvulateResult::EVULATE_TEXT) {
				$result->TextContent .= $vresult->TextContent;
			} 
			else if ($vresult->Result == TextEvulateResult::EVULATE_RETURN || $vresult->Result == TextEvulateResult::EVULATE_BREAK || $vresult->Result == TextEvulateResult::EVULATE_CONTINUE)
			{

				$result->Result = $vresult->Result;
				$result->TextContent .= $vresult->TextContent;
				break;
			}
		}
		return $result;
	}
	public function GetElementsHasAttributes($name, $depthscan = false, $limit = 0)
	{
		$elements = new TextElements();
		$lower = strtolower($name);
		for ($i = 0; $i < $this->SubElementsCount; $i++)
		{
			unset($elem);
			$elem = &$this->SubElements[$i];
			if (count($elem->ElemAttr) > 0 && $lower == "*")
			{
				$elements->Add($elem);
			}
			else
			{
				if ($elem->HasAttribute($lower))
				{
					$elements->Add($elem);
				}
			}
			if ($depthscan && $elem->SubElementsCount > 0)
			{
				$elements->AddRange($elem->GetElementsHasAttributes($name, $depthscan));
			}
		}
		return $elements;
	}
	public function GetElementsByTagName($name, $depthscan = false, $limit = 0)
	{
		$elements = new TextElements();
		$lower = strtolower($name);
		
		for ($i = 0; $i < $this->SubElementsCount; $i++)
		{
			unset($elem);
			$elem = &$this->SubElements[$i];
			if (strtolower($elem->ElemName) == $lower || $lower == "*")
			{
				$elements->Add($elem);

				if ($limit > 0 && count($elements) >= $limit)
				{
					break;
				}
			}
			if ($depthscan && $elem->SubElementsCount > 0)
			{
				$elements->AddRange($elem->GetElementsByTagName($name, $depthscan));
			}

		}
		return $elements;
	}
	public function GetElementsByPath($block)
	{
		$elements = new TextElements();
		for ($i = 0; $i < $this->SubElementsCount; $i++)
		{
			unset($subelem);
			$subelem = &$this->SubElements[$i];		
			if ($subelem->ElementType != TextElementType::ElementNode) continue;
			for ($j = 0; $j < count($block); $j++)
			{
				$curblock = $block[j];
				if ($curblock->IsAttributeSelector)
				{
					if ($curblock->BlockName == "*")
					{
						if (count($subelem->ElemAttr) == 0)
						{
							continue;
						}
					}
					else
					{
						if (!$subelem.HasAttribute($curblock->BlockName))
						{
							continue;
						}
					}
				}
				else
				{
					if ($curblock->BlockName != "*" && $curblock->BlockName != $subelem->ElemName)
					{
						continue;
					}
				}
				if (in_array($subelem, $elements) || (count($curblock->XPathExpressions.Count == 0) || XPathActions::XExpressionSuccess($subelem, $curblock->XPathExpressions)))
				{
					$element[] = &$subelem;
					XPathActions::Eliminate($elements, $curblock);
				}
				break;

			}
		}
		return $elements;
	}
	public function FindByXPathBlock($block)
	{
		$foundedElems = new TextElements();
		
		if ($block->IsAttributeSelector)
		{
			$foundedElems = $this->GetElementsHasAttributes($block->BlockName, $block->BlockType == \TextEngine\XPathBlockType::XPathBlockScanAllElem);
		}
		else
		{
			if (!empty($block->BlockName))
			{
				if ($block->BlockName == ".")
				{
					$foundedElems[] = &$this;
					return $foundedElems;
				}
				else if ($block->BlockName == "..")
				{
					$foundedElems[] = &$this->Parent;
					return $foundedElems;

				}
				else
				{
					
					$foundedElems = $this->GetElementsByTagName($block->BlockName, $block->BlockType == \TextEngine\XPathBlockType::XPathBlockScanAllElem);
				}
			}
		}
		if (count($block->XPathExpressions) > 0 && $foundedElems->GetCount() > 0)
		{

			for ($i = 0; $i < count($block->XPathExpressions); $i++)
			{
				unset($exp);
				$exp = &$block->XPathExpressions[$i];
			
				$foundedElems = \TextEngine\XPathActions::Eliminate($foundedElems, $exp);
	
				if ($foundedElems->GetCount() == 0)
				{
					break;
				}
			}
		}
		return $foundedElems;
	}

	public function FindByXPath($xpath)
	{
		$elements = new TextElements();
		$xpathItem = \TextEngine\XPathItem::ParseNew($xpath);
		$elements = $this->FindByXPathByBlockContainer($xpathItem->XPathBlockList);
		$elements->SortItems();
		return $elements;
	}
	private function FindByXPathByBlockContainer(&$container, &$senderitems = null)
	{
		$elements = new TextElements();
		$inor = true;
		for ($i = 0; $i < count($container); $i++)
		{
			unset($curblock);
			$curblocks = &$container[$i];
			if ($curblocks->IsOr())
			{
				$inor = true;
				continue;
			}
			if (!$inor)
			{

				if ($curblocks->IsBlocks())
				{
					$elements = $this->FindByXPathBlockList($curblocks, $elements);
				}
				else
				{
					$elements->AddRange($this->FindByXPathPar($curblocks, $senderitems));
				}
			}
			else
			{
				if ($curblocks->IsBlocks())
				{
					$elements = $this->FindByXPathBlockList($curblocks);
				}
				else
				{
					$elements = $this->FindByXPathPar($curblocks);
				}
			}
			$inor = false;
		}
		return $elements;
	}

	public function FindByXPathPar(&$xpar, $senderitems = null)
	{
		$elements = new TextElements();
		
		$elements = $this->FindByXPathByBlockContainer($xpar->XPathBlockList, $senderitems);
		if (count($xpar->XPathExpressions) > 0 && count($elements) > 0)
		{
			$elements->SortItems();
			for ($j = 0; $j < count($xpar->XPathExpressions); $j++)
			{
				unset($exp);
				$exp = &$xpar->XPathExpressions[$j];
				$elements = XPathActions::Eliminate($elements, $exp);
				if (count($elements) == 0)
				{
					break;
				}
			}
		}
		return $elements;
	}
	public function FindByXPathBlockList($blocks, $senderlist = null)
	{
		
		$elements = $senderlist;

		for ($i = 0; $i < $blocks->GetCount(); $i++)
		{
			unset($xblock);
			$xblock = &$blocks[$i];
			if ($i == 0 && $senderlist == null)
			{
				$elements = $this->FindByXPathBlock($xblock);
			
			}
			else
			{
				$elements = $elements->FindByXPath($xblock);
			}
		}
		return $elements;
	}
	public function FindByXPathOld($xpath)
	{
	
		$elements = new TextElements();
		$fn = new \TextEngine\XPathFunctions();
		$xpathblock = \TextEngine\XPathItem::Parse($xpath);
		$actions = new \TextEngine\XPathActions();
		$actions->XPathFunctions = new \TextEngine\XPathFunctions();
		for ($i = 0; $i < $xpathblock->XPathBlocks->GetCount(); $i++)
		{
			unset($xblock);
			unset($elements);
			$xblock = &$xpathblock->XPathBlocks[$i];
			if ($i == 0)
			{
				$elements = $this->FindByXPathBlock($xblock);
			}
			else
			{
				unset($newelements);
				$newelements = new TextElements();
				for ($j = 0; j < $elements->GetCount(); $j++)
				{
					unset($elem);
					$elem = &$elements[$j];
					$nextelems = $elem->FindByXPathBlock($xblock);
					for ($k = 0; $k < $nextelems->GetCount(); $k++)
					{
						if ($newelements->Contains($nextelems[$k])) continue;
						$newelements->Add($nextelems[k]);
					}
				}
				
				$elements = &$newelements;
			}
		}
		return $elements;
	}
	public function XPathSuccessSingle(&$block)
	{
		if ($this->ElementType != TextElementType::ElementNode || ($block->BlockName != "*" && $block->BlockName != $this->ElemName)) return false;
		if (count($block->XPathExpressions) > 0)
		{
			$myIndex = $this->Index();
			for ($i = 0; $i < count($block->XPathExpressions); $i++)
			{
				if (!XPathActions::XExpressionSuccess($this, $block->XPathExpressions[$i], null, $myIndex)) return false;
			}
		}
		return true;
	}	
}
class TextEvulateResult
{
	const EVULATE_NOACTION = 0;
	const EVULATE_TEXT = 1;
	const EVULATE_CONTINUE = 2;
	const EVULATE_RETURN = 3;
	const EVULATE_DEPTHSCAN = 4;
	const EVULATE_BREAK = 5;

	public $TextContent;
	public $Result;
	public $Start = 0;
	public $End = 0;
}
