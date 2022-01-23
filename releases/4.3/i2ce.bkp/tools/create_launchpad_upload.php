#!/usr/bin/php
<?php
/**
* © Copyright 2008 IntraHealth International, Inc.
* 
* This File is part of I2CE 
* 
* I2CE is free software; you can redistribute it and/or modify 
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
*  fixup_po -- moves $domain-$locale.[pm]o to $locale/$domain.[pm]o  where $domain is any of the command line arguemnents
*  e.g. fixup_po.php ihris-manage ihris-qualify
*
* @package I2CE
* @subpackage Core
* @author Carl Leitner <litlfred@ibiblio.org>
* @copyright Copyright &copy; 2008 IntraHealth International, Inc. 
* This file is part of I2CE. I2CE is free software; you can redistribute it and/or modify it under 
* the terms of the GNU General Public License as published by the Free Software Foundation; either 
* version 3 of the License, or (at your option) any later version. I2CE is distributed in the hope 
* that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY 
* or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details. You should have 
* received a copy of the GNU General Public License along with this program. If not, see <http://www.gnu.org/licenses/>.
* @version 2.1
* @access public
*/

$translations_dir = "translations"  . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR;
$usage[] =  "Looks for .pot files in $translations_dir\n";
$set_categories = false;
$set_configs = false;
require_once("translate_base.php");

@require_once ("Archive/Tar.php");
if (!class_exists('Archive_Tar')) {
    usage('Please install the PEAR Archive_Tar package');
}



I2CE::setupFileSearch(array('MODULES'=>getcwd()));
$fileSearch = I2CE::getFileSearch();
$module = $configurator->findAvailableConfigs($fileSearch,false);        
if (count($module) != 1) {
    usage("No Modules Specified");
}
$module = $module[0];
$out_dir = 'translations' . DIRECTORY_SEPARATOR . 'launchpad' ;
$archive_file = $out_dir . DIRECTORY_SEPARATOR . 'templates-' . $module .'.tgz';


if ($translations_dir[strlen($translations_dir) -1] == DIRECTORY_SEPARATOR) {
    $translations_dir = substr($translations_dir,0,-1);
}
if (!is_dir($translations_dir) || !is_readable($translations_dir)) {
    usage( "Could not find/read $translations_dir directory");
}
if (!is_dir($out_dir)) {
    if (!mkdir($out_dir, 0775,true)) {
        usage("Could not create $out_dir");
    }
}
if (!is_writable($out_dir)) {
    usage("Could not write to $out_dir");
}



$pos = strrpos($archive_file,'.');
if ($pos === false) {
    usage("Could not determine archive type for $archive_file");
}
switch ( substr($archive_file,$pos+1)) {
case 'tgz':
case 'gz':
    $compression  = 'gzup';
case 'bz2':
    $compression  = 'bz2';
case 'tar':
    $compression  = null;
    break;
default:
    echo("Could not determine archive type for $archive_file");
}

$tar =  new Archive_Tar($archive_file, $compression);




 
if (!$tar->create(array())) {
    usage("Could not create tar $archive_file");
}

$glob = preg_replace('/(\*|\?|\[)/', '[$1]', $translations_dir . DIRECTORY_SEPARATOR).'*.pot';
foreach (glob($glob) as $file) {
    if (!$tar->addModify($file,'translations/' . basename($file,'.pot'), $translations_dir)) {
        usage("Could not add $base");
    }
}
echo "Created $archive_file with:\n";
$contents = $tar->listContent();
foreach ($contents as $content) {
    echo "\t{$content['filename']}\n";
}
echo "Upload translations by browsing to:\n\t";
echo 'https://translations.edge.launchpad.net/' . $module . '/trunk/+translations-upload' . "\n";






# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:


    


