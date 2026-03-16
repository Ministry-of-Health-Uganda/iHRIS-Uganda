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
*  I2CE_CustomReport_Relationship_Template
* @package I2CE
* @subpackage Core
* @author Carl Leitner <litlfred@ibiblio.org>
* @version 2.1
* @access public
*/


class I2CE_CustomReport_Template extends I2CE_MagicDataTemplate {

    /**
     * An array of requested relationships templates I2CE_CustomReport_Template  indexed by the type and then by  name
     * @protected static array $templates
     */
    protected static $templates = array();



    /**
     * Gets a  template.  Also caches the results
     * @param string $type one of report, relationship or reportView.  Defaults to null meaning we get everything under /modules/CustomReports
     * @param string $name.  Defaults to null meaning we get all the <$type>s of the specified name
     * @returns I2CE_CustomReportTemplate on sucess
     */
    public static function getTemplate($type=null,$name = null) {
        $allowed_types = array('relationship','reports','reportViews');
        if ($type == '__NONE') {
            $pipe = 0;
            $name = '__NONE';
            $export = '/';
        } else   if (!is_string($type) || !in_array($type,$allowed_types)) {
            $pipe = 0;
            $type = '__ALL';  
            $name = '__ALL';  //won't be used b/c names have to be magic data keys
            $export = "/";
        } else  if (!is_string($name) || strlen($name ) == 0) {            
            $pipe = 1;
            $name = '__ALL';  //won't be used b/c names have to be magic data keys
            $export = "/{$type}";
        } else {            
            $pipe = 2;
            $export = "/{$type}/{$name}";
        }
        if (!is_array(self::$templates)) {
            self::$templates = array();
        }
        if (!array_key_exists($type,self::$templates) || !is_array(self::$templates[$type])) {
                self::$templates[$type] = array();
        }
        if (!array_key_exists($name,self::$templates[$type])) {
            if ($type == '__NONE') {
                self::$templates[$type][$name] = I2CE_SwissConfigFactory::getExportedTemplate('__NONE','CustomReports/export');
            } else {
                self::$templates[$type][$name] = I2CE_SwissConfigFactory::getExportedTemplate($export,'CustomReports/export',$pipe);
            }
            if (!self::$templates[$type][$name] instanceof I2CE_CustomReport_Template) {
            }     
        }
        return self::$templates[$type][$name];
    }

    

    /**
     * An array index by configTypes.  the values are arrays whose keys are fields with values the
     * config types the fields are supposed to have
     * @var protected static array $types
     */
    protected static $types =
        array(
            'FormRelationship_Join'=>array(
                'where'=>'FormRelationship_Where',
                'joins'=>'FormRelationship_Joins'
                ),
            'FormRelationship'=>array(
                'where'=>'FormRelationship_Where',
                'joins'=>'FormRelationship_Joins',
                'reporting_functions'=>'FormRelationship_ReportingFunctions'
                ),            
            'FormRelationship_Where'=>array(
                'operand'=>'FormRelationship_Where_Operands'
                ),   
            'CustomReports'=>array(
                'reports'=>'CustomReports_Reports',
                'reportViews'=>'CustomReports_ReportViews',
                'relationships'=>'FormRelationships'
                ),
            'CustomReports_Report'=>array(
                'meta'=>'CustomReports_Report_Meta',
                'reporting_forms'=>'CustomReports_Report_ReportingForms',
                'reporting_functions'=>'CustomReports_Report_ReportingFunctions'
                ),
            'CustomReports_Report_ReportingForm'=>array(
                'fields'=>'CustomReports_Report_ReportingForm_Fields'
                )
           
            );


    /**
     * An array with keys config types such that all sub-nodes of a node with this configType will have the
     * configType specified by the value associated to the key
     * @var protected static array $blanket
     */
    protected static $blanket = 
        array(
            'FormRelationship_ReportingFunctions'=>'FormRelationship_ReportingFunction',
            'FormRelationship_Joins'=>'FormRelationship_Join',
            'FormRelationships'=>'FormRelationship',
            'CustomReports_Reports'=>'CustomReports_Report',
            'CustomReports_ReportViews'=>'CustomReports_ReportView',
            'FormRelationship_Where_Operands'=>'FormRelationship_Where',
            'CustomReports'=>'',
            'CustomReports_Report_ReportingForms'=>'CustomReports_Report_ReportingForm',
            'CustomReports_Report_ReportingFunctions'=>'CustomReports_Report_ReportingFunction',
            'CustomReports_Report_ReportingForm_Fields'=>'CustomReports_Report_ReportingForm_Field'
            );




    /**
     * an array with keys configTypes with values the required fields for that config type.
     * will cause a failure if not present
     * $var protecected static $required
     */
    protected static $required = 
        array(
            'FormRelationship_Join'=>array('join_style','form'),
            'FormRelationship'=>array('form','display_name'),
            'CustomReports_Report'=> array('relationship','meta'),
            'CustomReports_Report_Meta'=> array('display_name'),
            //'CustomReports_Report_ReportingForm_Field'=>array('header')
            //'CustomReports_Relationship_Where'=>array('operator')
            );
            
    protected static $ensure = 
        array(
            'CustomReports'=> array('relationships','reports','reportViews'),
            'FormRelationship'=>array('joins','where','reporting_functions'),
            'FormRelationship_Join'=>array('joins','where'),
            'FormRelationship_Where'=>array('operand'),
            'CustomReports_Report'=> array('reporting_forms','reporting_functions'),
            'CustomReports_Report_ReportingForm'=> array('fields')
            );

    protected static $permissions = 
        array( 
            'FormRelationship'=>true,
            'FormRelationships'=>'task(custom_reports_can_access_relationships)',
            'CustomReports_Report'=>true,
            'CustomReports_Reports'=>'task(custom_reports_can_access_reports)',
            'CustomReports_ReportView'=>true,
            'CustomReports_ReportViews'=>'task(custom_reports_can_view_reportViews)',
            'CustomReports'=>'task(custom_reports_can_access)'
            );



    /**
     * Create an configurationGroup node by appending on to the given configurationGroup node the values
     * stored in the magic data at the specified $key.
     * @param DOMNode $configNode.  A configurationGroup node.
     * @param I2CE_MagicDataNode $config.  The data we wish to store at this node
     * @param array $pipe.  An array of path components relative to the $config. If the pipe is a non-empty array, we export only the 
     * keys specifed by the lowest member of $pipe, if it exists.  Otherwise, if the pipe is empty, we export all keys
     * @param string $key.  The key.  (Warning.  It assumes it exists in the magic data!)
     * @param string $configType.  Defaults to the empty string.  The configuration type to give the configuration node.
     * @param array $status.  An array of status options we should set for this configuration node.  Defaults to the empty array
     */
    public function createExportNodeConfigurationGroup($configNode,$config,$pipe,$key,$configType,$status) {
        if (array_key_exists($configType,self::$permissions)) {
            if (is_string(self::$permissions[$configType])) {
                $status['permission'] = self::$permissions[$configType];
            } else if (self::$permissions[$configType] === true) {
                $perm = '';
                $config->setIfIsSet($perm,'limit_access_to');
                $perm = trim($perm);
                if ($perm) {                    
                    $status['permission'] = $perm . ' or task(custom_reports_admin)';
                }                
            } else {
                I2CE::raiseError("Bad perm for $configType");                
            }
        }        
        return parent::createExportNodeConfigurationGroup($configNode, $config, $pipe, $key, $configType, $status);

    }    

    /**
     * Create an export node by appending on to the given configurationGroup node the values
     * stored in the magic data.
     * @param DOMNode $configNode.  A configurationGroup node.
     * @param I2CE_MagicDataNode $config.  The data we wish to store at this node
     * @param mixed $pipe.  A path component or an array of path components relative to the $config. If the pipe is a non-empty array, we export only the 
     * keys specifed by the lowest member of $pipe, if it exists.  Otherwise, if the pipe is empty or null, we export all keys.  Defaults to empty array
     * @param string $configType.  Defaults to the empty string.  The configuration type to give the configuration node.
     * @param array $status.  An array of status options we should set for this configuration node.  Defaults to the empty array
     */
    public function createExport($configNode,$config,$pipe=null,$configType ='',$status=array()) {
        if (array_key_exists($configType,self::$ensure)) {
            foreach (self::$ensure[$configType] as $ensure) {
                $config->$ensure; //touch it to make sure it exists at least as indeterminate
            }
        }
        if (array_key_exists($configType,self::$required)) {
            foreach (self::$required[$configType] as $required) {
                if (!isset($config->$required)) {
                    I2CE::raiseError("Could not find required node $required for $configType at " . $config->getPath(false));
                    return false;
                }
            }
        }
        if ($pipe === null) {
            $pipe = array();
        }
        if (!is_array($pipe)) {
            $pipe = array($pipe);
        }
        if (count($pipe) == 0) {
            $keys = $config->getKeys();
        } else {
            $component = array_shift($pipe);
            if (isset($config->$component)) {
                $keys = array($component);
            } else {
                I2CE::raiseError("Pipe componenent $component not found at " . $config->getPath(false));
                $keys = array();
            }
        }

        foreach ($keys as $key) {
            if (array_key_exists($configType,self::$types) && array_key_exists($key,self::$types[$configType])) {
                if (!$this->createExportNode($configNode,$config,$pipe,$key,self::$types[$configType][$key],$status)) {
                    return false;
                }
            } else if (array_key_exists($configType,self::$blanket)) {
                if (!$this->createExportNode($configNode,$config,$pipe,$key,self::$blanket[$configType],$status)) { 
                    return false;
                }
            } else {
                if (!$this->createExportNode($configNode,$config,$pipe,$key,'',$status)) { //no type is set.
                    return false;
                }
            }
        }
        return true;
    }


    /**
     * Get the reported fields for a report
     * @param string $report
     * @param boolean $get_disabled  Defaults to false
     * @returns array with keys the field name of the form "$form+$field" and values the header for the field
     */
    public static  function getReportFields($report,$get_disabled = false) {
        $fields = array();
        $reportsTemplate = self::getTemplate('reports',$report);
        if (!$reportsTemplate instanceof I2CE_MagicDataTemplate) {
            I2CE::raiseError("Could not export report '$report' template");
            return $fields;
        }     

        $results = $reportsTemplate->query('.//configurationGroup[@config="CustomReports_Report_ReportingForm_Field"]');
        for ($i=0; $i < $results->length; $i++) {
            $fieldNode = $results->item($i);
            $enabled = $reportsTemplate->getConfigurationTextContent('enabled',$fieldNode);
            if (!$get_disabled && !$enabled) {
                continue;
            }
            $form = $fieldNode->parentNode->parentNode->getAttribute('name');
            $field = $form .'+'.$fieldNode->getAttribute('name');
            $fields[$field ] = $reportsTemplate->getConfigurationTextContent('header',$fieldNode);
        }
        $results = $reportsTemplate->query('.//configurationGroup[@config="CustomReports_Report_ReportingFunction"]');
        for ($i=0; $i < $results->length; $i++) {
            $fieldNode = $results->item($i);
            $enabled = $reportsTemplate->getConfigurationTextContent('enabled',$fieldNode);
            if (!$get_disabled && !$enabled) {
                continue;
            }
            $field = '+'.$fieldNode->getAttribute('name');
            $fields[$field ] = $reportsTemplate->getConfigurationTextContent('header',$fieldNode);
        }
        return $fields;
    }



    /**
     * Get a list of the existing reports
     * @returns array with keys  report shortnames and values an array with indices 'display_name' and 'description' and 'category' and 'config_node'.  the last is
     * template config node for the report
     * reports are sorted aplhpabeticaly by 'display_name'
     */
    public static function getReportNames($cheat = false) {
        $reports = array();
        if ($cheat) { //we are cheating.
            $reportConfig  = I2CE::getConfig()->modules->CustomReports->reports;
            $reportNames = $reportConfig->getKeys();
            foreach ($reportNames as $r) {
                $data = array();
                $reportConfig->setIfIsSet($data,"$r/meta", true);
                if (!$data['display_name']) {
                    continue;
                }
                $reports[$r] = $data;
            }
        } else {
            //we are not cheating 
            $reportsTemplate = self::getTemplate('reports');
            if (!$reportsTemplate instanceof I2CE_MagicDataTemplate) {
                I2CE::raiseError("Could not export reports template");
                return $reports;
            }     
            $results = $reportsTemplate->query('.//configurationGroup[@config="CustomReports_Report_Meta"]');
            for ($i=0; $i < $results->length; $i++) {
                $reportMeta = $results->item($i);
                $report = $reportMeta->parentNode->getAttribute('name');
                if (!$report) {
                    continue;
                }
                $data = array();
                $data['display_name'] = $reportsTemplate->getConfigurationTextContent('display_name',$reportMeta);
                if (!$data['display_name']) {
                    continue;
                }
                $data['description'] = $reportsTemplate->getConfigurationTextContent('description',$reportMeta);
                $data['category'] = $reportsTemplate->getConfigurationTextContent('category',$reportMeta);
                if (!$data['category']) {
                    $data['category'] = 'Uncategorized';
                }
                $data['config_node'] = $reportMeta->parentNode;
                $reports[$report] = $data;
            }
        }
        uasort($reports,array('I2CE_CustomReport_Template','compareByDisplayName'));
        return $reports;
    }





    /**
     * Get the reports available by category
     * @returns array a multi-dimensional array.  the first set of indices is the category sorted alphabetically
     * the second set of indices are the reports for that category.
     * for each of these we have an array indexed by 'display_name' and 'description' and 'config_node'.  the last is
     * template config node for the report
     * the second set of indices is sorted alphabetically by 'display_name'
     */
    public static  function getReportsByCategory($cheat = false) {
        $t_reports = self::getReportNames($cheat);
        $reports = array();
        foreach ($t_reports as $report=>$data) {
            $category = $data['category'];
            unset($data['category']);
            $reports[$category][$report] = $data;
        }
        //sort report categories
        ksort($reports, SORT_STRING); //
        return $reports;
    }

    
    /**
     * Compares to arrays with keys 'display_name' to see which is "bigger"
     * @param array $m
     * @param array $n
     * @returns boolean
     */
    public static function compareByDisplayName($m,$n) {
        return strcasecmp($m["display_name"],$n["display_name"]);
    }


    /**
     * Get a list of the existing report views
     * @returns array with keys  report view names/index and values an array with indices 'display_name' and 'description' and 'report' and 'config_node',
     * the last is the template confignode which contains the view     
     * reports are sorted alhpabeticaly by 'display_name'
     */
    public static function getReportViews() {
        $views = array();
        $reportViewsTemplate = self::getTemplate('reportViews');
        if (!$reportViewsTemplate instanceof I2CE_MagicDataTemplate) {
            I2CE::raiseError("Could not export report Views template");
            return $views;
        }     
        $results = $reportViewsTemplate->query('//configurationGroup[@config="CustomReports_ReportView"]');
        for ($i=0; $i < $results->length; $i++ ) { 
            $view = $results->item($i);
            $data = array();
            $data['report'] = $reportViewsTemplate->getConfigurationTextContent('report',$view);
            if (!$data['report']) {
                continue;
            }
            $data['display_name'] = $reportViewsTemplate->getConfigurationTextContent('display_name',$view);
            $data['description'] = $reportViewsTemplate->getConfigurationTextContent('description',$view);
            $data['config_node'] = $view;
            $views[$view->getAttribute('name')] = $data;
        }
        uasort($views,array('I2CE_CustomReport_Template','compareByDisplayName'));
        return $views;
    }


    /**
     * Get the report view sorted by report
     * @returns array.  a multi-dimensional array.  the first index is the short name of the report.
     * the second index is the name/index of the report view.   This in turn refers to an array
     * with keys 'display_name' and 'description' and 'config_node',
     * the last is the template confignode which contains the view     
     * 
     */
    public static function getReportViewsByReport() {
        $t_views = self::getReportViews();
        $views = array();
        foreach ($t_views as $view=>$data) {
            $report = $data['report'];
            unset($data['report']);
            $views[$report][$view] = $data;
        }
        return $views;
    }

    /**
     * Get the relationship associated to a report
     * @param string $report
     * @returns strng.  The empty string on failure
     */
    public static function getRelationshipFromReport($report) {
        $reportTemplate = self::getTemplate('reports',$report);
        if (!$reportTemplate instanceof I2CE_CustomReport_Template) {
            I2CE::raiseError("Could not find the report $report");
            return '';
        }
        $qry = '//configurationGroup[@config="CustomReports_Report"]';
        $results= $reportTemplate->query($qry);
        if ($results->length != 1) {
            I2CE::raiseError("Could not find the data for the report $report");
            return '';
        }
        return  $reportTemplate->getConfigurationTextContent("relationship",$results->item(0));
    }




    /**
     * Looks up the form associated to a reportform in the specified relationship
     * @param string $relationship
     * @param string $reportform.  If $reportform == 'primary_form' or $relationship then we are looking up the primary form
     * @returns string.  The false on failure
     */
    public static function getFormFromReportForm($relationship,$reportform) {
        $relationshipTemplate = self::getTemplate('relationships',$relationship);
        if (!$relationshipTemplate instanceof I2CE_CustomReport_Template) {
            I2CE::raiseError("Could not find the relationship $relationship");
            return false;
        }
        if ($reportform == 'primary_form' || $reportform == $relationship ) {
            $qry = '//configurationGroup[@config="FormRelationship"]';
        } else {
            $qry = "//configurationGroup[@config='FormRelationship_Join' and @name='$reportform']";
        }
        $qry .= '/configuration[@name="form"]';
        $results= $relationshipTemplate->query($qry);
        if ($results->length != 1) {
            I2CE::raiseError("Could not find the form used for the reported form $reportform");
            return false;
        }
        return  $relationshipTemplate->getTextContent('value',$results->item(0));
    }




}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
