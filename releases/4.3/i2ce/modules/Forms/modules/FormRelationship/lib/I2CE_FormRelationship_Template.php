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
*  I2CE_FormRelationship_Template
* @package I2CE
* @subpackage Core
* @author Carl Leitner <litlfred@ibiblio.org>
* @version 2.1
* @access public
*/


class I2CE_FormRelationship_Template extends I2CE_MagicDataTemplate{

    /**
     * An array of requested relationships templates I2CE_CustomReport_Template  indexed by the type and then by  name
     * @protected static array $templates
     */
    protected static $templates = array();

    static $allowed_types = array('relationship');

    /**
     * Gets a  template.  Also caches the results
     * @param string $type one of report, relationship or reportView.  Defaults to null meaning we get everything under /modules/CustomReports
     * @param string $name.  Defaults to null meaning we get all the <$type>s of the specified name
     * @returns I2CE_CustomReportTemplate on sucess
     */
    public static function getTemplate($type=null,$name = null) {
        if ($type == '__NONE') {
            $pipe = 0;
            $name = '__NONE';
            $export = '/';
        } else   if ($type !== 'relationships') {
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
                self::$templates[$type][$name] = I2CE_SwissConfigFactory::getExportedTemplate('__NONE','FormRelationship/export');
            } else {
                self::$templates[$type][$name] = I2CE_SwissConfigFactory::getExportedTemplate($export,'FormRelationship/export',$pipe);
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
            'FormRelationship_Where_Operands'=>'FormRelationship_Where'
            );




    /**
     * an array with keys configTypes with values the required fields for that config type.
     * will cause a failure if not present
     * $var protecected static $required
     */
    protected static $required = 
        array(
            'FormRelationship_Join'=>array('join_style','form'),
            'FormRelationship'=>array('form','display_name')
            );
            
    protected static $ensure = 
        array(
            'FormRelationship'=>array('joins','where','reporting_functions'),
            'FormRelationship_Join'=>array('joins','where'),
            'FormRelationship_Where'=>array('operand')
            );

    protected static $permissions = 
        array( 
            'FormRelationship'=>true,
            'FormRelationships'=>'task(custom_reports_can_access_relationships)',
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





    public static  function getReportFormNode($configNode) {
        if  (!$configNode instanceof DOMElement) {
            return false;
        }
        $type = $configNode->getAttribute('config');
        while ($configNode instanceof DOMNode && $configNode->tagName == 'configurationGroup' &&
               ! ($type == 'FormRelationship' || $type== 'FormRelationship_Join' )) {
            $type = '';
            $configNode = $configNode->parentNode;
            if ($configNode instanceof DOMElement) {
                $type = $configNode->getAttribute('config');
            } else {
                $type = '';
            }
        }
        if ( ! ($type == 'FormRelationship' || $type== 'FormRelationship_Join' )) {
            return false;
        }
        return $configNode;
    }


    public static  function getReportForm($configNode) {
        $configNode = self::getReportFormNode($configNode);
        if (!$configNode instanceof DOMNode) {
            return false;
        }
        $form = trim($this->getConfigurationTextContent('form',$configNode));
        if (strlen($form) == 0) {
            return false;
        }
        return $form;
    }



}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
