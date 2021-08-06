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
 * Export Magic Data Page
 *
 * @package I2CE
 * @subpackage Core
 * @author Carl Leitner <litlfred@ibiblio.org>
 * @version 2.1
 * @access public
 */

class I2CE_Page_MagicDataExport extends I2CE_Page {


        

    /**
     * @var protected string $config_path  the configuration path requested for this page.
     */
    protected $config_path;

    /**
     * @var I2CE_MagicData protected The magic data pointed to by the config path
     */
    protected $config;
    


    protected function setConfig() {        
        if ($this->request_exists('config_path')) {
            $this->config_path = $this->request('config_path');
        } else {
            $this->config_path = '/' . implode('/' , $this->request_remainder);
        }
        $this->config = I2CE::getConfig()->traverse($this->config_path,false,false);
        if (!$this->config instanceof I2CE_MagicDataNode ) {
            $this->config = null;
        }
    }

    /**
     * Perform the actions for this page
     * @return boolean
     */
    protected function action() {
        parent::action();
        $this->setConfig();
        if (!$this->actionExport()) {
            $this->userMessage("There was a problem with exporting the data. Sorry.");
            return false;
        }
        return true;
    }

    /**
     * Call the action method for this page from the command line.
     * @return boolean
     */
    protected function actionCommandLine( $args, $request_remainder ) {
        $this->action();
        if (array_key_exists('surress_output',$args) && $args['supress_output']) {
            $this->_display(true);
        } else {
            $this->_display(false);
        }
    }


    protected function getMetaDataOptions() {
        $options = array();
        $base_version  = "1.0";
        $site_module = false;
        if ( (I2CE::getConfig()->setIfIsSet($site_module,"/config/site/module"))
             && I2CE_MagicDataNode::checkKey($site_module)) {
            I2CE::getConfig()->setIfIsSet($base_version,"/config/data/" . $site_module .'/version');
        }
        $defaults = array(
            'description' => 'MagicDataExport on ' . strftime('%c'),
            'version'=>$base_version . '.' . time(),
            'displayName'=>'Site Export' );
        foreach($defaults as $key=>$default) {
            if ($this->request_exists($key)) {
                $options[$key] = $this->request($key);
            } else {
                $options[$key] = $default;
            }
        }
        return $options;
    }


    protected function getModuleName() {
        if ($this->request_exists('name')) {
            $name = $this->request('name');
        } else {
            $site_module = 'Site'; 
            I2CE::getConfig()->setIfIsSet($site_module,'/config/site/module'); 
            $name = $site_module  . '_export_' . time(); 
        }
        return $name;
    }

    protected function actionExport() {
        //now start filling in the metadata
        //now fill in the config data
        if (!$this->template instanceof I2CE_MagicDataExport_Template) {
            I2CE::raiseError("Template is not Magic data:" . get_class($this->template));
            return false;
        }
        $name = $this->getModuleName();
        $path = null;
        if ($this->config instanceof I2CE_MagicDataNode) {
            $path = '/' . $this->config->getPath(false);
        }
        if (!$this->template->setModule($name,$path)) {
            I2CE::raiseError("Could not set module name");
            return false;
        }
        $options = $this->getMetaDataOptions();
        if (!$this->get_exists('no_metadata')) {            
            if (!$this->template->createMetaDataNode($options)) {
                I2CE::raiseError("Could not set the metadata for export");
                return false;
            }
        }
        $configNodes = $this->template->query('/I2CEConfiguration/configurationGroup');
        if ($configNodes->length != 1) { 
            I2CE::raiseError("Could not find top level configurationGroup");
            return false; //someone was messing around with the template file.
        }
        if (!$this->config instanceof I2CE_MagicDataNode) {
            //no magic data to export.
            if (!$this->noConfig($configNodes->item(0))) {
                return false;
            }
        } else {
            $configNodes->item(0)->appendChild(
                $this->template->createElement('version', array(), $options['version'])
                );
            if (!$this->template->createExport($configNodes->item(0),$this->config)) {
                I2CE::raiseError("Could not create magic data export XML Dom");
                return false;
            }
        }
        //we are good to go with the export.  Add an inline header 
        $this->template->addHeader("Content-disposition: attachment; filename=\"$name.xml\"");
        $this->template->addHeader("Content-type: application/xml");
        return true;
    }

    /**
     * Method called when there is no magic data to export
     *@param DOMNode $configNode The I2CEConfiguration/configurationGroup node
     * @returns boolean true on success
     */
    protected function noConfig($configNode) {
        $this->template->removeNode($configNode);
        return true;
    }

    

}

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
