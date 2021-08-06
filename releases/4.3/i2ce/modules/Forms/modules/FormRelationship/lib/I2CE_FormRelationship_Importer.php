<?php
/**
* © Copyright 2015 IntraHealth International, Inc.
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
* @package i2ce
* @subpackage forms
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.2
* @since v4.2
* @filesource 
*/ 
/** 
* Class I2CE_FormRelationship_Loader
* 
* @access public
*/


class I2CE_FormRelationship_Importer extends I2CE_Fuzzy{


    
    public function __construct($user = false) {
        I2CE_CachedForm::$spam=false;
        if (!$user instanceof I2CE_User) {
            $user = new I2CE_User();
        }
        $this->user = $user;
        $this->factory = I2CE_FormFactory::instance();
        $this->unique_fields =array();
    }


    protected static $lookup_stmt = null;

    public static function getNodeHash($node) {
        if (!$node instanceof DOMElement
            || $node->tagName  != 'relationship'
            || !$node->hasAttribute('name')
            || !($name = trim($node->getAttribute('name')))
            ){
            $msg ='';
            if ($node instanceof DOMElement) {
                $msg  = $node->ownerDocument->saveXML($node);
            }
            $this->raiseError("Invalid import:\n" . $msg);
            return null;
        }
        $node->normalize(); //so consistent hashing regardless of spacing);
        $hash = md5($node->ownerDocument->saveXML($node));
        return array($name,$hash);
    }

    public static function alreadyProcessed($node) {
        if (! is_array($return = self::getNodeHash($node))) {
            return null;
        }
        list($relationship,$hash) = $return;
        if (!is_string($relationship) || !is_string($hash)) {
            $this->raiseError("Bad parameters");
            return null;
        }
        if (self::$lookup_stmt === null) {
            $db = I2CE::PDO();
            try {
                self::$lookup_stmt =   $db->prepare( "SELECT id FROM form_relationship_importer WHERE relationship = ? and hash= ?"); 
            } catch ( PDOException $e ) {
                I2CE::pdoError($e,"Could not prepare lookup statement");
                return null;
            }
        }
        if (!self::$lookup_stmt) {
            return null;
        }
        self::$lookup_stmt->execute(array( $relationship,$hash ));
        try {
            $row = self::$lookup_stmt->fetch();
            self::$lookup_stmt->closeCursor();
        } catch ( PDOException $e ) {
            I2CE::pdoError( $e, "Error getting form id:" );
            return false;
        }
        if ( !isset( $row ) ) {
            return false;
        }
        return $row->id;
    }
   
    protected static $mark_stmt  = null;
    public static function markProcessed($id,$relationship,$hash) {
        if (!is_string($relationship) 
            || !is_string($hash) 
            || !is_string($id) 
            || !$id 
            || !$hash 
            || !$relationship ) {
            $this->raiseError("bad parameters");
            return null;
        }
        if (self::$mark_stmt === null) {
            $db = I2CE::PDO();
            try {
                self::$mark_stmt = $db->prepare( "INSERT INTO form_relationship_importer (id,relationship,hash) VALUES (?, ?, ?)" );
            } catch ( PDOException $e ) {
                I2CE::pdoError($e,"Could not prepare mark statement");
                return null;
            }
        }
        if (!self::$mark_stmt) {
            return null;
        }
        try {
            self::$mark_stmt->execute(array($id,$relationship,$hash));
        } catch ( PDOException $e ) {
            I2CE::pdoError($e,"Could not mark processed");
            return false;
        }
    }


    /* DOM XPath Object */                                 
    protected $xpather;
    /**
     *
     * when we do a mapped form, we should first check for duplicates before creating a new one.  
     * this will house the saved forms so far so we can check for duplicates 
     */
    protected $save_list_entries;




    protected $callback = false;

    public function setMessageCallback($callback) {
        $this->callback = $callback;
    }
    
    protected function raiseError($msg) {
        I2CE::raiseMessage($msg);
        if (is_callable($this->callback)) {
            call_user_func_array($this->callback,array($msg));
        }
    }
    
    public function loadFromXMLCollection($node, $rel_form_name_fields = false) {
        $type = 'relationshipCollection';
        $this->xpather = new DOMXPath($node->ownerDocument);
        if( is_string($node)) {
            $doc = new DOMDocument();
            if (! ($doc->loadXML($node))) {
                return false;
            }
            $node = $doc->documentRoot;
        } else if ($node instanceof DOMDocument) {
            $node = $node->documentElement;
        }
        if (!$node instanceof DOMElement) {
            $this->raiseError("Not XML");
            return false;
        }        
        if ($node->tagName != $type) {
            $this->raiseError("Invalid element tag: " . $node->tagName . ' != ' . $type);
            return false;
        }

        $allowed_duplicates = array();
        if ($node->hasAttribute('nomatching')) {
            $allowed_duplicates = explode(',',$node->getAttribute('nomatching'));
        }
        $allowed_invalid = array();
        if ($node->hasAttribute('invalid')) {
            $allowed_invalid = explode(',',$node->getAttribute('invalid'));
        }
        $ignore_ids = ($node->hasAttribute('ignoreids') && $node->getAttribute('ignoreids')==1);
        $save_ids = array();
	$exec = array('max_execution_time'=>20*60, 'memory_limit'=> (256 * 1048576));	    
        foreach ($this->xpather->query('./relationship',$node) as $rel_node) {
            I2CE::longExecution($exec);
            I2CE::raiseError("relationship");
            if (!$rel_node instanceof DOMElement
                || $this->alreadyProcessed($rel_node) 
                ) { 
                $this->raiseError("Skipping already processed record");
                continue;
            }
            $rel_name = 'adhoc';
            if ($node->hasAttribute('name')) {
                $rel_name = $node->getAttribute('name');
            }
            $form_name_fields = false; //load all fields by default
            if ($rel_name 
                && is_array($rel_form_name_fields)
                && array_key_exists($rel_name,$rel_form_name_fields)) {
                $form_name_fields = $rel_form_name_fields[$rel_name];
            }
            $rel_queue = $this->loadXML_join_forms($rel_node,$form_name_fields,false,$ignore_ids);
            $rel_save_ids= $this->process_saves($rel_queue,$allowed_duplicates,$allowed_invalid);
            $summary = "Import Summary (Relationship):\n";
            foreach ($rel_save_ids as $save_id=>$msg) {
                $summary   .= " " . $msg .  " ($save_id)\n";
            }
            $this->raiseError($summary);

            $save_ids =array_merge( $save_ids, $rel_save_ids);
        }
        return $save_ids;

    }
    

    /**
     * @param mixed $node DOMNode of XML containing XML source
     * @param mixed $form_name_fields.  Defaults to false.  If it is an array, it has keys the name of the form in the relationship and values is the list of the fields we should populate for that instance of the form
     */
    public function loadFromXML($node, $form_name_fields = false) {
        if ($this->alreadyProcessed($node) ) {
            $this->raiseError("Skipping already processed record");
            return array();
        }
        $type = 'relationship';
        $user = new I2CE_User();
        $this->xpather = new DOMXPath($node->ownerDocument);
        if( is_string($node)) {
            $doc = new DOMDocument();
            if (! ($doc->loadXML($node))) {
                return array();
            }
            $node = $doc->documentRoot;
        } else if ($node instanceof DOMDocument) {
            $node = $node->documentElement;
        }
        if (!$node instanceof DOMElement) {
            $this->raiseError("Not XML");
            return array();
        }        
        if ($node->tagName != $type) {
            $this->raiseError("Invalid element tag: " . $node->tagName . ' != ' . $type);
            return array();
        }
        $ignore_ids = ($node->hasAttribute('ignoreids') && $node->getAttribute('ignoreids'));
        $allowed_duplicates = array();
        if ($node->hasAttribute('nomatching')) {
            $allowed_duplicates = explode(',',$node->getAttribute('nomatching'));
        }
        $allowed_invalid = array();
        if ($node->hasAttribute('invalid')) {
            $allowed_invalid = explode(',',$node->getAttribute('invalid'));
        }
        $queue = $this->loadXML_join_forms($node,$form_name_fields,false,$ignore_ids);
        $rel_ids = $this->process_saves($queue,$allowed_duplicates,$allowed_invalid);
        return  $rel_ids;
    }



    protected function loadXML_join_forms($join_node, $form_name_fields,  $relparent_form_obj = false, $ignore_ids =false) {
        if (!$join_node instanceof DOMElement){
            $this->raiseError("not a node element");
            return $queue;
        }
        $form_name = false;
        $join_style = false;
        $join_field = false;
        $relationship = false;
        $hash = false;
        if (! $join_node->hasAttribute('form')
            || ! ( $form = $join_node->getAttribute('form'))
            ) {
            $this->raiseError("No form specified");
            return $queue;
        }
        switch ($join_node->tagName) {
            case 'relationship':
                $form_name = 'primary_form';
                $join_style = 'primary_form';
                if (! is_array($return = self::getNodeHash($join_node))) {
                    return null;
                }
                list($relationship,$hash) = $return;
                if (!is_string($relationship) || !is_string($hash)) {
                    $relationship = false;
                    $hash = false;
                    return null;
                }
                break;
            case 'joinedForm':
                if (! $join_node->hasAttribute('report_form_name')
                    || ! ( $form_name= $join_node->getAttribute('report_form_name'))
                    ) {
                    $this->raiseError("No form name specified");
                    return $queue;
                }
                if ($join_node->hasAttribute('join_style')) {
                    $join_style = $join_node->getAttribute('join_style');
                }
                if ($join_node->hasAttribute('join_field')) {
                    $join_field = $join_node->getAttribute('join_field');
                }

                break;
            default:
                $this->raiseError("Unrecognized joined data");
                break;
        }
        $id = '';
        if ($ignore_ids) {
            if ($join_node->hasAttribute('id')) {
                $join_node->removeAttribute('id');
            } 
        } else {
            if ( $join_node->hasAttribute('id')) {
                $id = trim($join_node->getAttribute('id'));
            }
        }
        $do_save = false;
        if ($id != '0' && $id != '') {
            $form_id = array($form,$id);
            $do_save = true;//unsure about this
        } else {
            $id = '';
            $form_id = array($form,'0');
            $do_save = true;
        }        
        if ( ! ($form_obj = $this->factory->createContainer($form_id)) instanceof I2CE_Form
            ) {
            $this->raiseError("Could not load $form $id when instantiating $form_name");
            return $queue;
        }
        foreach ($this->xpather->query('./form',$join_node) as $form_node) {            
            $fields = false; //load all fields by default
            if (is_array($form_name_fields)
                && array_key_exists($form_name,$form_name_fields)) {
                $fields = $form_name_fields[$form_name];
            }            
            if ($ignore_ids && $form_node->hasAttribute('id')) {
                $form_node->removeAttribute('id');
            } 
            if ( !($form_obj->loadFromXML($form_node,$fields))) {
                $this->raiseError("Error loading form:"  . $form_node->ownerDocument->saveXML($form_node));
            }

        }

        $pf_queue = array();
        foreach ($this->xpather->query('./joinedForms/joinedForm[@join_style="parent_field"]',$join_node) as $sub_join_node) {
            $pf_queue = array_merge($pf_queue,$this->loadXML_join_forms($sub_join_node,$form_name_fields,$form_obj,$ignore_ids));
        }

        $queue_entry = 
            array(
                'join_style'=>$join_style, 
                'form_obj'=>$form_obj, 
                'relparent_form_obj' => $relparent_form_obj,
                'join_field'=> $join_field,
                'do_save'=>$do_save,
                'relationship' => $relationship,
                'hash' => $hash
                );


        $queue[] = $queue_entry;
        // switch($join_style) {
        // case 'primary_form':
        // case 'child':
        //     $queue[] = $queue_entry;
        //     //need to specify that we need to use the relationship parent  form node's  object that was just saved
        //     //to get the id.
        //     break;
        // case 'parent_field':
        //     //This is a join that you would do if $join_node corresponded to a position form and had person_position in the parent form node of the relationship
        //     //in this case, you want the $position_obj to save before the $person_position_obj so that you can
        //     //set $person_position_obj to have the parent id created for $position_obj
        //     array_unshift($queue,$queue_entry);
        //     break;
        // default:
        //     I2CE::raiseError("Join style $join_style is not supported");
        //     break;
        // }

        $child_queue = array();
        foreach ($this->xpather->query('./joinedForms/joinedForm[@join_style="child"]',$join_node) as $sub_join_node) {
            $child_queue = array_merge($child_queue,$this->loadXML_join_forms($sub_join_node,$form_name_fields,$form_obj,$ignore_ids));
        }

        //I2CE::raiseError(print_r($queue_entry,true));
        return array_merge($pf_queue, array($queue_entry), $child_queue);
    }



    protected $unique_fields;
    protected function get_unique_fields($form_obj) {
        $form = $form_obj->getName();
        if (!array_key_exists($form,$this->unique_fields)) {
            $this->unique_fields[$form] = array();
            foreach ($form_obj as $field_name=>$field_obj) {
                if (!$field_obj->getOption('unique')) {
                    continue;
                }
                $this->unique_fields[$form][] = $field_name;
            }
        }
        return $this->unique_fields[$form];
    }



    protected function process_saves($queue,$allowed_duplicates,$allowed_invalid) {
        $this->raiseError("Processing " . count($queue) . " forms");
        $save_ids = array();
        $this->raiseError("BEGIN QUEUE PROCESSING");
        $exec = array('max_execution_time'=>20*60, 'memory_limit'=> (256 * 1048576));	    
        $db = I2CE::PDO();
        $db->beginTransaction();
        foreach ($queue as $queue_entry) {
            I2CE::longExecution($exec);
            if ( !is_array($queue_entry)
                 || ! ($join_style = $queue_entry['join_style'])
                 || ! ( $form_obj = $queue_entry['form_obj']) instanceof I2CE_Form
                ) {
                $this->raiseError("Skipping invalid queue entry");
                continue;
            }
            $msg = "imported new record";
            $relparent_form_obj = $queue_entry['relparent_form_obj'];
            $join_field = $queue_entry['join_field'];
            $do_save = $queue_entry['do_save'];
            $form = $form_obj->getName();

            $matched_obj = false;

            if ($form_obj instanceof I2CE_List
                && $join_style == 'parent_field'
                && ! in_array($form,$allowed_duplicates)
                && count($unique_fields = $this->get_unique_fields($form_obj)) > 0
                ) {
                $matched_obj = $this->find_matching($form_obj);
                if ($matched_obj instanceof I2CE_Form) {
                }
            }

            
            switch($join_style) {
            case 'primary_form':
                $msg .= ' - primary_form';
                //do nothing special
                break;
            case 'child':
                if ($relparent_form_obj instanceof I2CE_Form) {
                    $form_obj->setParent($relparent_form_obj);
                    $msg .= ' - child of ' . $relparent_form_obj->getNameId();
                }
                //need to specify that we need to use the relationship parent  form node's  object that was just saved
                //to get the id.
                break;
            case 'parent_field':
                //This is a join that you would do if $join_node corresponded to a position form and had person_position in the parent form node of the relationship
                //in this case, you want the $position_obj to save before the $person_position_obj so that you can
                //set $person_position_obj to have the parent id created for $position_obj
                if (!$matched_obj instanceof I2CE_Form) {
                    break;
                }
                $nameid = $matched_obj->getNameID();
                if (! ($relparent_form_obj instanceof I2CE_Form)
                    || ! $join_field
                    || ! ($join_field_obj = $relparent_form_obj->getField($join_field)) instanceof I2CE_FormField_MAP
                    ){
                    break;
                }
                $msg .= " - joined from $join_field of " . $relparent_form_obj->getNameId();
                $join_field_obj->setFromDB($nameid);   //Note the join_field_obj is after the current queue entry on the list.  this means it will be saved later in the loop
                break;
            default:
                $this->raiseError("Join style $join_style is not supported");
                break;
            }
            if (!$matched_obj instanceof I2CE_Form) {
                //save a new form
                $this->raiseError("Form instance not found, validating. (" . $form_obj->getNameID() . ")");
                if (! in_array($form,$allowed_invalid)) {
                    $form_obj->validate();
                }
                if ($form_obj->hasInvalid()) {
                    $this->raiseError("Form instance invalid. (" . $form_obj->getNameID() . ")\n<pre>" . htmlspecialchars($form_obj->getXMLRepresentation(false)) . "</pre>");
                    $msgs =array();
                    foreach ($form_obj as $field_name =>$field_obj) {
                        if (!$field_obj->hasInvalid()) {
                            continue;
                        }
                        $t_msg = $field_obj->getInvalid();
                        if (is_string($t_msg)) {
                            $t_msg .= "\nValue = " . $field_obj->getDBValue();
                            $msgs[] = " [" .$field_obj->getName() . "] "  . $t_msg ;
                        } else if (is_array($t_msg)) {
                            foreach ($t_msg as $i=>$tt_msg) {
                                if (is_string($tt_msg)) {
                                    continue;
                                }
                                unset($t_msg[$i]);
                            }
                            $msgs[] = "  [" .$field_obj->name() . "]" . implode("," , $t_msg) ."\nValue = " . $field_obj->getDBValue();
                        }
                    }
                    $md5 = md5($form_obj->getXMLRepresentation(false));
                    $msg = "Invalid form. will not attempt save. (" . $form_obj->getNameID(). ")\n" . implode("\n",$msgs);
                    $this->raiseError($msg);  
                    $save_ids[$md5] = $msg;
                    $db->rollback();
                    I2CE::raiseMessage("Failed to validate a form in queue so rolling back and skipping rest of queue.");
                    return array();
                } else if ($relparent_form_obj instanceof I2CE_Form && $relparent_form_obj->getID() == '0' && $join_style == 'child') {
                    $md5 = md5($form_obj->getXMLRepresentation(false));
                    $msg = "related form (" . $relparent_form_obj->getNameID() . ") is unsaved so will not attempt save (" . $form_obj->getNameID() . ") ";
                    $this->raiseError($msg);  
                    $save_ids[$md5] = $msg;   
                } else if ($do_save) {
                    if (!  $form_obj->save($this->user)) {
                        $md5 = md5($form_obj->getXMLRepresentation(false));
                        $msg = "Could not save " . $form_obj->getNameID();
                        $this->raiseError($msg);
                        $save_ids[$md5] = $msg;
                    } else {
                        $nameid =  $form_obj->getNameID();                    
                        $this->raiseError("Saved $nameid");
                        if ($queue_entry['relationship']
                            && $queue_entry['hash']
                            && $join_style  == 'primary_form'
                            ){
                            self::markProcessed($nameid,$queue_entry['relationship'],$queue_entry['hash']);
                        }

                        $save_ids[$nameid] = $msg;
                        if ($join_style == 'parent_field') {
                            if (! ($relparent_form_obj instanceof I2CE_Form)
                                || ! $join_field
                                || ! ($join_field_obj = $relparent_form_obj->getField($join_field)) instanceof I2CE_FormField_MAP
                                ){
                                break;
                            }
                            $msg .= " - joined from $join_field of " . $relparent_form_obj->getNameId();
                            $join_field_obj->setFromDB($nameid);   //Note the join_field_obj is after the current queue entry on the list.  this means it will be saved later in the loop
                        }

                    }
                } 
            }
        }
        $db->commit();
        return $save_ids;
    }


    protected function find_matching($form_obj) {
        $wheres = array();
        foreach ($form_obj as $field_name=>$field_obj) {
            if (!$field_obj instanceof I2CE_FormField
                || !$field_obj->hasOption('unique') 
                || !$field_obj->getOption('unique') 
                ){
                continue;
            }
            if ( ! $field_obj->hasOption('unique_field') ) {
                if ($field_obj->isValid()) {
                    $wheres[] = array (
                        'operator' => 'FIELD_LIMIT',
                        'style' => 'equals',
                        'field' => $field_obj->getName(),
                        'data' => array( 'value' => $field_obj->getDBValue() )
                        );
                }
            } else {
                $unique = $field_obj->getOption('unique_field');
                $unique_fields = explode(',',$unique);
                foreach ($unique_fields as $unique_field) {
                    //we need to say that this is unique only up to the other values
                    if ( strpos( $unique_field, ':' ) !== false ) {
                        $main_where = array (
                            'operator' => 'FIELD_LIMIT',
                            'style' => 'equals',
                            'field' => $field_obj->getName(),
                            'data' => array( 'value' => $field_obj->getDBValue() )
                            );

                        $field_path = explode(':',$unique_field);
                        $restricted_field = false;
                        if (preg_match('/^\[(.*?)\](.*)$/',$field_path[0])) {            
                            $restricted_field = $matches[1];
                            $field_path[0] = $matches[2];
                        } else if (preg_match('/^(.*?)\+(.*)$/',$field_path[0],$matches)) {
                            $restricted_field = $matches[1];
                        } else {
                            $restricted_field = $field_path[0];
                        }
                        $restricted_field_obj = $form_obj->getField( $restricted_field );
                        if ( !$restricted_field_obj instanceof I2CE_FormField_MAP ) {
                            $this->raiseError( "Invalid field passed as restricted field for " . $form_obj->getName() . ": $unique_field" );
                            return;
                        }
                        if ($restricted_field_obj->hasHeader('default')) {
                            $names[] = $restricted_field_obj->getHeader('default');
                        } else {
                            $names[] = $restricted_field_obj->getName();
                        }                
                        //now let's split up field_path into the forms and the fields
                        $top_formid = I2CE_List::walkupFieldPath($field_path,$restricted_field_obj->getDBValue());
                        if ($top_formid === false) {
                            //the value is not set. or inappropriately set.  error silently.  
                            //this is handled by hooked method defined in I2CE_Module_Form.
                            return;
                        }
                        //now we get all forms under $top_formid defined by the field path
                        $field_name = $field_obj->getName();
                        $field_val = $field_obj->getDBValue();
                        $form_id = $form_obj->getID();
                
                        $dtree_path = $field_path;
                        array_unshift( $dtree_path, $form_obj->getName() );
                        list( $top_form, $top_id ) = explode( '|', $top_formid, 2 );
                        $dtree_limits = array(
                            $top_form =>
                            array( 'operator' => 'FIELD_LIMIT',
                                   'style' => 'equals',
                                   'field'=>'id',
                                   'data' => array( 'value' => $top_id )
                                ),
                            $form_obj->getName() => $main_where,
                            );

                        $options = I2CE_List::buildDataTree( $dtree_path, array( $form_obj->getName() ), $dtree_limits );
                        $options = I2CE_List::flattenDataTree( $options );
                        if ( count( $options ) == 1 ) {
                            // There is exactly one match.  we should use it
                            $matched_id =  $options[0]['value'];
                            return $this->factory->createContainer($matched_id);

                        }
                        
                    }  else {
                        if ( !($unique_field_obj = $form_obj->getField($unique_field)) instanceof I2CE_FormField ) {
                            $this->raiseError("Invalid field $unqiue_field");
                            return;
                        }
                        if ( $unique_field_obj->isValid()) {
                            $wheres[] = array(
                                'operator'=>'FIELD_LIMIT',
                                'style'=>'equals',
                                'field'=>$unique_field_obj->getName(),
                                'data'=>array('value'=>$unique_field_obj->getDBValue())
                                );                
                        } else {
                            $wheres[] = 
                                array(
                                    'operator'=>'OR',
                                    'operand'=>array(
                                        0=> array(
                                            'operator'=>'FIELD_LIMIT',
                                            'style'=>'equals',
                                            'field'=>$unique_field_obj->getName(),
                                            'data'=>array('value'=>$unique_field_obj->getDBValue())
                                            ),
                                        1=>array(
                                            'operator'=>'FIELD_LIMIT',
                                            'style'=>'null',
                                            'field'=>$unique_field_obj->getName()
                                            )
                                        )
                                    );
                        
                        }
                    }
                }
            }
        }
        if ( count( $wheres ) == 0 ) {
            //no matching conditions. don't try to do anything
            return false;
        } else if ( count( $wheres ) > 1 ) {
            $where = array( 
                'operator' => 'AND',
                'operand' => $wheres
                );
        } else {
            $where= current($wheres);
        }
        if ( ! ($id =  I2CE_FormStorage::search($form_obj->getName(),false,$where,array(), true))) {
            return false;
        }
        $form_obj = $this->factory->createContainer(array($form_obj->getName(),$id));
        //$form_obj->populate();
        return $form_obj;
    }






}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
