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




$package_dir =getcwd();
require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR . 'base.php');
require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR . 'wiki_base.php');
require_once( dirname(__FILE__) . '/../lib/I2CE_Date.php');
require_once( dirname(__FILE__) .'/I2CE_MagicDataTemplate_Documentor.php');








//colors from http://meta.wikimedia.org/wiki/Wiki_color_formatting_help
$packages = array(
    'i2ce'=>array(
        'pkg_name'=>'i2ce',
        'top_module'=>'I2CE',
        'name'=>'I2CE',
        'color'=>'FireBrick'
        ),
    'manage'=>array(
        'pkg_name'=>'ihris-manage',
        'top_module'=>'ihris-manage',
        'name'=>'iHRIS Manage',
        'color'=>'DarkGreen'
        ),
    'common'=>array(
        'pkg_name'=>'ihris-common',
        'top_module'=>'ihris-common',
        'name'=>'iHRIS Common',
        'color'=>'SaddleBrown'
        ),
    'qualify'=>array(
        'pkg_name'=>'qualify',
        'top_module'=>'ihris-qualify',
        'name'=>'iHRIS Qualify',
        'color'=>'Teal'
        ),
    'textlayout'=>array(
        'pkg_name'=>'textlayout',
        'top_module'=>'textlayout',
        'name'=>'TextLayout Tools',
        'color'=>'Indigo'
        )    
    );




$search_dirs = array();
foreach (glob($package_dir . "/*" ,GLOB_ONLYDIR) as $dir) {
    $ldir = strtolower(basename($dir));
    foreach ($packages as $pkg=>&$info) {
        if (strpos($ldir,$pkg) !== false) {
            $dir = realpath($dir);
            $search_dirs[$pkg] = $dir;
            $info['dir'] = $dir;
            break;
        }
    }
    unset($info);  //deference this so we can use it
}
if (count($search_dirs) ===0) {
    die("Could not find any packages\n");
}
if (!array_key_exists('i2ce',$search_dirs)) {
    die("Could not find i2ce package in a subdirectory");
}

$base_version = trim(shell_exec('grep -m 1 \'<version>.*</version>\' ' .$search_dirs['i2ce'] . '/I2CE_Configuration.xml | sed s/.*\\<version\\>// | sed s/\\<\\\\/version\\>.*//'));
if (!$base_version) {
    die("Could not determine the main version");
}
$main_version = explode('.',$base_version);
if (count($main_version) < 3) {
    die("Invalid verison $base_version");
}
$main_version = implode(".", array_slice($main_version,0,3));

I2CE::raiseError("Processing version $main_version (Base I2CE version is $base_version)");




if ( simple_prompt("Is this development code?")) {
    $is_dev =true;
    $title_append = wikiGetVersionedTitleAppend('Development');
    $create_redirects = false;
    $versions = array(
        'i2ce'=>$main_version,
        'manage'=>$main_version,
        'common'=>$main_version,
        'qualify'=>$main_version,
        'textlayout'=>$main_version
        );
    foreach($versions as $pkg=>$vers) {
        $vers = substr($vers,0,strrpos($vers,'.'));
        $versions[$pkg] = $vers . '-dev';
        $packages[$pkg]['bzr'] = 'https://launchpad.net/' . $packages[$pkg]['pkg_name'];
        $packages[$pkg]['bzr_files'] =  "http://bazaar.launchpad.net/~intrahealth+informatics/" . $packages[$pkg]['pkg_name'] . "/$vers-dev/files/head:";
        $packages[$pkg]['bzr_annotate_files'] =  "http://bazaar.launchpad.net/~intrahealth+informatics/" . $packages[$pkg]['pkg_name'] . "/$vers-dev/annotate/head:";        
        $packages[$pkg]['bzr_translate'] =  "http://translations.launchpad.net/" .  $packages[$pkg]['pkg_name'] . "/trunk/+pots/";        
    }

} else {
    $is_dev = false;
    $title_append = wikiGetVersionedTitleAppend($main_version);
    $create_redirects = simple_prompt("Redirect all pages to this version?");
    $versions = array(
        'i2ce'=>$main_version,
        'manage'=>$main_version,
        'common'=>$main_version,
        'qualify'=>$main_version,
        'textlayout'=>$main_version
        );
    foreach($versions as $pkg=>$vers) {
        $versions[$pkg] = $vers . '-release';
        $packages[$pkg]['bzr'] = 'https://launchpad.net/' . $packages[$pkg]['pkg_name'];
        $packages[$pkg]['bzr_files'] =  "http://bazaar.launchpad.net/~intrahealth+informatics/" . $packages[$pkg]['pkg_name'] . "/$vers-release/files/head:";
        $packages[$pkg]['bzr_annotate_files'] =  "http://bazaar.launchpad.net/~intrahealth+informatics/" . $packages[$pkg]['pkg_name'] . "/$vers-release/annotate/head:";
        $packages[$pkg]['bzr_translate'] =  "http://translations.launchpad.net/" .  $packages[$pkg]['pkg_name'] . "/trunk/+pots/";        
    }

}







/*************************************
 *
 *  processing of modules
 *
 *******************************************/

$found_modules = getAvailableModules();

I2CE::longExecution();


/***********************************************
 *
 *  go througgh each of the module and pull out the magic data nodes when/where they are defined.
 *
 ************************************/




function processConfigFile($module,$top_module,$file, &$md_nodes, &$child_nodes) {
    global $packages, $module_packages;
    $mod_file = basename($file);
    $current_pkg = false;
    foreach ($packages as $pkg=>$info) {
        if ($top_module == $info['top_module']) {
            $module_packages[$module] = $pkg;
            $current_pkg = $pkg;
            break;
        }
    }    
    if (!$current_pkg || !array_key_exists($module,$module_packages)) {
        I2CE::raiseError("Could not deterine which package $module resides in"); //should not happen
        return false;
    }    
    $file_base = substr(dirname($file),strlen($packages[$current_pkg]['dir']));

    $template = new I2CE_MagicDataTemplate_Documentor();
    if (!$template->loadRootFile($file)) {
        I2CE::raiseError("Could not process $file");           
        return false;
    }
    $config_groups = $template->query("/I2CEConfiguration/configurationGroup"); 
    if($config_groups->length == 0 ) { 
        return array();
    }
    if ($module != 'I2CE') {
        $paths = array('modules');
    } else {
        $paths = array();
    }
    foreach ($config_groups as $config_group) {
        $status = $template->getDefaultStatus();
        if ($config_group->hasAttribute('locale')) {
            $locale = $config_group->getAttribute('locale');
        } else {
            $locale = false;
        }
        $vers = '0';
        if (false === $template->document($config_group,$status, $vers)) {
            I2CE::raiseError("Could not process $file");
            return false;
        }
    }
    $node_datas = $template->getNodeData();
    foreach ( $node_datas  as $node_path=>$node_data) {
        if (!array_key_exists($node_path, $md_nodes)) {
            $md_nodes[$node_path] = array();
        }
        $node_data['pkg'] = $current_pkg;
        $node_data['module']=$module;
        $node_data['src'] = $packages[$current_pkg]['bzr_annotate_files'] . $file_base . '/' . $mod_file;
        if (array_key_exists('line',$node_data)) {
            $node_data['src'] .=  '#' . $node_data['line'];
        }
        $md_nodes[$node_path][] = $node_data;
    }    
    $node_paths = array_keys($node_datas);    
    sort($node_paths);
    $t_nodes = $node_paths;
    foreach($node_paths as $node_path) {
        $c_nodes = array();
        $len = strlen($node_path);
        $s_len = $len;
        if ($node_path == '/') {
            $s_len = $len -1;
        }
        foreach($t_nodes as $i=>$n_p) {            
            if (strlen($n_p) <= $len) {
                continue;
            }
            if (substr($n_p,0,$len) != $node_path) {
                continue;
            }
            $c_nodes[ current(explode('/',substr($n_p,$s_len+1)))] = true;
        }
        if (!array_key_exists($node_path,$child_nodes)) {
            $child_nodes[$node_path] =  array();
        }
        $child_nodes[$node_path] = array_unique(array_merge(array_keys($c_nodes), $child_nodes[$node_path]));
    }
    return true;
}





$md_nodes = array();
$i=0;
$total = count($found_modules);
$child_nodes = array();
foreach ($found_modules as $module=>$top_module) {
    $i++;    
    $current_pkg = false;
    foreach ($packages as $pkg=>$info) {
        if ($top_module == $info['top_module']) {
            $module_packages[$module] = $pkg;
            $current_pkg = $pkg;
            break; 
        }
    }    
    if (!$current_pkg || !array_key_exists($module,$module_packages)) {
        I2CE::raiseError("Could not deterine which package $module resides in"); //should not happen
        continue;
    }    
    $file = '';
    $storage->setIfIsSet($file,"/config/data/$module/file");
    if (!$file || !is_readable($file)) {
        I2CE::raiseError("No file recored for $module");
        continue;
    }
    processConfigFile($module,$top_module,$file, $md_nodes,$child_nodes);
    echo "Process $module $i of $total\n";
}

ksort($md_nodes);


$descs = array(
    'displayName' => "Display Name: %s\n",
    'description' => "Description: %s\n",
    'value'=>"Value: %s\n",
    'version'=>"Version: This has be defined in version %s of this module\n"
    );

foreach ($md_nodes as $node_path=>$node_datas) {
    $title = 'MDN: ' . $node_path;
    if (count($node_datas) == 0) {
        continue; 
    }

    $texts = array();
    $type = 'parent';
    $translatable = false;
    foreach ($node_datas as $node_data) {
        if (count($node_data) <= 4) {
            //no info just src and module pkg and is_scalar
            continue;
        }
        $desc = "*This node is referenced in [[" . $packages[$node_data['pkg']]['name'] . " Module List{$title_append}#" .$node_data['module']. ' |' . $node_data['module'] . "]] [" . $node_data['src'] . " (src)]\n";
        foreach ($descs as $key=>$tag) {
            if (!array_key_exists($key,$node_data)) {
                continue;
            }
            $desc.='**' . str_replace("\n",'',sprintf($tag ,  $node_data[$key])) . "\n";
        }
        if (array_key_exists('translatable', $node_data) && $node_data['translatable']) {
            $translatable |=   $node_data['translatable'];
            $desc .='**Translate [' .  $packages[$node_data['pkg']]['bzr_translate'] . $node_data['module']  . " here]\n";
        }
        $texts[$node_data['module']] = $desc;
        if (array_key_exists('is_scalar',$node_data) && $node_data['is_scalar']) {
            $type = 'scalar';
        }
    } 
    if ($translatable && $type =='scalar') {
        $type = 'translatable scalar';
    }
    $comps = explode('/', $node_path);
    array_shift($comps);

    if (count($comps) > 1) {
        $linked_node_path = array_pop($comps);        
        while (count($comps)> 0) {
            $comp = array_pop($comps);
            if (count($comps) > 0) {
                $linked_node_path = '[[' .    'MDN: /' . implode('/', $comps) .'/'.$comp. $title_append .' | '. $comp .   '/ ]] '  . $linked_node_path ;
            } else {
                $linked_node_path = '[[' .    'MDN: ' . implode('/', $comps) .'/'.$comp. $title_append .' | '. $comp .  '/ ]] ' . $linked_node_path ;
            }
        }
        $linked_node_path = '[[ MDN: /' . $title_append . '| / ]] ' . $linked_node_path;
    } else {
        $comp = current($comps);
        if (strlen($comp)>0) {
            $linked_node_path = '[[ MDN:/' . $title_append .'| / ]] ' . current($comps);
        } else {
            $linked_node_path = '/' ;
        }
    }
    if ($type == 'parent' && $node_path != '/') {
        $linked_node_path .= '/';
    }
    if (count($texts) == 0) {
        $page = "The magic data node $linked_node_path is a $type node and  has no description.\n";
        if (array_key_exists($node_path,$child_nodes) && count($child_nodes[$node_path]) > 0) {
            $c_nodes = $child_nodes[$node_path];
            sort($c_nodes);
            $page .= "\nThis node has the following children:\n";
            if ($node_path != "/") {
                foreach ($c_nodes as $node) {
                    $page .= "* [[MDN: " . $node_path . '/' . $node . $title_append . '| ' . $node . "]]\n";
                }
            } else {
                foreach ($c_nodes as $node) {
                    $page .= "* [[MDN: " .  '/' . $node . $title_append . '| ' . $node . "]]\n";
                }
            }
        }
    } else {
        ksort($texts);
        $page = "The magic data node $linked_node_path is a $type node.\n";
        if (array_key_exists($node_path,$child_nodes) && count($child_nodes[$node_path]) > 0) {
            $c_nodes = $child_nodes[$node_path];
            sort($c_nodes);
            $page .= "\nThis node  has the following children:\n";
            if ($node_path != "/") {
                foreach ($c_nodes as $node) {
                    $page .= "* [[MDN: " . $node_path . '/' . $node . $title_append . '| ' . $node . "]]\n";
                }
            } else {
                foreach ($c_nodes as $node) {
                    $page .= "* [[MDN: " .  '/' . $node . $title_append . '| ' . $node . "]]\n";
                }
            }
        }
        $page .="It is referenced as follows:\n" .implode ("",$texts);       
    }
    echo "Uploading $node_path\n";
    if (! wikiUploadVersioned('__PAGE:' . $title . "\n" . $page)) {
        I2CE::raiseError("Could not upload $$title");
    }
    
}


