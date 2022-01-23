<?php
/**
 * @copyright © 2008, 2009 Intrahealth International, Inc.
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
*/
/**
*  I2CE_CustomReport_Display_PDF
* @package I2CE
* @subpackage Core
* @author Luke Duncan <lduncan@intrahealth.org>
* @since 4.1
* @version 4.1
* @access public
*/


class I2CE_CustomReport_Display_ODT extends I2CE_CustomReport_Display{

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

    /**
     * The odf file object.
     * @var I2CE_Odf
     */
    protected $odf;

    /**
     * The odf row for each report row
     * @var Segment
     */
    protected $odf_row;

    /**
     * The header variable names and values
     * @var array
     */
    protected $header_vars;
 
    /**
     * Total columns for exported reports.
     * @var array
     */
    protected $row_totals;

    protected function canView() {
        return true;
    }

    protected $image_objs = array();
    protected $extra = array();
    protected $full_keys = array();

    /**
     * Display the report
     * @param DOMNode $contentNode The DOM node we wish to display into
     * @param boolean $processResults Defaults to true meaning we run through the results
     * @param mixed $controls.  If null (default), we display all the report controsl.  If string or an array of string, we only display the indicated controls
     * @returns boolean. true on sucess
     */
    public function display($contentNode,$processResults=true, $controls = null) {
        I2CE::longExecution( array( "max_execution_time" => 1800 ) );
        $this->saveDefaultView();


        $template = $this->defaultOptions['odt_template'];

        $template_upload = false;
        $template_file = null;

        if ( $this->config->setIfIsSet($template_upload, "printed_forms/$template/template_upload",true )
             && array_key_exists('content',$template_upload) && $template_upload['content']
             && array_key_exists('name',$template_upload) && $template_upload['name'] ) {
            $template_file = $template_upload['name'];
            $pos = strrpos($template_file,'.');
            if ($pos !== false) {
                $name = substr($template_file, 0,$pos);
            } else {
                $name = $template_file;
            }
	    $template_loc = tempnam(sys_get_temp_dir(), basename($name .'_' )) . '.odt';
	    file_put_contents($template_loc,$template_upload['content']);            
        } else {
            $this->config->setIfIsSet( $template_file, "printed_forms/$template/template" );
            $template_loc = I2CE::getFileSearch()->search( 'ODT_TEMPLATES', $template_file );
            if ( !$template_loc ) {
                I2CE::raiseError( "No template file found from $template_file" );
                return false;
            }
        }
        $this->header_vars = array();
        foreach( $this->getDisplayFieldsData() as $formfield=>$data ) {
            $this->header_vars["++header+$formfield"] = $data['header'];
            list($formObj,$fieldObj)=$this->getFormFieldObjects($formfield);
            $this->image_objs[$formfield] =  ($fieldObj instanceof I2CE_FormField_IMAGE);
            $this->extra[$formfield] = array();
        }
        $this->limit_vars = $this->getLimitVars();
        $this->row_totals = array();
        if ( $this->config->is_scalar( "export_total_fields" ) ) {
            I2CE::raiseMessage("getting total fields " . $this->config->export_total_fields);
            $total_fields = explode( ',', $this->config->export_total_fields );
            foreach( $total_fields as $tf ) {
                $this->row_totals[$tf] = 0;
            }
        }


        $odf_config = array( 'DELIMITER_LEFT' => '{{{',
                'DELIMITER_RIGHT' => '}}}',
                'ZIP_PROXY' => 'PhpZipProxy',
                );
        $this->odf = new I2CE_Odf( $template_loc, $odf_config );

        try {
            $this->odf->setVars( '++report_title', $this->config->display_name, $this->encoding, $this->charset );
        } catch ( OdfException $e ) {
            //It's ok if it's not there so don't do anything.
        }
        try {
            $this->odf->setStyleVars( '++report_title', $this->config->display_name, $this->encoding, $this->charset );
        } catch ( OdfException $e ) {
            //It's ok if it's not there so don't do anything.
        }
        try {
            $this->odf->setVars( '++report_description', $this->config->description, $this->encoding, $this->charset );
        } catch ( OdfException $e ) {
            //It's ok if it's not there so don't do anything.
        }
        try {
            $this->odf->setStyleVars( '++report_description', $this->config->description, $this->encoding, $this->charset );
        } catch ( OdfException $e ) {
            //It's ok if it's not there so don't do anything.
        }

        foreach( $this->header_vars as $key => $val ) {
            try {
                $this->odf->setVars( $key, $val, $this->encoding, $this->charset );
            } catch ( OdfException $e ) {
                //It's ok if it's not there so don't do anything.
            }
            try {
                $this->odf->setStyleVars( $key, $val, $this->encoding, $this->charset );
            } catch ( OdfException $e ) {
                //It's ok if it's not there so don't do anything.
            }
        }
        foreach ($this->limit_vars as $key=>$val) {
            try {
                $this->odf->setVars( $key, $val, $this->encoding, $this->charset );
            } catch ( OdfException $e ) {
                //It's ok if it's not there so don't do anything.
            }
            try {
                $this->odf->setStyleVars( $key, $val, $this->encoding, $this->charset );
            } catch ( OdfException $e ) {
                //It's ok if it's not there so don't do anything.
            }
        }

        $user = new I2CE_User();
        $name = $user->firstname . ' ' . $user->lastname;
        try {
            $this->odf->setVars( '++user_name', $name, $this->encoding, $this->charset );
        } catch ( OdfException $e ) {
            //It's ok if it's not there so don't do anything.
        }
        try {
            $this->odf->setStyleVars( '++user_name', $name, $this->encoding, $this->charset );
        } catch ( OdfException $e ) {
            //It's ok if it's not there so don't do anything.
        }

        $time = strftime("%c");
        try {
            $this->odf->setVars( '++time', $time, $this->encoding, $this->charset );
        } catch ( OdfException $e ) {
            //It's ok if it's not there so don't do anything.
        }
        try {
            $this->odf->setStyleVars( '++time', $time, $this->encoding, $this->charset );
        } catch ( OdfException $e ) {
            //It's ok if it's not there so don't do anything.
        }

        $limitsDesc = $this->getReportLimitsDescription();
        try {
            $this->odf->setVars( '++report_limit', $limitsDesc, $this->encoding, $this->charset );
        } catch ( OdfException $e ) {
            //It's ok if it's not there so don't do anything.
        }
        try {
            $this->odf->setStyleVars( '++report_limit', $limitsDesc, $this->encoding, $this->charset );
        } catch ( OdfException $e ) {
            //It's ok if it's not there so don't do anything.
        }
 
        try {
            $this->odf_row = $this->odf->setSegment( 'report_row' );
        } catch ( OdfException $e ) {
            I2CE::raiseError( "Couldn't find report_row in ODT template $template_loc." );
            return false;
        }
        $keys =$this->odf_row->getKeys();
        foreach ($this->odf_row->getKeys() as $key) {
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
            $this->extra[$namedform.'+'.$field] = $extra;
            $this->full_keys[$namedform.'+'.$field] = $key;
        }
            

        $data = $this->getResults();
        if (!$this->processResults($data,$contentNode)) {
            I2CE::raiseError("Could not get results");
            return false;
        }        

        $this->odf->mergeSegment( $this->odf_row );

        foreach ($this->row_totals as $key=>$val) {
            I2CE::raiseMessage("Trying to set total for $key = $val");
            try {
                $this->odf->setVars( "++total+$key", $val, $this->encoding, $this->charset );
            } catch ( OdfException $e ) {
                //It's ok if it's not there so don't do anything.
            }
            try {
                $this->odf->setStyleVars( "++total+$key", $val, $this->encoding, $this->charset );
            } catch ( OdfException $e ) {
                //It's ok if it's not there so don't do anything.
            }
        }


        if ( ($errors = I2CE_Dumper::cleanlyEndOutputBuffers())) {
            I2CE::raiseError("Errors:\n" . $errors);
        }

        $this->odf->exportAsAttachedFile( $template_file );

        exit; // we want to make sure there is no further output or that the $this->page->display() method is not called
    }

    protected function getLimitVars() {
        $limit_vars = array();
        //$this->defaultOptions['nested_limits'][$field] =  array($limit_styles=>$limit_values) {
        if (array_key_exists('nested_limits',$this->defaultOptions) && is_array($this->defaultOptions['nested_limits'])) {
            $limitValues =$this->defaultOptions['nested_limits'];
        } else {
            $limitValues = array();
        }
        try {
            $relObj = new I2CE_FormRelationship($this->reportConfig->relationship);
        }catch(Exception $e) {
            I2CE::raiseError("Invalid relationship $relationship");
            return false;
        }
        $reportform_ids = array();
        $l_values = array();
        foreach ($this->reportConfig->reporting_forms as $reportform=>$formConfig) {
            if (!$formConfig instanceof I2CE_MagicDataNode) {
                continue;
            }        
            $l_values[$reportform] = array();
            foreach ($formConfig->fields as $field=>$fieldConfig) {
                if (!$fieldConfig instanceof I2CE_MagicDataNode) {
                    continue;
                }
                $enabled =false;
                $fieldConfig->setIfIsSet($enabled,"limits/equals/enabled");
                if (!$enabled) {
                    continue;
                }
                $rff = "$reportform+$field";
                if (!array_key_exists($rff,$limitValues) || !is_array($limitValues[$rff]) 
                    ||!array_key_exists('equals',$limitValues[$rff])
                    || !is_array($limitValues[$rff]['equals']) || !array_key_exists('value',$limitValues[$rff]['equals'])
                    || !$limitValues[$rff]['equals']['value'] ||  $limitValues[$rff]['equals']['value']== '|') {
                    $l_values[$reportform][$field] = false;
                }    else {             
                    $l_values[$reportform][$field] = $limitValues[$rff]['equals']['value'];
                }
                //example is $reportform_ids['position]['facility'] = 'facility|22';
            }
        }
        $ff = I2CE_FormFactory::instance();
        foreach ($l_values as $reportform=>$fields) {
            if ( ! ($form = $relObj->getForm($reportform))) {
                continue;
            }
            if (! ($formObj = $ff->createContainer($form)) instanceof I2CE_Form) {
                continue;
            }            
            foreach ($fields as $field=>$value) {
                if (!($mapField = $formObj->getField($field)) instanceof I2CE_FormField_MAP) {
                    continue;
                }
                if ($value) {
                    $mapField->setFromDB($value);
                    if (!($mFormObj = $mapField->getMappedFormObject()) instanceof I2CE_Form) {
                        continue;
                    }
                    foreach ($mFormObj->getFieldNames() as $name) {
                        if (!($fieldObj = $mFormObj->getField($name)) instanceof I2CE_FormField) {
                            continue;
                        }
                        $limit_vars["++limit+" . $reportform . "+". $field . '+' . $name] = $fieldObj->getDisplayValue();
                        //++limit+position+facility+name  or //++limit+position+facility+facility_type
                    }
                    $limit_vars["++limit+" . $reportform . "+". $field] = $mFormObj->name();
                } else {
                    //no value was set... try to blank out values.
                    $sforms = $mapField->getSelectableForms();
                    if (count($sforms) != 1) {
                        continue;
                    }
                    reset($sforms);
                    $sform = current($sforms);
                    if (! ($sformObj = $ff->createContainer($sform)) instanceof I2CE_Form) {
                        continue;
                    }
                    $limit_vars["++limit+" . $reportform . "+". $field] = '';
                    foreach ($sformObj->getFieldNames() as $name) {
                        $limit_vars["++limit+" . $reportform . "+". $field .'+' . $name] = '';
                    }
                }
                //format is ++limit+scheduled_training_course+id+training_course  for each of the 'equals' limits.  for now this is only on formfields
                //now we add in the mapped field values (just to be nice)

            }

        }
        //I2CE::raiseError(print_r($limit_vars,true));
        return $limit_vars;
    }


    /**
     * Process a result row.
     * @param array $row
     * @param int $row_num The current row number when processing results.  If there was a result limit, it starts the count from the beginning of the
     * result offset.  Othwerwise, it starts counting form zero.
     * @param DOMNode $contentNode. Default to null. A node to append the result onto
     */
    protected function processResultRow($row,$row_num,$contentNode=null) {
        $mapped_row = $this->mapResults($row);

        try {
            $this->odf_row->setVars( '++row_num', $row_num+1, $this->encoding, $this->charset );
        } catch( SegmentException $e ) {
            //It's ok if it's not there so don't do anything.
        }

        foreach( $this->header_vars as $key => $val ) {
            try {
                $this->odf_row->setVars( $key, $val, $this->encoding, $this->charset );
            } catch ( SegmentException $e ) {
                //It's ok if it's not there so don't do anything.
            }
        }
        $ff = I2CE_FormFactory::instance();
        foreach( $mapped_row as $key => $val ) {
            if ( array_key_exists( $key, $this->row_totals ) ) {
                $this->row_totals[$key] += $val;
            }
            if (array_key_exists($key,$this->image_objs) && ($fieldObj = $this->image_objs[$key])) {
                //need to get $reportform+id and get the formObj.
                list( $form, $field ) = explode( '+', $key, 2 );
                $formid = $form .'+id';
                if (!array_key_exists($formid,$mapped_row)
                    || ! ($formObj = $ff->createContainer($mapped_row[$formid])) instanceof I2CE_Form
                    || ! ($fieldObj = $formObj->getField($field)) instanceof I2CE_FormField
                    ){
                    I2CE::raiseError("Can't get form/field associated to $key". print_r($mapped_row,true));
                    try {
                        $this->odf_row->setVars($key,''); 
                    } catch(SegementException $e) {
                        //It's ok if it's not there so don't do anything.
                    }
                    continue;
                }
                $formObj->populate();
                try {                    
                    $this->odf_row->setImageFromField($this->full_keys[$key],$fieldObj,$this->extra[$key]);
                }catch (SegmentException $e) {
                    //It's ok if it's not there so don't do anything.
                }
            } else {

                if ( $row_num == 0 ) {
                    try {
                        $this->odf->setVars( $key, $val, $this->encoding, $this->charset );
                    } catch( OdfException $e ) {
                        //It's ok if it's not there so don't do anything.
                    }
                    try {
                        $this->odf->setStyleVars( $key, $val, $this->encoding, $this->charset );
                    } catch( OdfException $e ) {
                        //It's ok if it's not there so don't do anything.
                    }
                }
                
                try {
                    $this->odf_row->setVars( $key, $val, $this->encoding, $this->charset );
                } catch( SegmentException $e ) {
                    //It's ok if it's not there so don't do anything.
                }
            }
        }
        $this->odf_row->merge();

        return true;
    }
   

    /**
     * Adds any controls for this display to the content node.
     * @param DOMNode $contentNode
     * @returns boolean
     */
    protected function displayReportControl( $contentNode ) {
        if ( !parent::displayReportControl( $contentNode ) ) {
            return false;
        }
        $this->template->setDisplayData( "has_odt_templates", false );
        if ( $this->config->is_parent( "printed_forms" ) ) {
            $print_forms = $this->config->printed_forms->getAsArray();
            foreach( $print_forms as $print_form => $print_data ) {
                $this->template->setDisplayDataImmediate( "has_odt_templates", " " );
                $this->template->addOption( "odt_template", $print_form, 
                        $print_data['displayName'], $contentNode );
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
