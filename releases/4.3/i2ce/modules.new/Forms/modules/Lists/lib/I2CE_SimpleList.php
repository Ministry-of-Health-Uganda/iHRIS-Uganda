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
 * @package I2CE
 * @author Luke Duncan <lduncan@intrahealth.org>
 * @since v2.0.0
 * @version v2.0.0
 */
/**
 * Object for dealing with simple lists.
 * 
 * @package I2CE
 * @access public
 */
class I2CE_SimpleList extends I2CE_List {

    /**
     * Return the HTML file name for the form template for this form.
     * @param string $type
     * @return string
     */
    public function getHTMLTemplate($type='default') { 
        if (!$type || $type == 'default') {
            if ( $template_file = I2CE::getFileSearch()->search( 'TEMPLATES', "lists_form_" . $this->getName() . ".html")) {
                return $template_file;
            } else {
                return "lists_form_simple.html";
            }
        } else {
            return parent::getHTMLTemplate($type);
        }
    }

    /**
     * Return the HTML file name for the view template for this form.
     * @return string
     */
    public function getViewTemplate($type='default') { 
        if (!$type || $type =='default') {
            if ( $template_file = I2CE::getFileSearch()->search( 'TEMPLATES', "view_list_" . $this->getName() . ".html")) {
                return $template_file;
            } else {
                return "view_list_simple.html"; 
            }
        } else {
            if ( $template_file = I2CE::getFileSearch()->search( 'TEMPLATES', "view_list_" . $this->getName() . "_alternate_$type.html")) {
                return $template_file;
            } else {
                return "view_list_simple_alternate_$type.html"; 
            }
        }
    }

    /**
     * Return the list edit type for this list.
     * 
     * The possible return values are "list," "dual," or "select." Select will display a drop
     * down of all choices and list and dual will list them all in a table.  Dual includes the
     * linked list object for the object.
     * @return string
     */
    public function getListType() { return "list"; }
    
    
}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
