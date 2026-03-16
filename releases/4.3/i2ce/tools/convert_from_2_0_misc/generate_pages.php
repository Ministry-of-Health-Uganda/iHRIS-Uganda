<?php
$pages = file('pages');
$phpfunc = '[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*';
//application.php:$page = new iHRIS_PageFormParentPerson( "Add/Update Application", "application", 'hr_staff' );
echo "\t<configurationGroup name='page'>\n";
echo "\t  <displayName> Pages</displayName>\n";
echo "\t  <description> Information about various pages made available by the system</description>\n";
echo "\t  <status>required</status>\n";
foreach ($pages as $page) {  
    if (!preg_match('/^([a-zA-Z0-9_]*)\.php/',$page,$matches)) {
        die( "No shortname found\n$page");
    }
    $shortname = $matches[1];
    if (!preg_match('/new\s*(' . $phpfunc . ')/',$page,$matches)) {
        die( "No class name found\n$page");
    }
    $classname = $matches[1];
    if (!preg_match('/\((.*)\);/',$page,$matches)) {
        die( "No options found\n$page");
    }
    $options = explode(',',trim($matches[1]));
    foreach ($options as $index=>$option) {
        $options[$index] = trim($option);
    }
    if (count($options) < 2) {
        die ("Not enough options found\n$page");
    }
    if (count($options) > 3) {
        die ("Too many options found -- deal with it manually\n$page");
    }
    $args = array();
    if (!preg_match('/^[\'\"]??(.*)[\'\"]?$/U',$options[0],$matches)) {
        die ("Title {$option[0]} is not valid\n$page");
    }
    $options[0] = $matches[1];
    if (!preg_match('/^[\'\"]??(.*)[\'\"]?$/U',$options[1],$matches)) {
        die ("defaultHTMLFile {$option[1]} is not valid\n$page");
    }
    $options[1] = $matches[1];
    if (!isset($options[2])) {
        $options[2] = array('all');
    }  else {
        $options[2] = explode('|',$options[2]);
        foreach ($options[2] as $index=>$access) {
            $options[2][$index] = str_replace('\'','',$access);
        }
    }
        
    echo "\t\t<configurationGroup name='$shortname'>\n"; 
    echo "\t\t\t<displayName>" . ucwords($shortname) . " Page </displayName>\n";
    echo "\t\t\t<description> The page '" .$shortname . "' which has the action of: ".$options[0] ."</description>\n";
    echo "\t\t\t<configuration name='class' values='single'>\n";
    echo "\t\t\t\t<displayName>Page Class</displayName>\n";
    echo "\t\t\t\t<description>The class responsible for displaying this page</description>\n";
    echo "\t\t\t\t<status>required</status>\n";
    echo "\t\t\t\t<value>" . $classname . "</value>\n";
    echo "\t\t\t</configuration>\n";
    echo "\t\t\t<configurationGroup name='args'>\n";
    echo "\t\t\t\t<displayName>Page Options</displayName>\n";
    echo "\t\t\t\t<description>The options that control the access and display of all pages</description>\n";
    echo "\t\t\t\t<configuration name='title' values='single'>\n";
    echo "\t\t\t\t\t<displayName>Page Title</displayName>\n";
    echo "\t\t\t\t\t<description>Page Title</description>\n";
    echo "\t\t\t\t\t<status>required</status>\n";
    echo "\t\t\t\t\t<value>" . $options[0] . "</value>\n";
    echo "\t\t\t\t</configuration>\n";
    echo "\t\t\t\t<configuration name='defaultHTMLFile' values='single'>\n";
    echo "\t\t\t\t\t<displayName>Default HTML File</displayName>\n";
    echo "\t\t\t\t\t<description>The default HTML File for this page</description>\n";
    echo "\t\t\t\t\t<status>required</status>\n";
    echo "\t\t\t\t\t<value>" . $options[1] . "</value>\n";
    echo "\t\t\t\t</configuration>\n";
    echo "\t\t\t\t<configuration name='access' values='many'>\n";
    echo "\t\t\t\t\t<displayName>Page Access</displayName>\n";
    echo "\t\t\t\t\t<description>All of the roles that have access to this page</description>\n";
    echo "\t\t\t\t\t<status>optional</status>\n";
    foreach ($options[2] as $access) {
        echo "\t\t\t\t\t<value>$access</value>\n";
    }
    echo "\t\t\t\t</configuration>\n"; 
    echo "\t\t\t\t<configuration name='files' values='many'>\n";
    echo "\t\t\t\t\t<displayName>Template Files</displayName>\n";
    echo "\t\t\t\t\t<description>All the template files that should be loaded for this page</description>\n";
    echo "\t\t\t\t\t<status>optional</status>\n";
    echo "\t\t\t\t</configuration>\n";
    echo "\t\t\t</configurationGroup>\n";  //end of args
    echo "\t\t</configurationGroup>\n";  //end of $shortname
} 
echo "\t</configurationGroup>\n";  



# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
