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
*  I2CE_Module_FormWorm
* @package I2CE
* @subpackage Core
* @author Carl Leitner <litlfred@ibiblio.org>
* @version 2.1
* @access public
*/


class I2CE_Module_FormWorm extends I2CE_Module{

    public static function getMethods() {
        return array('I2CE_Page->addFormWorm'=>'addFormWorm',
                     'I2CE_Template->addFormWorm'=>'addFormWorm'
            );
    }

    public static function getHooks() {
        return array(
            'post_page_prepare_display_I2CE_Template'=> 'writeOutJS'
            );
    }


    protected $formworms;

    public function  __construct() {
        $this->formworms = array();
    }

    /**
     * Adds a form worm
     * @param string $wormname.  The id of the form we wish to add a form worm to
     * @param array $options.  Defaults to empty array.  Passed to the javascript formwork constructor.
     */
    public function addFormWorm($obj,$wormname,$options=array()) {
        $this->formworms[$wormname] = $options;
    }

    public function writeOutJS($page) {
        if (count($this->formworms) == 0) {
            return;
        }
        if (!$page instanceof I2CE_Page) {
            return;
        }
        $template= $page->getTemplate();
        if (!$template instanceof I2CE_Template) {
            return;
        }
        $template->addHeaderLink("mootools-core.js");
        $template->addHeaderLink("mootools-more.js");


//we can remove this if the following is resolved
//http://mootools.lighthouseapp.com/projects/2706/tickets/524-add-contenttype-for-requesthtml

        $template->addHeaderLink("getElementsByClassName-1.0.1.js");
        $template->addHeaderLink("I2CE_ClassValues.js");
        $template->addHeaderLink("I2CE_Validator.js");
        $template->addHeaderLink("I2CE_Window.js");
        $template->addHeaderLink("I2CE_ToggableWindow.js");
        $template->addHeaderLink("I2CE_MultiForm.js");
        $template->addHeaderLink("FormWorm.css");
        $js = "if (window.addEvent) {\n\twindow.addEvent('load',function(e) {\n\t\tformworms = new Array();\n";
        foreach ($this->formworms as $formworm=>$options) {
            if (is_array($options) && count($options) > 0) {
                $options = ',' . json_encode($options);
            } else {
                $options ='';
            }
            $js .="\t\tformworms['$formworm'] = new  I2CE_FormWorm('$formworm' $options);\n";
        }
        $js .=  "\t});\n}\n";
        $template->addHeaderText($js,'script','formworm');
    }

}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:

