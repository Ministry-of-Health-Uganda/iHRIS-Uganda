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
 ** The module that adds in an image data type
 * @package I2CE
 * @access public
 * @author Carl Leitner <litlfred@ibiblio.org>
 * @since Demo-v1.a
 * @version Demo-v2.a
 */

/*
 *The module meta data
Module Name: Binary Files
Module Version: 0.1
Module URI: http://www.capacityproject.org
Description:  Module to handle binary files.
Author: Carl Leitner
Author Email:litlfred@ibiblio.org
*/


class I2CE_Module_BinaryFiles extends I2CE_Module {

        
        
    /**
     * Only allow the module to be enabled if the  finfo http://us2.php.net/fileinfo extension is enabled
     */
    public function action_enable() {
        if (!class_exists('finfo',false)) {
            I2CE::raiseError('Magic file utilties not enabled.  Please run \'sudo apt-get install libmagic-dev php5-dev; sudo pecl install Fileinfo\' and add extension=fileinfo.so in your php.ini file.  or some such thing.');
            return false;
        }
        return true;
    }

    /*
     * Upgrade module method
     */
    public function upgrade($old_vers,$new_vers) {
        if (I2CE_Validate::checkVersion($old_vers,'<','4.0.6')) {
            if (! $this->createTempUploadTable()) {
                return false;
            }
        }
        return true;
    }


    /**
     * Method called when the module is enabled for the first time.
     * @param boolean -- returns true on success. false on error.
     */
    public function action_initialize() {
        I2CE::raiseError("Initializing Form Tables");
        if (!$this->createTempUploadTable()) {
            return false;
        }
        return true;
    }

    /**
     * method to intialize temporary upload table
     * @returns boolean
     */
    protected function createTempUploadTable() {
        if (!I2CE_Util::runSQLScript('initialize_temp_upload.sql')) {
            I2CE::raiseError("Could not initialize tempoary upload table");
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
