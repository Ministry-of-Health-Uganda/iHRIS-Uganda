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
* @subpackage page
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.2.0
* @since v4.2.0
* @filesource 
*/ 
/** 
* Class I2CE_Swiss_PageFormAutoView
* 
* @access public
*/


class I2CE_Swiss_PageXMLRelationship_Args extends I2CE_Swiss_PageRelationship_Args {


    protected function getTemplate() {
	return 'swiss_xmlrelationship_args.html';
    }


    public function processValues($vals) {
        I2CE::raiseError(print_r($vals,true));
        I2CE::raiseError(print_r($_FILES,true));
        if (!parent::processValues($vals)) {
            return false;
        }
        if (array_key_exists('get_all_fields',$vals) 
            && is_scalar($vals['get_all_fields'])
            ) {
            $this->setField('get_all_fields', $vals['get_all_fields'] ? 1: 0);
        }
        if (array_key_exists('use_cache',$vals) 
            && is_scalar($vals['use_cache'])
            ) {
            $this->setField('use_cache', $vals['use_cache'] ? 1: 0);
        }

        if (array_key_exists('use_display_fields',$vals) 
            && is_scalar($vals['use_display_fields'])
            ) {
            $this->setField('use_display_fields', $vals['use_display_fields'] ? 1: 0);
        }
        if (array_key_exists('do_upload',$vals)
            && $vals['do_upload']
            && array_key_exists('__files_upload',$vals)
            && ($tmpfile = $vals['__files_upload'])
            && array_key_exists($tmpfile,$_FILES)
            && is_array($_FILES[$tmpfile])
            && array_key_exists('tmp_name',$_FILES[$tmpfile])
            && ($file = $_FILES[$tmpfile]['tmp_name'])
            && ($contents = file_get_contents($file)) 
            &&  ($transformNode = $this->storage->traverse("transform",true,false)) instanceof I2CE_MagicDataNode
            ){
            $transformNode->setAttribute('binary',1);
            $transformNode->setAttribute('encoding','base64');
            if ( array_key_exists('type',$_FILES[$tmpfile]) ) {
                $transformNode->setAttribute('mime-type',$_FILES[$tmpfile]['type']);
            }
            $transformNode->setValue(base64_encode($contents));
        } else  if (
            array_key_exists('do_select',$vals)
            && $vals['do_select']
            && array_key_exists('select',$vals)
            && is_scalar($vals['select'])
            && is_array( $options = $this->getXSLTS())
            && in_array($vals['select'],$options)
            &&  ($transformNode = $this->storage->traverse("transform",true,false)) instanceof I2CE_MagicDataNode
            ){
            $transformNode->setAttribute('binary',0);
            $transformNode->setValue('@'.$vals['select']);            
        } else if ( array_key_exists('do_erase',$vals)
            && $vals['do_erase']
            &&  ($transformNode = $this->storage->traverse("transform",false,false)) instanceof I2CE_MagicDataNode
            ){
            $transformNode->erase();
        }
        return true;
    }


    protected function getXSLTS() {
        $file_search = I2CE::getFileSearch();
        $file_search->loadPaths('XSLTS');
        $files = $file_search->findByGlob('XSLTS',array('*XSL','*xsl'),true);            
        $pathset = $file_search->getSearchPath('XSLTS');
        $options =array();
        foreach ($files as $file) {
            foreach ($pathset as $paths) {
                foreach ($paths as $path) {
                    if (strpos($file,$path) === 0) {
                        $options[] = substr($file,strlen($path) +1);
                        continue 3;
                    }
                }
            }
        }
        return $options;
    }

    public function getChildType($child) {
        if ($child =='fields') {
            return 'PageXMLRelationship_FormFields';
        }
        return parent::getChildType($child);
    }

    public function displayArgs($mainNode,$transient_options, $action) {		
	if (!parent::displayArgs($mainNode,$transient_options,$action)) {
	    return false;
	}
        $selected = array($this->getField('get_all_fields') ? 1: 0);
        $this->template->selectOptionsImmediate('get_all_fields',$selected,$mainNode);

        $selected = array($this->getField('use_cache') ? 1: 0);
        $this->template->selectOptionsImmediate('use_cache',$selected,$mainNode);

        $selected = array($this->getField('use_display_fields') ? 1: 0);
        $this->template->selectOptionsImmediate('use_display_fields',$selected,$mainNode);

        $transform = '';
        if (($transformNode = $this->storage->traverse("transform",false,false)) instanceof I2CE_MagicDataNode) {
            $transform = $transformNode->getValue();  
        }

        $is_file =  (is_string($transform) && strlen($transform) > 0 && $transform[0] == '@');
        $is_upload =  (is_string($transform) && strlen($transform) > 0 && $transform[0] != '@');

        if ( ($selectNode = $this->template->getElementByName('select',0,$mainNode)) instanceof DOMElement) {
            $options = $this->getXSLTS();
            if ($is_file) {
                if ( ($doSelectNode = $this->template->getElementByName('do_select',0,$mainNode)) instanceof DOMElement) {
                    $doSelectNode->setAttribute('checked','checked');
                }
                $selected = substr($transform,1);
            } else {
                $selected = false;
            }
            foreach ($options as $option) {
                $attrs = array('value'=>$option);
                if ($option == $selected) {
                    $attrs['selected'] = 'selected';
                }
                $selectNode->appendChild($this->template->createElement('option',$attrs,$option));
            }
            if ($selected) {
                $this->template->setDisplayDataImmediate('has_transform',1,$mainNode);
                $this->template->setDisplayDataImmediate('transform_link','file/'. $selected ,$mainNode);
            } else {
                $this->template->setDisplayDataImmediate('has_transform',0,$mainNode);
            }

        }
        if ($is_upload) {
            if ( ($doSelectNode = $this->template->getElementByName('do_upload',0,$mainNode)) instanceof DOMElement) {
                $doSelectNode->setAttribute('checked','checked');
            }            
            $this->template->setDisplayDataImmediate('has_download',1,$mainNode);
            $this->template->setDisplayDataImmediate('download_link',"magicDataBrowser/download/" . $this->storage->getPath(false) . "/transform", $mainNode);
        } else {
            $this->template->setDisplayDataImmediate('has_download',0,$mainNode);
        }
        $this->renameInputs(array('use_display_fields','get_all_fields','use_cache','do_select','do_upload','select','upload','do_erase'),$mainNode);

        if ( ($formfieldsChild = $this->getChild('fields',true)) instanceof I2CE_Swiss
	     && ( $formfieldsNode = $this->template->getElementById('fields',$mainNode)) instanceof DOMNode
	    ) {
            $formfieldsChild->addAjaxLink('fields_link','fields_container', 'fields_ajax' ,$formfieldsNode,$action, $transient_options);
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
