<?php
/**
 * @copyright © 2008, 2009 Intrahealth International, Inc.
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
*  I2CE_Module_StetchPage
* @package I2CE
* @subpackage Core
* @author Carl Leitner <litlfred@ibiblio.org>
* @version 2.1
* @access public
*/


class I2CE_Module_StretchPage extends I2CE_Module{


    public static function getHooks() {
        return array('post_page_prepare_display_I2CE_Template'=>'addStretcher');
    }
    
    public function addStretcher($page) {
        if (!$page instanceof I2CE_Page) {
            return;
        }
        $template= $page->getTemplate();
        if (!$template instanceof I2CE_Template) {
            return;
        }
        $template->addHeaderLink('mootools-core.js');
        $template->addHeaderLink('StretchPage.js');
        $template->addHeaderLink('StretchPage.css');        
        $js="var PageStretch; window.addEvent('domready',  
function() {
       PageStretch = new PageStretcher('StretchPage','siteFooter');
});";
        $template->addHeaderText($js,'script','page_stetch');

    }

}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
