## Template Engine Usage

```php
            $evulator = new TextEvulator();
            $data = array();
            $data["is_loaded"] = true;
            $data["str_data"] = "string data";
            $data["int_data"] = 12345;
            //User can change Left and Right tag.
            //$evulator->LeftTag = '[';
            //$evulator->RightTag = ']';
            $evulator->GlobalParameters = &$data;
            $evulator->Text = "{if is_loaded}Loaded{/if} string data: {%str_data}, int data: {%int_data}";
            //Parse content.
            $evulator->Parse();
            //Evulate all.
            $result = evulator->Elements->EvulateValue();
            //Output: Loaded string data: string data, int data: 12345
            $resultStr = $result->TextContent;
```


## ParFormat Usage
```php
            //Long usage
            $pf = new ParFormat();
            $kv = array();
            $kv["name"] = "MacMillan";
            $kv["grup"] = "AR-GE";
            $kv["random"] = function() {
				return rand(1, 100);
			};
            $pf->SurpressError = true;
            $pf->Text = "User: {%name}, Group: {%grup}, Random Number: {%random()}";

            //Short usage
            ParFormat::Format("User: {%name}, Group: {%grup}, Number Sayı: {%random()}", $kv);


            //Output  User: MacMillan, Group: AR-GE, Random Number: 61
            $res = $pf->Apply($kv);
```

# Evulators

## NoPrintEvulator
```php
            $evulator = new TextEvulator();
            //NoPrint inner is evulated bu not print to result.
			$evulator->Text = "{NOPRINT}.............{/NOPRINT}";
```

## CM(Call Macro)Evulator and MacroEvulator
```php
            $evulator = new TextEvulator();
            //evulator->Text = "{noprint}{macro name=macroname}{/macro}{noprint}{cm macroname}";
            $evulator->ParamNoAttrib = true;
            $evulator->Text = "{noprint}{macro name=macro1}This is macro line, param1: {%param1}, param2: {%param2}\r\n{/macro}{/noprint} {cm macro1}{cm macro1 param1=\"'test'\" param2=123456}";
            $evulator->Parse();
            //Output: This is macro line, param1: , param2: \r\nThis is macro line, param1: test, param2: 123456\r\n
            $result = $evulator->Elements->EvulateValue()->TextContent;
```


## ForEvulator, ContinueEvulator And BreakEvulator Usage
```php
            $evulator = new TextEvulator();
            //evulator->Text = "{FOR var=i start=0 step=1 to=5}Current Step: {%i}{/FOR}";
            $kv = array();
            $kv["name"] = "TextEngine";
            $evulator->GlobalParameters = &$kv;
            $evulator->Text = "{FOR var=i start=0 step=1 to='name.Length'}{%name[i]}{if i == 4}{continue}{/if}{if i==7}{break}{/if}-{/FOR}";
            $evulator->ParamNoAttrib = true;
            $evulator->Parse();
            //Output: "T-e-x-t-En-g-i"
            $result = $evulator->Elements->EvulateValue()->TextContent;
```

## ForeachEvulator Usage
```php
            $evulator = new TextEvulator();
            //evulator->Text = "{FOREACH var=item in=list}{/FOR}";
			$kv = array();
            $kv["list"] = ["item1", "item2", "item3"];
            $evulator->GlobalParameters = &$kv;
            $evulator->Text = "{FOREACH var=current in=list}{%current}\r\n{/FOREACH}";
            $evulator->ParamNoAttrib = true;
            $evulator->Parse();
            //Output: item1\r\nitem2\r\nitem3\r\n
            $result = $evulator->Elements->EvulateValue()->TextContent;
```
## IfEvulator Usage
```php
            $evulator = new TextEvulator();
            //evulator->Text = "{IF statement}true{elif statement}elif true{/elif}{else}false{/IF}";
			$kv = array();
            $kv["status"] = 3;
            $evulator->GlobalParameters = &$kv;
            $evulator->ParamNoAttrib = true;
            $evulator->Text = "{IF status==1}status = 1{ELIF status == 2}status = 2 {ELSE}status on else, value: {%status}{/IF}";
            $evulator->Parse();
            //Output: status on else, value: 3
            $result = evulator->Elements->EvulateValue()->TextContent;
```

## IncludeEvulator Usage
```php
            $evulator = new TextEvulator();
            $evulator->Text = "{include name=\"'path'\" xpath='optional' parse='true or false(as text)'";
			$evulator->Parse();
            $result = $evulator->Elements->EvulateValue()->TextContent;
```

## RepeatEvulator Usage
```php
            $evulator = new TextEvulator();
            $evulator->Text = "{repeat current_repeat='cur' count=2}Current Repat: {%cur}{if cur == 0}-{/if}{/repeat}";
            $evulator->Parse();
            //Output: "Current Repat: 0-Current Repat: 1"
            $result = evulator->Elements->EvulateValue()->TextContent;
```

## ReturnEvulator Usage
```php
            $evulator = new TextEvulator();
            $evulator->Text = "Test variable, test variable 2 {if !test}{return}{/if} test variable 3";
            $evulator->Parse();
            //Output: Test variable, test variable 2 
            $result = evulator->Elements->EvulateValue()->TextContent;
```


## SetEvulator And UnsetEvulator
```php
            $evulator = new TextEvulator();
            $kv = array();
            $kv["status"] = true;
            $kv["total"] = 5;
            $evulator->GlobalParameters = &$kv;
            $evulator->Text = "{set if=status name=variable value='total * 2'} variable is: {%variable}, {unset name=variable}\r\nvariable is: {%variable}";
            $evulator->ParamNoAttrib = true;
            $evulator->Parse();
            //Output: " variable is: 10, \r\nvariable is: "
            $result = $evulator->Elements->EvulateValue()->TextContent;
```


## SwitchEvulator
```php
            $evulator = new TextEvulator();
            $kv = array();
            $kv["total"] = 2;
            $evulator->GlobalParameters = &$kv;
            $evulator->Text = "{switch c=total}
                                {case v=1}Value 1{/case}
                                {case v=2}Value 2{/case}
                                {default}Default Value{/default}
                                {/switch}";
            $evulator->ParamNoAttrib = true;
            $evulator->Parse();
            //Output: Value 2
            $result = $evulator->Elements->EvulateValue()->TextContent;
```


## PhpEvulator Text Style
```php
            $evulator = new TextEvulator();
			//evulator->Text = "{PHP @text=1}{/PHP}";
            $kv = array();
            $kv["name"] = "TextEngine";
            $kv["value"] = "1234";
            $evulator->GlobalParameters = &$kv;
            $evulator->Text = "";
            $evulator->ParamNoAttrib = true;
			//Firs activate PHP evulator
			$evulator->TagInfos["php"]->Flags = TextElementFlags::TEF_NoParse;
			$evulator->EvulatorTypes["php"] = "PHPEvulator";
			$evulator->Text = '{PHP @text=1 name="name" value="value" custom=10}this is text style PHPEvulator: Name=$name, Value=$value, Custom: $custom{/PHP}';
            $evulator->Parse();
            //Output: this is text style PHPEvulator: Name=TextEngine, Value=1234, Custom: 10
            $result = $evulator->Elements->EvulateValue()->TextContent;
```

## PhpEvulator Code Style
```php
            $evulator = new TextEvulator();
			//evulator->Text = "{PHP}$text = 'your content';{/PHP}";
            $kv = array();
            $kv["name"] = "TextEngine";
            $kv["value"] = "1234";
            $evulator->GlobalParameters = &$kv;
            $evulator->Text = "";
            $evulator->ParamNoAttrib = true;
			//Firs activate PHP evulator
			$evulator->TagInfos["php"]->Flags = TextElementFlags::TEF_NoParse;
			$evulator->EvulatorTypes["php"] = "PHPEvulator";
			$evulator->Text = '{PHP name="name" value="value" custom=10}
				$text = "Name=$name<br>Value=$value";
				if(isset($custom))
				{
					$text .= "<br>" . $custom;
				}
			{/PHP}';
            $evulator->Parse();
            //Output: this is text style PHPEvulator: Name=TextEngine<br>Value=1234<br>Custom: 10
            $result = $evulator->Elements->EvulateValue()->TextContent;
```
