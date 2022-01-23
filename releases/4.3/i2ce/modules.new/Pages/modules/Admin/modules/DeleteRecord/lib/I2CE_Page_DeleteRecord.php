<?php
/**
* © Copyright 2011 IntraHealth International, Inc.
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
* @subpackage admin
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.0.11
* @since v4.0.11
* @filesource 
*/ 
/** 
* Class I2CE_Page_DeleteRecord
* 
* @access public
*/


class I2CE_Page_DeleteRecord extends I2CE_Page {
    
    protected  $cli;
    /**
     * Create a new instance of a page.
     * 
     * The default constructor should be called by any pages extending this object.  It creates the
     * {@link I2CE_Template} and {@link I2CE_User} objects and sets up the basic member variables.
     * @param array $args
     * @param array $request_remainder The remainder of the request path
     */
    public  function __construct( $args,$request_remainder , $get = null, $post = null) {
        $this->cli = new I2CE_CLI();
        $this->cli->addUsage("[--delete=XXXX]: The form|id to delete.  If not set, then user will be prompted.\n");
        parent::__construct($args,$request_remainder,$get,$post);
        $this->cli->processArgs();
    }

    
    /**
     * The business method if this page is called from the commmand line
     * @param array $request_remainder the remainder of the request after the page specfication.  
     * @param array $args the array of unix style command line arguments 
     * Arguements are link that in: http://us3.php.net/manual/en/features.commandline.php#78651
    *
    */

    protected function actionCommandLine($args,$request_remainder) { 
        if ($this->cli->hasValue('delete')) {
            $formid = $this->cli->getValue('delete');
        } else {
            $formid = trim($this->cli->ask("Please enter the form and ID of the record you wish to delete.  For example person|1000."));
        }
        if (!$formid) {
            usage("Invalid ID ($formid)");
        }
        $ff = I2CE_FormFactory::instance();
        
        $linked = array($formid);
        $first = true;
        $skips =array();
        print_r($linked);
        while (count($linked) > 0) {
            reset($linked);
            $formid = current($linked);
            array_shift($linked);
            if (in_array($formid,$skips)) {
                continue;
            }
            $skips[] = $formid;
            list($form,$id) = explode("|",$formid);
            $name = '';
            $formObj = $ff->createForm($formid);
            if (!$formObj instanceof I2CE_Form) {
                continue;
            }
            $formObj->populate();
            if ( $formObj  instanceof I2CE_List) {
                $name = ': ' . I2CE_List::lookup($id,$form);
            }
            $main_rec = '';
            foreach ($formObj->getFieldNames() as $field) {
                if ( !($fieldObj = $formObj->getField($field)) instanceof I2CE_FormField) {
                    continue;
                }
                $main_rec .= "\t$field => " . $fieldObj->getDBValue()  ."\n";
            }
            if (!$this->cli->simple_prompt("Would you like to delete records related to $formid$name?", $main_rec)) {
                continue;
            }

            $first = false;
            $child_forms = array($formid=>array());
            $formObj = $ff->createForm($formid);
            if(!$formObj instanceof I2CE_Form) {
                I2CE::raiseError("Invalid form id  $formid");
                continue;
            }

            $this->getLinkedFormIds($formObj,$child_forms[$formid]);
            $menu = array();
            $this->makeMenu($child_forms,$menu);

            $selected = $this->cli->chooseDependentTreeMenuIndices("Please select child forms related to $formid to delete",$menu,array_keys($menu));
            $which = array();
            foreach ($selected as $sel) {
                $item = explode("\n",ltrim($menu[$sel],"\t"));
                if (count($item) < 1) {
                    continue;
                }
                $which[] = trim($item[0]);
            }
            if (count($which) == 0) {
                I2CE::raiseError("Nothing selected to delete related to $formid");
                continue;
            }
            if (!$this->cli->simple_prompt("Would you like to delete all the selected forms linked  $formid  as children/grand-children?")) {
                continue;
            }
            $linked = array_unique(array_merge($linked,$this->deleteForms($child_forms, $which)));    
        }
    }
    


    protected function makeMenu($child_forms, &$menu , $indent =0) {
        $pad = str_pad('',$indent,"\t");
        $indent++;
        foreach ($child_forms as $formid=>$data) {
            if (!array_key_exists('form',$data)) {
                continue;
            }
            $menu_entry = $pad . $formid  ;
            if (array_key_exists('links',$data) && count($data['links']) > 0) {
                foreach ($data['links_info'] as $field=>$links) {
                    $menu_entry .= "\n" . I2CE_CLI::$blue. "Links By $field To " . implode(" , " , $links)  .I2CE_CLI::$black ;
                }
            }
            if (array_key_exists('links_from',$data) && count($data['links_from']) > 0) {
                foreach ($data['links_from_info'] as $formfield=>$links) {
                    $menu_entry .= "\n" . I2CE_CLI::$blue. "Linked from $formfield on form ids: " . implode(" , " , $links)  .I2CE_CLI::$black ;
                }

            }
            if (array_key_exists('parent',$data)) {
                $menu_entry .= "\n" . I2CE_CLI::$blue. "Parent Form " . $data['parent'] . I2CE_CLI::$black ;
            }
            if (array_key_exists('children',$data)) {
                $menu_entry .= "\n" . I2CE_CLI::$blue . "Child Forms:" . I2CE_CLI::$black;
            }
            $menu[] =    $menu_entry;
            if (array_key_exists('children',$data)) {
                $this->makeMenu($data['children'],$menu,$indent);
            }    
        }
    }
    



    protected function deleteForms($child_forms, $which) {
        $linked = array();
        foreach ($child_forms as $formid=>$data) {
            if (!array_key_exists('form',$data) ) {
                continue;
            }
            if (in_array($formid,$which) ) {
                I2CE::raiseError( "Deleting $formid");
                $data['form']->delete();
            }

            if (array_key_exists('links',$data)) {
                $linked = array_unique(array_merge($data['links'],$linked));
            }
            if (array_key_exists('links_from',$data)) {
                $linked = array_unique(array_merge($data['links_from'],$linked));
            }
            if (array_key_exists('parent',$data)) {
                $linked = array_unique(array_merge(array($data['parent']),$linked));
            }
            if (array_key_exists('children',$data)) {
                $linked = array_unique(array_merge($linked,$this->deleteForms($data['children'],$which)));
            }    
        }
        return $linked;
    }

    protected function getLinkedFormIds($formObj,&$child_forms) {
        if (!$formObj instanceof I2CE_Form) {
            return;
        }
        $formObj->populate();
        $child_forms['form'] = $formObj;


        $links = array();
        $fields = $formObj->getFieldNames();
        $child_forms['links_info'] = array();
        foreach ($fields as $field) {
            $fieldObj =  $formObj->getField($field);
            if (!$fieldObj instanceof I2CE_FormField_MAPPED) {
                continue;
            }
            if (!$fieldObj->isValid() || !$fieldObj->isSetValue()) {
                continue;
            }
            if ($fieldObj instanceof I2CE_FormField_MAP) {
                $links[] = $fieldObj->getMappedForm()  . '|' . $fieldObj->getMappedID();
                $child_forms['links_info'][$field] = array($fieldObj->getMappedForm()  . '|' . $fieldObj->getMappedID());
            } else if ($fieldObj instanceof I2CE_FormField_MAP_MULT) {
                $child_forms['links_info'][$field] = array();
                foreach (explode(',', $fieldObj->getDBValue()) as $val) {
                    if (!$val) {
                        continue;
                    }
                    $links[] = $val;
                    $child_forms['links_info'][$field][] =$val;
                }
            } else {
                continue;
            }
        }
        if (count($links) > 0) {
            $child_forms['links'] = $links;
        }
        $links_from = array();
        $links_from_info = array();
        if ($formObj instanceof I2CE_List) {
            $data = I2CE_List::getFieldsMappingToList($formObj);
            foreach ($data as $link_form=>$link_fields) {
                foreach ($link_fields as $link_field_name=>$link_field_obj){
                    if ($link_field_obj instanceof iHRIS_Currency) {
                        $where = array(
                            'operator'=>'FIELD_LIMIT',
                            'field'=>$link_field_name,
                            'style'=>'like',
                            'data'=>array(
                                'value'=>$formObj->getFormID() . '=%'
                                )
                            );
                    } else    if ($link_field_obj instanceof I2CE_FormField_MAP) {
                        $where = array(
                            'operator'=>'FIELD_LIMIT',
                            'field'=>$link_field_name,
                            'style'=>'equals',
                            'data'=>array(
                                'value'=>$formObj->getFormID()
                                )
                            );
                    } else {
                        $where = array(
                            'operator'=>'OR',
                            'operand'=>array(
                                array(
                                    'operator'=>'FIELD_LIMIT',
                                    'field'=>$link_field_name,
                                    'style'=>'like',
                                    'data'=>array(
                                        'value'=>$formObj->getFormID() . ',%' 
                                        )
                                    ),
                                array(
                                    'operator'=>'FIELD_LIMIT',
                                    'field'=>$link_field_name,
                                    'style'=>'like',
                                    'data'=>array(
                                        'value'=>'%,' .$formObj->getFormID() 
                                        )
                                    ),
                                array(
                                    'operator'=>'FIELD_LIMIT',
                                    'field'=>$link_field_name,
                                    'style'=>'like',
                                    'data'=>array(
                                        'value'=>'%,' . $formObj->getFormID() . ',%' 
                                        )
                                    ),
                                array(
                                    'operator'=>'FIELD_LIMIT',
                                    'field'=>$link_field_name,
                                    'style'=>'equals',
                                    'data'=>array(
                                        'value'=>$formObj->getFormID()  
                                        )
                                    )
                                )
                                
                            );
                    }
                    $matches = I2CE_FormStorage::search($link_form,false,$where);
                    if ( $matches ) {
                        foreach ($matches as $match) {
                            $links_from[] = $link_form . '|' . $match;
                            if (!array_key_exists($link_form . '+' . $link_field_name,$links_from_info)) {
                                $links_from_info[$link_form . '+' . $link_field_name] = array();
                            }
                            $links_from_info[$link_form . '+' . $link_field_name][] =  $link_form . '|' . $match;
                        }
                    }
                }
            }
        }
        if (count($links_from) > 0) {
            $child_forms['links_from'] = $links_from;
            $child_forms['links_from_info'] = $links_from_info;
        }
        if ( ( $parentField = $formObj->getField('parent')) instanceof I2CE_FormField){
            if ( ($parent = $parentField->getDBValue())) {
                $child_forms['parent'] = $parent;
            }
        }
    
        foreach ($formObj->getChildForms() as $child_form) {
            $formObj->populateChildren($child_form);
            $children = $formObj->getChildren($child_form);
            if (!is_array($children) || count($children) == 0) {
                continue;
            }

            foreach ($children as $childObj) {
                if (!$childObj instanceof I2CE_Form) {
                    I2CE::raiseError("Baddness getting $child_form of " . $formObj->getFormID() . "\n" . gettype($childObj));
                    continue;
                }
                if (!array_key_exists('children',$child_forms)) {
                    $child_forms['children'] = array();
                }
                $child_forms['children'][$childObj->getFormID()] = array();
                $this->getLinkedFormIds($childObj,$child_forms['children'][$childObj->getFormID()]);
            }
        }    
        return;
    }



  }


 
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
      
