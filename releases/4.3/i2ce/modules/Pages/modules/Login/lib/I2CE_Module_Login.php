<?php
/**
 * @copyright © 2007, 2009 Intrahealth International, Inc.
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
*  I2CE_Module_Login
* @package I2CE
* @subpackage Core
* @author Carl Leitner <litlfred@ibiblio.org>
* @version 2.1
* @access public
*/


class I2CE_Module_Login  extends I2CE_Module{

        public static function getMethods() {
            return array('I2CE_Wrangler->manipulateWrangler_I2CE_logout'=>'manipulateWrangler');
        }
         
        public function manipulateWrangler($wrangler,$module, $page, $request,$pageRoot, $pageRemainder) {
            $user = new I2CE_User();
            $user->logout();
            if ( array_key_exists( 'autologout', $_GET ) 
                    && array_key_exists( 'HTTP_REFERER', $_SERVER ) && $_SERVER['HTTP_REFERER'] ) { 
                $_SESSION['referal'] = $_SERVER['HTTP_REFERER'];
            }   
            return  array('module'=>$module,'page'=>'login','request'=>$request,'pageRoot'=>$pageRoot,'pageRemainder'=> $pageRemainder);
        }
}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
