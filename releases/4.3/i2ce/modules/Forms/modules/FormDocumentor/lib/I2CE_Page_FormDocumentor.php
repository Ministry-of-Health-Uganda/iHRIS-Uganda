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
 * The page wrangler
 * 
 * This page loads the main HTML template for the home page of the site.
 * @package iHRIS
 * @subpackage DemoManage
 * @access public
 * @author Carl Leitner <litlfred@ibiblio.org>
 * @copyright Copyright &copy; 2007, 2008 IntraHealth International, Inc. 
 * @since Demo-v2.a
 * @version Demo-v2.a
 */

class I2Ce_Page_FormDocumentor extends I2CE_Page {


    
    /***
     * @var protected array $form_cache of I2CE_Form.  The cache of blank forms created.
     */
    protected $form_cache = array();
    /**
     * @var  protected I2CE_FormFactory $form_factory
     */
    protected $form_factory;
    /**     
     * @var protected boolean $check_map.  True if we should make check mapped fields
     */
    protected $check_map;

    /**
     * Create a new instance of a page.
     * 
     * The default constructor should be called by any pages extending this object.  It creates the
     * {@link I2CE_Template} and {@link I2CE_User} objects and sets up the basic member variables.
     * @param array $args
     * @param array $request_remainder The remainder of the request path
     */
    public  function __construct( $args,$request_remainder , $get = null, $post = null) {
        parent::__construct($args,$request_remainder,$get,$post);
        $this->form_factory = I2CE_FormFactory::instance();
        $this->check_map = I2CE_ModuleFactory::instance()->isEnabled("Lists");
        $this->locale = I2CE_Locales::DEFAULT_LOCALE;

    }

    /**
     * the locale to make the form map in.  specify on the command line by --localee=en_US
     * @var protected string $locale
     */
    protected $locale;

    /**
     * The site module
     * @returns string
     */
    protected function getModule() {
        return I2CE::getConfig()->config->site->module;
    }


    /**
     * Gets the output file with the given extension
     */
    protected function getOutputFile($ext) {
        $base_dir = '/tmp';
        I2CE::getConfig()->setIfIsSet($base_dir,"/modules/formDocumntor/output_dir");
        $module = $this->getModule();
        $version = $this->getVersion($module);
        if ($version) {
            $version = '_' . strtr($version,'.','_');
        }
        return $base_dir . '/' .   'forms-' . $module . $version . '.' . $ext;
    }

    /**
     * Gets the version of the specified module
     * @param string $module
     * @returns string;
     */
    protected function getVersion($module) {
        if (!is_string($module) || strlen($module) == 0) {
            return;
        }
        $version = '';
        I2CE::getConfig()->setIfIsSet($version,"/config/data/$module/version");
        return $version;
    }


    /**
     * Get the display name for the specified module.
     * strips out Demo/Demonstration language
     * @param string $module
     * @returns string
     */
    protected function getDisplayName($module) {
        $title = 'Form Documentor';
        if (I2CE::getConfig()->setIfIsSet($title,"/config/data/$module/displayName")) {
            $title = str_ireplace('Demonstration','',$title);
            $title = str_ireplace('Demo','',$title);
            $title = trim($title);
        }
        return $title;
    }

    /**
     * Gets a "scheme" used to describe form documentation options.
     * @returns array
     */
    protected function getSchemeDetails($scheme) {
        $details = array();
        I2CE::getConfig()->setIfIsSet($details,"/modules/formDocumentor/schemes/$scheme",true);
        return $details;
    }


    /**    
     * @var protected array  of string assocating a short field name to a field class
     */
    protected $field_defs = null;

    /**
     * Look up the short form of a field class from the long form
     * @param string $field_def the class of a form field
     */ 
    protected function reverseFieldDef($field_def) {
        if (!is_array($this->field_defs)) {
            $config = I2CE::getConfig();
            if (!$config->is_parent("/modules/forms/FORMFIELD")) {
                I2CE::raiseError("No fields defined", E_USER_ERROR);
            }
            $this->field_defs = array_flip($config->getAsArray("/modules/forms/FORMFIELD"));
        }
        if (!array_key_exists($field_def,$this->field_defs)) {
            return false;
        }
        return $this->field_defs[$field_def];
    }


    /**
     * Produces  a .txt file for the given forms as a string
     * @param array $forms of string the forms
     */
    public function text($forms ) {
        $module = $this->getModule();
        $form_list =   $this->getDisplayName($module);
        $version = $this->getVersion($module);
        if ($version) {
            $form_list .= ' - ' . $version;
        }
        $form_list .= "\n";
        sort($forms);          
        $config = I2CE::getConfig();
        foreach ($forms as $form) {
            if ( !($formObj = $this->form_factory->createContainer($form)) instanceof I2CE_Form) {
                continue;
            }
            $form_list .= "Form ($form):\n";
            $form_list .= "\tForm Class: " . get_class($formObj) . "\n";
            $child_forms = array_intersect($formObj->getChildForms(),$forms);
            sort($child_forms);
            if (count($child_forms) > 0) {
                $form_list .= "\tChild Forms: " . implode(',',$child_forms) . "\n";
            }
            $form_list .= "\tFields:\n";
            foreach ($formObj as $field=>$fieldObj) {
                if (! ($field_def = $this->reverseFieldDef(get_class($fieldObj)))) {
                    continue;
                }
                if (!$fieldObj->isInDB()) {
                    continue;
                }
                $form_list .= "\t\tField ($field):\n";
                $form_list .= "\t\t\tHeader: " . $fieldObj->getHeader() . "\n";
                $form_list .= "\t\t\tType: $field_def\n"; 
                $restrictions = '';
                if ($fieldObj->getOption('required')) {
                    $restrictions .= 'Required';
                }
                $unique = '';
                if ($fieldObj->hasOption('unique') && $fieldObj->getOption('unique')) {
                    if ($fieldObj->hasOption('unique_field') && ($unique_field = $fieldObj->getOption('unique_field'))) {
                        $unique = 'Unique in {' . trim($unique_field) . '} ';
                    } else {
                        $unique = 'Unique ';
                    }
                    if ($restrictions) {
                        $restrictions = $restrictions. ', '.$unique;
                    } else {
                        $restrictions = $unique;
                    }

                }
                if ($restrictions) {
                    $form_list .= "\t\t\tRestrictions: $restrictions\n";
                }
                if (!$this->check_map) {
                    continue;
                }
                if ( !$fieldObj instanceof I2CE_FormField_MAPPED) {
                    continue;
                }
                $map_forms = array_intersect($fieldObj->getSelectableForms(),$forms);
                sort($map_forms);
                if (count($map_forms) > 0) {
                    $form_list .= "\t\t\tMaps To Forms: " . implode(',', $map_forms) . "\n";
                }
            }
        }
        $file = $this->getOutputFile('txt');
        if ( file_put_contents($file,$form_list) === false)  {
            I2CE::raiseError("Could not write to " . $file);
        } else {
            I2CE::raiseError("Form list saved to $file");
        }
    }

    /**
     * Produces  a wiki page for the given forms as a string
     * @param array $forms of string the forms
     */
    public function wiki($forms) {
        $module = $this->getModule();
        $form_list =   $this->getDisplayName($module);
        $version = $this->getVersion($module);
        if ($version) {
            $form_list .= ' - ' . $version;
        }
        $form_list .= "\n";
        $config = I2CE::getConfig();
        sort($forms);
        $parent_forms = array();
        foreach ($forms as $form) {
            if ( !($formObj = $this->form_factory->createContainer($form)) instanceof I2CE_Form) {
                continue;
            }            
            $child_forms = array_intersect($formObj->getChildForms(),$forms);
            foreach ($child_forms as $child_form) {
                if (!array_key_exists($child_form,$parent_forms)) {
                    $parent_forms[$child_form] = array();
                }
                $parent_forms[$child_form][] = $form;
            }
        }
        foreach ($forms as $form) {
            if ( !($formObj = $this->form_factory->createContainer($form)) instanceof I2CE_Form) {
                continue;
            }            
            $form_list .= "==$form==\n";
            $form_list .= "The form ''$form'' is implemented by the class: [[Class: " . get_class($formObj) . " |" . get_class($formObj). "]]\n";
            $desc = '';
            if ($config->setIfIsSet($desc,"/modules/forms/forms/$form/meta/description")) {
                $form_list .= "\n" .trim($desc) . "\n\n";
            }
            $child_forms = array_intersect($formObj->getChildForms(),$forms);
            sort($child_forms);
            if (count($child_forms) > 0) {
                foreach ($child_forms as &$child_form) {
                    $child_form =   "[[#$child_form|$child_form]]";
                }
                unset($child_form);
                $form_list .= "It has the child forms:\n*" . implode( "\n",$child_forms) . "\n";
            }
            if (array_key_exists($form,$parent_forms) && is_array($parent_forms[$form]) && count($parent_forms[$form]) > 0) {
                $p_forms = $parent_forms[$form];
                sort($p_forms);
                foreach ($p_forms as &$p_form) {
                    $p_form =   "[[#$p_form|$p_form]]";
                }
                unset($p_form);
                $form_list .= "It is a child of the following forms:\n*" . implode( "\n",$p_forms) . "\n";
            }
            $form_list .= "It has the following fields:\n";
            foreach ($formObj as $field=>$fieldObj) {
                if (! ($field_def = $this->reverseFieldDef(get_class($fieldObj)))) {
                    continue;
                }
                if (!$fieldObj->isInDB()) {
                    continue;
                }
                $form_list .= "*$field:\n";
                $form_list .= "**Header: " . $fieldObj->getHeader() . "\n";
                $form_list .= "**Type: [[Class: " . get_class($fieldObj) . " |". $field_def."]]\n";
                $restrictions = false;
                if ($fieldObj->getOption('required')) {
                    $restrictions = 'Required';
                }
                $unique = '';
                if ($fieldObj->hasOption('unique') && $fieldObj->getOption('unique')) {
                    if ($fieldObj->hasOption('unique_field') && ($unique_field = $fieldObj->getOption('unique_field'))) {
                        $unique = 'Unique in {' . trim($unique_field) . '} ';
                    } else {
                        $unique = 'Unique ';
                    }
                    if ($restrictions) {
                        $restrictions = $restrictions .', '.$unique;
                    } else {
                        $restrictions = $unique;
                    }

                }
                if ($restrictions) {
                    $form_list .= "**Restrictions: $restrictions\n";
                }
                if (!$this->check_map ) {
                    continue;
                }
                if (!$fieldObj instanceof I2CE_FormField_MAPPED) {
                    continue;
                }
                $map_forms = array_intersect($fieldObj->getSelectableForms(),$forms);
                sort($map_forms);
                foreach ($map_forms as &$map_form) {
                    $map_form = "[[#$map_form|$map_form]]";
                }
                $form_list .= "**Maps To Forms: " . implode(',', $map_forms) . "\n";
            }
        }
        $file = $this->getOutputFile('wiki');
        if ( file_put_contents($file,$form_list) === false)  {
            I2CE::raiseError("Could not write wiki article to " . $file);
        } else {
            I2CE::raiseError("Wiki article saved to $file");
        }
    }



    /**
     * Produces  a .dot file for the given forms as a string
     * @param array $forms of string the forms
     */
    public function dot($forms) {        
        $nodes = array();
        $paths = array();
        $config = I2CE::getConfig();
        $scheme_details  = $this->getSchemeDetails('dot');    
        if (array_key_exists('colors',$scheme_details) && is_array($scheme_details['colors'])) {
            $form_groups = $scheme_details['colors'];
        } else {
            $form_groups = array();
        }
        $node_groups = array();
        sort($forms);
        foreach ($forms as $form) {
            if ( !($formObj = $this->form_factory->createContainer($form)) instanceof I2CE_Form) {
                continue;
            }
            $color = 'ivory3';
            foreach ($form_groups as $m_form=>$m_color) {
                if (strpos($form,$m_form) !== false) {
                    $color = $m_color;
                    break;
                }            
            }
            $fields = array();
            foreach ($formObj as $field=>$fieldObj) {
                if (! ($field_def = $this->reverseFieldDef(get_class($fieldObj)))) {
                    continue;
                }
                if (!$fieldObj->isInDB()) {
                    continue;
                }
                $header = trim($fieldObj->getHeader());
                $req = '';
                if ($fieldObj->getOption('required')) {
                    $req = ' *';
                }
                $unique = '';
                if ($fieldObj->hasOption('unique') && $fieldObj->getOption('unique')) {
                    if ($fieldObj->hasOption('unique_field') && ($unique_field = $fieldObj->getOption('unique_field'))) {
                        $unique = '!∈ \{' . trim($unique_field) . '\} ';
                    } else {
                        $unique = '! ';
                    }
                    if ($req) {
                        $unique = ','.$unique;
                    } else {
                        $unique = ' '.$unique;
                    }

                }
                $header = $header . $req . $unique;
                if ($header) {
                    $fields[] =  '<tr><td ALIGN=\'LEFT\'>' . $field . ' (' . $field_def . ')'   .  '</td></tr><tr><td ALIGN=\'LEFT\'>  <font color=\'grey30\'>' . $header .'</font></td></tr>';
                } else {
                    $fields[] =  '<tr><td ALIGN=\'LEFT\'>' .  $field .  ' (' . $field_def . ')'   .  '</td></tr>';
                }
                if (!$this->check_map )  {
                    continue;
                }
                if (!$fieldObj instanceof I2CE_FormField_MAPPED) {
                    continue;
                }
                $map_forms = array_intersect($fieldObj->getSelectableForms(), $forms);
                sort($map_forms);
                if (count($map_forms)  > 1) {                    
                    if (!array_key_exists('splitter+',$node_groups)) {
                        $node_groups['splitter+'] = array();
                    }
                    $node_groups['splitter+'][] = "\"splitter+{$form}+{$field}\" [shape=point size = 1 label = \"\"  ] ;";
                    $paths[] = "\"{$form}\" -> \"splitter+{$form}+{$field}\" [arrowhead = none label = \"$field\" ];";
                    foreach ($map_forms as $map_form) {
                        if (!in_array($map_form,$forms)) {
                            continue;
                        }
                        $paths[] = "\"splitter+{$form}+{$field}\" -> \"{$map_form}\"  ;";
                    }
                } else  if (count($map_forms)  == 1) {
                    reset($map_forms);
                    $map_form = current($map_forms);
                    if (in_array($map_form,$forms)) {
                        if ($field !== $map_form) {
                            $paths[] = "\"$form\" -> \"$map_form\" [ label = \"$field\" ]  ;";
                        } else {
                            $paths[] = "\"$form\" -> \"$map_form\"  ;";
                        }               
                    }
                }
            }
            $label = '<table border=\'0\' cellborder=\'0\'><tr><td BGCOLOR=\'white\' BORDER=\'1\'>' . $form  . ' (' . get_class($formObj) . ') </td></tr>' . implode('',$fields) . '</table>';

            if (!array_key_exists($color,$node_groups) || !is_array($node_groups[$color])) {
                $node_groups[$color] = array();
            }
            $node_groups[$color][$form] = "\"$form\" [style=filled fillcolor = $color   label =<$label> shape = \"Mrecord\"   ];";
                             
            $child_forms = array_intersect($formObj->getChildForms(), $forms);
            sort($child_forms);
            if (count($child_forms) > 0) {            
                foreach ($child_forms as $child_form) {
                    $paths[] = "\"$form\" -> \"$child_form\" [color=firebrick];";
                }
            }

        }

        
        if (array_key_exists('graph_options',$scheme_details) && is_array($scheme_details['graph_options'])) {
            $graph_options = $scheme_details['graph_options'];
        } else {
            $graph_options = array();
        }
        
        if (!array_key_exists('label',$graph_options) || !$graph_options['label'] || $graph_options['label'] == "''" ||  $graph_options['label'] == '""') {
            $module = $this->getModule();
            $title = $this->getDisplayName($module);            
            $version = $this->getVersion($module);
            if ($version) {
                $title .= ' - ' . $version;
            }                        
            $graph_options['label'] = '"' . $title . '"';
        }
        $bgcolor = 'white';        
        if (array_key_exists('bgcolor',$graph_options)) {
            $bgcolor = $graph_options['bgcolor'];
        }
        $graph_details =   "graph [";
        foreach ($graph_options as $key=>$val) {
            $graph_details .= "\n\t\t" .  $key .'=' . $val;
        }
        $graph_details .=  "\n\t];\n\tratio = auto;\n";
        foreach ($node_groups as $colors=>$ns) {
            foreach ($ns as $n) {
                $nodes[] = $n;
            }
        }
        $graph =  "digraph g {\n\t$graph_details\n\t" . implode( "\n\t",$nodes) . implode("\n\t",$paths) . "\n}\n";
        $dot_file  = $this->getOutputFile('dot');
        if (file_put_contents($dot_file,$graph) === false) {
            I2CE::raiseError("Could not write to $dot_file");
        }else {
            I2CE::raiseError(".dot graph file saved to $dot_file");
        }


        $dot = trim(`which dot`);
        $unflatten = trim(`which unflatten`);
        if (!$dot || !$unflatten) {
            I2CE::raiseError("the dot utility was not found on your system.  cannot create the imate. try sudo apt-get install dot");
            return ;
        }
        
        $output_file = $this->getOutputFile('gif');
        $dot = "$unflatten -f -l 2 -c 2 | $dot -T gif ";
        $composite = trim(`which composite`);
        $convert = trim(`which convert`);
        if ($composite) {            
            $watermark_file = I2CE::getFileSearch()->search('IMAGES','form_documentor_legend.gif');
            $watermark = '';            
            if ($watermark_file) {
                if ($convert) {
                    $watermark  = "  |$convert gif:-   -bordercolor white  -border 0x100 - |$composite -gravity SouthEast  $watermark_file gif:- ";
                } else {
                    $watermark  = "  |$composite -gravity SouthEast  $watermark_file -splice 0x20 gif:-   ";
                }
            }
            $exec = $dot  . $watermark . $output_file;
        } else {
            I2CE::raiseError("Imagemagick utitilies were not found on your system.  cannot watermark the file. try sudo apt-get isntall imagemagick");
            $exec = $dot . '-o ' . $output_file;
        }
        I2CE::raiseError("Attempting to execute:\n\t" . $exec);
        $proc = popen ($exec , "w");
        if (!is_resource($proc)) {
            I2CE::raiseError("Could not start execute");
        } else {
            fwrite($proc,$graph);
            fclose($proc);
            I2CE::raiseError("You should now have a graph at $output_file");
        }
        return ;
    }



    /**
     * The business method if this page is called from the commmand line
     * @param array $request_remainder the remainder of the request after the page specfication.  
     * @param array $args the array of unix style command line arguments 
     */
    protected function actionCommandLine($args,$request_remainder) { 
        $config = I2CE::getConfig();
        if (!$config->is_parent("/modules/forms/forms")) {
            I2CE::raiseError("No Forms", E_USER_ERROR);
        }
        $config = I2CE::getConfig();
        $module = $config->config->site->module;
        if (!$module) {
            I2CE::raiseError("No site module");
        }

        $formConfig = $config->modules->forms->forms;
        $this->locale = I2CE::getRuntimeVariable('locale',false);
        if (!$this->locale) {
            $this->locale = I2CE_Locales::DEFAULT_LOCALE;
        }
        $this->locale = I2CE_Locales::ensureSelectableLocale($this->locale);
        I2CE_Locales::setPreferredLocale($this->locale);
        $forms = I2CE::getRuntimeVariable('forms',false);
        if ($forms) {
            $forms = explode(',',$forms);
        } else {
            $forms  = $formConfig->getKeys('/modules/forms/forms');
            $cli = new I2CE_CLI();
            $forms = $cli->chooseMenuValues("Select Forms:",$forms);
        }
        if ($skipforms = I2CE::getRuntimeVariable('skipforms',false)) {
            $skipforms = explode('#',$skipforms);
            $t_forms = array();
            foreach ($forms as $form) {
                foreach ($skipforms as $skipform) {
                    if (preg_match('/' .$skipform .'/', $form)) {
                        continue 2;
                    }
                }
                $t_forms[] = $form;
            }
            $forms = $t_forms;
        }
        sort($forms, SORT_STRING);
        switch ($this->page) {
        case 'wiki':
            $this->wiki($forms);
            break;
        case 'dot':
            $this->dot($forms);
            break;
        case 'text':
        default:
            $this->text($forms);
            break;
        }
    }


  }








# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
