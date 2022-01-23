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


class I2CE_Swiss_PageRelationshipGenerateTemplate_Args extends I2CE_Swiss_PageRelationship_Args {

    
    protected function getTemplate() {
	return 'swiss_relationshiptemplate_args.html';
    }


    public function getChildType($child) {
        if ($child =='required') {
            return 'PageRelationshipGenerateTemplate_Required';
        }
        return parent::getChildType($child);
    }

    protected static $breaks = array(
        'page'=>'<text:p text:style-name="P1"/>',
        'line'=>'<text:line-break/>'
        );




    public function processValues($vals) {
        I2CE::raiseError(print_r($vals,true));
        I2CE::raiseError(print_r($_FILES,true));
        if (!parent::processValues($vals)) {
            return false;
        }
        if (array_key_exists('use_cache',$vals) 
            && is_scalar($vals['use_cache'])
            ) {
            $this->setField('use_cache', $vals['use_cache'] ? 1: 0);
        }
        if (array_key_exists('segment_style',$vals) 
            && is_scalar($vals['segment_style'])
            ) {
            $this->setField('segment_style', $vals['segment_style'] );
        }

        if (array_key_exists('segment_break',$vals) 
            && is_scalar($vals['segment_break'])
            && array_key_exists($vals['segment_break'],self::$breaks)
            ) {
            $this->setField('segment_break', self::$breaks[$vals['segment_break']] );
        }

        if (array_key_exists('connection',$vals) 
            && is_scalar($vals['connection'])
            ) {
            $this->setField('connection', $vals['connection'] );
        }

        if (array_key_exists('tmp_dir',$vals) 
            && is_scalar($vals['tmp_dir'])
            ) {
            $this->setField('tmp_dir', $vals['tmp_dir'] );
        }

        $format = false;
        $formats = I2CE::getConfig()->getKeys("/modules/PrintedFormsODT/unoconv/conversions");
        if (array_key_exists('format',$vals) 
            && is_scalar($format = $vals['format'])
            && (in_array($format,$formats) || $format == '')
            ) {
            $this->setField('format',$format);
        }
        if (I2CE_MagicDataNode::checkKey($format)
            && array_key_exists('export_options',$vals) 
            && is_scalar($vals['export_options'])
            ) {
            $this->storage->export_options->$format  = $vals['export_options'];
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
            &&  ($templateContentNode = $this->storage->traverse("template_upload/content",true,false)) instanceof I2CE_MagicDataNode
            ){
            $templateContentNode->setAttribute('binary',1);
            $templateContentNode->setAttribute('encoding','base64');
            if ( array_key_exists('type',$_FILES[$tmpfile]) ) {
                $templateContentNode->setAttribute('mime-type',$_FILES[$tmpfile]['type']);
            }
            $templateContentNode->setValue(base64_encode($contents));
            if ( array_key_exists('name',$_FILES[$tmpfile]) 
                 && ($templateNameNode = $this->storage->traverse("template_upload/name",true,false)) instanceof I2CE_MagicDataNode
                ) {
                $templateNameNode->setValue($_FILES[$tmpfile]['type']);
            }
            if (($templateFileNode = $this->storage->traverse("template_file",false,false)) instanceof I2CE_MagicDataNode){
                $templateFileNode->erase();
            }

        } else  if (
            array_key_exists('do_select',$vals)
            && $vals['do_select']
            && array_key_exists('select',$vals)
            && is_scalar($vals['select'])
            && is_array( $options = $this->getODTTemplates())
            && in_array($vals['select'],$options)
            &&  ($templateFileNode = $this->storage->traverse("template_file",true,false)) instanceof I2CE_MagicDataNode
            ){
            $templateFileNode->setValue($vals['select']);           
            if (($templateNode = $this->storage->traverse("template_upload",false,false)) instanceof I2CE_MagicDataNode){
                $templateNode->erase();
            }

        }
        $relationships = I2CE::getConfig()->getKeys("/modules/CustomReports/relationships");
        if (array_key_exists('header_relationship',$vals)
            && is_scalar($vals['header_relationship'])
            && (in_array($vals['header_relationship'],$relationships) || $vals['header_relationship'] =='')
            ) {
            $this->setField('header_relationship',$vals['header_relationship']);
        }

        
        return true;
    }


    
    protected function getODTTemplates() {
        $file_search = I2CE::getFileSearch();
        $file_search->loadPaths('ODT_TEMPLATES');
        $files = $file_search->findByGlob('ODT_TEMPLATES',array('*ODT','*odt'),true);            
        $pathset = $file_search->getSearchPath('ODT_TEMPLATES');
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

    


    public function displayArgs($mainNode,$transient_options, $action) {		
	if (!parent::displayArgs($mainNode,$transient_options,$action)) {
	    return false;
	}

        $selected = array($this->getField('use_cache') ? 1: 0);
        $this->template->selectOptionsImmediate('use_cache',$selected,$mainNode);


        $selected = array($this->getField('segment_style'));
        $this->template->selectOptionsImmediate('segment_style',$selected,$mainNode);

        $selected = array(array_search($this->getField('segment_break'),self::$breaks));
        $this->template->selectOptionsImmediate('segment_break',$selected,$mainNode);

        $formats = I2CE::getConfig()->getAsArray("/modules/PrintedFormsODT/unoconv/conversions");
        $format = $this->getField('format');
        if (($formatNode = $this->template->getElementByName('format',0,$mainNode)) instanceof DOMNode) {
            foreach ($formats as $f=>$data) {
                if (!array_key_exists('ext',$data)
                    || !$data['ext']) {
                    continue;
                }
                $attrs = array('value'=>$f);
                if ($format == $f) {
                    $attrs['selected']  = 'selected';
                }
                $formatNode->appendChild($this->template->createElement('option',$attrs,$data['ext']));
            }
        }

        $export_option = '';
        if(I2CE_MagicDataNode::checkKey($format)) {            
            $this->storage->setIfIsSet($export_option,"export_options/$format");
        }
        $this->template->setDisplayDataImmediate('export_options',$export_option);

        $this->template->setDisplayDataImmediate('connection',$this->getField('connection'));
        $this->template->setDisplayDataImmediate('tmp_dir',$this->getField('tmp_dir'));
        


        
        $file = $this->getField('template_file');
        $content = '';
        $this->storage->setIfIsSet($content,'template_upload/content');
        
        $is_upload =  (is_string($content) && strlen($content) > 0); //upload takes precedence
        $is_file =  (is_string($file) && strlen($file) > 0 && !$is_upload);


        if ( ($selectNode = $this->template->getElementByName('select',0,$mainNode)) instanceof DOMElement) {
            $options = $this->getODTTemplates();
            $selected = false;
            if ($is_file) {
                if ( ($doSelectNode = $this->template->getElementByName('do_select',0,$mainNode)) instanceof DOMElement) {
                    $doSelectNode->setAttribute('checked','checked');
                }
                $selected = $file;
            }
            foreach ($options as $option) {
                $attrs = array('value'=>$option);
                if ($option == $selected) {
                    $attrs['selected'] = 'selected';
                }
                $selectNode->appendChild($this->template->createElement('option',$attrs,$option));
            }
            if ($file) {
                $this->template->setDisplayDataImmediate('has_template',1,$mainNode);
                $this->template->setDisplayDataImmediate('template_link','file/'. $file ,$mainNode);
            } else {
                $this->template->setDisplayDataImmediate('has_template',0,$mainNode);
            }
        }
        if ($is_upload) {
            if ( ($doSelectNode = $this->template->getElementByName('do_upload',0,$mainNode)) instanceof DOMElement) {
                $doSelectNode->setAttribute('checked','checked');
            }            
            $this->template->setDisplayDataImmediate('has_download',1,$mainNode);
            $this->template->setDisplayDataImmediate('download_link',"magicDataBrowser/download/" . $this->storage->getPath(false) . "/template_upload/content", $mainNode);
        } else {
            $this->template->setDisplayDataImmediate('has_download',0,$mainNode);
        }


        if ( ($relNode = $this->template->getElementByName('header_relationship',0,$mainNode)) instanceof DOMNode) {
            $relationships = I2CE::getConfig()->getKeys("/modules/CustomReports/relationships");
            $selected_rel = $this->getField('header_relationship');
            foreach ($relationships as $relationship) {
                $attrs = array('value'=>$relationship);
                if ($relationship == $selected_rel) {
                    $attrs['selected'] = 'selected';
                } 
                $title = false;
                if (I2CE::getConfig()->setIfIsSet($title,"/modules/CustomReports/relationships/$relationship/display_name")) {
                    $attrs['title'] = $title;
                }
                $relNode->appendChild($this->template->createElement('option',$attrs,$relationship));
            }
            if ($selected_rel) {
                $this->template->setDisplayDataImmediate('has_header_relationship',1,$mainNode);
                $this->template->setDisplayDataImmediate('header_relationship_link',"CustomReports/edit/relationships/" . $selected_rel, $mainNode);
            } else {
                $this->template->setDisplayDataImmediate('has_header_relationship',0,$mainNode);
            }
        }



        $this->renameInputs(array('use_cache','do_select','do_upload','select','upload','connection','tmp_dir','format','export_options',
                                  'segment_style','segment_break','header_relationship'),$mainNode);

        if ( ($formfieldsChild = $this->getChild('required',true)) instanceof I2CE_Swiss
	     && ( $formfieldsNode = $this->template->getElementById('fields',$mainNode)) instanceof DOMNode
	    ) {
            $formfieldsChild->addAjaxLink('fields_link','fields_contents', 'fields_ajax' ,$formfieldsNode,$action, $transient_options);
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
