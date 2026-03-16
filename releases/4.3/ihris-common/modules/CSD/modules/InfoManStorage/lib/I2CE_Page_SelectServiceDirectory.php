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
* @package I2CE
* @subpackage CSD
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.2.0
* @since v4.2.0
* @filesource 
*/ 
/** 
* Class I2CE_Page_SelectServiceDirectory
* 
* @access public
*/


class I2CE_Page_SelectServiceDirectory extends I2CE_Page {

    public function action() {
        parent::action();

	$init_options = array(
            'root_path'=>'/modules/forms/storage_options/CSD/remote_services',
            'root_path_create'=>true,                    
	    'root_url'=>'remote_directory_selector',
            'root_type'=>'ServiceDirectorySelector');
        try {
            $swiss_factory = new I2CE_SwissMagicFactory($this,$init_options);
        } catch (Exception $e) {
            I2CE::raiseError("Could not create swissmagic for selectable" . $e->getMessage());
            return false;
        }
        try {
            $swiss_factory->setRootSwiss();
        } catch (Exception $e) {
            I2CE::raiseError("Could not create root swissmagic for selectable" . $e->getMessage());
            return false;
        }
        $swiss_path = $this->request_remainder;
        $action = array_shift($swiss_path); 

        if ($action == 'update' && $this->isPost()) {
            if ($swiss_factory->updateValues($this->post())) {
		$this->userMessage ( "Updated Remote Directories");
	    } else {
		$this->userMessage ( "Unable To Update Remote Directories");
	    }
	    $this->setRedirect('home');
        }
	$action = 'edit';
        return $swiss_factory->displayValues( $this->template->getElementById('siteContent'),$swiss_path, $action);

    }

}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
