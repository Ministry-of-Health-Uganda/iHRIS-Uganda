<?php
/*
 * © Copyright 2012 IntraHealth International, Inc.
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
 * Edit participants action for a training
 * @package iHRIS
 * @subpackage Common
 * @access public
 * @author  Carl Leitner <litlfred@ibiblio.org> 
 * @copyright Copyright &copy; 2012 IntraHealth International, Inc. 
 * @since v4.1.3
 * @version v4.1.3
 */

/**
 * The action page class for editing particpants for a training
 * @package iHRIS
 * @subpackage Train
 * @access public
 */
class I2CE_PageGenerateRelationshipTemplate extends I2CE_PageActionRelationship {
    /**
     * The charset for this display
     * @var $charset
     */
    protected $charset = 'UTF-8';

    /**
     * Should html encoding be done on the data?
     * @var $encoding
     */
    protected $encoding = true;

    protected $template_file = false;
    protected $template_vars = array();
    protected $template_var_options = array();
    protected $template_var_keys = array();
    /**
     * Perform the main actions of the page.
     * @return boolean
     */
    protected function action() {
        if ( !parent::action() ) {
            I2CE::raiseError("Base action failed");
            return false;
        }
        $this->getODTTemplate();
        if (! ($this->template_file)) {
            I2CE::raiseError("No Template");
            return false;
        }        
        if (! ($this->getTemplateVariables())) {
            I2CE::raiseError("No template variables");
            return false;
        }
        if (!$this->loadData()) {
            I2CE::raiseError("could not load data");
            return false;
        }
        $this->generateTemplate();
    }

    protected function getFields() {
        $vars = $this->template_vars;
        foreach ($vars as &$fields){
            if (!in_array('id',$fields)) {
                $fields[] = 'id';
            }
        }
        unset($fields);
        return $vars;
    }


    protected function generateTemplate() {
        I2CE::longExecution( array( "max_execution_time" => 1800 ) );
        if (!class_exists('I2CE_Odf')) {
            I2CE::raiseError("Please turn on the printed forms module");
            return false;
        }
        $odf_config = array( 'DELIMITER_LEFT' => '{{{',
                'DELIMITER_RIGHT' => '}}}',
                'ZIP_PROXY' => 'PhpZipProxy',
                );
        $odf = new I2CE_Odf( $this->template_file, $odf_config );

        $segment_style ='global';
        if (array_key_exists('segment_style',$this->args)
            && is_string($this->args['segment_style'])) {
            $segment_style = $this->args['segment_style'];
        }
        $method = 'generateTemplate_' . $segment_style;
        if (!$this->_hasMethod($method)) {
            I2CE::raiseError("Invalid method: $method");
            return false;
        }
        $this->$method($odf);
        I2CE::raiseError("Outputing template");
        $this->outputTemplate($odf,basename($this->template_file,'.odt'));        
    }


    protected function generateTemplate_report_row($odf) {
        $user = new I2CE_User();
        $name = $user->firstname . ' ' . $user->lastname;
        try {
            $odf->setVars( '++user_name', $name, $this->encoding, $this->charset );
        } catch ( OdfException $e ) {
            //It's ok if it's not there so don't do anything.
        }
        $time = strftime("%c");
        try {
            $odf->setVars( '++time', $time, $this->encoding, $this->charset );
        } catch ( OdfException $e ) {
            //It's ok if it's not there so don't do anything.
        }
        $this->setStyleVars($odf);
        $this->setHeaderVars($odf);
        try {
            $odf_row = $odf->setSegment( 'report_row' );
        } catch ( OdfException $e ) {
            I2CE::raiseError( "Couldn't find report_row in ODT template" );
            return false;
        }
        I2CE::raiseError("report_row has keys " . print_r($odf_row->getKeys(),true));
        if (array_key_exists('segement_break',$this->args) && $this->args['segment_break']) {
            $odf_row->setSegmentBreak($this->args['segment_break']);
        }
        $required = array();
        if (array_key_exists('required',$this->args)) {
            $required = $this->args['required'];
        }    
        $required = array();
        $missing = $this->getMissingFields($required);
        if (count($missing) > 0) {
            I2CE::raiseError("Could not generate because missing required fields:" . print_r($missing,true));
            $this->userMessage("Required Fields Were Missing");
            return false;
        }
        $this->loopData($odf,$odf_row);
        try {            
            $odf->mergeSegment( $odf_row );
        } catch (OdfException $e) {
            I2CE::raiseError("Could not merge global segemnet");
            return false;
        }
    }

    protected function generateTemplate_global($odf) {
        try {
            $global_segment = $odf->setGlobalSegment();
        } catch ( OdfException $e ) {
            I2CE::raiseError( "Couldn't set global segement");
            return false;
        }
        $user = new I2CE_User();
        $name = $user->firstname . ' ' . $user->lastname;
        try {
            $odf->setVars( '++user_name', $name, $this->encoding, $this->charset );
        } catch ( OdfException $e ) {
            //It's ok if it's not there so don't do anything.
        }
        $time = strftime("%c");
        try {
            $odf->setVars( '++time', $time, $this->encoding, $this->charset );
        } catch ( OdfException $e ) {
            //It's ok if it's not there so don't do anything.
        }
        
        $date = strftime("%d %B %Y");
        try {
            $odf->setVars( '++date', $date, $this->encoding, $this->charset );
        } catch ( OdfException $e ) {
            //It's ok if it's not there so don't do anything.
        }
        $this->setStyleVars($odf);        
        if (array_key_exists('segement_break',$this->args) && $this->args['segment_break']) {
            $global_segment->setSegmentBreak($this->args['segment_break']);
        }
        $required = array();
        if (array_key_exists('required',$this->args)) {
            $required = $this->args['required'];
        }    
        $required = array();
        $missing = $this->getMissingFields($required);
        if (count($missing) > 0) {
            I2CE::raiseError("Could not generate because missing required fields:" . print_r($missing,true));
            $this->userMessage("Required Fields Were Missing");
            return false;
        }
        $this->loopData($odf,$global_segment);
        try {
            $odf->mergeGlobalSegment();
        } catch (OdfException $e) {
            I2CE::raiseError("Could not merge global segemnet");
            return false;
        }
    }


    protected function setHeaderVars($segment) {
        $rel_base = '/modules/CustomReports/relationships';
        if (array_key_exists('relationship_base',$this->args)
            && is_scalar($this->args['relationship_base'])) {
            $rel_base = $this->args['relationship_base'];
        }
        $use_cache = I2CE_ModuleFactory::instance()->isEnabled('CachedForms');
        if (array_key_exists('use_cache',$this->args)) {
            $use_cache = $this->args['use_cache'];
        }
        if ($use_cache) {
            $cache_callback =array('I2CE_CachedForm','getCachedTableName');
        } else {
            $cache_callback = null;
        }
        try{
            if (!array_key_exists('header_relationship',$this->args)
                || !is_string($this->args['header_relationship'])
                || strlen($this->args['header_relationship']) == 0
                ||! ($formRelationship = new I2CE_FormRelationship($this->args['header_relationship'],$rel_base,$cache_callback)) instanceof I2CE_FormRelationship
                || $formRelationship->getPrimaryForm() != $this->primObj->getName()
                ) {
                return;
            }
            if (array_key_exists('use_display_fields',$this->args)
                && $this->args['use_display_fields'] == 0 ){
                $formRelationship->useRawFields();
            }
            $fields = $this->getFields();
            $data = $formRelationship->getFormData($this->primObj->getName(),$this->primObj->getId(),$fields,array(),true);
            $row =0;
            foreach ($data as $formfields) {            
                if (! ($this->setData($segment,$row,$formfields,$formRelationship))) {
                    I2CE::raiseError("Error setting data for row: $row");
                    return false;
                }
                break;
                //only do one row
            }
        } catch (Exception $e) {

        }        
    }

    protected function loopData($odf,$segment,$data = null) {
        if ($data == null) {
            $data = $this->data;
        }
        $row = 0;
        if (is_array($data)) {
            reset($data);
        } else if ($data instanceof Iterator) {
            $data->rewind();
        }
        I2CE::raiseError("Loop Data");
        foreach ($data as $formfields) {            
            $row++;         
            if (! ($this->setData($segment,$row,$formfields))) {
                I2CE::raiseError("Error setting data for row: $row");
                return false;
            }
            $segment->merge();
        }
        return true;
    }

    protected function getMissingFields($required,$data = null) {
        if ($data == null) {
            $data = $this->data;
        }
        $all_missing = array();
        if (!is_array($required) || count($required) == 0) {
            return $all_missing;
        }
        if (is_array($data)) {
            reset($data);
        } else if ($data instanceof Iterator) {
            $data->rewind();
        }
        $row = 0;
        foreach ($data as $formfields) {
            $row++;
            foreach ($required as $reportformfield) {
                list($reportform,$field) = array_pad(explode("+",$reportformfield,2),2,'');
                $missing = array();
                if  (!array_key_exists($reportform,$formfields)
                     || !is_array($fields = $formfields[$reportform])
                     || !array_key_exists($field,$fields)
                     ||  strlen($fields[$field]) == 0
                    ){
                    $missing[] = $reportformfield;
                }
                if (count($missing) > 0) {
                    $all_missing[$row] = $missing;
                }
            }
        }
        return $all_missing;
    }

    protected function setStyleVars($odf,$data = null) {
        if ($data == null) {
            $data = $this->data;
        }
        $formObjs = array();
        if (is_array($data)) {
            reset($data);
        } else if ($data instanceof Iterator) {
            $data->rewind();
        }
        $ff = I2CE_FormFactory::instance();
        $formfields = current($data);
        foreach ($data as $formfields) {
            foreach ($formfields as $reportform=>$fields) {
                foreach ($this->template_vars[$reportform] as $field) {
                    if (!array_key_exists($field,$fields)) {
                        $fields[$field] = '';
                    }
                }
                foreach ($fields as $field=>$value) {
                    $formfield = $reportform . '+' . $field;
                    if (!array_key_exists($formfield,$this->template_var_keys)) {
                        continue;
                    }                    
                    $keys = $this->template_var_keys[$formfield];
                    foreach ($keys as $key) {
                        try {                 
                            $odf->setStyleVars($key, $value, $this->encoding, $this->charset );
                        } catch( Exception $e ) {
                            //It's ok if it's not there so don't do anything.
                        }
                    }
                }
            }
            break;
        }
    }

    
    protected function setData($segment,$row,$formfields,$formRelationship = null) {
        if ($formRelationship == null) {
            $formRelationship = $this->formRelationship;
        }
        try {
            $segment->setVars( '++row_num', $row, $this->encoding, $this->charset );
        } catch( SegmentException $e ) {
            //do nothing
        }        
        $ff = I2CE_FormFactory::instance();
        $formObjs = array();
        foreach ($formfields as $reportform=>$fields) {
            $form = $formRelationship->getForm($reportform);
            if (!array_key_exists($reportform,$formObjs)) {
                $formObjs[$reportform] = $ff->createContainer($form);
            }
            $formObj = $formObjs[$reportform];
            if (!$formObj instanceof I2CE_Form) {
                I2CE::raiseError("Bad form report form  $reportform.  Could not instantiate form $form");
                continue;
            }

            foreach ($this->template_vars[$reportform] as $field) {
                if (!array_key_exists($field,$fields)) {
                    $fields[$field] = '';
                }
            }
            foreach ($fields as $field=>$value) {
                $formfield = $reportform . '+' . $field;
                if (!array_key_exists($formfield,$this->template_var_keys)) {
                    continue;
                }                    
                $keys = $this->template_var_keys[$formfield];
                foreach ($keys as $key) {
                    $options =  $this->template_var_options[$key];
                    if ($formObj instanceof I2CE_Form) {
                        $fieldObj = $formObj->getField($field);
                        $this->setSegmentValue($segment,$fieldObj,$key,$value,$options);
                    } else {
                        $segment->setVars( $key, '', $this->encoding, $this->charset );
                    }
                }
            }
        }
        return true;
    }

    protected function outputTemplate($odf,$basename) {
        if (!array_key_exists('tmp_dir',$this->args)
            || ! is_scalar($tmp_dir = $this->args['tmp_dir'])
            || !is_dir($tmp_dir)) {
            $tmp_dir = sys_get_temp_dir();
        }
        $odt_temp_file = tempnam($tmp_dir,'_ODT_');
        $odf->saveToDisk($odt_temp_file);

        $unoconv = false;
        if (I2CE::getConfig()->setIfIsSet($unoconv,"/modules/PrintedFormsODT/unoconv/exec")) {
            $unoconv = system("which " . $unoconv);
        }
        $conversions = array();
        I2CE::getConfig()->setIfIsSet($conversions,"/modules/PrintedFormsODT/unoconv/conversions",true);
        $format = false;
        if (array_key_exists('format', $this->args)) {
            $format = $this->args['format'];
        }
        if ($this->request_exists('format')) {
            $format = $this->request('format');
        }
        $command_options = '';
        if ($unoconv 
            && is_string($format)
            && is_array($conversions)
            && array_key_exists($format,$conversions)
            && is_array($conversion = $conversions[$format])
            && array_key_exists('mime',$conversion)
            && is_string($mime = $conversion['mime'])
            && array_key_exists('ext',$conversion)
            && is_string($ext = $conversion['ext'])
            ) {   
            $conv_temp_file = tempnam($tmp_dir,'_' . $format . '_' );
            $export_options = '';
            if (array_key_exists('export_options',$this->args)
                && is_array($this->args['export_options'])
                && array_key_exists($format,$this->args['export_options'])
                && is_string($t_export_options  = $this->args['export_options'][$format])
                && strlen ($t_export_options  = trim($t_export_options)) > 0) {
                $export_options = ' -e ' . $t_export_options . ' ';
            }
            $connection = '';
            if (array_key_exists('connection',$this->args)
                && is_string($t_connection = $this->args['connection'])
                && strlen ($t_connection  = trim($t_connection)) > 0) {
                $connection = ' -c ' . $t_connection . ' ';
            }

            //On Ubuntu server 12.04 the odt needs chmod 605 before of try to use unoconv
            chmod($odt_temp_file, 0605);


            $multiple = false;
            $unoconv_config = I2CE::getConfig()->modules->PrintedFormsODT->unoconv;
            $unoconv_config->setIfIsSet($multiple,"allow_multiple");
            $home_idx = 1;
            if ( $multiple ) {
                while( $home_idx ) {
                    if ( $unoconv_config->is_parent( "status/$home_idx" ) ) {
                        $avail = 0;
                        $unoconv_config->setIfIsSet($avail, "status/$home_idx/available" );
                        if ( $avail ) {
                            break;
                        } else {
                            $home_idx++;
                            continue;
                        }
                    }
                    break;
                }
                $unoconv_config->status->$home_idx->available = 0;
                if ( !file_exists( "/tmp/unoconv_homes/home$home_idx" ) ) {
                    mkdir( "/tmp/unoconv_homes/home$home_idx", 0755, true );
                }
                $exec = "HOME=/tmp/unoconv_homes/home$home_idx $unoconv --port=400$home_idx -o $conv_temp_file $export_options -f $format $odt_temp_file";
            } else {
                $exec = $unoconv . ' -o ' . $conv_temp_file . $export_options .' -f ' . $format . ' ' .  $odt_temp_file;
            }

            
            //NEED TO DO ON UBUNTU SERVER: sudo mkdir /home/www-data; sudo  chown www-data:www-data /home/www-data
            $error = 0;
            system ($exec,$error);
            if ( $multiple ) {
                $unoconv_config->status->$home_idx->available = 1;
            }
            if ( ($errors = I2CE_Dumper::cleanlyEndOutputBuffers())) {
                I2CE::raiseError("Errors:\n" . $errors);
            }            
            I2CE::raiseError("Converting with $exec");
            if ($error == 0) {
                header('Content-type: ' . $mime);
                header('Content-Disposition: attachment; filename="'.$basename. '.' . $ext. '"');
                readfile($conv_temp_file);
                exit;
            }            
            I2CE::raiseError("Error ($error) with $exec");
        } 
        if ( ($errors = I2CE_Dumper::cleanlyEndOutputBuffers())) {
            I2CE::raiseError("Errors:\n" . $errors);
        }            
        header('Content-type: application/vnd.oasis.opendocument.text');
        header('Content-Disposition: attachment; filename="'.$basename.'.odt"');
        readfile($odt_temp_file);
        exit;
    }

    protected function setSegmentValue($segment,$fieldObj,$key,$value,$options=array()) {
        if (array_key_exists('eval',$options) && is_callable($options['eval'])) {
            $value = call_user_func($options['eval'],$value);
        }        
        if ($fieldObj instanceof I2CE_FormField_IMAGE) {
            $fieldObj->setFromDB($value);
            try {                    
                $segment->setImageFromField($key,$fieldObj,$options);
            }catch (Exception $e) {
                I2CE::raiseMessage("Cant set image $key" );
                //It's ok if it's not there so don't do anything.
            }
        } else {
            if ($fieldObj instanceof I2CE_FormField_MAP_MULT && array_key_exists('separator',$options)) {
                $value = explode(",",$value);
                foreach($value as &$v) {
                    $v = trim($v);
                }
                unset($v);
                $value = implode(str_replace('\n', "\n", $options['separator']),$value);
            }
            try {                    
                $segment->setVars( $key, $value, $this->encoding, $this->charset );
            } catch( Exception $e ) {
                //It's ok if it's not there so don't do anything.
            }
        }
    }

    protected function getTemplateVariables() {
        $template_contents = new ZipArchive();
        if ($template_contents->open($this->template_file)!==TRUE) {
            I2CE::raiseError("Could not extract odt file");
            return false;
        }
        $template_vars = array();
        for ($i=0; $i<$template_contents->numFiles;$i++) {
            $stats = $template_contents->statIndex($i);
            if (  $stats['name'] != 'content.xml' && $stats['name'] !='styles.xml') {
                continue;
            }
            $matches = array();
            //pull out all the template variables for processing.
            preg_match_all( '/{{{([0-9a-zA-Z_\-\+\,\\\=\.:]+(\(.*?\))?)}}}/', $template_contents->getFromIndex($i), $matches, PREG_SET_ORDER);
            foreach ($matches as $match) {
                if (!is_array($match) || count($match) < 2 || !is_string($match[1]) || strlen($match[1]) == 0) {
                    continue;
                }
                $template_vars[] = $match[1];
            }
            $template_vars = array_unique($template_vars);
        }
        if (count($template_vars) == 0) {
            return false;
        }
        foreach ($template_vars as $key) {
            if ((!is_string($key) || strlen($key) < 1)) {
                continue;
            }
            if ($key[0] == '+') {
                continue;
            }
            list($namedform,$field,$t_extra) = array_pad(explode("+",$key,3),3,'');
            $extra = array();
            if (is_string($t_extra) && strlen($t_extra) > 0) {
                $t_extra = explode(',',$t_extra);
                foreach ($t_extra as $ex) {
                    list($ex_k,$ex_v) = array_pad(explode('=',$ex,2),2,'');
                    if (!$ex_k || !$ex_v) {
                        continue;
                    }
                    $extra[$ex_k] = $ex_v;
                }
            }             
            $namedform = trim($namedform);
            $field = trim($field);
            if (!array_key_exists($namedform,$this->template_vars)) {
                $this->template_vars[$namedform] = array();
            }
            $this->template_var_options[$key] = $extra;
            $ff = $namedform.'+'.$field;
            if (!array_key_exists($ff,$this->template_var_keys)) {
                $this->template_var_keys[$ff] = array();
            }
            $this->template_var_keys[$ff][] = $key;
            $this->template_vars[$namedform][] = $field;
        }        
        foreach ($this->template_vars as &$fields) {
            $fields = array_unique($fields);
        }
        foreach ($this->formRelationship->getFormNames() as $reportform) {
            if (!array_key_exists($reportform,$this->template_vars)) {
                $this->template_vars[$reportform] = array();
            }
        }
        return true;
    }



    protected function getODTTemplate() {
        //print_r($this->args);
        if ( array_key_exists('template_upload',$this->args) && is_array($template_upload = $this->args['template_upload'])
             && array_key_exists('content',$template_upload) && $template_upload['content']
             && array_key_exists('name',$template_upload) && $template_upload['name'] ) {
            $name = $template_upload['name'];
            $pos = strrpos($name,'.');
            $ext ='';
            if ($pos !== false) {
                $ext = substr($name,$pos);
                $name = substr($name, 0,$pos);
            }
	    $this->template_file = tempnam(sys_get_temp_dir(), basename($name .'_' )) . $ext;
	    file_put_contents($this->template_file,$template_upload['content']);            
        } else  if ( array_key_exists('template_file',$this->args) && $template = $this->args['template_file']) {
            $this->template_file = I2CE::getFileSearch()->search('ODT_TEMPLATES',$template);
            if (!$this->template_file) {
                I2CE::raiseError("No template file found from $template");
                return false;
            }
        }


    }

    
}

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
