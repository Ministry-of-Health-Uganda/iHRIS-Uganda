#!/usr/bin/php
<?php
/*
 * © Copyright 2007, 2008 IntraHealth International, Inc.
 * 
 * This File is part of iHRIS
 * 
 * iHRIS is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
/**
 * Apache Tail
 *
 * @package I2CE
 * @subpackage Core
 * @author Carl Leitner <litlfred@ibiblio.org>
 * @copyright Copyright &copy; 2008, 2008 IntraHealth International, Inc. 
 * @version 1.0
 */
require_once ("Console/Getopt.php");  
$cg = new Console_Getopt();  
$args = $cg->readPHPArgv(); 

array_shift($args );
if (count($args) == 0 ) {
    echo "Usage: " . basename(__FILE__) ." file.php\nHere file.php is a class which extends I2CE_Form\n"; 
    die();
}


$config = new DOMDocument();
$xpath = new DOMXPath( $config);
$topNode = createConfigGroup('formClasses','Form Class Configuration','/modules/forms/formClasses');
$config->appendChild($topNode);
$red = "\033[31m";
$black = "\033[30m";
$phpfunc = '[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*';

foreach ($args as $file) {
    if(!file_exists($file) || !is_readable($file)) {
        echo "Cannot read the file $file\n";
        die();
    }
    echo "Generating form field configuration xml for $file.\n";
    $contents = file($file);
    $classNode = null;
    $fieldsNode = null;
    foreach ($contents as $line) {
        if (check_for_class_name($line)) {
            continue;
        }
        if (check_for_add_field($line)) {
            continue;
        }
        if (check_for_set_map($line)) {
            continue;
        }
    }    
}

$text = $config->saveXML($topNode);
$tidy = new tidy();
$config = array(
    'input-xml'=>true,
    'output-xml'=>true,
    'indent'=>true,
    'wrap'=>0,
    );
$tidy->isXML();
$tidy->parseString($text,$config,'UTF8');
$tidy->cleanRepair();
file_put_contents("config_formClass.xml",  tidy_get_output($tidy) . "\n");


function check_for_add_field($line) {
    global $config;
    global $red;
    global $black;
    global $classNode;
    global $fieldsNode;
    if (!preg_match('/^\s*\$this->addField\(\s*(.*)\s*\);\s*$/',$line,$matches)) {
        if (preg_match('/addField/',$line)) {
            echo "{$red}Found addField() but don't know how to deal with it at:{$black}\n\t$line\n";
        }
        return false;
    }
    $fields = explode(',',$matches[1]); //we hope that there are no commas in the names
    if (count($fields) < 2) {
        echo "{$red}Too few arguements at:{$black}\n\t$line\n";
        return false;
    }
    if (count($fields) > 5) {
        echo "{$red}Too many arguements at:{$black}\n\t$line\n";
        return false;
    }
    for ($i=0; $i<min(2,count($fields)); $i++) {
        if (!remove_quotes($fields[$i])) {
            echo "{$red}Problem removing quotes $i:{$black}\n\t$line\n";
            return false;
        }
    }
    $node = createConfigGroup($fields[1],'The field \'' .$fields[1] . "'");
    if ($fieldsNode === null) {
        $fieldsNode = createConfigGroup('fields','The fields defined for this form');
        $classNode->appendChild($fieldsNode);            
    }
    $fieldsNode->appendChild($node);
    $valNode = createConfig('formfield','The form field type',$fields[0]);
    $node->appendChild($valNode);
    if (count($fields) >=3) {
        $valNode = createConfig('in_db','Store the field in the database',$fields[2],null,'boolean');
        $node->appendChild($valNode);
    }
    if (count($fields) >=4) {
        $valNode = createConfig('required','This field is required to be set',$fields[3],null,'boolean');
        $node->appendChild($valNode);
    }
    if (count($fields) == 5) {
        $valNode = createConfig('unique','This field is required to be unique',$fields[3],null,'boolean');
        $node->appendChild($valNode);
    }
    $createdFields = true;
    return true;
}

function check_for_set_map($line) {
    global $config;
    global $red;
    global $black;
    global $xpath;
    global $classNode;
    if (!preg_match('/^\s*\$this\->fields\[\'(.*)\'\]\->setMap\(\s*(.*)\s*\);\s*$/',$line,$matches)) {
        if (preg_match('/setMap/',$line)) {
            echo "{$red}Found setMap() but don't know how to deal with it at:{$black}\n\t$line\n";
        }
        return false;
    }
    $args = array();
    $matches[2] = trim($matches[2]);
    if (!empty($matches[2])) {
        $args = explode(',',$matches[2]);
    }
    if (count($args) > 3) {
        echo "{$red}Too many arguements at:{$black}\n\t$line\n";
    }    
    for ($i=0; $i < count($args); $i++) {
        if (!remove_quotes($args[$i])) {
            echo "{$red}Problem removing quotes $i:{$black}\n\t$line\n";
            echo count($args);
            return false;
        }
    }
    $fieldNode= $xpath->query('./configurationGroup/configurationGroup[@name="'. $matches[1] . '"]',$classNode);
    if ($fieldNode->length !== 1) {
        echo "{$red}Cannot deteremine the node for the field ". $matches[1] . "for:{$black}\n\t$line\n";
        echo "\tFound " . $fieldNode->length . " nodes\n";
        return false;
    }
    $fieldNode = $fieldNode->item(0);
    $node = createConfigGroup('setMap','Configuration on mapping the field values');
    $fieldNode->appendChild($node);
    $valNode=createConfig('useMap','Whether or not to use a map','true',null,'boolean');
    $node->appendChild($valNode);
    if (count($args) >=1 ) {
        $valNode=createConfig('form','The form to use',$args[0]);
        $node->appendChild($valNode);
    }
    if (count($args) >=2 ) {
        $valNode=createConfig('lookup_func','The function to lookup values with',$args[1]);
        $node->appendChild($valNode);
    }
    if (count($args) ==3 ) {
        $valNode=createConfig('list_func','The function to list values with',$args[2]);
        $node->appendChild($valNode);
    }
    return true;
}




function check_for_class_name($line) {
    global $config;
    global $red;
    global $black;
    global $phpfunc;
    global $classNode;
    global $topNode;
    if (!preg_match("/class\s*($phpfunc)\s*extends\s*($phpfunc)/",$line,$matches)) {
        return false;
    }
    $classNode = createConfigGroup($matches[1],"Configuration for the class '" . $matches[1] . "'");    
    $topNode->appendChild($classNode);
    $valNode = createConfig('extends','The class this form extends',$matches[2]);
    $classNode->appendChild($valNode);
    return true;
}


function remove_quotes(&$string) {
    if (!preg_match('/^\s*["\'](.*)["\']\s*$/',$string,$matches)) {
        echo "Unable to remove quotes for ($string)\n";
        return false;
    }
    $string = $matches[1];     
    if (strlen($string) == 0) {
        echo "Warning: unquoted to zero length string\n";
    }
    return true;
}

function human_text($text) {
    return ucwords(preg_replace('/[-_]/',' ',$text ));
}

function createConfigGroup($name,$display,$path=null) {
    global $config;
    $node = $config->createElement('configurationGroup');
    $node->setAttribute('name',$name);
    if ($path !== null) {
        $node->setAttribute('path',$path);
    }
    $DNnode = $config->createElement('displayName');
    $DNnode->appendChild($config->createTextNode($display));
    $node->appendChild($DNnode);
    return $node;
}

function createConfig($name,$display,$value=null,$path=null,$type=null) {
    global $config;
    $node = $config->createElement('configuration');
    $node->setAttribute('name',$name);
    if ($path !== null) {
        $node->setAttribute('path',$path);
    }
    if ($type !== null) {
        $node->setAttribute('type',$type);
    }
    $DNnode = $config->createElement('displayName');
    $DNnode->appendChild($config->createTextNode($display));
    $node->appendChild($DNnode);
    if ($value !== null) {
        $Valnode = $config->createElement('value');
        $Valnode->appendChild($config->createTextNode($value));
        $node->appendChild($Valnode);
    }
    return $node;
}

