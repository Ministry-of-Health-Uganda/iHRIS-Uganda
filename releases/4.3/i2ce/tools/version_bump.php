<?php

require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . "CLI.php" );
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . "base.php" );


$base_vers = false;
if (simple_prompt("Is this a minor version bump (e.g. 4.0.5.X -> 4.0.6.0)?")) {
    $bump_type = 2;
    $base_vers = ask("What is the target minor version (e.g. 4.0.6)?");
} else  if (simple_prompt("Is this a major version bump (4.0.X -> 4.1.0)?")) {
    $bump_type = 1;
    $base_vers = ask("What is the target major version (e.g. 4.1)?");
} else  if (simple_prompt("Is this a fanastic version bump (4.X -> 5.0)?")) {
    $bump_type = 0;
    $base_vers = ask("What is the target fantastic version (e.g. 5)?");
} else {
    die("I don't know what you want to do\n");
}

$base_vers = trim($base_vers);
$base_vers_comps = explode('.',$base_vers);
if (count($base_vers_comps) != $bump_type +1) {
    die("Invalid target version ($base_vers)\n");
}

$check_vers_comps = $base_vers_comps;
$check_vers_comps[$bump_type]--;
$check_vers =  implode('.',$check_vers_comps); 

$check_short_vers = implode('.',array_slice($base_vers_comps,0,$bump_type));



$new_vers_comps = $base_vers_comps;
$new_vers_comps[] = '0';
$new_vers = implode('.',$new_vers_comps);



$found_modules = array_keys(getAvailableModules());
$always_update = array();
foreach ($found_modules as $module) {
    $mod_file = false;
    $storage->setIfIsSet($mod_file,"/config/data/$module/file");
    if (!$mod_file) {
        I2CE::raiseError("No file recored for $module");
        continue;
    }
    $loc_files = glob (dirname($mod_file) . DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR . basename($mod_file));
    $loc_files[] = $mod_file;
    foreach ($loc_files as $file) {
        $template = new I2CE_TemplateMeister();
        if (!$template->loadRootFile($file)) {
            echo "\tCould not load\n";
            continue;
        }        
        if (( ! ($res = $template->query('/I2CEConfiguration/metadata/version')) instanceof DOMNodeList) || ($res->length == 0)) {
            echo "\tVersion not found\n";
            continue;
        }
        $versNode = $res->item(0);
        $vers = trim($versNode->textContent);
        $vers_comps = explode('.',$vers);
        //first we check to see if the major/minor/fanastic versions match.  if not,we don't update.
        $exis_vers = implode('.',array_slice(array_pad($vers_comps,$bump_type+1,0),0,$bump_type+1));
        if ($exis_vers != $check_vers) {
            //if the base versions and e
            if ($check_short_vers && I2CE_Validate::checkVersion($vers ,'greaterThan',$check_short_vers) && I2CE_Validate::checkVersion($vers, 'lessThan',$base_vers)) {
                if (!array_key_exists($module,$always_update)) {
                    $always_update[$module] = null;
                }
                if (!prompt("Would you like to update $module from $vers to $new_vers?",$always_update[$module])) {
                    continue;
                }
            } else {
                continue;
            }
        }
        if ($file == $mod_file) {
            echo "{$red}BUMPING{$black}: $module from " . trim($versNode->textContent) . " to $new_vers\n";
        } else {
            $locale = basename(dirname($file));
            echo "{$red}BUMPING{$black}: $module localized to $blue$locale$black from " . trim($versNode->textContent) . " to $new_vers\n";
        }
        while ($versNode->hasChildNodes()) {
            $versNode->removeChild($versNode->firstChild);
        }
        $versNode->appendChild($template->createTextNode($new_vers));
        file_put_contents($file, $template->getDisplay( true ));
    }
}
