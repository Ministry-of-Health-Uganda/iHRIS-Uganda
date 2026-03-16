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
* @package i2ce
* @subpackage form
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.2.0
* @since v4.2.0
* @filesource 
*/ 
/** 
* Class I2CE_Page_RelationshipImporter
* 
* @access public
*/


class I2CE_Page_RelationshipImporter extends I2CE_Page{


    public function actionCommandLine($args, $request_remainder) {
	$input = 'php://stdin';
	if (!array_key_exists('new_form_ids',$args)) {
	    $args['new_form_ids'] = false;
	}
	if (array_key_exists('input',$args) ) {
	    $input = $args['input'];
	}
	if (array_key_exists('new_form_ids',$args) ) {
	    $args['new_form_ids'] = $args['new_form_ids'];
	}
	$user = new I2CE_User();
	$importer = new I2Ce_RelationshipImporter($user,$args);
	if ($importer->import_data(file_get_contents($input))) {
	    exit(0);
	} else {
	    exit(1);
	}
    }

}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
