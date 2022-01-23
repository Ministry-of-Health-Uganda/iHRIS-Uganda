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
 * @package iHRIS
 * @subpackage Common
 * @access public
 * @author Luke Duncan <lduncan@intrahealth.org>
 * @copyright Copyright &copy; 2015 IntraHealth International, Inc. 
 * @since v4.2.0
 * @version v4.2.0
 */

/**
 * The page class for displaying the a person's record.
 * @package iHRIS
 * @subpackage Common
 * @access public
 */
class iHRIS_PageViewChild extends I2CE_PageViewChild { 

    /**
     * Load the template files to the template object.
     * 
     */  
    protected function loadHTMLTemplates() {
        parent::loadHTMLTemplates();
        $this->template->appendFileById( "menu_view_link.html", "li", "navBarUL", true );
    }

    /**
     * Return the child template to use.
     * @return string
     */
    protected function getViewChildTemplate() {
        return 'view_' . $this->childForm . '.html';
    }

    /**
     * Return the child form name.
     * @return string
     */
    protected function getChildFormName() {
        if ( $this->request_exists('child') && $this->request('child') ) {
            return $this->request('child');
        } else {
            return false;
        }
    }

    /**
     * Return the order by to use for the set child.
     * @return array
     */
    protected function getOrderBy() {
        return ( array_key_exists( 'order_by', $this->args ) 
            && is_array( $this->args['order_by'] )
            && array_key_exists( $this->childForm, $this->args['order_by'] )
            && is_array( $this->args['order_by'][$this->childForm] )
            ? $this->args['order_by'][$this->childForm] : array() );
    }

}



# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
