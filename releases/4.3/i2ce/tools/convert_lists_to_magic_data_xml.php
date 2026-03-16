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
 * Translate Templates
 *
 * @package I2CE
 * @subpackage Core
 * @author Carl Leitner <litlfred@ibiblio.org>
 * @copyright Copyright &copy; 2008, 2008 IntraHealth International, Inc. 
 * @version 1.0
 */



/*****************************************
 *
 *  Wiki webservice helper function
 *
 *****************************************/
require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR . 'CLI.php');
require_once( dirname(__FILE__) . '/../lib/I2CE.php');
require_once( dirname(__FILE__) . '/../lib/I2CE_FileSearch.php');
require_once( dirname(__FILE__) . '/../lib/I2CE_TemplateMeister.php');
require_once( dirname(__FILE__) . '/../lib/I2CE_MagicDataTemplate.php');
require_once( dirname(__FILE__) . '/../modules/Pages/lib/I2CE_Template.php');
require_once( dirname(__FILE__) . '/../modules/MagicDataExport/I2CE_MagicDataExport_Template.php');

if (count($arg_files) != 1) {
    usage("Please specify list html");
}
$in_file = realpath($arg_files[0]);
$out_file = dirname($in_file) . '/' . basename($in_file,'.html') . '.xml';
$template = new I2CE_Template();        
$template->loadRootFile($in_file);
$data = array();
foreach ($template->query('//li/a') as $a) {
    $li = $a->parentNode;
    $href = $a->getAttribute('href');
    $comp = parse_url($href);
    if ( (!is_array($comp))  || (!array_key_exists('query',$comp)) || (!$comp['query']) || ($li->tagName != 'li' ) ) {
        echo "Skipping(0): " .$a->textContent . " : href=" . $href . "\n";
        continue;
    }
    $qry = array();
    parse_str($comp['query'],$qry);
    if (!is_array($qry) || !array_key_exists('type',$qry) ) {
        echo "Skipping(1): " .$a->textContent . " : href=" . $href . "\n";
        continue;
    }
    $type = $qry['type'];
    unset($qry['type']);
    $qry['form'] = $type;
    if ($li->hasAttribute('task')) {
        $qry['task'] =$li->getAttribute('task');
    }

    $h2_list = $template->query('preceding::h2[1]',$li);
    if ($h2_list->length != 1) {
        I2CE::raiseMessage("No h2 before this node");
    } else {
        $qry['category'] = $h2_list->item(0)->textContent;
    }

    $h3_list = $template->query('preceding::h3[1]',$li);
    if ($h3_list->length == 1) {
        //check to see if preceeding h2 is same as current category
        $h2_list = $template->query('preceding::h2[1]',$h3_list->item(0));
        if ($h2_list->length == 1 && $h2_list->item(0)->textContent == $qry['category']) {
            $qry['subcategory'] = $h3_list->item(0)->textContent;
        }
    }

    $mods = $template->query("ancestor-or-self::*[@type='module'][1]",$li);
    if ($mods->length == 1  && ($mod = $mods->item(0)->getAttribute('name'))) {
        $qry['mods']= $mod;
    }
    $qry['text'] = $a->textContent;
    $data[$type] = $qry;
    
}
$list_storage = I2CE_MagicData::instance( "temp_lists");
$list_storage->modules->Lists->auto_list = $data;
foreach ($list_storage->modules->Lists->auto_list as $type=>$listConfig) {
    $listConfig->traverse('text',true,false)->setTranslatable();
}


I2CE::setupFileSearch(array('XML'=>array(dirname(dirname(__FILE__) .  "/../modules/MagicDataExport/xml/export_magicdata.xml"))));
echo $list_storage;
$export = new I2CE_MagicDataExport_Template();
$export->loadRootFile(dirname(dirname(__FILE__) .  "/../modules/MagicDataExport/xml/export_magicdata.xml") . "/export_magicdata.xml");
$configNodes = $export->query('/I2CEConfiguration/configurationGroup');
$configNode =  $configNodes->item(0);
$configNode->setAttribute('name','auto_list');
$configNode->setAttribute('path','/modules/Lists/auto_list');
$export->createExport($configNode,$list_storage->modules->Lists->auto_list);
foreach ($export->query('//displayName',$configNode) as $dp) {
    $dp->parentNode->removeChild($dp);  //condense the .xml export
}
foreach ($export->query('//*[@name="category"] | //*[@name="subcategory"] | //*[@name="text"]') as $t) {
    $t->setAttribute('locale','en_US'); //shouldn't have to do this because we set the nodes as translatbale above.  there is a bug in magic data export
}

$out =  $export->getDisplay();
file_put_contents($out_file,$out);
echo "Saved Magic Data XML template to $out_file\n";



# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
