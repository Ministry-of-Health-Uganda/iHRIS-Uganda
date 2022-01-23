<?php
/**
* © Copyright 2011 IntraHealth International, Inc.
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
* @package motools
* @subpackage I2CE_Page
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.1
* @since v4.1
* @filesource 
*/ 
/** 
* Class I2CE_Page_TreeSelectData
* 
* @access public
*/


class I2CE_Page_TreeSelectData extends I2CE_Page{
    


    public function display($supress_output = false) {
        if (!array_key_exists('HTTP_HOST',$_SERVER)) {
            exit("No command line usage for this page");
        }
        
        if (!$this->get_exists('delay_index')) {
            I2CE::raiseError("Invalid tree data request:  'request' is missing");
            return false;
        }
        
        $delay_index = $this->get('delay_index');
        if (!array_key_exists('tree_data',$_SESSION) || !is_array($_SESSION['tree_data']) || !array_key_exists($delay_index,$_SESSION['tree_data'])) {
            return false;
        }
        $data= $_SESSION['tree_data'][$delay_index];
        unset($_SESSION['tree_data'][$delay_index]);
        if (!is_array($data)) {
            return false;
        }
        $template = new I2CE_Template();        
        $template->loadRootText("<span id='root'>");
        $root = $template->getElementById('root');
        $doc = $template->getDoc();
        I2CE_Module_TreeSelect::createTreeData($template,$root,$data);

        $tdoc = new DOMDocument();
        $tdoc->appendChild($tdoc->importNode($root, true));
        echo $tdoc->saveHTML();
        die();
    }




}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
