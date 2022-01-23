<?php
/**
 * @copyright © 2013 Intrahealth International, Inc.
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
*  I2CE_Module_Cron
* @package I2CE
* @subpackage Core
* @author Luke Duncan <lduncan@intrahealth.org>
* @version 4.1.6
* @access public
*/


class I2CE_Module_Cron extends I2CE_Module{

    /**
     * Return a list of the cron types available.
     * For use with an ENUM field type.
     * @return array
     */
    public function listTypes() {
        $types = array();
        foreach( I2CE::getConfig()->modules->admin->cron->types as $key => $data ) {
            if ( $data instanceof I2CE_MagicDataNode ) {
                $types[$key] = $data->display_name;
            }
        }
        return $types;
    }


}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
