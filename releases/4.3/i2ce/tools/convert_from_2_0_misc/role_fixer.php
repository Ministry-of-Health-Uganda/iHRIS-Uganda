<?php
$roles = array(
    '/[\'"]?I2CE_ROLE_ADMIN[\'"]?/',
    '/[\'"]?I2CE_ROLE_ALL[\'"]?/',
    '/[\'"]?I2CE_ROLE_ANY[\'"]?/',
    '/[\'"]?I2CE_ROLE_HR_MANAGER[\'"]?/',
    '/[\'"]?I2CE_ROLE_HR_STAFF[\'"]?/',
    '/[\'"]?I2CE_ROLE_EXEC_MANAGER[\'"]?/'
    );

$replacements = array(
    '\'admin\'',
    '\'all\'',
    '\'any\'',
    '\'hr_manager\'',
    '\'hr_staff\'',
    '\'exec_manager\''
    );

function process_file($file) {
    echo "Replacing $file\n";
    global $replacements;
    global $roles;
    $contents = file($file);
    foreach($contents as $number=>$line) {
        $contents[$number] = preg_replace($roles,$replacements,$line);
    }
    $fh = fopen($file,"w");
    foreach ($contents as $line) {
        fwrite($fh,$line);
    }
    fclose($fh);
}



function run_files($file) {
    if (is_link($file)) {
        return;
    }
    if (is_dir ($file . '/.')) {
        $dh = opendir($file);
        $files = array();
        while (false !== ($f = readdir($dh))) {
            if ($f[0] !='.') {
                $files[] = $file . '/' . $f;
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
