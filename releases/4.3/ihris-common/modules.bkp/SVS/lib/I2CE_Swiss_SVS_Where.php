<?php
/**
* © Copyright 2014 IntraHealth International, Inc.
* 
* This File is part of I2CE 
* 
* I2CE is free software; you can redistribute it and/or modify 
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
* @package ihris-common
* @subpackage svs
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.2.0
* @since v4.2.0
* @filesource 
*/ 
  /** 
   * Class I2CE_Swiss_SVS_where
   * 
   * @access public
   */



class I2CE_Swiss_SVS_Where extends I2CE_Swiss_Where{


    public function getChildType($child) {
        if($child ==  'operand') {
            return 'SVS_Where_Operands';
        }
        return parent::getChildType($child);
    }

    public function getForm() {
        if ($this->parent instanceof I2CE_Swiss_SVS   ) {
            return $this->parent->getField('list');
        }
	return parent::getForm();
    }

    public function getFormName() {
        if ($this->parent instanceof I2CE_Swiss_SVS   ) {
            return $this->parent->getField('list');
        }
	return parent::getFormName();
    }

    public function displayValues($content_node,$transient_options, $action) {
        $this->template->addHeaderLink('CustomReports.css');
        return parent::displayValues($content_node,$transient_options, $action);
    }

  }


# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
    