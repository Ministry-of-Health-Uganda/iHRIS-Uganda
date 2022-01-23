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

require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR . 'base.php');
require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR . 'wiki_base.php');

// $titles = wikiGetTitlesWithPrefix('DEVELOPMENT:');
// print_r($titles);
// if (!simple_prompt("Continue?")) {
//     die("Done\n");
// }
// foreach ($titles as $title) {
//     // if (!wikiDelete($title)) {
//     //     I2CE::raiseError("Could not delete $title");
//     // }
// }





$version = '4.0.3';
//$titles = wikiGetTitlesWithNamespace('iHRIS',$version);
$titles = wikiGetTitlesWithNamespace('iHRIS');
//print_r($titles);






$snoopy2 = new Snoopy(); //we need a new snoopy instance so we don't get the logged in -- edit section version of the page
foreach ($titles as $title) {
    $title = 'iHRIS:Run Reports';
    $post = array(
        'action'=>'render',
        'title'=>$title,
        );
   if (!$snoopy2->submit($wikiroot_url,$post)) {
       I2CE::raiseError("Could not test for $title");
       return false;
   }
   $html = $snoopy2->results;
   $doc = new DOMDocument();
   $xpath = new DOMXpath($doc);
   if (!$doc->loadHTML("<meta http-equiv='content-type' content='text/html; charset=UTF-8'>" . $html)) {
       I2CE::raiseError("Could not load html\n");
       continue;
   }
   die($doc->childNodes->length . "\n");

   cleanupLinkedImages($xpath);
   echo $doc->saveHTML();
   die("Ending loop prematurel\n");
}


function cleanupLinkedImages($xpath) {
    //replace things like:
    //  <a href="http://open.intrahealth.org/mediawiki/File:Reports_chart_options.png" class="image" title="Reports chart options.png"><img alt="" src="/w/upload/thumb/Reports_chart_options.png/500px-Reports_chart_options.png" width="500" height="568" border="0"></a>
    //with this:
    //  <img alt="" src="/w/upload/thumb/Reports_chart_options.png/500px-Reports_chart_options.png" width="500" height="568" border="0"></a>
    $qry = "//a[@class='image']/img";
    $qry = "//img";
    if (! ($nodelist = $xpath->query($qry)) instanceof DOMNodeList) {
        echo "Bad Query: $qry\n";
    }
    foreach ($nodelist as $node) {
        echo "node \n";
    }
    
}