<?php
/**
 * @copyright © 2009 Intrahealth International, Inc.
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
 * @package I2CE
 * @subpackage Core
 * @author Carl Leitner <litlfred@ibiblio.org>
 * @version 2.1
 * @access public
 */

  /**
   * I2CE_Template_Options
   * @package I2CE
   * @todo Better Documentation
   */
class I2CE_Template_Options extends I2CE_Module{

    public static function getHooks() {
        return array(
            'pre_page_prepare_display_I2CE_Template'=> 'processOptions'
            );        
    }


    public static function getMethods() {
        return array(
            'I2CE_Page->addOption'=>'addOption',
            'I2CE_Template->addOption'=>'addOption',
            'I2CE_Page->addOptions'=>'addOptions',
            'I2CE_Template->addOptions'=>'addOptions'
            );
    }


    /**
     * Add option elements to the select node with the given id attribute.
     * @param mixed $selectID a string, the node id, or a DOMNode
     * @param string $id The code that will be the submitted value of the form element if selected.
     * @param string $value The display text that will be seen on the page.
     * @param DOMNode $node
     */
    public function addOption($template,$selectID,$id,$value,$node=null) {
        //$template is either I2CE_Template or I2CE_Page, it don't matter
        if (is_array($value)) {
            $value['value'] = $id;
            //$template->setData($value,$selectID,'OPTION', $selectID);
            $template->setData($value,$node,'OPTION', $selectID);
        } else if (is_scalar($value)) {
            //$template->setData(array('value'=>$id,'text'=>$value),$selectID,'OPTION', $selectID);            
            $template->setData(array('value'=>$id,'text'=>$value),$node,'OPTION', $selectID,false);            
        }
    }

    /**
     * Add options elements to the select node with the given id attribute.
     * @param mixed $selectID a string, the node id, or a DOMNode
     * @param array  $options.  An array where key will be the select id and values will be the select value.
     * @param string $value The display text that will be seen on the page.
     */
    public function addOptions($template,$selectID, $options, $node = null) {
        if (!is_array($options)) {
            return;
        }
        foreach ($options as $id=>$value) {
            $this->addOption($template, $selectID, $id,$value,$node);
        }
    }


    /*
     *Process the options for this page.
     */
    public function processOptions($page) {
        if (!$page instanceof I2CE_Page) {
            I2CE::raiseError("Did not receive page when expected");
            return false;
        }
        $template = $page->getTemplate();
        if (!$template instanceof I2CE_Template) { 
            return;
        }
        $qry = '//select[@id]';
        $results = $template->query($qry);
        for( $i = 0; $i < $results->length; $i++ ) {
            $node = $results->item($i);
            $id = $node->getAttribute('id');
            $data_list = $template->getData('OPTION',$id,$node,true,false);
            $template->removeData('OPTION',$id,false,$node);
            if (empty($data_list)) { 
                continue; 
            } 
            I2CE_DisplayData::processDisplayValue($template,$node,$data_list,true);
        }
    }


}

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
