<?php
/**
* © Copyright 2009 IntraHealth International, Inc.
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
* @package I2CE
* @subpackage forms
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.0.0
* @since v4.0.0
* @filesource 
*/ 
/** 
* Class I2CE_FormStorage_magicdata
* 
* @access public
*/


  //we want the magicdata formstorage to use the DB, if possible, to popualate.
  //thus the definition of the I2CE_FormStorage_magicdata class will depend on if
  //the permanent magic data storage is I2CE_MagicDataStorageDB or not.
  //
  //We put the class definiitions in a sun-directoryy classDef so they won't accidently
  //get picked up by the autoloader.


$config = I2CE::getConfig();
if ($config instanceof I2CE_MagicData && $config->getPermanentStorageClass() == 'I2CE_MagicDataStorageDBAlt') {
    require_once('classDef/I2CE_FormStorage_magicdata.db_alt.php');
}else if ($config instanceof I2CE_MagicData && $config->getPermanentStorageClass() == 'I2CE_MagicDataStorageDB') {
    require_once('classDef/I2CE_FormStorage_magicdata.db.php');
} else {    
    require_once('classDef/I2CE_FormStorage_magicdata.no_db.php');
}


# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
