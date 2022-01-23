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
 *  I2CE_Page_Stub
 * @package I2CE
 * @subpackage Core
 * @author Carl Leitner <litlfred@ibiblio.org>
 * @version 2.1
 * @access public
 */


class I2CE_Page_Stub extends I2CE_Page{
    


    public function display($supress_output = false) {
        if (!array_key_exists('HTTP_HOST',$_SERVER)) {
            exit("No command line usage for this page");
        }
        foreach (array('request','content') as $key) {
            if (!$this->get_exists($key)) {
                I2CE::raiseError("Invalid Stub request:  $key is missing");
                return false;
            }
        }
        $value = $this->get('content');
        $req = array_pad(explode('?',$this->request('request')),2,'');
        list($req,$query_str) = $req;
        $get = array(); 
        if ($query_str) {
            parse_str($query_str, $get);
        }


        $post = array(); 
        if ($this->isPost()) {
            I2CE_Util::flattenVariables($this->post,$post,false);
        }
        if (count($this->request_remainder) > 0) {
            $attribute = $this->request_remainder[0];
        } else {
            $attribute = 'id';
        }
        if (strpos($value,',')) {
            $values = explode(',',$value);
            $value = array();
            foreach ($values as $val) {
                list($a,$v) = array_pad(explode('=',$val,2),2,'');
                if (!$v) {
                    $v = $a;
                    $a = $attribute;
                }
                if (!array_key_exists($a,$value) || !is_array($value[$a])) {
                    $value[$a] = array();
                }
                $value[$a][] = $v;
            }
        } else {
            $value = array($attribute=>array($value));
        }        
        $js = false;
        if ($this->get_exists('keep_javascripts')) {
            $js =  $this->get('keep_javascripts');
        }
        
        $wrangler = new I2CE_Wrangler();
        $page = $wrangler->wrangle($req,false, $get,$post);            
        if (!$page instanceOf I2CE_Page) {
            $this->userMessage("Unable to find requested page",'notice');
            I2CE::raiseError("Unable to create page");
            exit();
        }
        $page->setIsPost($this->isPost());
        $page->display(true);          
        foreach ($value as $a=>&$vals) {      
            if (!is_array($vals) || count($vals) == 0) {
                unset($vals[$a]);
                continue;
            }
            $vals = array_map('addslashes',$vals);
            $vals = "(@$a='" . implode("' or @$a='", $vals) . "')";
        }
        if (count($value) == 0) {
            $this->userMessage("Nothing selected to return");
            I2CE::raiseError("Nothing selected to return");
            exit();
        }
        $qry = '//*[(' . implode(' or ' ,$value) . ")]";
        $list= $page->getTemplate()->query($qry);
        $nodes = 0;
        if ($list instanceof DOMNodeList) {
            $nodes += $list->length;
        }
        $scriptNodeList  =null;
        if ($js) {
            $js_ids = explode(',',$js);
            $qry = '/html/head//script[@id="' . implode('" or @id="', $js_ids) . '"]';
            $scriptNodeList = $page->getTemplate()->query($qry);
            if ($scriptNodeList instanceof DOMNodeList) {
                $nodes += $scriptNodeList->length;
            }
        }       
        if ($nodes == 0) {
            I2CE::raiseError(
                "Requested page content not found: " . implode(' or ',$value) . " for request " 
                . $this->module . '/' . $this->page . '/' . implode('/', $this->request_remainder) 
                );
            exit();
        }               
        $doc = new DOMDocument();
        if ($scriptNodeList instanceof DOMNodeList) {
            for ($i=0; $i < $scriptNodeList->length; $i++) {
                $scriptNode = $doc->importNode($scriptNodeList->item($i),true);
                $doc->appendChild($scriptNode);
            }
        }
        if ($list instanceof DOMNodeList) {
            for ($i=0; $i < $list->length; $i++) {
                $doc->appendChild($doc->importNode($list->item($i),true));
            }
        }
        echo $doc->saveHTML();
    }



  }

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
