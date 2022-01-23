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


class I2CE_PageBuilder extends I2CE_Page {

    public function action() {
        parent::action();
		
		// Add js file for displaying printf arguments
		$this->template->addHeaderLink('printf.js');
		
		$init_options = array(
            'root_path'=>'/I2CE/page',
	    	'root_url'=>'PageBuilder',
            'root_type'=>'PageBuilder');
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
        
        //grab the action from the request
        $action = array_shift( $this->request_remainder);
        
        $swiss_path = $this->request_remainder;

        if ($action == 'update' && $this->isPost()) {
            if ($this->get('noRedirect')) {
                $redirect = false;
            } else {
                $redirect = true;
            }
            $msgs = array(
                'not_updated'=>'Unable to Update Values',
                'updated'=>'Updated Values');
            foreach ($msgs as $k=>&$v) {
                I2CE::getConfig()->setIfIsSet($v,"/I2CE/page/text/user_messages/$k");
            }
            I2CE::raiseError(print_r($this->post(),true));
            if ( $swiss_factory->updateValues($this->post(),$redirect)) {                        
                $msg = $msgs['updated'];
            } else {
                $msg = $msgs['not_updated'];
            }
            if ($redirect) {
                $this->userMessage($msg, 'notice',true);
                $swiss = $swiss_factory->getSwiss($swiss_path);
                if ($swiss instanceof I2CE_Swiss) { 
                    $redirect = $swiss->getURLRoot('edit') . $swiss->getPath() . $swiss->getURLQueryString();
                } else {
                    $redirect ='PageBuilder';
                }
                $this->setRedirect($redirect);
                return true;
            }
        }
        else if ($action == 'delete') {
        	
            return $this->actionDelete($init_options['root_path']);            
        }
	$action = 'edit';
        return $swiss_factory->displayValues( $this->template->getElementById('siteContent'),$swiss_path, $action);

    }
    
    protected function actionDelete($root_path) {
        $msgs = array(
            'cannot_delete'=>'You don\'t have access to delete report data',
            'bad_delete_path'=>"Invalid path for deletion"
            );
        foreach ($msgs as $k=>&$v) {
        //I2CE::raiseError($v);
            I2CE::getConfig()->setIfIsSet($v, $root_path."text/user_messages/$k");
        }

		
		
        if (! $this->hasPermission('task(can_delete_pages_arguments)')) {
            $this->userMessage($msgs['cannot_delete'],false);
            return false;
        }
        
        
        $request = $this->request_remainder;
        
        
        // This is for deleting entire pages (top level)
         if (count($this->request_remainder)  == 1 ) {
         	if (! $this->hasPermission('task(can_delete_pages)')  ) {
                $this->userMessage($msgs['cannot_delete'],false);
                return false; 
            }
        }
        
        //only works for sublevel page options
        if (count($this->request_remainder)  < 1) {
        //I2CE::raiseError("in count");
            $this->userMessage($msgs['bad_delete_path'] . ": ".$root_path . implode('/',$request),false);
            return false; 
        }
        
        $config = I2CE::getConfig()->traverse($root_path.'/' . implode('/',$request),false,false);
       
        if (!$config instanceof I2CE_MagicDataNode) {
        //I2CE::raiseError("in for config");
            $this->userMessage($msgs['bad_delete_path'] .": ".$root_path . implode('/',$request),false);
            return false;
        }
        $config->erase();
        if (array_key_exists('HTTP_REFERER',$_SERVER)) {
        //I2CE::raiseError("key exissts");            
            $this->redirect($_SERVER['HTTP_REFERER']);
        } else {
            array_pop($request);
            //I2CE::raiseError("key not exists");
            $this->redirect('PageBuilder/' . implode('/',$request));
        }
        return true;
    }

}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:

