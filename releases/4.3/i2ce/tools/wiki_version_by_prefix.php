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
$titles = wikiGetTitlesWithNamespace('iHRIS');
// foreach ( $titles as $title) {
//     $text = wikiGetText($title);
//     if ($text === false) {
//         continue;
//     }
//     //wikiMakeVersioned($title,$text,$version);
// }


echo wikiGetText('iHRIS:Understanding_iHRIS_Manage');

//print_r(wikiGetPageData('iHRIS:Understanding_iHRIS_Manage'));