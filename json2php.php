<?php
if ( $argc != 2 ) {
    die("Usage: php xsd2class <file>\n");
}
$file = file_get_contents($argv[1]);

$json = json_decode($file);

// We assume $json starts by representing an object
processJSONObject($argv[1], $json);

function processJSONObject($name, $json)
{
    $fp = fopen($name.'.class.php', 'w');
    fwrite($fp, generateFileComment());
    fwrite($fp, generateClassComment($name));
    fwrite($fp, "class $name\n{\n");
    fwrite($fp, processProperties($json));
    
    fwrite($fp, "}");
    fclose($fp);
}
print_r($json);

function processProperties($json)
{
	$properties = '';
	$docComments = '';
	$params = '';
	$validations = '';
	foreach( $json->properties as $name=>$property ) {
		$properties .= "    public \$$name;\n";
		$docComments .= "     * @param $property->type \$$name $property->description\n";
		$params .= "\$$name, ";
		// Finally if it's an object we want to process it too
		if( 'object' == $property->type ) {
			processJSONObject($name, $property);
		}
	}
	
	$docComments = "    /**\n$docComments     */\n";
	$constructor = "    public __construct(" . substr($params, 0, -2) . ")\n    {\n";
	$constructor = $constructor . $validations . "    }\n";
	
	return $properties . $docComments . $constructor;
}

function generateFileComment()
{
    return <<<FILECOMMENT
/**
 * Generated by json2php
 *
 * PHP version 5.3
 *
 * @category Util
 * @package  json2php
 * @author   David Gillen <david.gillen@nexus451.com>
 * @license  GNU GPL - http://www.gnu.org/licenses/gpl.txt
 * @link     https://github.com/davidgillen/XSD2PHP
 */\n
FILECOMMENT;
}

function generateClassComment($name, $json)
{
    return <<<CLASSCOMMENT
/**
 * Class representing a $name
 * {$json->description}
 *
 * @category json2php
 * @package  json2php
 * @author   David Gillen <david.gillen@nexus451.com>
 * @license  GNU GPL - http://www.gnu.org/licenses/gpl.txt
 * @link     http://www.nexus451.com
 */\n
CLASSCOMMENT;
}