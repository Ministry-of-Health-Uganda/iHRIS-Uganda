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



$html_dir = "form_documentor";
$usage = 
    "[--template_dir=\$template_dir]: The directory to store html files in\n"
    ."\tIf not set, we use $template_dir\n"
    ."\tThis script is intended to be run in the directory containing the top-level modules directory\n";


$set_categories = false;
$search_dirs = array();
foreach (glob('*') as $dir) {
    if (!is_dir($dir) || !is_readable($dir)) {
        continue;
    }
    $search_dirs[] = $dir;
    if (is_dir($dir  . DIRECTORY_SEPARATOR . 'sites')) {
        $search_dirs[] = $dir  . DIRECTORY_SEPARATOR . 'sites' . DIRECTORY_SEPARATOR . '*';
    }
}
require_once ("translate_base.php");  


foreach ($args as $key=>$val) {
    switch($key) {
    case 'html_dir':
        $html_dir = $val;
        break;
    }
}

$forms_dir = $html_dir . DIRECTORY_SEPARATOR . 'forms';
$classes_dir =  $html_dir . DIRECTORY_SEPARATOR  . 'classes';

foreach (array($html_dir , $forms_dir, $classes_dir) as $dir) {
    if (!is_dir($dir)) {
        if (!mkdir($dir,0774,true)) {
            usage("Could not make $dir");
        }
    }
    if (!is_writable($dir)) {
        usage("Could not write to $dir");
    }
}


getAvailableModules();
if (count($found_modules) == 0) {
    usage("No modules found");
}


$forms = array();
$formClasses = array();
$classMap = array();


foreach ($found_modules as $module=>$top) {
    $file = null;
    $storage->setIfIsSet($file,"/config/data/$module/file");
    if (!$file) {
        echo "No config file for $module -- Skipping\n";
        continue;
    }
    $mod_storage = I2CE_MagicData::instance( "tmp_$module" );
    I2CE::setConfig($mod_storage);
    $mod_configurator =new I2CE_Configurator($mod_storage,false);
    ob_start();
    $s = $mod_configurator->processConfigFile($file, '0',false,false); //process the configGroups but not the meta data.  dont show verbose errors.
    if ($s != $module) {
        echo ("Module mismatch on $s!=$module  in $file-- Skipping\n");
        continue ;
    }
    if (!$mod_storage->pathExists("/modules/forms/forms") && !$mod_storage->pathExists("/modules/forms/formClasses")) {
        $mod_configurator->__destruct();
        $mod_configurator = null;
        $mod_storage->erase();
        $mod_storage = null;        
        continue;
    }
    foreach ($mod_storage->modules->forms->forms as $form=>$formData) {
        $data = array();
        foreach (array('class'=>false,'display'=>false,'meta/child_forms'=>false) as $k=>$required) {
            $data[$k] = null;
            if (!$formData->setIfIsSet($data[$k], $k,true)) {
                if ($required) {
                    continue 2;
                } else{
                    unset($data[$k]);
                }
            }
        }
        if (!array_key_exists($form,$forms)) {
            $forms[$form] = array();
        }
        $forms[$form][$module] = $data;
        if (!array_key_exists($data['class'], $classMap)) {
            $classMap[$data['class']][]= $form;
        }
    }
    foreach ($mod_storage->modules->forms->formClasses as $class=>$classData) {
        $data = array();
        foreach (array('extends'=>false,'fields'=>false) as $k=>$required) {
            $data[$k] = null;
            if (!$classData->setIfIsSet($data[$k], $k, true)) {
                if ($required) {
                    echo "Skipping $class b/c no $k in module $module\n";
                    continue 2;
                } else {
                    unset($data[$k]);
                }
            }
        }        
        if (!array_key_exists($class,$formClasses)) {
            $formClasses[$class] = array();
        }
        $formClasses[$class][$module] = $data;

    }

    $mod_configurator->__destruct();
    $mod_configurator = null;
    $mod_storage->erase();
    $mod_storage = null;            
}




function addFormData($data,$form,$mod) {
    global $forms;
    if (!$data['display']) {
        $data['display'] = $form;
    }
    $out ="The form $form ({$data['display']}) is defined in the module $mod";
    if ($data['class']) {
        $out .= " and refers to class <a href='../classes/{$data['class']}.html'>{$data['class']}</a>";
    }
    if ($data['meta/child_forms']) {
        $out .= '<br/>The module adds in as child forms: ';
        foreach ($data['meta/child_forms'] as $f) {
            $out .= "<a href='$f.html'>$f</a> ";
        }
    } 
    return $out;
}




foreach ($forms as $form=>$datas) {
    $out = '<html><body>';
    $out = '<h2>' . $form . '</h2>';
    if (count($datas) > 1) {
        $out .= '<ul>';
        foreach ($datas as $mod=>$data) { 
            $out .='<li>';
            $out .=  addFormData($data,$form,$mod);
            $out .= '</li>'; 
        };
        $out .= '</ul>';
    } else if (count($datas) ==1) {
        $mod = key($datas);
        $data = $datas[$mod];
        $out .= addFormData($data,$form,$mod);     
    }
    $parents = array();
    foreach ($forms  as $f=>$datas) {
        foreach ($datas as $mod=>$data) {
            if(!is_array($data['meta/child_forms'])) {
                continue;
            }
            if (in_array($form,$data['meta/child_forms'])) {
                $parents[] =   "<li><a href='$f.html'>$f</a> from the module $mod</li>";
            }        
        }
    }
    if (count($parents) > 0) {
        $out .= "<br/>The form is a child form for the forms:<ul>" . implode('',$parents) . "</ul>";
    }
    $out .='</body></html>';
    $form_file = $forms_dir . DIRECTORY_SEPARATOR . $form . '.html';
    if (!file_put_contents($form_file, $out)) {
        die( "Could not write $form_file\n");
    }
}



foreach ($formClasses as $class=>$datas) {
    $out = '<html><body>';
    $out = '<h2>' . $class . '</h2>';
    if (count($datas) > 1) {
        $out .= '<b>Warning:</b>The class $class is provided by more than one module<br/>';
        $out .= '<ul>';
        foreach ($datas as $mod => $data) {
            $out .= '<li>';
            $out .= "The class $class is provided by the module $module as follows:<br/>";
            $out .= addClassData($mod,$class,false);         
            $out .= '</li>';
        }
        $out .= '</ul>';
    } else if (count($datas) == 1) {
        $module = key($datas);
        $data = $datas[$module];
        $out .= "The class $class is provided by the module $module<br/>";
        $out .= addClassData($module,$class,true);        
    }
    $out .='</body></html>';
    $class_file = $classes_dir . DIRECTORY_SEPARATOR . $class . '.html';
    if (!file_put_contents($class_file, $out)) {
        die("Could not write $class_file\n");
    }
}

function addClassData($mod,$class,$deep) {
    global $formClasses;
    global $classMap;
    $out = '';
    $class_data = $formClasses[$class][$mod];
    if (!is_array($class_data)) {
        die ("badness of the k-buster sort for $class/$mod\n");
    }
    do {
        if (is_array($class_data['fields']) && count($class_data['fields']) > 0) {
            $out .= "The following fields are provided by $class:";
            $out .= '<ul>';
            foreach ($class_data['fields'] as $field=>$data) {
                $out .= '<li>';
                $out .= 'The field <i>' . $field . '</i> has type ' . $data['formfield'] . '.';
                if (array_key_exists('required',$data) && $data['required']) {
                    $out .= '<br/>It is required.';
                }
                if (array_key_exists('in_db',$data) && !$data['in_db']) {
                    $out .= '<br/>It is <b>not</b> stored in the database';
                }
                if (array_key_exists('setMap', $data) && array_key_exists('useMap',$data['setMap']) && $data['setMap']['useMap']) {
                    $mapData = $data['setMap'];
                    $map_form = $field;
                    if (array_key_exists('form',$mapData)) {
                        $map_form = $mapData['form'];
                    }
                    $map_field = 'id';
                    if (array_key_exists('field',$mapData)) {
                        $map_field = $mapData['field'];
                    }
                    $out .= "<br/>It maps to the form <a href='../forms/$map_form.html'>$map_form</a> via it's field $map_field";
                }
                $out .= '</li>';
            }
            $out .= '</ul>';
        }
        if (array_key_exists($class, $classMap) && is_array($classMap[$class]) && count($classMap[$class]) > 0) {
            $out .= "The class $class is referenced by the following forms<ul>";
            foreach ($classMap[$class] as $form) {
                $out .= '<li>';
                $out .= "<a href='../forms/$form.html'>$form</a>";
                $out .= '</li>';
            }
            $out .='</ul>';
        }
        if ($deep && (!array_key_exists('extends',$class_data) || !$class_data['extends'] || $class_data['extends'] == 'I2CE_Fuzzy')) {
            $deep = false;
        } else {
            $old_class = $class;
            $class = $class_data['extends'];
            if (($class == 'I2CE_Form')) {
                $deep = false;
            } else if (!array_key_exists($class, $formClasses)) {
                $deep = false;
                $class =false;
            }
            if ($class) {
                $mod = false;
                if (count($formClasses[$class]) > 1) {                    
                    $deep = false;
                } else {
                    $mod = key($formClasses[$class]);
                    $class_data = $formClasses[$class][$mod];
                }
                $out .=  "The class $old_class extends the class <a href='$class.html'>$class</a>";
                if ($mod) {
                    $out .= " which is provided in the module $mod";            
                }
                $out .= '<br/>';
            }
        }
    } while ($deep);


    return $out;
}
ksort($forms);
ksort($formClasses);
reset($forms);
$first = 'forms/' . key($forms) . '.html';

$index = '<html>';
$index .= '<FRAMESET cols="20%, 80%">';
$index .= '<FRAMESET rows="50%, 50%">';
$index  .= '<frame src="index_forms.html"/>';
$index  .= '<frame src="index_formClasses.html"/>';
$index .= '</frameset>';
$index .= "<frame name='show' src='$first'/>";
$index .= '</frameset>';
$index .='</html>';
$index_file = $html_dir . DIRECTORY_SEPARATOR . 'index.html';
if (!file_put_contents($index_file, $index)) {
    die( "Could not write $form_file\n");
}
$index = '<html><body>';
$index = '<h2>Forms</h2><ul>';
foreach ($forms as $form=>$data) {
    $index.= "<li><a target='show' href='forms/$form.html'>$form</a></li>";
}
$index_file = $html_dir . DIRECTORY_SEPARATOR . 'index_forms.html';
if (!file_put_contents($index_file, $index)) {
    die( "Could not write $form_file\n");
}
$index = '<html><body>';
$index .= '</ul><h2>Form Classes</h2><ul>';
foreach ($formClasses as $class=>$data) {
    $index.= "<li><a target='show'  href='classes/$class.html'>$class</a></li>";
}
$index .='</ul>';
$index_file = $html_dir . DIRECTORY_SEPARATOR . 'index_formClasses.html';
if (!file_put_contents($index_file, $index)) {
    die( "Could not write $form_file\n");
}



