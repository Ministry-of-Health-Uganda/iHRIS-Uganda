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
 * @author Luke Duncan <lduncan@intrahealth.org>
 * @since v2.0.0
 * @version v2.0.0
 */

/**
 * The page class for displaying a I2CE_List record.
 * @package I2CE
 * @subpackage Common
 * @access public
 */
class I2CE_PageViewList extends I2CE_Page {

    /**
     * @var string The type of list object being edited.
     */
    protected $type;
    /**
     * @var integer The record id number of the object being edited.
     */
    protected $id;
    /**
     * @var I2CE_List The list object being viewed.
     */
    protected $list;
    
    /**
     * Create a new instance of this page.
     * 
     * This will call the parent constructor and then setup the base
     * template pages for the {@link Template template}.  It also sets up the values
     * for the member variables.
     * @param string $title The title for this page.
     * @param string $defaultHTMLFile The default HTML file for this page.
     * @param mixed $access The role required to access this page.
     * @param array $files The list of template files to load for this page.
     */
    public function __construct( $args,$request_remainder,  $get = null, $post = null) {
        parent::__construct( $args,$request_remainder,$get,$post);
        $this->type = "";
        $this->id = 0;
        if ( $this->request_exists( 'type' ) ) {
            $this->type = $this->request('type');
        }
        if ( $this->request_exists( 'id' ) ) {
            $this->id = $this->request('id');            
        }
        if (strlen($this->id) > 0 && strpos($this->id,'|') === false) {
            I2CE::raiseError("Deprecated use of id: {$this->id}");
            $this->id = $this->type . '|' . $this->id;
        }

    }

    protected function getChildHTMLTemplate($child_form) {
        return "view_list_" . $child_form . ".html";
    }
        
    
    /**
     * Initializes any data for the page.  
     * @returns boolean.  True on sucess. False on failture
     */
    protected function initPage() {                     
        if (!$this->type) {
            return false;
        }
        $factory = I2CE_FormFactory::instance();
        $this->list = $factory->createContainer( $this->id );
        if (!$this->list instanceof I2CE_List) {
            I2CE::raiseError($this->id . " is not a list ");
            return false;
        }
        $permission = "task(can_view_database_list_{$this->type}|can_view_all_database_lists)";
        if (!$this->permissionParser->hasPermission($permission)) {
            if ($this->list instanceof I2CE_List) {
                $list_name = $this->list->getDisplayName();
            } else {
                $list_name = $this->type;
            }
            $this->userMessage("You don't have permission to view the list `" . $list_name . "`",'notice');
            $this->setRedirect("lists");
            return false;
        }
        return parent::initPage();
    }


    /**
     * Load the  template (HTML or XML) files to the template object.
     *  
     * 
     */  
    protected function loadHTMLTemplates() {
        parent::loadHTMLTemplates();
        $this->template->appendFileById( "menu_view.html", "li", "navBarUL", true );
    }

    /**
     * Perform the main actions of the page.
     */
    protected function action() {
        parent::action();

        $this->list->populate();
        $this->template->setDisplayData( "type_name", $this->list->getDisplayName() );
        $this->template->setDisplayData( "list_name", $this->list->name() );      
        if ($this->request_exists('i2ce_template')) {
            $template_alt = $this->request('i2ce_template');
        } else {
            $template_alt = '';
        }
        $viewNode = $this->template->addFile( $this->list->getViewTemplate($template_alt) );
        $this->template->setDisplayData( "list_view_link", "type=" . $this->type );
        $this->template->setDisplayData( "list_edit_link", "type=" 
                 . $this->type . "&id=" . $this->id );
        $this->template->setAttribute( "task", "can_edit_database_list_" 
                . $this->type, "list_edit_link_node" );
        $editNode = $this->template->getElementById( "list_edit_link_node", $viewNode );
        if ( !$editNode instanceof DOMNode ) {
            // No edit node so add it to the can_edit task for the li in this dom.
            // The auto list page is handling this better, but this is just a stopgap measure
            // to remove the main link when the form isn't writable.
            $editSearch = $this->template->query( "./descendant-or-self::div[@class='editRecord']/ul/li[@task='can_edit_database_list_" . $this->type . "']", $viewNode );
            if ( $editSearch->length > 0 ) {
                $editSearch->item(0)->setAttribute( "id", "list_edit_link_node" );
            }
        }
        if (!I2CE_FormStorage::isWritable($this->type)) {
            $this->template->removeNodeById('list_edit_link_node');
        }

        $this->template->setAttribute( "task", "can_view_database_list_" 
                . $this->type, "list_view_link_node" );
        $this->template->setForm( $this->list );
        $this->showChildren();
        $this->showMapped();
        return true;
    }


    protected function showMapped() {
        $factory = I2CE_FormFactory::instance();
        $maps = array();
        if ($this->request_exists('mapped')) {
            $maps = explode(',',$this->request('mapped'));                
        }
        $formConfig = I2CE::getConfig()->traverse("/modules/forms/forms");
        foreach ($maps as $mapped) {
            $template_file = $this->getChildHTMLTemplate($mapped);
            $template_file = $this->template->findTemplate($template_file,false);
            if (!$template_file) {
                continue;
            }
            $appendNode = $this->template->getElementById($mapped);
            if (!$appendNode instanceof DOMElement) {
                continue;
            }
            if (strpos($mapped,'+') !== false) {
                list($mapped,$mapped_field ) = explode('+',$mapped,2);
            } else {
                $mapped_field = $this->list->getName();
            }
            $orders = I2CE_List::getSortFields($mapped);
            $where = array(
                'operator'=>'FIELD_LIMIT',
                'field'=>$mapped_field,
                'style'=>'equals',
                'data'=>array('value'=>$this->list->getNameId()));
            $map_ids = I2CE_FormStorage::search($mapped, false,$where,$orders);
            foreach ($map_ids as $map_id) {
                $mapObj =$factory->createContainer($mapped .'|'.$map_id);
                if (!$mapObj instanceof I2CE_Form) {
                    continue;
                }                                  
                $mapObj->populate();
                $node = $this->template->appendFileByNode( $template_file, "div", $appendNode );
                if (!$node instanceof DOMNode) {
                    I2CE::raiseError("Could not import $template_file div");
                    continue;
                }
                $this->setForm( $mapObj, $node);
            }
        }
        $this->template->addHeaderLink('view.js');
        return true;
    }


    protected function showChildren() {
        $child_forms = $this->list->getChildForms();
        foreach ($child_forms as $child_form) {
            $template_file = $this->getChildHTMLTemplate($child_form);
            $template_file = $this->template->findTemplate($template_file,false);
            if (!$template_file) {
                continue;
            }
            $appendNode = $this->template->getElementById($child_form);
            if (!$appendNode instanceof DOMElement) {
                continue;
            }
            $display = 'default';
            if ($appendNode->hasAttribute('display')) {
                $display = $appendNode->getAttribute('display');
                $appendNode->removeAttribute('display');
            }
            $this->list->populateChild($child_form,null,null,$display);
            $childObjs = $this->list->getChildren($child_form);
            foreach ($childObjs as $childObj) {
                $node = $this->template->appendFileByNode( $template_file, "div", $appendNode );
                if (!$node instanceof DOMNode) {
                    I2CE::raiseError("Could not import $template_file div");
                    continue;
                }
                $this->setForm( $childObj, $node);
            }
        }
    }
}


# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
