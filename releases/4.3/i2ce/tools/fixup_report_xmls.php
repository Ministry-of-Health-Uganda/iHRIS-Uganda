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
require_once('CLI.php');
require_once('I2CE.php');
require_once('I2CE_FileSearch.php');
require_once('I2CE_TemplateMeister.php');
$names = array ('name','description','display_name','header');

if (count($arg_files) == 0) {
    usage("Please specify report configurariotn xmls");
}

foreach ($names as &$name) {
    $name = "@name = '$name'";
}
$qry = '/I2CEConfiguration/configurationGroup//configuration[' . implode(' or ' , $names) . ']';



foreach ($arg_files as $arg_file) {
    if (!is_readable($arg_file)) {
        I2CE::raiseError("Could not read $arg_file");
        continue;
    }
    if (!is_writable($arg_file)) {
        I2CE::raiseError("Could not write $arg_file");
        continue;
    }
    $template = new I2CE_TemplateMeister();
    if (!$template->loadRootFile(realpath($arg_file))) {
        I2CE::raiseError("Could not load $arg_file");
        continue;
    }    
    //cleanup disabled nodes
    $remove_qry = '/I2CEConfiguration/configurationGroup//configuration[@name="enabled" and value = 0 ]/..';
    if (! ($remove_nodes = $template->query($remove_qry)) instanceof DOMNodeList) {
        I2CE::raiseError("No nodes found to remove.  Bad query?");
    } else {
        I2CE::raiseError("Removing " . $remove_nodes->length . " disabled nodes");
        foreach ($remove_nodes as $node) {
            $node->parentNode->removeChild($node);
        }
    }
    //cleanup  nodes that have configuarion subnodes-
    $cleanup_qry = '/I2CEConfiguration/configurationGroup/configurationGroup';
    if (! ($cleanup_nodes = $template->query($cleanup_qry)) instanceof DOMNodeList) {
        I2CE::raiseError("No nodes found to cleanup.  Bad query?");
    } else {
        $cleaned = 0;
        foreach ($cleanup_nodes as $node) {
            if (can_remove($node,0)) {
                $cleaned++;
                $node->parentNode->removeChild($node);
            }
        }
        I2CE::raiseError("Cleaned $cleaned nodes that had no child configuration");
    }
    if (!$nodes = $template->query($qry)) {
        I2CE::raiseError("No nodes found.  Bad query?");
        continue;
    }
    if ( $nodes->length == 0) {
        I2CE::raiseError("No nodes found");
        continue;
    }
    $added = 0;
    foreach ($nodes as $node) {
        if ($node->hasAttribute('locale')) {
            continue;
        }
        $node->setAttribute('locale',I2CE_LOCALES::DEFAULT_LOCALE);
        $added++;
    }
    $out = $template->getDisplay();
    if (!$out) {
        I2CE::raiseError("Badnes");
        continue;
    }
    if (!file_put_contents($arg_file,$out)) {
        I2CE::raiseError("Could not overwrite $arg_file");
        continue;
    }
    I2CE::raiseError("Updated " . $added . " nodes in $arg_file");

}

function can_remove($node,$depth) {
    global $cleaned;
    if (!$node instanceof DOMElement) {
        return true;
    }
    switch ($node->tagName) {
    case 'value':
        return strlen(trim($node->textContent)) == 0;
    case 'configuration':
        if (!$node->childNodes instanceof DOMNodeList) {
            return true;
        }
        $can_remove = true;
        foreach ($node->childNodes as $n) {
            $can_remove &= can_remove($n,$depth+1);
        }
        return $can_remove;
    case 'configurationGroup':
        if ( !$node->childNodes instanceof DOMNodeList) {
            return true;
        }
        $can_remove = true;
        $nodes = $node->childNodes;
        $removed = true;
        while ($removed) {
            $removed = false;
            $nodes = $node->childNodes;
            foreach ($nodes as $n) {
                if (can_remove($n,  $depth+1)) {
                    if (!$n instanceof DOMElement || $n->tagName != 'displayName') {
                        $cleaned++;
                        $node->removeChild($n);
                        $removed = true;
                        $nodes = $node->childNodes;
                        break;
                    }
                } else {
                    $can_remove = false;
                }
            }
        }
        return $can_remove;
    default:
        return true;
    }    
}
