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
* Class iHRIS_UUID_Map for pecl uuid
*   http://freezerfrog.com/2009/12/generating-a-uuid-in-php-using-pecl-uuid/comment-page-1/
* @access public
*/


class iHRIS_UUID_Map  extends I2CE_Form{


    /**
     *Checks to see if any fields of form has in invalid message
     * @returns boolean
     */
    public function hasInvalid() {
        if (parent::hasInvalid()) {
            return true;
        }
        return !self::isValidUUID($this->id);
    }


    /**
     * Ensure that the given id is aUUID
     * @param string $id
     * @returns boolean
     */
    public static function isValidUUID($id) {
        return (boolean) preg_match('/^[A-Fa-f0-9]{8}-[A-Fa-f0-9]{4}-[A-Fa-f0-9]{4}-[A-Fa-f0-9]{4}-[A-Fa-f0-9]{12}$/',$id);
    }



    /**
     * The uuid object used to create uuids on
     * @var static protected resource $uuidobject
     */
    protected static $uuidobject;
   
    /**
     * On long running deamons i've seen a lost resource. This checks the resource and creates it if needed.
     *
     */
    protected static function ensure() {
        if ( is_resource ( self::$uuidobject )) {
            return true;
        }
        if (!iHRIS_Module_UUID_Map::hasUUID()) {
            return false;
        }
        uuid_create ( &self::$uuidobject);
        if (!is_resource(self::$uuidobject)) {
            return false;
        }
        return true;
    } 

    /**
     * Wrapper function to fenerates a UUID via the pecl uuid module.
     * More {@link http://pwet.fr/man/linux/fonctions_bibliotheques/ossp/uuid doucmentation} on ossp uuid.
     *
     * <br/>
     * Version 1 UUIDs are guaranteed to be unique through combinations of hardware addresses, time stamps and random seeds. There is a reference in the UUID to the hardware (MAC) address of the first network interface card (NIC) on the host which generated the UUID this reference is intended to ensure the UUID will be unique in space as the MAC address of every network card is assigned by a single global authority (IEEE) and is guaranteed to be unique. The next component in a UUID is a timestamp which, as clock always (should) move forward, will be unique in time. Just in case some part of the above goes wrong (the hardware address cannot be determined or the clock moved steps backward), there is a random clock sequence component placed into the UUID as a catch-all for uniqueness.
     * <br/>
     * Version 3 and version 5 UUIDs are guaranteed to be inherently globally unique if the combination of namespace and name used to generate them is unique.  It is  not supported at the moment
     * <br/>
     * Version 4 UUIDs are not guaranteed to be globally unique, because they are generated out of locally gathered pseudo-random numbers only. Nevertheless there is still a high likelihood of uniqueness over space and time and that they are computationally difficult to guess. 
     *                                                                                                *
     * Loosely based off of work of  {@link http://www.php.net/manual/en/function.uniqid.php#88434 Marius Karthaus }
     *
     * @param string $version.  Defaults to 1
     * @retruns mixed string or false on failure
     */
    public static function generateUUID($version = 4) {
         if (!self::ensure()) {
             return false;
         }
         $uuidstring = '';
         switch ($version) {
         case 3:
             I2CE::raiseError("Version 3 not supported");
             return false;
             $version =UUID_MAKE_V3 ;
             break;
         case 5:
             I2CE::raiseError("Version 5 not supported");
             return false;
             $version =UUID_MAKE_V5 ;
             break;
         case 4:
             $version = UUID_MAKE_V4;
             break;
         default:
         case 1:
             $version = UUID_MAKE_V1;
             break;

         }
         if (  0 != uuid_make ( self::$uuidobject, $version ) > 0 ) { //i think it returns a non-zero on failure
             return false;
         }
         uuid_export ( self::$uuidobject, UUID_FMT_STR, &$uuidstring );
         return trim ( $uuidstring ); 
    }

}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
