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
*  I2CE_SwissConfig_FormRelationship_Where
* @package I2CE
* @subpackage Core
* @author Carl Leitner <litlfred@ibiblio.org>
 @version 2.1
* @access public
*/


class I2CE_Swiss_FormRelationship_Where extends I2CE_Swiss_Where {

    public function getForm() {
        if ($this->parent instanceof I2CE_Swiss_FormRelationship) {
            return $this->parent->getForm();
        }
        return parent::getForm();
    }

    public function getFormName() {
        if ($this->parent instanceof I2CE_Swiss_FormRelationship) {
            return $this->parent->getStorage()->getName();
        } 
        return parent::getFormName();
    }


    public function getChildType($child) {
        if ($child == 'operand') {
            return 'FormRelationship_Where_Operands';
        } 
        return parent::getChildType($child);
    }

     public function isRelationshipBase() {
        if ($this->parent instanceof I2CE_Swiss_FormRelationship) {
            $swissRelationship = $this->parent->getRelationship();
            return get_class($swissRelationship) === 'I2CE_Swiss_FormRelationship';
        } else if ($this->parent instanceof I2CE_Swiss_FormRelationship_Where_Operands) {
            return $this->parent->isRelationshipBase();
        }
        return null;
    }


    public function getExcludedForms() {
        $excludes =   I2CE::getConfig()->getAsArray("/modules/CustomReports/limit_excludes/relationship_form");
        if (!is_array($excludes)) {
            $excludes = array();
        }
        if ($this->isRelationshipBase()) {
            $texcludes = I2CE::getConfig()->getAsArray('/modules/CustomReports/limit_excludes/primary_form');
            if (!is_array($texcludes)) {
                $texcludes = array();
            }
            $excludes = array_merge($excludes,$texcludes);
        }
        return $excludes;
    }









}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
