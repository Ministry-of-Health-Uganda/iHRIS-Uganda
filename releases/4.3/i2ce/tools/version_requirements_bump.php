<?php

require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . "CLI.php" );
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . "base.php" );


$base_vers = false;
$bump_type = 1;
$base_vers = ask("What is the target major version (e.g. 4.2)?");
$base_vers = trim($base_vers);

$base_vers_comps = explode('.',$base_vers);
if (count($base_vers_comps) != $bump_type +1) {
    die("Invalid target version ($base_vers)\n");
}

$check_vers_comps = $base_vers_comps;
$check_vers_comps[$bump_type]--;
$check_vers =  implode('.',$check_vers_comps); 

$check_short_vers = implode('.',array_slice($base_vers_comps,0,$bump_type));

$next_vers_comps  = $base_vers_comps;
$next_vers_comps[1]++;
$next_vers = implode('.',$next_vers_comps);




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
    $template = new I2CE_TemplateMeister();
    if (!$template->loadRootFile($mod_file)) {
        echo "\tCould not load\n";
        continue;
    }        
    
    if (( ! ($results = $template->query('/I2CEConfiguration/metadata/requirement/lessThan[@version="' . $base_vers . '"]')) instanceof DOMNodeList) || ($results->length == 0)) {
        continue;
    }
    $changed = false;
    foreach ($results as $ltNode) {
        $res = $template->query('./atLeast',$ltNode->parentNode);
        if ((!$res instanceof DOMNodeList) || ($res->length != 1)) {
            echo "$module has no atLeast node\n";
            continue ;
        }
        $changed = true;
        $ltNode->setAttribute('version',$next_vers);
        $res->item(0)->setAttribute('version',$base_vers);
    }
    if (!$changed) {
        continue;
    }
    echo "{$red}BUMPING REQUIREMENTS{$black} for $module \n";
    file_put_contents($mod_file, $template->getDisplay( true ));
}
