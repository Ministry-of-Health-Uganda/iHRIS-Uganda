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
 * @package I2CE
 * @author Carl Leitner <litlfred@ibiblio.org>
 * @since v2.0.0
 * @version v2.0.0
 */
/**
 * Page to run sql scripts in the background
 * @package I2CE
 * @abstract
 * @access public
 * @see Template
 */
class I2CE_Page_Run_SQL extends I2CE_Page{

    /**
     *called from the command line as
     *   --page=BackgroundPage/$script"
     * Or:
     *  --page=BackgroundPage/$db/$script
     *Where $db is the database the script is to run on.  Make sure $db is not backticked!
     *
     */
    protected function actionCommandLine($args,$req) {
        $req = $this->request_remainder;
        if (count($req) == 1) { //the first (and only) request remaineder is the script
            I2CE_Util::runSQLScript($req[0]);
        }  else if (count($req) ==2){ //the first request remainder is the DB to use the second is the script
            I2CE_Util::runSQLScript($req[1],$req[0]);  
        } else {
            I2CE::raiseError("Invalid request to run sql script");                
        }
    }

} 


# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
