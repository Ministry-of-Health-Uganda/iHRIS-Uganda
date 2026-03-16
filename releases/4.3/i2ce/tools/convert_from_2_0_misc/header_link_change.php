<?php

//$replacements = array('/filedump\?/'=>'file?');
$replacements = array('/addHeaderLink\(([\'"])/' => 'addHeaderLink(${1}file?');  

function process_file($file) { 
    global $replacements; 
    $contents = file($file); 
    foreach($contents as $number=>$line) {
        foreach ($replacements as $in=>$out) {
            if (preg_match($in,$line)) {
                echo "Matched on $file:$line";
                $line = preg_replace($in,$out,$line);
                $contents[$number] = $line;
                echo "Matched on $file:$line";
            }
        }
    }
    //  $fh = fopen($file,"w");
    foreach ($contents as $line) {
        //fwrite($fh,$line);
    }
    //  fclose($fh);
}



function run_files($file) {
    if (is_link($file)) {
        return;
    }
    if (is_dir ($file . '/.')) {
        $dh = opendir($file);
        $files = array();
        while (false !== ($f = readdir($dh))) {
            if ($f[0] == '.') {
                continue;
            }
            if (is_dir($file .'/' . $f . '/.')) {
                $files[] = $file . '/' . $f;                            
            } else {
                $ext = strtolower(substr(strrchr($f, '.'), 1));
                if (($ext == 'css') || ($ext =='html') || ($ext=='php')|| ($ext=='js')) {
                    $files[] = $file . '/' . $f;                              
                }
            }
        }
        closedir($dh);
        foreach ($files as $f) {
            run_files($f);
        }
    } else {
        process_file($file);
    }
}

$dirs = array('lib','modules','admin-templates','data','scripts','../ihris-common','../ihris-manage');
foreach ($dirs as $dir) {
    run_files("./$dir");
}



# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
