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
   *  I2CE_CustomReport_Display
   * @package I2CE
   * @subpackage Core
   * @author Carl Leitner <litlfred@ibiblio.org>
   * @version 2.1
   * @access public
   */


abstract class I2CE_CustomReport_Display extends I2CE_Fuzzy{

    /**
     * an array indexed by   forms 
     * @var protected array $formObjs 
     */
    protected $formObjs;
    

    /**an array which tells us of if a report field  is mapped.  Keys are report fields of the form "$reportform+$field(+$aggregate)". values are booleans
     * @var protected array mappedFields
     */
    protected $mappedFields;

    /**
     * An array where keys are of the  "$reportform(+field(+$aggregate))" and the values are the corresponding "$form+$field" (that is the real form for the later)
     * @var protected array $maps
     */
    protected $formMaps;
    /**
     * An array where keys are of the  "$reportform(+field(+$aggregate))" and the values are the corresponding "$form+$field" (that is the real form for the later)
     * @var protected array $fieldMaps
     */
    protected $fieldMaps;
    
    /**an array of mapped values indexed by "$form+$field" (Note: $form is _not_ the report form, but the true form shortname)
     * @var protected array mappedValues
     */
    protected $mappedValues;
    
    /**
     * The shortname for the display.  E.g. 'Default'
     * @var protected string $display
     */
    protected $display;

    /**
     * @var protected I2CE_CustomReport
     */
    protected $reportObj;

    /**
     * @var array The list of limits for this display.
     */
    protected $limitDescText;

    /**
     * @var array The cache of module limits with description.
     */
    protected $limitModules;
    
    /**
     * The constuctor
     * @param I2CE_Page $page
     * @param string $view
     * @throws Excecption on error
     */
    public function __construct($page,$view) {
        $this->page = $page;
        $this->template= $this->page->getTemplate();
        $this->fieldMaps = array();
        $this->formMaps = array();
        $this->mappedFields = array();
        $this->mappedValues = array();
        $this->formObjs = array();
        $this->display = 'Default';
        if (preg_match('/^I2CE_CustomReport_Display_([a-zA-Z0-9_]+)$/',get_class($this),$matches)) {
            $this->display = $matches[1];
        }
        $config = I2CE::getConfig()->modules->CustomReports;
        $this->displayConfig = $config->displays->{$this->display};
        if (!isset($config->reportViews->$view)) {
            throw new Exception("You specified an invalid report view ($view)");
        }
        $this->view = $view;        
        $this->config = $config->reportViews->$view;
        if (!I2CE_CustomReport::reportExists($this->config->report)) {
            throw new Exception("View refers to nonexistent report {$this->config->report}");
        }
        $status = I2CE_CustomReport::getStatus( $this->config->report );
        if ( !$status || $status == 'not_generated' || $status == 'failed' ) {
            throw new Exception("Report for {$this->config->report} has not been generated");
        }
        $this->reportObj = new I2CE_CustomReport($this->config->report); //may throw an error. don't catch it

        $this->reportConfig = $config->reports->{$this->config->report};
        if (!$this->reportConfig instanceof I2CE_MagicDataNode) {
            throw new Exception("Report  {$this->config->report} is invalid");
        }
        if (!isset($this->reportConfig->relationship) || !$this->reportConfig->relationship) {
            throw new Exception("View refers to nonexistent relationship {$this->reportConfig->relationship}");
        }
        $this->relationshipConfig = $config->relationships->{$this->reportConfig->relationship};
        if (!$this->relationshipConfig instanceof I2CE_MagicDataNode) {
            throw new Exception("Report relationship {$this->reportConfig->relationship} is invalid");
        }
        $this->defaultOptions = $this->getDefaultOptions($this->page->request(),array() );        
        if (!is_array($this->defaultOptions)) {  
            throw new Exception("Cannot get display options");
        }
        if (!$this->canView()) {
            throw new Exception("Cannot look at view {$this->view}");
        }        

    }

    public function getRelationshipName() {
        return $this->relationshipConfig->getName();
    }

    public function getReportName() {
        return $this->reportConfig->getName();
    }
    public function getReportViewName() {
        return $this->config->getName();
    }
    
    /**
     * The options for this display
     */
    protected $defaultOptions;
    /**
     * the view we are displaying
     * @var protected string $view
     */
    protected $view;
    /**
     * The page we are displaying on
     * @var protected I2CE_Page $page
     */
    protected $page;
    /**
     * @var protected I2CE_TemplateMeister $template
     */
    protected $template;

    
    /**
     * The magic data node which holds the information about the display that is being used
     * @var protected I2CE_MagicDataNode $displayConfig
     */
    protected $displayConfig;
    
    /**
     * The magic data node which holds the information about this view
     * @var protected I2CE_MagicDataNode $config
     */
    protected $config;
    /**
     * The magic data node which holds the information about the report this view referes to
     * @var protected I2CE_MagicDataNode $reportConfig
     */
    protected $reportConfig;

    /**
     * The magic data node which holds the information about the relationship this view refers to
     * @var protected I2CE_MagicDataNode $relationshipConfig
     */
    protected $relationshipConfig;

    /**
     * Abstract method that each display is resposbile for implementing.  Checks to see
     * if it can display the given view.
     * @returns boolean
     */
    abstract protected function canView();



    /**
     * array  with keys the 'reportformfields' and values the data associated to it.  
     * these fields are those we assume have a numeric representation
     * @var protected array $numeric
     */
    protected $numeric;


    /**
     * Get the report results prefix for the DOM.
     * @return strng
     */
    protected function getReportPrefix() {
        return '';
    }
        
    /**
     * Find the numeric fields.  Returns it (and stores in in the variable $this->numeric)
     * @returns @array.  Keys are report for fields and values are the data associate defined in getDisplayedFields()
     */
    protected function findNumericFields( $disabled = false ) {
        if ($disabled == false && is_array($this->numeric)) {
            return $this->numeric;
        }
        $numeric = array();
        // get disabled fields. get none of the aggregate information
        $reportformfields = $this->getReportViewDisplayedFields( $disabled, array( '' ) );
        //now we need to see if we have at least one numeric data column and one text data column
        foreach ($reportformfields as $reportformfield=>$data) {
            list ($mergekey,$mergereport,$formfield) = array_pad(explode(':',$reportformfield,3),-3,'');
            list( $form, $field ) = array_pad(explode( '+', $formfield ),2,'');
            if (I2CE_MagicDataNode::checkKey($mergereport)) {
                $relationship = false;
                if (!I2CE::getConfig()->setIfIsSet($relationhsip,"/modules/CustomReports/reports/$mergereport/relationship")) {
                    continue;
                }
                $relationshipConfig = I2CE::getConfig()->traverse("/modules/CustomReports/relationships/$relationship");
            } else {
                $relationshipConfig = $this->relationshipConfig;
            }
            if ( strpos( $field, '+' ) !== false ) {
                continue; //badness we somehow have aggregate type info
            }
            if (!$form) { //this is a function field
                $is_numeric = false;
                $relationshipConfig->setIfIsSet($is_numeric,"reporting_functions/$field/is_numeric");
                if ($is_numeric) {
                    $numeric[$reportformfield] = $data; //$reportformfield == $field in this case
                }
                continue;
            }
            list($formObj,$fieldObj) = $this->getFormFieldObjects($reportformfield, $mergereport);
            if (!$formObj instanceof I2CE_Form) {
                continue;
            }
            if ($formObj->isNumeric($field)) {
                $numeric[$reportformfield] = $data;
                continue;
            }
        }
        if ( $disabled === false ) {
            $this->numeric = $numeric;
        }
        return $numeric;
    }


    /**
     * Validates an array of fields to sort on
     * @param array $sort_fields.  an array of string, the potential sort fields
     * @param array $valid_fields.  an array of string, the valid  fields 
     * @return array.  An validate array of string, the sorting fields
     */
    protected function validateSortFields($sort_fields,$valid_fields) {
        $validated = array();
        $new = array();
        if ($this->relationshipConfig instanceof I2CE_MagicDataNode) {
            $primary = $this->relationshipConfig->getName();
            
            foreach ($valid_fields as $formfield) {
                $parts =explode( '+', $formfield ); //function fields have blank $form_form
                if (!is_array($parts) || count($parts) == 0) {
                    continue;
                }
                if ($parts[0] == 'primary_form') {
                    $parts[0] = $primary;
                    $new[] = implode("+",$parts);
                }
            }
            $valid_fields = array_merge($valid_fields,$new);
        }
        foreach ($sort_fields as $field) {
            if (!is_string($field) || strlen($field) == 0) {
                continue;
            }
            if ($field == 'none') {
                continue;
            }
            $order = '';
            if ($field[0] == '-') {
                $order = '-';
                $field = substr($field,1);
            }
            if (!in_array($field,$valid_fields)) {
                continue;
            }
            $validated[] = $order. $field;
        }
        return $validated;
    }


    /**
     * Unset the limit_paginated option since doing it in the $_GET array won't work since no value would be set.
     * This is mainly for other objects working with reports instead of the default report display.
     */
    public function unsetPaging() {
        $this->defaultOptions['limit_paginated'] = false;
    }


    /**
     * @param boolean $check_restart defaults to true in which case if the results are paginated and the offeset is more than the number of results, we restart it setting the page to 1
     * @returns mixed false on failure on succes an array. at index 'results' and  buffered result object  at index 'num_results' the
     * number of results that would be found without the limit
     */
    protected function getResults($check_restart = true) {
        if (array_key_exists('limit_paginated',$this->defaultOptions) && $this->defaultOptions['limit_paginated']) {
            if (!array_key_exists('limit_page', $this->defaultOptions) || !(is_integer($this->defaultOptions['limit_page']) || ctype_digit($this->defaultOptions['limit_page'])) || ((int)$this->defaultOptions['limit_page']) < 1) {
                $this->defaultOptions['limit_page'] = 1;
            }
            if (!array_key_exists('limit_per_page',$this->defaultOptions) || ! (is_integer($this->defaultOptions['limit_per_page']) || ctype_digit($this->defaultOptions['limit_per_page'] ))|| ((int)$this->defaultOptions['limit_per_page']) < 1) {
                //we don't have a valid 'limit_per_page'
                $this->defaultOptions['limit_per_page'] = 100; //default to 100 
            }
            $this->defaultOptions['limit_page'] = (int) $this->defaultOptions['limit_page'];
            $this->defaultOptions['limit_per_page'] = (int) $this->defaultOptions['limit_per_page'];
            $limit_offset  =  ( $this->defaultOptions['limit_page'] -1) * ($this->defaultOptions['limit_per_page']);
            $limit_amount = $this->defaultOptions['limit_per_page'];
        }  else {      
            $limit_offset = $this->defaultOptions['limit_offset'];
            //$limit_amount = $this->defaultOptions['limit_per_page'];
            $limit_amount = false;
            if ($limit_offset !==false || $limit_offset !== 'false' )  {
                if ( !(is_integer($limit_offset) || ctype_digit($limit_offset)) || $limit_offset < 0) {
                    $limit_offset =0;
                }
            }
            if ($limit_amount === null || empty($limit_amount) || $limit_amount === 'false') {
                $limit_amount = false;
            }
            if ($limit_amount !== false) {
                if ( !(is_integer($limit_amount) || ctype_digit($limit_amount)) || $limit_amount < 1) {
                    $limit_amount = 100;
                }
            }
        }
        if ($this->defaultOptions['sort_order'] == 'none') {
            $sort_order = array();
        } else {
            $sort_order = explode(',',$this->defaultOptions['sort_order'] . '');        
            foreach ($sort_order as $i=>$field) {
                if ($field == 'none') {
                    unset($sort_order[$i]);
                }
            }
        }
        $fieldData = $this->getDisplayFieldsData();
        $sort_order= $this->validateSortFields($sort_order,array_keys($fieldData));
        $fields = array();
        $groups = array();
        $aggregates = array();
        $aggregate_fields = array();
        $has_total = false;

       
        $cols_in_report = array('' => I2CE_CustomReport::getColumnsInReportTable($this->config->report));        
        $referenced_form_ids = array();
        $no_display = array();
        foreach ($fieldData as $field=>$data) {
            if (!is_array($data)) {
                $no_display[] = $field;
            }
            if ($field == 'total') {
                $has_total = true;
                continue;
            }
            list($merge_form_form,$form_field, $aggregate) =  array_pad(explode( '+', $field ),3,''); //function fields have blank $form_form
            list($mergekey,$mergereport,$form_form) = array_pad(explode(':',$merge_form_form),-3,'');//pad to the left.  note there is no mergekey/mergereprot on primary table
            if ($mergereport == 'primary_table') {
                $mergereport ='';
            }
            if (!array_key_exists($mergereport,$cols_in_report)) {
                $cols_in_report[$mergereport] = I2CE_CustomReport::getColumnsInReportTable($mergereport);
            }
            if (($form_form != $field ? !in_array($form_form . '+' . $form_field,$cols_in_report[$mergereport]) : !in_array($form_form,$cols_in_report[$mergereport])) ) {
                I2CE::raiseError("Skipping field $field ($form_form)($form_field) b/c it is not in report $mergereport:\n\t" . implode(',',$cols_in_report[$mergereport]));
                continue;
            }

            if ($mergekey) {
                $merge = $mergekey.':' . $mergereport . ':';
            } else {
                $merge = '';
            }
            if ($form_form && $form_form != $field) {
                if ($merge) {
                    $referenced_form_ids[]= "`$mergekey:$mergereport`.`$form_form+id` AS `$merge$form_form+id`";
                } else {                
                    $referenced_form_ids[]= "`primary_table`.`$form_form+id` AS `$form_form+id`";
                }
            }

            if ( $form_form == $field ) {
                $fields[] = "$merge$field";
            } else {
                $fields[] = "$merge$form_form+$form_field";
            }
            if ($form_form && $form_form != $field )   {
                //it is not a function field.  check if there is a link
                if (is_array($data) && $data['link'] && $data['link_append']) {
                    if ($merge) {
                        $groups[$data['link_append']] =  "`$mergekey:$mergereport`.`{$data['link_append']}`  AS `$merge{$data['link_append']}`";
                    } else {
                        $groups[$data['link_append']] = '`'  .  $data['link_append']  . '`';
                    }
                }
            }
            if ( $form_form == $field ) {
                $reportfield = $field;
            } else {
                $reportfield = "$form_form+$form_field";
            }
            switch ($aggregate) {
            case 'sum':
                if ($merge) {
                    $aggregates[$reportfield] = " SUM(`$mergekey:$mergereport`.`$reportfield`) AS `$field`";
                    $aggregate_fields[$reportfield] = "$field";
                } else {
                    $aggregates[$reportfield] = " SUM(`$reportfield`) AS `$field`";
                    $aggregate_fields[$reportfield] = "$field";
                }
                break;
            case 'maximum':
                if ($merge) {
                    $aggregates[$reportfield] = " MAX(`$mergekey:$mergereport`.`$reportfield`) AS `$field`";
                    $aggregate_fields[$reportfield] = "$field";
                } else {
                    $aggregates[$reportfield] = " MAX(`$reportfield`) AS `$field`";
                    $aggregate_fields[$reportfield] = "$field";
                }
                break;
            case 'minmimum':
                if ($merge) {
                    $aggregates[$reportfield] = " MIN(`$mergekey:$mergereport`.`$reportfield`) AS `$field`";
                    $aggregate_fields[$reportfield] = "$field";
                } else {            
                    $aggregates[$reportfield] = " MIN(`$reportfield`) AS `$field`";
                    $aggregate_fields[$reportfield] = "$field";
                }
                break;
            case 'average':
                if ($merge) {
                    $aggregates[$reportfield] = " AVG(`$mergekey:$mergereport`.`$reportfield`) AS `$field`";
                    $aggregate_fields[$reportfield] = "$field";
                } else {            
                    $aggregates[$reportfield] = " AVG(`$reportfield`) AS `$field`";
                    $aggregate_fields[$reportfield] = "$field";
                }
                break;
            case 'count':
                if ($merge) {
                    $aggregates[$reportfield] = " COUNT(`$mergekey:$mergereport`.`$reportfield`) AS `$field`";
                    $aggregate_fields[$reportfield] = "$field";
                } else {            

                    $aggregates[$reportfield] = " COUNT(`$reportfield`) AS `$field`";
                    $aggregate_fields[$reportfield] = "$field";
                }
                break;
            case 'count_distinct':
                if ($merge) {
                    $aggregates[$reportfield] = " COUNT( DISTINCT `$mergekey:$mergereport`.`$reportfield`) AS `$field`";
                    $aggregate_fields[$reportfield] = "$field";
                } else {
                    $aggregates[$reportfield] = " COUNT(DISTINCT `$reportfield`) AS `$field`";
                    $aggregate_fields[$reportfield] = "$field";
                }
                break;
            default: //'none'
                if ($merge) {
                    $groups[$reportfield] = "`$mergekey:$mergereport`.`$reportfield` AS `$field`";
                } else {
                    $groups[$reportfield] = "`primary_table`.`$reportfield` AS `$reportfield`";
                }
            }
        }
        if (count($fields) == 0 && !$has_total) {
            I2CE::raiseError("No fields to get results from");
            return false;
        }
        $sorts = array();
        foreach ($sort_order as $formfield) {
            if (strlen($formfield) == 0) {
                continue;
            }
            if ($formfield[0] == '-') {
                $sort_field = substr($formfield,1);
                $sort_postfix = ' DESC';
            } else {
                $sort_field = $formfield;
                $sort_postfix = '';
            }
            if ($sort_field != 'total' && !in_array($sort_field,$fields)) { //make sure we don't have junk
                continue;
            }
            if (array_key_exists($sort_field,$aggregate_fields)) {
                $sort_field = $aggregate_fields[$sort_field];
            }
            $sorts[$sort_field] = '`' . $sort_field . '`' . $sort_postfix;
        }
        $order_by = '';
        if (count($sorts) > 0) {
            $lsorts = array();
            foreach ($sorts as $s) {
                $lsorts[] = strtolower($s);
            }
            $order_by = ' ORDER BY ' . implode(',',$lsorts);
        }
        $group_by = '';
        $group_bys = array();
        if (count($aggregates) == 0) { //no aggregate fields
            if ($has_total) {
                $group_bys = $groups;
                foreach( $group_bys as $field => $info ) {
                    if ( in_array( $field, $no_display ) ) {
                        unset( $group_bys[$field] );
                    }
                }
                $select_fields = $group_bys;
                $select_fields[] ='COUNT(*) AS `total`';
            }else{
                if (count($groups) == 0) {
                    I2CE::raiseError("Report view {$this->view} has no viewable fields");
                    return false;
                }
                $select_fields = $groups;
            }
        } else {            
            $select_fields = $aggregates;
            $group_bys = $groups;
            $select_fields = array_merge($aggregates,$groups);
            if ($has_total) {
                $group_bys = array_diff($select_fields,$no_display);
                $select_fields[] ='COUNT(*) AS `total`';                            
            }
            foreach (array_keys($group_bys) as $gb) {
                if (in_array($gb,$no_display)) {
                    unset($group_bys[$gb]);
                }
            }
        }
        $group_bys = array_map( function($n) { return ( ($i = stripos( $n, " as" ) ) === false ? $n : substr( $n, 0, $i ) ); }, $group_bys);
        if (count($group_bys) > 0) {
            $lgroup_bys = array();
            foreach ($group_bys as $g) {
                $lgroup_bys[] = strtolower($g);
            }
            $group_by = ' GROUP BY ' .  implode( ',', $lgroup_bys);
        }            
        if ($limit_offset !== false && $limit_amount !== false) {
            $limit = " LIMIT $limit_amount OFFSET $limit_offset ";
            $this->row_start = $limit_offset;
            $this->row_amount = $limit_offset;
        } else {
            $limit  = '';
            $this->row_start = false;
            $this->row_amount = false;
        }
        if (array_key_exists('nested_limits',$this->defaultOptions)) {
            $where = $this->processWhere($this->defaultOptions['nested_limits']);
        } else {
            $where = '';
        }
        $merge_reports = $this->getMergedReportJoins();
        $select_fields = array_unique(array_merge($referenced_form_ids,$select_fields));
        $lselect_fields = array();
        foreach ($select_fields as $s) {
            $lselect_fields[] = strtolower($s);
        }
        $qry = "SELECT SQL_CALC_FOUND_ROWS " . implode(',',$lselect_fields) . " FROM " . 
            I2CE_CustomReport::getCachedTableName($this->config->report) . ' AS primary_table '.
            implode( " " ,  $merge_reports) .
            ' ' . $where . ' ' . $group_by . ' ' . $order_by . ' ' . $limit;
        try {
            I2CE::raiseError("Doing $qry");
            $db = I2CE::PDO();
            $res = $db->query($qry);
            I2CE::raiseMessage($qry);
        } catch ( PDOException $e ) {
            I2CE::pdoError($e,"Could not get results");
            return false;
        }
        try {
            $num_rows = I2CE_PDO::getRow( "SELECT FOUND_ROWS() AS num_rows" );
            $num_rows = (int) $num_rows->num_rows;
        } catch ( PDOException $e ) {
            I2CE::pdoError($e,"Could not get total number of results");
            $num_rows = false;
        }
        if ($check_restart && $num_rows > 0 && $num_rows < $limit_offset) {
            //set the limit offset to be 0 and restart the query..
            if (array_key_exists('limit_paginated',$this->defaultOptions) && $this->defaultOptions['limit_paginated']) {
                if (array_key_exists('limit_page', $this->defaultOptions) 
                    && (is_integer($this->defaultOptions['limit_page']) || ctype_digit($this->defaultOptions['limit_page']))
                    &&  ((int)$this->defaultOptions['limit_page']) > 1) {
                    $this->defaultOptions['limit_page'] = 1 ;
                    return $this->getResults(false);
                }
            }
            
        }
        return array('results'=>$res,'num_results'=>$num_rows, 'has_total' => $has_total );        
    }

    protected $seen_merges =array();
    protected function getMergedReportJoins() {
        $joins = array();
        $this->seen_merges =array($this->config->report);
        $this->_getMergedReportData($this->config,$joins);
        return $joins;
    }
    
    protected function _getMergedReportData($config,&$joins,$merge_field = false, $merge_ref = 'primary_table') {
        if (!$config instanceof I2CE_MagicDataNode) {
            return ;
        }
        if (!$config->is_parent('fields')) {
            return;
        }

        if ($merge_field) {
            $enabled = false;
            $config->setIfIsSet($enabled,'enabled');
            if (!$enabled) {
                return;
            }
            //PUT IN LOGIC TO GET THE REPORT MERGE FROM THE formfield and KEY
            $mergekey = $config->getName();

            list($mergeform,$mergefield) = array_pad(explode('+',$merge_field,2) ,2,'');
            if (!$mergeform || !$mergefield) {
                I2CE::raiseError("Bad form field");
                return;
            }
            $mergereport = false;
            $mergeconfig =I2CE::getConfig()->traverse("/modules/CustomReports/reports/" .$this->config->report . "/reporting_forms/$mergeform/fields/$mergefield/merges/$mergekey");
            if (!$mergeconfig instanceof I2CE_MagicDataNode) {
                I2CE::raiseError("Bad merge /modules/CustomReports/reports/" .$this->config->report . "/reporting_forms/$mergeform/fields/$mergefield/merges/$mergekey");
                return;
            }
            $mergeconfig->setIfIsSet($mergereport,"report");
            if (!I2CE_MagicDataNode::checkKey($mergereport)) {
                I2CE::raiseError("Bad merge report");
                return;
            }
            if (in_array($mergereport,$this->seen_merges)) {
                //don't allow to merge in a report more than once
                return;
            }
            $this->seen_merges[] = $mergereport;


            $child_merge_ref = "$mergekey:$mergereport";
            $mergeconfig->setIfIsSet($child_mergeform,"reportForm");
            if (!I2CE_MagicDataNode::checkKey($child_mergeform)) {
                I2CE::raiseError("Bad merge report form");
                 return;
            }
            $join_style =false;
            $mergeconfig->setIfIsSet($join_style,"join_style");
            if (!$join_style) {
                I2CE::raiseError("Bad join style");
                return;
            }
            $conditions = array();
            foreach ($config->fields as $formFieldName=>$formFieldConfig) {            
                if (!$formFieldConfig instanceof I2CE_MagicDataNode) {
                    continue;
                }            
                $additional = false;
                $add_reportfield = false;
                if ($formFieldConfig->setIfIsSet($additional,'merge_additional')) {
                    I2CE::raiseError("Merge  Addiitonal at " . $formFieldConfig->getPath() . ' on ' . $additional);
                    // something like  /$report_view/fields/primary_form+job_cadre  or /$report_views
                    $additional = explode('/',ltrim($additional,'/'));
                    if (count($additional) == 3) {
                        //it is a report view
                        $add_reportfield = array_pop($additional);
                        //array_pop($additional); //fields
                        //array_pop($additional); //reportview
                        $add_merge ='primary_table';
                    } else if (count($additional) > 3) {
                        $add_reportfield = array_pop($additional); 
                        array_pop($additional);
                        $add_merge = array_pop($additional);
                    }
                }
                if ($add_reportfield) {
                    //$conditions[] =  '`' . $child_merge_ref . '`.`' . $mergeform . '+'. $mergefield . '` = `' . $add_merge .'`.`'. $add_reportfield .'`';
                    $conditions[] =  '`' . $child_merge_ref . '`.`' . $formFieldName . '` = `' . $add_merge .'`.`'. $add_reportfield .'`';
                }
            }
            $method = 'mergeOn_' . $join_style;
            if ( ! ($conditions = $this->$method($merge_ref, $mergeform,$mergefield, $child_merge_ref, $child_mergeform,$mergeconfig->getAsArray('join_data'),$conditions))) {
                I2CE::raiseError("bad join condition");
                return;
            }
            $merge_ref = $child_merge_ref;
            $show_blanks = 1;
            $config->setIfIsSet($show_blanks,"show_blanks");
            if ($show_blanks) {
                $join = ' RIGHT JOIN ';
            } else {
                $join = '  JOIN ';
            }
            $joins[] =  $join . I2CE_CustomReport::getCachedTableName($mergereport) . ' AS `' . $merge_ref . '` ON ((' . implode(') AND  (' , $conditions) . ')) ';
            
        }
        foreach ($config->fields as $formFieldName=>$formFieldConfig) {            
            if (!$formFieldConfig instanceof I2CE_MagicDataNode) {
                continue;
            }            
            if (!$formFieldConfig->is_parent("merges")) {
                continue;
            }
            foreach ($formFieldConfig->merges as $mergekey=>$mergeConfig) {
                $this->_getMergedReportData($mergeConfig,$joins, $formFieldName, $merge_ref);
            }
        }
    }



    /**
     * Generate SQL join statement for joining on the named parent form's id with the named child form on a given field
     * @param string $childFormName the name of the child form in the relationship
     * @param array $joinData The array containg the join data
     * @return mixed. On success, a string which is the SQL join statement.  On failure, false
     */     
    protected function mergeOn_parent_field( $parent_merge_ref,$parent_form, $parent_field ,$merge_ref, $child_form, $joinData,  $conditions ) {
        if  ((!is_array($joinData)) || 
             (!array_key_exists('field', $joinData)) || 
             (!is_string($joinData['field'])) ||
             (strlen($joinData['field']) == 0) ){
            I2CE::raiseError("Join child field not specified for $parent_form/$child_form:" . print_r($joinData,true));
            return false;
        }
        $conditions[] =  "  `$parent_merge_ref`.`{$parent_form}+{$parent_field}` = `$merge_ref`.`$child_form+id` ";
        return $conditions;
    }


    /**
     * Generate SQL join statement for joining on the named parent form's id with the named child form on a given field
     * @param string $childFormName the name of the child form in the relationship
     * @param array $joinData The array containg the join data
     * @return mixed. On success, a string which is the SQL join statement.  On failure, false
     */     
    protected function mergeOn_child_field( $parent_merge_ref,$parent_form, $parent_field ,$merge_ref, $child_form, $joinData ,  $conditions) {
        if  ((!is_array($joinData)) || 
             (!array_key_exists('field', $joinData)) || 
             (!is_string($joinData['field'])) ||
             (strlen($joinData['field']) == 0) ){
            I2CE::raiseError("Join child field not specified for $parent_form/$child_form:" . print_r($joinData,true));
            return false;
        }
        $conditions[] = "`$parent_merge_ref`.`{$parent_form}+id` = `$merge_ref`.`$child_form+{$joinData['field']}`";
        return $conditions;
    }

    /**
     * Generate SQL join statement for joining on the named parent form's id with the named child form on a given field
     * @param string $childFormName the name of the child form in the relationship
     * @param array $joinData The array containg the join data
     * @return mixed. On success, a string which is the SQL join statement.  On failure, false
     */     
    protected function mergeOn_child($parent_merge_ref,$parent_form, $parent_field ,$merge_ref, $child_form, $joinData ,  $conditions ) {
        $conditions[] =  " `$parent_merge_ref`.`{$parent_form}+id` = `$merge_ref`.`$child_form+parent`";
        return $conditions;
    }

    /**
     * Generate SQL join statement for joining on the named parent form's id with the named child form on a given field
     * @param string $childFormName the name of the child form in the relationship
     * @param array $joinData The array containg the join data
     * @return mixed. On success, a string which is the SQL join statement.  On failure, false
     */     
    protected function mergeOn_parent( $parent_merge_ref,$parent_form, $parent_field ,$merge_ref, $child_form, $joinData,  &$conditions ) {
        $conditions[] = "  `$parent_merge_ref`.`{$parent_form}+parent` = `$merge_ref`.`$child_form+id`";
        return $conditions;
    }

    /**
     * The starting row (if any) for limiting the results. If not used, it is false
     * @var procted mixed $row_start
     */
    protected $row_start;
    /**
     * The number of rows (if any) for limiting the results. If not used, it is false
     * @var procted mixed $row_amount
     */
    protected $row_amount;

    /**
     * Maps any mapped  fields in a results.
     * @param array $result A an array with inicies of the form "$reportform+$field(+$aggregate)"  with unmapped value
     * @return array the mapped results
     */
    protected function mapResults($result) {
        $ret = array();
        foreach ($result as $reportformfield => $value) {
            $ret[$reportformfield] = $this->getMappedValue($reportformfield,$value);
        }       
        return $ret;
    }
    

    /**
     * Gets the mapped value for a report form field.
     * @param string $reportformfield or a report form field or a form field of the form "$reportform+$field" or "$reportform+$field+$aggreate" or "$form+field"
     * @param value
     * @parmam boolean $isReportForm Defaults to true.  If true we a sending a report form field
     * If false, we are working with a form field.
     * @returns string the origina value of  on failure, the mapped value on success
     */
    protected function getMappedValue($reportformfield,$value,$isReportForm=true) {
        if ( $value === null ) {
            return "";
        }
        $this->setupMappedValues($reportformfield,$isReportForm); //makes sure the map is setup
        if ($isReportForm === true) {
            $isReportForm = $this->config->report;
        } 
        list ($mergekey,$mergereport,$reportformfield) = array_pad(explode(':',$reportformfield,3),-3,'');
        if ($mergereport) {
            $isReportForm = $mergereport;
        }        
        if (is_string($isReportForm)) {
            $this->mapReportFormField($reportformfield, $isReportForm);
            $formfield = $this->fieldMaps[$isReportForm][$reportformfield];
        } else {
            $formfield = $reportformfield;
        }        
        list($a_form,$a_field,$a_aggregate) = array_pad(explode('+',$reportformfield),3,'');
        if( $a_aggregate ) {
            // If it's an aggregate field then don't do any mapped lookups.
            return $value;
        }
        if ( !$formfield ) {
            list($form,$field,$aggregate) = array_pad(explode('+',$reportformfield),3,'');
            if ( $form == ""   && is_string($isReportForm) ) {
                $relationship = false;
                if (I2CE::getConfig()->setIfIsSet($relationship,"/modules/CustomReports/reports/$isReportForm/relationship")) {
                    $relationshipConfig = I2CE::getConfig()->traverse("/modules/CustomReports/relationships/$relationship");

                    // This may be a function
                    $formfield_type = null;
                    if ( $relationshipConfig->setIfIsSet( $formfield_type,   "reporting_functions/$field/formfield" ) ) {
                        $formfield_class = null;
                        if ( I2CE::getConfig()->setIfIsSet( $formfield_class,    "/modules/forms/FORMFIELD/$formfield_type" ) ) {
                            if ( is_subclass_of( $formfield_class, "I2CE_FormField_MAPPED" ) ) {
                                $options = array( "meta" => array() );
                                if ( $relationshipConfig->setIfIsSet( $select_forms, "reporting_functions/$field/select_forms" ) && $select_forms ) {
                                    $options["meta"]["form"] = 
                                        explode( ",", $select_forms );
                                }
                                if ( $relationshipConfig->setIfIsSet( $linked_fields, "reporting_functions/$field/link_fields" ) && $linked_fields ) {
                                    $options["meta"]["display"] =
                                        array( "default" => 
                                               array( "fields" => 
                                                      $linked_fields )
                                            );
                                }
    
                                $ff_obj = new $formfield_class( $field, $options );
                                $ff_obj->setFromDB( $value );
                                $ff_value = $ff_obj->getDisplayValue();
                                return $ff_value;
                            }
                        }
                    }
                }
            }
            return $value;
        }
        if (is_string($formfield) && array_key_exists($formfield,$this->mappedValues)) {
            $map_value = false;
            if ( array_key_exists( $value, $this->mappedValues[$formfield] ) ) {
                $map_value = $this->mappedValues[$formfield][$value];
            }
            /*
            foreach ($this->mappedValues[$formfield] as $data) {
                if (array_key_exists('value',$data) && $data['value'] == $value) {
                    $map_value = $data['display'];
                    break;
                }
            }
            */
            if ($map_value !== false) {
                return $map_value;
            }
        }
        list($form,$field) = array_pad(explode('+',$formfield),2,'');
        if (!array_key_exists($form,$this->formObjs) || !$this->formObjs[$form] instanceof I2CE_Form) {
            return $value;
        }
        $fieldObj = $this->formObjs[$form]->getField($field);
        if (!$fieldObj instanceof I2CE_FormField) {
            return $value;
        }
        $fieldObj->setFromDB($value);
        return $fieldObj->getDisplayValue();
    }
    /**
     * Stores the array of mapped value for a formfield in  {@var $formfield}
     * @parmam boolean $isReportForm Defaults to true.  If true we a sending a report form field
     * If false, we are working with a form field.
     * @param string $reportformfield
     */
    protected function setupMappedValues($reportformfield,$isReportForm = true) {
        if ($isReportForm === true) {
            $isReportForm = $this->config->report;
        } 
        list ($mergekey,$mergereport,$reportformfield) = array_pad(explode(':',$reportformfield,3),-3,'');
        if ($mergereport) {
            $isReportForm = $mergereport;
        }        
        if (is_string($isReportForm)) {
            $this->mapReportFormField($reportformfield, $isReportForm);
            $formfield = $this->fieldMaps[$isReportForm][$reportformfield];
            if(!$this->isMapped($reportformfield, $isReportForm)) {
                return;
            }
        } else {
            $formfield = $reportformfield;
        }
        if ($formfield === false) {
            return;
        }
        if (array_key_exists($formfield,$this->mappedValues)) {
            //we have already gotten the mapped values
            return;
        }
        //we need to get the mapped values
        list($formObj,$fieldObj) = $this->getFormFieldObjects($reportformfield,$isReportForm);
        if (!$formObj instanceof I2CE_Form) { //we are dealing with a funcion field which is not mapped so return
            return; 
        }
        $mappedValues = array();
        if ($fieldObj instanceof I2CE_FormField) {
            $mappedValues = $fieldObj->getMapOptions();
        }
        if (!is_array($mappedValues)) { //just to be sure
            $mappedValues = array();
        }
        //$this->mappedValues[$formfield] = $mappedValues;
        $this->mappedValues[$formfield] = array();
        if ( count($mappedValues) > 100 ) {
            I2CE::raiseMessage("A large amount of values are returned from the setupMappedValues function for CustomReport display for $reportformfield.  This should be modified to display a text value instead of the mapped value by joining the appropriate form in the relationship.");
        }
        foreach( $mappedValues as $record ) {
            $this->mappedValues[$formfield][$record['value']] = $record['display'];
        }
    }


    /**
     * Checks to see if a form field is mapped
     * @param string $reportformfield of the form "$reportform+$fiedld(+$aggregate)"
     */
    protected function isMapped($reportformfield, $report = null) {
        if ($report === null) {
            $report = $this->config->report;
        }
        list ($mergekey,$mergereport,$reportformfield) = array_pad(explode(':',$reportformfield,3),-3,'');
        if ($mergereport) {
            $report = $mergereport;
        }        
        if (!is_string($report)) {
            return false;
        }
        if (!array_key_exists ($report,$this->mappedFields)) {
            $this->mappedFields[$report] = array();
        }
        if (!array_key_exists ($reportformfield,$this->mappedFields[$report])) {
            //we have not check this form field yet.
            list($formObj,$fieldObj) = $this->getFormFieldObjects($reportformfield, $report);
            $this->mappedFields[$report][$reportformfield] =  (($formObj instanceof I2CE_Form) && ($fieldObj instanceof I2CE_FormField_MAPPED));
        }
        return $this->mappedFields[$report][$reportformfield];
    }


    protected $relationships = array();

    /**
     * Sets the maps
     * @param string $reportformfield
     */
    protected function mapReportFormField($reportformfield, $report = null) {
        if ($report === null) {
            $report = $this->config->report;
        }
        list ($mergekey,$mergereport,$reportformfield) = array_pad(explode(':',$reportformfield,3),-3,'');
        if ($mergereport) {
            $report = $mergereport;
        }        
        if (!is_string($report)) {
            return;
        }
        if (!array_key_exists($report,$this->formMaps)) {
            $this->formMaps[$report] = array();
        }
        if (array_key_exists($reportformfield,$this->formMaps[$report])) {
            return;
        }
        list($reportform,$field,$aggregate) = array_pad(explode('+',$reportformfield),3,'');
        $form = false;
        if (strlen($reportform) == 0) { //this is a function field
            $reportform = false;
        } elseif ( !$field && $reportform == "total" ) {
            $this->fieldMaps[$report][$reportform] = false;
            return;
        } else {
            $relationship = false;
            if (I2CE::getConfig()->setIfIsSet($relationship,"/modules/CustomReports/reports/$report/relationship") && I2CE_MagicDataNode::checkKey($relationship)) {
                if (!array_key_exists($relationship,$this->relationships)) {
                    try {
                        $this->relationships[$relationship] = new I2CE_FormRelationship($relationship);
                    }   catch(Exception $e) {
                        I2CE::raiseError("Invalid relationship $relationship");
                    }
                }
                if ($this->relationships[$relationship] instanceof I2CE_FormRelationship) {
                    $form  = $this->relationships[$relationship]->getForm($reportform);
                }
            }
            if (!is_string($form) || strlen($form)== 0) {
                $form = false;
            }
        }
        $this->formMaps[$report][$reportformfield] = $form;
        $this->formMaps[$report]["$reportform+$field"] = $form;
        $this->formMaps[$report][$reportform] = $form;
        if ($form == false) {
            $this->fieldMaps[$report][$reportformfield] = false;
            $this->fieldMaps[$report]["$reportform+field"] = false;
            $this->fieldMaps[$report][$reportform] = false;
        } else {
            $this->fieldMaps[$report][$reportformfield] = "$form+$field";
            $this->fieldMaps[$report]["$reportform+field"] = "$form+$field";
            $this->fieldMaps[$report][$reportform] = "$form+$field";
        }
    }
    

    /**
     * Get the form and field objects associated to a formfield string.  
     * @param string $reportformfield of the re[prt form "$form" "$form+$field"  or "$form+$field+$aggregate"
     * @parmam string $isReportForm Defaults to true.  If true we a sending a report form field
     * If false, we are working with a form field.
     * @returns array.  Index 0 is an I2CE_Form (on success, false on failure), Index 1 is an I2CE_FormField on succces, false on failure
     */
    protected function getFormFieldObjects($reportformfield,$isReportForm = true) {
        $ret = array(0=>false,1=>false);
        if ($isReportForm === true) {
            $isReportForm = $this->config->report;
        }
        list ($mergekey,$mergereport,$reportformfield) = array_pad(explode(':',$reportformfield,3),-3,'');
        if ($mergereport) {
            $isReportForm = $mergereport;
        }        
        if (is_string($isReportForm)) {
            $this->mapReportFormField($reportformfield, $isReportForm);
            if (!is_string($this->fieldMaps[$isReportForm][$reportformfield])) {
                return $ret;
            }
            $formfield = $this->fieldMaps[$isReportForm][$reportformfield];
        } else {
            $formfield = $reportformfield;
        }
        list($form,$field,$aggregate) = array_pad(explode('+',$formfield),3,'');
        if (!$form) { //it is a function field so there are no form/field objects associated to it/
            return $ret;
        }        
        if (!array_key_exists($form,$this->formObjs)) {
            $factory  = I2CE_FormFactory::instance();
            $this->formObjs[$form] = $factory->createContainer($form);
            if (!$this->formObjs[$form] instanceof I2CE_Form) {
                $this->formObjs[$form] = false;
            }
        }
        $ret[0] = $this->formObjs[$form];
        if (!$ret[0] instanceof I2CE_Form) {
            return $ret;
        }
        $ret[1] = $ret[0]->getField($field);
        if (!$ret[1] instanceof I2CE_FormField) {
            $ret[1] = false;
        }
        return $ret;
    }


    /**
     * Setup and return the module limits details.
     * @param string $report The report to get limits for.
     * @param boolean $display If set then return the limit description instead
     *                         of the limit details.
     * @return array
     */
    protected function getModuleLimits( $report=null, $display=false ) {
        if ($report == null) {
            $report = $this->config->report;
        }
        if (!is_string($report)) {
            return array();
        }
        if ( !is_array( $this->limitModules ) ) {
            $this->limitModules = array();
        }
        if ( !array_key_exists( $report, $this->limitModules ) ) {
            $this->limitModules[$report] = array( 'where' => array(), 
                    'display' => array() );
            $report_forms = I2CE::getConfig()->getAsArray( "/modules/CustomReports/reports/$report/reporting_forms" );
            foreach( $report_forms as $form => $form_data ) {
                if ( !array_key_exists( 'fields', $form_data ) ) {
                    continue;
                }
                foreach( $form_data['fields'] as $field => $field_data ) {
                    if ( $field != 'id' && array_key_exists( 'enabled', $field_data ) && !$field_data['enabled'] ) {
    
                        continue;
                    }
                    if ( array_key_exists( 'module_limits', $field_data ) 
                            && is_array( $field_data['module_limits'] ) ) {
                        foreach( $field_data['module_limits'] as $module => $module_data ) {
                            if ( array_key_exists( 'link_field', $module_data ) 
                                    && is_string($module_data['link_field']) 
                                    && strlen( $module_data['link_field'] ) > 0 ) {
                                $moduleObj = I2CE_ModuleFactory::instance()->getClass( $module );
                                if ( $moduleObj->_hasMethod( "getLimitsByForm" ) ) {
                                    $allowed = $moduleObj->getLimitsByForm( $module_data['link_field'] );
                                    if ( $allowed === true ) {
                                        continue;
                                    } elseif( is_array( $allowed ) ) {
                                        if ( count($allowed) > 0 ) {
                                            $this->limitModules[$report]['where'][] = "`$form+$field` IN ( '" 
                                                . implode("','", $allowed ) . "' )";
                                            $descArr = array();
                                            list ( $formObj, $fieldObj ) = $this->getFormFieldObjects("$form+$field", $report);                    
                                            if ( $field == 'id' ) {
                                                foreach( $allowed as $val ) {
                                                    $formObj->setId( $val );
                                                    $formObj->populate();
                                                    $descArr[] = $formObj->name();
                                                }
                                            } else {
                                                foreach( $allowed as $val ) {
                                                    $fieldObj->setFromDB( $val );
                                                    $descArr[] = $fieldObj->getDisplayValue();
                                                }
                                            }
                                            if ( count($descArr) > 0 ) {
                                                $this->limitModules[$report]['display'][] = $field_data['header'] . ": [" . implode( ', ', $descArr ) . "]";
                                            }
                                        //} else {
                                            //$or_wheres[] = "`$form+$field` IS NULL";
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        if ( $display ) {
            return $this->limitModules[$report]['display'];
        } else {
            return $this->limitModules[$report]['where'];
        }
    }


    /**
     * Process the fields  to make limits based on their limiting values
     * @param array $limits an array defining limiting values for particular fields
     * @returns string
     */
    protected function processWhere($limitValues, $report = null) {        
        if ($report == null) {
            $report = $this->config->report;
        }
        if (!is_string($report)) {
            return '';
        }
        $wheres = array();
        if (is_array($limitValues) && count($limitValues) > 0) {
            $factory = I2CE_FormFactory::instance();
            $t_excludes =   I2CE::getConfig()->modules->CustomReports->limit_excludes->displayed->getAsArray();
            $excludes = array();
            foreach ($t_excludes as $exclude) {
                $excludes[$exclude] = true;
            }
            foreach ($limitValues as $reportformfield=>$limit_styles) {
                if (!is_array($limit_styles)) {                
                    continue;
                }
                $limit_styles = array_diff_key($limit_styles,$excludes);
                if (count($limit_styles) == 0) {
                    continue;
                }            
                list($reportform,$field) = array_pad(explode('+',$reportformfield),2,'');
                if ( $this->getSwissReportInternal($reportformfield,$report) !== null ) {

                    $where = trim($this->processWhereByInternal($reportformfield, $limit_styles, $report) );
                } elseif (!$reportform) { //function fields have no limits.
                    $where = trim($this->processWhereByFunction($field,$limit_styles, $report));
                } else {
                    list($formObj,$fieldObj)= $this->getFormFieldObjects($reportformfield,$report);
                    if (!$formObj instanceof I2CE_Form) {
                        continue;
                    }            
                    $where = trim($this->processWhereByField($reportform,$field,$formObj,$limit_styles,$report));
                }
                if (strlen($where) >0) {
                    $wheres[] = '(' . $where . ')';
                }
            }
        }

        $or_wheres = $this->getModuleLimits( $report );

        $where = '';
        if (count($wheres) > 0) {
            $where = ' WHERE (' . implode(' AND ' , $wheres) . ') ';
        }
        if ( count( $or_wheres ) > 0 ) {
            if ( $where == '' ) {
                $where = ' WHERE ';
            } else {
                $where .= ' AND ';
            }
            $where .= '( ' . implode( ' OR ', $or_wheres ) . ' )';
        }
        return $where;
    }

    
    /**
     * @param string $form  the (report) form  
     * @param string $field the field
     * param I2CE_Form $formObj the instantiation of the form that the report form references 
     * @param array $limitStyles the limit values for this formfield indexed by limit type
     */
    protected function processWhereByField($form,$field,$formObj,$limitStyles, $report = null) {        
        if ($report == null) {
            $report = $this->config->report;
        }
        if (!is_string($report)) {
            return;
        }
        if (!is_array($limitStyles) || count($limitStyles) == 0 ) {
            return '';
        }
        $config = I2CE::getConfig()->traverse("/modules/CustomReports/reports/$report/reporting_forms/$form/fields/$field/limits",false,false);
        if (!$config instanceof I2CE_MagicDataNode) {
            return '';
        }
        $wheres = array();
        $callback = function($f1,$f2) use($form,$field) { return "`$form+$field`"; }; //weird, but i do want $form and $field substituted now and now later.
        foreach ($limitStyles  as $limitStyle=>$values) {
            if (!isset($config->$limitStyle)) {
                continue;
            }
            if (!isset($config->$limitStyle->enabled) || !$config->$limitStyle->enabled) {
                continue;
            }
            if (!is_array($values) || count($values)==0) {
                continue;
            }
            $where =  trim($formObj->generateLimit(array('field'=>$field,'style'=>$limitStyle,'data'=>$values), $callback));
            if (strlen($where) >0) {
                $wheres[] = '(' . $where . ')';
            }
        }
        $where = '';
        if (count($wheres) >0) {
            $where = '(' . implode ( ' AND ' , $wheres) . ')';
        }
        return $where;        
    }


    
    /**
     * @param string $function
     * @param array $limitStyles the limit values for this formfield indexed by limit type
     */
    protected function processWhereByFunction($function,$limitStyles,$report = null) {        
        if ($report == null) {
            $report = $this->config->report;
        }
        if (!is_string($report)) {
            return '';
        }
        if (!is_array($limitStyles) || count($limitStyles) == 0 ) {
            return '';
        }
        $config = I2CE::getConfig()->traverse("/modules/CustomReports/reports/$report/reporting_functions/$function/limits",false,false); 
        if (!$config instanceof I2CE_MagicDataNode) {
            return '';
        }
        $wheres = array();
        foreach ($limitStyles  as $limitStyle=>$values) {
            if (!isset($config->$limitStyle)) {
                continue;
            }
            if (!isset($config->$limitStyle->enabled) || !$config->$limitStyle->enabled) {
                continue;
            }
            if (!is_array($values) || count($values)==0) {
                continue;
            }
            $repFunction = $this->getSwissReportFunction($function,$report); //get rid of the leading +
            if (!$repFunction instanceof I2CE_Swiss_CustomReports_Report_ReportingFunction) {
                continue;
            }
            $fieldObj = $repFunction->getFieldObj();
            if (!$fieldObj instanceof I2CE_FormField) {
                continue;
            }
            $where =  trim($fieldObj->generateLimit(array('style'=>$limitStyle,'data'=>$values), '`+' . $function .'`'));
            if (strlen($where) >0) {
                $wheres[] = '(' . $where . ')';
            }
        }
        $where = '';
        if (count($wheres) >0) {
            $where = '(' . implode ( ' AND ' , $wheres) . ')';
        }
        return $where;        
    }

    /**
     * @param string $internal
     * @param array $limitStyles the limit values for this formfield indexed by limit type
     */
    protected function processWhereByInternal($internal,$limitStyles,$report = null) {        
        if ($report == null) {
            $report = $this->config->report;
        }
        if (!is_string($report)) {
            return '';
        }
        if (!is_array($limitStyles) || count($limitStyles) == 0 ) {
            return '';
        }
        $config = I2CE::getConfig()->traverse("/modules/CustomReports/reports/$report/reporting_internals/$internal/limits",false,false); 
        if (!$config instanceof I2CE_MagicDataNode) {
            return '';
        }
        $wheres = array();
        foreach ($limitStyles  as $limitStyle=>$values) {
            if (!isset($config->$limitStyle)) {
                continue;
            }
            if (!isset($config->$limitStyle->enabled) || !$config->$limitStyle->enabled) {
                continue;
            }
            if (!is_array($values) || count($values)==0) {
                continue;
            }
            $repInternal = $this->getSwissReportInternal($internal,$report); //get rid of the leading +
            if (!$repInternal instanceof I2CE_Swiss_CustomReports_Report_ReportingInternal) {
                continue;
            }
            $fieldObj = $repInternal->getFieldObj();
            if (!$fieldObj instanceof I2CE_FormField) {
                continue;
            }
            $where =  trim($fieldObj->generateLimit(array('style'=>$limitStyle,'data'=>$values), '`' . $internal .'`'));
            if (strlen($where) >0) {
                $wheres[] = '(' . $where . ')';
            }
        }
        $where = '';
        if (count($wheres) >0) {
            $where = '(' . implode ( ' AND ' , $wheres) . ')';
        }
        return $where;        
    }




        
    /**
     * returns an array of the default display options.  Default options are read and overriddenb
     * in the following order:
     * /modules/CustomReports/displays/$display/display_options
     * /modules/CustomReports/relationships/display_options/$display
     * /modules/CustomReports/reports/display_options/$display
     * /modules/CustomReports/reportViews/$view/display_options/$display
     * Finally any options that have a key in $get are replaced by that value
     * @param array $get
     * @param array $options. Default to the empty array.  The options that we want to be already set before we start goinng through
     * @returns mixed array or false on failure
     */
    protected function getDefaultOptions($get,$options = array() ) {
        //make sure we can overide the following options which may come from the $get varaible.
        $make_exist = array('limit_paginated','limit_page','limit_per_page',
                            'limit_offset','limit_amount','sort_order','display_order',
                            'total','save_options_as_default_view');
        foreach ($make_exist as $key) {
            if (!array_key_exists($key,$options) ) {
                $options[$key] = null;
            }
        }    
        $check = array(
            "/modules/CustomReports/default_display_options"=>I2CE::getConfig(),
            "/modules/CustomReports/displays/{$this->display}/display_options"=>I2CE::getConfig(),
            'default_display_options'=>$this->relationshipConfig,
            "display_options/{$this->display}"=>$this->relationshipConfig,
            'default_display_options'=>$this->reportConfig,
            "display_options/{$this->display}"=>$this->reportConfig,
            'default_display_options'=>$this->config,
            "display_options/{$this->display}"=>$this->config);

        // I don't know if this is the right way to do this or not.
        // I'm guessing probably not, but for a new report I added no other options were set
        // so maybe the edit needs to change to save the data elsewhere.
        $this->config->setIfIsSet( $options['total'], "total" );
        
        foreach($check as $k=>$node) {
            $t_options = array();
            if ( $node->setIfIsSet($t_options,$k,true)) {
                I2CE_Util::merge_recursive($options,$t_options);
            }
        }
        //now look at the get variables
        I2CE_Util::merge_recursive($options,$get,true,false); //add new keys but not the empty ones
        //finally load some limiting stuff from get variables that not have been set in the default options above.
        //and convert it over to the nested
        if (!array_key_exists('nested_limits',$options) || !is_array($options['nested_limits'])) {
            $options['nested_limits'] = array();
        }
        if (array_key_exists('limits',$get) && is_array($get['limits'])) {
            // Override the default limits if there are any in the POST
            // This is to fix issues with multiple selection boxes where
            // nothing is in the POST for those variables so it's impossible
            // to clear the defaults.
            $options['nested_limits'] = $get['limits'];
            /*
            foreach ( $get['limits'] as $field => $limit ) {
                $options['nested_limits'][$field] = $limit;
            }
            */
            /*
            I2CE_Util::merge_recursive($options['nested_limits'],$get['limits'],true,false);  //add new keys but not the empty ones
            */
        }
        return $options;
    } 


    /**
     * Adds any report display controls that can be added for this view.
     * @param DOMNode $conentNode
     * @param mixed $controls.  If null (default), we display all the report controsl.  If string or an array of string, we only display the indicated controls
     * @returns boolean $true on success
     */
    protected function displayReportControls($contentNode, $controls=null) {
        $this->template->addHeaderLink('mootools-core.js');
        $this->template->addHeaderLink('I2CE_ClassValues.js');        
        $this->template->addHeaderLink('I2CE_SubmitButton.js');        
        $displays = array();
        if (!$this->page instanceof I2CE_ShowReport_Interface 
            || !$this->page instanceof I2CE_Page ) {
            return true;
        }
        if (! is_array($displays = $this->page->getAllowedDisplays($this->view))) {
            $displays = array();
        }
        if (!in_array('Default',$displays)) {
            $displays[]= 'Default';       
        }
        $displayConfig = I2CE::getConfig()->modules->CustomReports->displays;
        if (is_string($controls)) {
            $controls= array($controls);
        }
        if (is_array($controls)) {
            $displays = array_intersect($displays,$controls);
        }
        if (count($displays)== 0) {
            $displays[] = 'Default';
        }
        if ( in_array( 'Default', $displays ) && count( $displays ) > 1 ) {
            foreach ( $displays as $i => $display ) {
                $hide = false;
                $displayConfig->setIfIsSet( $hide, "$display/hide_with_default" );
                if ( $hide ) {
                    unset( $displays[$i] );
                }
            }
        }
        if ( count( $displays ) > 1 
                && I2CE::getFileSearch()->search('TEMPLATES', 
                    "customReports_display_limit_apply_{$this->display}.html") ) {

            $reportLimitsNode = $this->template->getElementById('report_limits');
            if ($reportLimitsNode instanceof DOMNode ){
                $applyNode = $this->template->appendFileByNode( 
                        "customReports_display_limit_apply_{$this->display}.html",
                        "tr", $reportLimitsNode );
            }

        }
        foreach ($displays as $display ) {
            if ($display != $this->display ) {
                if (! ($displayObj = $this->page->instantiateDisplay($display,$this->view)) instanceof I2CE_CustomReport_Display) {
                    continue;
                }
                if (!$displayObj->canView()) {
                    continue;
                }
            } else {
                $displayObj = $this;
            }
            $controlNode = $this->template->createElement('span',array('class'=>"CustomReport_control",'id'=>"CustomReport_controls_$display"));            
            $contentNode->appendChild($controlNode);
            $displayObj->displayReportControl($controlNode);
        }
        return true;
    }
    
    protected function getBasePage() {
        $module = $this->page->module();
        if ($module == 'I2CE') {
            $base_page = $this->page->page();
        } else {
            $base_page = $module .'/' . $this->page->page();
        }
        return $base_page;
    }

    /**
     * Adds any controls for this display to the content node
     * @param DOMNode $contentNode 
     */
    protected function displayReportControl($contentNode) {
        $fileSearch = I2CE::getFileSearch();
        if ($fileSearch->search('CSS', "customReports_display_control_{$this->display}.css")) {
            $this->template->addHeaderLink("customReports_display_control_{$this->display}.css");
        }
        if ($fileSearch->search('TEMPLATES', "customReports_display_control_{$this->display}.html")) {
            $controlNode = $this->template->appendFileByNode("customReports_display_control_{$this->display}.html",'div',$contentNode);
            if (!$controlNode instanceof DOMNode ){
                I2CE::raiseError("Could not find template for ({$this->display}) control");
                return false;
            }
        } else {
            return true;
        }
        $base_page = $this->getBasePage();
        //$submitNodes = $this->template->query('.//input[@type="submit" and @name="' .  $this->display .  '_submit"]',$controlNode);
        //Some nodes were changed to use id and got rid of name, so check for both
        //for the weird query see: http://pivotallabs.com/users/alex/blog/articles/427-xpath-css-class-matching and http://stackoverflow.com/questions/1390568/xpath-how-to-match-attributes-that-contain-a-certain-string
        $qry = './/input[@type="submit" and ( @name="' .  $this->display .  '_submit" or @id="' . $this->display . '_submit" )] | .//span[@id="' .$this->display . '_submit" and contains(concat(" ",normalize-space(@class)," ")," button ")]';
        $submitNodes = $this->template->query($qry,$controlNode);
        for ($i=0; $i< $submitNodes->length; $i++) {
            $submitNode = $submitNodes->item($i);
            $class = $submitNode->getAttribute('class') . " method=post action=index.php/$base_page/{$this->view}/{$this->display}";            
            $submitNode->setAttribute('class',$class);            
        }
        $submitNodes = $this->template->query('.//input[@type="submit" and @name="' .  $this->display .  '_saveOptions"]',$controlNode);
        for ($i=0; $i< $submitNodes->length; $i++) {
            $submitNode = $submitNodes->item($i);
            $class = $submitNode->getAttribute('class') . " method=post action=index.php/$base_page/saveOptions/{$this->view}/{$this->display}";            
            $submitNode->setAttribute('class',$class);            
        }
        return true;
    }
    



    protected  $reportViewsFactory;
    protected  function getReportViewsFactory() {
        if ($this->reportViewsFactory instanceof I2CE_SwissMagicFactory) {
            return $this->reportViewsFactory;
        }
        $repViewsConfig = $this->config->traverse('..');
        if (!$repViewsConfig instanceof I2CE_MagicDataNode) {
            I2CE::raiseError("Bad magic data path to report views");
            return false;
        }
        $init_options = array(
            'root_url_postfix'=>'reportViews',
            'root_path'=>$repViewsConfig->getPath(false),
            'root_type'=>'CustomReports_ReportViews');
        try {
            $swiss_factory = new I2CE_SwissMagicFactory($this->page,$init_options);
        } catch (Exception $e) {
            I2CE::raiseError("Could not create swissmagic for reportViews:" . $e->getMessage());
            return false;
        }
        try {
            $swiss_factory->setRootSwiss();
        } catch (Exception $e) {
            I2CE::raiseError("Could not create root swissmagic for relationships:" . $e->getMessage());
            return false;
        }      
        $this->reportViewsFactory = $swiss_factory;
        return $this->reportViewsFactory;
    }


    protected function getSwissReportView() {
        $reportViewsFactory = $this->getReportViewsFactory();
        if (!$reportViewsFactory instanceof I2CE_SwissMagicFactory) {
            return false;
        }
        return $reportViewsFactory->getSwiss('/' . $this->view);
    }

    protected function getSwissReport( $report) {
        if ($report == null) {
            $report = $this->config->report;
        }
        if (!is_string($report)) {
            return false;
        }
        $reportView = $this->getSwissReportView();
        if(!$reportView instanceof I2CE_Swiss_CustomReports_ReportView) {
            return false;
        }
        return $reportView->getSwissReport($report);
    }
    

    protected function getSwissReportFunction($func, $report) {
        if ($report == null) {
            $report = $this->config->report;
        }
        list ($mergekey,$mergereport,$func) = array_pad(explode(':',$func,3),-3,'');
        if ($mergereport) {
            $report = $mergereport;
        }        
        if (!is_string($report)) {
            return false;
        }
        $swissReport = $this->getSwissReport($report);
        if (!$swissReport instanceof I2CE_Swiss_CustomReports_Report) {
            return false;
        }
        $funcs = $swissReport->getChild('reporting_functions');
        if (!$funcs instanceof I2CE_Swiss_CustomReports_Report_ReportingFunctions) {
            return false;
        }
        return $funcs->getChild($func);
    }

    protected function getSwissReportInternal($internal, $report) {
        if ($report == null) {
            $report = $this->config->report;
        }
        list ($mergekey,$mergereport,$internal) = array_pad(explode(':',$internal,3),-3,'');
        if ($mergereport) {
            $report = $mergereport;
        }        
        if (!is_string($report)) {
            return null;
        }
        $swissReport = $this->getSwissReport($report);
        if (!$swissReport instanceof I2CE_Swiss_CustomReports_Report) {
            return null;
        }
        $internals = $swissReport->getChild('reporting_internals');
        if (!$internals instanceof I2CE_Swiss_CustomReports_Report_ReportingInternals) {
            return null;
        }
        return $internals->getChild($internal);
    }


    /**
     * Return a string representation of the limits for this report.
     * @return string
     */
    public function getReportLimitsDescription() {

        if ( !is_array( $this->limitDescText ) || count( $this->limitDescText ) < 1 ) {
            $report = $this->config->report;
            $this->limitDescText = $this->getModuleLimits( $report, true );
    
            if (array_key_exists('nested_limits',$this->defaultOptions) && is_array($this->defaultOptions['nested_limits'])) {
                $limitValues =$this->defaultOptions['nested_limits'];
            } else {
                $limitValues = array();
            }
            if ( $report &&  ($reportConfig = I2CE::getConfig()->traverse("/modules/CustomReports/reports/$report")) instanceof I2CE_MagicDataNode) {
                foreach( $limitValues as $name => $data ) {
                    if ( substr( $name, 0, 1 ) == "+" ) {
                        $name = substr( $name, 1 );
                        foreach ( $data as $type => $value ) {
                            $repFunction = $this->getSwissReportFunction( $name, $report );
                            if (!$repFunction instanceof I2CE_Swiss_CustomReports_Report_ReportingFunction) {
                                continue;
                            }
                            $fieldObj = $repFunction->getFieldObj();
                            if (!$fieldObj instanceof I2CE_FormField) {
                                continue;
                            }
                            if ( $reportConfig->is_parent( "reporting_functions/$name/limits/$type" ) ) {
                                $limitConfig = $reportConfig->reporting_functions->$name->limits->$type;
                                if ( array_key_exists( 'value', $value ) 
                                        && $value['value'] != "" ) {
                                    if ( $limitConfig->is_scalar( 'header' ) ) {
                                        $limitDescHeader = $limitConfig->header;
                                    } else {
                                        $limitDescHeader = $limitConfig->getName();
                                    }
                                    if ( is_array( $value['value'] ) ) {
                                        $descArr = array();
                                        foreach( $value['value'] as $val ) {
                                            $fieldObj->setFromDB( $val );
                                            $descArr[] = $fieldObj->getDisplayValue();
                                        }
                                        $descText = "[" . implode( ', ', $descArr ) . "]";
                                    } else {
                                        $fieldObj->setFromDB( $value['value'] );
                                        $descText = $fieldObj->getDisplayValue();
                                    }
                                    $this->limitDescText[] = $limitDescHeader . ": " 
                                        . $descText;
                                }
                            }
                        }
                    } else {
                        foreach( $data as $type => $value ) {
                            list( $reportform, $field ) = explode( '+', $name, 2 );
                            list ( $formObj, $fieldObj ) = $this->getFormFieldObjects("$reportform+$field", $report);                    
                            if ( $reportConfig->is_parent( "reporting_forms/$reportform/fields/$field/limits/$type" ) ) {
                                $limitConfig = $reportConfig->traverse("reporting_forms/$reportform/fields/$field/limits/$type");
                                if ( array_key_exists( 'value', $value ) 
                                        && $value['value'] != "" ) {
                                    if ( $limitConfig->is_scalar( 'header' ) ) {
                                        $limitDescHeader = $limitConfig->header;
                                    } else {
                                        $limitDescHeader = $limitConfig->getName();
                                    }
                                    if ( is_array( $value['value'] ) ) {
                                        $descArr = array();
                                        foreach( $value['value'] as $val ) {
                                            $fieldObj->setFromDB( $val );
                                            $descArr[] = $fieldObj->getDisplayValue();
                                        }
                                        $descText = "[" . implode( ', ', $descArr ) . "]";
                                    } else {
                                        $fieldObj->setFromDB( $value['value'] );
                                        $descText = $fieldObj->getDisplayValue();
                                    }
                                    $this->limitDescText[] = $limitDescHeader . ": " 
                                        . $descText;
                                }
                            }
                        }
                    }
                }
            }
        }
        if ( count( $this->limitDescText ) > 0 ) {
            return implode( ', ', $this->limitDescText );
        } else {
            return "";
        }
    }



    /**
     * Displays any report limits in the content node
     * @param DOMNode $contentNode
     */
    protected function displayReportLimits($contentNode) {       
        if (array_key_exists('nested_limits',$this->defaultOptions) && is_array($this->defaultOptions['nested_limits'])) {
            $limitValues =$this->defaultOptions['nested_limits'];
        } else {
            $limitValues = array();
        }
        $excludes = I2CE::getConfig()->getAsArray("/modules/CustomReports/limit_excludes/displayed");
        $this->seen_merges =array($this->config->report);
        $this->limitDescText = $this->getModuleLimits( $this->config->report, true );
        $this->_displayReportLimits($contentNode,$this->config, $this->config->report, $limitValues, $excludes);
        if ( count( $this->limitDescText ) > 0 ) {
            $this->template->setDisplayDataImmediate( 'report_view_limit_has_description',
                    true );
            $this->template->setDisplayDataImmediate( 'report_view_limit_description',
                    implode( ", ", $this->limitDescText ) );
        } else {
            $this->template->setDisplayDataImmediate( 'report_view_limit_has_description',
                    false );
        }
        return true;
    }

    protected $displayed_limits = array();
    /**
     * Displays any report limits in the content node
     * @param DOMNode $contentNode
     */
    protected function _displayReportFormLimit($reportform,$field,$limit, $contentNode, $rv_config, $reportConfig,$limitValues, $excludes,  $merge = '') {       
        $report = $reportConfig->getName();
        $reportformfield = "$merge$reportform+$field";
        $reportformfieldlimit = $reportformfield . "+" . $limit;
        if (in_array($reportformfieldlimit,$this->displayed_limits) || in_array($limit,$excludes)) {
            return;
        }
        $this->displayed_limits[] = $reportformfieldlimit;
        list ($formObj,$fieldObj) = $this->getFormFieldObjects("$reportform+$field", $report);                    
        if (!$formObj instanceof I2CE_Form 
            || ! $fieldObj instanceof I2CE_FormField
            || ! ($limitConfig = $reportConfig->traverse("reporting_forms/$reportform/fields/$field/limits/$limit")) instanceof I2CE_MagicDataNode
            || !isset($limitConfig->enabled )
            || !$limitConfig->enabled) {
            return;
        }
        if ( $field == 'id' && $formObj instanceof I2CE_List 
                && ($fieldConfig = $reportConfig->traverse("reporting_forms/$reportform/fields/$field")) instanceof I2CE_MagicDataNode 
                && isset($fieldConfig->form_display) && $fieldConfig->form_display ) {
            $idFieldConf = array( 'meta' => array( 'form' => array( $formObj->getName() ) ) );
            if ( isset($fieldConfig->form_display_fields) && $fieldConfig->form_display_fields ) {
                $idFieldConf['meta']['display'] = array( 'default' => array( 'fields' => $fieldConfig->form_display_fields ) );
            }
            $fieldObj = I2CE_FormField::createField("MAP", "id", $idFieldConf );
        }
        if (array_key_exists($reportformfield,$limitValues) ) {
            $fieldLimitValues = $limitValues[$reportformfield];
        } else {
            $fieldLimitValues = array();
        }
        if (array_key_exists($limit,$fieldLimitValues) && is_array($fieldLimitValues[$limit] )) {
            $limitLimitValues = $fieldLimitValues[$limit];
        } else {
            $limitLimitValues = array();
        }
        //now we should have a valid limit
        $limit_default = 'default';
        $reportConfig->setIfIsSet($limit_default,"reporting_forms/$reportform/fields/$field/limit_default");
        $method = 'processLimitMenu_' . $limit;                                        
        $data = $fieldObj->$method($limitLimitValues,false);
        $method = 'getLimitMenu_' . $limit;
        $node = $fieldObj->$method($this->template,"limits:$merge$reportformfield:$limit",$data,$limit_default);
        if ( !$node instanceof DOMNode) {
            return;
        }
        $limitDesc = $fieldObj->describeLimit( $limit, $data );
        if ( $limitDesc && $limitDesc != "" ) {
            if ( $limitConfig->is_scalar( 'header' ) ) {
                $limitDescHeader = $limitConfig->header;
            } else {
                $limitDescHeader = $limitConfig->getName();
            }
            $this->limitDescText[] = $limitDescHeader . ": " 
                . $limitDesc;
        }
        $this->displayReportLimit($contentNode,$node,$limitConfig);
    }


    /**
     * Displays any report limits in the content node
     * @param DOMNode $contentNode
     */
    protected function _displayReportFunctionLimit($function,$limit, $contentNode, $rv_config, $reportConfig,$limitValues, $excludes,  $merge = '') {       
        $report = $reportConfig->getName();
        $limitConfig = $reportConfig->reporting_functions->$function->limits->$limit;
        $reportfunclimit = $merge . '+' . $function . '+' . $limit;
        if (in_array($reportfunclimit,$this->displayed_limits) 
            || in_array($limit,$excludes) 
            || ! ($repFunction = $this->getSwissReportFunction($function,$report)) instanceof I2CE_Swiss_CustomReports_Report_ReportingFunction
            || ! (  $fieldObj = $repFunction->getFieldObj()) instanceof I2CE_FormField
            || !isset($limitConfig->enabled )
            || !$limitConfig->enabled) {
            return;
        }
        $this->displayed_limits[] = $reportfunclimit;
        if (array_key_exists($merge . '+'.$function,$limitValues) ) {
            $funcLimitValues = $limitValues['+'.$function];
        } else {
            $funcLimitValues = array();
        }
        if (array_key_exists($limit,$funcLimitValues) && is_array($funcLimitValues[$limit] )) {
            $limitLimitValues = $funcLimitValues[$limit];
        } else {
            $limitLimitValues = array();
        }
        $method = 'processLimitMenu_' . $limit;                                        
        $data = $fieldObj->$method($limitLimitValues,false);
        $method = 'getLimitMenu_' . $limit;
        $node = $fieldObj->$method($this->template,"limits:$merge+$function:$limit",$data);
        if ( !$node instanceof DOMNode) {
            return;
        }                       
        $limitDesc = $fieldObj->describeLimit( $limit, $data );
        if ( $limitDesc && $limitDesc != "" ) {
            if ( $limitConfig->is_scalar( 'header' ) ) {
                $limitDescHeader = $limitConfig->header;
            } else {
                $limitDescHeader = $limitConfig->getName();
            }
            $this->limitDescText[] = $limitDescHeader . ": "  . $limitDesc;
        }
        $this->displayReportLimit($contentNode,$node,$limitConfig);
    }

    /**
     * Displays any report limits for internals in the content node
     * @param DOMNode $contentNode
     */
    protected function _displayReportInternalLimit($internal,$limit, $contentNode, $rv_config, $reportConfig,$limitValues, $excludes ) {       
        $report = $reportConfig->getName();
        $limitConfig = $reportConfig->reporting_internals->$internal->limits->$limit;
        $reportintlimit = $internal . '+' . $limit;
        if (in_array($reportintlimit,$this->displayed_limits) 
            || in_array($limit,$excludes) 
            || ! ($repInternal = $this->getSwissReportInternal($internal,$report)) instanceof I2CE_Swiss_CustomReports_Report_ReportingInternal
            || ! (  $fieldObj = $repInternal->getFieldObj()) instanceof I2CE_FormField
            || !isset($limitConfig->enabled )
            || !$limitConfig->enabled) {
            return;
        }
        $this->displayed_limits[] = $reportintlimit;
        if (array_key_exists($internal,$limitValues) ) {
            $intLimitValues = $limitValues[$internal];
        } else {
            $intLimitValues = array();
        }
        if (array_key_exists($limit,$intLimitValues) && is_array($intLimitValues[$limit] )) {
            $limitLimitValues = $intLimitValues[$limit];
        } else {
            $limitLimitValues = array();
        }
        $method = 'processLimitMenu_' . $limit;                                        
        $data = $fieldObj->$method($limitLimitValues,false);
        $method = 'getLimitMenu_' . $limit;
        $node = $fieldObj->$method($this->template,"limits:$internal:$limit",$data);
        if ( !$node instanceof DOMNode) {
            return;
        }                       
        $limitDesc = $fieldObj->describeLimit( $limit, $data );
        if ( $limitDesc && $limitDesc != "" ) {
            if ( $limitConfig->is_scalar( 'header' ) ) {
                $limitDescHeader = $limitConfig->header;
            } else {
                $limitDescHeader = $limitConfig->getName();
            }
            $this->limitDescText[] = $limitDescHeader . ": "  . $limitDesc;
        }
        $this->displayReportLimit($contentNode,$node,$limitConfig);
    }


    /**
     * Displays any report limits in the content node
     * @param DOMNode $contentNode
     */
    protected function _displayReportLimits($contentNode, $rv_config, $report,$limitValues, $excludes, $merge = '') {       
        $swissRel = false;
        if ( !$report ||  ! ($reportConfig = I2CE::getConfig()->traverse("/modules/CustomReports/reports/$report")) instanceof I2CE_MagicDataNode) {
            return;
        }
        $limit_orders = array();
        $reportConfig->setIfIsSet($limit_orders, "limit_order",true);        
        ksort($limit_orders);
        foreach ($limit_orders as $limit_order) {            
            list( $limit_reportform, $limit_field , $limit_style ) = array_pad(explode( '+', $limit_order, 3 ),3,'');
            if (!$limit_field || !$limit_style) {
                continue;
            }
            if (!$limit_reportform) {
                //limit on report function
                $this->_displayReportFunctionLimit( $limit_field,$limit_style ,$contentNode,$rv_config,$reportConfig,$limitValues,$excludes,$merge);
            } else {
                $this->_displayReportFormLimit( $limit_reportform,$limit_field,$limit_style ,$contentNode,$rv_config,$reportConfig,$limitValues,$excludes,$merge);
                //limit on report form
            }
        }
        if ($reportConfig->is_parent('reporting_functions')) {
            foreach ($reportConfig->reporting_functions as $func=>$funcConfig) {
                if (!$funcConfig instanceof I2CE_MagicDataNode || !$funcConfig->is_parent('limits')) {
                    continue;
                }
                foreach ($funcConfig->limits->getKeys() as $limit_style) {
                    $this->_displayReportFunctionLimit( $func,$limit_style ,$contentNode,$rv_config,$reportConfig,$limitValues,$excludes,$merge);
                }            
            }
        }
        if ( $reportConfig->is_parent('reporting_internals') ) {
            foreach( $reportConfig->reporting_internals as $internal => $internalConfig ) {
                if ( !$internalConfig instanceof I2CE_MagicDataNode || !$internalConfig->is_parent('limits') ) {
                    continue;
                }
                foreach( $internalConfig->limits->getKeys() as $limit_style ) {
                    $this->_displayReportInternalLimit( $internal, $limit_style, 
                            $contentNode, $rv_config, $reportConfig, $limitValues, 
                            $excludes );
                }
            }
        }
        if ($reportConfig->is_parent('reporting_forms')) {
            foreach ($reportConfig->reporting_forms as $reportform=>$reportFormConfig) {
                if (!$reportFormConfig instanceof I2CE_MagicDataNode || !$reportFormConfig->is_parent('fields')) {
                    continue;
                }
                foreach ($reportFormConfig->fields as $field=>$fieldConfig) {
                    if (!$fieldConfig instanceof I2CE_MagicDataNode || !$fieldConfig->is_parent('limits')) {
                        continue;
                    }
                    foreach ($fieldConfig->limits->getKeys() as $limit) {
                        $this->_displayReportFormLimit( $reportform,$field,$limit ,$contentNode,$rv_config,$reportConfig,$limitValues,$excludes,$merge);
                    }
                    if ($fieldConfig->is_parent('merges')) {
                        foreach ($fieldConfig->merges as $mergekey=>$mergeConfig) {
                            $merge_report = false;
                            if (!$mergeConfig instanceof I2CE_MagicDataNode
                                || ! ($rv_merge_config = $rv_config->traverse("fields/$reportform+$field/merges/$mergekey")) instanceof I2CE_MagicDataNode
                                || !isset($rv_merge_config->enabled )
                                || !$rv_merge_config->enabled 
                                || !isset($merge_config->enabled )
                                || !$merge_config->enabled 
                                || ! $reportConfig->setIfIsSet($merge_report,"reporting_forms/$reportform/fields/$field/merges/$mergekey/report")
                                || !I2CE_MagicDataNode::checkKey($merge_report) 
                                || in_array($merge_report,$this->seen_merges)) {
                                //don't allow report to be merged in more than once
                                continue;
                            }
                            $this->seen_merges[] = $merge_report;                            
                            $this->_displayReportLimits($contentNode, $rv_merge_config, $merge_report,$limitValues, $excludes,"$mergekey:$merge_report:");
                        }
                    }
                }
            }
        }
        return true;
    }
    
    



    /**
     * Adds the report limit node to its content node
     * @param DOMNode $contentNode
     * @param DOMNode $limitnode
     * @param I2CE_MagicDataNode $limitConfig
     */
    protected function displayReportLimit($contentNode,$limitNode,$limitConfig) {

        if (! ($limitContainer = $this->template->appendFileByNode('display_report_limit.html','//*[@name="limit_container"][1]',$contentNode))) {
            I2CE::raiseError("Could not load display_report_limit.html");
            return false;
        }
        if (!$limitConfig->is_scalar('header')) {
            $header = $limitConfig->getName();
        } else {
            $header = $limitConfig->header;
        }
        $this->template->setDisplayDataImmediate('header',$header, $limitContainer);

        if ( ! ($inputNode = $this->template->getElementByName('limit',0,$limitContainer)) instanceof DOMElement) {
            I2CE::raiseError("Could not get limit node in display_report_limit.html");
            return false;
        }
        $inputNode->appendChild($limitNode);
        return true;
    }


    
    public function saveDefaultView() {
        if (!array_key_exists( 'save_options_as_default_view',$this->defaultOptions)) {
            return;
        }
        if (!$this->defaultOptions['save_options_as_default_view']) {
            return;
        }
        unset($this->defaultOptions['save_options_as_default_view']);
        if (!is_string($this->display) || strlen($this->display) ==0) {
            return;
        }
        $this->config->default_display = $this->display;
        $this->saveDisplayOptions();
    }

    public function saveDisplayOptions() {
        if (!is_string($this->display) || strlen($this->display) ==0) {
            return false;
        }
        $this->config->display_options->{$this->display}->erase(); 
        $valid =         array_keys($this->getDisplayFieldsData());
        $this->defaultOptions['sort_order'] = implode(",", $this->validateSortFields(explode(",",$this->defaultOptions['sort_order']),$valid));
        $this->config->display_options->{$this->display} = $this->defaultOptions;
        return true;
    }


    /**
     * Display the report
     * @param DOMNode $contentNode The DOM node we wish to display into. If null, we do not do any of the DOM processing stuff, do
     * not call the report display controls, limits etc. It will however still call processResults with a DOMNode of null
     * @param boolean $processResults Defaults to true meaning we run through the results.  If false, we do not process results.
     * @param mixed $controls.  If null (default), we display all the report controsl.  If string or an array of string, we only display the indicated controls
     * @returns boolean. true on sucess
     */
    public function display($contentNode,$processResults = true, $controls = null) {
        $this->saveDefaultView();
        if ($processResults) {
            $data = $this->getResults();
        }
        if ($contentNode instanceof DOMNOde) {
            $baseHTML = "customReports_display_{$this->display}_base.html";
            $this->displayConfig->setIfIsSet($baseHTML,'base_html');
            $baseNode = $this->template->appendFileByNode( $baseHTML, 'div',$contentNode );        
            if (!$baseNode instanceof DOMNode) {
                I2CE::raiseError("Unable to add {$this->display} display base template");
                return false;
            }        
            if (!$this->displayMetaInfo($baseNode)) {
                I2CE::raiseError("Could not process results");
                return false;
            }
            $no_controls = ( array_key_exists( 'no_controls', $this->defaultOptions ) && $this->defaultOptions['no_controls'] );
            #$reportLimitsNode = $this->template->getElementById('report_limits', $baseNode);
            if( $no_controls ){
              $this->template->setDisplayDataImmediate( 'report_view_limit_has_description', false );
              $this->template->setDisplayDataImmediate( 'report_view_show_controls', false );
             }else{
              $this->template->setDisplayDataImmediate( 'report_view_show_controls', true );
              $reportLimitsNode = $this->template->getElementById('report_limits', $baseNode);
				
              if (!$reportLimitsNode instanceof DOMNode ){
                  I2CE::raiseError("Don't know where to add report limits");
                  return false;
              }
              if (!$this->displayReportLimits($reportLimitsNode)) {
                  return false;
              }
              $reportControlsNode = $this->template->getElementById('report_controls', $baseNode);
              if (!$reportControlsNode instanceof DOMNode ){
                  I2CE::raiseError("Don't know where to add report controls");
                  return false;
              }
              if (!$this->displayReportControls($reportControlsNode, $controls)) {
                  return false;
              }
            }
              $resultsNode = $this->template->getElementById($this->getReportPrefix() . 'report_results', $baseNode);
            
            if (!$resultsNode instanceof DOMNode ){
                I2CE::raiseError("Don't know where to add report results");
                return false;
            }
          
        } else {
            $resultsNode = null;
        }
        if ($processResults) {
            $this->template->setDisplayData('show_results','1');
            if (!$this->processResults($data,$resultsNode)) {                
                return false;
            }            
        } else {
            $data = false;
            $this->template->setDisplayData('show_results','');
        }
        return true;
    }

    /**
     * Process results
     * @param array $results_data an array of results.  indices are 'restults' and Buffered result and 'num_results' the
     * number of results.  (these values may be false on failure)
     * @param DOMNode $contentNode.  Default to null a node to append the results onto
     */
    protected function processResults($results_data,$contentNode=null) {
        //$results_data['num_results']  is the total number of results or false
        if ($results_data['results'] == false) {
            I2CE::raiseError("No results");
            return false;
        }
        if ($this->row_start === false) {
            $row_num = 0;
        } else {
            $row_num = $this->row_start;
        }        
        try {
            while ($row = $results_data['results']->fetch()) {
                if (!$this->processResultRow($row,$row_num,$contentNode)) {
                    unset( $results_data['results'] );
                    return false;
                }
                $row_num++;
            }
        } catch ( PDOException $e ) {
            I2CE::pdoError( $e, "Failed to process row results.");
            unset( $results_data['results'] );
            return false;

        }
        unset( $results_data['results'] );
        return true;
    }

    
    /**
     * Process a result row.
     * @param array $row
     * @param int $row_num The current row number when processing results.  If there was a result limit, it starts the count from the beginning of the
     * result offset.  Othwerwise, it starts counting form zero.
     * @param DOMNode $contentNode. Default to null. A node to append the result onto
     * @returns boolean. True on success
     */
    protected function processResultRow($row,$row_num,$contentNode=null) {
        return true;
    }
   

    protected function displayMetaInfo($contentNode) {
        if (!$this->template instanceof I2CE_Template) { //this is not a web page -- maybe its xml i.e. only an instanceof I2CE_TemplateMeister
            I2CE::raiseError("Unexpected template type " . get_class($this->template));
            return false;
        }
        $form = $this->template->query(".//*[@id='limit_form']",$contentNode);
        if ($form->length == 1) {
            //$form = $form->item(0)->setAttribute('action',$this->getBasePage() . "/show/{$this->view}/{$this->display}");            
            // The above was causing problems with having /show/show in the URL so I took it out.
            $form = $form->item(0)->setAttribute('action',$this->getBasePage() . "/{$this->view}/{$this->display}");            
        }   else {
            I2CE::raiseError("Could not form node");
            return false;
        }            
        $formWormOptions = array('optionsMenuPositionVert'=>'mouse_above');
        $this->template->addFormWorm('limit_form',$formWormOptions);
        $fileSearch = I2CE::getFileSearch();
        if ($fileSearch->search('CSS', "customReports_display.css")) {
            $this->template->addHeaderLink("customReports_display.css");
        }
        if ($fileSearch->search('CSS', "customReports_display_{$this->display}.css")) {
            $this->template->addHeaderLink("customReports_display_{$this->display}.css");
        }
        $this->template->setDisplayDataImmediate('report_view_display_name',$this->config->display_name,$contentNode);
        $this->template->setDisplayDataImmediate('report_view_description',$this->config->description,$contentNode);
        $this->template->setDisplayDataImmediate('limit_paginated',$this->defaultOptions['limit_paginated'],$contentNode);
        $this->template->setDisplayDataImmediate('limit_page',$this->defaultOptions['limit_page'],$contentNode);
        $this->template->setDisplayDataImmediate('limit_per_page',$this->defaultOptions['limit_per_page'],$contentNode);        
        $this->template->setDisplayDataImmediate('limit_offset',$this->defaultOptions['limit_offset'],$contentNode);
        $this->template->setDisplayDataImmediate('limit_amount',$this->defaultOptions['limit_amount'],$contentNode);        
        $this->template->setDisplayDataImmediate('sort_order',$this->defaultOptions['sort_order'],$contentNode);
        $time = I2CE_CustomReport::getLastGenerationTime($this->config->report);
        if ($time) {
            $cache_msg = "Report was generated at: " . strftime("%c",$time);
        } else {
            $cache_msg = "Report has not been generated";
        }
        $this->template->setDisplayDataImmediate('report_time',$cache_msg);

        $has_relations = '';
        if ($this->config->setIfIsSet($related_views,'related_views')) {
            $related_views = explode(',',$related_views);
            $related = array();
            foreach ($related_views as $related_view) {
                if (!I2CE_MagicDataNode::checkKey($related_view)) {
                    continue;
                }
                if (!$this->config->is_parent("../$related_view") ) {
                    continue;
                }
                $view_name = $related_view; 
                $this->config->setIfIsSet($view_name,"../$related_view/display_name");
                $disabled = false;
                $this->config->setIfIsSet($disabled,"../$related_view/disable");
                if ($disabled) {
                    continue;
                }
                $related[$related_view]= $view_name;
            }           
            if (count($related) >0 && ( ($node = $this->template->getElementById('related_views_list', $contentNode)) instanceof DOMNode )) {
                $has_relations = 1;
                foreach ($related as $related_view=>$view_name) {
                    $liNode = $this->template->createElement('li');
                    $linkNode =$this->template->createElement('a',array('href'=>"CustomReports/show/$related_view"), $view_name);
                    $liNode->appendChild($linkNode);
                    $node->appendChild($liNode);
                }
            }
        }
        $this->template->setDisplayDataImmediate('related_views',$has_relations,$contentNode);

        return true;
    }


    protected function getPivots() {
        $this->seen_merges = array($this->config->report);
        if (!$this->config->setIfIsSet($related_views,'related_views')) {
            return array();
        }
        $t_related_views = explode(',',$related_views);
        $pivotable_views = array();
        foreach ($t_related_views as $related_view) {
            if ($this->config->getName() == $related_view) {
                continue;
            }
            if (!I2CE_MagicDataNode::checkKey($related_view)) {
                continue;
            }
            if (!$this->config->is_parent("../$related_view")) {
                continue;
            }
            $view_name = false;
            if (!$this->config->setIfIsSet($view_name,"../$related_view/display_name")) {
                continue;
            }
            $disabled = false;
            $this->config->setIfIsSet($disabled,"../$related_view/disable");
            if ($disabled) {
                continue;
            }            
            $report = false;
            if (!$this->config->setIfIsSet($report,"../$related_view/report") || !I2CE_MagicDataNode::checkKey($report)) {
                continue;
            }
            if (! ($reportConfig = I2CE::getConfig()->traverse("/modules/CustomReports/reports/$report")) instanceof I2CE_MagicDataNode) {
                continue;
            }
            if (!$reportConfig->is_parent('reporting_forms')) {
                continue;
            }
            $relationship = false; 
            if (! ($reportConfig->setIfIsSet($relationship,"relationship")) || ! I2CE_MagicDataNode::checkKey($relationship)) {
                continue;
            }
            if (!array_key_exists($relationship,$this->relationships)) {
                try {
                    $this->relationships[$relationship] = new I2CE_FormRelationship($relationship);
                }   catch(Exception $e) {
                    I2CE::raiseError("Invalid relationship $relationship");
                }
            }
            if (!$this->relationships[$relationship] instanceof I2CE_FormRelationship) {
                continue;
            }
            foreach ($reportConfig->reporting_forms as $reportForm=>$reportFormConfig) {
                if ((!$reportFormConfig instanceof I2CE_MagicDataNode) || !$reportFormConfig->is_parent('fields')) {
                    continue;
                }
                if ( ! ($form  = $this->relationships[$relationship]->getForm($reportForm))) {
                    continue;
                }
                foreach ($reportFormConfig->fields as $field=>$fieldConfig) {
                    if ((!$fieldConfig instanceof I2CE_MagicDataNode) || !$fieldConfig->is_parent('limits')) {
                        continue;
                    }
                    foreach ($fieldConfig->limits as $limit=>$limitConfig) {
                        if ((!$limitConfig instanceof I2CE_MagicDataNode)) {
                            continue;
                        }   
                        $allow = false;
                        $limitConfig->setIfIsSet($allow,'enabled');
                        if (!$allow) { 
                            continue;
                        }
                        $allow = false;
                        $limitConfig->setIfIsSet($allow,'pivot');
                        if (!$allow) { 
                            continue;
                        }
                        $allow = false;
                        $limitConfig->setIfIsSet($allow,'allow_pivot');
                        if (!$allow) { 
                            continue;
                        }
                        list($formObj,$fieldObj) = $this->getFormFieldObjects("$reportForm+$field", $report);
                        if (!$fieldObj instanceof I2CE_FormField) {
                            continue;
                        }
                        //we have a valid pivotable limit                        
                        $pivotable_views[] = array(
                            'view_name'=>$view_name,
                            'relatedView'=>$related_view,
                            'pivotLimit'=>$limit,
                            'pivotForm'=>$form,
                            'pivotReportForm'=>$reportForm,
                            'pivotField'=>$field,
                            'mapped' => false
                            );

                        if ($fieldObj instanceof I2CE_FormField_MAPPED) {
                            $pivotable_views[] = array(
                                'view_name'=>$view_name,
                                'relatedView'=>$related_view,
                                'pivotLimit'=>$limit,
                                'pivotForm'=>$form,
                                'pivotReportForm'=>$reportForm,
                                'pivotField'=>$field,
                                'mapped' => $fieldObj->getSelectableForms()
                                );                                
                        }
                    }
                }
                
            }
        }
        if (count($pivotable_views) == 0) {
            return array();
        }
        $pivots = array();
        $this->_getPivots($this->config,$pivots,$pivotable_views,$this->reportConfig);
        return $pivots;
    }
    
    protected function _getPivots($config,&$pivots,$pivotable_views,$reportConfig = null,$merge ='') {
        if (!$config instanceof I2CE_MagicDataNode || !$config->is_parent('fields') || !$reportConfig instanceof I2CE_MagicDataNode) {
            return;
        }
        $relationship = false; 
        if (! ($reportConfig->setIfIsSet($relationship,"relationship")) || ! I2CE_MagicDataNode::checkKey($relationship)) {
            return;
        }
        if (!array_key_exists($relationship,$this->relationships)) {
            try {
                $this->relationships[$relationship] = new I2CE_FormRelationship($relationship);
            }   catch(Exception $e) {
                I2CE::raiseError("Invalid relationship $relationship");
            }
        }
        if (!$this->relationships[$relationship] instanceof I2CE_FormRelationship) {
            return;
        }
        //first make sure all form+id's exist so we can pick it up.
        $forms = array();
        foreach ($config->fields->getKeys() as $reportformfield) {
            list($reportForm,$field) = array_pad(explode('+',$reportformfield,2),2,'');
            if (!$reportForm || !$field || $field == 'id') {
                continue;
            }
            $formid = $reportForm . '+id';
            if ($config->fields->pathExists($formid) || $config->fields->is_scalar($formid)) {
                continue;
            }
            $config->fields->$formid->set_parent();
        }
        foreach ($config->fields as $reportformfield=>$formFieldConfig) {
            list($reportForm,$field) = array_pad(explode('+',$reportformfield,2),2,'');
            //go through each of the fields in this report view to see if report has any enabled reports                
            foreach ($pivotable_views as $i=>$pivot_data) {
                $reportForms[$reportForm] = true;
                if (array_key_exists('mapped',$pivot_data) && is_array($pivot_data['mapped']) && count($pivot_data['mapped']) > 0) {
                    if ($field != 'id') {
                        continue;
                    }                    
                    if ( ! ($form  = $this->relationships[$relationship]->getForm($reportForm))) {
                        continue;
                    }
                    if (!in_array($form,$pivot_data['mapped'])) {
                        continue;
                    }
                } else{ 
                    if ($pivot_data['pivotField'] != $field) {
                        continue;
                    }
                    if ( ! ($form  = $this->relationships[$relationship]->getForm($reportForm))) {
                        continue;
                    }
                    if ($pivot_data['pivotForm'] != $form) {
                        continue;
                    }
                }
                if (!array_key_exists($merge.$reportformfield,$pivots)) {
                    $pivots[$merge.$reportformfield] = array();
                }
                $pivots[$merge.$reportformfield][] = $pivot_data;
            }
            if (!$formFieldConfig->is_parent("merges")) {
                continue;
            }
            foreach ($formFieldConfig->merges as $mergekey=>$mergeConfig) {
                $enabled = false;
                $mergeConfig->setIfIsSet($enabled,'enabled');
                if (!$enabled) {
                    continue;
                }
                $merge_report = false;
                $reportConfig->setIfIsSet($merge_report,"reporting_forms/$form/fields/$field/merges/$mergekey/report");
                if (!I2CE_MagicDataNode::checkKey($merge_report)) {
                    continue;
                }
                if (in_array($merge_report,$this->seen_merges)) {
                    //don't allow report to be merged in more than once
                    continue;
                }
                $this->seen_merges[] = $merge_report;

                if ( ! ($merge_report_config = I2CE::getConfig()->traverse("/modules/CustomReports/reports/$merge_report")) instanceof I2CE_MagicDataNode) {
                    continue;
                }
                if (!array_key_exists($relationship,$this->relationships)) {
                    try {
                        $this->relationships[$relationship] = new I2CE_FormRelationship($relationship);
                    }   catch(Exception $e) {
                        I2CE::raiseError("Invalid relationship $relationship");
                    }
                }

                $child_ref = "$mergekey:$merge_report:";
                $this->_getPivots($mergeConfig,$pivots,$pivotable_views,$report_config,$child_ref);
            }
        }
         
    }
    

    protected $displayedFields;




    /**
     *Gets the data about the fields we are going to display.
     */
    public function getDisplayFieldsData() {
        if (is_array($this->displayedFields)) {
            return $this->displayedFields;
        }
        if (array_key_exists('displayFields',$this->defaultOptions)) {
            $displayFields = $this->defaultOptions['displayFields'];
        } else {
            $displayFields = array();
        }
        $total = 'Total';
        I2CE::getConfig()->setIfIsSet($total,"/modules/CustomReports/text/headers/count");
        if (!is_array($displayFields) || count($displayFields) == 0) { //display field information has not been explicitly set
            $fieldData = $this->getReportViewDisplayedFields(false); //don't get disabled fields.
            $has_total = $this->defaultOptions['total'];
            if ($has_total) {
                $fieldData['total'] = array('header'=>$total,'link'=>false, 'link_append'=>false, 'link_type'=>'link','target'=>false);
            }
        } else { //we explicility set some fields
            $t_fieldData = $this->getReportViewDisplayedFields(true,true); // get disabled fields.
            $fieldData = array();
            $has_total = false;
            foreach ($displayFields as $displayFieldData) {
                $reportformfield = $displayFieldData['formfield'];
                if (!$reportformfield) {
                    continue;
                }
                if ($reportformfield == 'total') {
                    $fieldData['total'] = array('header'=>$total,'link'=>false, 'link_append'=>false, 'target'=>false, 'link_type'=>'link');
                    continue;
                }
                if (array_key_exists( 'aggregate', $displayFieldData ) && $displayFieldData['aggregate']) {
                    $reportformfield .= '+' . $displayFieldData['aggregate'];
                }
                $t_field = $reportformfield;
                while( $t_field && 
                        !array_key_exists( $t_field, $t_fieldData ) ) {
                    $t_field = substr( $t_field, 0, strrpos( $t_field, '+' ) );
                }
                if (!array_key_exists($t_field,$t_fieldData)) {
                    $fieldData[$reportformfield] = false;
                } else {
                    $fieldData[$reportformfield] = $t_fieldData[$t_field];
                }
            }
        }
        $this->displayedFields = $fieldData;
        return  $this->displayedFields;
    }




    protected function getReportViewDisplayedFields($getDisabled = false, $all_aggregates = false) {
        $field_list = array();
        $this->seen_merges =array($this->config->report);
        $this->_getReportViewDisplayedFieldsWalker($getDisabled,$all_aggregates,$this->config,$field_list,$this->reportConfig->getName(),'');
        return $field_list;
    }
    
    protected function _getReportViewDisplayedFieldsWalker($getDisabled , $all_aggregates, $baseConfig,&$field_list, $parent_report,$parent_ref) {
        if ( !$parent_report || !($reportConfig = I2CE::getConfig()->traverse("/modules/CustomReports/reports/$parent_report")) instanceof I2CE_MagicDataNode) {
            return;
        }
        if (!$baseConfig instanceof I2CE_MagicDataNode || !$baseConfig->is_parent("fields")) {
            return ;
        }
        $fieldBaseConfig = $baseConfig->fields;
        $sub_field_list = array();
        $displayedFields = array();
        $seen_forms = array();
        $headers = array(
            'sum'=>'Sum',
            'maximum'=>'Maximum',
            'minimum'=>'Minimum',
            'average'=>'Average',                
            'count_distinct'=>'Total Distinct',                
            'count'=>'Total'               
            );
        if ( ($headerConfig = I2CE::getConfig()->traverse("/modules/CustomReports/text/headers")) instanceof I2CE_MagicDataNode) {
            foreach ($headers as $k=>&$v) {
                $headerConfig->setIfIsSet($v,$k);
            }
        }

        foreach ($fieldBaseConfig as $formfield=>$formFieldConfig) {             //cyclying on feilds in report view
            if (!$formFieldConfig instanceof I2CE_MagicDataNode) {
                continue;
            } 

            list($form,$field_agg) = array_pad(explode('+',$formfield),2,'');            
            list($field,$aggregate) =  array_pad(explode('+',$field_agg),2,'');           
            $link = false;
            $link_append = '';
            $link_target = false;
            $link_type = 'link';
            $rep_enabled = false;
            if (!$field_agg && $form == $formfield) { //this is an internal field and the header is stored in
                $rep_enabled = true;
                $header =  I2CE_MagicDataExport_Template::humanText($formfield);
                $reportConfig->setIfIsSet($header,"reporting_internals/$formfield/header");
            } elseif (!$form)  { //this is  function field and the name of the field is stored in 
                $header =  I2CE_MagicDataExport_Template::humanText($field);
                $reportConfig->setIfIsSet($header,"reporting_functions/$field_agg/header");
                $reportConfig->setIfIsSet($rep_enabled,"reporting_functions/$field_agg/enabled");
            } else { //this is a reportform+field
                $header = I2CE_MagicDataExport_Template::humanText($form) . ' ' . I2CE_MagicDataExport_Template::humanText($field); 
                $reportConfig->setIfIsSet($header,"reporting_forms/$form/fields/$field_agg/header");
                $reportConfig->setIfIsSet($link,"reporting_forms/$form/fields/$field_agg/link");            
                $reportConfig->setIfIsSet($link_append,"reporting_forms/$form/fields/$field_agg/link_append");            
                $reportConfig->setIfIsSet($link_type,"reporting_forms/$form/fields/$field_agg/link_type");            
                $reportConfig->setIfIsSet($link_target,"reporting_forms/$form/fields/$field_agg/target");            
                $reportConfig->setIfIsSet($rep_enabled,"reporting_forms/$form/fields/$field_agg/enabled");
            }
            if ( !$field_agg && $form == $formfield ) {
                $aggs = array('');
                $displayedField = $formfield;
            } elseif ($all_aggregates === true) {
                $aggs = array('sum','maximum','minimum','average','count','count_distinct','');
                $displayedField = $form . '+' . $field;
            } elseif ( is_array( $all_aggregates ) ) {
                $aggs = $all_aggregates;
                $displayedField = $form . '+' . $field;
            } else { // get default aggregate stuff
                if ( !$aggregate ) {
                    $aggreate = '';
                    $formFieldConfig->setIfIsSet( $aggregate, "aggregate" );
                }
                if ( $aggregate == 'none' ) {
                    $aggregate = '';
                }
                $aggs = array($aggregate);
                $displayedField = $formfield;
            }
            if ($getDisabled) {
                $enabled = true;
            } else {
                $enabled = false;
                $formFieldConfig->setIfIsSet($enabled,"enabled");
            }
            $enabled &= $rep_enabled;
            if ($enabled) {
                if ($form && $form != $formfield) {
                    if (!array_key_exists($form,$seen_forms)) {
                        $seen_forms[$form] = array('parent'=>false,'id'=>false);
                    }
                    if ($field == 'id') {
                        $seen_forms[$form]['id'] = true;
                    } else if ($field == 'parent') {
                        $seen_forms[$form]['parent'] = true;
                    }
                }
                foreach ($aggs as $agg) {
                    $t_link = false;
                    $t_link_append = false;
                    $t_link_type = false;
                    $t_link_target = false;
                    $t_header = $header;
                    $t_field = $displayedField;
                    if (array_key_exists($agg,$headers)) {
                        $t_header .= ' (' . $headers[$agg] . ')';
                    } else {
                        $agg = '';
                        $t_link = $link;
                        $t_link_append = $link_append;
                        $t_link_type = $link_type;
                        $t_link_target = $link_target;
                    }
                    if ($agg) {
                        $t_field .= '+' . $agg;
                    }
                    $displayedFields[$parent_ref . $t_field] = array('header'=>$t_header,'link'=>$t_link, 'target'=>$t_link_target, 'link_append'=>$t_link_append, 'link_type'=>$t_link_type);
                }
            } else if (!array_key_exists($displayedField,$displayedFields)) {
                $displayFields[$parent_ref . $displayedField] = false;
            }
            $additional = false;
            $add_reportfield = false;
            if ($formFieldConfig->setIfIsSet($additional,'merge_additional')) {
                // something like  /$report_view/fields/primary_form+job_cadre  or /$report_views
                $additional = explode('/',ltrim($additional,'/'));
                if (count($additional) == 3) {
                    //it is a report view
                    $add_reportfield = array_pop($additional);
                    //array_pop($additional); //fields
                    //array_pop($additional); //reportview
                    $add_merge ='primary_table:';
                } else if (count($additional) > 3) {
                    $add_reportfield = array_pop($additional); 
                    array_pop($additional);
                    $add_merge = array_pop($additional)  .':';
                }
            }
            if ($add_reportfield) {
                if (!array_key_exists($displayedField, $displayedFields)) {
                    $displayedFields[$parent_ref. $displayedField] = false;
                }
                if (!array_key_exists($add_merge. $add_reportfield, $displayedFields)) {
                    $displayedFields[$add_merge. $add_reportfield] = false;
                }
            }
            if (!$formFieldConfig->is_parent("merges")) {
                continue;
            }
            $sub_field_list[$displayedField] = array();           
            foreach ($formFieldConfig->merges as $mergekey=>$mergeConfig) {
                $enabled = false;
                $mergeConfig->setIfIsSet($enabled,'enabled');
                if (!$enabled) {
                    continue;
                }
                $merge_report = false;
                $reportConfig->setIfIsSet($merge_report,"reporting_forms/$form/fields/$field/merges/$mergekey/report");
                if (!I2CE_MagicDataNode::checkKey($merge_report)) {
                    continue;
                }
                if (in_array($merge_report,$this->seen_merges)) {
                    //don't allow report to be merged in more than once
                    continue;
                }
                $this->seen_merges[] = $merge_report;

                if ( ! ($merge_report_config = I2CE::getConfig()->traverse("/modules/CustomReports/reports/$merge_report")) instanceof I2CE_MagicDataNode) {
                    continue;
                }
                $child_ref = "$mergekey:$merge_report:";
                $this->_getReportViewDisplayedFieldsWalker($getDisabled , $all_aggregates,$mergeConfig,$sub_field_list[$displayedField], $merge_report,$child_ref);
            }
        }
        foreach ($seen_forms as $form=>$seen) {
            if (!$seen['id'])  {
                $displayedFields[$parent_ref . "$form+id"] = false;
            } 
            if (!$seen['parent']) {
                $displayedFields[$parent_ref . "$form+parent"] = false;
            }
        }
        $displayOrder = '';
        if (array_key_exists('display_order',$this->defaultOptions) && $this->defaultOptions['display_order']) {
            $displayOrder = $this->defaultOptions['display_order'];
        } else {
            $baseConfig->setIfIsSet($displayOrder,'display_order');
        }
        $displayOrder = explode(',',$displayOrder);
        foreach ($displayOrder as $field) {
            if (!$field) {
                continue;
            }
            $field = $parent_ref . $field;
            foreach ($displayedFields as $fieldName=>$details) {
                if (strpos($fieldName,$field) !== 0) {                    
                    continue;
                }
                if ( (strlen($fieldName) == strlen($field)) || $fieldName[strlen($field)] == '+') {
                    $field_list[$fieldName] = $details;
                    unset($displayedFields[$fieldName]);
                    if (array_key_exists($displayedField,$sub_field_list)) {
                        $field_list = array_merge($field_list,$sub_field_list[$displayedFields]);
                        unset($sub_field_list[$displayedFields]);
                    }

                }
            }
        }
        while (count($displayedFields) > 0) {
            reset($displayedFields);
            $key =key($displayedFields);
            $details = array_shift($displayedFields);
            $field_list[$key] =$details;
            if (array_key_exists($displayedField,$sub_field_list)) {
                $field_list = array_merge($field_list,$sub_field_list[$displayedFields]);
                unset($sub_field_list[$displayedFields]);
            }
        }
        while (count($sub_field_list) > 0) {
            $field_list =array_merge($field_list,array_shift($sub_field_list));
        }
    }





  }
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
