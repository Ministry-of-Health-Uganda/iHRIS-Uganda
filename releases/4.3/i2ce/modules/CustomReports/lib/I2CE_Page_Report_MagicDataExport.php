<?php
/**
 * @copyright © 2008, 2009 Intrahealth International, Inc.
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
*  I2CE_Page_Report_MagicDataExport
* @package I2CE
* @subpackage Core
* @author Carl Leitner <litlfred@ibiblio.org>
* @version 2.1
* @access public
*/


class I2CE_Page_Report_MagicDataExport extends I2CE_Page_MagicDataExport{



    protected function setConfig() {
        if ( (count($this->request_remainder) > 0) && ($this->request_remainder[0] == '__NONE')) {
            $this->config_path = '__NONE';
            $this->config = null;
        } else {
            $this->config = I2CE::getConfig()->traverse('/modules/CustomReports',true);
            if (!$this->config instanceof I2CE_MagicDataNode ) {
                $this->config = null;
            }
        }
    }

    
    /**
     * Method called when there is no magic data to export
     *@param DOMNode $configNode The I2CEConfiguration/configurationGroup node
     * @returns boolean true on success
     */
    protected function noConfig($configNode) {
        if ($this->config_path != '__NONE') {
            $this->template->removeNode($configNode);
        }
        return true;
    }
   

    
    

    protected function getModuleName() {
        if ($this->request_exists('name')) {
            $name = $this->request('name');
        } else {
            $name = 'custom_report';
            if (count($this->request_remainder) > 0) { 
                if (count($this->request_remainder) > 1) {
                    $name .= '_' . substr($this->request_remainder[0],0,-1);
                    $name .= '_' . $this->request_remainder[1];
                } else {
                    $name .= '_' . $this->request_remainder[0];
                }
            }
            $name .= '_export_'. time(); 
        }
        return $name;
    }




    protected function getMetaDataOptions() {
        $options = $this->request();
        $dispName = 'Custom Reports';
        $description = "Custom Report";
        if (count($this->request_remainder) > 0) {
            if (count($this->request_remainder) == 1) {
                $dispName .= ' ' . ucfirst(strtolower($this->request_remainder[0]));
                $description .= ' ' .strtolower($this->request_remainder[0]) . ' export';
            } else {
                $dispName .= ' ' . ucfirst(strtolower(substr($this->request_remainder[0],0,-1)));
                $description .= ' ' . strtolower(substr($this->request_remainder[0],0,-1)) . ' export of ' . $this->request_remainder[1];
            }
        }
        $description .= ' on  ' . strftime('%c');
        foreach(array('version'=>'1.0.' . time(), 'displayName'=>$dispName,'description'=>$description) as $key=>$default) {
            if (!array_key_exists($key,$options)) {
                $options[$key] = $default;
            }
        }
        return $options;
    }







}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
