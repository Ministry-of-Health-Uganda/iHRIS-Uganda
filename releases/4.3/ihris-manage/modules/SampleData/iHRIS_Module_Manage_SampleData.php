<?php     // -*- mode: php; c-default-style: "bsd"; indent-tabs-mode: nil; c-basic-offset: 4 -*-
/**
* © Copyright 2008 IntraHealth International, Inc.
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
*  iHRIS_Module_Manage_Demo
* @package iHRIS
* @subpackage Manage
* @author Carl Leitner <litlfred@ibiblio.org>, Luke Duncan <lduncan@intrahealth.org>
* @copyright Copyright &copy; 2008 IntraHealth International, Inc. 
* @since v3.1.0
* @version v3.1.0
* @access public
*/


class iHRIS_Module_Manage_SampleData extends I2CE_Module {

    
    /**
     * Method called when the module is enabled for the first time.
     * @param boolean -- returns true on success. false on error.
     */
    public function action_initialize() {
        /*
        $site_module = "";
        I2CE::getConfig()->setIfIsSet( $site_module, "config/site/module" );
        $mod_factory = I2CE_ModuleFactory::instance();
        if ( $mod_factory->isEnabled( $site_module ) ) {
            //launch background import of data.
            $this->launchBackgroundPage("/BackgroundProcess/SQL/ManageSampleData.sql");
        } else {
        */
            //load the data right now because the site isn't ready to run background processes
            I2CE::raiseError("Loading Sample Data");
            if (!I2CE_Util::runSQLScript('ManageSampleData.sql')) {
                I2CE::raiseError("Could not initialize Manage Sample Data tables");
                return false;
            }        
            /*
        }
        */
        return true;
    }
        
    



}
?>
