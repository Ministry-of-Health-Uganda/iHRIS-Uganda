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


class I2CE_PageXMLRelationshipSingle extends I2CE_Page {



    
    public function loadRelationship() {
        $rel_base = '/modules/CustomReports/relationships';
        if (!array_key_exists('relationship',$this->args)
            || !is_scalar($this->args['relationship'])) {
            I2CE::raiseError("Invalid relationship");
            return false;
        }
        if (array_key_exists('relationship_base',$this->args)
            && is_scalar($this->args['relationship_base'])) {
            $rel_base = $this->args['relationship_base'];
        }
        $use_cache = true;
        if (array_key_exists('use_cache',$this->args)) {
            $use_cache = $this->args['use_cache'];
        }
        if ($this->request_exists('use_cache')) {
            $use_cache = $this->request('use_cache');
        }
        $use_cache &= I2CE_ModuleFactory::instance()->isEnabled('CachedForms');
        if ($use_cache) {
            $cache_callback =array('I2CE_CachedForm','getCachedTableName');
        } else {
            $cache_callback = null;
        }
        try {            
            $this->formRelationship = new I2CE_FormRelationship($this->args['relationship'],$rel_base,$cache_callback);
        } catch (Exception $e) {
            I2CE::raiseError("Could not create form relationship : " . $this->args['relationship']);
            return false;
        }
        if (!array_key_exists('use_display_fields',$this->args) 
            || (!$this->args['use_display_fields'])
            ){
            $this->formRelationship->useRawFields();
        }
        return true;
    }


    protected function getID() {
	if (!$this->request_exists('id')
	    ||! is_scalar($id = $this->request('id'))
	    ||! $id
	    ) {
            return false;
        }
        $form_name = $this->formRelationship->getForm('primary_form');
        list($form,$fid) = array_pad(explode('|',$id,2),2,'');
        if ($form == $form_name) {
            //it was sent as id=person|23
            return $fid;
        } else if ($fid =='') {
            //it was sent as id=23
            return $id;
        } else {
            return false;
        }
    }
    
    
    /**
     * Perform the main actions of the page.
     * @return boolean
     */
    protected function action() {
        if ( !parent::action() ) {
            I2CE::raiseError("Base action failed");
            return false;
        }
        if (!$this->loadRelationship()) {
            I2CE::raiseError("Could not load relationship");
            return false;
        }
        return $this->generate($this->getID());
    }

    protected function actionCommandLine($args,$request_remainder) { 
        return $this->action();
    }

    protected $root_doc = false;
    protected function get_root_doc() {
        if ($this->root_doc === false) {
            $this->root_doc = new DOMDocument();
            $this->root_doc->loadXML("<relationship/>");
        }
        while ($this->root_doc->documentElement->hasChildNodes()) {
            $this->root_doc->documentElement->removeChild($this->root_doc->documentElement->lastChild);
        }
        return $this->root_doc;
    }

    public function get_doc_for_id($id) {
        $form_name = $this->formRelationship->getForm('primary_form');
        $fields = $this->getFields();
        $doc = $this->get_root_doc();
        $node = $doc->documentElement;            
        $data = $this->formRelationship->getFormData($form_name,$id,$fields,array(),$node);      
        return $doc;
    }



    public function get_transform_file() {
        return $transform_file;
    }

    protected function get_transform_vars() {
        $transform_vars = array();
        if (array_key_exists('transform_vars',$this->args)
            &&is_array( $this->args['transform_vars'])
            ){
            $transform_vars = $this->args['transform_vars'];
        }
        return $transform_vars;
    }

    public function generate($id,$stream = true) {        
        I2CE::longExecution( array( "max_execution_time" => 1800 ) );
        if (! ($doc = $this->get_doc_for_id($id)) instanceof DOMDocument) {
            I2CE::raiseError("Could not get document for " . $this->formRelationship->getPrimaryForm() . "|$id");
            return false;
        }
        $contents= $doc->saveXML($doc->documentElement);
        $transform_src = false;
        $transform_file = false;
        $trans_is_temp = false;
        if ( !( $this->request_exists('transform') && $this->request('transform') == 0)) {
            //allow request varible to turn of transform to check underlying data source easily
            if (array_key_exists('transform',$this->args)
                &&is_string( $this->args['transform'])
                ){
                $transform_src = $this->args['transform'];
            }
            if (is_string($transform_src)
                && strlen($transform_src)) { 
                if ( $transform_src[0]=='@') {
                    //it's a file.  search for it.
                    $file = substr($transform_src,1);
                    if ( ! ($transform_file = I2CE::getFileSearch()->search( 'XSLTS', $file))) {
                        I2CE::raiseError("Invalid transform file at $file => {$transform_file}\n" . print_r(I2CE::getFileSearch()->getSearchPath('XSLTS'),true));
                        return false;
                    }
                } else if  (substr($transform_src,0,7) == 'file://') {
                    $transform_file = substr($transform_src,7);
                } else {
                    $trans_is_temp = true;
                    $transform_file = tempnam(sys_get_temp_dir(), 'XSL_REL_');
                    file_put_contents($transform_file,$transform_src);
                }
            } 
        }

        if ($stream) {
            if ( ($errors  = I2CE_Dumper::cleanlyEndOutputBuffers())) {
                I2CE::raiseError("Got errors:\n$errors");
            }
            header('Content-Type: text/xml');
            flush();
            if ($transform_file) {
                $temp_file = tempnam(sys_get_temp_dir(), 'XML_REL_SINGLE_');
                file_put_contents($temp_file,$contents);
                $cmd = $this->get_transform_cmd($contents,$temp_file,$transform_file);
                passthru($cmd);            
                unlink($temp_file);
                if ($trans_is_temp) {
                    unlink($transform_file);
                }

            } else   {
                echo $contents;
            }
            exit(0);
        } else {
            if ($transform_file) {
                $temp_file = tempnam(sys_get_temp_dir(), 'XML_REL_SINGLE_');
                file_put_contents($temp_file,$contents);
                $cmd = $this->get_transform_cmd($contents,$temp_file,$transform_file);
                $trans = shell_exec($cmd);            
                unlink($temp_file);
                if ($trans_is_temp) {
                    unlink($transform_file);
                }
                return $trans;
            } else   {
                return $contents;
            }
        }
    }

    protected function get_transform_cmd($contents,$temp_file,$transform_file) {
        $params = array();
        $transform_vars = $this->get_transform_vars();
        foreach ($transform_vars as $key=>$val) {
            $params[] = "--stringparam " . escapeshellarg($key) . " " . escapeshellarg($val)  ;
        }
        $cmd = "xsltproc " .   implode(" ", $params) . " " .  escapeshellarg($transform_file) . " " . escapeshellarg($temp_file);
        return $cmd;
    }


    protected $fields = null;
    protected function getFields() {
        if (!is_array($this->fields)) {
            if (array_key_exists('get_all_fields',$this->args) 
                && is_scalar($this->args['get_all_fields'])
                && ($this->args['get_all_fields'] == 1)
                ) {
                $formfields = array();
                $ff = I2CE_FormFactory::instance();
                foreach ($this->formRelationship->getFormNames() as $formName) {
                    if (! ($form = $this->formRelationship->getForm($formName))
                        || ! ($formObj= $ff->createContainer(array($form,0))) instanceof I2CE_Form
                        ) {
                        continue;
                    }
                    $formfields[$formName] = array();
                    foreach ($formObj->getFieldNames() as $fieldName) {
                        if (!($fieldObj = $formObj->getField($fieldName)) instanceof I2CE_FormField
                            || ! $fieldObj->isInDB()
                            ) {
                            continue;
                        }
                        $formfields[$formName][] = $fieldName;
                    }
                    $cachefields = array("created","last_modified","parent");
                    foreach ($cachefields as $cachedfieldName) {                
                        $formfields[$formName][] = $cachedfieldName;
                    }


                }
                $this->fields =  $formfields;

            } else 	if (array_key_exists('fields',$this->args)
                            && is_array($this->args['fields'])
                ) {
                $this->fields =  $this->args['fields'];
            } else {
                $this->fields =  array();
            }
        }
        return $this->fields;
    }




}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
