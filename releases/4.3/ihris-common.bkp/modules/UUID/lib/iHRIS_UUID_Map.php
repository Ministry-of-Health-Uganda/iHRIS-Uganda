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
* @package common
* @subpackage uuid
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.0.0
* @since v4.0.0
* @filesource 
*/ 
/** 
* Class iHRIS_UUID_Map
* 
* @access public
*/
if (defined('UUID_TYPE_DEFAULT')) {
    require_once('classDefs/iHRIS_UUID_Map_pecl.php');  //the UUID package which comes from pecl
} else if (defined('UUID_MAKE_V3')) {
    require_once('classDefs/iHRIS_UUID_Map_ossp.php');  //the OSSP UUID package which comes from apt-get php5-uuid
}


