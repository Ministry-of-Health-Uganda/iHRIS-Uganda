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
* @subpackage pages
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.2.0
* @since v4.2.0
* @filesource 
*/ 
/** 
* Class I2CE_Swiss_PageArgs
* 
* @access public
*/


class I2CE_Swiss_PageArgs extends I2CE_Swiss {


    /**
     * Update config for given values
     * @param array $vals.  An array of values (presumably from $_POST) to update magic data from
     * @returns  true on sucess
     */
    public function processValues($vals) {
        if (array_key_exists('defaultHTMLFile',$vals)
            && is_array($vals['defaultHTMLFile'])) {
            foreach ($vals['defaultHTMLFile'] as $key=>$val) {
                if (!I2CE_MagicDataNode::checkKey($key)
                    || !is_scalar($val)) {
                    continue;
                }
                if( $this->storage->is_scalar("defaultHTMLFile/$key")
                    || $val ) {
                    //either key was already set and we are ovewriting it, or we are adding a new key with a set value
                    $this->storage->defaultHTMLFile->$key=$val;
                }
			
            }
        }
        if (array_key_exists('title',$vals)) {
            $this->setTranslatableField('title',$vals['title']);	   
        }
		
		return true;
    }

    protected function getTemplate() {
        return 'swiss_page_args.html';
    }


    public function displayValues($content_node,$transient_options, $action) {
        if (! ($mainNode = $this->template->appendFileByNode($this->getTemplate(),'div',$content_node)) instanceof DOMNode) {
            return false;
        }
        return $this->displayArgs($mainNode,$transient_options,$action);
    }


    public function displayArgs($mainNode,$transient_options,$action) {
        //Display 
        $this->template->setDisplayDataImmediate('title',$this->getField('title'),$mainNode);
        $inputs = array('title');

        if ( ($filesNode = $this->template->getElementByName('html_files',0,$mainNode)) instanceof DOMNode) {
            $html_files = $this->storage->getAsArray('defaultHTMLFile');
            if (!is_array($html_files) || count($html_files) == 0) {
                $html_files = array(0=>''); //make sure there is room to put at least one file
            } else {
                ksort($html_files);
                $html_files[] = '';
            }
            $first = true;
            foreach ($html_files as $k=>$v) {
                if (!$first) {
                    $filesNode->appendChild($this->template->createElement('br',array()));
                } else {
                    $first = false;
                }
                $input = 'defaultHTMLFile[' . $k  .']';
                $inputs[] = $input;
                $filesNode->appendChild($this->template->createTextNode($k . ':'));
                $filesNode->appendChild($this->template->createElement('input',array('name'=>$input,'value'=>$v)));
            }
        }
		
		$this->renameInputs($inputs,$mainNode);
        return true;
    }
  }
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
