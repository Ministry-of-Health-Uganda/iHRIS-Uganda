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
* @package i2ce
* @subpackage formbuilder
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.2.0
* @since v4.2.0
* @filesource 
*/ 
/** 
* Class I2CE_Swiss_FS_entry
* 
* @access public
*/


class I2CE_Swiss_FS_SVSFile extends I2CE_Swiss {


    public function displayValues($content_node,$transient_options, $action) {
        if (! ($mainNode = $this->template->appendFileByNode('swiss_form_storage_svs_file.html','div',$content_node)) instanceof DOMNode) {
            return false;
        }
        $source = '';
        $this->storage->setIfIsSet($source,'source');
        
        $has_source =  (is_string($source) && strlen($source) > 0); 
        if ($has_source) {
            $this->template->setDisplayDataImmediate('has_download',1,$mainNode);
            $this->template->setDisplayDataImmediate('download_link',"magicDataBrowser/download/" . $this->storage->getPath(false) . "/source", $mainNode);
        } else {
            $this->template->setDisplayDataImmediate('has_download',0,$mainNode);
        }
        $this->renameInputs(array('upload'),$mainNode);
        return true;

    }

    public function processValues($vals) {

        if ( array_key_exists('__files_upload',$vals)
            && ($tmpfile = $vals['__files_upload'])
            && array_key_exists($tmpfile,$_FILES)
            && is_array($_FILES[$tmpfile])
            && array_key_exists('tmp_name',$_FILES[$tmpfile])
            && ($file = $_FILES[$tmpfile]['tmp_name'])
            && ($contents = file_get_contents($file)) 
            &&  ($svsSourceNode = $this->storage->traverse("source",true,false)) instanceof I2CE_MagicDataNode
            ){
            $svsSourceNode->setAttribute('binary',1);
            $svsSourceNode->setAttribute('encoding','base64');
            if ( array_key_exists('type',$_FILES[$tmpfile]) ) {
                $svsSourceNode->setAttribute('mime-type',$_FILES[$tmpfile]['type']);
            }
            $svsSourceNode->setValue(base64_encode($contents));
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
