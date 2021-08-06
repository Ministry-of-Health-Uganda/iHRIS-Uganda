<?php
/**
 * @copyright © 2011, 2012 Intrahealth International, Inc.
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
 * @access public
 * @author Luke Duncan <lduncan@intrahealth.org>
 * @since 4.1.1
 * @version 4.1.1
 */

/**
 * The abstract class for that all access modules must implement
 * @package I2CE
 */
abstract class I2CE_ModuleAccess extends I2CE_Module {

    /**
     * @var I2CE_User The current user object.
     */
    protected $user;
    
    /**
     * Return an array of limit_add options to 
     * add for this user to limit fields.
     * @param I2CE_Form $formObj
     * @param array $args
     * @return array
     */
    abstract public function getLimitAdd( $formObj, $args );

    /**
     * Return the list of allowed fields for the given form 
     * for this access module.
     * Return true if there are no limits at all set for the
     * user. (i.e. everything is allowed)
     * @param string $form
     * @return mixed
     */
    abstract public function getLimitsByForm( $form );


    /**
     * Create this module object to set the current user.
     */
    public function __construct() {
        parent::__construct();
        $this->user = new I2CE_User();
    }

    /**
     * Set the user for this module.
     * This is for any command line access that needs to override the user.
     * @param I2CE_User $user
     */
    public function setUser( $user ) {
        if ( $user instanceof I2CE_User ) {
            $this->user = $user;
        } else {
            I2CE::raiseError("Invalid user object passed to setUser");
        }
    }

    /**
     * Return the current user object.
     * @return I2CE_User
     */
    public function getUser() {
        return $this->user;
    }


}

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
