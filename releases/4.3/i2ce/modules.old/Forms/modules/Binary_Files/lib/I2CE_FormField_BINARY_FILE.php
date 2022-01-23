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
    * @package I2CE
    * @author Carl Leitner <litlfred@ibiblio.org>
    * @since v2.0.0
    * @version v2.0.0
    */
/**
 * Class for defining the binary file field used by a {@link I2CE_Form} object.
 * @package I2CE
 * @access public
 */
abstract class I2CE_FormField_BINARY_FILE extends I2CE_FormField_STORE_BINARY_FILE {

    public function loadFromXML($node) {
        I2CE::raiseError("Loading from XML");
        if (!$node instanceof DOMElement
            || ! ($val_nodes = $node->getElementsByTagName('value')) instanceof DOMNodeList
            || ! ($val_nodes->length == 1)
            || ! ($val_node = $val_nodes->item(0)) instanceof DOMElement
            ) {
            return;
        }
        foreach (self::$keys as $key=>$var) {
            if ( ! ($val_node->hasAttribute($key))) {
                continue;
            }
            $this->$var = $val_node->getAttribute($key);
        }
        $this->value = base64_decode($val_node->textContent);

    }

    /**
     * Appends an XML representation of the field data onto the current node
     
     */
    protected function appendXMLRepresentation($field_node) {
        $doc = $field_node->ownerDocument;
        $field_node->appendChild($val_node = $doc->createElement('value',base64_encode($this->getValue())));
        foreach (self::$keys as $key=>$var) {
            $val_node->setAttribute($key, $this->$var);
        }
    }


    /**
     * The magically determined mime type
     * @var protected mixed $file_name.  Either false or a string
     */
    protected $mime_type = false;    


    /**
     * The number of null \0 at the end of the string (it seems MDB2 does truncates \0 when getting results, check to see if PDO needs this)
     * @var protected int $null_term
     */
    protected $null_term = 0;    

    /**
     * The file name as it was uploaded (may not be set)
     * @var protected mixed $file_name.  Either false or string
     */
    protected $file_name = false;    

    /**
     * The time the file  was uploaded (may not be set)
     * @var protected mixed $fmod_time.  Either false or integer(unix time stamp)
     */
    protected $fmod_time = false;    


    /**
     * Gets the binary data for the form field
     * @returns string
     */
    public function getBinaryData() {
        return $this->value;
    }

    /**
     * Create a new instance of a I2CE_FormField
     * @param string $name
     * @param array $options A list of options for this form field.
     */
    public function __construct( $name, $options=array() ) {
        parent::__construct($name, $options);
    }

    /**
     *  An array whose keys are the keys to the metadata for the file and values are the variables
     * they should be stored in .  All keys need to be a string of length 9
     * @protected static array of string $keys
     */
    protected static $keys = array('mime-type'=>'mime_type','file-name'=>'file_name' , 'fmod-time'=>'fmod_time' , 'null-term'=>'null_term');

    /**
     * Sets the value of this field from the database format.
     * @param mixed $value
     */
    public function setFromDB( $value ) {
        //keys should be fixed length of 9 so we can reduce the string processing.
        //format is mime-type<VAL1>file-name<val-2>some-keys<VAL3>datadatadatadata
        //keys don't need to be in a fixed order
        while ( strlen($value) >= 10 && $value[9] == '<' ) {
            $key = substr($value,0,9);
            if (!array_key_exists($key, self::$keys) || ($pos=strpos($value,'>')) === false) { 
                break;
            }
            $var = self::$keys[$key];
            $this->$var = substr($value,10,$pos - 10);
            $value = substr($value,$pos + 1); 
        }
        parent::setFromDB($value);
    }

        
    /**
     * Returns the value of this field.
     * @return mixed
     */
    public function getValue() {
        if ( $this->value == 'from_file' ) {
            return parent::getValue();
        } else {
            return $this->value . str_pad('',$this->null_term,"\0");
        }
    }



    /**
     * Return the meta data as a string for this file.
     * @return string;
     */
    public function getMetaValue() {
        $ret = '';
        foreach (self::$keys as $key=>$var) {
            if ($this->$var) {
                $ret .= $key . '<' . $this->$var . '>';
            }
        }
        return $ret;
    }


    /** 
     * Returns the value of this field ready to be stored in the database.
     * @return mixed
     */
    public function getDBValue() {
        $ret = $this->getMetaValue();
        return $ret . $this->value;
    }


    /**
     * Returns the value of this field as a human readable format.
     * @param I2CE_Entry $entry If an I2CE_Entry object is passed then it will return the value
     * for that entry assuming it's an entry for this field.
     * @return mixed
     */
    public function getDisplayValue( $entry = false,$style = 'default' ) {
        if( $entry instanceof I2CE_Entry ) {
            if (strlen( $entry->getValue()) == 0) {
                return '';
            }
            $size = (int) ($this->getContentLength() / 1024);
            $postfix = 'kb';
            if ($size > 1024) {
                $postfix = 'MB';
                $size = (int) ($size   / 1024);
            }
            //return $entry->getMimeType() . ' File Size: ' . $size . $postfix;
            return $this->file_name . ' : ' . $size . $postfix;
        } else {
            if (strlen( $this->value) == 0) {
                return '(No Data)';
            }
            $size = (int)($this->getContentLength() / 1024);
            $postfix = 'kb';
            if ($size > 1024) {
                $postfix = 'MB';
                $size = (int) ($size   / 1024);
            }
            //return $this->getMimeType() . ' File Size: ' . $size . $postfix;
            if ($this->file_name) {
                $ret = $this->file_name;
            } else {
                $ret = $this->name;
            }
            $ret .= ' : ' . $size . $postfix;
            if ($this->mime_type) {
                $ret .= ' (' . $this->mime_type .')';
            }
            return  $ret;
        }
    }

    /**
     * Gets the length of the conten
     * @returns int
     */
    public function getContentLength() {
        return parent::getContentLength() + ( (int) $this->null_term);
    }

    /**
     * Returns the last modification time of this field, or false if unknown
     * @param mixed.
     */
    public function getModTime() {
        return $this->fmod_time;
    }

    /**
     * Generate the link to the given binary field in the given form id
     * @param string $formid
     * @param string $field
     * @param string $key.  Defaults to null.  Otherwise it is a temp key
     * @returns string
     */
    public static function  getFieldLink($formid,$field, $key = null) {
        if ($key) {
            return  "BinField?formid=" . urlencode($formid) .  '&field=' . urlencode($field) . '&tmp_key=' . urlencode($key) ;
        }else {
            return  "BinField?formid=" . urlencode($formid) .  '&field=' . urlencode($field);
        }
    }

    /**
     *  Gets the link to this binary field.  It will fail if there is field is not attached to any form
     *  @return mixed string on success or null on failure.
     */
    protected function getLink() {
        if (!$this->container instanceof I2CE_Form) {
            return null;
        }
        return self::getFieldLink($this->container->getNameId(),$this->name, $this->tmp_key);
    }


    public function setFromData($data,$file_name,$mime_type=false, $fmod_time = false) {
        $content_length = strlen($data);
        $this->value = rtrim($data,"\0"); //MDB2 strips off terminating \0 when returning results, check if PDO needs this.
        $this->null_term = $content_length - strlen($this->value);
        $this->file_name = $file_name;
        if ($fmod_time == false) {
            $this->fmod_time = time();
        } else {
            $this->fmod_time = $fmod_time;
        }
        $this->mime_type = $mime_type;
        if (!$this->mime_type || $this->mime_type == 'application/unknown' || $this->mime_type == 'application/x-download') {
            $this->mime_type = I2CE_MimeTypes::magicMimeType($this->getValue());
        }
        if (!$this->isValidMimeType($this->mime_type)) {
            //let's try to get it from the extension of the uploaded file
            if ( ($pos = strrpos($file_name, '.')) !== false) {
                $ext = substr($file_name, $pos + 1);
                $this->mime_type = I2CE_MimeTypes::extToMimeType($ext);
                if (!$this->isValidMimeType($this->mime_type)) {
                    I2CE::raiseError("Unable to upload file $file_name, Invalid mime type from extension");
                    $this->file_name = false;
                    $this->fmod_time = false;
                    $this->mime_type = false;
                    $this->value = false;
                    $this->null_term = 0;
                    return;
                }
            } else {
                I2CE::raiseError("Unable to set file $file_name, Invalid mime type");
                $this->file_name = false;
                $this->fmod_time = false;
                $this->mime_type = false;
                $this->value = false;
                $this->null_term = 0;
                return;
            }
        }

    }


    protected function getTmpFileKeyValue($key) {
        $file_indices = explode("[", $this->getHTMLName());  //looks like array(forms,person_passport_photo],0],0],fields],image],file])
        foreach ($file_indices as &$i) {
            if (substr($i,-1) == ']') {
                $i = substr($i,0,-1);
            }
        }
        unset($i);
        reset($file_indices);
        $leading_index = array_shift($file_indices);
        if (!array_key_exists($leading_index,$_FILES) || !is_array($_FILES[$leading_index]) || !array_key_exists($key,$_FILES[$leading_index]) || !is_array($_FILES[$leading_index][$key])) {
            return false;
        }
        $vals = $_FILES[$leading_index][$key];
        foreach ($file_indices as $index) {
            if (!array_key_exists($index,$vals) || !is_array($vals[$index])) {
                return false;
            }
            $vals = $vals[$index];
        }
        if (!array_key_exists('file',$vals)) {
            return false;
        }
        return $vals['file'];
    }

    protected function hasTmpFileKey($key) {
        $file_indices = explode("[", $this->getHTMLName());  //looks like array(forms,person_passport_photo],0],0],fields],image],file])
        foreach ($file_indices as &$i) {
            if (substr($i,-1) == ']') {
                $i = substr($i,0,-1);
            }
        }
        unset($i);
        reset($file_indices);
        $leading_index = array_shift($file_indices);
        if (!array_key_exists($leading_index,$_FILES) || !is_array($_FILES[$leading_index]) || !array_key_exists($key,$_FILES[$leading_index]) || !is_array($_FILES[$leading_index][$key])) {
            return false;
        }
        $vals = $_FILES[$leading_index][$key];
        foreach ($file_indices as $index) {
            if (!array_key_exists($index,$vals) || !is_array($vals[$index])) {
                return false;
            }
            $vals = $vals[$index];
        }
        if (!array_key_exists('file',$vals)) {
            return false;
        }
        return true;
    }


    /** 
     * Sets the value of this field from the posted form.
     * @param mixed $post
     */
    public function setFromPost( $post ) {
        $html_name = $this->getHTMLName() ;  //looks like: forms[person_photo_passport][0][0][fields][image][file]:
        $file = $this->getTmpFileKeyValue('tmp_name');
        $error = $this->getTmpFileKeyValue('error') ;
        $size = $this->getTmpFileKeyValue('size');
        $name = $this->getTmpFileKeyValue('name');
        $type = $this->getTmpFileKeyValue('type');
        if ( $file === false || $error ===  UPLOAD_ERR_NO_FILE ){
            if (array_key_exists('tmp_key',$post)) {
                $this->setTempKey( $post['tmp_key']);
                //$this->setFromTemporaryTable();
                $this->setFromTemporaryLocation();
                return;
            } else {
                I2CE::raiseError("No temporary file for " . $this->getHTMLName() . " found in\n" . print_r($_FILES,true));
                return;
            }
        }

        //for error codes see:http://php.net/manual/en/features.file-upload.errors.php
        // if ($_FILES[$file_indx]['error'] != UPLOAD_ERR_OK) {
        //     I2CE::raiseError("Error uploading file: $file_indx:" . print_r($_FILES,true));
        //     return;
        // }
        if (!file_exists($file)) {
            //FLAG FOR TRANSLATION!
            $error_msgs = array( 
                UPLOAD_ERR_INI_SIZE => 'The file was too large.',
                UPLOAD_ERR_FORM_SIZE => 'The file was too large.',
                UPLOAD_ERR_PARTIAL => 'An error occurred uploading the file.',
                UPLOAD_ERR_NO_FILE => 'No file was chosen.',
                UPLOAD_ERR_NO_TMP_DIR => 'An error occurred uploading the file.',
                UPLOAD_ERR_CANT_WRITE => 'An error occurred uploading the file.',
                UPLOAD_ERR_EXTENSION => 'An error occurred uploading the file.',
                );

            I2CE::raiseError("Unable to upload file  " . $this->getTmpFileKeyValue('name') . ", File too large?\n: $error:" . $error_msgs[$error]);
            $this->setInvalidMessage('no_upload' , $error_msgs[$error] ); // this is a fuzzy method b/c it doesn't know  about forms.
            return;
        }

        if ( !$this->setFromPostUpload( $file, $size, $name, $type ) ) {
            return;
        }
        if (!$this->tmp_key) {
            $this->setTempKey(md5($this->name . $name . rand(0,100000)));
        }
        //$this->storeInTemporaryTable();                
        $this->storeInTemporaryLocation();
    } 



    /**
     * Return the display value of this form field as a DOM Node.
     * @param DOMNode $node  
     * @param I2CE_Template $template
     * @return DOMNode
     */
    public function getDisplayNode( $node, $template ) {
        if ($this->container instanceof I2CE_Form) {
            $link_node = $template->createElement(
                'a',
                array("href"=>$this->getLink()),
                $this->getDisplayValue());
            return $link_node;
        } else {
            return $template->createTextNode($this->getDisplayValue());
        }
    }


        
    /**
     * Checks to see if a mime type is a valid  mime type for this binary file
     * @param string $mime_type
     * @returns true if valid.  false otherwise
     */
    abstract public function isValidMimeType($mime_type);

    /**
     *  Check if the set value is too big
     */
    public function isTooBig() {
        $path = "meta/max_size_kb";
        if (!is_string($this->value) || !$this->optionsHasPath($path) || ! is_numeric( $max_size = $this->getOptionsByPath($path)) || $max_size <= 0) {
            return false;
        }
        return (strlen($this->value) > $max_size*1024); 
    }

    /**
     * Checks to see if the current value for this is set and valid.
     * @return boolean
     */
    public function isValid() {
        if (!$this->isValidMimeType($this->getMimeType()) || $this->isTooBig()) {
            $this->value = null;
            return false;
        }
        return true;
    }
        

    public function processDOMEditable($node,$template,$form_node) {
        $ele_name = $this->getHTMLName();
        $link = $this->getLink();
        if ($link && strlen( $this->value ) > 0 ) {
            $text = $this->getDisplayValue();
            $attrs = array("href"=>$link);
            $node->appendChild($template->createElement('a', $attrs,$text));
            $node->appendChild($template->createElement('br'));
        }
        $node->appendChild($template->createElement( "input", array( "name" => $ele_name . '[file]' , "type"=>"file", 'size' => 40)));
        //we need to add a hidden input  node so that $this->setFromPost() is triggeredd  from $form->setFromPost()
        $node->appendChild($template->createElement( "input", array( "name" => $ele_name . '[exists]' , "type"=>"hidden", 'value' => 1)));
        if ($this->tmp_key) {
            $node->appendChild($template->createElement( "input", array( "name" => $ele_name . '[tmp_key]' , "type"=>"hidden", 'value' => $this->tmp_key)));
        }
    }



    /**
     * @param DOMNode $node
     * @param I2CE_Template $template
     * @param DOMNode $form_node
     */
    public function processDOMNotEditable( $node, $template, $form_node) {
        $ele_name = $this->getHTMLName();
        $node->appendChild($this->getDisplayNode($node, $template ));
        if ($this->tmp_key) {
            $node->appendChild($template->createElement( "input", array( "name" => $ele_name . '[tmp_key]' , "type"=>"hidden", 'value' => $this->tmp_key)));
        }
    }



    /**
     *Get the file name associated to this binary field.  if none, it will generate one based on the form and field
     * @returns string
     */
    public function getFileName() {
        if ($this->file_name) {
            $filename  = $this->file_name;
        } else {        
            //no file name is stored, so base it on the form and field name
            if ($this->container instanceof I2CE_Form) {
                $filename = $this->container->getName() . '+' . $this->name;
            } else{
                $filename = $this->name;
            }        
            $filename = '.' . $this->getExtension();
        }
        return $filename;

    }
    /**
     *  Any headers that need to be sent when this field is dumped
     * @returns array of string
     */
    public function getHeaders() {
        $headers  = array();
        $headers[] = "Content-Type: " . $this->getMimeType();
        $filename = $this->getFileName();
        $headers[] = "Content-disposition: inline; filename=\"". $filename ."\"";
        return $headers;
    }

    /**
     *get the default extension for this 
     *@returns string
     */
    abstract protected function defaultExtension();


    /**
     *get the default extension for this 
     *@returns string
     */
    abstract protected function defaultMimeType();





    /**
     * Get the magically determined mime type
     * @returns string
     */
    public function getMimeType() {
        if ($this->mime_type === false) { //the mime type was not determined yet
            $this->mime_type = I2CE_MimeTypes::magicMimeType($this->value);            
        }
        if (!$this->mime_type) {
            $this->mime_type = $this->defaultMimeType();
        }
        return $this->mime_type;
    }




    /**
     * Get the extension associated with this binary file
     */
    public function getExtension() {
        $mime_type = $this->getMimeType();
        if (!$mime_type) {
            $mime_type = $this->defaultMimeType();
        }
        return I2CE_MimeTypes::mimeTypeToExt($mime_type);
    }

                



}

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
