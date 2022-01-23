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
*  I2CE_Swiss_Locale
* @package I2CE
* @subpackage Core
* @author Carl Leitner <litlfred@ibiblio.org>
* @version 2.1
* @access public
*/


class I2CE_Swiss_Locale extends I2CE_Swiss {
    

    public function getDisplayName() {
        return $this->name;
    }
    public function getDescription() {
        return I2CE_Module_LocaleSelector::getLocaleName($this->name);
    }


    /**
     * Constructor
     * @param I2CE_MagicDataNode the storage for this swiss 
     */
    public function __construct($storage, $factory, $name=null,$parent = null) {
        parent::__construct($storage,$factory,$name,$parent);
        if ($this->storage->is_indeterminate()) {
            $this->storage->set_parent();
        }
        //$this->storage->resolution; //access the child resolution to make sure that this gets created as a parent

    }


    public function processValues($vals) {
        if (!array_key_exists('resolution', $vals)) {
            return true;
        }
        $resolution = I2CE_Locales::ensureValidResolution($this->name,explode(',',$vals['resolution']));
        if (!$this->storage->eraseChilren()) {
            return false;
        }
        $this->storage = $resolution;
        return true;
    }



    public function displayValues($contentNode, $trasient_options,$action) {
        if ( ! ($mainNode = $this->template->appendFileByNode('locale_' . $action . '.html', 'span', $contentNode) instanceof DOMNode) ){
            return false;
        }
        $this->template->setDisplayDataImmediate('displayName',$this->getDisplayName());
        $this->template->setDisplayDataImmediate('description',$this->getDescription());
        $this->template->setDisplayDataImmediate('locale_default', I2CE_Locales::DEFAULT_LOCALE , $mainNode);
        if (($pos = strpos($this->name,'_'))!== false) {
            $lang = substr($this->name,0,$pos);
        } else {
            $lang = $this->name;
        }
        $this->template->setDisplayDataImmediate('locale_language', $lang , $mainNode);
        $this->template->setDisplayDataImmediate(
            'resolution', 
            implode(',',I2CE_Locales::ensureValidResolution($this->name,$this->storage->getAsArray('resolution'))),
            $mainNode);
        if ($action === 'edit') {
            $this->renameInputs('resolution',$mainNode);
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
