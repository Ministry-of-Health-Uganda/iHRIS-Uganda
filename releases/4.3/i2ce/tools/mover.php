<?php

$template_dir = "./translations/templates";
$usage[] = 
    "[--template_dir=\$template_dir]: The directory to store .pot template files in\n"
   ."\tIf not set, we use $template_dir\n" ;
$usage[] = 
    "[--remove-strings=T/F] set to true to always remove the string from a module's .pot\n"
    ."\twhich are no longer present in the module\n";
require_once ("translate_base.php");  

if (array_key_exists('template_dir',$args)) {
    $template_dir = $args['template_dir'];    
}


if (!is_dir($template_dir)) {
    if (!mkdir($template_dir,0775,true)) {
        usage("Could not make $template_dir");
    }    
}
//fixup permissions if set badly before
chmod($template_dir,0775);
if ( !is_writeable($template_dir)) {
    usage("The direcotory $locales_dir is not a readable directory");
}
$template_dir = realpath($template_dir);


$configs = getTranslatableConfigs(true); //this will just create any en_US that we need to
$templates = getTranslatableDocuments();


foreach ($found_modules  as $module=>$top_module) {
    if (!array_key_exists($module,$configs) && !array_key_exists($module,$templates)) {
        continue;
    }
    $old_pot_file = $template_dir . DIRECTORY_SEPARATOR . $module . '.pot';
    if (!is_readable($old_pot_file)) {
        I2CE::raiseError("$old_pot_file does not exist.");
        continue;
    }
    $launch_module = launchpad($module);
    $pot_dir =  $template_dir . DIRECTORY_SEPARATOR . $launch_module;
    if (!is_dir($pot_dir)) {
        if (!mkdir($pot_dir,0775,true)) {
            usage("Could not make $pot_dir");
        }    
    }
    $pot_file = $pot_dir . DIRECTORY_SEPARATOR . $launch_module . '.pot';
    //echo "bzr mv $old_pot_file $pot_file\n"; continue;
    I2CE::raiseError(shell_exec("bzr add  $pot_dir"));
    I2CE::raiseError(shell_exec("bzr mv $old_pot_file $pot_file"));
}