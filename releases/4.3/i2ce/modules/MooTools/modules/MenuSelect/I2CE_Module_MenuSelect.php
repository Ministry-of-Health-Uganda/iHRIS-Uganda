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
*  I2CE_Module_MenuSelect
* @package I2CE
* @subpackage Core
* @author Carl Leitner <litlfred@ibiblio.org>
* @version 2.1
* @access public
*/


class I2CE_Module_MenuSelect extends I2CE_Module{
  

    public static function getMethods() {
        return array(
            'I2CE_Page->menuSelect'=>'menuSelect',
            'I2CE_Template->menuSelect'=>'menuSelect',
            'I2CE_Page->addUpdateSelect'=>'addUpdateSelect',
            'I2CE_Template->addUpdateSelect'=>'addUpdateSelect'
            );
    }


    
    /**
     *  Adds a 'menuSelect'.  A menu select is a pair of selects.  The parent and the child.  When
     *  a parent is selected, we update the child select. 
     *  Can be called on either a page or a template.  
     *  $param string $parent_id the $id of the select parent select.
     *  $param array $option_list    the keys are the value of each of the options for the parent select.
     *  the value is itself an array.  this array has keys the value to be used for the child select and values
     *  are the text to be displayed.
     */
    public function menuSelect($template,$parent_id,$option_list) {
        if ($template instanceof I2CE_Page) {
            $template = $template->getTemplate();
        }        
        $template->addHeaderLink( "mootools-core.js");
        $template->addHeaderLink( "menu_select.js");
        $script_vars = 'menu_entries["' . $parent_id . '"] = new Array();' . "\n";
        foreach( $option_list as $link_id => $options ) {
            if (is_int($link_id) ||  (is_string($link_id) && ctype_digit($link_id))) {
            } else {
                $link_id = "'" . $link_id . "'";
            }
            $script_vars .= 'menu_entries["' . $parent_id . '"][' . $link_id . '] = new Array();' . "\n";
            foreach( $options as $id => $val ) {
                if (is_int($id) ||  (is_string($id) && ctype_digit($id))) {
                } else {
                    $id = "'" . $id . "'";
                }
                //$script_vars .= 'menu_entries["' . $parent_id . '"][' . $link_id . '][' . $id . '] = "' . $val . '";' . "\n";
                $script_vars .= 'menu_entries["' . $parent_id . '"][' . $link_id . '].push( new Array( ' . $id . ', "' . $val . '" ) );' . "\n";
            }
        }
        $template->addHeaderText( $script_vars,'javascript',true); //create is as a separate javascript node
    }



    /**
     * Adds a javascripy select_update object for the named select.
     * @param mixed $template. The calling object
     * @param string $select_id.  The id of the select
     * @param array $options. Defaults to the empty array.  Othwewise associative array of ooptions passed to javascript object
     */
    public function addUpdateSelect($template,$select_id, $options = array()) {
        if ($template instanceof I2CE_Page) {
            $template = $template->getTemplate();
        }        
        foreach ($options as $k=>&$v) {
            $k = $k . ':';
            if (is_string($v)) {
                if (preg_match('/^\s*function\s*\(/',$v)) {
                    $v = $k . ' ' .  $v;
                } else {
                    $v =  $k . ' \'' .  addslashes($v) . '\'';
                }
            } else if ($v === true) {
                $v = $k . ' ' .  'true';
            } else if ($v === false) {
                $v =  $k . ' ' .  'false';
            }
        }        
        $options = implode(",", $options);
        if (strlen($options) > 0) {
            $options = ",{" . $options  . "}";
        }
        $template->addHeaderLink('mootools-core.js');
        $template->addHeaderLink('mootools-more.js');
        $template->addHeaderLink('select_update.js');
        $js = "selectUpdateInstances['$select_id'] = new SelectUpdate('$select_id' $options);";
        $template->addHeaderText($js,'javascript','select_update', "\nvar selectUpdateInstances = {};");
    }
    

}

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
