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
*  I2CE_Swiss_CustomReports_Base
* @package I2CE
* @subpackage Core
* @author Carl Leitner <litlfred@ibiblio.org>
* @version 2.1
* @access public
*/


abstract class I2CE_Swiss_CustomReports_Base extends I2CE_Swiss{

    public function initializeDisplay($action) {
        parent::initializeDisplay($action);
        $this->template->addHeaderLink('mootools-core.js');
        return true;
    }

    public function displayOptions($optionsNode,$transient_options) {
        if (!$this->template->appendFileByNode('customreports_options.html','span',$optionsNode) instanceof DOMNode) {
            return;
        }
        $this->template->setDisplayData('locale_link',$this->getURLRoot(). $this->path . $this->getURLQueryString(array('locale'=>null)), $optionsNode);
        $this->template->setDisplayData('current_locale', $this->getLocale(),$optionsNode);
    }



    

    protected function getAncestorByClass($class) {
        $swiss = $this;
        while ( (!$swiss instanceof $class) && ($swiss instanceof I2CE_Swiss) && ($swiss->hasParent())) {
            $swiss = $swiss->getParent();
        }
        if (!$swiss instanceof $class) {
            return null;
        } else {
            return $swiss;
        }
    }




}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
