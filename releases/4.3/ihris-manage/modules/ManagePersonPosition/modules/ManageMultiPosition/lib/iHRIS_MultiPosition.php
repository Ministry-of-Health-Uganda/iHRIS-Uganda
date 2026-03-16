<?php
/*
 * © Copyright 2007, 2008 IntraHealth International, Inc.
 * 
 * This File is part of iHRIS
 * 
 * iHRIS is free software; you can redistribute it and/or modify
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
 */
/**
 * @package iHRIS
 * @subpackage Manage
 * @author Luke Duncan <lduncan@intrahealth.org>
 * @copyright Copyright &copy; 2007, 2008 IntraHealth International, Inc. 
 * @since v4.1.0
 * @version v4.1.0
 */
/**
 * Object for dealing with create multiple positions at once.
 * 
 * @package iHRIS
 * @subpackage Manage
 * @access public
 */
class iHRIS_MultiPosition extends iHRIS_Position {

    /**
     * Return the template file to be used for this form.
     * @param string $type
     * @return string
     */
    public function getHTMLTemplate($type='default') {
        if ( $this->getId() == 0 ) {
            return "lists_new_form_position.html";
        } else {
            return parent::getHTMLTemplate($type);
        }
    }

    /**
     * Validate this form.
     */
    public function validate() {
        if ( I2CE_Validate::checkNumber( $this->count, 1 ) ) {
            if ( !$this->code_start || !I2CE_Validate::checkNumber( $this->code_start, 0 ) ) {
                $this->setInvalidMessage('code_start','required');
            } 
            if ( $this->code_format != "" ) {
                if ( preg_match( '/%[\d\.]*d/', $this->code_format ) == 0 ) {
                    $this->code_format .= "%d";
                }
                $storage = I2CE_ModuleFactory::instance()->getClass("forms-storage");
                if ( $storage instanceof I2CE_FormStorage ) {
                    $count = $this->count;
                    $code_start = $this->code_start;
                    while ( $count-- ) {
                        $this->code = sprintf( $this->code_format, $code_start );
                        $storage->validate_formfield( $this->getField( "code" ) );
                        if ( $this->getField("code")->hasInvalid() ) {
                            $this->setInvalidMessage("code_format",'unique');
                        }
                        $code_start++;
                    }
                }
            }
        }
        parent::validate();
    }

    /**
     * Make a special save function
     * @param I2CE_User $user
     * @param boolean $transact
     * @return boolean
     */
    public function save( $user, $transact = true ) {
        if ( $this->getId() != '0' ) {
            return parent::save( $user, $transact );
        }
        $ret_val = true;
        $count = $this->count;
        if ( !$count ) {
            $count = 1;
        }
        $code_start = $this->code_start;
        while ( $count-- ) {
            $this->setId( 0 );
            if ( $this->code_format != "" ) {
                $this->code = sprintf( $this->code_format, $code_start );
                $code_start++;
            }
            $ret_val = $ret_val && parent::save( $user, $transact );
        }
        return $ret_val;
    }

    
}

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
