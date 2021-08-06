<?php
/**
 * @copyright © 2007, 2009 Intrahealth International, Inc.
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
*  I2CE_FormBrowser
* @package I2CE
* @subpackage Core
* @author Carl Leitner <litlfred@ibiblio.org>
* @version 2.1
* @access public
*/


class I2CE_FormBrowser extends I2CE_Fuzzy {
    /**
     * @var I2CE_Page $page.   
     */
    protected $page;
    protected $template;
    protected $formBrowserPrefix;
    protected $action;



    /**
     * Th constructor
     * @param I2CE_Page $page
     * @param string $action.  What action to perform for this form browser.  Valid options are 'showForm', 'editForm' and 'saveForm'
     * @param array $options.  An associatve array.  The following keys are recognized:
     * 'FBPrefix' => The prefix of all id's related to the formBrowser.  If not set 'formBrowser' is used.
     * (This enables having ajax functionality for
     * multiple formBrowsers on the same page).
     */
    public function __construct($page,$action = 'showForm',$options) {
        if( !(array_key_exists('FBPrefix',$options)) || $options['FBPrefix'] === null) {
            $options['FBPrefix'] = "formBrowser";
        }
        $this->page = $page;
        $this->template = $page->getTemplate();
        $this->formBrowserPrefix = $options['FBPrefix'];
        $this->action = $action;
    }

    /***
     * Adds the form field  nodes that will evenutally the details of the specified field.
     * The eventually is because we expect the setForm() to be called
     * @param string $form
     * @param string $file
     * @param boolean $edit Wether or not this form should be editable
     * @param boolean $even (Defaults to false). If true, will add the class 'even' to the created form field nodes.
     */
    protected function showFieldDetailsForRecord($detailNode,$formObj,$field, $edit,  $even = false,$chain = false ) {
        $form = $formObj->getName();
        $formClassConfig = I2CE::getConfig()->traverse("/modules/forms/formClasses/");
        $factory = I2CE_FormFactory::instance();
        $classes = $factory->getClassHierarchy($form);
        foreach ($classes as $class) {
            $fieldConfig = $formClassConfig->traverse($class . "/fields/$field", false,false);            
            if ($fieldConfig instanceof I2CE_MagicDataNode) {
                break;
            }           
        }
        if (!$fieldConfig instanceof I2CE_MagicDataNode) {
            I2CE::raiseError("No field config data associated with $form:$field");
            return;
        }
        $href = '';
        if ($edit) {
            $file = "formBrowser_form_details_record_edit.html";
            $tag = "tr";
        } else {
            $fieldConfig->setIfIsSet($href,"meta/linked_page");
            if ($href) {
                $file = "formBrowser_form_details_record_link.html";
                $tag = "tr";
            } else {
                $file = "formBrowser_form_details_record.html";
                $tag = "div";
            }
        }
        $fieldNode = $this->template->appendFileById( $file, $tag, "particular_record" , false, $detailNode);
        if (!$fieldNode instanceof DOMNode) {
            return;
        }
        if ((!$edit) && ($href)) {
            $hrefNode = $this->template->getElementById("form_linked_field",$fieldNode);
            if ($hrefNode instanceof DOMNode) {
                $href .= $formObj->getId();                
                $hrefNode->setAttribute('href',$href);
            }
            $formFieldNode = $this->template->getElementByName("form_field",0,$fieldNode);
        } else {
            $formFieldNode = $this->template->getElementByName("form_field",0,$fieldNode);
        }
        if (!$formFieldNode instanceof DOMElement) {
            return;
        }        
        $formFieldNode->setAttribute('name',$form . ':' . $field);
        if (($edit) || ($href && !$edit)) { 
            $this->template->setDisplayDataImmediate('form_field_head',$field,$fieldNode);
            $evenNode = $fieldNode;
        } else {
            $formFieldNode->setAttribute("head",$field);
            $evenNode = $formFieldNode;
        }
        if ($even && ($evenNode instanceof DOMElement)) {
            $evenNode->setAttribute("class","even");
        }
        $linkedFormName = "";
        $useMap = false;
        $mapConfig = $fieldConfig->traverse("setMap",false,false);
        if (!$mapConfig instanceof I2CE_MagicDataNode) {
            return;
        }
        $mapConfig->setIfIsSet($useMap,"useMap");
        if ($useMap && !$edit && !$href) {
            $linkedFormName = $form; 
            $mapConfig->setIfIsSet($linkedFormName,"form");
            $formObj->getField( $field )->setHref( "formBrowser/showForm/$linkedFormName?FBPrefix={$this->formBrowserPrefix}&id=");
        }
        if ($edit && is_array($chain)) {
            $chainNode = $formFieldNode;
            foreach ($chain as $link_field) {
                $t_chainNode = $this->template->loadFile("formBrowser_form_details_record_edit_link.html","span");
                $t_chainNode->setAttribute("name", $form . ":" . $link_field);
                $chainNode->appendChild($t_chainNode);
                $chainNode = $t_chainNode;
            }
        }
    }

    /**
     * Shows details the underlying database information and class information of a  form field.
     * Intend to be called when there is no form instance we are going to set (i.e. no id for a record is
     * expected to be give)
     * @param string $form
     * @param I2CE_MagicDataNode $fieldConfig.  The magic data node /modules/forms/formClasses/$form/fields/$fieldName
     * where $fieldName is the name of the field we are adding
     * @param boolean $even (Defaults to false). If true, will add the class 'even' to the created form field nodes.
     */
    protected function showFieldDetails($detailNode,$form, $fieldConfig,$even = 'I2CE_FormField') {
        $field = $fieldConfig->getName();
        die("deprecated bbaddness in i2ceformbrowser");
        $details = I2CE_FormField::getFormFieldIdAndType($form,$field);
        $fieldNode = $this->template->appendFileById( "formBrowser_form_details_no_record.html", "tr", "particular_record" , false, $detailNode);
        if (!$fieldNode instanceof DOMNode) {
            return;
        }
        if ($even) {
            $fieldNode->setAttribute('class','even');
        }
        $fieldType = "";
        $fieldClass = "";
        $fieldConfig->setIfIsSet($fieldType,"formfield");
        I2CE::getConfig()->setIfIsSet($fieldClass,"/modules/forms/FORMFIELD/$fieldType");
        $this->template->setDisplayDataImmediate('field_class',$fieldClass,$fieldNode);
        $this->template->setDisplayDataImmediate('field_type',$fieldType,$fieldNode);
        $this->template->setDisplayDataImmediate('field_name',$field,$fieldNode);
        $this->template->setDisplayDataImmediate('field_id',$details['id'],$fieldNode);
    }



    /**
     * Create a form browser node and append it to the specifed node
     * @param string $form
     * @param integer $id.  The record id of the form we wish to browser or an integer < 1 if we wish to 
     * display generic information about the structure of the form.
     * @param mixed $appendNode.  Either a DOMNode or a string which is the id of a node.  We will append the
     * formBrowser to this node.
     */
    public  function getFormBrowser($form,$id) {
        $factory = I2CE_FormFactory::instance();
        if (! ($id > 0)) {
            $id = -1;
        }
        if ($this->action == 'editForm') {
            $detailNode = $this->template->loadFile( "formBrowser_form_details_edit.html", "div" );
        } else { 
            $detailNode = $this->template->loadFile( "formBrowser_form_details.html", "div" );
        }
        if (!$detailNode instanceof DOMNode) {
            return null;
        }
        $contentNode = $this->template->getElementById('formBrowser_content',$detailNode);
        if (!$contentNode instanceof DOMElement) {
            I2CE::raiseError("Unable to find node with id 'formBrowser_content'");
            return $detailNode; 
        } 
        $formConfig = I2CE::getConfig()->traverse("/modules/forms/forms/$form",false,false);
        if (!$formConfig instanceof I2CE_MagicDataNode) {
            return $detailNode;
        }
        $contentNode->setAttribute('id', $this->formBrowserPrefix . '_content');
        $formDispName = '';
        $formDesc ='';
        $formConfig->setIfIsSet($formDispName,'display');
        $formConfig->setIfIsSet($formDesc,"meta/description");
        $this->template->setDisplayDataImmediate("form_name",$form,$detailNode);
        $this->template->setDisplayDataImmediate("form_dispname",$formDispName,$detailNode);
        $this->template->setDisplayDataImmediate("form_desc",$formDesc,$detailNode);
        $formClass = '';
        $formConfig->setIfIsSet($formClass,'class');
        if ( (!is_string($formClass)) || strlen($formClass) == 0) {
            $detailNode->appendChild($this->template->createTextNode("Warning: no class is associated to this form"));
            return $detailNode;
        }
        $this->template->setDisplayDataImmediate("form_class",$formClass,$detailNode);
        if ($id > 0) {
            $formObj = $factory->createContainer($form.'|'.$id);
            $formObj->populate();
        }
        $this->addRecordSelect($formConfig,$detailNode,$id); 
        $even = false;
        if ( $this->addChildForms($detailNode,$formConfig,$formObj)) {
            $even = true;
        }
        if ($this->addParentLink($detailNode,$formConfig,$formObj, $even)) {
            $even = !$even;
        }
        if ($this->addEditLink($detailNode,$form,$id,$even)) {
            $even = !$even;
        } 
        if ($this->addSaveLink($detailNode,$form,$id,$even)) {
            $even = !$even;
        } 

        $fields = $factory->getFieldNames($form);
        $formClassConfig =  I2CE::getConfig()->traverse("/modules/forms/formClasses/$formClass", false,false);
        $tableNode = $this->template->getElementById('particular_record',$detailNode);
        if (!$tableNode instanceof DOMNode) {
            $fields = array();
        }
        $prefixNode = $this->template->getElementById('fb_prefix',$detailNode);
        if ($prefixNode instanceof DOMNode) {
            $prefixNode->setAttribute('value',$this->formBrowserPrefix);
        }
        $formClassBase = I2CE::getConfig()->modules->forms->formClasses;
        if ($formObj instanceof I2CE_Form) {    
            $chains = array();
            $linked = array();
            if ($this->action == 'editForm') {
                $chains = $this->getLinkChains($fields, $formClassBase);
            }
            //echo "<pre>"; print_r($chains); echo "</pre>";
            foreach ($chains as $chain) {
                foreach ($chain as $field) {
                    $linked[$field] = true;
                }
            }           
            $fields = array_keys($fields);
            $edit = $this->action == 'editForm';
            foreach ($fields as $field) {
                $even = !$even;
                if (! ($linked[$field] === true)) {
                    $this->showFieldDetailsForRecord($detailNode,$formObj,$field, $edit, $even);
                } 
            }
            foreach ($chains as $chain) {
                $field = array_shift($chain);
                $this->showFieldDetailsForRecord($detailNode,$formObj,$field,$edit, $even, $chain);
            }
            $this->template->setForm($formObj,$tableNode);
        } else {
            foreach ($fields as $field=>$t_formClass) {
                $t_formClassConfig =  $formClassBase->traverse($t_formClass, false,false);
                if ($t_formClassConfig instanceof I2CE_MagicDataNode) {
                    $even = !$even;
                    $this->showFieldDetails($detailNode,$form,$t_formClassConfig->fields->$field,$even);
                }
            }
        }
        return $detailNode;
    }
        

    protected function getLinkChains($fields,$formClassBase) {
        $chains = array();
        $field_data = array();
        foreach ($fields as $field=>$t_formClass) {
            $t_fieldClassConfig =  $formClassBase->traverse($t_formClass .'/fields/' . $field, false,false);
            if (!$t_fieldClassConfig instanceof I2CE_MagicDataNode) {
                continue;
            }
            $field_data[$field] = $t_fieldClassConfig->getAsArray();
        }
        //echo "<pre>"; var_dump($field_data); echo "</pre>";
        $links =array();
        foreach ($field_data as $field=>$data) {
            if ($data['setLink']['link']) {
                $links[ $data['setLink']['link']] = $field;
            } 
        } //the keys of $links are this form's fields that, when selected, will modify the select options of the field named by the value.
        //now we try to colate these $links into chains.
        foreach ($links as $top=>$bottom) {
            $found = false;
            foreach ($chains as $indx=>$chain) {
                if (in_array($top,$chain)) {
                    array_push($chain,$bottom);
                    $chains[$indx] = $chain;
                    $found =true;
                    break;
                } 
                if (in_array($bottom,$chain)) {
                    array_unshift($chain,$top);
                    $chains[$indx] = $chain;
                    $found =true;
                    break;
                } 
            }
            if (!$found) {
                $chains[] = array($top,$bottom);
            }
        }
        return $chains;
    }

    /**
     * Add the edit Link 
     * @param DOMNode $detailNode.  The major node which contains the formBrowser we are creating
     * @param  I2CE_MagicDataNode $formConfig /modules/forms/forms/$formName
     * @param boolean $even.  If true,  makes this have class 'even'
     */ 
    protected function addEditLink($detailNode,$form,$id,$even) { 
        if ($this->action == 'saveForm'   || ! ($id> 0) ) {
            //remove the link
            $this->template->removeNodeById('edit_form_row', $detailNode);
            return false;
        } else {
            $this->template->setDisplayDataImmediate('edit_form_link','formBrowser/editForm/' . $form . '/' . $id,$detailNode);
            $node = $this->template->getElementById('edit_form_row',$detailNode);
            if ($node instanceof DOMNode) {
                $added = true;
                if (!$even) {
                    $node->setAttribute('class','even');
                } else {
                    $node->setAttribute('class','');
                }
            }
            $formNode =$this->template->getElementById('form_edit_button',$detailNode);
            if ($formNode instanceof DOMNode) {
                $formNode->setAttribute('id',$this->formBrowserPrefix . '_form_edit_button' );
            }
            if ($this->page->hasAjax()) {
                $this->page->addAjaxUpdate(
                    $this->formBrowserPrefix.'_content',$this->formBrowserPrefix . '_form_edit_button','click',
                    "formBrowser/editForm/$form?FBPrefix={$this->formBrowserPrefix}&id=$id",$this->formBrowserPrefix.'_content',true,'',true);
            }
            return true;
        }
    }

    /**
     * Add the save Link 
     * @param DOMNode $detailNode.  The major node which contains the formBrowser we are creating
     * @param  I2CE_MagicDataNode $formConfig /modules/forms/forms/$formName
     * @param boolean $even.  If true,  makes this have class 'even'
     */ 
    protected function addSaveLink($detailNode,$form,$id,$even) { 
        if ( ! ( $id> 0)  || !$this->action =='saveForm' || $this->action == 'showForm') {
            //remove the link
            //$this->template->removeNodeById('form_save_row');
            return false;
        } else {
            $this->template->setDisplayDataImmediate('form_save_link','formBrowser/saveForm/' . $form . '/' . $id,$detailNode);
            $node = $this->template->getElementById('form_save_row',$detailNode);
            if ($node instanceof DOMNode) {
                $added = true;
                if (!$even) {
                    $node->setAttribute('class','even');
                } else {
                    $node->setAttribute('class','');
                }
            }
            return true;
        }
    }
    /**
     * Add the select records.
     * @param DOMNode $detailNode.  The major node which contains the formBrowser we are creating
     * @param  I2CE_MagicDataNode $formConfig /modules/forms/forms/$formName
     * @param I2CE_Form $formObj the form object for the form we are displaying ( or null.if we are not looking at a particular record)
     * @param boolean $even.  If true,  makes this have class 'even'
     */ 
    protected function addParentLink($detailNode,$formConfig,$formObj,$even) { 
        $parentFormLink = '';
        $parentForm  = '';
        $added = false;
        $factory = I2CE_FormFactory::instance();
        if ($formObj instanceof I2CE_Form) {            
            $parentId = $formObj->getParentID();
            if ($parentId > 0) {
                $parentForm = $factory->lookupFormByRecordId($parentId);
            }
            if ($parentForm) {
                $node = $this->template->getElementById('parent_form_row',$detailNode);
                if ($node instanceof DOMNode) {
                    $added = true;
                    if ($even) {
                        $node->setAttribute('class','even');
                    } else {
                        $node->setAttribute('class','');
                    }
                }
                $parentFormLink = "formBrowser/showForm/$parentForm?FBPrefix={$this->formBrowserPrefix}&id=$parentId";
            } else {
                $even = !$even;
            }
        }        
        $this->template->setDisplayDataImmediate("parent_form_name",$parentForm,$detailNode);
        $this->template->setDisplayDataImmediate("parent_form_link",$parentFormLink,$detailNode);
        $formNode =$this->template->getElementById('form_view_parent_button',$detailNode);
        if ($formNode instanceof DOMNode) {
            $formNode->setAttribute('id',$this->formBrowserPrefix . '_form_view_parent_button' );
        }
        if ($this->page->hasAjax() && $added) {
            if ($parentForm) {
                $this->page->addAjaxUpdate(
                    $this->formBrowserPrefix.'_content',$this->formBrowserPrefix.'_form_view_parent_button','click',
                    "formBrowser/showForm/$parentForm?FBPrefix={$this->formBrowserPrefix}&id=$parentId",$this->formBrowserPrefix.'_content',true,'',true);
            }
        }                              
        return $added;
    }


    /**
     * Add the select records.
     * @param DOMNode $detailNode.  The major node which contains the formBrowser we are creating
     * @param  I2CE_MagicDataNode $formConfig /modules/forms/forms/$formName
     */
    protected function addRecordSelect($formConfig, $detailNode, $id) {
        $selectNode = $this->template->getElementById('particular_record_select',$detailNode);        
        if (!$selectNode instanceof DOMNode) {
            return;
        }
        $form = $formConfig->getName();
        $factory = I2CE_FormFactory::instance();
        $recordIds = $factory->getRecords($form);
        foreach ($recordIds as $recordId) {
            $text = 'Id: ' . $recordId;
            $primary_fields = array();
            $formConfig->setIfIsSet($primary_fields,"metadata/primary_field",true);
            if (!is_array($primary_fields)) {
                $primary_fields = array($primary_fields);
            }
            if (count($primary_fields) > 0) {
                $text .= ' --';
                $formObj = $factory->createContainer($form.'|'.$recordId);
                $formObj->populate();
                if (!$formObj instanceof I2CE_Form) {
                    $primary_fields = array();
                }
            }
            ksort($primary_fields); 
            foreach ($primary_fields as $field) {
                $text .= " " .  $formObj->getField($field)->getDisplayValue();
            }
            $selectNode->appendChild(
                $this->template->createElement('option',array('value'=>$recordId),$text)
                );
            if ($formObj instanceof I2CE_Form) {
                $formObj->cleanup();
            }
        }       
        $optionNode = $this->template->query(".//option[@value='$id']",$selectNode);
        if ($optionNode->length == 1) {
            $optionNode->item(0)->setAttribute("selected","selected");
        }
        foreach (array("form_view_record", "particular_record_select") as $id) {
            $formNode =$this->template->getElementById($id,$detailNode);
            if ($formNode instanceof DOMNode) {
                $formNode->setAttribute('id',$this->formBrowserPrefix . '_' . $id);
            }
        }
        if ($this->page->hasAjax() ) {
            $this->page->addAjaxUpdate(
                $this->formBrowserPrefix.'_content',$this->formBrowserPrefix . '_particular_record_select','change',
                "formBrowser/showForm/$form?FBPrefix={$this->formBrowserPrefix}",$this->formBrowserPrefix.'_content',true,
                $this->formBrowserPrefix.'_form_view_record',true);
            $this->template->removeNodeById('form_view_record_button', $detailNode);
        }
    }



    /**
     * Adds any child forms for this form.
     * @param DOMNode $detailNode.  The major node which contains the formBrowser we are creating
     * @param  I2CE_MagicDataNode $formConfig /modules/forms/forms/$formName
     * @param I2CE_Form $formObj the form object for the form we are displaying ( or null.if we are not looking at a particular record)
     * @returns boolean.  Return false if none were added, true if child forms were added.
     */
    public function addChildForms($detailNode,$formConfig, $formObj) {
        $view_child = '';
        $child_forms = array();
        $formConfig->setIfIsSet($child_forms,"meta/child_forms",true);
        if (count($child_forms) > 0) {
            $view_child = "formBrowser/showForm";
        }
        $prefixNode = $this->template->getElementById('child_fb_prefix',$detailNode);
        if ($prefixNode instanceof DOMNode) {
            $prefixNode->setAttribute('value',$this->formBrowserPrefix);
        }
        $this->template->setDisplayDataImmediate('form_view_child',$view_child,$detailNode);        
        $selectNode = $this->template->getElementById('child_form_select',$detailNode);
        $added = false;
        if ($selectNode instanceof DOMNode && $formObj instanceof I2CE_Form) {
            foreach ($child_forms as $child) {
                $childIds = $formObj->getChildIds($child);
                foreach($childIds as $childId) {
                    $added = true;
                    $selectNode->appendChild(
                        $this->template->createElement('option',array('value'=>$child .':' . $childId),$child . ' Id: ' . $childId)
                        );
                }
            }
        } else if ($selectNode instanceof DOMNode) {
            foreach ($child_forms as $child) {
                $added = true;
                $selectNode->appendChild(
                    $this->template->createElement('option',array('value'=>$child),$child)
                    );
            }            
        }
        foreach (array("form_view_child", "form_view_child_button") as $id) {
            $formNode =$this->template->getElementById($id,$detailNode);
            if ($formNode instanceof DOMNode) {
                $formNode->setAttribute('id',$this->formBrowserPrefix . '_' . $id);
            }
        }
        if ($this->page->hasAjax() && $added) {
            $this->page->addAjaxUpdate(
                $this->formBrowserPrefix.'_content',$this->formBrowserPrefix.'_form_view_child_button','click',
                "formBrowser/{$this->formBrowserPrefix}",$this->formBrowserPrefix . '_content',true,  
                $this->formBrowserPrefix.'_form_view_child',true);  
        }
        if (!$added) {
            return false;
        } else {
            return true;
        }
    }
        




}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
