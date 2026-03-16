<?php
/**
* © Copyright 2012 IntraHealth International, Inc.
* 
* This File is part of I2CE 
* 
* I2CE is free software; you can redistribute it and/or modify 
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
* @package I2CE
* @subpackage Forms
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.1.3
* @since v4.1.3
* @filesource 
*/ 
/** 
* Class I2CE_TaskLog_FormStorage
* 
* @access public
*/


class I2CE_Module_TaskLog_FormStorage extends I2CE_Module{

    
    public static function getHooks() { 
	return array(
            'form_post_save'=>'logFormSave',	    
	    'form_post_populate'=>'storeValues'
	    );
    }


    
    public static function getMethods() {
        return array(
            'I2CE_FormFactory->setTask' => 'setTask',
            'I2CE_FormFactory->setTaskID' => 'setTaskID',
            'I2CE_FormFactory->getTask' => 'getTask',
            'I2CE_FormFactory->getTaskID' => 'getTaskID',
            'I2CE_FormFactory->pauseTaskLog' => 'pauseTaskLog',
            'I2CE_FormFactory->resumeTaskLog' => 'resumeTaskLog',
            
            );
    }

    protected $paused = false;

    public function resumeTaskLog($obj) {
        $this->paused = false;
    }

    public function pauseTaskLog($obj) {
        $this->paused = true;
    }

    protected $task = 'DEFAULT';
    protected $taskid = null;

    public function getTask($obj,$task) {
        return $this->task;
    }

    public function setTask($obj,$task, $taskid = null) {
        if (!is_string($task) || !$task) {
            return;
        }
        $this->task = $task;
        if ( $this->task == 'DEFAULT') {
            $taskid = null;
        } else {
            if ($taskid === null ) {
                $taskid = 'TS:' . time();
            }            
        }
        $this->setTaskId($obj,$taskid);
    }


    public function getTaskid($obj,$taskid) {
        return $this->taskid;
    }

    public function setTaskID($obj,$taskid = null) {
        $this->taskid = $taskid;
    }

    
    protected $data_store = array();

    public function storeValues($data) {
        if ($this->paused) {
            return;
        }
	//$data = array( 'form' => $form ) 
	if (!$data['form'] instanceof I2CE_Form) {
	    return;
	}
	$values_to_store = array();
	foreach ($data['form']->getFieldNames() as $field_name) {
	    if (! ($fieldObj= $data['form']->getField($field_name)) instanceof I2CE_FormField) {
		continue;
	    }
	    $values_to_store[$field_name] = $fieldObj->getDBValue();
	}
	$this->data_store[$data['form']->getNameID()] = $values_to_store;
    }
    
    protected function getFieldsWithDifferentValue($formObj) {
        $field_names = $formObj->getFieldNames(); 
        //any  failure in check validit should return false.
        $formid = $formObj->getNameID();
	if (!array_key_exists($formid,$this->data_store)
	    |!is_array($this->data_store[$formid])
	    ) {
            return $field_names;
	}
	$form_store = $this->data_store[$formid];
        $not_same = array();
	foreach ($field_names as $field_name) {
	    if (!array_key_exists($field_name,$form_store)
		||! ($fieldObj= $formObj->getField($field_name)) instanceof I2CE_FormField
		|| $fieldObj->getDBValue() !== $form_store[$field_name]
		) {
                $not_same[] = $field_name;
		//return false;
	    }
	}
        unset($this->data_store[$formid]); //free up some memory
	return $not_same;
    }


    public function logFormSave($data) {
        if ($this->paused) {
            return;
        }
	//$data = array( 'form' => $form, 'user' => $user ) 
	if (!$data['form'] instanceof I2CE_Form 
	    || !$data['user'] instanceof I2CE_User
	    || count( $log_fields = $this->getFieldsWithDifferentValue($data['form'])) == 0
	    ) {
	    return;
	}
        $success = true;
	foreach ($log_fields as $field_name) {
	    if (
                ! ($fieldObj= $data['form']->getField($field_name)) instanceof I2CE_FormField
		){
		continue;
	    }
            $change_type = $data['form']->getAttribute( "change_type_default" );
            if ( $data['form']->hasAttribute( "change_type_" . $field_name ) ) {
                $change_type = $data['form']->getAttribute( "change_type_" . $field_name );
	    }
	    $log_data =  array(
		'who'=> $data['user']->username(),
		'form'=>$data['form']->getName(),
		'id'=>$data['form']->getID(),
		'field'=>$fieldObj->getName(),
		'value'=>$fieldObj->getDBValue(),
		'change_type'=> $change_type,
                'type'=>$fieldObj->getTypeString(),
		);
	    $success &= $this->logFieldChange($log_data);
	}
        return $success;
    }

    protected static $insert_stmt = array();

    public function logFieldChange($log_data) {
        $required = array('type','type','form','field','id','who','change_type');
        if (!is_array($log_data) ) {
            I2CE::raiseError("Invalid logging data");
            return false;
        }
        foreach ($required as $key) {
            if (!array_key_exists($key,$log_data) || !is_scalar($log_data[$key]) || !$log_data[$key] ) {
                I2CE::raiseError("Invalid logging data for $key");
                return false;
            }
        }
        if (!in_array($log_data['type'],array('blob','integer','text','date','string'))) {
            I2CE::raiseError("Invalid type:" . $log_data['type']);
            return false;
        }

        $log_data['task']=$this->task;
        $log_data['task_id']=$this->taskid;

        $db = I2CE::PDO();

        if ( !array_key_exists($log_data['type'], self::$insert_stmt)
             || !self::$insert_stmt[$log_data['type']]) {
            try {
                self::$insert_stmt[$log_data['type']] = $db->prepare( 
                    "INSERT INTO form_task_log ( form,field,id, who, change_type,task,task_id," . $log_data['type'] ."_value ) VALUES ( ?,  ?, ?, ?,?,?,?,? )" );
            } catch ( PDOException $e ) {
                I2CE::pdoError( $e, "Error preparing entry insert of type " . $log_data['type']);
                return false;
            }
        }
        $t_log_data = array();
        foreach (array('form','field','id', 'who', 'change_type','task','task_id', ) as $key) {
            $t_log_data[] = $log_data[$key];
        }
        $t_log_data[] = $log_data['value'];
        try {
            self::$insert_stmt[$log_data['type']]->execute( $t_log_data);
        } catch ( PDOException $e ) {
            I2CE::pdoError( $e, "Error logging\n" . print_r($t_log_data,true) );
            return false;
        }
        return true;
        
    }




    /**************************
     *
     *    Init methods
     *
     **************************/


    /**
     * Method called when the module is enabled for the first time.
     * @param boolean -- returns true on success. false on error.
     */
    public function action_initialize() {
        I2CE::raiseError("Initializing Form Task Logger");
        if (!I2CE_Util::runSQLScript('initialize_form_task_log.sql')) {
            I2CE::raiseError("Could not initialize I2CE form tables");
            return false;
        }
	return true;
    }

    /**
     * Upgrades the modules
     * @param string $old_vers
     * @param string $new_vers
     * @returns boolean
     */
    public function upgrade($old_vers,$new_vers) {
        I2CE::raiseError("upgrade $old_vers -- $new_vers");
        if (I2CE_Validate::checkVersion($old_vers,'<','4.1.3.7')) {
            I2CE::raiseError("Initializing Form Task Logger");
            if (!I2CE_Util::runSQLScript('initialize_form_task_log.sql')) {
                I2CE::raiseError("Could not initialize I2CE form tables");
                return false;
            }
        }
        return true;
    }


  }
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
