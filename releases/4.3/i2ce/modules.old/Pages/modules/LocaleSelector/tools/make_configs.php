#!/usr/bin/php
<?php
/**
 * @copyright © 2009 Intrahealth International, Inc.
 * This File is part of I2CE
 *
 * I2CE is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by
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
 * @package I2CE
 * @subpackage Core
 * @author Carl Leitner <litlfred@ibiblio.org>
 * @version 2.1
 * @access public
 */

if (dirname(__FILE__) != getcwd()) {
    die("Please run in the tools subdirectory\n");
}
echo "Please increase your version in LocaleSelector.xml\n";

$lines = file('all_languages');
if ($lines === false) {
    die("Badness");
}
$lang = null;
$lang_trans = array();
$avail = array();
foreach ($lines as $i=>$line) {
    if (preg_match('/^\[(\w+?)\]\s*$/',$line,$matches)) {
        $lang = trim($matches[1]);
        $lang_trans[$lang] = array();
        continue;
    }
    if (preg_match('/^Name\[(\w+?)\]=(.*?)\s*$/',$line,$matches)) {
        if ($lang === null) {
            echo "Warning no language found but name found on line $i\n";
            continue;
        }
        $l = $matches[1];
        $trans = $matches[2];
    } else if (preg_match('/^Name=(.*?)\s*$/',$line,$matches)) { 
        if ($lang === null) {
            echo "Warning no language found but name found on line $i\n";
            continue;
        }
        $l = 'en_US';
        $trans = $matches[1];
    } else {
        continue;
    }
    $l = trim($l);
    $trans = trim($trans);
    if (strlen($l) == 0 || strlen($trans) == 0) {
        continue;
    }
    $lang_trans[$lang][$l] = $trans;
    $avail[$l] = true;
}


if (count($lang_trans) == 0) {
    die("No languages found\n");
}
if (!preg_match('/<version>(.+)<\/version>/',file_get_contents('../LocaleSelector.xml') , $matches)) {
    die ("No version found for locale selector module\n");
}
$version = $matches[1];


//http://www.i18nguy.com/temp/rtl.html
//http://en.wikipedia.org/wiki/List_of_ISO_639-2_codes
$RTLS = array(
    'ar','ara','arc','aze','az','cmc','div','dv','fa','fas','he',
              'jav','jv','jpr','jrb','kas','kaz','kk','ku','kur','ks','lad',
              'ma','mal','may','ms','msa','pa','pal','pan','peo','per','ps',
              'pus','sam','sd','so','som','snd','syc','syr','tmh','tk','tuk'
              ,'ug','uig','ur','urd');


$RTL_exprs =array( '/\p{Hebrew}/u', '/\p{Arabic}/u');


mb_internal_encoding("UTF-8");
foreach ($avail as $l=>$true) {
    $dir = '..' . DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR . $l;
    if (!is_dir($dir)) {
        if (!mkdir($dir,0775,true)) {
            die( "WARNING: Could not make $dir\n");
        }
    }            
    if ( ($dash_pos = strpos($l,'_')) === false ) {
        $two_letter = $l;
    } else {
        $two_letter = substr($l,0,$dash_pos);
    }
    $rtl = in_array( $two_letter, $RTLS);

    $out = '';
    if ($l != 'en_US') {
        $out .= '<?xml version="1.0"?>
<!DOCTYPE I2CEConfiguration SYSTEM "I2CE_Configuration.dtd">
<I2CEConfiguration name=\'localeSelector\'>     
  <metadata>
    <displayName>Locale Selector</displayName>   
    <className>I2CE_Module_LocaleSelector</className>
    <version>' . $version .'</version>
  </metadata>
  <configurationGroup name=\'localeSelector\' locale="'. $l  .'">
';
    }
    $out .= "    <configuration name='languages' path='/locales/languages'  values='many' type='delimited'>\n";
    $out .= "      <version>$version</version>\n";
    $added = false;
    foreach ($lang_trans as $lang=>$trans_data){
        if (count($trans_data) == 0) {
            continue;
        }
        if (!array_key_exists($l,$trans_data)) {
            continue;
        }
        $added = true;
        $trans = $trans_data[$l];
        if ($rtl) {
            if (($pos = mb_strpos($trans,'(')) !== false &&   ($pos2 = mb_strpos($trans,')')) !== false) {
                $all_parts = array( mb_substr($trans,0,$pos), mb_substr($trans,$pos+1, $pos2 - $pos -1));
            } else {
                $all_parts = array($trans);
            }            
            foreach ($all_parts as &$all_part) {
                $parts = preg_split('/ /u',$all_part);                
                foreach ($parts as $i=>&$part) {
                    $t_rtl = false;
                    foreach ($RTL_exprs as $RTL_expr) {
                        if (preg_match($RTL_expr,$part)) {
                            $t_rtl = true;
                            break;
                        }
                    }
                    if (!$t_rtl) {
                        continue;
                    }
                    $len = mb_strlen($part,'UTF-8');
                    $ps = array();
                    while ($len) {
                        $t = mb_substr($part,0,1,'UTF-8');
                        $ps[] = $t;
                        $part = mb_substr($part,1,$len,'UTF-8');
                        $len = mb_strlen($part);
                    }
                    $part = implode('', array_reverse($ps));
                }
                $all_part = implode(' ',array_reverse($parts));
            }
            if (count($all_parts) == 2) {
                $trans =  '(' . $all_parts[1] . ')' . $all_parts[0] ;
            } else {
                $trans = $all_parts[0];
            }
        }
        $out .= "      <value>$lang:" . $trans . "</value>\n";        
    }
    if (!$added) {
        continue;
    }
    $out .= "    </configuration>\n";
    if ($l != 'en_US') {
        $out .= "  </configurationGroup>\n</I2CEConfiguration>\n";
    }
    if ($l == 'en_US') {
        $file = $dir . DIRECTORY_SEPARATOR . 'Languages.xml';
    } else {
        $file = $dir . DIRECTORY_SEPARATOR . 'LocaleSelector.xml';
    }
    if (!file_put_contents($file,$out)) {
        die();
    }
    echo "Made $file\n";
}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
