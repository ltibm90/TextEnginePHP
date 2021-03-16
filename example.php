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

function TemplateTest()
{
	$pf = new ParFormat();
	$kv["name"] = "MacMillan";
	$kv["grup"] = "AR-GE";
	$kv["random"] = function() {return rand(1, 100);};
	$pf->Text = 'ParFormat örneği; Kullanıcı: {%name}, Grup: {%grup}, Random Sayı: {%random()}';
	echo $pf->Apply($kv);




	$globalInfo = new stdClass();
	$globalInfo->title = "Cyber-Warrior User Info";
	$globalInfo->footer = "<b>Ana Sayfa</b>";
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
}
TemplateTest();



