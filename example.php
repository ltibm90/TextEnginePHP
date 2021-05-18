<?php
require_once "TextEngine/TextEngine.php";



function TextEngineOrnek1()
{
	$te = new TextEvulator("{tag}içerik{/tag}");
	$te->Parse();
	print_r($te->Elements->EvulateValue()->TextContent);
	
}

function TextEngineOrnek2()
{
	$te = new TextEvulator("{tag}içerik: <b>{%mesaj}</b>{/tag}");
	$te->GlobalParameters =  array('mesaj' => 'Deneme');
	$te->Parse();
	print_r($te->Elements->EvulateValue()->TextContent);
	
}


function TextEngineOrnek3()
{
	$te = new TextEvulator("{tag}içerik: <b>{%mesaj}</b>{/tag}");
	$cobj = new stdClass();
	$cobj->mesaj = "Deneme";
	$te->GlobalParameters =  &$cobj;
	$te->Parse();
	print_r($te->Elements->EvulateValue()->TextContent);
	
}
function TextEngineOrnek4()
{
	$te = new TextEvulator("{tag}içerik: <b>{%'Mesaj: ' + mesaj + ', Uzunluk: ' + strlen_cw(mesaj)}</b>{/tag}");
	$te->ParamNoAttrib = true;
	$cobj = new stdClass();
	$cobj->strlen_cw = function($var1) {return strlen($var1);};
	$cobj->mesaj = "Deneme";


	$te->GlobalParameters =  &$cobj;
	$te->Parse();
	print_r($te->Elements->EvulateValue()->TextContent);
	
}

class CyberWarrior
{
	public $CurrentGroup = "AR-GE";
	public function CurrentMember()
	{
		return "MacMillan";
	}
}
function TextEngineOrnek5()
{
	$te = new TextEvulator("Mevcut Grup: {%CurrentGroup}, Mevcut Üye: {%CurrentMember()}");
	$te->ParamNoAttrib = true;
	$cobj = new CyberWarrior();

		
	$te->GlobalParameters =  &$cobj;
	$te->Parse();
	print_r($te->Elements->EvulateValue()->TextContent);
	
}


class ArrayTest
{
	function TestJoin($array, $joinchar)
	{
		$text = '';
		foreach ($array as &$cur) {
			if(!empty($text)) $text .= $joinchar;
			$text .= $cur;
		}

		return $text;
	}
}
function TextEngineOrnek6()
{
	$te = new TextEvulator("Sonuç: {%TestJoin(['C', 'y', 'b', 'e', 'r'], '*')}");
	$te->ParamNoAttrib = true;
	$cobj = new ArrayTest();


		
	$te->GlobalParameters =  &$cobj;
	$te->Parse();
	print_r($te->Elements->EvulateValue()->TextContent);
	
}



class UserInfo
{
	public $Name;
	public $RegisterDate;
	public $Informations;
	function  __construct ()
	{
		$this->Name = "macmillan";
		$this->RegisterDate = "01.01.2020";
		$this->Informations["Mesajlar"] = 1000;
		$this->Informations["Konular"] = 100;
		$this->Informations["Rep Puanı"] = 25;
	}
	public function GetReferrer()
	{
		return "XÜye";
	}
	public function GetGroup()
	{
		return "AR-GE";
	}
	public function HasCustomInformations()
	{
		return is_array($this->Informations) && count($this->Informations) > 0;
	}
}
class WhileTestClass
{
	public $Items;
	public $Position;
	function  __construct ()
	{
		$this->Items = [];
		$this->Position = -1;

	}
	function Next()
	{
		return ++$this->Position < count($this->Items);
	}

    function Get()
	{
		return $this->Items[$this->Position];
	}
}


function TemplateTest()
{
	$pf = new ParFormat();
	$kv["name"] = "MacMillan";
	$kv["grup"] = "AR-GE";
	$kv["random"] = function() {return rand(1, 100);};
	$pf->Text = 'ParFormat örneği; Kullanıcı: {%name}, Grup: {%grup}, Random Sayı: {%random()} {{%1.25 + 2.75} == {%1.25 + 2.75}';
	echo $pf->Apply($kv);

	$wtc = new WhileTestClass();
	$wtc->Items [] = "Item1";
	$wtc->Items [] = "Item2";
	$wtc->Items [] = "Item3";
	$wtc->Items [] = "Item4";
	$wtc->Items [] = "Item5";
	$wtc->Items [] = "Item6";

	$globalInfo = new stdClass();
	$globalInfo->title = "Cyber-Warrior User Info";
	$globalInfo->footer = "<b>Ana Sayfa</b>";
	$globalInfo->whileItems = $wtc;
	$userInfo = new UserInfo();
	$globalInfo->currentUser = new stdClass();
	$globalInfo->currentUser->Access = 1;
	$globalInfo->OnlineUsers = ["MacMillan", "Üye2", "Üye3", "Üye4"];
	$te = new TextEvulator(__DIR__ . "/template.html", true);
	
	//$te->SetDir(__DIR__);
	$globalInfo->user = &$userInfo;
	$te->GlobalParameters = &$globalInfo;
	$te->ParamNoAttrib = true;
	$te->NoParseEnabled = true;
	$te->TagInfos["php"]->Flags = TextElementFlags::TEF_NoParse;
	$te->EvulatorTypes["php"] = "PHPEvulator";
	//$te->LeftTag = '[';
	//$te->RightTag = ']';
	$te->Parse();
	print_r($te->Elements->EvulateValue()->TextContent);
	
	

	echo "<br><br><b>Örnek Atama İşlemi</b></br>";
	echo "Başlangıçtaki değeri";
	$tac = new TestAssignClass();
	echo "<pre>";
	print_r($tac);
	echo "</pre>";
	$pdText = "{%Prop1 = 1} \r\n{%Prop2 = Prop1 + 4}\r\n{%Prop4[0] = 'item changed'}\r\n{%Prop3 = [1, 2, 3, 'named' => 4]}\r\n{%Prop5 = {item: 'value1', item2: 'value2', item3: 5}}\r\n{%Prop5.item3 += 10}";
	$pr = ParFormat::FormatEx($pdText, $tac, function($attr) {
		$attr->Flags |= PardecodeFlags::PDF_AllowAssigment;
	});
	echo "<br><br><b>ParFormat İle Girilen Metin </b></br>";
	echo "<pre>$pdText</pre>";
	echo "<br><br><b>Atama İşleminden Sonraki Değer </b></br>";
	echo "<pre>";
	print_r($tac);
	echo "</pre><br><br>";
	
	echo "<br>TextEvulator satır satır komut işlemi ile kullanılarak yapılan bir örnek";
	echo "Kod: <pre>";
	echo '$tac2 = new TestAssignClass();
	$tac2->Prop1 = 1;
	$tac2->Prop2 = 2;
	$tac2->Prop3 = 3;<br><br>';
	echo '$te = new TextEvulator("Prop1 = Prop2 + 1\r\nProp2 = Prop3 + 1\r\nProp3 = Prop1 + Prop2");
	$te->GlobalParameters = &$tac2;
	$te->ApplyCommandLineByLine();
	$res = $te->EvulateValue();';
	echo "</pre>";
	
	echo '<pre>print_r($tac2);</pre><br><b>Sonuç</b>';
	
	$tac2 = new TestAssignClass();
	$tac2->Prop1 = 1;
	$tac2->Prop2 = 2;
	$tac2->Prop3 = 3;
    $te = new TextEvulator("Prop1 = Prop2 + 1\r\nProp2 = Prop3 + 1\r\nProp3 = Prop1 + Prop2");
	$te->GlobalParameters = &$tac2;
	$te->ApplyCommandLineByLine();
	$res = $te->EvulateValue();
	echo "<pre>";
	print_r($tac2);
	echo "</pre>";
	
	
	

}
class TestAssignClass
{
	public $Prop1;
	public $Prop2;
	public $Prop3;
	public $Prop4 = array("item", "item2");
	public $Prop5;
}
TemplateTest();



