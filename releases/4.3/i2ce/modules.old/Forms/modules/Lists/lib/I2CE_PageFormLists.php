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
 * Manage editing of all the list databases used for drop down menus.
 * 
 * This page has the code for the {@link ListsPage} object and then it
 * creates an instance of this page object and calls the {@link ListsPage::display() display}
 * method.
 * @package I2CE
 * @subpackage Common
 * @access public
 * @author Luke Duncan <lduncan@intrahealth.org> / Carl leitner <litlfred@ibiblio.org>
 * @since v2.0.0
 * @version v2.0.0
 */

/**
 * Page object to handle the management of all the list databases used for drop down menus.
 * 
 * This page handles all the editing of the list database tables using the relevant
 * {@link I2CE_Form} interface objects.  The base page lists all the types of data
 * that can be edited.  From there you can add a new entry or edit an existing one.
 * 
 * This object overrides the default {@link save()} and {@link action()} methods since there are many
 * database objects to be edited.
 * @package I2CE
 * @subpackage Common
 * @access public
 */
class I2CE_PageFormLists extends I2CE_PageForm {

    /**
     * @var string The type of list object being edited. e.g. the form name
     */
    protected $type;
    /**
     * @var string The field, if any, for which we wish to select the list for.
     */
    protected $select_field;
    /**
     * @var integer The record id number of the object being edited.
     */
    protected $id;
        
    /**
     * Create a new instance of this page.
     * 
     * This will call the parent constructor and then setup the base
     * template pages for the {@link I2CE_Template template}.  It also sets up the values
     * for the member variables.
     * @param string $title The title for this page.
     * @param string $defaultHTMLFile The default HTML file for this page.
     * @param mixed $access The role required to access this page.
     * @param array $files The list of template files to load for this page.
     */
    public  function __construct( $args,$request_remainder , $get = null, $post = null) {
        parent::__construct( $args,$request_remainder, $get,$post);
        $this->type = "";
        $this->id = '';
        $this->select_field = false;
        $this->select_field_value = '';
        $arr = array( 'id'=>&$this->id, 'type'=>&$this->type, 'field'=>&$this->select_field);
        foreach ( $arr as $key=>&$val) {
            if ($this->request_exists($key)) {
                $val = $this->request($key);
            }
        }
        if (strlen($this->id) > 0 && strpos($this->id,'|') === false) {
            I2CE::raiseError("Deprecated use of id: {$this->id}");
            $this->id = $this->type . '|' . $this->id;
        }
    } 

    /**                                                                                                                                                                                          
     * Get the HTML template for the primary form                                                                                                                                                
     * @returns string                                                                                                                                                                           
     */
    protected function getPrimaryHTMLTemplate() {
        if ($this->request_exists('i2ce_template')) {
            $template_alt = $this->request('i2ce_template');
        } else {
            $template_alt = '';
        }
        if ($this->getPrimary() instanceof I2CE_List) {
            return $this->getPrimary()->getHTMLTemplate($template_alt);
        } else {
            return true;
        }
    }

    /**
     * Get the HTML templat for any child forms
     * @var string $child_form
     * @returns string
     */
    protected function getChildHTMLTemplate($child_form) {
        return "lists_form_{$child_form}.html";
    }


    /**
     * Wether or not we show a hidden list member
     * @returns boolean
     */
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
    
    /**
     * Set the data to be displayed for the outside of the form field elements.
     * 
     * Set up the static data to be displayed in the template.  The default method
     * doesn't do anything, but sub-classes may need to override this method.
     *  */
    protected function setDisplayData() {
        parent::setDisplayData();
        $add_type = array(
            'type'=>$this->type, 
            "nosetdefault" => $this->request("nosetdefault"));
	if (($hiddenSelectNode = $this->template->getElementByName('show_i2ce_hidden',0)) instanceof DOMElement) {
	    $this->template->addHeaderLink("mootools-core.js");
	    $url = "index.php/lists?" . http_build_query($add_type) . '&show_i2ce_hidden=';
	    $js = 'document.location.href = "' .addslashes($url) . '" + this.get("value");'; 
	    $hiddenSelectNode->setAttribute('onChange',$js);
	    $this->template->selectOptionsImmediate('show_i2ce_hidden',$this->showHidden());
	}
        if ( ($primary = $this->getPrimary()) instanceof I2CE_List) {
            $this->template->setDisplayData( "type_name", $primary->getDisplayName() );
            $this->template->setDisplayData( "type", $primary->getName() );
            $this->template->setDisplayData( "id", $primary->getNameId() );
        }
        $this->template->setDisplayData( "link", $this->request("link") );
        $this->template->setDisplayData( "mapped", $this->request("mapped") );
        $this->template->setDisplayData( "nosetdefault", $this->request("nosetdefault") );
        $this->template->setDisplayData( "i2ce_template", $this->request("i2ce_template") );
        if (I2CE_FormStorage::isWritable($this->type)) {
            $this->template->setDisplayData( "list_is_writable", 1);
        } else {
            $this->template->setDisplayData( "list_is_writable", 0);
        }

        $show_link = $add_type;
        if ($this->select_field instanceof I2CE_FormField) {
            $add_type['field']=$this->select_field->getName();
            if ( $this->request("nosetdefault") != "1" && $this->select_field->isSetValue()) {
                $add_type[$this->select_field->getHTMLName()] = $this->select_field->getDBValue();
            }
            if ($this->select_field->isSetValue()) {
                $show_link[$this->select_field->getHTMLName()] = $this->select_field->getDBValue();
            } else {
                $show_link[$this->select_field->getHTMLName()] = '';
            }
            $show_link['field'] = $this->select_field->getName();
            $show_link['id'] = $this->id;
            $this->template->setDisplayData('field', $this->select_field->getName());
            $this->template->setDisplayData('field_name', $this->select_field->getHeader());
        } else {
            $this->template->setDisplayData('field', '');
            $this->template->setDisplayData('field_name','');
        }
        $this->template->setDisplayData('do_not_show_i2ce_hidden_link' , $show_link);
        $this->template->setDisplayData('do_show_i2ce_hidden_link' , $show_link);
        $add_type['show_i2ce_hidden'] = $this->showHidden();
        $this->template->setDisplayData( "add_type", $add_type);
        if ( $this->get_exists('add') || substr( $this->id, -2 ) == "|0" ) {
            $return_page = "lists?";
        } else {
            $return_page = $this->getViewPage( $this->type ) . "?id=" . $this->id;
        }
        foreach( $add_type as $key => $val ) {
            $return_page .= "&$key=$val";
        }
        $this->template->setDisplayData( "add_return", $return_page );

    }


    /**
     * Initializes any data for the page
     * @returns boolean.  True on sucess. False on failture
     */
    protected function initPage() {
        if (!parent::initPage()) { //parent loads needed objects and checks for view access
            return false;
        }
        if ( empty($this->type)) {
            if ($this->isPost()) {
                //type should not be empty
                return false;
            }
            $task = "can_view_database_lists";
            $msg = "You don't have permission to view database  lists";
        }  else {     
            $task = "can_view_database_list_{$this->type}|can_view_all_database_lists ";
            $msg = "You don't have permission to edit the list `{$this->type}`";
        }
        if ( !$this->permissionParser->hasPermission("task($task)") ) {
            $this->userMessage($msg,'notice');
            return false;
        }
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


        if ( !$this->isPost() 
             && ( strlen($this->id ) == 0   || ($this->id == $this->type . '|0') ) 
             && (!$this->get_exists('add') && !$this->type) ) {
            return true;
        }
        //we want to load objects for a GET request when the id was not been explicitly set and it is not an add
        // if ( $this->isPost() 
        //      ||  (!( strlen($this->id ) == 0   || ($this->id == $this->type . '|0') )) 
        //      || !$this->get_exists('add') ) {
        //     //we don't need to load objects
        //     return true;
        // }
        if (!$this->type) {
            return (! $this->isPost());
        }
        $factory = I2CE_FormFactory::instance();
        if ($this->id) {
            $obj = $factory->createContainer( $this->id );
        } else {
            $obj = $factory->createContainer( $this->type );
        }
        if (!$obj instanceof I2CE_List) {
            I2CE::raiseError("Trying to edit {$this->type}|{$this->id} which is not a list:" . get_class($obj));
            return false;
        }
        $this->setObject( $obj );
        if ($this->isConfirm(false) ||  $obj->getId() != '0') {
            $this->template->setForm($obj); //make sure this is the default object for the page
        }
        if ($this->select_field) {            
            $this->select_field = $obj->getField($this->select_field);
            $obj->load($this->request(),false,false);
            if ($this->select_field instanceof I2CE_FormField_MAP) {
                $selectObj = $this->select_field->getMappedFormObject();
                if ($selectObj instanceof I2CE_Form) {
                    //make this object avaialable for record level security
                    $this->template->setForm($selectObj);
                }
            }
        }
        $child_forms = $obj->getChildForms();
        foreach ($child_forms as $i=>$child_form) {
            $template_file = $this->getChildHTMLTemplate($child_form);
            $template_file = $this->template->findTemplate($template_file,false);
            if ($template_file) {
                continue;
            }
            unset($child_forms[$i]);
        }
        if ($obj->getId() == '0') {
            foreach ($child_forms as $child_form) {
                $childObj = $factory->createContainer($child_form);
                if (!$childObj instanceof I2CE_Form) {
                    I2CE::raiseError("Could not create the  form " . $child_form);
                    continue;
                }
                $obj->addChildForm($childObj);
                $this->setObject( $childObj, I2CE_PageForm::EDIT_CHILD );
            }
        } else {
            foreach ($child_forms as $child_form) {
                $obj->populateChild($child_form);
                $childObjs = $obj->getChildren($child_form);
                if (count($childObjs) == 0) {
                    //create the child form as it does not exist
                    $childObj = $factory->createContainer($child_form);
                    if (!$childObj instanceof I2CE_Form) {
                        I2CE::raiseError("Could not create the  form " . $child_form);
                        continue;
                    }
                    $obj->addChildForm($childObj);
                    $this->setObject( $childObj, I2CE_PageForm::EDIT_CHILD );
                } else  {
                    foreach ($childObjs as $childObj) {
                        $this->setObject( $childObj, I2CE_PageForm::EDIT_CHILD , $childObj->getNameId() );
                    }
                }
            }
        }
        return  parent::loadObjects();
    }

        
    /**
     * Load the HTML template files for editing and confirming the list information.
     * 
     * Since this page has special versions, this method is only called when a particular list object
     * is being added or edited.  All other pages are loaded within the {@link action}
     */
    protected function loadHTMLTemplates() {
        if (!$this->type) {
            return true;
        }
        $primary = $this->getPrimary();
        if (!$primary instanceof I2CE_List) {
            if ($primary instanceof I2CE_Form) {
                I2CE::raiseError("Primary is not a list");
                return false;
            } 
            return true;
        }
        $this->template->addFile( "lists_form_base.html" );
        $this->template->addFile( $this->getPrimaryHTMLTemplate(), "tbody" );        
        foreach ($this->objects[I2CE_PageForm::EDIT_CHILD] as $childObj) {
            $template_file = $this->getChildHTMLTemplate($childObj->getName());
            $found_template_file = $this->template->findTemplate($template_file,false);
            if ($found_template_file) {
                $node = $this->template->appendFileById( $found_template_file, "tbody", "list_fields" );
                $node->setAttribute('id',$childObj->getNameId());
            }            
        }
        return true;
    }


        
        
    /**
     * Save the objects to the database.
     * 
     * Save the list object being edited and return to the appropriate admin page.
     */
    protected function save() {
        if (parent::save() === false) {
            return false;
        }
        if ($this->post_exists('redirect')) {
            $redirect = $this->post('redirect');
        } else  if ($this->post_exists('redirect_byid')) {
            $redirect = $this->post('redirect_byid') .      $this->getPrimary()->getNameId();
        } else  {
            $use_id = $this->getPrimary()->getNameId();
            $redirect = $this->getViewPage( $this->type ) . "?type=" . $this->type . "&id=" . $use_id;
        }
        $this->setRedirect($redirect );
        return true;
    }
    
    /**
     * Perform the action of the page with a select object
     */
    protected function actionExtra($select_obj) {        
        $this->template->addFile( "lists_" . $this->type . ".html" );
    }

    /**
     * Perform the action of the page to show all lists.
     */
    protected function actionAllLists() {
        $this->template->addFile( "lists.html" );
        return true;
    }
        

    protected function addAlphabet() {
        if ($this->module == 'I2CE') {
            $url = $this->page;
        } else {
            $url = $this->module . '/' . $this->page;
        }
        $qry_fields = $this->request();
        $qry_fields['type'] = $this->type;
        foreach (array('page','letter') as $key) {
            if (array_key_exists($key,$qry_fields)) {
                unset($qry_fields[$key]);
            }
        }        
        $alpha_node = $this->template->appendFileById( "lists_type_header_alphabet_clear.html", "span", "lists_alphabet" );
        $this->template->setDisplayDataImmediate( "alpha_link", http_build_query($qry_fields) , $alpha_node);
        $atoz = range( 'A', 'Z' );
        array_unshift( $atoz, '#' );
        foreach( $atoz as $letter ) {
            if ( $letter == '#' ) {
                $qry_fields['letter'] = 'num';
            } else {
                $qry_fields['letter'] = $letter;
            }
            if ( $letter == $this->get('letter') || ($letter == '#' && $this->get('letter') == 'num') ) {
                $alpha_node = $this->template->appendFileById( "lists_type_header_alphabet_selected.html", "span", "lists_alphabet" );
            } else {
                $alpha_node = $this->template->appendFileById( "lists_type_header_alphabet.html", "span", "lists_alphabet" );
                $this->template->setDisplayDataImmediate( "alpha_link", http_build_query($qry_fields) , $alpha_node);
            }
            $this->template->setDisplayDataImmediate( "alpha_name", $letter, $alpha_node );
        }
    }


    protected function actionSelectList() {
        $this->template->addFile( "lists_type_list.html" );

        $this->addAlphabet();

        $this->template->appendFileById( "lists_type_header.html", "th", "lists_header" );        
        if ($this->select_field instanceof I2CE_FormField) {
            $select_value = $this->select_field->getDBValue();
        } else {
            $select_value = '';
        }
        $list = I2CE_List::listOptions($this->type,$this->showHidden());
        if ( $this->get_exists('letter') ) {
            $list = array_filter( $list, "self::filter_by_" . $this->get('letter') );
        }
        return $this->paginateList($list);
    }

    public function __call( $func, $args ) {
        if ( substr( $func, 0, 10 ) == "filter_by_" ) {
            $letter = substr($func, 10);

            if ( $letter == "num" ) {
                if ( is_numeric( $args[0]['display'][0] ) ) return true;
                else return false;
            }
            if ( strtolower( $args[0]['display'][0] ) == strtolower($letter) ) return true;
            else return false;
        }

        return parent::__call( $func, $args );
    }

    protected function paginateList($list) {
        if ($this->module == 'I2CE') {
            $url = $this->page;
        } else {
            $url = $this->module . '/' . $this->page;
        }

        $page_size = 50;
        if (array_key_exists('page_length',$this->args)) {
            $page_size = $this->args['page_length'];
        }
        $page_size = (int) $page_size;
        if ($page_size <=  0) {
            $page_size = 50;
        }
        $total_pages = max(1,ceil (count($list)/$page_size));
        if ($total_pages > 1) {
            $page_no =  (int) $this->request('page');
            $page_no = min(max(1,$page_no),$total_pages);
            $offset = (($page_no - 1)*$page_size );
            $list = array_slice($list, $offset, $page_size,true);
            $qry_fields = $this->request();
            $qry_fields['type'] = $this->type;
            foreach (array('page') as $key) {
                if (array_key_exists($key,$qry_fields)) {
                    unset($qry_fields[$key]);
                }
            }        
            $this->makeJumper('select_list',$page_no,$total_pages,$url,$qry_fields);                
        }
        return $this->actionDisplayList_row($list);
    }



    protected function actionSelectMapped() {        
        $this->template->addFile( "lists_type_mapped.html" );
        $this->addAlphabet();
        $add_node = $this->template->getElementById('mapped');
        if (!$add_node instanceof DOMNode) {
            I2CE::raiseError("Don't know where to add mapped field options");
        } else { 
            $select_template = "lists_type_mapped_" . $this->type . "_" . $this->select_field->getName() . ".html";
            $select_template = $this->template->findTemplate( $select_template, false );
            if ( !$select_template ) {
                $select_template = "lists_type_mapped_default.html";
            }
            $node = $this->template->appendFileById( $select_template, "span", "mapped" );
            $form_node = $this->template->getElementById('select_form_field_node');
            if ($form_node instanceof DOMElement) {
                $form_node->setAttribute('show_i2ce_hidden',$this->showHidden()); 
                $this->select_field->processDOMEditable($node, $this->template,$form_node);                        
                $add_node->appendChild($node);
            } else {
                I2CE::raiseError("could not find 'select_form_field_node' in " . $select_template);
            }
        }
        $this->template->appendFileById( "lists_type_header.html", "th", "lists_header" );
        $keys = explode('[',$this->select_field->getHTMLName());
        foreach ($keys as &$key) {
            if (strlen($key) > 0 && substr($key,-1) == ']') {
                $key = substr($key,0,-1);
            }
        }
        unset($key);
        if (!$this->select_field->isSetValue() && !$this->request_exists($keys)) {
            return true; //don't show any options until a value has been selected
        }
        #select_field may be not set.  that's ok.  listOptions does error checking
        $list = I2CE_List::listOptions($this->type,$this->showHidden(),$this->select_field);
        if ( $this->get_exists('letter') ) {
            $list = array_filter( $list, "self::filter_by_" . $this->get('letter') );
        }
        if ($this->request_exists('display') && $this->_hasMethod('actionDisplayList_' . $this->request('display'))) {
            $method= 'actionDisplayList_' . $this->request('display');
            return $this->$method($list);
        } else if (count($list) > 0) {
            return $this->paginateList($list);
        } else {
            return true;
        }
    }

    /**
     * Return the view list page for this type of form.
     * If the page exists for view_$type it returns that, otherwise
     * it uses view_list.
     * @return string
     */
    protected function getViewPage( $type ) {
        if ( I2CE::getConfig()->is_parent( "/I2CE/page/view_" . $type ) ) {
            return "view_" . $type;
        } else {
            return 'view_list';
        }
    }

    protected function getRowBaseLink() {
        $link = "";
        if ($this->request_exists('link')) {
            $link = $this->request('link');
            if (strpos($link,'?') !== false) {
                $link .= '&';
            } else {
                $link .= '?';
            }
        }
        if ( strlen( $link ) < 2 ) {
            $link = $this->getViewPage( $this->type ) . "?";
        }
        if ($this->request_exists('mapped')) {
            $link .='mapped=' . $this->request('mapped') . '&';
        }
        $link .= 'type=' . $this->type .'&id=';
        return $link;
    }


    protected function actionDisplayList_row($list) {
        $odd = false;
        $link = $this->getRowBaseLink();
        $imported = $this->template->loadFile( "lists_type_row.html", "tr", "lists_body" );
        if (!$imported instanceof DOMNode) {
            I2CE::raiseError("Could not find lists_type_row.html");
            return false;
        }        
        $append = $this->template->getElementById('lists_body');
        if (!$append instanceof DOMNode) {
            I2CE::raiseError("Don't know where to append list rows");
            return false;
        }
        foreach( $list as  $data) {
            $imported_row = $imported->cloneNode(true);            
            $this->template->appendNode($imported_row,$append);
            if ( $odd ) {
                $this->template->setNodeAttribute( "class", "even", $imported_row );
            }
            $odd = !$odd;
            $this->template->setDisplayDataImmediate( "lists_row_link", $link . $data['value'], $imported_row);
            $this->template->setDisplayDataImmediate( "lists_row_name", $data['display'], $imported_row );
        }
        return true;        
    }



    /**
     * Perform the actions of the page.
     * 
     * This handles some special actions because there are three versions of this page:
     * - The list page of all objects that can be edited.
     * - The add/update page for each list object.
     * - The edit form for each list object.
     * 
     * Only in the third case is the parent object action method called since that is the default setup
     * for editing objects used in most other {@link PageForm} objects.
     */
    protected function action() {        
        if ( empty($this->type)) {
            return $this->actionAllLists();
        }
        if ( !$this->isPost()  && ( strlen($this->id ) == 0   || ($this->id == $this->type . '|0') ) && !$this->get_exists('add') ) {
            if ($this->select_field instanceof I2CE_FormField_MAPPED) {
                if (!$this->actionSelectMapped()) {
                    return false;
                }
            } else {
                if (!$this->actionSelectList()) {
                    return false;
                }
            }
            $this->setDisplayData();
        } else {       
            parent::action();
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
