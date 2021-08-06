<?php
/**
 * @copyright © 2007, 2008, 2009 Intrahealth International, Inc.
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
 * View the details for then given record that is an instance of a I2CE_List.
 * @package I2CE
 * @subpackage Common
 * @access public
 * @author Carl Leitner
 * @since v2.0.0
 * @version v2.0.0
 */

/**
 * The page class for displaying a I2CE_List record.
 * @package I2CE
 * @subpackage Common
 * @access public
 */

class I2CE_PageAutoList extends I2CE_PageFormAuto {


    protected function getAction() {
	if ( count($this->request_remainder) > 0) {
	    return $this->request_remainder[0];  //view, edit and remap are handled
	} else {
	    return 'menu';
	}

    }


    protected function getMDRoot() {
	if ($this->module == 'I2CE') {
	    return '/I2CE/page/' . $this->page .'/args';
	} else {
	    return '/modules/' . $this->module . '/page/' . $this->page .'/args';
	}
    }

    protected function getPrimaryFormName() {	
        $form = false;
        if ($this->request_exists('type') && I2CE_MagicDataNode::checkKey($type =  $this->request('type'))) {
	    if ( I2CE::getConfig()->setIfIsSet($form,$this->getMDRoot() .  '/forms/' . $type . '/form') && $form) {
		return $form;
	    } else {
		return $type;
	    }
        } else if ($this->request_exists('form') && is_scalar($form = $this->request('form'))&& $form) {
            return $form;
        } else if ($this->request_exists('form_name') && is_scalar($form_name = $this->request('form_name'))&& $form_name) {
            return $form_name;
        } else {
            return false;
        }
    }



    protected function getPrimary() {
        if (!$this->primaryObject instanceof I2CE_Form) {
            $ff = I2CE_FormFactory::instance();
            $this->primaryObject= $ff->createContainer($this->getPrimaryFormName());
        }
        return $this->primaryObject;
    }




    protected function loadHTMLTemplates() {
	if ($this->getAction() == 'edit') {
	    if (! ($node = $this->template->getElementById('siteContent')) instanceof DOMNode
		|! $listConfig = $this->getListConfig()
		) {
		return false;
	    }
	    $this->template->setDisplayData('type',$listConfig['type']);
	    $this->template->setDisplayData('form_name',$listConfig['form']);
	    //$this->template->setDisplayData('button_return',$this->getViewLink());
	    $listConfig['is_edit'] = true;
	    $gizmo = new I2CE_Gizmo_List($this,$this->primaryObject,$listConfig);
	    $gizmo->generate($node);
	    return true;
	} else if ($this->getAction() == 'menu') {
	    $this->template->appendFileById("auto_list.html", 'div', 'siteContent' );	
	} else if ($this->getAction() == 'remap') {
	    $this->template->appendFileById("remap.html", 'div', 'siteContent' );	
	}
        return true;

    }

    /**
     * Checks to see if there are any permissions in the page's args for the given action.
     * If so, it evaluates them.  If not returns true.
     * @returns boolean
     */
    protected function checkActionPermission($action) {
	if ($this->getAction() == 'menu') {
	    return true;
	}
        if (!parent::checkActionPermission($action)) {
            return false;
        }
	if ($this->getAction() =='edit') {
	    if (!($primaryFormName = $this->getPrimaryFormName())) {
		return false; //weirdness.  should just stop whatever is happening
	    }
	    $listConfig = $this->getListConfig();
	    if (array_key_exists('edit_task',$listConfig) && $listConfig['edit_task']) {
		return $this->hasPermission("task(" .  $listConfig['edit_task'] . ")");
	    } else {
		return true;
	    }
	} else {
	    return true;
	}
    }



    public function __construct( $args,$request_remainder, $get = null,$post = null) {
        parent::__construct( $args,$request_remainder,$get,$post);
        if (!array_key_exists('auto_template',$this->args) || !is_array($this->args['auto_template'])) {
            $this->args['auto_template'] = array();
        }
    }


    protected function action_display() {
	if ($this->getAction() == 'menu'
	    || !is_array( $listConfig = $this->getListConfig())
	    || !$this->primaryObject instanceof I2CE_List) {
	    return $this->action_menu();
	} else if ($this->getAction() == 'view') {
	    if ($this->primaryObject->getID() != '0') {
		return $this->action_view_form();
	    } else  if (array_key_exists('field',$listConfig) && $listConfig['field']) {
		return $this->action_select_field($listConfig);
	    } else  {
		return $this->action_select_list($listConfig);
	    }
	} else if ($this->getAction() == 'edit') {
	    return parent::action_display();
	}
	
    }

    /**
     * Handles creating hte I2CE_TemplateMeister templates and loading any default templates
     * @returns boolean true on success
     */
    protected function initializeTemplate() {
	if ( $this->getAction() =='menu' ||  ! ($listConfig = $this->getListConfig())) {
	    $title = "Administer Database Lists";
	    I2CE::getConfig()->setIfIsSet($title,"/modules/Lists/message/menu_title");
	    $this->args['title'] = $title;
	} else {
	    $this->args['title'] = $listConfig['title'];
	}
	return parent::initializeTemplate();
    }


    protected function setDisplayData() {
        parent::setDisplayData();
	if ($this->getAction() == 'menu') {
	    $listConfig = $this->getListConfig();
	    if ( ($primary = $this->getPrimary()) instanceof I2CE_List) {
		$this->template->setDisplayData( "type_name", $primary->getDisplayName() );
		$this->template->setDisplayData( "form", $primary->getName() );
		$this->template->setDisplayData( "form_name", $primary->getName() );
		$this->template->setDisplayData( "id", $primary->getNameId() );
	    }
	    if (!$listConfig['type']){
		//$this->template->setDisplayData( "form", $this->request('form'));
		$this->template->setDisplayData( "form_name", $this->request('form_name'));
	    } else {
		$this->template->setDisplayData( "type", $this->request('type'));
	    }
	    $this->template->setDisplayData( "link", $this->request("link") );
	    if (I2CE_FormStorage::isWritable($this->getPrimaryFormName())) {
		$this->template->setDisplayData( "list_is_writable", 1);
	    } else {
		$this->template->setDisplayData( "list_is_writable", 0);
	    }
	}
    }




    protected function getListConfig($type = null, $form = null) {
        $listConfig = null;
	if ($form === null) {
	    if ($this->request_exists('form') && I2CE_MagicDataNode::checkKey($this->request('form'))) {
		$form = $this->request('form');
	    } else if ( $this->request_exists('form_name') && I2CE_MagicDataNode::checkKey($this->request('form_name'))) {
		$form = $this->request('form_name');
	    }

	}
        if ($type === null) {
            if ($this->request_exists('type') && I2CE_MagicDataNode::checkKey($this->request('type'))) {
                $type = $this->request('type');
            }
	    if ($type === null) {
		$type = $form;
	    }
	} 
	if ($type === null) {
	    I2CE::raiseError("No Valid Type");
	    return array();
	}
	$listConfig  = I2CE::getConfig()->getAsArray($this->getMDRoot() .'/forms/' . $type);
        if (!is_array($listConfig)) {
            $listConfig = array();
        }
	$listConfig['type'] = $type;
        if ($form === null ){
	    if (array_key_exists('form',$listConfig)) {
		$form = $listConfig['form'];
	    } else {
		$form = $type;
	    }
        }
        if ($form) {
            $listConfig['form'] = $form;
            $listConfig['type'] = $type;
            $task = 'can_view_database_list_' . $form;
            if (I2CE_PermissionParser::taskExists($task)) {
                $listConfig['task'] =$task;
            }
            $edit_task = 'can_edit_database_list_' . $form;
            if (I2CE_PermissionParser::taskExists($edit_task)) {
                $listConfig['edit_task'] =$edit_task;
            }
        } 
        if (!array_key_exists('form',$listConfig) || !$listConfig['form']) {
            return false;
        }
        $form = $listConfig['form'];

        if (!array_key_exists('text',$listConfig) || !$listConfig['text']) {
            $listConfig['text']=I2CE_FormFactory::instance()->getDisplayName($form);
        }
        if (!array_key_exists('title',$listConfig)) {
            $title = "View %s";
            I2CE::getConfig()->setIfIsSet($title,'/modules/Lists/messages/title');
            $title = @vsprintf($title,$listConfig['text']);
            $listConfig['title'] = $title;
        }
        if (!array_key_exists('field_data',$listConfig) || !is_array($listConfig['field_data'])) {
            $listConfig['field_data']= array();
        }
        if ($this->request('remap') && $this->request('remap') ) {
            $listConfig['default_disabled'] =1;
            $listConfig['field_data']['remap']=array('enabled'=>1);
	}
	$this->setEditLinks($listConfig,$type,$form);
        return $listConfig;
    }


    protected function setEditLinks(&$listConfig,$type,$form) {
        if (!array_key_exists('edit_links',$listConfig) || !is_array($listConfig['edit_links'])) {
            $listConfig['edit_links'] = array();
        }
        if (!array_key_exists('edit',$listConfig['edit_links'])) {
            $text = 'Edit this information';
            I2CE::getConfig()->setIfIsSet($text,"/modules/Lists/messages/edit_text");
            if ($type) {
                $href = $this->page . "/edit?type=$type&id=";
            } else {
                $href = $this->page . "/edit?form_name=$form&id=";
            }
            $data =  array(
                    'href'=>$href,
                    'formfield'=>"$form:id",
                    'text'=>$text
                    );
            if (array_key_exists('edit_task',$listConfig)) {
                $data['task'] = $listConfig['edit_task'];
            }
            $listConfig['edit_links']['edit'] = $data;
        }
        if (!array_key_exists('select',$listConfig['edit_links'])) {
            if ($type) {
                $href = $this->page  . "/view?type=$type";
            } else {
                $href = $this->page . "/view?form_name=$form";
            }
            $text = 'Select another %';
            I2CE::getConfig()->setIfIsSet($text,"/modules/Lists/messages/select_text");
            $text = @vsprintf($text,$listConfig['text']);
            $data =  array(
                    'href'=>$href,
                    'formfield'=>false,
                    'text'=>$text
                    );
            if (array_key_exists('task',$listConfig)) {
                $data['task'] = $listConfig['task'];
            }
            $listConfig['edit_links']['select'] = $data;
        }
        if (!array_key_exists('new',$listConfig['edit_links'])) {
            if ($type) {
                $href = $this->page . "/edit?type=$type";
            } else {
                $href = $this->page . "/edit?form_name=$form";
            }
            $text = 'Add new %s';
            I2CE::getConfig()->setIfIsSet($text,"/modules/Lists/messages/new_text");
            $text = @vsprintf($text,$listConfig['text']);
            $data =  array(
                    'href'=>$href,
                    'formfield'=>false,
                    'text'=>$text
                    );
            if (array_key_exists('task',$listConfig)) {
                $data['task'] = $listConfig['task'];
            }
            $listConfig['edit_links']['new'] = $data;
        }

        if ($this->user->getRole()=='admin' ) {
            $href = $this->page . "/edit?remap=1&form_name=" .$form ."&id=";
            $text = 'Set remapping data';
            I2CE::getConfig()->setIfIsSet($text,"/modules/Lists/messages/remap_text");
            $data =  array(
                    'href'=>$href,
                    'formfield'=>$form. ':id',
                    'text'=>$text
                    );
            $listConfig['edit_links']['set_remap'] = $data;
        }
        if ( !I2CE_FormStorage::isWritable( $form ) ) {
            unset( $listConfig['edit_links']['edit'] );
            unset( $listConfig['edit_links']['new'] );
            unset( $listConfig['edit_links']['set_remap'] );
        }
    }

    function setRemapLinks(&$listConfig,$obj) {
        if (!is_array($listConfig) || !array_key_exists('edit_links',$listConfig) || !is_array($listConfig['edit_links']) 
                || !$obj instanceof I2CE_List || !($remapFieldObj=$obj->getField('remap')) instanceof I2CE_FormField_MAP 
                || !($remapID = $remapFieldObj->getMappedID()) || !($remapForm = $remapFieldObj->getMappedForm())
                || ($this->user->getRole() != 'admin') 
           ) {
            return;
        }
        $ff = I2CE_FormFactory::instance();
        $sourceForm = $obj->getName();
        $sourceId = $obj->getId();       
        foreach ($ff->getForms() as $form) {
            if (! ($sObj = $ff->createContainer($form)) instanceof I2CE_Form) {
                continue;
            }
            foreach ($sObj->getFieldNames() as $sfield) {
                if (! ($sFieldObj = $sObj->getField($sfield)) instanceof I2CE_FormField_MAP
                        || ! in_array($sourceForm,$sFieldObj->getSelectableForms())
                   ) {
                    continue;
                }
                $where = array(
                        'operator'=>'FIELD_LIMIT',
                        'field'=>$sfield,
                        'style'=>'equals',
                        'data'=>array('value'=>$sourceForm.'|'.$sourceId)
                        );
                if (($count = count(I2CE_FormStorage::search($form,false,$where) ) )< 1) {
                    continue;
                }
                $href = "auto_list/remap?form=$form&field=$sfield&id=";
                $text = 'Remap field %1$s in %2%s (%3$s matches)';
                I2CE::getConfig()->setIfIsSet($text,"/modules/Lists/messages/remap_field_text");
                $text = @sprintf($text,$sfield,$form,(string) $count);
                $data =  array(
                        'href'=>$href,
                        'formfield'=>$sourceForm . ':id',
                        'text'=>$text,
                        'attributes'=>array('onclick'=>"if (confirm('Are you sure?')) {return true;} else {return false;}" ) //needs to be localized

                        );
                $listConfig['edit_links']['remap_form_'  . $form . '_field_' . $sfield] = $data;	    
            }
        }
    }


    protected $primaryObject;
    protected function action_view_form() {
        $listConfig = $this->getListConfig();
        if (! $this->primaryObject  instanceof I2CE_List) {
            if ($this->request_exists('type') && I2CE::getConfig()->setIfIsSet($form, $this->getMDRoot() . "/forms/" . $this->request('type') . '/form') && $form) {
                $append = '?type=' . $this->request('type');
            } else if ($this->request_exists('form_name') && is_scalar($form = $this->request('form_name'))&& $form) {
                $append = '?form_name=' . $this->request('form_name');
            } else if ($this->request_exists('form') && is_scalar($form = $this->request('form'))&& $form) {
                $append = '?form_name=' . $this->request('form');
            } else {
                $append = '';
            }
            $this->setRedirect($this->page. '/view/' . $append);
            return false;
        }
        $this->setRemapLinks($listConfig,$this->primaryObject);
        if( ! ($node = $this->template->getElementById( 'siteContent')) instanceof DOMNode) {
            return false;
        }
	
	//$this->args['auto_template'];
	$gizmo = new I2CE_Gizmo_List($this,$this->primaryObject,$listConfig);
	$gizmo->generate($node);
	return true;
    }



    /**
     * Create and load data for the objects used for this form.
     * 
     * Create the list object and if this is a form submission load
     * the data from the form data.  It determines the type based on the
     * {@link $type} member variable.
     */
    protected function loadObjects() {
	if ($this->getAction() =='remap') {
	    I2CE::raiseError("Is RM");
	    return true;
	} else if ($this->getAction() == 'view'  ) {
	    $listConfig = $this->getListConfig();
	    if (! ($primaryFormName = $this->getPrimaryFormName())) {
		I2CE::raiseError("Primary form name mismatch " . $primaryFormName  . ' != ' . $this->getPrimaryFormName());
		return false;
	    }
	    if (!array_key_exists('form',$listConfig)) {
		return false;
	    }
	    if ($this->request_exists('id')) {
		$id = $this->request('id');
		if (strpos($id,'|')=== false) {
		    I2CE::raiseError("Deprecated use of id variable");
		    $id = $primaryFormName . '|' . $id;
		}
	    } else {
		$id = $primaryFormName . '|0';
	    }
	    $obj = I2CE_FormFactory::instance()->createContainer($id);
	    if (!$obj instanceof I2CE_List || $obj->getName() != $listConfig['form'] ) {
		I2CE::raiseError("Not list/incorrect list");
		return false;
	    }
	    $ff = I2CE_FormFactory::instance();
	    if ($obj->getID() != '0' && !$ff->hasRecord($obj->getName(),$obj->getID())) {
		I2CE::raiseError("No record:" . $obj->getFormID());
		$obj->cleanup();
		return false;
	    }
	    $obj->populate();
	    $this->primaryObject = $obj;
	} else 	if ($this->getAction() == 'edit') {
	    parent::loadObjects();
	} 
        return true;
    }


    protected function loadGet() {
	$nosetdefault = false;
	$listConfig = $this->getListConfig();
	if (array_key_exists('nosetdefault',$listConfig) && $listConfig['nosetdefault']) {
	    $nosetdefault = true;
	}
	return !$nosetdefault;
    }





    protected $select_field =false;
    protected function getSelectField($listConfig) {
        if ($this->select_field ===false) {
            if (! ($obj = $this->getPrimary()) instanceof I2CE_List) {
                I2CE::raiseError("primary not list");
                return false;
            }
            $select_field_name =false;
            if (!array_key_exists('field',$listConfig) || !$listConfig['field']) {
                return false;
            }
            $this->select_field = $obj->getField($listConfig['field']);
            $obj->load($this->request(),false,false);
            if (!$this->select_field instanceof I2CE_FormField_MAP) {
                $this->select_field =null;
                return false;
            }
            if ( $this->select_field->issetValue() ) {
                $selectObj = $this->select_field->getMappedFormObject();
                if ($selectObj instanceof I2CE_Form) {
                    //make this object avaialable for record level security
                    $this->template->setForm($selectObj);
                }
            }
        }
        return $this->select_field;
    }
    protected function action_select_list($listConfig) {	
        $node = $this->template->addFile( "auto_lists_type_list.html" );
        if ($this->module == 'I2CE') {
            $url = $this->page;
        } else {
            $url = $this->module . '/' . $this->page;
        }
	$this->template->setDisplayDataImmediate('auto_list_link',$url,$node);
	$this->template->setDisplayDataImmediate('add_new_link', $url . '/edit' ,$node);

        $this->template->appendFileById( "auto_list_type_header.html", "th", "lists_header" );        

        $this->setTemplateVars($listConfig);
        $list = I2CE_List::listOptions($this->getPrimaryFormName(),$this->showHidden());
        if ( $this->request_exists('letter') ) {
            $list = array_filter( $list, "self::filter_by_" . $this->request('letter') );
        }
        $page_size = 50;
        if (array_key_exists('page_length',$this->args)) {
            $page_size = $this->args['page_length'];
        }
        if ( count($list) > 0 ) {
            if (!  ($list = $this->paginateList($list,array('form_name'=>$this->getPrimaryFormName()),'select_list',$page_size))) {
                return false;
            }
        }
        return $this->actionDisplayList_row($list,$listConfig);
    }

    protected function setTemplateVars($listConfig,$link_data = array()) {
        if (!is_array($link_data)) {
            $link_data = array();
        }
        $this->template->setDisplayData('text',$listConfig['text']);
        if (!$listConfig['type']) {
            $link_data['form_name']=$listConfig['form'];
        } else {
            $link_data['type']=$listConfig['type'];
        }
        if ($listConfig['type']) {
            $this->template->setDisplayData('type',$listConfig['type']);
        } else {
            $this->template->setDisplayData('form_name',$listConfig['form']);
        }
        $link_data['show_i2ce_hidden'] = $this->showHidden();
        if ($this->request_exists('letter') && $this->request('letter')) {
            $link_data['letter'] = $this->request('letter');
        }
        $this->template->setDisplayDataImmediate('add_new_link',$link_data);
        if (($hiddenSelectNode = $this->template->getElementByName('show_i2ce_hidden',0)) instanceof DOMElement) {
            $this->template->addHeaderLink("mootools-core.js");
            $h_link_data = $link_data;
            if (array_key_exists('show_i2ce_hidden',$h_link_data)) {
                unset($h_link_data['show_i2ce_hidden']);
            }
            $url = "index.php/" . $this->page . "/view?" . http_build_query($h_link_data) . '&show_i2ce_hidden=';
            $js = 'document.location.href = "' .addslashes($url) . '" + this.get("value");'; 
            $hiddenSelectNode->setAttribute('onChange',$js);
            $this->template->selectOptionsImmediate('show_i2ce_hidden',$this->showHidden());
        }
        $can_edit = true;
        if (array_key_exists('edit_task',$listConfig) && ! $this->hasPermission('task(' . $listConfig['edit_task'] .')')) {
            $can_edit =false;
        }
        if ($can_edit  && I2CE_FormStorage::isWritable($this->getPrimaryFormName())) {
            $this->template->setDisplayData( "list_is_writable", 1);
        } else {
            $this->template->setDisplayData( "list_is_writable", 0);
        }
        if ( ($link = $this->getRemapAllLink($listConfig)) && ($this->hasRemapData($listConfig))) {
            $this->template->setDisplayData('list_hasremap',1);
            $this->template->setDisplayData('remap_link',$link);
        } else {
            $this->template->setDisplayData('list_hasremap',0);
        }
        $this->addAlphabet($link_data);

    }


    protected function addAlphabet($link_data) {
        if (! ($alpha_node = $this->template->appendFileById( "auto_lists_type_header_alphabet_clear.html", "span", "lists_alphabet" )) instanceof DOMNode) {
            return;
        }
        if ($this->module == 'I2CE') {
            $url = $this->page;
        } else {
            $url = $this->module . '/' . $this->page;
        }
	$url .= '/view';

	$this->setDisplayDataImmediate('alpha_link',$url,$alpha_node);

        if (array_key_exists('letter',$link_data)) {
            unset($link_data['letter']);
        }
        $this->template->setDisplayDataImmediate( "alpha_link", http_build_query($link_data) , $alpha_node);
        $atoz = range( 'A', 'Z' );
        array_unshift( $atoz, '#' );
        foreach( $atoz as $letter ) {
            if ( $letter == '#' ) {
                $link_data['letter'] = 'num';
            } else {
                $link_data['letter'] = $letter;
            }
            if ( $letter == $this->get('letter') || ($letter == '#' && $this->get('letter') == 'num') ) {
                if  (! ($alpha_node = $this->template->appendFileById( "auto_lists_type_header_alphabet_selected.html", "span", "lists_alphabet" )) instanceof DOMNode) {
                    continue;
                }
            } else {
                if (! ($alpha_node = $this->template->appendFileById( "auto_lists_type_header_alphabet.html", "span", "lists_alphabet" )) instanceof DOMNode) {
                    continue;
                }
		$this->setDisplayDataImmediate('alpha_link',$url,$alpha_node);
                $this->template->setDisplayDataImmediate( "alpha_link", http_build_query($link_data) , $alpha_node);
            }
            $this->template->setDisplayDataImmediate( "alpha_name", $letter, $alpha_node );
        }
    }



    protected function showHidden() {
        $show_hidden = 0;
        if ($this->request_exists('show_i2ce_hidden')) {
            $show_hidden = (int) $this->request('show_i2ce_hidden');
            if ($show_hidden < 0 || $show_hidden > 2) {
                $show_hidden = 0;
            }
        }
        return $show_hidden;
    }



    protected function action_select_field($listConfig) {        
        if (! ($obj = $this->getPrimary()) instanceof I2CE_List) {
            I2CE::raiseError("No primary list");
            return false;
        }
        if (! ($select_field = $this->getSelectField($listConfig)) instanceof I2CE_FormField_MAPPED) {
            I2CE::raiseError("select field is not map");
            return false;
        }

        if (! ($node =$this->template->addFile( "auto_lists_type_mapped.html" )) instanceof DOMNode) {
            return false;
        }
        if ($this->module == 'I2CE') {
            $url = $this->page;
        } else {
            $url = $this->module . '/' . $this->page;
        }
        $this->template->setDisplayDataImmediate( 'auto_list_link', $url, $node );
        $this->template->setDisplayDataImmediate( 'add_new_link', $url . '/edit', $node );
        $this->template->setDisplayDataImmediate( 'field', $select_field->getName(), $node );
        $this->template->setDisplayDataImmediate( 'field_name', $select_field->getHeader(), $node );
        $this->template->setDisplayDataImmediate( "type_name", $obj->getDisplayName() );
        if( ($formNode = $this->template->getElementByTagName('form',0,$node))instanceof DOMElement){
            $formNode->setAttribute('action',$url .'/view' );
        }

        $add_node = $this->template->getElementById('mapped');
        if (!$add_node instanceof DOMNode) {
            I2CE::raiseError("Don't know where to add mapped field options");
        } else { 
            $select_template = "lists_type_mapped_" . $this->getPrimaryFormName() . "_" . $select_field->getName() . ".html";
            $select_template = $this->template->findTemplate( $select_template, false );
            if ( !$select_template ) {
                $select_template = "lists_type_mapped_default.html";
            }
            $node = $this->template->appendFileById( $select_template, "span", "mapped" );
            $form_node = $this->template->getElementById('select_form_field_node');
            if ($form_node instanceof DOMElement) {
                $form_node->setAttribute('show_i2ce_hidden',$this->showHidden()); 
                $select_field->processDOMEditable($node, $this->template,$form_node);                        
                $add_node->appendChild($node);
            } else {
                I2CE::raiseError("could not find 'select_form_field_node' in " . $select_template);
            }
        }
        $this->template->appendFileById( "lists_type_header.html", "th", "lists_header" );
        $keys = explode('[',$htmlname = $select_field->getHTMLName());
        foreach ($keys as &$key) {
            if (strlen($key) > 0 && substr($key,-1) == ']') {
                $key = substr($key,0,-1);
            }	    
        }
        unset($key);	
        $link_data = array($htmlname=>$this->request($keys));
        $this->setTemplateVars($listConfig,$link_data);
        if (!$select_field->isSetValue() && !$this->request_exists($keys)) {
            return true; //don't show any options until a value has been selected
        }
        //select_field may be not set.  that's ok.  listOptions does error checking
        $list = I2CE_List::listOptions($this->getPrimaryFormName(),$this->showHidden(),$select_field);
        if ( $this->request_exists('letter') ) {
            $list = array_filter( $list, "self::filter_by_" . $this->request('letter') );
        }
        $page_size = 50;
        if (array_key_exists('page_length',$this->args)) {
            $page_size = $this->args['page_length'];
        }

        if ( count($list) > 0 ) {
            if (!  ($list = $this->paginateList($list,array('form_name'=>$obj->getName()),'select_list',$page_size))) {
                return false;
            }
        }
        return $this->actionDisplayList_row($list,$listConfig);
    }

    public function __call( $func, $args ) {
        if ( substr( $func, 0, 10 ) == "filter_by_" ) {
            $letter = substr($func, 10);
            if ( $letter == "num" ) {
                if ( is_numeric( $args[0]['display'][0] ) ) {
                    return true;
                }   else {
                    return false;
                }
            }
            if ( strtolower( $args[0]['display'][0] ) == strtolower($letter) ) {
                return true;
            }  else {
                return false;
            }
        } else {
            return parent::__call( $func, $args );
        }
    }



    protected function action_menu() {
        if (! ($catsNode = $this->template->getElementByID('list_categories'))instanceof DOMNode) {
            I2CE::raiseError("Cannot find list_categories");
            return false;
        }

        $style ='tab';	
        I2CE::getConfig()->setIfIsSet($style, $this->getMDRoot() . '/options/menu/style');
        if (!I2CE_ModuleFactory::instance()->isEnabled('tabbed-pages') || !$style =='tab') {
            $style = 'column';
        }
        if ($this->request_exists('style') && (is_scalar($this->request('style'))) && $this->request('style')) {
            $style = $this->request('style');
        }
        $method = 'action_menu_'  . $style;
        if ($this->_hasMethod($method)) {
            return $this->$method($catsNode);
        }
        return $this->action_menu_column($catsNode); //default display
    }

    protected function action_menu_column($catsNode) {
        $cols = 2;
        I2CE::getConfig()->setIfIsSet($cols,$this->getMDRoot() ."/options/menu/cols");
        $cols = (int) $cols;
        if ($this->request_exists('cols')) {
            $cols =  (int) $this->request('cols');
        }
        if ($cols < 1 ) {
            $cols = 1;
        } 
        $catNodeT = $this->template->createElement('div',array('style'=>'display:table;width:100%'));
        $catsNode->appendChild($catNodeT);
        $catNodeR = $this->template->createElement('div',array('style'=>'display:table-row;width:100%'));
        $catNodeT->appendChild($catNodeR);
        $catNodeCells = array();
        $width = 99.0/$cols;
        for ($i=0; $i < $cols; $i++) {	    
            $catsNodeCells[$i] = $this->template->createElement('div',array('style'=>'display:table-cell;width:' . $width . '%'));
            $catNodeR->appendChild($catsNodeCells[$i]);
        }

        $categories = $this->getCategorizedLists();
        $tot  = 0;
        foreach($categories as $cat_details) {
            if (!is_array($cat_details) 
                    || !array_key_exists('subcategory',$cat_details)	
                    || !is_array($cat_details['subcategory'])) {
                continue;
            }
            $tot +=count($cat_details['subcategory']);
        }
        $count =0;
        foreach ($categories as $cat=>$cat_details) {
            if (!is_array($cat_details) 
                    || !array_key_exists('subcategory',$cat_details)	
                    || !is_array($subcats = $cat_details['subcategory'])) {
                continue;
            }
            $catNode = $this->template->createElement('h2');
            $cat_name = $cat;
            if (array_key_exists('text',$cat_details)
                    && is_scalar($cat_details['text'])
               ) {
                $cat_name = $cat_details['text'];
            }
            $catNode->appendChild($this->template->createTextNode( $cat_name));
            $catListNode = $this->template->createElement('div');
            foreach ($subcats as $subcat => $lists) {
                $scatListNode = $this->template->createElement('ul');
                $is_available = false;
                foreach ($lists as $list=>$listConfig) {
                    if (! ($linkNode = $this->getListLinkNode($listConfig)) instanceof DOMNode) {
                        continue;
                    }
                    $is_available = true;
                    $liNode = $this->template->createElement('li');
                    $liNode->appendChild($linkNode);
                    $scatListNode->appendChild($liNode);		    
                }
                if ( $is_available ) {
                    if ($subcat != '0') {
                        $scatNode = $this->template->createElement('h3');
                        $scatNode->appendChild($this->template->createTextNode( $subcat));
                        $catListNode->appendChild($scatNode);
                    }
                    $catListNode->appendChild($scatListNode);		
                }
            }
            $which = (int) ($count/ ($tot/$cols));
            $count += count($subcats);
            $catsNodeCells[$which]->appendChild($catNode);
            $catsNodeCells[$which]->appendChild($catListNode);
        }
        return true;
    }

    protected function action_menu_tab($catsNode) {

        //<div id='tab_panel'>
        //<ul class='tabs' id='tabs_link'/>
        //<div class='tabs_content' id='tabs_content'/>
        //</div>
        $this->template->addHeaderLink('mootools-core.js');
        $this->template->addHeaderLink('mootools-more.js');
        $this->template->addHeaderLink('I2CE_AjaxTabPanel.js');
        $this->template->addHeaderLink('tabs.css');

        $catsNode->appendChild($tabNode = $this->template->createElement('div',array('id'=>'tab_panel')));
        $tabNode->appendChild( $tabsNode = $this->template->createElement('ul',array('class'=>'tabs','id'=>'tabs_link')));
        $tabNode->appendChild( $tabsContentNode = $this->template->createElement('div',array('class'=>'tabs_content','id'=>'tabs_content')));
        $categories = $this->getCategorizedLists();
        
        $selected = false;
        if ($this->request_exists('selected_tab') 
                && is_scalar($this->request('selected_tab'))
                && array_key_exists($this->request('selected_tab'),$categories)) {
            $selected = $this->request('selected_tab');
        } elseif ( array_key_exists( 'HTTP_REFERER', $_SERVER ) ) {
            $referer = parse_url( $_SERVER['HTTP_REFERER'] );
            $ref_qry = array();
            if ( array_key_exists( 'query', $referer ) ) {
                parse_str( $referer['query'], $ref_qry );
                $selected_form = null;
                if ( array_key_exists( 'type', $ref_qry ) ) {
                    $selected_form = $ref_qry['type'];
                } elseif ( array_key_exists( 'form_name', $ref_qry ) ) {
                    $selected_form = $ref_qry['form_name'];
                }
                if ( $selected_form && ($listConfig = $this->getListConfig( $selected_form )) && array_key_exists( 'category', $listConfig ) ) {
                    $selected = $listConfig['category'];
                }
            }
        }
        if ( !$selected ) {
            $selected = key($categories);
        }
        $selected = preg_replace('/[^a-zA-Z0-9_\,]/s','',$selected);
        $js = 'document.addEvent("domready", function() {
                var tab = new I2CE_AjaxTabPanel("tab_panel");
                if (tab) { tab.showTab("' . addslashes($selected) . '");}});';
        $this->template->addHeaderText($js,'script','create_tabs');
        foreach ($categories as $cat=>$cat_details) {
            $tab_id = preg_replace('/[^a-zA-Z0-9_\,]/s','',$cat);
            if (!is_array($cat_details) 
                    || !array_key_exists('subcategory',$cat_details)	
                    || !is_array($subcats = $cat_details['subcategory'])) {
                continue;
            }
            $cat_name = $cat;
            if (array_key_exists('text',$cat_details)
                    && is_scalar($cat_details['text'])
               ) {
                $cat_name = $cat_details['text'];
            }
            $attrs  = array('class'=>'tab_link','id'=>'tab_link_' . $tab_id);
            $t_attrs = array('id'=>'tab_content_' . $tab_id,'class'=>'tab_content');
            if ($cat  != $selected) {
                $t_attrs['style']='display:none';
            }
            $tabsNode->appendChild($liNode = $this->template->createElement('li',$attrs,$cat_name));
            $catsNodeCell = $this->template->createElement('div',$t_attrs);
            $tabsContentNode->appendChild($catsNodeCell);
            $catsNodeCell->appendChild($catNode=  $this->template->createElement('h2'));
            $catNode->appendChild($this->template->createTextNode( $cat_name));
            $catListNode = $this->template->createElement('div');
            $catsNodeCell->appendChild($catListNode);
            foreach ($subcats as $subcat => $lists) {
                $scatListNode = $this->template->createElement('ul');
                $is_available = false;
                foreach ($lists  as $list=>$listConfig) {
                    if (! ($linkNode = $this->getListLinkNode($listConfig)) instanceof DOMNode) {
                        continue;
                    }
                    $is_available = true;
                    $liNode = $this->template->createElement('li');
                    $liNode->appendChild($linkNode);
                    $scatListNode->appendChild($liNode);		    
                }
                if ( $is_available ) {
                    if ($subcat != '0') {
                        $scatNode = $this->template->createElement('h3');
                        $scatNode->appendChild($this->template->createTextNode( $subcat));
                        $catListNode->appendChild($scatNode);
                    }
                    $catListNode->appendChild($scatListNode);		
                }
            }
        }		
        return true;
    }


    protected function getCategorizedLists() {
        $categories = array();
        $skip_forms = array();
        $viewed_forms = array();
        $auto_lists = I2CE::getConfig()->getKeys($this->getMDRoot() . '/forms');
        $configs = array();
        foreach ($auto_lists as $type) {
            if (! ($listConfig = $this->getListConfig($type)) ) {
                continue;
            }
            $form = false;
            if (!array_key_exists('form',$listConfig)  || ! ($form  = $listConfig['form'])) {
                continue;
            }
            $viewed_forms[] = $form;
            $skip = false;
            if (array_key_exists('skip',$listConfig)  &&  $listConfig['skip']) {
                $skip_forms[] = $form;
                continue;
            }
            $cat = 'other_lists';	    
            if (array_key_exists('category',$listConfig) && is_string($listConfig['category']) && strlen($listConfig['category'])) {
                $cat = $listConfig['category'];
            }
            $cats[] = $cat;
            if (!array_key_exists($cat,$configs)) {
                $configs[$cat] = array();
            }
            $configs[$cat][$type] = $listConfig;
        }
        $this->loadAllLists();
        if ($this->user->getRole() == 'admin') {
            $other_forms = array_diff(array_diff($this->all_lists,$viewed_forms),$skip_forms);
            if (count($other_forms) > 0) {
                $cats[] = 'other_lists'; //semi-reserved list
                if (!array_key_exists('other_lists',$configs)) {
                    $configs['other_lists'] = array();
                }
                $other_forms_data = array();
                foreach( $other_forms as $form) {
                    if (  ! ($listConfig = $this->getListConfig(false,$form))) {
                        continue;
                    }
                    $other_forms_data[] =  $listConfig;
                }
                if (!array_key_exists('other_lists',$configs)) {
                    $configs['other_lists'] = array();
                }

                //usort($other_forms_data,array($this,'sortByTextKey'));
                $configs['other_lists'] = array_merge($configs['other_lists'],$other_forms_data);
            }
        }
        $cats = array_unique($cats);
        $cat_names = I2CE::getConfig()->getAsArray($this->getMDRoot() . '/category' );
        if (!is_array($cat_names)) {
            $cat_names = array();
        }
        if (!array_key_exists('other_lists',$cat_names)) {
            $cat_name = 'Other Lists';
            I2CE::getConfig()->setIfIsSet($cat_name,"/modules/Lists/messages/other_lists");
            $cat_names['other_lists'] = $cat_name;
        }

        foreach ($cats as $cat) {
            $s_categories = array();
            $subcats = array();
            $sconfigs= array();
            foreach ($configs[$cat] as $list=>$listConfig) {
                $subcat = '0'; //reserved subcategory as default
                if (array_key_exists('subcategory',$listConfig) && is_scalar($listConfig['subcategory']) && $listConfig['subcategory']) {
                    $subcat= $listConfig['subcategory'];		    
                }
                $subcats[] = $subcat;
                if (!array_key_exists($subcat,$sconfigs)) {
                    $sconfigs[$cat] = array();
                }
                $sconfigs[$subcat][$list] = $listConfig;
            }
            $subcats  = array_unique($subcats);
            sort($subcats);
            foreach ($subcats as $subcat) {
                if (count($sconfigs[$subcat]) == 0) {
                    continue;
                }
                usort($sconfigs[$subcat],array($this,'sortByTextKey'));
                $s_categories[$subcat] = $sconfigs[$subcat];
            }
            if (count($s_categories) == 0) {
                continue;
            }
            if (version_compare(PHP_VERSION, '5.4.0') < 0 ) {
                ksort($s_categories, SORT_STRING );
            } else { 
                ksort($s_categories, SORT_NATURAL | SORT_FLAG_CASE);
            }
            $cat_name = $cat;
            if (array_key_exists($cat,$cat_names) 
                    && is_scalar($cat_names[$cat])) {
                $cat_name = $cat_names[$cat];
            }
            $categories[$cat] = array('text'=>$cat_name,'subcategory'=>$s_categories);
        }
        if ($this->request_exists('category') && is_scalar($cat = $this->request('category')) && $cat) {
            $t_categories = array();
            foreach (explode(",",$cat) as $cat) {
                if (!array_key_exists($cat,$categories)) {
                    continue;
                }
                $t_categories[$cat]=$categories[$cat];
            }
            $categories =$t_categories;
        }
        uasort($categories,array($this,'sortByTextKey'));

        return $categories;
    }


    protected function sortByTextKey($a,$b) {
        return strcasecmp($a['text'],$b['text']);
    }

    protected $all_lists = array();

    protected function loadAllLists() {
        $this->all_lists = array();

        $ff = I2CE_FormFactory::instance();
        foreach ($ff->getForms() as $form) {
            $formClass = $ff->getClassName($form);
            if (!I2CE_List::isList($formClass)) {
                continue;
            }
            $this->all_lists[] = $form;
        }
    }

    protected $remap_ids = array();
    protected function hasRemapData($listConfig) {

        $form = false;
        if (array_key_exists('form',$listConfig) && is_scalar($listConfig['form'])) {
            $form = $listConfig['form'];
        } 
        $ff = I2CE_FormFactory::instance();
        $formClass = $ff->getClassName($form);
        if (!$formClass || !I2CE_List::isList($formClass)) {
            return false;
        }
        $where = array(
                'operator'=>'FIELD_LIMIT',
                'field'=>'remap',
                'style'=>'not_null',
                'data'=>array()
                );
        $this->remap_ids = I2CE_FormStorage::search($form,false,$where);
        return count( $this->remap_ids) > 0;
    }

    protected function getRemapAllLink($listConfig) {
        if ($this->user->getRole()  != 'admin') {
            return false;
        }
        $form = false;
        if (array_key_exists('form',$listConfig) && is_scalar($listConfig['form'])) {
            $form = $listConfig['form'];
        } 
        $ff = I2CE_FormFactory::instance();
        $formClass = $ff->getClassName($form);
        if (!$formClass || !I2CE_List::isList($formClass)) {
            return false;
        }
        return 'index.php/' . $this->page . '/remap?id='  . $form .'|*';
    }


    protected function getListLinkNode($listConfig) {
        $task = false;
        if (array_key_exists('task',$listConfig) && is_scalar($listConfig['task'])) {
            $task = $listConfig['task'];
        } 
        if ($task  && I2CE_PermissionParser::taskExists($task) && !$this->hasPermission("task($task)")) {
            return false;
        }
        $form = false;
        if (array_key_exists('form',$listConfig) && is_scalar($listConfig['form'])) {
            $form = $listConfig['form'];
        } 
        if (!$form || !in_array($form,$this->all_lists)) {
            return false;
        }
        $text ='';
        if (array_key_exists('text',$listConfig) && is_scalar($listConfig['text'])) {
            $text = $listConfig['text'];
        }  
        if (!$text) {
            $text = I2CE_FormStorage::instance()->getDisplayName($form);
        }
        if (! $listConfig['type']) {
            $attrs = array('href'=>'index.php/' . $this->page . '/view?form_name=' . $form);
        } else {
            $attrs = array('href'=>'index.php/' . $this->page . '/view?type=' . $listConfig['type']);
        }

        return  $this->template->createElement('a',$attrs,$text);

    }




    protected function actionDisplayList_row($list,$listConfig) {
        $odd = false;
        if ($listConfig['type']) {
            $link = $this->page . '/view?type=' . $listConfig['type'];
        } else {
            $link = $this->page . '/view?form_name=' . $listConfig['form'];
        }
        $imported = $this->template->loadFile( "lists_type_row.html", "tr", "lists_body" );
        if (!$imported instanceof DOMNode) {
            I2CE::raiseError("Could not find lists_type_row.html");
            return false;
        }        
        $remaped = $this->template->loadFile( "lists_type_row_remapped.html", "tr", "lists_body" );
        if (!$remaped instanceof DOMNode) {
            $remaped = $imported;
        }        

        $append = $this->template->getElementById('lists_body');
        if (!$append instanceof DOMNode) {
            I2CE::raiseError("Don't know where to append list rows");
            return false;
        }

        foreach( $list as  $data) {
            $id = substr($data['value'],strlen($listConfig['form']) + 1);
            if (in_array($id,$this->remap_ids)
                    && ( $remap = I2CE_FormStorage::lookupField($listConfig['form'],$id,'remap',''))
               ) {
                $imported_row = $remaped->cloneNode(true);            	
                if ($listConfig['type']) {
                    $url = 'index.php/' . $this->page . '/view?type=' . $listConfig['type'];
                } else {
                    $url = 'index.php/' . $this->page . '/view?form_name=' . $listConfig['type'];
                }
                $url .= '&id=' . $listConfig['form'] . '|' . $id;
                $this->template->setDisplayDataImmediate('remapped_link',$url,$imported_row);
                list($rform,$rid) = array_pad(explode('|',$remap,2),2,'');
                $this->template->setDisplayDataImmediate('remapped_value',I2CE_List::lookup($rid,$rform),$imported_row);
            } else {
                $imported_row = $imported->cloneNode(true);            
            }
            $this->template->appendNode($imported_row,$append);
            if ( $odd ) {
                $this->template->setNodeAttribute( "class", "even", $imported_row );
            }
            $odd = !$odd;
            $this->template->setDisplayDataImmediate( "lists_row_link", $link .'&id=' . $data['value'], $imported_row);
            $this->template->setDisplayDataImmediate( "lists_row_name", $data['display'], $imported_row );
        }
        return true;        
    }




    /**
     * Save the objects to the database.
     * 
     * Save the default object being edited and return to the view page.
     * @global array
     */
    protected function save() {        
	if ($this->getAction() == 'edit')  {
	    if ($this->primaryObject instanceof I2CE_List 
                && ($hideField = $this->primaryObject->getField('i2ce_hidden')) instanceof I2CE_FormField_YESNO 
                && ($remapField = $this->primaryObject->getField('remap')) instanceof I2CE_FormField_REMAP
                && $remapField->isSetValue()) {
		$hideField->setFromDB(1);
	    }
	    return parent::save();
	} else {
	    return false;
	}
    }

    protected function  getReturnLink($append_id) {
        if ($this->module == 'I2CE') {
            $url = $this->page;
        } else {
            $url = $this->module . '/' . $this->page;
        }	
        $listConfig = $this->getListConfig();
        if ($listConfig['type']) {
            $link= $url. '/view?type=' . $listConfig['type'];
        } else {
            $link = $url . '/view?form_name=' . $listConfig['form'];
        }
	if ($append_id) {
	    $link .=   "&id=" . $this->getPrimary()->getNameID();
	}
	return  $link ;
    }




    protected function  getViewLink() {
        if ($this->module == 'I2CE') {
            $url = $this->page;
        } else {
            $url = $this->module . '/' . $this->page;
        }	
        $listConfig = $this->getListConfig();
        if ($listConfig['type']) {
            $link= $url . '/view?type=' . $listConfig['type'];
        } else {
            $link = $url . '/view?form_name=' . $listConfig['form'];
        }
        return  $link .  "&id=" . $this->getPrimary()->getNameID();
    }



    

    //*REMAPPING ACTIONS*//
    protected function initPage() {
	if ($this->getAction() == 'remap') {
	    return true;
	} else {
	    return parent::initPage();
	}
    }

    protected $ff;
    protected function _display($supress_output = false) {
	if($this->getAction() !=  'remap') {
	    return parent::_display($supress_output);
	}
	$this->template->addHeaderLink('mootools-core.js');
	$this->template->addHeaderLink('mootools-more.js');
	parent::_display($supress_output);
        if ( ($errors = I2CE_Dumper::cleanlyEndOutputBuffers())) {
            I2CE::raiseError("Errors:\n" . $errors);
        }
	$this->ff = I2CE_FormFactory::instance();
	if (!$this->request_exists('id') 
	    || !  $formid = $this->request('id') 
	    ) {
	    $this->pushError("Bad list id $id"); //needs to be localized
	    return false;
	}
        $success = true;
        list($form,$id) =array_pad(explode("|",$formid,2),2,'');
        if ($id == '*') {
            $where = array(
                'operator'=>'FIELD_LIMIT',
                'field'=>'remap',
                'style'=>'not_null',
                'data'=>array()
                );
            $ids = I2CE_FormStorage::search($form,false,$where);
            I2CE::raiseError("Form $form has remapping data for " . implode(" ", $ids));
            if (count($ids) > 0) {
                foreach (I2CE_List::getFieldsMappingToList($form) as $rform=>$fields) {
                    foreach ($fields as $fieldObj) {
                        $field = $fieldObj->getName();
                        foreach ($ids as $id) {
                            I2CE::raiseError("Checking for remaps on $rform+$field");
                            $success &= $this->doRemap($rform,$field,$form . '|'. $id);
                        }
                    }
                }
            }
            $url = "index.php/auto_list?form_name=" . $form;
        } else {
            $form = '';
            if (!$this->request_exists('form_name') || !  ($form = $this->request('form_name')) || !in_array($form,$this->ff->getForms()) ) {
                $this->pushError("Form $form not found");
                return false;
            }
            $field ='';
            if (!$this->request_exists('field') || !  $field = $this->request('field')) {
                $this->pushError("Bad Field $field");
                return false;
            }
            $success = $this->doRemap($form,$field,$formid);
            $url = "index.php/auto_list?id=$formid&form_name=" . $form;

        }
	if ($success) {
	    $this->pushContent( "Data was succesully remapped.  Continue on to database lists <a href='$url'>site</a>?");
	} else {
	    $this->pushContent( "Data was <b>not</b> succesully remapped.  Continue on to database lists <a href='$url'>site</a>?");
	}
	return true;
    }

    protected function doRemap($form,$field,$id) {
	$obj = $this->ff->createContainer($id);
	if  (! $obj instanceof I2CE_List) {
	    $this->pushError("ID $id does not refer to a list"); //needs to be localized
	    return false;
	}
	$obj->populate();
	$newform ='0';
	$newid ='0';
	$rField = $obj->getField('remap');
	if ( (!$rField instanceof I2CE_FormField_REMAP) 
	     || !($newform = $rField->getMappedForm()) 
	     || ! ($newid = $rField->getMappedID())) {
	    $this->pushError("No remapping data has been set for $id [$newform|$newid]" .get_class($rField));
	    return false;
	}
	$where = array(
	    'operator'=>'FIELD_LIMIT',
	    'field'=>$field,
	    'style'=>'equals',
	    'data'=>array('value'=>$id)
	    );
	if (( ($count = count($remapIDs =I2CE_FormStorage::search($form,false,$where) ) ))< 1) {
	    $this->pushMessage("No fields found to remap");
	    return true;
	}
	$this->pushCount($count);
	$exec = array('max_execution_time'=>20*60, 'memory_limit'=> (256 * 1048576));	    
	$user = new I2CE_User();
	$success= true;
	foreach  ($remapIDs as $i=>$remapID) {
	    I2CE::longExecution($exec);
	    if ( ! ($remapObj = $this->ff->createContainer($form .'|'.$remapID)) instanceof I2CE_Form) {
		$this->pushMessage("Could not create $form|$remapID",$i+1);
		$success= false;	
		continue;
	    }
	    $remapObj->populate();
	    if (! ($fieldObj= $remapObj->getField($field)) instanceof I2CE_FormField_MAP) {
		$this->pushMessage("Field $field is not a map field",$i+1);
		$success= false;
		$remapObj->cleanup();
		continue;
	    }
	    $fieldObj->setFromDB($newform .'|' . $newid);
	    $this->pushMessage("Remapping $field in  $form|$remapID to be $newform|$newid",$i+1);
	    if (! ($remapObj->save($user))) {
		$success= false;
		$this->pushMessage("Could not save $field in $form|$remapID to be $newform|$newid",$i+1);
	    } else {
		$this->pushMessage("Remapped $field in $form|$remapID to be $newform|$newid",$i+1);
	    }
	    $remapObj->cleanup();

	}
        return $success;
    }
    protected function pushContent($html) {
	I2CE::raiseError($html);
	$js_message = '<script type="text/javascript">addContent("<div>' . $html .'</div>");</script>';
	echo $js_message;
	flush();

    }

    protected function pushError($message,$i=0) {	 
	I2CE::raiseError($message);
	$this->pushMessage($message,$i);
    }
    
    protected function pushMessage($message,$i=0) {	
        I2CE::raiseMessage($message);
	$js_message = '<script type="text/javascript">addMessage("' .  str_replace("\n",'<br/>',addcslashes($message , '"\\')) . '",' . $i .  ");</script>\n";
	echo $js_message;
	flush();	
    }

    protected function pushCount($count) {
	I2CE::raiseError("Doing $count");
	$js_message = '<script type="text/javascript">setCount(' .$count. ");</script>\n";
	echo $js_message;
	flush();	
    }



    public function paginateList($list,$qry_fields = array(), $jumper_id = 'select_list',$page_size = 50) {
        if ($this->module == 'I2CE') {
            $url = $this->page;
        } else {
            $url = $this->module . '/' . $this->page;
        }
	$url .='/view';
        $page_size = (int) $page_size;
        if ($page_size <=  0) {
            $page_size = 50;
        }
        $total_pages = max(1,ceil (count($list)/$page_size));
        $pageVar = 'page';
        if ($jumper_id != 'select_list') {
            $pageVar = $jumper_id . '_page';
        }
        if ($total_pages > 1) {
            $page_no =  (int) $this->request($pageVar);
            $page_no = min(max(1,$page_no),$total_pages);
            $offset = (($page_no - 1)*$page_size );
            $list = array_slice($list, $offset, $page_size,true);
	    I2CE_Util::merge_recursive($qry_fields,$this->request());
	    foreach (array($pageVar) as $key) {
                if (array_key_exists($key,$qry_fields)) {
                    unset($qry_fields[$key]);
                }
            }        
            $this->makeJumper($jumper_id,$page_no,$total_pages,$url,$qry_fields,$pageVar);                
        }
        return $list;
    }


}


