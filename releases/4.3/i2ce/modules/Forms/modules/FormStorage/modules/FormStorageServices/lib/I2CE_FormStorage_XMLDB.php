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
* @subpackage FormStorage
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.2.0
* @since v4.2.0
* @filesource 
*/ 
/** 
* Class I2CE_FormStorage_XMLDB
* 
* @access public
*/


class I2CE_FormStorage_XMLDB extends I2CE_FormStorage_Service{


    /********   Main Form Mechanism Functions  ***********/
    public function isWritable() {
        return true; //needs an attached service however
    }


    public function populateRecordFromList($form) {
        if (!$form instanceof I2CE_Form) {
            I2CE::raiseError("Invalid Object");
            return false;
        }
        $form_name = $form->getName();
        $form_id = $form->getID();
	if (! ($this->init_data($form_name))) {
            return false;
        }
        if (! ($records_doc = $this->getAllRecordData($form_name)) instanceof DOMDocument) {
            $this->unset_last_cache();
            return false;
        }
        $xpath = new DOMXpath($records_doc);
        if (! ($results = $xpath->query("//form[@id='{$form_id}']")) instanceof DOMNodeList
            || ! $results->length == 1
            ){
            I2CE::raiseError("Didn't find {$form_id} in " . $records_doc->saveXML());
            $this->unset_last_cache();
            return false;
        }
        I2CE::raiseError("Loading from " . $records_doc->saveXML($results->item(0)));
        return $form->loadFromXML($results->item(0));
    }


    public function getAllRecordData($form,$mod_time = -1) {
	if (! ($this->init_data($form))) {
            return false;
        }
        if (!$this->has_service($form,'getAllRecordData')) {
            return false;
        }
        $doc = new DOMDocument();
        if (! ($doc->loadXML('<form/>'))) {
            I2CE::raiseError("Could not generate getAllRecordData message");
            return array();            
        }
        if ($mod_time >= 0) {
            $doc->documentElement->setAttribute('modified',date(DATE_ISO8601, $mod_time));
        }
        if ( ($out_message =   $this->process_transform($form, 'getAllRecordData','out',$doc,true)) === false) {
            I2CE::raiseError("Could not transform outgoing message");
        }
        $out_message = '';
        $in_message = $this->call_service($form,'getAllRecordData', $out_message);
        if (! ($trans_message =   $this->process_transform($form, 'getAllRecordData','in',$in_message,false)) instanceof DOMDocument) {
            I2CE::raiseError("Result was not a valid XML document");
            $this->unset_last_cache();
            return array();
        }        
        I2CE::raiseError("All Record Data:\n" .  $trans_message->saveXML());
        return $trans_message;        

    }

    public function populate($form) {
        $form_name = $form->getName();
	if (! ($this->init_data($form_name))) {
            return false;
        }
        if (!$this->has_service($form_name,'populate')) {
            //try to get all records and populate from there.
            if (!$this->populateRecordFromList($form)) {
                I2CE::raiseError("[" . $form->getNameID() . "] No valid service details defined for populate or getAllRecordData :" . print_r($this->services[$form_name],true));
                return false;
            } else {
                return true;
            }
        }


        $form_xml = $form->getXMLRepresentation()->ownerDocument;
        if (($out_message =   $this->process_transform($form_name, 'populate','out',$form_xml,true)) === false) {
            I2CE::raiseError("Could not transform out going message");
            return false;
        }
        $in_message = $this->call_service($form_name,'populate', $out_message);
        I2CE::raiseError("Populate received:\n" . $in_message);
        if (  ($trans_message =   $this->process_transform($form_name, 'populate','in',$in_message,false)) === false) {
            I2CE::raiseError("Could not transform incoming message");
            $this->unset_last_cache();
            return false;
        }
        I2CE::raiseError("Populate=" . $trans_message->saveXML());
        return $form->loadFromXML($trans_message);
    }

    /**
     * @param string $form.  the form name
     * @param array $sel_fields.  an array of field names you want the values for
     * @param mixed $id.  Defaults to null.  If non-null it is the id that we wish to limit to.
     * @param $id 
     * @param integer $mod_time. Defaults to -1.  
     *    If non-negative, we only list the requested fields for an 
     *    id if at least one of them has a modification
     *    time greater than or equal to $mod_time.  If the form storage has no way of tracking modifucation time, 
     *    all entries are listed.
     * @param boolean $parent. Defaults to false.  If true, we include the parent id as a referenced field.  
     * @param mixed $limit. Defaults to false.  It true, returns only one result.  If an integer it is the numeber of records to limit to.
     *     If it is as an array of two integers, it is the offset and then number of results to limit to.  
     *
     * @return mixed.  
     *    boolean: returns false if there is no ability to generate a SQL statement to use in a sub-select on.  
     *             the columns returned will be the field names
     *    string: a SQL statement to use in a sub-select on.
     *    
     */
    public function getSubSelectFieldsQuery($form,$sel_fields,$id = null,$mod_time = -1,$parent = false ,$limit = false  ) {
	if ($id == '0' ) {
            if ( ($records = $this->getSubSelectFieldsQuery_worker($form,$mod_time,$parent,$limit))  === false
                ){
                I2CE::raiseError("Could not get records for $form");
                return false;
            }
            return $records;
        } else {            
            return false; //just fall back to populate one by one for now.
        }
    }



    public function getSubSelectFieldsQuery_worker( $form, $mod_time = -1, $parent =false ,$limit = false) {
	if (! ($this->init_data($form))
            || ! ($formObj = I2CE_FormFactory::instance()->createContainer($form.'|0')) instanceof I2CE_Form                
            || !!$this->has_service($form,'subSelect')
            || ! is_array($args = $this->services[$form]['subSelect'])
            || ! array_key_exists('dataElement',$args)
            || ! (is_string($data_element = $args['dataElement'])) 
            || ! $data_element
            || !  is_string( $results = $this->services[$form]['getRecords']['results'])
            || ! ($doc = new DOMDocument()) instanceof DOMDocument
            || (! ($doc->loadXML('<form/>'))) 
            ) {
            I2CE::raiseError("No subSelect result set specified:" . print_r($this->services[$form],true));
            return false;
        }

        $doc = new DOMDocument();
        if (! ($doc->loadXML('<form/>'))) {
            I2CE::raiseError("Could not generate subSelect message");
            return false;
        }

        if (is_array($limit) && count($limit) == 2) {
            reset($limit);
            $doc->setAttribute('start' , current($limit));
            end($limit);
            $doc->setAttribute('max' , current($limit));
        } else  if ($limit === true ) {
            $doc->setAttribute('max' , 1);
        } else if  (is_integer($limit) ) {
            $doc->setAttribute('max' , $limit);
        }
        
        if ($mod_time >= 0) {
            $doc->documentElement->setAttribute('modified',date(DATE_ISO8601, $mod_time));
        }
        if ($parent) {
            list($parent_form,$parent_id) = array_pad(explode('|',$parent,2),2,'');
            $doc->documentElement->setAttribute('parent_form',$parent_form);
            $doc->documentElement->setAttribute('parent_id',$parent_id);
        }
        I2CE::raiseError("FORM XML:\n" . $doc->saveXML());
        if ( ($out_message =   $this->process_transform($form, 'subSelect','out',$doc,true)) === false) {
            I2CE::raiseError("Could not transform outgoing message");
        }
        $in_message = $this->call_service($form,'subSelect', $out_message);

        $subselects = array();
        $field_objs = array();

        $sel_fields = array('id','parent','created','last_modified');
        foreach ($formObj as $field => $fieldObj) {
            $sel_fields[] = $field;
        }
        $sel_fields  = array_unique($sel_fields);
        
        foreach ($sel_fields as $sel_field) {
            if (($field_obj = $formObj->getField($sel_field)) instanceof I2CE_FormField) {
                $field_objs[$sel_field] = $field_obj;
                $types[$sel_field] = $field_obj->getTypeString();
            } else {                       
                $field_objs[$sel_field] = false;
                $types[$sel_field] = false;
            }

        }

        //now stream $in_message

        $reader = new XMLReader;
        $reader->xml($in_message);


        while ($reader->read()) {
            if ( $reader->name != $data_element
                || $reader->nodeType != XMLReader::ELEMENT
                || ! ($data_xml = $reader->expand())
                || ! ($data_trans =   $this->process_transform($form, 'subSelect','in',$data_xml,false)) instanceof DOMDocument
                || ! (true || $formObj->reset())
                || ! ($formObj->loadFromXML($data_trans))
                ) {
                continue; //we need to advance cursor to find a beginning or end  <$data_element>
            }
            
            $vals = array();
            foreach ($sel_fields as $sel_field) {
                if ($sel_field == 'id') {
                    $val = $formObj->getId();
                    if (!$val) {
                        $val = ' "0" ';
                    } else {
                        $val = ' ' . I2CE::PDO()->quote($val) . ' ';
                    }
                } else if (! $field_objs[$sel_field]) {
                    $val = ' NULL ';
                } else {
                    $val = $field_objs[$sel_field]->getDBValue();
                    switch ($types[$sel_field]) {
                    case 'string':
                    case 'text':
                        $val = " " . I2CE::PDO()->quote($val) . " ";
                        break;
                    case 'date':
                        if ($val) {
                            $val = " CAST('" . $val . "' AS DATETIME) ";
                        } else {
                            $val = ' NULL ';
                        }
                        break;
                    case 'integer':
                    case 'float':
                    default:
                        //do nothing;
                    }
                }
                if (count($subselects) == 0) {
                    $val .= " AS `" . $sel_field . "` ";
                }
                $vals[] = $val;
            }
            $subselects[] = implode( ",\t",$vals);
        }
        if (count($subselects) > 0) {
            return "\nSELECT\n" . implode("\n UNION SELECT " , $subselects);
        } else {
            return false;
        }
        
    }



    /**
     * Return an array of all the record ids for a given form.
     * @param string $form
     * @param integer $mod_time.  Defaults to  -1.  If non-negative, it is a unix timestamp and we retrieve records modified at or after the given time
     * @param string $parent Defaults to false.  If true, The parent field we want to restrict values to
     * @return array
     */
    public function getRecords( $form, $mod_time = -1, $parent =false ,$limit = false) {
        return $this->getRecords_worker( $form, $mod_time , $parent );
    }


    public function getRecords_worker( $form, $mod_time = -1, $parent =false ,$limit = false) {
	if (! ($this->init_data($form))) {
            return false;
        }
        if ( !$this->has_service($form,'getRecords')
             || ! array_key_exists('results',$this->services[$form]['getRecords'])
             ||!  is_string( $results = $this->services[$form]['getRecords']['results'])
            ) {
            I2CE::raiseError("No result set specified:" . print_r($this->services[$form],true));
            return array();
        }

        $doc = new DOMDocument();
        if (! ($doc->loadXML('<form/>'))) {
            I2CE::raiseError("Could not generate getRecords message");
            return array();            
        }

        if (is_array($limit) && count($limit) == 2) {
            reset($limit);
            $doc->setAttribute('start' , current($limit));
            end($limit);
            $doc->setAttribute('max' , current($limit));
        } else  if ($limit === true ) {
            $doc->setAttribute('max' , 1);
        } else if  (is_integer($limit) ) {
            $doc->setAttribute('max' , $limit);
        }
        


        if ($mod_time >= 0) {
            $doc->documentElement->setAttribute('modified',date(DATE_ISO8601, $mod_time));
        }
        if ($parent) {
            list($parent_form,$parent_id) = array_pad(explode('|',$parent,2),2,'');
            $doc->documentElement->setAttribute('parent_form',$parent_form);
            $doc->documentElement->setAttribute('parent_id',$parent_id);
        }
        I2CE::raiseError("FORM XML:\n" . $doc->saveXML());
        if ( ($out_message =   $this->process_transform($form, 'getRecords','out',$doc,true)) === false) {
            I2CE::raiseError("Could not transform outgoing message");
        }
        $in_message = $this->call_service($form,'getRecords', $out_message);
        if (! ($trans_message =   $this->process_transform($form, 'getRecords','in',$in_message,false)) instanceof DOMDocument) {
            I2CE::raiseError("Result was not a valid XML document");
            $this->unset_last_cache();
            return array();
        }        
        $xpath = new DOMXpath($trans_message);
        foreach ($this->namespaces[$form] as $ns=>$uri) {
            $xpath->registerNamespace($ns,$uri);
        }
        $records = array();
        if (($val_nodes = $xpath->query($results)) instanceof DOMNodeList) {
            foreach ($val_nodes as $val_node) {
                if ($val_node instanceof DOMAttr) {
                    $records[] = $val_node->value;
                } else if ($val_node instanceof DOMNode) {
                    $records[] = $val_node->textContent;
                }
            }
        } else {
            I2CE::raiseError("Invalid results query from:" . $results . "\n" . $trans_message) ;
            $this->unset_last_cache();
            return false;
        }       
        if ($val_nodes->length == 0) {
            $this->unset_last_cache();
        }
        return $records;
    }




    /**
     * Save a form object into entry tables.
     * If this functio is over-written, it should include the fuzzy method call
     * foreach ($form as $field) {
     *      $field->save(true/false, $user)
     * }
     * @param I2CE_Form $form
     * @param I2CE_User $user
     * @param boolean $transact
     */
    public function save( $form, $user, $transact ) {
        if (!$this->isWritable() ) {
            return true;
        }                
        if (!$form instanceof I2CE_Form
            || ! (        $form_name = $form->getName())
            || ! ($this->init_data($form_name)) 
            ) {
            return false;
        }
        $form_xml = $form->getXMLRepresentation()->ownerDocument;
        $form_id = $form->getID();
        if ( $form_id != '0') {
            $endpoint = 'update';
        }  else {
            $endpoint = 'create';

        }
        I2CE::raiseError("Saving ($endpoint) on " . $form_xml->saveXML());
        if (!array_key_exists($endpoint,$this->services[$form_name])
            || !is_array($this->services[$form_name][$endpoint])
            ) {
            I2CE::raiseError("No service set specified for $endpoint of $form_name");
            return false;
        }
        if ( $form_id == '0'
             && (! array_key_exists('results',$this->services[$form_name]['create'])
                 || ! is_string( $results = $this->services[$form_name]['create']['results'])
                 || !$results)
            ) {
            I2CE::raiseError("No result set specified for create $form_name");
            return false;
        }

        if (($out_message =   $this->process_transform($form_name, $endpoint,'out',$form_xml,true)) === false) {
            I2CE::raiseError("Could not transform out going message");
            return false;
        }
        $in_message = $this->call_service($form_name,$endpoint, $out_message);
        if (  ($trans_message =   $this->process_transform($form_name, $endpoint,'in',$in_message,false)) === false) {
            I2CE::raiseError("Could not transform incoming message");
            return false;
        }
        if ($form_id == '0') {
            $xpath = new DOMXpath($trans_message);
            foreach ($this->namespaces[$form_name] as $ns=>$uri) {
                $xpath->registerNamespace($ns,$uri);
            }
            $xpath = new DOMXpath($trans_message);
            if (!($val_nodes = $xpath->query($results)) instanceof DOMNodeList
                || !($val_nodes->length ==1) ) {
                I2CE::raiseError("Invalid results query");
                return false;
            }
            $val_node = $val_nodes->item(0);
            if ($val_node instanceof DOMAttr) {
                $form_id = $val_node->value;
            } else if ($val_node instanceof DOMNode) {
                $form_id = $val_node->textContent;
            }       
            $form->setId($form_id);
        }
        $this->clear_service_cache($form_name);
        return true;
        
    }

    /**
     * Deletes a form from the entry tables.
     * @param I2CE_Form $form
     * @param boolean $transact: a flag to use transactions or not. default: true
     * @return boolean
     */
    public function delete( $form, $transact ) {
        I2CE::raiseError("Delete not implemented");
        $form_name = $form->getName();
	if (! ($this->init_data($form_name))) {
            return false;
        }
        $form_xml = $form->getXMLRepresentation()->ownerDocument;
        if (($out_message =   $this->process_transform($form_name, 'delete','out',$form_xml,true)) === false) {
            I2CE::raiseError("Could not transform out going message");
            return false;
        }
        $in_message = $this->call_service($form_name,'delete', $out_message);
        $this->clear_service_cache($form_name);
        return true;
    }



    /*****      Helper funcitons               ******/
    protected $procs = array();
    protected function process_transform($form,$endpoint,$transform,$message,$as_string ) {
        if (is_string($message)) {
            $document = new DOMDocument();
            $document->loadXML($message);
            $message = $document->documentElement;
        } else if ($message instanceof DOMDocument) {
            $document = $message;
            $message = $document->documentElement;
        } else if ($message instanceof DOMNode) {
            $document = new DOMDocument();
            $message = $document->importNode($message,true);
            $document->appendChild($message);
        } else {
            I2CE::raiseError("Invalid object sent to transformer" . gettype($message));
            return false;
        }
        if (!$document instanceof DOMDocument) {
            I2CE::RaiseError("Could not load message as XML Doc");
            return false;
        }
        if ( ($proc = $this->get_processor($form,$endpoint,$transform))) {
            if ( ($out =   @$proc->transformToXML($document)) === false) {
                I2CE::raiseError("Could not transform accoring to xsl");
                return false;
            }
            I2CE::raiseError("Transformed to:\n$out");
        
            if ($as_string) {
                return $out;
            } else {
                $out_doc = new DOMDocument();
                if (!$out 
                    || ! $out_doc->loadXML($out)) {
                    return false;
                }
                return $out_doc;
            }

        } else {
	    I2CE::raiseError("Not transform for $endpoint $transform");
            if ($as_string) {
                return $document->saveXML();
            } else {
                return $document;
            }
        }

    }

    protected function get_processor($form,$endpoint,$transform) {
        if (!array_key_exists($form,$this->procs)
            || !is_array($this->procs[$form])
            || ! array_key_exists($endpoint,$this->procs[$form])
            || ! is_array($this->procs[$form][$endpoint])
            || ! array_key_exists($transform,$this->procs[$form][$endpoint])
            ) {
            if ( array_key_exists($endpoint,$this->services[$form])
                 && is_array($this->services[$form][$endpoint])
                 && array_key_exists('transforms',$this->services[$form][$endpoint])
                 && is_array($this->services[$form][$endpoint]['transforms'])) {
                $transforms = $this->services[$form][$endpoint]['transforms'];
            }else {
                $transforms = array();
            }
            $error_on_src_file = true;
            if (array_key_exists($transform,$transforms)
                &&is_string( $transforms[$transform])
                ){
                $transform_src = $transforms[$transform];
            } else {
                $transform_src = '@' . $form . DIRECTORY_SEPARATOR . $endpoint . '_'. $transform . '.xsl';
                $error_on_src_file = false;
            }
	    I2CE::raiseError(print_r($transforms,true));
            if ($transform_src == '0' ) {
                //no outgoing message
                return '';
            }
            if ($transform_src[0]=='@') {
                //it's a file.  search for it.
                $file = substr($transform_src,1);
                if ( ! ($transform_file = I2CE::getFileSearch()->search( 'XSLTS', $file))
                     || !($transform_src = file_get_contents($transform_file))
                    ) {
                    if ($error_on_src_file) {
                        I2CE::raiseError("Invalid transform file at $file => {$transform_file}\n" . print_r(I2CE::getFileSearch()->getSearchPath('XSLTS'),true));
                        return false;
                    } else {
                        $transform_src = false;
                    }
                }
            }
            if (!$transform_src) {
                return false;
            }
            //try to do the transofmr
            if (!array_key_exists($form,$this->procs)) {
                $this->procs[$form] = array();
            }
            if (!array_key_exists($endpoint,$this->procs[$form])) {
                $this->procs[$form][$endpoint] = array();
            }
            
            $this->procs[$form][$endpoint][$transform] = ($proc = new XSLTProcessor());
            $xslDoc = new DOMDocument();
            if (! ($xslDoc->loadXML($transform_src))) {
                $errors = libxml_get_errors();
                $e = '';
                foreach ($errors as $error) {
                    $e .= display_xml_error($error, $xml);
                }
                I2CE::raiseError("Invalid XSLT document form $form/$endpoint/$transform:\n" . $e);
                $this->procs[$form][$endpoint][$transform] = false;
                return false;
            }            
            if (!  @$proc->importStylesheet($xslDoc)) {
                I2CE::raiseError("Could not import style sheet");
                $this->procs[$form][$endpoint][$transform] = false;
                return false;
            }
        } else {
            $proc = $this->procs[$form][$endpoint][$transform] ;
        }
        return $proc;
    }
        

    
    protected function call_endpoint($form,$endpoint,$data) {
	if ( ( $payload  = $this->process_transform($form,$endpoint,$data)) === false
	     || ($result = $this->call_service($form,$endpoint,$payload)) === false) {
            $this->unset_last_cache();
	    return false;
	}
	return $result;

    }

    /**
     *$var protected array $namespaces array of the name spaces used index by the form name
     */
    protected $namespaces = array();

    /**
     * Init form storage options
     * @param string $form 
     */
    protected function init_data( $form ) {
        if (in_array($form,$this->init_status)) {
            //already done.
            return true;
        }
        if (! parent::init_data($form)) {
            return false;
        }
        $options = $this->getStorageOptions($form);
        if ( !$options instanceof I2CE_MagicDataNode ) { 
            //this really shouldnt be happening at this point. just being careful
            I2CE::raiseError( "Invalid storage options for $form" );
            return false;
        }               
        $this->namespaces[$form] = array();
        $options->setIfIsSet($this->namespaces[$form], "namespaces",true);
        if (!is_array(        $this->namespaces[$form])) {
            $this->namespaces[$form] = array();
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
