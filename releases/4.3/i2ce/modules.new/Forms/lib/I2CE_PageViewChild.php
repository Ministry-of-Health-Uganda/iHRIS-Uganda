<?php
/*
 * © Copyright 2015 IntraHealth International, Inc.
 * 
 * This File is part of iHRIS
 * 
 * iHRIS is free software; you can redistribute it and/or modify
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
 */
/**
 * View a person's record.
 * @package I2CE
 * @access public
 * @author Luke Duncan <lduncan@intrahealth.org> 
 * @copyright Copyright &copy; 2015 IntraHealth International, Inc. 
 * @since v4.2.0
 * @version v4.2.0
 */

/**
 * The page class for displaying the a form and its children record.
 * @package I2CE
 * @access public
 */
class I2CE_PageViewChild extends I2CE_Page { 

    /**
     * @var I2CE_Form The parent object for this page.
     */
    protected $parent;
    /**
     * @var string The parent form for this page.
     */
    protected $parentForm;


    /**
     * @var I2CE_Form The child object for this page.
     */
    protected $child;
    /**
     * @var string The child form for this page.
     */
    protected $childForm;


    /**
     * Get and return the parent form name for this page.
     * @return string
     */
    protected function getParentFormName() {
        if ( !array_key_exists( 'parent_form', $this->args ) 
            || !is_scalar( $this->args['parent_form'] ) 
            || !$this->args['parent_form'] ) {
            I2CE::raiseError("No parent form set");
            return false;
        }
        return $this->args['parent_form'];
    }

    /**
     * Get and return the child form name for this page.
     * @return string
     */
    protected function getChildFormName() {
        if ( !array_key_exists( 'child_form', $this->args ) 
            || !is_scalar( $this->args['child_form'] ) 
            || !$this->args['child_form'] ) {
            I2CE::raiseError("No child form set");
            return false;
        }
        return $this->args['child_form'];
    }


    /**
     * Load the parent object for this page.
     */
    protected function loadParentObject() {
        $factory = I2CE_FormFactory::instance();
        if ( !($class = $factory->getClassName($this->parentForm)) ) {
            I2CE::raiseError("No object class associated to " . $this->$parentForm);
            return false;
        }
        if ( $this->request_exists('id') ) {
            $id = $this->request('id');
            if ( strpos($id,'|') === false ) {
                I2CE::raiseError("Deprecated use of id variable");
                $id = $this->parentForm . '|' . $id;
            }
        } else {
            $id = $this->parentForm . '|0';
        }
        $this->parent = $factory->createContainer( $id);
        if (!$this->parent instanceof $class) {
            return false;
        }
        $this->parent->populate();
        return true;
    }


    /**
     * Initializes any data for the page
     * @returns boolean.  True on sucess. False on failture
     */
    protected function initPage() {
        if ( ( $this->parentForm = $this->getParentFormName() ) === false ) {
            return false;
        }
        if ( ( $this->childForm = $this->getChildFormName() ) === false ) {
            return false;
        }
        if ( !$this->loadParentObject() ) {
            I2CE::raiseError("Failed to load parent object.");
            return false;
        }
        $parentID = $this->parent->getID();
        if ($parentID == '0' || !I2CE_FormFactory::instance()->hasRecord( $this->parentForm, $parentID ) ) {
            $message = "Unable to find that record for %s|%s.";
            I2CE::getConfig()->setIfIsSet( $message, "/modules/forms/page_feedback_messages/invalid_record" );

            $this->userMessage( vsprintf( $message, array( $this->parentForm, $parentID ) ) );
            $this->setRedirect('home');
            return true;
        }
        $this->template->setForm( $this->parent );
        return parent::initPage();
    }
            

    /**
     * Perform the main actions of the page.
     */
    protected function action() {
        $task = $this->getViewChildTask();
        if (I2CE_PermissionParser::taskExists($task) && !$this->hasPermission("task($task)")) {
            $this->userMessage("You do not have permission to view this $childForm",true);
            return false;
        }

        $tag = ( array_key_exists('template_tag', $this->args) && is_scalar($this->args['template_tag']) 
                ? $this->args['template_tag'] : 'div' );
        $append_id = ( array_key_exists('template_id', $this->args) && is_scalar($this->args['template_id']) 
                ? $this->args['template_id'] : 'child_form' );

        $appendNode = $this->template->getElementById( $append_id );
        if ( !$appendNode instanceof DOMNode ) {
            I2CE::raiseError( "No $append_id to add child form " . $this->childForm );
            return false;
        }


        $limit_page = ( $this->request_exists('limit_page') ? $this->request('limit_page') : 1 );
        if ( $limit_page < 1 ) {
            $limit_page = 1;
        }

        $order_by = $this->getOrderBy();

        $found = I2CE_FormStorage::search( $this->childForm, $this->parent->getNameId(), array(), $order_by, array( $limit_page-1, 1 ) );
        if ( !$found && $limit_page > 1 ) {
            $limit_page = 1;
            $found = I2CE_FormStorage::search( $this->childForm, $this->parent->getNameId(), array(), $order_by, array( $limit_page-1, 1 ) );
        }
        if ( !$found ) {
            $this->redirect( "view?id=" . $this->parent->getNameId() );
            return true;
        }
        $total_rows = I2CE_FormStorage::getLastListCount( $this->childForm );
        if ( $total_rows <= 1 ) {
            $this->template->setAttribute( 'style', 'display:none;', 'child_pager_div' );
        } else {
            $query = $this->request();
            unset( $query['limit_page'] );
            $query = I2CE_Page::flattenRequestVars($query);
            $this->makeScalingJumper( 'child', $limit_page, $total_rows, $this->pageRoot(), $query, 'limit_page' );
        }

        $this->child = I2CE_FormFactory::instance()->createContainer( $this->childForm . "|" . $found );
        $this->child->populate();

        I2CE_ModuleFactory::callHooks( 'pre_add_child_form_' . $this->childForm,
                array( 'form' => $this->child, 'page' => $this,
                    'set_on_node' => null, 'append_node' => $appendNode ) );

        $childNode = $this->template->appendFileByNode( $this->getViewChildTemplate(), $tag, $appendNode );
        $this->template->setForm( $this->child, $childNode );

        I2CE_ModuleFactory::callHooks( 'post_add_child_form_' . $this->childForm,
                array( 'form' => $this->child, 'page' => $this,
                    'node' => $childNode,
                    'set_on_node' => null, 'append_node' => $appendNode ) );

        $this->template->setDisplayDataImmediate( "child_form_header", "View " . $this->child->getDisplayName() );

        if ( ( array_key_exists( 'show_edit', $this->args ) ? !$this->args['show_edit'] : true ) ) {
            $this->template->findAndRemoveNodes( "//div[@class='editRecord']" );
        }
        // Do we want this?
        // $this->template->findAndRemoveNodes( "//span[@history='false']" );

        return true;
    }

    /**
     * Return the task needed to view this child form.
     * @return string
     */
    protected function getViewChildTask() {
        return $this->parentForm . '_can_view_child_form_' . $this->childForm;
    }
    
    /**
     * Return the view child template to use to display the child form.
     * @return string
     */
    protected function getViewChildTemplate() {
        return (array_key_exists('template_prefix', $this->args) && is_scalar($this->args['template_prefix']) 
                ? $this->args['template_prefix'] . '_' : '' ) 
            . $this->parentForm . '_view_' . $this->childForm . '.html';
    }

    /**
     * Return the order by to be used for this child form.
     * @return array
     */
    protected function getOrderBy() {
        return ( array_key_exists('order_by', $this->args ) && is_array( $this->args['order_by'] )
                ? $this->args['order_by'] : array() );
    }

}



# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
