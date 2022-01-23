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



$templates_dir ="." . DIRECTORY_SEPARATOR . "translations" .  DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR;

$package_dir =getcwd();
require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR . 'base.php');
require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR . 'wiki_base.php');
require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR . 'translate_base.php');
require_once( dirname(__FILE__) . '/../lib/I2CE_Date.php');







wikiLogin();

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
    }

}







/*************************************
 *
 *  processing of modules
 *
 *******************************************/

$found_modules = getAvailableModules();

I2CE::setupFileSearch(); //reset the file search.
$classFileSearch = I2CE::getFileSearch();

$mod_class_paths = array();
foreach (array_keys($found_modules) as $module) {
    $mod_factory->loadPaths($module,'CLASSES',true,$classFileSearch); //load all template paths
}




/**
 * The autoload function is used to load class files when needed
 * 
 * This function will be used to load any class files when they
 * are required instead of in every file.
 * 
 * It searchs the configuration array for the common class directory
 * as well as the class directory specifically for this project.
 * @global array
 * @param string $class_name The name of the class being loaded
 */
function i2ce_class_autoload( $class_name ) {
    global $classFileSearch;
    $class_file = $classFileSearch->search( 'CLASSES', $class_name . '.php' );


    $class_found = false;
    if ( $class_file ) {
        require_once($class_file);
        if (class_exists($class_name,false) || interface_exists($class_name,false)) {
            $class_found = true;
        }  else {
            I2CE::raiseError("Defintion for class {$class_name} is not contained in {$class_file}", E_USER_WARNING);
        }
    }
    if (!$class_found) {
        $classes = I2CE_ModuleFactory::callHooks('autoload_search_for_class',$class_name);
        $count = count($classes);
        foreach ($classes as $class) {
            if ((!is_string($class)) || strlen($class)  == 0) {
                continue;
            }
            if (false === eval($class)) {
                I2CE::raiseError("While looking for $class_name, could not parse: $class");
            }
            if (class_exists($class_name,false)) {
                $class_found = true;
                break;
            }
        }
    }
    if (!$class_found) {
        $debug = debug_backtrace();
        $msg =  "Cannot find the defintion for class ({$class_name})";
        if (array_key_exists(1,$debug) && array_key_exists('line',$debug[1])) {
            $msg .= "called from  line " .$debug[1]['line'] .' of file ' . $debug[1]['file'] ;
        }
        $msg .= "\nSearch Path is:\n" 
            . print_r(I2CE::getFileSearch()->getSearchPath('CLASSES'), true); 
//        I2CE::raiseError( $msg, E_USER_NOTICE);
    }
} 

spl_autoload_register('i2ce_class_autoload');


I2CE::longExecution(array('memory_limit'=> (512 * 1048576)));


/*****************************************
 *  make the module list pages
 *****************************************/

$module_packages =array();
$wout = array();
$wout['all'] ="__PAGE:iHRIS Module List\nThis is a list of all the modules available in the iHRIS Suite\n";
foreach ($packages as $pkg=>$info) {
    $wout[$pkg] = '__PAGE:' . $info['name'] ." Module List\n";
    $wout[$pkg] .= "This is a list of all modules available in version " . $versions[$pkg] . ' of the package [' . $info['bzr'] . ' ' . $info['name'] ."]\n";
}


$comps = array(
    'atleast' => 'at least ',
    'atmost' => 'at most ',
    'exactly' => 'exactly ',
    'greaterthan' => 'greater than ',
    'lessthan' => 'less than ');

ksort($found_modules,SORT_STRING);
$classObjs = array();
$fuzzys = array();
$fuzzys_CLI = array();





$all_methods = array();
$module_dirs = array();
foreach ($found_modules as $module=>$top_module) {
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
    if (!$file) {
        I2CE::raiseError("No file recored for $module");
        continue;
    }
    $file_base = substr(dirname($file),strlen($packages[$current_pkg]['dir']));
    $module_dirs[$module] = $current_pkg . '/' . $file_base;
    $file_base = ltrim($file_base ,'/');    
    $wout['all'] .= "*[[" .  $packages[$current_pkg]['name'] . ' Module List' . $title_append. '#' . $module . ' | ' . 
        $module . "]] is a part of <span style='color:"  . $packages[$current_pkg]['color'] . "'>" .  $packages[$current_pkg]['name'] . "</span>" 
        . " version  {$versions[$current_pkg]} \n";

    $wout[$current_pkg] .=  "==" . $module . "==\n";
    $vers ='?';
    $storage->setIfIsSet($vers,"/config/data/$module/version");
    $text = '';
    if ($storage->setIfIsSet($text,"/config/data/$module/displayName")) {
        $wout[$current_pkg] .= "This describes version $vers of the module $text ($module) \n";
    } else {
        $wout[$current_pkg] .= "This describes version $vers of the module $module\n";
    }
    if ($module == $top_module) {
        $wout[$current_pkg] .= "It is the top module of this package\n";
    }
    $wout[$current_pkg] .= "*Source: [" . $packages[$current_pkg]['bzr_files'] . '/'. $file_base . "  $pkg/$file_base ]\n";
    if ($storage->setIfIsSet($className,"/config/data/$module/class/name")) {
        $classObj = null;
        $classObj = new $className();
        if (!$classObj instanceof I2CE_Module) {
            die("No class asscoaited to $className ");
        }

        $classObjs[$module] = $classObj;
        $wout[$current_pkg] .= "*Module Class: The module class is implemented by [[Class: $className{$title_append}| $className]]\n"; 
        $fuzz = $classObj->getMethods();
        if (count($fuzz) > 0) {
            $wout[$current_pkg] .= "*Fuzzy Methods:\n";
            foreach ($fuzz as $mc=>$im) {
                if (!preg_match('/^(.*)\->(.*)$/',$mc,$matches)) {
                    continue;
                }
                $fuzz_class = $matches[1];
                $fuzz_method = $matches[2];
                $wout[$current_pkg] .= "**Implements the method [[Class: $fuzz_class{$title_append}#$fuzz_method() | $fuzz_class->$fuzz_method() ]] ".
                    "via [[Class: $className{$title_append}#$im() | $im()]]\n";
                if (!array_key_exists($fuzz_class,$fuzzys)) {
                    $fuzzys[$fuzz_class] = array();
                }
                if (!array_key_exists($fuzz_method, $fuzzys[$fuzz_class])) {
                    $fuzzys[$fuzz_class][$fuzz_method] = array();
                }
                $fuzzys[$fuzz_class][$fuzz_method][]=array('implementing_class'=>$className,'implementing_method'=>$im);
                if (!array_key_exists($fuzz_class, $all_methods)) {
                    $all_methods[$fuzz_class] = array('fuzz'=>array());
                }
                $all_methods[$fuzz_class]['fuzz'][] = $fuzz_method;
            }                
            
            $fuzz = $classObj->getCLIMethods();
            if (count($fuzz) > 0) {
                $wout[$current_pkg] .= "*Command Line Inteterprecter (CLI) Fuzzy Methods:\n";
                foreach ($fuzz as $mc=>$im) {
                    if (!preg_match('/^(.*)\->(.*)$/',$mc,$matches)) {
                        continue;
                    }
                    $fuzz_class = $matches[1];
                    $fuzz_method = $matches[2];
                    $wout[$current_pkg] .= "**Implements the CLI method [[Class: $fuzz_class{$title_append}#$fuzz_method() | $fuzz_class->$fuzz_method() ]] ".
                        "via [[Class: $className{$title_append}#$im() | $im()]]\n";
                    if (!array_key_exists($fuzz_class,$fuzzys_CLI)) {
                        $fuzzys_CLI[$fuzz_class] = array();
                    }
                    if (!array_key_exists($fuzz_method, $fuzzys_CLI[$fuzz_class])) {
                        $fuzzys_CLI[$fuzz_class][$fuzz_method] = array();
                    }
                    $fuzzys_CLI[$fuzz_class][$fuzz_method][]=array('implementing_class'=>$className,'implementing_method'=>$im);
                    if (!array_key_exists($fuzz_class, $all_methods)) {
                        $all_methods[$fuzz_class] == array('fuzz_CLI'=>array());
                    }
                    $all_methods[$fuzz_class]['fuzz_CLI'][] = $fuzz_method;
                }
            }
        }
    }    
    if ($storage->setIfIsSet($text,"/config/data/$module/description")) {
        $wout[$current_pkg] .= "*Description: " . str_replace( "\n", '<br/>', trim($text)) . "\n";
    }
    foreach (array('requirement'=>"Requirements",'conflict'=>"Conflicts") as $tag=>$desc) {
        if (!$storage->is_parent("/config/data/$module/$tag")) {
            continue;
        }
        $wout[$current_pkg] .= "*" . $desc . ":\n";        
        foreach ($storage->traverse("/config/data/$module/$tag") as $m=>$info) {
            $v = array();
            foreach ($info as $i) {
                if (!$i->is_scalar('operator') || !$i->is_scalar('version')) {
                    continue;
                }
                $v[] = $comps[strtolower($i->operator)] . $i->version;
            }
            $wout[$current_pkg] .= "**[[iHRIS Module List{$title_append}#$m | $m]] " . implode(' and ',$v) . "\n";
        }
    }
    $enable_list = array();
    $storage->setIfIsSet($enable_list,"/config/data/$module/enable",true);
    if (count($enable_list) > 0) {
        foreach ($enable_list as &$e) {
            $e = "[[iHRIS Module List{$title_append}#$e | $e]]";
        }
        $wout[$current_pkg] .= "*Optionally Enables: " . implode(" ", $enable_list)  . "\n";
        unset($e); //dereference
    }
    $paths = array();
    $storage->setIfIsSet($paths,"/config/data/$module/paths",true);
    if (count($paths) > 0) {
        $wout[$current_pkg] .= "*Paths:\n";
        foreach ($paths as $cat=>$ps) {
            foreach ($ps as &$p) {
                if ($p[0] == '/') {
                    $p .= ' ';
                    continue;
                }
                $p = ltrim($p,'./');
                $p = '[' . $packages[$current_pkg]['bzr_files'] . '/' . $file_base . '/' . $p .' ' . $file_base . '/' . $p . '] ';
            }
            unset($p);
            $wout[$current_pkg] .= "**" . ucwords(strtolower($cat)) . ": " .  implode(",",$ps) ;
            if ($cat == 'CLASSES') {
                $wout[$current_pkg] .= "{{MODULECLASSLIST:$module}}";
            } else    if ($cat == 'MODULES') {
                $wout[$current_pkg] .= "{{MODULESUBMODULELIST:$module}}";
            } else    if ($cat == 'TEMPLATES') {
                $wout[$current_pkg] .= "{{MODULETEMPLATELIST:$module}}";
            }
            $wout[$current_pkg] .= "\n";
        }
    }
    
}




/***************************************
 * functions to help to translate phpdoc to wiki for form classes
 **************************************/





$php_types = array(
    'string'=>'http://www.php.net/manual/en/language.types.string.php',
    'bool'=>'http://www.php.net/manual/en/language.types.boolean.php',
    'boolean'=>'http://www.php.net/manual/en/language.types.boolean.php',
    'int'=>'http://www.php.net/manual/en/language.types.integer.php',
    'float'=>'http://www.php.net/manual/en/language.types.float.php',
    'array'=>'http://www.php.net/manual/en/language.types.array.php',
    'null'=>'http://www.php.net/manual/en/language.types.null.php',
    'object'=>'http://www.php.net/manual/en/language.types.object.php',
    'resource'=>'http://www.php.net/manual/en/language.types.resource.php',
    'mixed'=>'http://www.php.net/manual/en/language.pseudo-types.php',
    'callback'=>'http://www.php.net/manual/en/language.pseudo-types.php',
    'numeric'=>'http://www.php.net/manual/en/language.pseudo-types.php',
    'domnode'=>'http://www.php.net/manual/en/class.domnode.php',
    'domnodelist'=>'http://www.php.net/manual/en/class.domnodelist.php',
    'domdocument'=>'http://www.php.net/manual/en/class.domdocument.php',
    'domtext'=>'http://www.php.net/manual/en/class.domtext.php',
    'domxpath'=>'http://www.php.net/manual/en/class.domxpath.php',
    'traversable'=>'http://www.php.net/manual/en/class.traversable.php',
    'countable'=>'http://www.php.net/manual/en/class.countable.php',
    'iterator'=>'http://www.php.net/manual/en/class.iterator.php',
    'outeriterator'=>'http://www.php.net/manual/en/class.outeriterator.php',
    'recursiveiterator'=>'http://www.php.net/manual/en/class.recursiveiterator.php',
    'seekableiterator'=>'http://www.php.net/manual/en/class.seekableiterator.php',
    'iteratoraggregate'=>'http://www.php.net/manual/en/class.iteratoraggregate.php',
    'arrayaccess'=>'http://www.php.net/manual/en/class.arrayaccess.php',
    'serializable'=>'http://www.php.net/manual/en/class.serializable.php'
    );



function cleanupComment($comment) {
    $lines = explode("\n",$comment);
    foreach ($lines as $i=>&$l) {
        $l = trim($l);
        if (!$l) {
            continue;
        }
        if ($l[0] == '*') {
            $l = substr($l,1);
        }
    }
    unset($l); //dereference
    $lines[0] = substr($lines[0],3); // ignore the /**
    $lines[count($lines)-1] = substr($lines[count($lines)-1],0,-1);
    return trim(implode("\n",$lines));
}







//line number in key 2
//token string in key 1
//token code in key 0

//comment token codes:
//  T_COMMENT
// foreach (array(309,307,370,354,352,366) as $code) {
//     echo $code . ":" . token_name($code) . "\n";
// }
//print_r($tokens);die();


$processed = array();
function processFileTokens ($file) {
    global $processed;
    if (array_key_exists($file,$processed)) {
        return false;
    }
    $processed[$file] = true;
    if (substr($file, strrpos($file, '.') + 1) != 'php') {
        return false;
    }    
//     system("php -l $file  > /dev/null", $lint);
//     if ($lint != 0) {
//         //try again:
//         system("php -l $file", $lint);
//         if ($lint != 0) {
//             I2CE::raiseError("Skipping $file as it does not lint ($lint)");
//             return false;
//         }
//     }
    $tokens = token_get_all(file_get_contents($file));
    if (!is_array($tokens)) {
        I2CE::raiseError("No tokens for $file");
    }
    $classes = array();
    $class = false; //we start in the global scope

    reset($tokens);    
    while( ($pair = each($tokens)) !== false) {
        list($index,$token) = $pair;
        if ($class) {
            $check = array(T_DOC_COMMENT, T_PUBLIC,T_PROTECTED, T_PRIVATE, T_ABSTRACT);
        } else {
            $check = array(T_DOC_COMMENT,  T_CLASS, T_ABSTRACT);
        }
        if (is_array($token) && in_array($token[0] , $check)) {
            if ($token[0] == T_DOC_COMMENT) {
                $comment = cleanupComment($token[1]);
                $declr = false;
                $following = '';
                $in_class = false;
                $abstract = false;
            } else if ($token[0] == T_ABSTRACT) {
                $comment = '';
                $abstract = true;
                $following .= $token[1];                
                $declr = false;
                $in_class = false;
            } else if ($token[0] == T_CLASS) {
                $comment = '';
                $in_class = true;
                $declr = false;
                $following = $token[1];                
                $abstract = false;
            } else {
                $comment = '';
                $declr = strtolower(substr(token_name($token[0]),2));
                $following = $token[1];                
                $in_class = false;
                $abstract = false;
            }
            $line = false;
            $desc = '';
            $in_func = false;
            $in_var =false;
            $got_name = false;
            $var_list = array();
            $static = false;
            $in_default = false;
            $in_var_list =false;
            $extends = false;
            $implements = array();
            $in_implements = false;
            $open_paren = false;
            $in_extends = false;
            $in_const = false;
            $by_ref = false;
            $returns_by_ref = false;
            $final = false;
            while( ($pair = each($tokens)) !== false) {
                list($index,$token2) = $pair;
                if ($token2 === '{') {
                    if (!$in_class)  {
                        I2CE::raiseError("BadnessA $file\n[$following]\n[$comment]\n$in_default/$default" , E_USER_WARNING);
                        return false;
                    }
                    break;
                }
                if ($token2 === '}') {
                    //maybe ending the class have an unused comment;
                    break;
                }
                if ($token2 === ')') {
                    if (!$in_func)  {
                        if (!$in_class && !$declr) {
                            I2CE::raiseError("BadnessB $file/$following", E_USER_WARNING);
                            return false;
                        }
                    }
                    $following .= ')';
                    if (!$in_default) {                        
                        break;
                    } else { 
                        //we are in a default value of an arg
                        if ($open_paren) {
                            $open_paren = false;
                            $var_list[$var_list_which]['default'] .= ')';
                            $following  .=")";
                            continue;
                        } else {
                            break;
                        }
                    }
                }
                if ($token2 === '(') {
                    if ($in_func) {
                        if ($in_var_list) {
                            if (!$in_default) {
                                I2CE::raiseError("BadnessC0 $file", E_USER_WARNING);
                                return false;
                            }
                            $open_paren = true;
                        } else {
                            $following .= '(';
                            $in_var_list =true;
                            continue;
                        }
                    } else if ($declr) {
                        if (!$in_default) {
                            I2CE::raiseError("BadnessC1 $file", E_USER_WARNING);
                            return false;
                        } else{
                            $default .= '(';
                            $following .= '(';
                            continue;
                        }
                    } else {
                        //can happen if there is aphpdoc embeeded in a function in a class.  just ignore it for now as it will get warned later
                        //I2CE::raiseError("BadnessC2 $file\n$following",E_USER_WARNING);
                        break 2;
                    }
                }
                if ($token2 === ',') {
                    if (!$in_implements) {
                        if (!$in_class && !$in_func &&  $declr && $in_default) {
                            $default .= ')';
                            $following .= ')';
                            continue;
                        } else {
                            $in_default = false;
                            if (!$in_var_list)  {
                                I2CE::raiseError("BadnessD $file/$following",E_USER_WARNING);
                                return false;
                            }
                        }
                    }
                    $following .= ',';
                    continue;
                }
                if ($token2 === ';') {
                    if ($in_const) {
                        break ;
                    } else if ($declr && (!$in_class && !$in_func)) {
                        break ;
                    } else     if (!$declr  || $in_class || $in_func) {
                        I2CE::raiseError("BadnessE $file/$following",E_USER_WARNING);
                        return false;
                    }
                    $following .= ';';
                    break;
                }
                if ($token2 === '=') {
                    $in_default = true;
                    if ($in_class ) {
                        I2CE::raiseError("BadnessF $file",E_USER_WARNING);
                        return false;
                    }
                    if ($in_var_list) {
                        //in function arguement list
                        $var_list[$var_list_which]['default'] = '';
                    } else if ($declr) { 
                       //variable decl.
                        $default = '';                        
                    } else if ($in_const) {
                        $default = '';
                    } else {
                        I2CE::raiseError("BadnessG $file",E_USER_WARNING);
                        return false;                        
                    }
                }
                if ($token2 == '&') {
                    if ($in_func)  {
                        if ( $in_var_list ) {
                            $by_ref = true;
                            $following .= '&';
                            continue;
                        } else {
                            $returns_by_ref = true;
                            $following .= '&';
                            continue;
                        }
                    } else {
                        I2CE::raiseError("unexpected token $file:\n" . print_r($token2,true) . "\n$following",E_USER_WARNING);
                    }
                }
                if (is_string($token2)) {
                    if ($in_default) {
                        if ($in_var_list) {
                            $var_list[$var_list_which]['default'] .= $token2;
                        } else {
                            $default .= $token2;
                        }
                    }
                }
                if ( !is_array($token2)) {
                    if( !$in_default) {
                        I2CE::raiseError("unexpected token $file:\n" . print_r($token2,true) . "\n$following",E_USER_WARNING);
                        return false;
                    }
                    continue;
                }       
                if ($in_default) {
                    if ($in_var_list) {
                        $var_list[$var_list_which]['default'] .= $token2[1];
                    } else {
                        $default .= $token2[1];
                    }
                }                
                switch($token2[0]) {                
                case T_CONST:
                    $in_const = true;
                    $following .= $token2[1];
                    break;
                case T_DOC_COMMENT:
                    if ($t_com =  cleanupComment($token2[1])) {
                        $comment .= "\n" .$t_com;
                    }
                    break;
                case T_CLASS:
                    if ($in_func) {
                        I2CE::raiseError("baddness0 on " . token_name($token2[0]) . " token $file" . "\nline:"  .$token2[2] . "\ntoken:" . $token2[1],E_USER_WARNING);
                        return false;
                    } else if ($in_class) {
                        I2CE::raiseError("baddness1 on " . token_name($token2[0]) . " token $file" . "\nline:"  .$token2[2] . "\ntoken:" . $token2[1],E_USER_WARNING);
                        return false;
                    }
                    $in_class = true;
                    $following .= $token2[1];
                    break;
                case T_FUNCTION:
                    if ($in_func) {
                        I2CE::raiseError("baddness0 on " . token_name($token2[0]) . " token $file" . "\nline:"  .$token2[2] . "\ntoken:" . $token2[1],E_USER_WARNING);
                        return false;
                    } else if ($in_class) {
                        I2CE::raiseError("baddness1 on " . token_name($token2[0]) . " token $file" . "\nline:"  .$token2[2] . "\ntoken:" . $token2[1],E_USER_WARNING);
                        return false;
                    }
                    $in_func = true;
                    $following .= $token2[1];
                    break;  
                case T_IMPLEMENTS:
                    $in_extends = false;
                    $in_implements = true;
                    if ($in_class) {
                        $following .= $token2[1];                
                        break;
                    } else { //shouldn't happen
                        I2CE::raiseError("baddness on " . token_name($token2[0]) . " token $file" . "\nline:"  .$token2[2] . "\ntoken:" . $token2[1],E_USER_WARNING);
                        return false;
                    }
                case T_EXTENDS:
                    $in_extends = true;
                    if ($in_class) {
                        $following .= $token2[1];                
                        break;
                    } else { //shouldn't happen
                        I2CE::raiseError("baddness on " . token_name($token2[0]) . " token $file" . "\nline:"  .$token2[2] . "\ntoken:" . $token2[1],E_USER_WARNING);
                        return false;
                    }
                case T_VARIABLE:
                    if ($in_class) {
                        I2CE::raiseError("baddness0 on " . token_name($token2[0]) . " token $file" . "\nline:"  .$token2[2] . "\ntoken:" . $token2[1],E_USER_WARNING);
                        return false;                        
                    } else if ($in_func) {
                        $var_list_which = $token2[1];
                        $var_list[$var_list_which] = array('default'=>'', 'by_reference'=>$by_ref);
                        $by_ref =false;
                    } else if ($declr) {       
                        $got_name = $token2[1];
                        $line = $token2[2];
                    } else{                        
                        //can happen if there is aphpdoc embeeded in a function in a class.  just ignore it for now as it will get warned later
                        //I2CE::raiseError("baddness1 on " . token_name($token2[0]) . " token $file" . "\nline:"  .$token2[2] . "\ntoken:" . $token2[1]);
                        break 2;
                    }
                    $following .= $token2[1];                
                    break;
                case T_STRING:
                    if ($in_implements) {                       
                        $implements[] = $token2[1];
                    } else if ($in_extends) {
                        $extends = $token2[1];
                    } else if (!$got_name) {
                        $got_name = $token2[1];
                        $line = $token2[2];
                    } else if ($in_var_list) {
                        //assuming it is a variable type hint
                        $hint = $token2[1];
                    } else {
                        if (!$in_default) {
                            print_r($token2);
                            var_dump($in_var_list);
                            I2CE::raiseError("baddness on " . token_name($token2[0]) . " token $file" . "\nline:"  .$token2[2] . "\ntoken:" . $token2[1],E_USER_WARNING);
                            return false;
                        }
                    }
                    $following .= $token2[1];                
                    break;
                case T_PRIVATE:
                case T_PROTECTED:
                case T_PUBLIC:
                    $declr = strtolower(substr(token_name($token2[0]),2));
                    $following .= $token2[1];                
                    break;
                case T_ABSTRACT:
                    $abstract = true;
                    $following .= $token2[1];                
                    break;
                case T_STATIC:
                    $static = true;
                    $following .= $token2[1];                
                    break;
                case T_FINAL:
                    $final = true;
                    $following .= $token2[1];                
                    break;
                case T_ENCAPSED_AND_WHITESPACE:
                case T_STRING:
                case T_WHITESPACE:
                case T_COMMENT:
                    $following .= $token2[1];                
                    break;
                default:
                    if (!$in_default) {
                        break 2;           
                    }
                }
                continue;
            }
            //we have finished readding all that we need.
            if ($in_class) {
                $class = $got_name;
                if (array_key_exists($class,$classes)) {
                    I2CE::raiseError("baddness repeated class $class in $file");
                    return false;
                }
                $classes[$class]['abstract'] = $abstract;
                $classes[$class]['comment'] = $comment;
                $classes[$class]['following'] = $following;
                $classes[$class]['extends'] = $extends;
                $classes[$class]['implements'] = $implements;
                $classes[$class]['functions'] = array();
                $classes[$class]['vars'] = array();
                $classes[$class]['constants'] = array();
                $classes[$class]['line']=$line;
            } else if ($in_const) {
                $const = $got_name;
                $classes[$class]['constants'][$got_name] =array(
                    'default'=>$default,
                    'comment'=>$comment,
                    'line'=>$line,
                    'following'=>$following
                    );
            } else  if ($in_func) {
                if (!$class ) {
                    continue;
                }
                $function = $got_name;
                if (array_key_exists($function,$classes[$class]['functions'])) {
                    I2CE::raiseError("baddness repeated function $function in $file", E_USER_WARNING);
                    return false;
                }
                $classes[$class]['functions'][$function] = array(
                    'declaration'=>$declr,
                    'comment'=>$comment,
                    'abstract'=>$abstract,
                    'final'=>$final,
                    'static'=>$static,
                    'returns_by_ref'=>$returns_by_ref,
                    'following'=>$following,
                    'line'=>$line,
                    'var_list'=>$var_list);
            } else  if ($declr) { //we are in a variable decl
                if (!$class ) {
                    continue;
                }
                $var = $got_name;
                if (!is_string($var)) {
                    I2CE::raiseError("baddness variable naeme ($var) in $file/$following/$comment",  E_USER_WARNING);
                }
                if (array_key_exists($var,$classes[$class]['vars'])) {
                    I2CE::raiseError("baddness repeated var $var in $file");
                    return false;
                }
                $default = false;
                if (preg_match('/=(.*);/',$following,$matches)) {
                    $default = $matches[0];
                }
                $classes[$class]['vars'][$var] = array(
                    'declaration'=>$declr,
                    'comment'=>$comment,
                    'static'=>$static,
                    'following'=>$following,
                    'default' => $default,
                    'line'=>$line
                    );                

            } else {
                if ($comment = trim($comment)) {
                    I2CE::raiseError("Unused comment in $file:\n$comment\n");
                }
            }

        }
    }
    return $classes;
}


function possibleInternalReference($string) {
    global $php_types;
    if (array_key_exists(strtolower($string),$php_types)) {
        return "[" .  $php_types[strtolower($string)] . " $string ]";
    } else {
        return '{{MAYBECLASS:' . $string . '}}';
    }
}



$class_file_list = array();
$fout = array(); //output for clases
$all_vars = array();
$extensions = array();
$module_class_list = array();
function preWiki($file,$module) {
    global $title_append;
    global $extensions;
    global $fout;
    global $class_file_list;
    global $current_pkg;
    global $packages;    
    global $all_methods;
    global $all_vars;
    global $module_class_list;
    global $versions;
    if (!$current_pkg || !array_key_exists($current_pkg,$packages)) {
        I2CE::raiseError("Bad current package $current_pkg for module $module and file $file");
        return;
    }
    if (substr($file,0,strlen($packages[$current_pkg]['dir'])) != $packages[$current_pkg]['dir']) {
        I2CE::raiseError("File $file is not within the packackage $current_pkg"); //should not happen
        return;
    }    
    $file_base = substr($file,strlen($packages[$current_pkg]['dir']));
    $file_base = ltrim($file_base ,'/');
    $classes = processFileTokens($file);
//    if (preg_match('/I2CE_Page\.php/',$file)) {      print_r($classes);die();    }
    if (!$classes) {
        return;
    }
    if (count($classes) != 1) {
        I2CE::raiseError("Unexpected number of classes in $file");
        return;
    }
    reset($classes);
    $class = key($classes);
    $details = current($classes);
    if (!array_key_exists($module,$module_class_list)) {
        $module_class_list[$module] = array();
    }
    $module_class_list[$module][] = $class;
    if (!array_key_exists($class,$class_file_list)) {
        $class_file_list[$class] = array();
    }
    if (!array_key_exists($class, $all_methods)) {
        $all_methods[$class] = array();
    } 
    foreach (array('protected','private','public') as $declaration) {
        if (!array_key_exists($declaration,$all_methods[$class])) {
            $all_methods[$class][$declaration] = array();
        }
    }
    if (!array_key_exists($class, $all_vars)) {
        $all_vars[$class] = array();
    }
    $class_file_list[$class][] = array('file'=>$file_base,'pkg'=>$current_pkg);
    if ($details['abstract']) {
        $details['abstract'] = " ''abstract''";
    }
    $out = "This article describes the{$details['abstract']} class ''$class'' .\n";
    if ($details['extends']) {
        if (!array_key_exists($class,$extensions)) {
            $extensions[$class] = array();
        }
        $extensions[$class][$current_pkg . '/' . $file_base] = $details['extends'];
        $out .= "*Extends the class: " . possibleInternalReference($details['extends']) . ".\n";
    }
    if (count($details['implements'])>0) { 
        foreach ($details['implements'] as $impl) {
            $out .= "*Implements the interface " . possibleInternalReference($impl) . "\n";
        }
    }
    $out .= "{{SHOWINHERITENCE:$file_base:$class}}";
    $out .= "*Location: Part of the module [[" . $packages[$current_pkg]['name'] . " Module List{$title_append}#$module|$module]] in the package [{$packages[$current_pkg]['bzr']} {$packages[$current_pkg]['name']}] {$versions[$current_pkg]}\n";
    if ($details['line']) {
        $out .= "*Source: Defined in the file [{$packages[$current_pkg]['bzr_annotate_files']}/{$file_base}#L" . $details['line']  ." {$file_base}] on line " .  $details['line']. "\n";
    } else {
        $out .= "*Source: Defined in the file [{$packages[$current_pkg]['bzr_annotate_files']}/{$file_base} {$file_base}]\n";
    }
    $comment =
        explode("\n",
                preg_replace(
                    array(
                        '/[©@]*\s*copyright.*?licenses\/>\./is',
                        '/^\s*@package\s.*/im',
                        '/^\s*@subpackage\s.*/im',
                        '/^\s*@version\s.*/im',
                        '/^\s*@access\s.*/im'
                        )
                    ,"\n",$details['comment']
                    )
            );    
    $tags[] = array();
    foreach ($comment as &$c)  {
        if (preg_match('/^\s*@see\s+([a-z0-9_]+)\s*/i',$c,$matches)) {
            $tags['See'][] = '*See: ' . possibleInternalReference($matches[1]) . "\n";
            $c = '';
            continue;
        }
        foreach (array('Author','Since') as $tag) {
            if (preg_match("/^\s*@$tag\s*(.*)$/i",$c,$matches)) {
                $tags[$tag][]  = "*$tag: {$matches[1]}\n";
                $c= '';
                continue;
            }
        }
        $c = trim($c) ;
    }
    unset($c); //defreence
    foreach ($tags as $ts) {
        $out .= implode("\n",$ts);
    }
    if ($comment = trim(implode(" ",$comment))) {
        $comment = preg_replace("/\n+/s"," ",$comment);
        $out .= $comment . "\n";
    }   
    $out .= "{{SHOWFORMFIELDS:$class}}";
    if (count($details['constants']) > 0) {
        $out .= "==Constants==\n";
        foreach ($details['constants'] as $const =>$c_details) {
            $out .= "===$class::$const===\n";
            if ($comment = trim($c_details['comment'])) {
                $out .= $comment ."\n";
            }
            $out .= "Defined as: " . trim(ltrim(trim($c_details['default']),'=')) . "\n";
        }
    }
    if (count($details['vars']) > 0) {
        $out .= "==Variables==\n";
        foreach ($details['vars'] as $var=>$var_details) {
            $out .= "===$var===\n";
            $comment = explode("\n",$var_details['comment']);            
            $type_details = '';
            foreach ($comment as &$c) {
                $c = trim($c);
                if (preg_match('/^\s*@var\s+(static|protected|public|private)?\s*([a-z0-9_]*)\s*(\$[a-z0-9_]+)(.*)$/i',$c,$matches)) {
                    if ($var == $matches[3]) {
                        $type_details = possibleInternalReference($matches[2]) . " " . $var . "\n";
                        $c = trim($matches[4]);
                    }
                } else if (preg_match('/^\s*@var\s+(static|protected|public|private)?\s*([a-z0-9_]+)(.*)$/i',$c,$matches)) {
                    $type_details = possibleInternalReference($matches[2]) . ' ' . $var . "\n";
                    $c = trim($matches[3]);
                }

            }
            unset($c); //dereference
            if (!$type_details) {
                $type_details = $var . "\n";
            }
            if ($var_details['declaration']) {
                $declr = $var_details['declaration'];
            } else {
                $declr = 'public';
            }
            $type_details =$declr .' ' . $type_details;
            if (!array_key_exists($declr,$all_vars[$class])) {
                $all_vars[$class][$declr] = array();
            }
            $all_vars[$class][$declr][] = $var;
            if ($var_details['static']) {
                $type_details = 'static ' . $type_details;
            }
            if ($comment = trim(implode("\n", $comment))) {
                $out .= preg_replace("/\n+/s"," ",$comment) . "\n";
            }
            if ($var_details['line']) {
                $out .= "*Defined in [" . 
                    $packages[$current_pkg]['bzr_annotate_files'] . '/' . $file_base . '#L' . $var_details['line'].' ' . $current_pkg . '/' . $file_base . '] on line '
                    . $var_details['line'] . "\n";
            }
            $out .= "*Type: " . $type_details . "\n";
            if ($var_details['default']) {
                $out .= "*Default Value:" . $var_details['default'] . "\n";
            }
        }
    }
    if (count($details['functions']) > 0) {
        $out .= "==Methods==\n";
        ksort($details['functions'],SORT_STRING);
        foreach ($details['functions'] as $func=>$f_details) {
            $out .= "===$func()===\n";
            $comment = explode("\n",$f_details['comment']);
            $returns = false;
            $f_comment = '';
            $var_details = array();
            foreach ($f_details['var_list'] as $var =>$v_d) {
                $var_details[$var] = '';
            }
            $sig_details= '';            
            $sig_details = 'function ' . $func . "(" . implode(",", array_keys($f_details['var_list'])) . ")\n";
            if ($f_details['returns_by_ref']) {
                $sig_details = '&' . $sig_details;
            }
            if ($f_details['declaration']) {
                $declaration = $f_details['declaration'];
            } else {
                $declration = 'public';
            }
            $sig_details = $declaration . ' ' . $sig_details;
            $all_methods[$class][$declaration][] = $func;
            if ($f_details['final']) {
                $sig_details = 'final ' . $sig_details;
            }
            if ($f_details['static']) {
                $sig_details = 'static ' . $sig_details;
            }
            if ($f_details['abstract']) {
                $sig_details = 'abstract ' . $sig_details;
            }
            $current_var =false;
            $in_var = false;
            $in_ret = false;
            foreach ($comment as &$c) {
                //@param DOMNode $node
                //@param I2CE_Template $template
                //@return DOMNode
                $c = trim($c);
                if (preg_match('/^\s*@param\s+([a-zA-Z0-9_]+)\s+(\&?\$[a-zA-Z0-9_]+)(.*)$/i',$c,$matches)) {
                    if (array_key_exists(ltrim($matches[2],'&'),$var_details) ) { //it  actually refers to a var in the arguemnet list
                        $current_var = ltrim($matches[2],'&');
                        $in_var = $current_var;
                        $var_details[$current_var] = possibleInternalReference($matches[1]) . ' ' . $matches[2] . "\n";
                        if ($matches[3] = trim(ltrim($matches[3],"\n\t .,-:;"))) {
                            $var_details[$current_var] .= '<br/>' . $matches[3] . "\n";
                        }
                    } else {
                        $in_var = false;
                        $f_comment .= $c . "\n";
                    }
                } else if (preg_match('/^\s*@returns?\s+(.*?)$/i',$c,$matches)) {
                    $in_ret = true;
                    $in_var = false;
                    if ($matches[1] = trim($matches[1])) {
                        list($first,$rest) = array_pad(explode(" ",$matches[1],2),2,'');
                        $returns = possibleInternalReference($first);
                        if ($rest = trim(ltrim($rest,"\n\t .,-:;"))) {
                            $returns .= '<br/>' . $rest . "\n";
                        }
                    }
                } else {
                    if ($in_ret) {
                        $returns .= $c . "\n";
                    } else if ($in_var) {
                        $var_details[$in_var] .= $c . "\n";
                    } else {
                        $f_comment .= $c . "\n";
                    }
                }
            }
            unset($c); //dereference
            if ( $f_comment = trim($f_comment)) {
                $out .= preg_replace("/\n+/s"," ",$f_comment) . "\n";
            }
            if ($f_details['line']) {
                $out .= "*Defined in [" . 
                    $packages[$current_pkg]['bzr_annotate_files'] . '/' . $file_base . '#L' . $f_details['line'].' ' . $current_pkg . '/' . $file_base . '] on line '
                    . $f_details['line'] . "\n";
            }
            $out .= "*Signature: " . $sig_details;
            if (count($f_details['var_list']) > 0) {
                $out .= "*Parameters:\n";
                foreach ($f_details['var_list'] as $var=>$arg_details) {
                    if ($det = trim($var_details[$var])) {
                        $out .= "** " .  preg_replace("/\n+/s"," ",$det) . "\n";
                            //$out .= "** " . str_replace("\n", "<br/>" , $det) . "\n";
                    } else {
                        if ($arg_details['by_reference'])  {
                            $out .= "**&$var \n";
                        } else {
                            $out .= "**$var \n";
                        }
                    }
                    if ($arg_details['default']) {
                        $out .= "***Default Value: " . ltrim($arg_details['default'],' =') . "\n";
                    }
                }
            }
            if ($returns = trim($returns)) {
                $returns = preg_replace("/\n+/s"," ",$returns);
                if ($f_details['returns_by_ref']) {
                    $out .= "*Returns (By Reference): $returns\n";
                } else {
                    $out .= "*Returns: $returns\n";
                }
            }


        }
    }
    $fout[$current_pkg .'/'. $file_base] = $out;
}



function preWikiWalkDir($dir,$module) {
    if (is_link($dir)) {
        return;
    }
    if (basename($dir) == 'local') {
        return;
    }
    if (is_dir($dir) ) {
        foreach (glob("$dir/*") as $file) {
            preWikiWalkDir($file,$module);
        }
    } else if(is_file($dir)) {
        preWiki($dir,$module);
    }
}


/**************************************8
 *
 * post proocessing methods for classes
 *
 *************************************/


function getClassHierarchy($class,$file_base) {
    global $extensions;
    $ret = array('parents'=>array(),'children'=>array());    
    if (array_key_exists($class,$extensions) && array_key_exists($file_base,$extensions[$class])) { //we allow ambiguity in the first class definition file
        $p_class = $extensions[$class][$file_base];
        do {
            $ret['parents'][] = $p_class;
            if ( array_key_exists($p_class,$extensions) && count($extensions[$p_class]) == 1) {
                reset ($extensions[$p_class]);
                $p_class = current($extensions[$p_class]);
            } else {
                $p_class = false;
            }
        } while ($p_class);
    }
    //now we get the children
    $child_list = array($class =>&$ret['children']);    
    $check_children= array($class);    
    while (count($check_children) > 0) {
        $child = array_pop($check_children);
        foreach ($extensions as $c_class => $c_classes) {
            foreach ($c_classes as  $e_class) {
                if ($e_class == $child) { //we found an extension so $e_class has parent class $child
                    $child_list[$child][$c_class] = array();
                    $child_list[$c_class] = &$child_list[$child][$c_class];
                    $check_children[] = $c_class;
                }
            }
        }
    }
    return $ret;
}


function flattenChildren($children,$depth = 2) {
    $ret = '';
    foreach ($children as $child=>$details) {
        $ret .= str_pad('',$depth,'*') . ' {{MAYBECLASS:' . $child . "}}\n";
        if (count($details) > 0) {
            $ret .= flattenChildren($details,$depth+1);
        }
    }
    return $ret;
}







function getInheritedMethodsAndVars($class,$file_base) {
    global $extensions;
    global $all_methods;
    global $all_vars;
    global $title_append;
    $reg_methods = array();
    $fuzz_methods = array();
    $cli_methods = array();
    $vars = array();
    $base_methods = array();
    if (array_key_exists($class,$all_methods)) {
        foreach ($all_methods[$class] as  $methods) {
            $base_methods = array_merge($base_methods,$methods);
        }
    }
    list($pkg, $s_file_base)  =  explode("/",$file_base,2);
    if (array_key_exists($class,$extensions) && array_key_exists($file_base,$extensions[$class])) { //we allow ambiguity in the first class definition file
        $class = $extensions[$class][$file_base];        
    } else {
        return '';
    }
    do {
        //now we need to add in the inherited methods form $all_methods[$class]][puvblic/privat/protected/fuzz/fuzz_CLI]  = array(method1,method2,...)   
        if (array_key_exists($class,$all_methods)) {
            foreach (array('public','protected') as $declr) {
                if (array_key_exists($declr,$all_methods[$class])) {
                    foreach ($all_methods[$class][$declr] as $method) {
                        if (!array_key_exists($method,$reg_methods) && !in_array($method,$base_methods)) {                        
                            $reg_methods[$method] = "===$method()===\nThis $declr method is inherited from [[Class: $class{$title_append}#$method() | $class->$method()]]\n";
                        }
                    }
                }
            }
            if (array_key_exists('fuzz',$all_methods[$class])) {
                foreach ($all_methods[$class]['fuzz'] as $method) {
                    if (!array_key_exists($method,$fuzz_methods)  && !in_array($method,$base_methods)) {
                        $fuzz_methods[$method] = "===$method()===\nThis method is inherited from [[Class: $class{$title_append}#$method() | $class->$method()]]\n";
                    }
                }
            }
            if (array_key_exists('fuzz_CLI',$all_methods[$class])) {
                foreach ($all_methods[$class]['fuzz_CLI'] as $method) {
                    if (!array_key_exists($method,$cli_methods)  && !in_array($method,$base_methods)) {
                        $cli_methods[$method] = "===$method()===\nThis method is inherited from [[Class: $class{$title_append}#$method() | $class->$method()]]\n";
                    }
                }
            }
        }
        //now we need to add in the inherited varaibles from $all_vars[$class][puvblic/privat/protected] = array('var1',...''varn')
        if (array_key_exists($class,$all_vars)) {
            if (array_key_exists($class,$all_vars)) {
                foreach (array('public','protected') as $declr) {
                    if (array_key_exists($declr,$all_vars[$class])) {
                        foreach ($all_vars[$class][$declr] as $var) {
                            if (!array_key_exists($var,$vars)) {
                                $vars[$var] = "===$var===\nTheis $declr variable is inherited from [[Class: $class{$title_append}#$var | $class->$var]]\n";
                            }
                        }
                    }
                }
            }
        }
        if ( array_key_exists($class,$extensions) && count($extensions[$class]) == 1) {
            reset ($extensions[$class]);
            $class = current($extensions[$class]);
        } else {
            $class = false;
        }
    }  while ($class);
    $ret = '';
    if (count($reg_methods)>0) {
        $ret .= "==Inherited Methods==\n" . implode("\n",$reg_methods);
    }
    if (count($vars)>0) {
        $ret .= "==Inherited Variables==\n" . implode("\n",$vars);
    }
    if (count($fuzz_methods)>0) {
        $ret .= "==Inherited Fuzzy Methods==\n" . implode("\n",$fuzz_methods);
    }
    if (count($cli_methods) > 0) {
        $ret .= "==Inherited Fuzzy CLI Methods==\n" . implode("\n",$cli_methods);
    }
    return $ret;
}


/*****************************************
 *  get modules and their paths sorted out
 *****************************************/


$mod_class_paths = array();
foreach ($found_modules as $module=>$top_module) {
    $current_pkg = false;
    $current_pkg = $module_packages[$module];
    if (!array_key_exists($module,$module_packages)) {
        I2CE::raiseError("Could not deterine which package $module resides in"); //should not happen
        continue;
    }    
    I2CE::setupFileSearch(); //reset the file search.
    $fileSearch = I2CE::getFileSearch();
    $mod_factory->loadPaths($module,'CLASSES',true,$fileSearch); //load all template paths
    $ordered_paths = $fileSearch->getSearchPath('CLASSES'); //get the paths found with their
    $lib_dirs = array();
    foreach ($ordered_paths as $paths) {
        foreach ($paths as $path) {            
            $lib_dirs[] = $path;
        }
    }
    $mod_class_paths[$module] = $lib_dirs;
}

/*****************************************
 *  make the prewiki pages
 *****************************************/


if ($do_wiki_class = simple_prompt("Make Wiki pages for PHP classes?")) {
    foreach ($found_modules as $module=>$top_module) {
	$current_pkg = false;
	$current_pkg = $module_packages[$module];
	if (!array_key_exists($module,$module_packages)) {
	    I2CE::raiseError("Could not deterine which package $module resides in"); //should not happen
	    continue;
	}    
	if (count($mod_class_paths[$module]) == 0) {
	    //no classes in this module.  let's continue
	    continue;
	}
	foreach ($mod_class_paths[$module] as $dir) {
	    preWikiWalkDir($dir,$module);
	}
	//at some point we should now get any form classes defined in magic data
    }

    $form_storage = I2CE_MagicData::instance( "CLI_application_forms" );
    foreach ($found_modules as $module=>$top_module) {
	$current_pkg = false;
	$current_pkg = $module_packages[$module];
	if (!array_key_exists($module,$module_packages)) {
	    I2CE::raiseError("Could not deterine which package $module resides in"); //should not happen
	    continue;
	}  
	$mod_storage = I2CE_MagicData::instance( "CLI_application_mod_form:$module" );
	$mod_configurator =new I2CE_Configurator($mod_storage,false);
	if ($module != $mod_configurator->processConfigFile($storage->config->data->$module->file,true,false,false)) {
	    I2CE::raiseError("Unable to process config data for $module");
	    return false;
	}
	if ($mod_storage->is_parent("/modules/forms/FORMFIELD")) {
	    $form_storage->FORMFIELD = $mod_storage->modules->forms->FORMFIELD;
	}
	if ($mod_storage->is_parent("/modules/forms/formClasses")) {
	    foreach ($mod_storage->modules->forms->formClasses as $class => $fClass) {
		$fClass->module = $module;
		$fClass->package = $current_pkg;
	    }
	    $form_storage->formClasses = $mod_storage->modules->forms->formClasses;
	}
    }
    $form_fields = array();
    foreach ($form_storage->formClasses as $class=>$definition) {
	if (!$definition->is_scalar('extends')) {
	    continue;
	}
	$f_def = array();
	if ($definition->is_parent('fields') && count($definition->fields) > 0) {
	    foreach ($definition->fields as $name=>$f_definition) {
		if (!$f_definition instanceof I2CE_MagicDataNode){
		    continue;
		}
		if (!$f_definition->is_scalar('formfield')) {
		    continue;
		}
		$ff = $f_definition->formfield;
		if (!$form_storage->is_scalar("FORMFIELD/$ff")) {
		    continue;
		}
		$ff_long = $form_storage->FORMFIELD->$ff;            
		$f_def[$name] = "===$name===\nThe form field ''$name'' is implemented by [[Class: " . $ff_long . "{$title_append} |" . $ff . " ]]";
	    }
	}
	$form_fields[$class] = $f_def;    
	$module = $definition->module;
	$current_pkg = $definition->package;
	$dyn_ref = "__DYNAMIC__($class)";
	$fout[$current_pkg . '/' . $dyn_ref]  = "This article desrcibes the [[Defining Forms#Dynamic Creation|dynamically]] created class ''$class''.\n";
	$fout[$current_pkg . '/' .$dyn_ref] .= "*Extends the class: " . possibleInternalReference($definition->extends) . ".\n";
	if (!array_key_exists($class,$extensions)) {
	    $extensions[$class] = array($current_pkg . '/' . $dyn_ref=>$definition->extends);
	}
	$fout[$current_pkg . '/' .$dyn_ref] .= "{{SHOWINHERITENCE:$dyn_ref:$class}}";
	if (!array_key_exists($class,$class_file_list)) {
	    $class_file_list[$class] = array();
	}
	$class_file_list[$class][] =  array('file'=>$dyn_ref,'pkg'=>$current_pkg);
	$fout[$current_pkg . '/' .$dyn_ref] .= "*Location: It is defined in configuration magic data of the module [[".$packages[$current_pkg]['name'] ." Module List{$title_append}#$module|$module]] in the package [{$packages[$current_pkg]['bzr']} {$packages[$current_pkg]['name']}] {$versions[$current_pkg]}\n";
	$fout[$current_pkg . '/' .$dyn_ref] .= "{{SHOWFORMFIELDS:$class}}";
    }
    $form_storage->erase(); 
    //now we merge all the $fout  files group by class into one file -- need to do this
    //for mutpliy defined things like I2CE_FormStorage_magicdata which has two definitions.
    $cout = array();
    ksort($class_file_list);
    foreach ($class_file_list as $class => &$details) {
	$cout[$class] = '__PAGE:Class: ' . $class ."\n";
	//details = array('file'=>$file_base,'pkg'=>$current_pkg) -- $
	if (count($details) > 1) { //first we see if one of these is a dynamic defition which we can ignore
	    $dyn_refs = array();
	    foreach ($details as $i=>$det) {
		if ( substr($det['file'],0,11) == '__DYNAMIC__') {
		    $dyn_refs[] = $i;
		}
	    }
	    if (count($dyn_refs) < count($details)) { //yes we can get rid of the dyn refs
		foreach ($dyn_refs as $i) {
		    unset($details[$i]);
		}
	    }
	}
	if (count($details) > 1) {
	    I2CE::raiseError( $class . " is multiply defined \n" . print_r($details,true));
	    //multiplt defined
	    $cout[$class] .= "The class $class contains " . count($details) . " definitions:\n";
	    foreach ($details as $det) {
		$pkg = $det['pkg'];
		$file_base = $pkg . '/' . $det['file'];
		$cout[$class] .= "*Definition in [[#$file_base|$file_base]] of the package <span style='color:" . 
		    $packages[$pkg]['color'] . "'>" . $packages[$pkg]['name'] ."</span>\n" ; 
	    }        
	    foreach ($details as $det) {
		$pkg = $det['pkg'];
		$file_base = $pkg . '/' . $det['file'];
		$cout[$class] .= "=$file_base=\n";
		$cout[$class] .= $fout[$file_base] . "\n";
		$cout[$class] .= getInheritedMethodsAndVars($class,  $file_base);
	    }
	} else if (count($details) == 1){
	    reset($details);
	    $key = key($details);
	    $pkg = $details[$key]['pkg'];
	    $file_base = $pkg . '/' . $details[$key]['file'];
	    $cout[$class] .= $fout[$pkg .'/'.$details[$key]['file']];
	    $cout[$class] .= getInheritedMethodsAndVars($class,$file_base);
	} else {
	    die("No details for $class");
	}
    }
    unset($details);

//now we add in any fuzzy methods.
    foreach ($cout as $class => &$text) {
	if (array_key_exists($class,$fuzzys) && count($fuzzys[$class]) > 0) {    
	    $text .= "==Fuzzy Methods==\n";
	    //$fuzzys[$fuzz_class][$fuzz_method][]=array('implementing_class'=>$className,'implementing_method'=>$im);
	    foreach ($fuzzys[$class] as $fuzz_method=>$implementors) {
		$text .= "===$fuzz_method()===\n";
		if (count($implementors) > 1) {
		    $text .= "This method is implemented as follows:\n";
		    foreach ($implementors as $implementor) {
			$text .= "*By [[Class: " 
			    . $implementor['implementing_class'] . $title_append . '#' . $implementor['implementing_method'] . '() | '
			    . $implementor['implementing_class'] . '->' . $implementor['implementing_method'] . "() ]]\n";                    
		    }
		} else {
		    $text .= "This method is implemented by [[Class: " 
			. $implementors[0]['implementing_class'] . $title_append . '#' . $implementors[0]['implementing_method'] . '() | '
			. $implementors[0]['implementing_class'] . '->' . $implementors[0]['implementing_method'] . "() ]]\n";                    
		}
	    }
	}
	if (array_key_exists($class,$fuzzys_CLI) && count($fuzzys_CLI[$class]) > 0) {    
	    $text .= "==Fuzzy CLI Methods==\n";
	    //$fuzzys[$fuzz_class][$fuzz_method][]=array('implementing_class'=>$className,'implementing_method'=>$im);
	    foreach ($fuzzys_CLI[$class] as $fuzz_method=>$implementors) {
		$text .= "===$fuzz_method()===\n";
		if (count($implementors) > 1) {
		    $text .= "This method is implemented as follows:\n";
		    foreach ($implementors as $implementor) {
			$text .= "*By [[Class: " 
			    . $implementor['implementing_class'] . $title_append .  '#' . $implementor['implementing_method'] . '() | '
			    . $implementor['implementing_class'] . '->' . $implementor['implementing_method'] . "() ]]\n";                    
		    }
		} else {
		    $text .= "This method is implemented by [[Class: " 
			. $implementors[0]['implementing_class'] . $title_append .  '#' . $implementors[0]['implementing_method'] . '() | '
			. $implementors[0]['implementing_class'] . '->' . $implementors[0]['implementing_method'] . "() ]]\n";                    
		}
	    }
	}
    }
    unset($text);

//now we add in the category
    foreach ($cout as $class => &$text) {
	$text .= "\n\n[[Category:Class Documentation{$title_append}]]\n";
    }
    unset($text);

}

/***********************
 * 
 *  get all the available tempaltes
 *
 ************************/
$template_list = array();
$template_by_mod_list = array();

if ($do_wiki_template = simple_prompt("Make wiki pages related to templates?")) {
    foreach ($found_modules as $module=>$top_module) {
	$current_pkg = false;
	$current_pkg = $module_packages[$module];
	if (!array_key_exists($module,$module_packages)) {
	    I2CE::raiseError("Could not deterine which package $module resides in"); //should not happen
	    continue;
	}        
	$len = strlen($packages[$current_pkg]['dir']) ;
	$priority = 50;
	$storage->setIfIsSet($priority,"/config/data/$module/priority");
	I2CE::setupFileSearch(); //reset the file search.
	$fileSearch = I2CE::getFileSearch();
	$mod_factory->loadPaths($module,'TEMPLATES',true,$fileSearch); //load all template paths
	$results = $fileSearch->findByRegularExpression('TEMPLATES','/^.*html$/',true); //get the paths found with their **=recusrive search
	$files = array();
	$template_by_mod_list[$module] = array();
	foreach ($results as $file) {
	    $file = realpath($file);
	    $template = basename($file);
	    $file_base = ltrim(substr($file,$len),'/');
	    $template_by_mod_list[$module][] = "[[iHRIS Template List{$title_append}#$template | $template]]";
	    if (!array_key_exists($template,$template_list)){ 
		$template_list[$template] = array();
	    }
	    if (!array_key_exists($priority,$template_list[$template])){ 
		$template_list[$template][$priority] = array();
	    }
	    $desc = false;
	    if (preg_match('/<!--\s+TEMPLATEDOC:\s*(.*?)\s*-->/',file_get_contents($file),$matches)) {
		$desc = $matches[1];
	    }
	    $template_list[$template][$priority][$module] = 
		array(
		    'package'=>$current_pkg,
		    'file_base'=>$file_base,
		    'description'=>$desc
		    );
	}
    }
    ksort($template_list, SORT_STRING);
    foreach ($template_list as $template=>&$prior_list) {
	ksort($prior_list);
    }
    unset($prior_list);
    $tout = "__PAGE:iHRIS Template List\n";
    foreach ($template_list as $template=>$prior_list) {
	$tout .= '==' . $template . "==\n";
    
	$tout .= "This  template is loaded with the following priorites:\n";
	foreach ($prior_list as $prior=>$details) {
	    foreach ( $details as $mod=>$detail) {
		$tout.= "*From the module [[".
		    $packages[$detail['package']]['name'] . ' Module List' . $title_append. '#'. $mod . ' | ' . $mod . ']]' .
		    " in the package <span style='color:" .$packages[$detail['package']]['color'] . "'>"  . $packages[$detail['package']]['name'] . "</span> it is loaded with priority $prior.<br/> It is located at [" . 
		    $packages[$detail['package']]['bzr_annotate_files'] . '/' . $detail['file_base'] . ' ' . $detail['package'] . '/' . $detail['file_base'] . ']';
		if ($detail['description']) {
		    $tout .= "<br/>'''Description''':" . $detail['description'];
		}
		$tout .= "\n";                            

	    }
	}
    }
}



/****************************************
 *
 *  Look through each modules for any dynamically defined form classes a
 * also get all the form fields for each module.
 * also get the tasks and roles and strings  in .pot files
 *
 *************************************************/

if ($do_wiki_translation = simple_prompt("Make translation wiki pages?")) {

    $pots = array();
    $pot_master = array();
    $prefixes = array(
	'' => "Translator Comment",
	':'=> "Source Comments",
	'.'=> "Extracted Comments"
	);

    $def_locale_cmd = 'pcregrep -o1 "value>(.*?):" ';
    $def_locale_file = dirname(__FILE__) . "/../modules/Pages/modules/LocaleSelector/modules/DefaultLocales/DefaultLocales.xml";
    $def_locale_out = array();
    exec($def_locale_cmd . $def_locale_file,$default_locales);
    foreach ($default_locales as &$def_locale_line) {
	$def_locale_line = trim($def_locale_line);
    }
    unset($def_locale_line);
    //$default_locales = array('fr','sw','it','es','pt','ar');
    $file = basename(__FILE__) . "/../modules/Pages/modules/LocaleSelector/modules/DefaultLocales/DefaultLocales.xml";
    foreach ($found_modules as $module=>$top_module) {
	$current_pkg = false;
	$current_pkg = $module_packages[$module];
	if (!array_key_exists($module,$module_packages)) {
	    I2CE::raiseError("Could not deterine which package $module resides in"); //should not happen
	    continue;
	}  
	$pot_file = 
	    $packages[$current_pkg]['dir'] . DIRECTORY_SEPARATOR .$templates_dir . launchpad($module) . DIRECTORY_SEPARATOR . launchpad($module) . '.pot';
	if (!array_key_exists($current_pkg,$pots)) {
	    $pots[$current_pkg] = array();
	}
	if (is_readable($pot_file) && (count($pot_strings = loadPOT($pot_file)) > 1)) {
	    $count = -1;

	    foreach ($pot_strings as $string=>$details) {
		$count++;
		if (!$string) {
		    continue;
		}
		if (!array_key_exists($string,$pot_master)) {
		    $pot_master[$string] = array();
		}
		if (!array_key_exists($string, $pots[$current_pkg])) {
		    $pots[$current_pkg][$string] = array();
		}
		$links = array();
		foreach ($default_locales as $locale) {
		    //https://translations.launchpad.net/ihris-manage/trunk/+pots/accident/pt_BR/+translate
		    $links[]  = '[https://translations.launchpad.net/' . strtolower($packages[$current_pkg]['top_module']) . '/trunk/+pots/' . launchpad($module) . '/' . $locale . '/' . $count . '/+translate ' . $locale .']';
		}
		$comm = '';
		if (array_key_exists('comments',$details)) {
		    foreach ($details['comments'] as $prefix=>$comment_list) {
			if (count($comment_list) == 0 || !array_key_exists($prefix,$prefixes)) {
			    continue;
			}
			foreach ($comment_list as &$c) {
			    $c = preg_replace ('/\<a\s+href=\'(.*?)\'\>(.*?)\<\/a\>/','[$1 $2]',$c);
			}
			$comm .= "\n* " . $prefixes[$prefix] . "\n** " . implode("\n** ", $comment_list) . "\n";
		    }
		}
		$data= array('module'=>$module, 'links'=>$links, 'pkg'=>$current_pkg, 'comments'=>$comm);
		$pot_master[$string][] = $data;
		$pots[$current_pkg][$string][]  =  $data;
	    }
	}
    
    }


    $pot_wikis = array();
    foreach ($pots as $pkg=>$strings) {
	$pot_wiki = array();
	$pot_wiki[''] =  "__PAGE:" . $packages[$pkg]['name'] . " Translation List\nThis is a list of all text that can be translated in " . $packages[$pkg]['name'].  "\n";
	foreach ($strings as $string=>$data) {
	    if (count($data) == 0) {
		continue;
	    }
	    if (count($data) == 1) {
		$data = current($data);
		$data['pkg'] = $pkg;
		$desc = "The text <source lang='text'>$string</source> appears in the the module [[" . $packages[$pkg]['name'] . " Module List{$title_append}#" . $data['module'] . " | " . $data['module'] . ']]';
		$pot_wiki[$string] = "\n\n\n$desc\nTranslate to: " . implode (",", $data['links']) . "\n";
		if ($data['comments']) {
		    $pot_wiki[$string] .= $data['comments'];           
		}

	    } else {
		$pot_wiki[$string] = "\n\n\nThe text <source lang='text'>$string</source> appears in the the modules:\n";
		foreach ($data as $d) {
		    $d['pkg'] = $pkg;
		    $pot_wiki[$string] .= "\n*[[" . $packages[$pkg]['name'] . " Module List{$title_append}#" . $d['module'] . " | " . $d['module'] . ']]'
			." Translate to: " . implode (",", $d['links']) . "\n";
		    if ($d['comments']) {
			$pot_wiki[$string] .= $d['comments'];           
		    }
		}
	    }
	}
	ksort($pot_wiki);
	$pot_wikis[$pkg] = implode('',$pot_wiki);
    }
    ksort($pot_master);
    $pot_master_out = array(''=>"__PAGE:iHRIS Translation List\nThis is a list of all text that can be translated in the iHRIS System\n");
    foreach($pot_master as $string=>$data) {
	if (!$string || count($data) == 0) {
	    continue;
	}
	$pot_master_out[$string] = "\n\n\nThe text <source lang='text'>$string</source> appears in the the modules:\n";
	foreach ($data as $d) {
	    $pot_master_out[$string] .= "\n*[[" . $packages[$d['pkg']]['name'] . " Module List{$title_append}#" . $d['module'] . " | " . $d['module'] . ']]'
		." Translate to: " . implode (",", $d['links']) . "\n";
	    if ($d['comments']) {
		$pot_master_out[$string] .= $d['comments'];           
	    }
	}

    }
    ksort($pot_master_out);
    $pot_master_out = implode('',$pot_master_out);
}


$roles =array();
$tasks = array();
$role_tasks = array();
$task_tasks = array();
if ($do_wiki_task =  simple_prompt("Make task and role wiki pages")) {
    foreach ($found_modules as $module=>$top_module) {
	$current_pkg = false;
	$current_pkg = $module_packages[$module];
	if (!array_key_exists($module,$module_packages)) {
	    I2CE::raiseError("Could not deterine which package $module resides in"); //should not happen
	    continue;
	}  
	$mod_storage = I2CE_MagicData::instance( "CLI_application_mod_task_role:$module" );
	$mod_configurator =new I2CE_Configurator($mod_storage,false);
	if ($module != $mod_configurator->processConfigFile($storage->config->data->$module->file,true,false,false)) {
	    I2CE::raiseError("Unable to process config data for $module");
	    return false;
	}
	if ($mod_storage->is_parent("/I2CE/formsData/forms/role")) {
	    foreach ($mod_storage->I2CE->formsData->forms->role as $role=>$data) {
		if (!$data->is_parent('fields')) {
		    continue;
		}
		if ($data->is_scalar('fields/assignable') && !$data->fields->assignable) {
		    continue;
		}
		if (!$data->is_scalar('fields/name')) {
		    continue;
		}
		$roles[$role] = array('pkg'=>$current_pkg, 'module'=>$module,'trickle_up'=>explode(',',$data->fields->trickle_up),'name'=>$data->fields->name);
	    }
	}
	if ($mod_storage->is_parent("/I2CE/tasks/task_description")) {
	    $t_tasks = $mod_storage->I2CE->tasks->task_description->getKeys();
	    foreach ($t_tasks as  $task) {
		$desc = '';
		if ($mod_storage->I2CE->tasks->task_description->is_scalar($task)) {
		    $desc = $mod_storage->I2CE->tasks->task_description->$task;
		    if (!$desc) {
			$desc = $mod_storage->I2CE->tasks->task_description->getTranslation($task,false,'en_US');
		    }
		}
		$tasks[$task] = array('module'=>$module,
				      'pkg'=>$current_pkg,
				      'desc'=>$desc);
	    }
	}
	if ($mod_storage->is_parent("/I2CE/tasks/role_trickle_down")) {
	    foreach ($mod_storage->I2CE->tasks->role_trickle_down as $role => $r_tasks) {
		if (!array_key_exists($role,$role_tasks)) {
		    $role_tasks[$role] = array();                   
		}
		$role_tasks[$role] = array_unique(array_merge($role_tasks[$role],$r_tasks->getAsArray()));
	    }
	}
	if ($mod_storage->is_parent("/I2CE/tasks/task_trickle_down")) {
	    foreach ($mod_storage->I2CE->tasks->task_trickle_down as $task => $t_tasks) {
		if (!array_key_exists($task,$task_tasks)) {
		    $task_tasks[$task] = array();                    
		}

		$task_tasks[$task] = array_unique(array_merge($task_tasks[$task],$t_tasks->getAsArray()));
	    }
	}
	$mod_storage->erase();
	unset($mod_storage);
    }
    $tkout = "__PAGE:iHRIS Task List\nThis is a list of all the tasks available in the iHRIS System\n";
    ksort($tasks,SORT_STRING);
    foreach ($tasks as $task=>$data) {
	$tkout .= "==$task==\nThe task ''$task'' is defined in the module [[" . $packages[$data['pkg']]['name'] . " Module List{$title_append}#" . $data['module'] . " | " . $data['module'] . ']]'
	    . ' of the package ' . $packages[$data['pkg']]['name'] . ".\n";
	$tkout .= "*Description:" . $data['desc'] . "\n";
	if (array_key_exists($task,$task_tasks) && count($task_tasks[$task]) > 0) {
	    $t_tasks = $task_tasks[$task];
	    foreach ($t_tasks as &$t) {
		$t = "[[#$t | $t]]";
	    }
	    unset($t);
	    $tkout .= "*Can perform the following sub-tasks: " .implode(", ",$t_tasks) . "\n";
	}
    }
    ksort($roles,SORT_STRING);
    $rout = "__PAGE:iHRIS Role List\nThis is a list of all the roles available in the iHRIS System\n";
    foreach ($roles as $role=>$data) {
	//$roles[$role] = array('pkg'=>$current_pkg, 'module'=>$module,'trickle_up'=>explode(',',$data->fields->trickle_up),'name'=>$data->fields->name);
	$rout .= "==$role==\nThe role ''{$data['name']}'' is defined in the module [[" . $packages[$data['pkg']]['name'] . " Module List{$title_append}#" . $data['module'] . " | " . $data['module'] . ']]'
	    . ' of the package ' . $packages[$data['pkg']]['name'] . ".\n";
	foreach ($data['trickle_up'] as $i=>&$r) {
	    if (!$r || !array_key_exists($r,$roles)) {
		unset($data['trickle_up'][$i]);
		continue;
	    }
	    $r = "[[#$r | "  . $roles[$r]['name'] . "]]";
	}
	unset ($r);
	if (count($data['trickle_up']) > 0) {
	    $rout .= "*Any of the tasks that a  {$data['name']} can perform can be performed by any the following roles: " . implode(",",$data['trickle_up']) . "\n";
	}
	if (array_key_exists($role,$role_tasks) && count($role_tasks[$role]) > 0) {
	    $t_tasks = $role_tasks[$role];
	    foreach ($t_tasks as &$t) {
		$t = "[[iHRIS Task List{$title_append}#$t | $t]]";
	    }
	    unset($t);
	    $rout .= "*Can perform the following tasks: " .implode(", ",$t_tasks) . "\n";
	}
    }

}














/*****************************8
 *
 *  Some post processing 
 *
 ********************************/


//now we have a list of all the found classes. we change the {{MAYBECLASS:XXXXX}} to what it needs to be in the class files
$maybeclasses = array();
foreach(array_keys($class_file_list) as $class) {
    $maybeclasses[ '{{MAYBECLASS:' . $class . '}}'] = "[[Class: $class{$title_append} | $class]]";
}



function replaceMaybeClass($maybeclass) {
    global $maybeclasses;     
    if (array_key_exists($maybeclass[0],$maybeclasses)) {
        return $maybeclasses[$maybeclass[0]];
    } else {        
        return rtrim(substr($maybeclass[0],13),'}');
    }
}



function possibleInternalReferenceLink($arr) {
    list($all,$url,$text) = $arr;
    global $php_types;
    global $class_file_list;
    global $class;
    global $all_vars;
    global $title_append;
    if ($url[0] == '$') {
        foreach (array('protected','private','public') as $declr) {
            if (array_key_exists($declr,$all_vars[$class]) && in_array($url,$all_vars[$class][$declr])) {
                if ($text = trim($text)) {
                    return "[[#$url | $text]]";
                } else  {
                    return "[[#$url | $url]]";
                }
            }
        }
    } else if (array_key_exists(strtolower($url),$php_types)) {
        return "[" .  $php_types[strtolower($url)] . " $text]";
    } else if (array_key_exists($url,$class_file_list)) {
        return "[[Class: $url$title_append | $text]]";
    } else {
        return $url . ' ' . $text;
    }
}


function moduleTemplateList($matches) {
    global $template_by_mod_list;
    //$template_by_mod_list[$module][] = '[' . $packages[$current_pkg]['bzr_annotate']  .  $file_base . ' ' . $template .']';
    $module = $matches[1];    
    if (!array_key_exists($module,$template_by_mod_list) || count($template_by_mod_list[$module]) == 0) {
        return '';
    }
    return '<br/>'  . implode(", " , $template_by_mod_list[$module]) ;
}

function moduleClassList($matches) {
    global $module_class_list;
    $module = $matches[1];    
    if (!array_key_exists($module,$module_class_list) || count($module_class_list[$module]) == 0) {
        return '';
    }
    $ret =  "<br/>"  ;
    $list = array();
    foreach ($module_class_list[$module] as $m) {
        $list[] =  "{{MAYBECLASS:" . $m . "}}" ;
    }
    return $ret . implode(", " , $list);
}


function moduleSubModuleList($matches) {
    global $module_dirs; //    $module_dirs[$module] = $current_pkg . '/' . $file_base;
    $module = $matches[1];    
    if (!array_key_exists($module,$module_dirs)) {
        return '';
    }
    $module_base = $module_dirs[$module];
    $num_sep = count(explode("/",$module_base)) +2;
    $sub_mods = array();
    foreach ($module_dirs as $sub_module=>$sub_dir) {
        if (strpos($sub_dir,$module_base ) ===0 && $num_sep == count(explode("/",$sub_dir))) {
            $sub_mods[]= $sub_module;
        }
    }
    if (count($sub_mods) == 0) {
        return '';
    }
    $ret = "<br/>";    
    foreach ($sub_mods as &$sub_mod) {
        $sub_mod = "[[#" . $sub_mod .' |' . $sub_mod . ']]';
    }
    unset($sub_mod);
    return $ret . implode(", " , $sub_mods);
}






foreach ($wout as $pkg=>&$text) {
    $text = preg_replace_callback('/{{MODULECLASSLIST:([0-9a-zA-Z_\-]+)}}/','moduleClassList',$text);
    $text = preg_replace_callback('/{{MODULESUBMODULELIST:([0-9a-zA-Z_\-]+)}}/','moduleSubModuleList',$text);
    $text = preg_replace_callback('/{{MODULETEMPLATELIST:([0-9a-zA-Z_\-]+)}}/','moduleTemplateList',$text);
    $text = preg_replace_callback('/{{MAYBECLASS:.*?}}/','replaceMaybeClass',$text);
}
unset($text);


function replaceFormFields($matches) {
    global $form_fields;
    global $extensions;
    global $title_append;
    $class = $matches[1];
    $ret = '';
    $found = array();
    if (array_key_exists($class,$form_fields) && count($form_fields[$class]) > 0) {
        $ret .= "==Form Fields==\n" .
            "This class is a [[Class: I2CE_Form{$title_append} |form class]] and provides the following [[Class: I2CE_FormField{$title_append} |form fields]]\n" .
            implode("\n",$form_fields[$class]) . "\n";
        foreach ($form_fields[$class] as $name=>$det) {
            $found[$name] = true;
        }
    }
    $i_ret =array();
    $p_class = $class;
    while ( $p_class && array_key_exists($p_class,$extensions)) {
        if (array_key_exists($p_class,$form_fields)) {
            foreach ($form_fields[$p_class] as $name=>$det) {
                if (array_key_exists($name,$found)) {
                    continue;
                }
                $found[$name] = true;
                $i_ret[] = $det . 'is inherited from the class [[Class: ' . $p_class . $title_append .  '|' . $p_class ."]]\n";
            }
        }
        reset($extensions[$p_class]);
        $p_class = current($extensions[$p_class]);
    }
    if (count($i_ret)>0) {
        $ret .= "==Inherited Form Fields==\n" . implode("\n",$i_ret) . "\n";        
    }
    return $ret;
}



function replaceInheritence($matches) {
    list($all,$file,$class) = $matches;    
    $hier = getClassHierarchy($class,$file);
    $hier_text ='';
    if (count($hier['parents']) > 0) {
        $hier_text = '*Parent Classses: ';
        foreach ($hier['parents'] as $p_class) {
            $hier_text .= " {{MAYBECLASS:$p_class}}";
        }
        $hier_text .= "\n";
    }
    if (count($hier['children']) > 0) {
        $hier_text .= "*Child Classes:\n";
        $hier_text .= flattenChildren($hier['children']);
    }
    return $hier_text;
}


foreach ($cout as $class=>&$text) {
    $text = preg_replace_callback('/{\s*@link\s+(\$?[0-9a-z_]+)\s*(.*?)\s*}/i','possibleInternalReferenceLink',$text);
    $text = preg_replace_callback('/{{SHOWFORMFIELDS:(.*?)}}/','replaceFormFields',$text);
    $text = preg_replace_callback('/{{SHOWINHERITENCE:(.*?):(.*?)}}/','replaceInheritence',$text);
    $text = preg_replace_callback('/{{MAYBECLASS:.*?}}/','replaceMaybeClass',$text);
}
unset($text);




/******************************
 *  Upload the articles we craeted to the wiki
 *******************************/
if ($do_wiki_class) {
    while ( $class = trim(ask("Enter a class to preview (blank for none)"))) {
	if (array_key_exists($class,$cout)) {
	    echo $cout[$class] . "\n";
	    if (simple_prompt("Would you like to upload this to the wiki?")) {
		if (!wikiUploadVersioned($cout[$class])) {
		    I2CE::raiseError("Could not upload $class");
		} else {
		    I2CE::raiseError("Uploaded $class");
		}
	    }
	} else {
	    echo "No such class ($class)\n";
	}
    }
}

$upload = null;
if ($do_wiki_translation && prompt("Upload translation lists to wiki?", $upload)) {
    foreach ($pot_wikis as $pkg=>$pot_wiki) {
        if (! wikiUploadVersioned($pot_wiki)) {
            I2CE::raiseError("Could not upload translation list for $pkg");
        } else {
            I2CE::raiseError("Uploaded translation list for $pkg");
        }
    }
    if (! wikiUploadVersioned($pot_master_out)) {
        I2CE::raiseError("Could not upload master translation list ");
    } else {
        I2CE::raiseError("Uploaded master translation list");
    }

}


if ($do_wiki_class) {
    $class_list = "__PAGE:iHRIS Class List\nThis is a list of all the classes available in the iHRIS Suite with links to the API\n";
    $class_names = array_keys($cout);
    sort($class_names,SORT_STRING);
    foreach ($class_names as $class) {    
	$class_list .= "*[[Class: $class{$title_append} | $class]]\n";
    }


    $upload = null;
    ksort($cout,SORT_STRING);
    foreach ($cout as $class=>$text) {
	if (prompt("Upload class file \"$class\" to wiki?", $upload, $text)) {
	    if (! wikiUploadVersioned($text)) {
		I2CE::raiseError("Could not upload class file \"$class\" to wiki");
	    } else{
		I2CE::raiseError("Uploaded class file \"$class\" to wiki");
	    }
	}
    }
}

if ($do_wiki_task && prompt("Upload task list  to wiki?", $upload, $tkout)) {
    if (! wikiUploadVersioned($tkout)) {
        I2CE::raiseError("Could not upload task list");
    } else {
        I2CE::raiseError("Uploaded task list");
    }
}


if ($do_wiki_task && prompt("Upload role list  to wiki?", $upload, $rout)) {
    if (! wikiUploadVersioned($rout)) {
        I2CE::raiseError("Could not upload role list");
    } else {
        I2CE::raiseError("Uploaded role list");
    }
}



if ($do_wiki_template && prompt("Upload tempalte list  to wiki?", $upload, $tout)) {
    if (! wikiUploadVersioned($tout)) {
        I2CE::raiseError("Could not upload template list");
    } else {
        I2CE::raiseError("Uploaded template list");
    }
}

if ($do_wiki_class && prompt("Upload class list  to wiki?", $upload, $class_list)) {
    if (! wikiUploadVersioned($class_list)) {
        I2CE::raiseError("Could not upload class list");
    } else {
        I2CE::raiseError("Uploaded class list");
    }
}



$upload = null;
foreach ($wout as $pkg=>$text) {
    if (prompt("Upload module list \"$pkg\" to wiki?", $upload, $text)) {
        if (! wikiUploadVersioned($text)) {
            I2CE::raiseError("Could not upload module list \"$pkg\"");
        } else {
            I2CE::raiseError("Uploaded module list \"$pkg\"");
        }
    }
}


