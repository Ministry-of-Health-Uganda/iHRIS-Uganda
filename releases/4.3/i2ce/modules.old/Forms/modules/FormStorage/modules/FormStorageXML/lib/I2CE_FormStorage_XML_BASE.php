<?php
/**
* © Copyright 2009 IntraHealth International, Inc.
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
* @subpackage forms
* @author Luke Duncan <lduncan@intrahealth.org>
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.0.0
* @since v4.0.0
* @filesource 
*/ 
/** 
* Class I2CE_FormStorage_XML
* Storage mechanism for reading XML code lists.
* 
* @access public
*/


abstract class I2CE_FormStorage_XML_BASE extends   I2CE_FormStorage_File_Base{

    /**
     * Get the xpath query for the data nodes relative to the  the containing data
     * @param string $form
     * @returns string
     */
    abstract protected function getDataNodesQuery($form);


    /**
     * Get the xpath query for the base node containing the data
     * @param string $form
     * @returns string
     */
    abstract protected function getBaseQuery($form);

    /**
     * Get the namespaces we should register with xpath
     * @param string $form
     * @returns array keys are namespace prefixes values are the URIS
     */
    protected function getNamespaces($form) {
        return array();
    }


    /**
     * @var protected array $dom_data.  An array indexed by form names of 
     * arrays.  array( 'doc' => DOMDocument, 
     *                 'xpath' => DOMXPath, 
     *                 'dataset' => DOMNode,
     *                 )
     */
    protected $dom_data = array(); 


    /**
     * Release any resourced held by this form storage mechanism for the indicated form
     * @param string $form
     */
    public function release($form) {
        if (!is_string($form)) {
            return;
        }
        if (array_key_exists($form,$this->id_def)) {
            unset($this->id_def[$form]);
        }        
        if (array_key_exists($form,$this->fieldData)) {
            unset($this->fieldData[$form]);
        }        
        if (array_key_exists($form,$this->dom_data)) {
            if (is_array($this->dom_data[$form])) {
                if ($this->dom_data[$form]['xpath'] instanceof DOMXPath) {
                    unset($this->dom_data[$form]['xpath']->document);
                }
                foreach (array('dataset','xpath','doc') as $key) {
                    if (array_key_exists($key,$this->dom_data[$form])) {
                        unset ($this->dom_data[$form][$key]);
                    }
                }
            }
            unset($this->dom_data[$form]);
        }        
        parent::release($form);

    }


    /**
     * Tries to get the DOM for the  file
     * @param string $form 
     * @return DOMDocument
     */
    protected function getDOMData( $form ) {
        if ( array_key_exists( $form, $this->dom_data ) 
                && is_array( $this->dom_data[$form] ) ) {
            return $this->dom_data[$form];
        }
        $file_loc =  $this->getFile($form);
        if (($file_data = $this->getFileData($form))=== false) {
            return false;
        }
        $doc = new DOMDocument();
        if ( !$doc->loadXML( $file_data ) ) {
            I2CE::raiseError( "Could not open $file_loc for reading." );
            return false;
        }
        $xpath = new DOMXPath( $doc );
        foreach ($this->getNamespaces($form) as $ns=>$uri) {
            $xpath->registerNameSpace($ns,$uri);
        }
        $entries = $xpath->query($this->getBaseQuery($form));
        if ( $entries->length != 1 ) {
            I2CE::raiseError( "Could not find " . $this->getBaseQuery($form) . " in $file_loc -- " . $entries->length );
            return false;
        }
        $this->dom_data[$form] = array( 
            'doc' => $doc, 
            'xpath' => $xpath,
            'dataset' => $entries->item(0) 
            
            );
        return $this->dom_data[$form];
    }


    

    /**********************************
     * 
     * Implement the abstract read methods
     *
     **********************************


   

    /**
     * Worker moethod to  the offset in the CSV file for the specified form id and store it into the {$locations} cache.
     * Reads through the file until the specified is found.
     * @param string $form
     */
    protected function ensureLocations($form) {
        if (array_key_exists($form,$this->locations)  && is_array($this->locations[$form])) {
            return ;
        }
        $this->locations[$form] = array();
        if ( ($dom_data = $this->getDOMData( $form )) == false) {
            return;
        }
        
        $entries = $dom_data['xpath']->query(             
            $this->getDataNodesQuery($form),
            $dom_data['dataset'] 
            );
        if (!$entries instanceof DOMNodeList) {
            return;
        }
        $count = 1;
        foreach( $entries as $entry ) {           
            if ( !($id = $this->getLocationId($form,$entry,$count)) ) {
                continue;
            }
            $count++;
            $this->locations[$form][$id] = $entry;
        }
    }


    protected function getNodeIdDescription($form) {
        $storageOptions = $this->getStorageOptions($form);
        if (!$storageOptions instanceof I2CE_MagicDataNode) {
            I2CE::raiseError("Bad storage options");
            return false;
        }
        $def = array(
            'attribute'=>false,
            'query'=>false,
            'eval'=>false);
        $storageOptions->setIfIsSet($def['query'],"id/query");
        $storageOptions->setIfIsSet($def['attribute'],"id/attribute");
        $storageOptions->setIfIsSet($def['prepended'],"id/form_prepended");        
        if ($def['prepended']) {
            $def['prependend'] = strlen($form) +  1;
        } else {
            $def['prependend'] = 0;
        }
        $storageOptions->setIfIsSet($def['eval'],"id/eval");        
        if ($def['eval']) {
            @eval('$def["eval"] = function($id) {' . $def['eval']  . " return $id;}");
        }        
        return $def;
    }
    


    /**
     * @var protected array $id_def array, indexed by form,with detisl details to pull out id from a node
     */
    protected $id_def= array();


    /**
     * Get the id associated to the given node
     * @param string $form
     * @param DOMNode $node
     * @param int count
     * @returns string.  '0' or null  on failure a string on success.
     */
    protected function getLocationId($form, $node, $count) {
        if ( ($dom_data = $this->getDOMData($form)) === false) {
            return '0'; //shouldn't happen
        }
        if(!array_key_exists($form,$this->id_def) ) {
            $this->id_def[$form] = $this->getNodeIdDescription($form);
        }
        if ( ($id_def = $this->id_def[$form]) === false) {
            return '0';
        }
        $id = '0';
        if (array_key_exists('query',$id_def)  
            && $id_def['query']
            && ($vals = $dom_data['xpath']->query($id_def['query'],$node)) instanceof DOMNodeList 
            && $vals->length == 1) {
            
            $id = $vals->item(0)->textContent;            
        }else if (array_key_exists('attribute',$id_def) && $id_def['attribute']) {
            if ($node->hasAttribute($id_def['attribute'])) {
                $id = $node->getAttribute($id_def['attribute']);
            }
        } else {
            $id =  $count;
            $id_def['prepended'] = 0;
        }            
        $id = substr((string)$id, $id_def['prepended']);
        if (is_callable($id_def['eval'])) {
            $id = $id_eval($id);
        }
        return $id;
    }




    /**
     * Get the value based on the  given field data
     * @param string $form
     * @param string $field
     * @param array $fieldData
     * @param array $dom_data
     * @param DOMNode $node
     * @returns mixed. null if no data was found.  otherwise it is a string
     */
    protected function getFieldValue($form,$field,$node) {
        if ( ($dom_data = $this->getDOMData($form)) === false) {
            return null;
        }
        $fieldData = $this->getFieldDataDesc($form, $field);        
        $dbval = null;
        if (array_key_exists('query',$fieldData) 
            && $fieldData['query']
            && ($vals = $dom_data['xpath']->query($fieldData['query'],$node)) instanceof DOMNodeList 
            && $vals->length == 1) {
            $dbval = $vals->item(0)->textContent;
        } else if (array_key_exists('attribute',$fieldData) && $fieldData['attribute'] && $node->hasAttribute($fieldData['attribute'])) {
            $dbval = $node->getAttribute($fieldData['attribute']);
        }        
        return $dbval;
    }
 
    /**
     * Process the given node for form data
     * @param string $form
     * @param DOMNode $node
     * @returns array indexed by field name (including 'parent') and values the DB value
     */
    protected function getFormData($form,$node) {
        $data = array();
        $fieldDatas = $this->getFieldDataDesc($form);
        foreach ($fieldDatas as $field => $fieldData) {
            if ( ($dbval = $this->getFieldValue($form,$field,$node)) === null) {
                continue;
            }
            if (array_key_exists('eval',$fieldData) && is_callable($callback = $fieldData['eval'])) {
                $dbval = $callback($dbval);
            }
            $data[$field] = $dbval;
        }
        return $data;
    }
    
    
    /**
     * @var protected array $fieldData An array of arrays, indexed by form name, containing the data defining the field
     */
    protected $fieldData = array();

    
    /**
     * Get the field data description for the given form.
     * @param string $form
     * @param string $field.  Defaults to null in which case we get all the fields for the given form
     * @returns array indexed by field name or an array of the field data.
     */
    protected function getFieldDataDesc($form,$field = null) {
        if (array_key_exists($form,$this->fieldData) && is_array($this->fieldData[$form])){
            if ($field === null) {
                return $this->fieldData[$form];
            } else if (array_key_exists($field,$this->fieldData[$form])) {
                return $this->fieldData[$form][$field];
            } else {
                return array();
            }
        }
        $this->fieldData[$form]= array();
        $options = $this->getStorageOptions($form);
        if ( !$options instanceof I2CE_MagicDataNode ) { 
            //this really shouldnt be happening at this point. just being careful
            I2CE::raiseError( "Invalid storage options for $form" );
            return array();
        }
        if  ($options->is_parent('parent') && $options->is_scalar('parent/attribute') && $options->parent->attribute) {
            $this->fieldData[$form]['parent'] = $options->getAsArray('parent');
        }
        if  ($options->is_parent('fields')) {
            foreach ($options->fields as $fld=>$fieldConfig  ) {
                if (!$fieldConfig instanceof I2CE_MagicDataNode) {
                    continue;
                }
            $this->fieldData[$form][$fld] = $fieldConfig->getAsArray();
            }
        }
        foreach ($this->fieldData[$form] as $fld => &$fieldData){
            if (!array_key_exists('eval',$fieldData) || !$fieldData['eval']) {
                $fieldData['eval'] = false;
                continue;
            } 
            $fieldData['eval'] = @eval('$fieldData["eval"]  = function($val) {' . $fieldData['eval']  . " return $val;}");
        }
        if ($field === null) {
            return $this->fieldData[$form];
        } else if (array_key_exists($field,$this->fieldData[$form])) {
            return $this->fieldData[$form][$field];
        } else {
            return array();
        }
    }



}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
