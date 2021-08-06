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
*  I2CE_CustomReport
* @package I2CE
* @subpackage Core
* @author Carl Leitner <litlfred@ibiblio.org>
* @version 2.1
* @access public
*/


class I2CE_CustomReport extends I2CE_Fuzzy{
    
    /**
     * The magic data node holding the configuration information for this report
     * $var protected I2CE_MagicDataNode $config
     */
    protected $config;



    /**
     * $var protected string $table the name of the cached  table of the report
     */
    protected $table;

    /**
     * $var protected string $tmp_table the temporary name of the cached  table of the report
     */
    protected $tmp_table;

    /**
     * @var protected PDO $db The instance of the database to perform queries on.
     */
    protected $db;
    

    /**
     * @var protected array $populate_queries   an array of queries used to populate the cached reports table(s)
     */
    protected $populate_queries;

    /**
     * @var protected integer The length for the id and parent columns in cached report tables.
     */
    protected $id_length;


    /**
     * The constructor
     * @param string $report  The report name
     */
    public function __construct($report ) {
        $this->report = $report;
        if (!$this->reportExists($report)) {
            $msg = "The requested report $report does not exist";
            I2CE::raiseError($msg);
            throw new Exception($msg); 
        }
        $this->id_length = 255;
        I2CE::getConfig()->setIfIsSet( $this->id_length, "/modules/CustomReports/id_varchar_length" );
        $this->config = I2CE::getConfig()->traverse("/modules/CustomReports/reports/$report");
        $this->relationship = '';
        $this->config->setIfIsSet($this->relationship,'relationship');
        if (!$this->relationship) {
            $msg = "The requested report $report does not have a relationship";
            I2CE::raiseError($msg);
            throw new Exception($msg); 
        }
        $this->db = I2CE::PDO();
        $this->table = self::getCachedTableName($report,true);
        $this->populate_queries = array();
        $qry = "SELECT * FROM `information_schema`.`COLUMNS` WHERE `TABLE_SCHEMA` = '" . I2CE_CachedForm::getCacheDatabase() .
            "' AND `TABLE_NAME` =  ? AND `COLUMN_NAME` = ?";
        try {
            $this->get_field_def = $this->db->prepare($qry);
        } catch ( PDOException $e ) {
            I2CE::pdoError( $e, "Unable to prepare field definition query" );
            throw new Exception($msg); 
        }
        //this might throw an exception.  don't catch it here.
        $this->rel = new I2CE_FormRelationship($this->relationship, '/modules/CustomReports/relationships', array('I2CE_CachedForm','getCachedTableName'));
    }


    public function getFormRelationship() {
        return $this->rel;
    }

    /**
     * @var protected PDOStatement $get_field_def
     */
    protected $get_field_def;
    

    protected static $report_table_cols = array();

    /**
     * Get the actual columns in the cached report table
     */
    public static function getColumnsInReportTable($report) {
        if (array_key_exists($report, self::$report_table_cols) && is_array(self::$report_table_cols[$report])) {
            return self::$report_table_cols[$report];
        }
        $cols_qry = 
            "SELECT column_name FROM information_schema.columns WHERE "
            . " CONCAT('`',TABLE_SCHEMA,'`.`',TABLE_NAME , '`') = '" . self::getCachedTableName($report,true) . "'";
        try {
            $db = I2CE::PDO();
            $res = $db->query($cols_qry);
            $cols_in_report = array();
            while ($row = $res->fetch()) {
                $cols_in_report[] = $row->column_name;
            }
            self::$report_table_cols[$report] = $cols_in_report;
            return self::$report_table_cols[$report];
        } catch( PDOException $e ) {
            I2CE::pdoError($e,"Could not get columns in report " .  self::getCachedTableName($report,true));
            return array();
        } 
   }


    /**
     * Gets the forms required by the report
     * @param string $report
     * @returns array of string
     */
    public function getFormsRequiredByReport() {
        if (!$this->rel instanceof I2CE_FormRelationship) {
            return array();
        }
        return $this->rel->getRequiredForms();
    }

    /**
     * Below is the static code to actually handle checking/setting the status of a cached report
     * 
     */


    public static function getCachedTableName($report,$withDB = true, $table_prefix = '', $counter = false) {
        $db_name = '';
        $DBConfig = I2CE::getConfig()->modules->CustomReports->database_options;
        if ($withDB) {
            $DBConfig->setIfIsSet($db_name,'database');
            if (strlen($db_name) > 0) {
                if ($db_name[strlen($db_name) -1] !== '.') {
                    $db_name = '`' . $db_name . '`.';
                } else {
                    $db_name = '`' . substr($db_name,0,-1) . '`.';
                }            
            } else {
                $db_name = '`'  . I2CE_PDO::details('dbname') . '`.';
            }
        }        
        if (!$table_prefix) {
            $table_prefix = '';
            $DBConfig->setIfIsSet($table_prefix,'table_prefix');
        }
        if (strlen($table_prefix) > 0) {
            if ($table_prefix[strlen($table_prefix)-1] !== '_') {
                $table_prefix .= '_';
            }
        }
        if ($counter) {
            return $db_name . '`' .   $table_prefix  . $report . '[' . $counter . ']' .'`';
        } else {
            return $db_name . '`' .   $table_prefix . $report  . '`';
        }
    }


    /**
     *Get all available report
     */
    public static function  getReports() {
        return I2CE::getConfig()->getKeys("/modules/CustomReports/reports");
    }

    /**
     * Checks to see if a report exists
     * @param string $report
     * @returns boolean
     */
    public static function  reportExists($report) {
        if (!I2CE_MagicDataNode::checkKey($report)) {
            return false;
        }
        return I2CE::getConfig()->__isset("/modules/CustomReports/reports/$report");
    }


    /**
     * Checks to see if a report has failed in its generation
     * @param string $report
     * @returns mixed.  True if it has failed, false if it has not failed, null if the report does not exist.
     */
    public static function hasFailed($report) {
        if (!self::reportExists($report)) {
            return null;
        }
        $status =  self::getStatus($report);
        if ($status === 'failed') {
            return true;
        }
        if (($status !== 'in_progress') || ($status !== 'stale')) {
            return false;
        }
        $timeConfig = I2CE::getConfig()->traverse('/modules/CustomReports/times',true);        
        $timeConfig->volatile(true);
        $fail_time  = null;
        $timeConfig->setIfIsSet($fail_time,'fail');
        if (!is_integer($fail_time)) {
            $fail_time = 600;
        }
        $fail_time = (int)( ((int)$fail_time) * 60);        
        $begin_generation = self::getLastGenerationTime($report);
        if (  (time() - ((int) $begin_generation) ) >   $fail_time)  {
            self::setStatus($report,'failed');
            return true;
        }
        return false;        
    }

    
    protected static $hooman = array();
    protected static function hooman($key) {
        if (count(self::$hooman) == 0) {
            self::$hooman = array(
                'does_not_exist'=>'Does not exist',
                'not_generated'=>'Not generated',
                'stale'=>'Stale',
                'failed'=>'Failed',
                'in_progress'=>'In Progress',
                'generated'=>'Generated'
                );
            if ( ($statusConfig = I2CE::getConfig()->traverse("/modules/CustomReports/text/report_status")) instanceof I2CE_MagicDataNode) {
                foreach (self::$hooman as $k=>&$v) {
                    $statusConfig->setIfIsSet($v,$k);
                }
            }
        }
        if (array_key_exists($key,self::$hooman)) {
            return self::$hooman[$key];
        } else {
            return $key;
        }
    }

    /**
     * Get the status of the requested report
     * @param string $report
     * @param boolean hooman_readable defaults to false.
     * @returns string 'does_not_exist','not_generated','generated','failed','in_progress', 'stale' if it is not hooman readable
     **/
    public static  function getStatus($report,$hooman_readable=false) {
        if (!self::reportExists($report)) {
            if ($hooman_readable) {
                return self::hooman('does_not_exist');
            } else {
                return 'does_not_exist';
            }
        }
        $status = '';
        I2CE::getConfig()->modules->CustomReports->status->volatile(true);
        I2CE::getConfig()->setIfIsSet($status, "/modules/CustomReports/status/$report");
        if ($status) {
            if ($hooman_readable) {
                return self::hooman($status);
            } else {
                return $status;
            }
        } else {
            self::setStatus($report,'not_generated');
            if ($hooman_readable) {
                return self::hooman('not_generated');
            } else {
                return 'not_generated';
            }
        }
    }

    /**
     * Set the status us a report.
     * @param string $report
     * @param string $status
     */
    public static function setStatus($report,$status) {
        if (!self::reportExists($report)) {
            return;
        }
        I2CE::getConfig()->__set("/modules/CustomReports/status/$report", $status);
    }

    /**
     * Check to see if a report is stale.
     * @param string $report
     * @returns boolean true/false 
     */
    public  static function isStale($report) {
        if (!self::reportExists($report)) {
            return true;
        }
        $status =  self::getStatus($report);
        if ($status === 'stale' || $status == 'failed' || $status == 'not_generated') {
            return true;
        }
        if ($status === 'in_progress') {
            return false;
        }

        $generation_time = self::getLastGenerationTime($report);
        if ($generation_time === null || $generation_time <= 0) {
            return true;
        }
        if ($generation_time > time()) { //make sure there is no time sync. problem
            return true;
        }
        //status is not in progress
        $timeConfig = I2CE::getConfig()->traverse('/modules/CustomReports/times',true);        
        $timeConfig->volatile(true);
        $stale_time  = 10;
        if ($timeConfig->setIfIsSet($stale_time,'stale')) {        
            if (is_integer($stale_time) || (is_string($stale_time) && ctype_digit($stale_time))) {
                if ($stale_time <= 0) {
                    return true;
                }
            } else {
                $stale_time = 10;
            }
        }
        //lookup per-reprot stale time and override if  necc.
        $t_stale_time = null;
        if ($timeConfig->setIfIsSet($t_stale_time,"stale_by_report/$report")) {
            if (is_integer($t_stale_time) ||  (is_string($stale_time) && ctype_digit($stale_time))) {
                if ( $t_stale_time > 0 ) {
                    $stale_time = $t_stale_time;
                } else {
                    return true;
                }
            }
        }
        $stale_time = $stale_time * 60; //convert to minutes
        return  (($generation_time + $stale_time) < time()); 
    }


    /**
     *@param string $report The shortname for the report
     *@return mixed.  False on failure, int the time the last report generation  on sucess
     */
    public static function getLastGenerationTime($report) {
        if (!self::reportExists($report)) {
            return false;
        }
        $timeConfig = I2CE::getConfig()->traverse('/modules/CustomReports/times',true);        
        $timeConfig->volatile(true);
        $generation = 0;
        $timeConfig->setIfIsSet($generation,"generation/$report");        
        if (  (!(is_integer($generation) || ctype_digit($generation))) || ((int)$generation) < 1) {
            self::setStatus($report,'not_generated');
            return false;
        }
        return $generation;

    }


    /**
     * Below is the code to actually handle generation of reports
     * 
     */

    /**
     *  Generate the cached report
     * @param boolean $force.  Defaults to false.  If set to true, it will force the regeneration of the report if it is in_progress
     * @param boolean $cache_forms.  Defaults to True.  If set to true, it will cache the forms required by this report
     */
    public function generateCache($force=false,$cache_forms = true) {
        if (!self::reportExists($this->report)) {
            return false;
        }
        if ((!$force) && $this->getStatus($this->report) == 'in_progress') {
            I2CE::raiseError("Cached report in progress for {$this->report} and generation is not forced");
            return false;
        }
        if (!$force && !self::isStale($this->report)) {
            I2CE::raiseError("Report {$this->report} is not stale");
            //report is not stale
            return true;
        }
        $this->setStatus($this->report,'in_progress');
        if ($cache_forms) {
            $forms = $this->getFormsRequiredByReport();
            I2CE::raiseError("Attempting to cache the required forms for the report {$this->report}:\n\t" . implode(',', $forms));
            $failures = array();
            foreach ($forms as $form) {
                try {
                    $cachedForm = new I2CE_CachedForm($form);
                }
                catch(Exception $e) {
                    if (array_key_exists('HTTP_HOST',$_SERVER)) { //we don't need to check here, b/c it won't error out.  we are doing it to keep the log file clean
                        $msgs = array( 'not_cached'=>'Unable to setup cached form');
                        foreach ($msgs as $k=>&$v) {
                            I2CE::getConfig()->setIfIsSet($v,"/modules/CustomReports/text/user_messages/$k");
                        }                        
                        $this->userMessage ( $msgs['not_cached'] . ": $form");
                    }
                    $failures[] = $form;
                    continue;
                }
                if (!$cachedForm->generateCachedTable(!$force)) {
                    $failures[] = $form;
                    continue;
                }
            }
            if (count($failures) > 0) {
                I2CE::raiseError("Warning data may be out of date for report {$this->report} -- could not cache forms:\n\t" . implode(',' ,$failures));
            } else {
                I2CE::raiseError("Cached all forms");
            }
        }
        $timeConfig = I2CE::getConfig()->traverse('/modules/CustomReports/times',true);        
        $timeConfig->volatile(true);
        $timeConfig->generation->{$this->report} = time();
        if (!$this->_generateCache()) {
            $this->setStatus($this->report,'failed');
            I2CE::raiseError("Report generation failed for {$this->report}");
            return false;
        }
        I2CE::raiseError("Report generation succeeded for {$this->report}");
        $this->setStatus($this->report,'generated');
        $timeConfig->generation->{$this->report} = time();
        return true;
    }
    

    /**
     *Drop the zebra_XXX table
     */
    public function dropTable($update_status = true) {
        if ($update_status) {
            self::setStatus($this->report,'not_generated');
        }
        try {
            $this->db->exec("DROP TABLE IF EXISTS {$this->table}");
        } catch ( PDOException $e ) {
            I2CE::pdoError($e, "Could not drop old table {$this->table}"); 
            return false;
        }
        return true;
    }


    protected function _generateCache() {
        if (! ($last_tmp_table= $this->setupQueries())) {
            I2CE::raiseError("Could not process form tree for report {$this->report}");
            return false;
        } 
        $counter = count( $this->populate_queries );
        $md5_key = 'no_md5';
        foreach( $this->populate_queries[$counter] as &$query ) {
            if ( is_array( $query ) ) {
                $query = $query[$md5_key];
            }
        }
        unset($query);
        if ( array_key_exists( 'update_set_md5', $this->populate_queries[$counter] ) ) {
            unset( $this->populate_queries[$counter]['update_set_md5'] );
        }
        $replace = false;
        while ( $counter && array_key_exists( $counter, $this->populate_queries ) ) {
            if ( $counter > 1 ) {
                $md5_key = 'no_md5';
                if ( array_key_exists( 'insert_limit', $this->populate_queries[$counter] ) || 
                        array_key_exists( 'insert_nolimit', $this->populate_queries[$counter] ) ) {
                    $md5_key = 'md5';
                } else {
                    if ( array_key_exists( 'update_set_md5', $this->populate_queries[$counter-1] ) ) {
                        unset( $this->populate_queries[$counter-1]['update_set_md5'] );
                    }
                }

                foreach( $this->populate_queries[$counter-1] as &$query ) {
                    if ( is_array( $query ) ) {
                        $query = $query[$md5_key];
                    }
                }
                unset($query);
                if ( array_key_exists( 'update', $this->populate_queries[$counter] ) ) {
                    $this->populate_queries[$counter-1]['create'] = $this->populate_queries[$counter]['create'];
                    $this->populate_queries[$counter-1]['drop'] = $this->populate_queries[$counter]['drop'];
                    //$this->populate_queries[$counter-1]['drop_prev'] = $this->populate_queries[$counter]['drop_prev'];
                    unset( $this->populate_queries[$counter]['create'] );
                    unset( $this->populate_queries[$counter]['drop'] );
                    unset( $this->populate_queries[$counter]['insert_copy'] );
                    //unset( $this->populate_queries[$counter]['drop_prev'] );

                    if ( !$replace ) {
                        $replace = '[' . $counter . ']`';
                    }
                    $find = '[' . ($counter-1) . ']`';
                    foreach( $this->populate_queries[$counter-1] as $type => &$query ) {
                        if ( $type != 'drop_prev' ) {
                            $query = str_replace( $find, $replace, $query );
                        }
                    }
                    unset($query);
                } else {
                    $replace = false;
                }
            }
            $counter--;
        }
        foreach ($this->populate_queries as $qry_list) {
            foreach( $qry_list as $type => $qry ) {
                /*
                if ( $type == 'drop_prev' ) {
                    I2CE::raiseMessage( "Skipping $qry" );
                    continue;
                }
                */

                try {
                    I2CE::raiseError("Doing $type: $qry");
                    $start_time = time();
                    $res = $this->db->exec($qry);
                    $end_time = time();
                    if (substr(ltrim($qry),0,6) == 'INSERT' || substr(ltrim($qry),0,6) == 'UPDATE') {
                        I2CE::raiseMessage("Inserted $res rows");
                    }
                    I2CE::raiseMessage("Query took " . ($end_time-$start_time) . " seconds.");

                } catch ( PDOException $e ) {
                    I2CE::pdoError($e, "Unable to populate cached report");
                    return false;
                }
            }
        }
        // we move over the tmp_table to table.
        if (!$this->dropTable(false)) {
            return false;
        }
        try {
            $this->db->exec("RENAME TABLE {$last_tmp_table} TO {$this->table}");
        } catch ( PDOException $e ) {
            I2CE::pdoError($e, "Could not rename temp table");
            return false;
        }
        return true;
    }

    protected $reportedFunctions = null;

    protected function getReportedFunctions() {
        if (is_array($this->reportedFunctions)) {
            return $this->reportedFunctions;
        }
        $this->reportedFunctions = array();
        if (!$this->config->is_parent('reporting_functions')) {
            return $this->reportedFunctions;
        }
        $enabled = array();
        foreach ($this->config->reporting_functions as $funcName=>$funcConfig) {
            if (!$funcConfig instanceof I2CE_MagicDataNode) {
                continue;
            }
            if (!$funcConfig->is_scalar("enabled") || ! $funcConfig->enabled) {
                continue;
            }
            $enabled[] = $funcName;
        }
        if (count($enabled) == 0) {
            return $this->reportedFunctions;
        }
        $this->reportedFunctions = $this->rel->getFunctionDetails($enabled);
        return $this->reportedFunctions;
    }
    
    /**
     * Return the named as value for a select query.
     * e.g. `demographic`.`id` AS `demographic+id` would return
     * `demographic+id`
     * @param string $select
     * @return string
     */
    public static function getSingleQueryAsName( $select ) {
        $find_as = strripos( $select, ' AS ' );
        if ( $find_as !== false ) {
            return substr( $select, $find_as+4 );
        } else {
            return $select;
        }
    }

    /**
     * Return the array or string with the portion before the
     * ' AS ' removed.
     * @param mixed $selects
     * @return mixed
     */
    public static function getQueryAsName( $selects ) {
        if ( is_array( $selects ) ) {
            return array_map( array("self","getSingleQueryAsName"), $selects );
        } elseif ( is_string( $selects ) ) {
            return self::getSingleQueryAsName( $selects );
        }
    }



    protected function setupQueries() {
        $formConfigs = $this->rel->getFormConfig();
        $parentConfigs = $this->rel->getParentFormNames();
        $primaryForm = $this->rel->getPrimaryForm();
        $primaryFormReference = $this->rel->getReferencedForm($primaryForm);
        if (!$primaryFormReference) {
            I2CE::raiseError("Could not get a reference for the primary form $primaryForm");
            return false;
        }
        $primaryFormName = $this->rel->getPrimaryFormName();
        $create_fields = array(); 
        $create_fields ["last_modified"] = "`last_modified` datetime";
        $create_fields ["primary_form+id"] = "`primary_form+id` varchar(".$this->id_length.") DEFAULT ''";
        $create_fields ["primary_form+parent"] = "`primary_form+parent` varchar(".$this->id_length.")";
        $keys = array("last_md5 BINARY(16) UNIQUE","md5 BINARY(16) UNIQUE",'KEY (`primary_form+id`)','KEY(`last_modified`)' );                    
        //get the required fields for reported functions
        $requiredFields = array();
        $reportingFunctions = $this->getReportedFunctions();
        foreach ($reportingFunctions as $functions=>$data) {
            if (!is_array($data) || !array_key_exists('required_fields',$data) ||!is_array($data['required_fields'])) {
                I2CE::raiseError("Invalid data received for function $functions:\n" . print_r($data,true));
                continue;
            }
            if (!is_array($data) || !array_key_exists('type',$data) || !$data['type']) {
                I2CE::raiseError("Function $functions has no data type specified",E_USER_ERROR);
                continue;
            }
            foreach ($data['required_fields'] as $reportformfield) {
                list($reportform,$field) = explode('+',$reportformfield);
                if (!$reportform || !$field || $field == 'id' ) {
                    continue;
                }
                if (!array_key_exists($reportform,$requiredFields)) {
                    $requiredFields[$reportform] = array();
                }
                $requiredFields[$reportform][$field] = true;
            }
        }
        $formNames = $this->rel->getFormNames();
        $factory = I2CE_FormFactory::instance();
        $counter = 0;
        $prev_tmp_table  = false;
        $curr_tmp_table  = false;
        foreach ($formNames as $formName) {
            $counter++;
            $last_create_fields = $create_fields;
            $form = $this->rel->getForm($formName);
            $formReference = $this->rel->getReferencedForm($form);
            $formObj = $factory->createContainer($form); 
            if (!$formObj instanceof I2CE_Form) {
                I2CE::raiseError("$formName in report references $form which could no be instantiated:" .get_class($formObj));
                return false;
            }
            $parentFormName = $this->rel->getParentFormNames($formName);
            $parentForm = $this->rel->getForm($parentFormName);
            //$parentFormReference = $this->rel->getReferencedForm($parentForm);
            if (!$form || !$parentForm || !$parentFormName || !$formReference ) {
                I2CE::raiseError("Invalid form data at $formName: $form/$parentForm/$parentFormName/$formReference/$parentFormReference");
                return false;
            }
            $limit_fields =    $this->rel->getLimitingFields($formName); //get the fields used in limits.            
            $reporting_fields = array();
            foreach ($this->rel->getChildFormNames($formName) as $childFormName) {
                $reporting_fields = array_merge($reporting_fields,$this->rel->getJoiningFields($childFormName));
            }
            $limit_fields = array_merge( $limit_fields, $reporting_fields );
            if (array_key_exists($formName,$requiredFields)) {
                $reporting_fields = array_merge($reporting_fields,$requiredFields[$formName]);
            }
            if ('primary_form' == $formName) {
                $reportConfig = $this->config->reporting_forms->primary_form;
                if (array_key_exists('primary_form',$requiredFields)) {
                    $reporting_fields = array_merge($reporting_fields,$requiredFields['primary_form']);
                } 
            }else {
                $reportConfig = $this->config->reporting_forms->$formName;
            }
            $reportingFieldsBase = $reportConfig->fields;
            foreach ($reportingFieldsBase  as $field=>$fConfig) {
                if (is_scalar($fConfig)) {
                    continue;
                }
                if (!isset($fConfig->enabled)) {
                    continue;
                }
                //now check that this field is actual in the form
                $fieldObj = $formObj->getField($field);
                if ($fConfig->enabled) {                    
                    $reporting_fields[$field]=true;
                    continue;
                }
                if (!$fConfig->is_parent('limits')) {
                    continue;
                }
                foreach ($fConfig->limits as $limit=>$data) {
                    if ((!$data instanceof I2CE_MagicDataNode)  || !$data->is_scalar('data') || !$data->enabled) {
                        continue;
                    }
                    $reporting_fields[$field] = true;  
                }
            }    
            //now we go through all forms looking for link appends that refer to this form.
            foreach ($this->config->reporting_forms as $linkFormName=>$linkConfig) {
                foreach ($linkConfig->fields as $linkField=>$fConfig) {
                    if (!(isset($fConfig->link_append) && $fConfig->is_scalar('link_append') &&  ($fConfig->link_append))) {
                        continue;
                    }
                    if (strpos($fConfig->link_append,'+') !== false) {
                        list($link_form,$link_field) = explode('+', $fConfig->link_append);
                    } else {
                        $link_form = $linkFormName;
                        $link_field = $fConfig->link_append;
                    }
                    if (! (($link_form == $formName) || ($link_form == 'primary_form' && $formName == $primaryFormName) )) {
                        continue;
                    }
                    if ($link_field && $link_field == 'id') {
                        continue;
                    }
                    $reporting_fields[$link_field] = true;   
                }                
            }
            $compact_report = false;
            if ( $this->config->is_scalar('compact_report') && $this->config->compact_report ) {
                $compact_report = true;
            }
            if ( !$compact_report || (count($reporting_fields) > 0 && count($limit_fields) > 0 ) ) {
                $reporting_fields = array_merge( $reporting_fields, $limit_fields );
            }
            $updates = array();
            $as = array();
            if ($formName != 'primary_form') {
                if ( $compact_report && count($reporting_fields) == 0 ) {
                    $counter--;
                    continue;
                }
                $as[ "`{$formName}+id`"] = "`$formName`.id  as `{$formName}+id`";
                $as[ "`{$formName}+parent`"] = "`$formName`.parent  as `{$formName}+parent`";
                $as[ "`last_modified`" ] = "GREATEST(IFNULL(base_table.last_modified,'1900-01-01 00:00:00'), IFNULL(`$formName`.last_modified,'1900-01-01 00:00:00')) as last_modified";
                //$as[ "`last_modified`" ] = "GREATEST(base_table.last_modified,`$formName`.last_modified) as last_modified";
                $create_fields["$formName+id"] = "`{$formName}+id` varchar(".$this->id_length.") DEFAULT ''";
                $create_fields["$formName+parent"] = "`{$formName}+parent` varchar(".$this->id_length.")";
                //the next line is just to be safe. i think we only need this in the case that $parentFormName == $primaryFormName
                //$updates[] = "`{$parentFormName}+id` = values(`{$parentFormName}+id`)"; 
                $updates[] = "`{$formName}+id` = values(`{$formName}+id`)"; 
                $updates[] = "`{$formName}+parent` = values(`{$formName}+parent`)"; 
            } else {
                $as[ "`last_modified`" ] = "`$parentForm`.last_modified as `last_modified`";
                $as[ "`primary_form+id`"] = "`$parentForm`.id as `primary_form+id`";
                $as[ "`primary_form+parent`"] = "`$parentForm`.parent as `primary_form+parent`";
            }
            


            $fields = array_keys($reporting_fields);

            foreach ($fields as $field) {
                if ($field == 'id') {
                    continue;
                }
                if (!is_string($field) || strlen($field) == 0) {
                    I2CE::raiseError("Invalid field data for $formName");
                    continue;
                }
                $fieldObj = $formObj->getField($field);
                if (!$fieldObj instanceof I2CE_FormField) {
                    I2CE::raiseError("$formName / $form in report references the field $field in $form which could no be instantiated:" .get_class($formObj));
                    continue;
                }
                $name = "$formName+$field";
                if (array_key_exists($name,$as)) {
                    I2CE::raiseError("Reported field $field as $name is duplicated for {$this->report} at $formName");
                    continue;
                }
                $create_field = $this->getCreateField($form,$field, $name);
                if ($create_field === false) {                    
                    I2CE::raiseError("Unable to find field data for $form.$field in {$this->report}");
                    continue;
                } 
                if (in_array($field, $limit_fields)) { //this field is used as a limit so lets try and put an index
                    $create_fields[$name] = $create_field['field'];
                    $type = $create_field['type'];
                    if (preg_match('/^varchar\(([0-9]+)\)$/',$type,$matches)) {
                        $amt = min($matches[1],255);
                        $keys[] = "INDEX `$name` (`$name` ($amt))";
                    }else if (preg_match('/^char\(([0-9]*)\)$/',$type,$matches)) {
                        $amt = min($matches[1],255);
                        $keys[] = "INDEX `$name` (`$name` ($amt))";
                    } else if ($type == 'text') {
                        $keys[] = "INDEX `$name` (`$name` (255))";
                    } else if ($type == 'longtext') {
                        $keys[] = "INDEX `$name` (`$name` (255))";
                    } else if (preg_match('/blob/',$type)) {
                        //do nothing
                    } else if (preg_match('/^set/',$type)) {
                        //do nothing
                    } else if (preg_match('/^enum/',$type)) {
                        //do nothing
                    } else {
                        $keys[] = "INDEX `$name` (`$name`)";
                    }
                } else {
                    //we need to change varchars to text so we can handle really large reports https://bugs.launchpad.net/i2ce/+bug/823965
                    $create_fields[$name] = preg_replace('/varchar\(([0-9]+)\)/','text',$create_field['field']);
                }
                if ($formName !== 'primary_form') {
                    $as['`'.$name.'`'] = "`$formName`.`$field`  AS `$name` ";
                } else {
                    $as['`'.$name.'`'] = "`$form`.`$field`  AS `$name` ";
                }
                if ($formName != $primaryFormName) {
                    $updates[] = "`$name`=values(`$name`)";
                }
            }
            $prev_tmp_table = $curr_tmp_table;
            $curr_tmp_table =  self::getCachedTableName($this->report,true,'tmp_custom_report',$counter);
            $limit_one = false;
            $has_ancestor = false;
            if ($formName == 'primary_form') {
                $join = '';
                $from = " FROM " .  $primaryFormReference . " AS `$primaryForm` ";
                $update_from = ' ' . $primaryFormReference . " AS `$primaryForm` ";
            } else {
                if ($this->rel->isRightJoin($formName)) {
                    $join_style = 'RIGHT JOIN';
                } else  if ($this->rel->isJoin($formName)) {
                    $join_style = 'JOIN';
                } else {
                    $join_style = 'LEFT JOIN';
                }
                $limit_one = $this->rel->limitOne($formName);
                $has_ancestor = $this->rel->hasAncestor($formName);
                $join = $this->rel->getJoin($formName, $join_style);
                if ($join === false) {
                    I2CE::raiseError("Unable to join $formName");
                    return false;
                }
                $from = " FROM  {$prev_tmp_table} AS `base_table` ";
                $update_from = " {$prev_tmp_table} AS `base_table` ";
            } 
            
            $where = $this->rel->generateWhereClause($formName);
            if (!is_string($where)) {
                I2CE::raiseError("Could not get where clause for $formName=>$form for {$this->report}");
                $where = '';
            }  
            $where =trim($where);
            if (strlen($where) > 0) {
                $where = ' WHERE (' . $where . ')';
            }
            $this->populate_queries[$counter]['drop'] = "DROP TABLE IF EXISTS $curr_tmp_table";
            if ( count($keys) > 64 ) {
                I2CE::raiseError( "There are too many indices for this report so stopping at 64 to avoid errors with MySQL.");
                $keys = array_slice( $keys, 0, 64 );
            }
            $this->populate_queries[$counter]['create']  = "CREATE TABLE {$curr_tmp_table} ( "  .  implode(',', array_merge($create_fields,$keys)) . ")  ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin";
            if ($prev_tmp_table) {
                $last_id_fields = array();
                $last_fields = array();
                $last_fields_as = array();
                $this_id = false;
                foreach ($create_fields as $cf=>$cr) {

                    if ( $cf == 'last_modified' ) {
                        continue;
                    }
                    if (substr($cf,-3) == '+id') {
                        if (array_key_exists($cf,$last_create_fields)) {
                            //$last_id_fields[] = 'base_table.`' . $cf .'`';
                            $last_id_fields[] = '`' . $cf .'`';
                        } else {
                            $this_id = '`' . substr($cf,0,-3) .'`.id';
                        }
                    }
                    if (array_key_exists($cf,$last_create_fields)) { 
                        //this field was already created ... should be the same as (something like): if !array_key_exists($cf,$as)
                        $last_fields[] = "`base_table`.`$cf` as `$cf`";
                        $last_fields_as[] = "`$cf`";
                    } 
                }
                $ignore = '';
                $last_index = ", base_table.md5 AS last_md5 ";
                $last_fields_as[] = "last_md5";
                $md5_fields = array();
                foreach ($last_id_fields as $l_f) {
                    //$md5_fields[] = "IF(ISNULL(base_table.$l_f),'',base_table.$l_f)";
                    $md5_fields[] = "base_table.$l_f";
                } 
                //$md5_fields[] = "IFNULL($this_id,'')";
                $md5_fields[] = "$this_id";
                $index = ", UNHEX(MD5(CONCAT_WS(''," . implode(',',$md5_fields) . "))) AS md5 ";
                if ($limit_one) {
                    $ignore = ' IGNORE ';
                }


                if ( stripos( $join, 'SELECT' ) !== false 
                        || $join_style == 'RIGHT JOIN' || !$limit_one || $has_ancestor ) {

                    if ($join_style == 'RIGHT JOIN' || !$limit_one) {
                        $this->populate_queries[$counter]['update_clear_md5'] = 
                            "UPDATE $prev_tmp_table set md5= NULL";
                    }

                    $this->populate_queries[$counter]['insert_join']['md5'] = 
                        "INSERT $ignore INTO {$curr_tmp_table} ( " 
                        . implode( ',', self::getQueryAsName( $last_fields ) ) 
                        . ',' . implode( ',', self::getQueryAsName( $as ) )
                        . ',' . self::getQueryAsName( $last_index ) 
                        . ',' . self::getQueryAsName( $index ) 
                        . " ) SELECT " . implode(',',$last_fields) 
                        ." , " . implode(',',$as)   
                        . $last_index . $index
                        . $from .  $join . $where ; 
                    $this->populate_queries[$counter]['insert_join']['no_md5'] = 
                        "INSERT $ignore INTO {$curr_tmp_table} ( " 
                        . implode( ',', self::getQueryAsName( $last_fields ) ) 
                        . ',' . implode( ',', self::getQueryAsName( $as ) )
                        . ',' . self::getQueryAsName( $last_index ) 
                        . " ) SELECT " . implode(',',$last_fields) 
                        ." , " . implode(',',$as)   
                        . $last_index
                        . $from .  $join . $where ; 


                    if ($join_style == 'LEFT JOIN') {
                        //$this->populate_queries[] = "ALTER TABLE {$curr_tmp_table} ADD  last_md5 VARCHAR(32) UNIQUE";   
                        //$this->populate_queries[] = "UPDATE {$curr_tmp_table} SET last_md5=MD5(CONCAT(" . implode(',',$last_id_fields) . "))"  ;
                        //we need to add in rows from the previous table that were not left joined
                        $as_null = array_keys($as);
                        foreach ($as_null as &$as_n) {
                            if ($as_n == '`last_modified`') {
                                $as_n = " '1900-01-01 00:00:00' AS `last_modified` ";
                            } else if (substr($as_n,-4) == '+id`') {
                                $as_n = "'' AS " . $as_n;
                            } else {
                                $as_n = " NULL AS " .  $as_n;
                            }
                        }
                        unset($as_n);
                        $null_index = "`base_table`.md5";
                        if ($limit_one) {
                            $this->populate_queries[$counter]['insert_limit'] = 
                                "INSERT IGNORE INTO {$curr_tmp_table} ( " 
                                . implode( ',', self::getQueryAsName( $last_fields ) )
                                . ',' . implode( ',', self::getQueryAsName( $as_null ) )
                                . ',md5,last_md5'
                                . " ) SELECT " . implode(',',$last_fields) ." , " . implode(',',$as_null)  .    ',base_table.md5 AS md5, base_table.md5 AS last_md5 FROM ' . $prev_tmp_table . ' AS `base_table` '.
                                'WHERE md5 NOT IN ( SELECT last_md5 FROM ' . $curr_tmp_table . ')';
                        } else {
                            //if not limit one was have to set prev.md5 to null so we don't get a duplicate key error.
                            //however as the md5 field from the previous table as been set to null before the first instert query for the current table was run,
                            //this means that  we are setting current.last_md5 = prev.md5  = null 
                            //thus we can't do the quick thing in the true half of this else clause above.
                            // we need those rows from the preivous table which do not show up in the current table
                            //we are forced to recalculate the "multi-index" for the previous temp table and the current temp table.  we don't do unhex(md5()) to save time as it is only a comparison.
                            //this is slow.
                            $subq_where = array();
                            foreach( $last_id_fields as $idf ) {
                                $subq_where[] = "(`sub_table`.$idf = `base_table`.$idf OR ( `sub_table`.$idf IS NULL AND `base_table`.$idf IS NULL))";
                            }
                            $this->populate_queries[$counter]['insert_nolimit'] = 
                                "INSERT IGNORE INTO {$curr_tmp_table} ( " 
                                . implode( ',', self::getQueryAsName( $last_fields ) )
                                . ',' . implode( ',', self::getQueryAsName( $as_null ) )
                                . ',md5,last_md5'
                                . " ) SELECT " . implode(',',$last_fields) ." , " . implode(',',$as_null)  .    ',base_table.md5 AS md5, base_table.md5 AS last_md5 FROM ' . $prev_tmp_table . ' AS `base_table` ' .
                                 "WHERE NOT EXISTS ( SELECT 1 FROM $curr_tmp_table AS `sub_table` WHERE " . implode(' AND ', $subq_where ) 
                                //"WHERE CONCAT_WS(''," . implode(',',$last_id_fields) . ") NOT IN ( " .
                                //"SELECT CONCAT_WS(''," . implode(',',$last_id_fields) . ")  FROM " . $curr_tmp_table 
                                . ')';
    
                        }
                    }

                } else {
                    if ( strpos( $join, ' ON ' ) !== false ) {
                        list( $update_join, $update_where ) =
                            explode( ' ON ', $join, 2 );
                        if ( strlen($where) > 0 ) {
                            $update_where = $where . " AND ( " . $update_where . " ) ";
                        } else {
                            $update_where = " WHERE " . $update_where;
                        }
                    } elseif ( strlen($where) > 0 ) {
                        $update_where = $where;
                    }
                    $update_join = substr( $update_join, strpos( $update_join, 'JOIN' ) +4 );
                    $update_as = array();
                    foreach( $as as $upd ) {
                        $upd = str_ireplace( ' as ', ' as ', $upd );
                        if ( strpos( $upd, ' as ' ) !== false ) {
                            list ( $upd_value, $upd_field ) = explode( ' as ', $upd );
                            if ( $upd_field == 'last_modified' ) {
                                $upd_field = 'base_table.last_modified';
                            }
                            $update_as[] = "$upd_field = $upd_value";
                        } else {
                            I2CE::raiseError( "Invalid clause (no ' as ') for updating the table: $upd" );
                            die();
                        }
                    }
                    $this->populate_queries[$counter]['insert_copy'] =
                        "INSERT $ignore INTO {$curr_tmp_table} ( "
                        . implode( ',', $last_fields_as ) 
                        . " ) SELECT " . implode( ',', $last_fields ) 
                        . $last_index . $from;
                    $this->populate_queries[$counter]['update'] =
                        "UPDATE $curr_tmp_table AS base_table, $update_join SET "
                        . implode( ',', $update_as )
                        . $update_where;
                    if ($join_style == 'JOIN' ) {
                        $this->populate_queries[$counter]['delete_extra_joined'] = "DELETE FROM {$curr_tmp_table} WHERE ((`{$formName}+id` IS NULL)  OR (`{$formName}+id` = ''))";
                    }
                    $index = preg_replace( '/(,)(.+)AS(.+)/', '$3 = $2', $index );
                    $index = preg_replace( '/`\.id/', '+id`', $index );
                    $this->populate_queries[$counter]['update_set_md5'] =
                        "UPDATE $curr_tmp_table AS base_table SET "
                        . $index;

                }

                if ($index) {
                    //$this->populate_queries[] = "ALTER TABLE {$curr_tmp_table} DROP  last_md5 ";                    
                    //$this->populate_queries[] = "BAD QUERY";                    
                }
                $this->populate_queries[$counter]['drop_prev'] = "DROP TABLE IF EXISTS $prev_tmp_table";
            } else {
                $index = ",NULL AS last_md5, UNHEX(MD5(`$primaryForm`.id)) AS md5 ";
                $index_fields = ',last_md5,md5';
                $this->populate_queries[$counter]['insert_join']['md5'] =
                    "INSERT INTO {$curr_tmp_table} ( " 
                    . implode( ',', array_keys( $as ) )
                    . " $index_fields ) SELECT " . implode(',',$as) . $index 
                    . $from .  $join .  $where ;                
                $this->populate_queries[$counter]['insert_join']['no_md5'] =
                    "INSERT INTO {$curr_tmp_table} ( " 
                    . implode( ',', array_keys( $as ) )
                    . " ) SELECT " . implode(',',$as) 
                    . $from .  $join .  $where ;                
            }
            $drop_empty = 0;
            $reportConfig->setIfIsSet( $drop_empty, "drop_empty" );
            if ( $drop_empty == 1 ) {
                $this->populate_queries[$counter]['drop_empty'] =
                    "DELETE FROM {$curr_tmp_table} WHERE `{$formName}+id` IS NULL OR `{$formName}+id` = ''";
            }
        }
        $agged = false;
        $f_fields = array();
        foreach ($reportingFunctions as $function=>$data) {
            $counter++;
            $prev_tmp_table =  $curr_tmp_table;
            $curr_tmp_table =  self::getCachedTableName($this->report,true,'tmp_custom_report',$counter);
            $groupby = '';
            $c_fields = array_keys($create_fields);
            if ($data['aggregate']) {
                $agg_forms =  preg_split('/\s*,\s*/',$data['aggregate'],-1,PREG_SPLIT_NO_EMPTY);
                //if any form appears in agg_form than any child forms (in the relationship should also be there)
                $child_forms = array();
                foreach ($agg_forms as $agg_form) {
                    $child_forms = array_merge($child_forms,$this->rel->getChildFormNames($agg_form,null));
                }
                $agg_forms = array_unique(array_merge($agg_forms,$child_forms));
                $data['aggregate'] = array( 'last_modified' );                
                foreach ($agg_forms as $agg_form) {
                    foreach ($c_fields as $c_field) {
                        if (strpos($c_field,$agg_form . '+') === 0) {
                            $data['aggregate'][] = $c_field;
                        }
                    }
                }
                //$c_fields = array_diff($c_fields,$data['aggregate']);
                //$data['aggregate'] = array_intersect($data['aggregate'],$data['required_fields']);
                I2CE::raiseError('cf=' . implode(' ',$c_fields));
                I2CE::raiseError('agg=' . implode(' ', $data['aggregate']));
                I2CE::raiseError('rf=' . implode(' ',$data['required_fields']));
                $agg_fields = array_diff($c_fields,array_merge($data['aggregate'],$data['required_fields']));
                I2CE::raiseError('res='. implode(' ',$agg_fields));
                if (count($agg_fields) > 0) {
                    //$groupby = ' GROUP BY `' . implode("`,`" ,$data['aggregate']) . '`';
                    $groupby = ' GROUP BY `' . implode("`,`" , $agg_fields ). '`';
                    if (!$agged) {
                        array_shift($keys); //get rid of last_md5
                        array_shift($keys); //get rid of md5
                        $agged =true;
                    }
                    
                }                
            } else {
                $data['aggregate'] = array();
            }
            $t_fields = array();
            foreach ($c_fields as $f) {                
                $t_fields[$f] = $create_fields[$f];
            }
            if (!$agged) {
                $c_fields[] ='last_md5';
                $c_fields[] ='md5';
            }
            foreach ($c_fields as &$c_field) {
                if (in_array($c_field,$data['aggregate'])) {
                    $c_field = "NULL AS `$c_field`";
                } else {
                    $c_field = "`$c_field`";
                }
            }
            unset($c_field);
            foreach (array_keys($f_fields) as $f_field) {
                $c_fields[] = "`$f_field`";
            }
            $last_select = implode(",", $c_fields);
            $f_fields["+$function"] = "`+$function` {$data['type']}";
            $this->populate_queries[$counter]['drop'] = "DROP TABLE IF EXISTS $curr_tmp_table";
            $this->populate_queries[$counter]['create'] = "CREATE TABLE {$curr_tmp_table} ( "  .  implode(",", array_merge($t_fields,$keys,$f_fields)) . ")  ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin";        
            if ( $groupby != '' ) {
                $this->populate_queries[$counter]['insert_func'] = "INSERT IGNORE INTO {$curr_tmp_table} ( " 
                    . implode( ',', self::getQueryAsName( $c_fields ) )
                    . ",`+$function`"
                    . " ) SELECT $last_select, " .  $data['qry'] . " AS `+$function`" .
                    " FROM {$prev_tmp_table} " . $groupby;
            } else {
                $this->populate_queries[$counter]['insert_copy'] =
                    "INSERT IGNORE INTO {$curr_tmp_table} ( "
                    . implode( ',', self::getQueryAsName( $c_fields ) )
                    . " ) SELECT $last_select FROM {$prev_tmp_table}";
                $this->populate_queries[$counter]['update'] =
                    "UPDATE {$curr_tmp_table} AS base_table SET `+$function` = "
                    . $data['qry'];
            }

            $this->populate_queries[$counter]['drop_prev'] = "DROP TABLE IF EXISTS $prev_tmp_table";
        }
        //$this->populate_queries[] = "ALTER TABLE {$curr_tmp_table} DROP  last_md5 ";                    
        return $curr_tmp_table;
    }




    protected function getCreateField($form,$field,$name) {
        try {
            $this->get_field_def->execute(array(I2CE_CachedForm::getCachedTableName($form,false),$field));
            if ($this->get_field_def->rowCount() != 1) {
                I2CE::raiseError("Unexpected number of rows " . $this->get_field_def->rowCount() . " when determining field defintion for $form.$field");
                return false;
            }
            $result = $this->get_field_def->fetch();
            $field = "`$name` " . $result->column_type . ' ' ;
            if ($result->character_set_name){
                $field .= ' CHARACTER SET ' . $result->character_set_name . ' ';
            } 
            if (isset($result->collation_name)) {
                $field .= ' COLLATE ' . $result->collation_name;
            }
            $val = array('type'=> $result->column_type, 'field'=>$field);
            $this->get_field_def->closeCursor();
        } catch ( PDOException $e ) {
            I2CE::pdoError($e,"Unable to get field defintion for " . I2CE_CachedForm::getCachedTableName($form,false) . ".$field");
            return false;
        }
        return $val;
    }

  

}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
