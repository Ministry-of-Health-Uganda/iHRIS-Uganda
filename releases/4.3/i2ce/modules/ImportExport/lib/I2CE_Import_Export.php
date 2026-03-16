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
 ** The module that Debuggin infor
 * @package I2CE
 * @access public
 * @author Mark A. Hershberger <mhershberger@intrahealth.org>
 * @since 3.0
 * @version 3.0
 */

class I2CE_Import_Export extends I2CE_Module {

    public function __construct() {
        parent::__construct();
    }
        
    public function action_initialize() {
        I2CE::raiseError("Initializing Form Tables");
        if (!I2CE_Util::runSQLScript('initialize_importexport.sql')) {
            I2CE::raiseError("Could not initialize I2CE import_export table");
            return false;
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
