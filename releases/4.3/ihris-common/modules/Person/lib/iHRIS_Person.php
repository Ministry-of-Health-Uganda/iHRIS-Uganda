<?php
/*
 * © Copyright 2006, 2007, 2008, 2009 IntraHealth International, Inc.
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
 */
/**
 * @package iHRIS
 * @subpackage Common
 * @author Luke Duncan <lduncan@intrahealth.org>
 * @since v4.1.0
 * @version v4.1.0
 */
/**
 * iHRIS_Person class for the person form.
 *
 * @package iHRIS
 * @subpackage Common
 */
class iHRIS_Person extends I2CE_Form {
    /**
     * Load the ignore surname field if it exists.
     * @param array $post
     * @param boolean $populate_on_set_id
     */
    public function setFromPost( $post, $populate_on_set_id = false ) {
        parent::setFromPost( $post, $populate_on_set_id );
        if ( is_array( $post ) && array_key_exists( 'ignore', $post ) 
                && is_array( $post['ignore'] ) ) {
            if ( array_key_exists( 'surname', $post['ignore'] ) ) {
                $this->fields['surname_ignore']->setFromPost( $post['ignore']['surname'] );
            }
        }
    }
}

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
