<?php
/**
 * @copyright © 2014 Intrahealth International, Inc.
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
    * @author Luke Duncan <lduncan@intrahealth.org>
    * @since v4.1.10
    * @version v4.2.0
    */
/**
 * Class for defining binary file storage field used by a {@link I2CE_Form} object.
 * @package I2CE
 * @access public
 */
$config = I2CE::getConfig();
$binary_storage = 'db';
$config->setIfIsSet( $binary_storage, '/modules/BinField/store/mechanism' );
if ( $binary_storage == 'file' ) {
    $migrated = 0;
    $config->setIfIsSet( $migrated, '/modules/BinField/store/migrated_to_file' );
    if ( $migrated != 1 ) {
        I2CE::raiseError( "FATAL ERROR: Trying to use file storage for binary files, but the data must be manually migrated or you may lose your data.  Please see here for more details: " );
        die();
    }
    require_once('classDef/I2CE_FormField_STORE_BINARY_FILE.file.php' );
} else {
    // Just default to DB if something invalid is there.
    require_once('classDef/I2CE_FormField_STORE_BINARY_FILE.db.php' );
}


# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
