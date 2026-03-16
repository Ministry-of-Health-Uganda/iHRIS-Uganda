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
* @subpackage Forms
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.2.0
* @since v4.2.0
* @filesource 
*/ 
/** 
* Class I2CE_PageXMLRelationship
* 
* @access public
*/


class I2CE_Page_FormRelationship_Import extends I2CE_Page {



    protected function loadHTMLTemplates() {
        if ($this->isPost()) {
            $this->template->appendFileById("upload_form_relationship_progress.html", 'div', 'siteContent' );	
        } else {
            $this->template->appendFileById("upload_form_relationship.html", 'div', 'siteContent' );	
        }
        return true;
    }

    protected function _display($supress_output = false) {
	if(!$this->isPost()) { //not doing a data import
	    return parent::_display($supress_output);
	} else {
            parent::_display($supress_output);
            if ( ($errors = I2CE_Dumper::cleanlyEndOutputBuffers())) {
                I2CE::raiseError("Errors:\n" . $errors);
            }
            if (!$this->hasPermission('task(form_relationship_can_import)')) {
                $this->pushError("You do not have permission to import");
                return false;
            }
            $ignore_ids = $this->request_exists('ignore_ids') && $this->request('ignore_ids');
            $src = $_FILES['upload']['tmp_name'];
            $this->import($src,$ignore_ids);            
            return true;
        }
    }


    /**
     * Perform any actions for the page
     * 
     * @returns boolean.  true on sucess
     */
    public function action() {
        parent::action();
        if (!$this->hasPermission('task(form_relationship_can_import)')) {
            return false;
        }
        return true;
    }



    public function pushContent($html) {
	I2CE::raiseError($html);
	$js_message = '<script type="text/javascript">addContent("<div>' . $html .'</div>");</script>';
	echo $js_message;
	flush();

    }

    public function pushError($message,$i=0) {	 
	I2CE::raiseError($message);
	$this->pushMessage($message,$i);
    }
    
    public function pushMessage($message,$i=0) {	
        I2CE::raiseMessage($message);
	$js_message = '<script type="text/javascript">addMessage("' .  str_replace("\n",'<br/>',addcslashes($message , '"\\')) . '",' . $i .  ");</script>\n";
	echo $js_message;
	flush();	
    }

    public function pushCount($count) {
	I2CE::raiseError("Doing $count");
	$js_message = '<script type="text/javascript">setCount(' .$count. ");</script>\n";
	echo $js_message;
	flush();	
    }





    public function actionCommandLine($args, $request_remainder) {        
        if (array_key_exists('file',$args)
            && is_readable($file = $args['file'])
            ){
            $ignore_ids = (array_key_exists('ignore_ids',$args) && $args['ignore_ids']);
            return $this->import($file,$ignore_ids);
        } else {
            I2CE::raiseError("No file specified");
            return false;
        }
    }


    protected function import($file,$ignore_ids = false) {

        $reader = new XMLReader;
        $reader->open($file, null, 1<<19);
        $save_ids = array();
        $count = 0;
	$exec = array('max_execution_time'=>20*60, 'memory_limit'=> (256 * 1048576));	    
        $importer = new I2CE_FormRelationship_Importer();
        if (array_key_exists('HTTP_HOST',$_SERVER) ) {
            $importer->setMessageCallback(array($this,'pushMessage'));
        }
        $defaults = array(
            'ignoreids'=>$ignore_ids ? '1' : '0',
            'nomatching'=>'',
            'invalid'=> ''
            );

        $next = false;
        while ($next || $reader->read()){  
            $next =false;
            if ($reader->nodeType != XMLReader::ELEMENT){
                continue;
            }
            switch ($reader->name) {
            case  'relationshipCollection':
                foreach ($defaults as $key=>$val){
                    if ( ($v = $reader->getAttribute($key)) !== null) {
                        $defaults[$key]= $v;
                    }
                }
                while ( $reader->read()) { //skip to a relationship sub-element
                    if ($reader->nodeType == XMLReader::ELEMENT && $reader->name == 'relationship') {
                        break;
                    }
                }
                //break;  - purposefully do not break as we want to get process any relationship under a relationshipCollection
            case 'relationship':
                I2CE::longExecution($exec);
                $node = $reader->expand();
                $doc = new DOMDocument();
                $i_node = $doc->importNode($node,true);
                foreach ($defaults as $k=>$v) {
                    $i_node->setAttribute($k,$v);
                }
                $new_ids =  $importer->loadFromXML($i_node);
                $save_ids = array_merge($save_ids,$new_ids);
                if (array_key_exists('HTTP_HOST',$_SERVER) && count($new_ids) > 0) {
                    $this->pushMessage("Imported records with ids:\n". implode(",",$new_ids));
                }
                $count++;
                $reader->next();
                $next = true;
                break;
            default:
                if (array_key_exists('HTTP_HOST',$_SERVER)) {
                    $this->pushError("Unrecognized data type: " . $reader->name);
                }               
                break;
            }
        }
        $summary = "Import Summary:  (processed $count relationships)\n";
        foreach ($save_ids as $save_id=>$msg) {
            $summary   .=  str_replace("\n","\n  - ",$msg) .  "\n";
        }
        I2CE::raiseMessage($summary);
        $this->pushMessage($summary);
        return true;
    }


}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
