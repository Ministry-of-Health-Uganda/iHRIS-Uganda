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
 * @author Luke Duncan <lduncan@intrahealth.org>
 * @since v1.0.0
 * @version v2.0.0
 */

require_once("I2CE_Fuzzy.php");

if (!class_exists('I2CE_TemplateMeister',false)) {

/**
 * The I2CE_Template class that all display pages use.
 * 
 * @package I2CE
 */
    class I2CE_TemplateMeister extends I2CE_Fuzzy{
        /**
         * The top level DOM document that all methods work with.
         * @var DOMDocument
         */
        public $doc;
        /**
         * The XPath object used for search for DOM nodes in the document.
         * @var DOMXPath
         */
        protected $xpath;

        /**
         * The user who is accessing this template
         * @var protected I2CE_User $user
         */
        protected $user;

        /**
         * The working directory for the document.  Used for validation and document creation.
         * @var protected string $working_dir
         */
        protected $working_dir;
        /**
         * Set the user for this template
         * @param I2CE_User $user
         */
        public function setUser($user = false) {
            $this->user = $user;
        }
       
        /**
         * Get the user for this template
         * @returns I2CE_User $user or false if non has been set
         */
        public function getUser() {
            return $this->user;
        }



        /**
         * This method finds the location of a template file.If the file is not an absolute file path, it searches the class path 'XML'
         * 
         * This method searches the template directory path from the global configuration array
         * for the given template.  If it exists it returns the full path to the file and if not
         * it returns false.  It seaches the path backwards so that later directories
         * can override package versions of files.
         * @param string $template The name of the template file.
         * @param boolean $raise_error Defaults to true.  Raise error if template is not found
         * @return mixed
         */
        public function findTemplate( $template , $raise_error = true ) {      
            if (I2CE_FileSearch::isAbsolut($template)) {
                return $template;
            }
            $template_file = I2CE::getFileSearch()->search( 'XML', $template );
            if ( $template_file ) {
                return $template_file;
            } else {
                if ($raise_error) {
                    $this->raiseError( "Couldn't find template file: $template.\nSearch Path is:\n" 
                                       . print_r(I2CE::getFileSearch()->getSearchPath('TEMPLATES'), true), E_USER_NOTICE );
                }
                return false;
            }
        }

        /** 
         * I2CE_Template constructor method.
         * 
         * This constructor sets up the basic variables for all I2CE_Template objects.
         * 
         * 
         */
        public function __construct() {
            $this->loadOptions = LIBXML_DTDVALID |LIBXML_DTDATTR|LIBXML_DTDLOAD;
            $this->verboseErrors = false;
            $this->user = false;
            $this->headers = array('Content-type: text/xml; charset=utf-8');                
            $this->working_dir = null;
        }


        /**
         * Set the working directory for the document.  Used for validation and document creation.
         * @param string $working_dir
         */
        public function setWorkingDir($working_dir) {
            $this->working_dir  = $working_dir;
        }

        /**
         * Creates a new document and loads the root file used for this template.
         * @param string $root_file
         * @returns boolean false on failure
         */
        public function loadRootFile($root_file) {
            $file = $this->findTemplate($root_file);
            if (!$file ) {
                $this->raiseError("Could not find template file $root_file:\n" );
                return false;
            }
            $contents = file_get_contents($file);
            if ($contents === false) {
                $this->raiseError("Could not get contents of  file $root_file at $file" );
                return false;
            }
            $this->doc = new DOMDocument();
            $this->doc->documentURI = rawurlencode($file);

            if (!$this->loadRootText($contents)) {
                $this->raiseError("Could not import contents of $root_file at $file");
                return false;
            }
            return true; 
        }

        /**
         * Process any arguments sent to the page
         * @returns boolean true on sucess. false on failure
         */
        public function processArgs($args) {
            return true;
        }

        /**
         * Creates a new document and loads the input text
         * @param string $text;
         * @returns boolean. false on failure
         */
        public function loadRootText($text ) {
            if ($this->working_dir !== null){ 
                $dir = getcwd();
                chdir($this->working_dir);
            }
            if (!$this->doc instanceof DOMDocument) {                
                $this->doc = new DOMDocument();
            }
            $this->formatOutput = true;
            $this->resolveExternals = true;
            if ($text) {
                if (!$this->_loadText($this->doc, $text,false)) {
                    $this->raiseError("Could not load root text"); 
                    return false;
                }
            }
            $this->xpath = new DOMXPath( $this->doc );
            if ($this->working_dir !== null) {
                chdir($dir);
            }
            return true;
        }


        /**
         * the load options used when loading XML templates
         * @var protected integer $loadOptions
         */ 
        protected $loadOptions;

        /**
         * Set the load options used when loading XML templates.  If not called, we use the 
         * load options = LIBXML_DTDVALID &  & LIBXML_DTDATTR
         * @param integer options.
         */
        public function setLoadOptions($options) {
            $this->loadOptions = $options;
        }

        /**
         * Add a header for this tempalte.  If not set we user 'Content-type: text/xml; charset=utf-8'
         * @param string $header
         */
        public function addHeader($header) {
            $this->headers[] = $header;
        }
        /**
         * Clear all headers currently set for this template.
         */
        public function clearHeaders() {
            $this->headers = array();
        }

        /**
         * Get the headers for this page.  
         * @returns array of string
         */
        public function getHeaders() {
            return $this->headers;
        }
        /**
         * @var protected array $header of string.  The header strings.
         */
        protected $headers;



        /**
         *  Called to prepare the display. 
         */
        public function prepareDisplay() {
            //we do nothing
        }

        /**
         * Returns the displayed page as a (tidy!) string
         * @param boolean $decode_entities Set if you want to decode HTML
         *                                 entities back to the original
         *                                 ignoring HTML specialchars
         * @param int $decode_flags default is ENT_NOQUOTES
         * @returns string
         */
        public function getDisplay( $decode_entities=false, 
                $decode_flags = ENT_NOQUOTES ) {
            $out = $this->doc->saveXML();
            if (function_exists('tidy_get_output')) {
                $tidy = new tidy();
                $config = array(
                    'input-xml'=>true,
                    'output-xml'=>true,
                    'indent'=>true,
                    'wrap'=>0,
                    );
                $tidy->isXML();
                $tidy->parseString($out,$config);
                $tidy->cleanRepair();
                $out = tidy_get_output($tidy);
            }
            if ( $decode_entities ) {
                foreach( get_html_translation_table( HTML_SPECIALCHARS, $decode_flags ) as $code ) {
                    $out = str_replace( $code, htmlspecialchars( $code ), $out );
                }
                return html_entity_decode($out, $decode_flags ); 
            } else {
                return   $out;
            }
        }

        /**
         * Searches the DOM XPath for the given id.
         * 
         * returns the first match found.
         * @param string $id
         * @param $node if non null gets the element relative to that node.  Defaults to null
         * @return DOMNode
         */
        public function getElementById( $id , $node =null) {
            if ($id instanceof DOMNode) {
                return $id;
            }
            if ($node instanceof DOMNode) {
                $result = $this->query( "(./descendant-or-self::*[@id='$id'])[1]",$node );
            } else {
                $result = $this->query( "(//*[@id='$id'])[1]" );
            }
            if ($result->length == 0) {
                return null;
            }  else {
                return $result->item(0);
            }
        }
        /**
         * Searches the DOM XPath for the given name attribute.
         * 
         * This searches the document for the given name attribute.  It returns
         * whatever occurrence is requested.  
         * If $occurrence is negative then it will return the occurence offset from the end.
         * Example: -1 returns the last occurence.  If $occurence is null it returns a DOMNodeList
         * @param string $name
         * @param integer $occurrence
         * @param $node if non null gets the element relative to that node.  Defaults to null
         * @return DOMNode
         */
        public function getElementByName( $name, $occurrence,$node=null ) {
            if ($occurrence >= 0) {
                $occurrence++; //indexing starts at 1 according to W3C
                $occurrence = '(position() = ' . $occurrence .')' ;
            } else {
                if ($occurrence === -1) {
                    $occurrence = '(position() = last())';
                } else {
                    $occurrence++;
                    $occurrence = '(position() = last()' . $occurrence . ')';
                }
            }
            if ($node instanceof DOMNode) {
                $qry =  "(./descendant-or-self::*[@name='$name'])[$occurrence]" ;
            } else {
                $qry =  "(//*[@name='$name'])[$occurrence ]" ;
            }
            $result = $this->query($qry,$node);
            if ($result instanceof DOMNodeList && $result->length == 1) {
                return $result->item(0);
            }
        }


        /**
         * Searches the DOM XPath for the given tag name 
         * 
         * This searches the document for the given tagname .  It returns
         * whatever occurrence is requested.  If $occurrence is negative then it will return the occurence offset from the end.
         * Example: -1 returns the last occurence.  If $occurence is null it returns a DOMNodeList
         * @param string $tagname
         * @param integer $occurrence
         * @param $node if non null gets the element relative to that node.  Defaults to null
         * @return DOMNode
         */
        public function getElementByTagName( $tagname, $occurrence,$node=null ) {
            if ($node instanceof DOMNode) {
                $result = $this->query("./descendant-or-self::$tagname",$node);
            } else {
                $result = $this->doc->getElementsByTagName( $tagname); 
            }
            if ( $result->length == 0 ) {
                return null; 
            }
            if ($occurrence === null) {
                return $result;
            } 
            if ( $occurrence < 0 ) {
                $occurrence = $result->length + $occurrence;
            }
            return $result->item( $occurrence );
        }



        /**
         * Get the DOM for the template
         * @returns DOMDocument
         */
        public function getDoc() {
            return $this->doc;
        }

    
        /**
         * Create an element to be added to this page.
         * 
         * Create a new element in the document with the given attributes and return the node to be
         * added where needed.
         * @param string $element_name
         * @param array $attributes
         */
        public function createElement( $element_name, $attributes = array(), $value = "" ) {
            if ( $value != "" ) {
                $element = $this->doc->createElement( $element_name );
                $element->appendChild( $this->createTextNode( $value ) );
            } else {
                $element = $this->doc->createElement( $element_name );              
            }
            foreach( $attributes as $attr => $attr_value ) {
                $element->setAttribute( $attr, $attr_value );
            }
            return $element;
        }
    
        

  

        /**
         * Sets an attribute on the given node ID and xpath.
         * 
         * Finds a given node by ID and then searches the given xpath and then 
         * adds or replaces the given attribute.
         * @param string $attr
         * @param string $value
         * @param string $id.  If non-null we perform our xpath query relative to the node with this id
         * @param string $xpath.  Defaults to null, in which case we set the attribute of the node by id.
         * If non-null, we change the attribute of every node found by the xpath query which is searched relative to the the node with the given id. 
         * @param $node Defaults to null. if non null gets the element by id relative to that node if id is not null.  If id is null, tand xpath is not null, then we get query with respect to that node
         */
        public function setAttribute( $attr, $value, $id, $xpath=null,$node = null ) {
            $n= null;
            if ($id !== null) {
                $n = $this->getElementById( $id,$node );
                if (!$n instanceof DOMNode) {
                    return;
                }
            } else {
                $n = $node;
            }
            if ($xpath === null && $n instanceof DOMNode) {
                $n->setAttribute( $attr, $value );
            } else{
                $result = $this->query( $xpath, $n );
                for( $i = 0; $i < $result->length; $i ++ ) {
                    $result->item($i)->setAttribute( $attr, $value );
                }
            }
        }
        /**
         * Sets an attribute on the given node.
         * @param string $attr
         * @param string $value
         * @param DOMNode $node
         */
        public function setNodeAttribute( $attr, $value, $node ) {
            $node->setAttribute( $attr, $value );
        }





        /**
         * Replace a node based on the id attribute.
         * 
         * Finds a node with the same id attribute as the one in the
         * node passed to this method and replaces it.
         * @param DOMNode $node
         * @param DOMNode $relative_node if non null gets the element relative to that node.  Defaults to null
         */
        public function replaceNode( $node, $relative_node = null ) {
            $div = $node->getAttribute( "id" );
            $content = $this->getElementById( $div , $relative_node);
            if ( $content instanceof DOMNode ) {
                return $content->parentNode->replaceChild( $node, $content );
            } else {
                $this->raiseError( "Error trying to replace $div.  No such id found in DOM." );
            }
        }
        /**
         * Append a node to the node with the given id attribute.
         * @param DOMNode $node
         * @param string $id
         * @param boolean $before If set then the node will be appended as the first child instead of the last.
         * @param DOMNode $relative_node if non null gets the element relative to that node.  Defaults to null
         */
        public function appendNodeById( $node, $id, $before=false,$relative_node=null ) {
            $parent = $this->getElementById( $id ,$relative_node);
            if ($parent instanceof DOMNode) {
                return $this->appendNode( $node, $parent, $before );
            }
        }
        /**
         * Append a node to the give occurrence of the node with the given name attribute.
         * @param DOMNode $node
         * @param string $name
         * @param integer $occurrence
         * @param $relative_node if non null gets the element relative to that node.  Defaults to null
         * @param boolean $before If set then the node will be appended as the first child instead of the last.
         */
        public function appendNodeByName( $node, $name, $occurrence,$relative_node = null,$before=false ) {
            $parent = $this->getElementByName( $name, $occurrence,$relative_node );
            if ($parent instanceof DOMNode) {
                return $this->appendNode( $node, $parent, $before );
            }
        }

        /**
         * Append a node to the give occurrence of the node with the given name attribute.
         * @param mixed $nodes  array of DOMNode or a DOMNodeList
         * @param string $name
         * @param integer $occurrence -- which occurence of $name to use.
         * If $occurrence is negative then it will return the occurence offset from the end.
         * Example: -1 returns the last occurence.  
         * @param boolean $before If set then the node will be appended as the first child instead of the last.
         * @param $node if non null gets the element relative to that node.  Defaults to null
         */
        public function appendNodesByName( $nodes, $name, $occurrence,$before=false,$node=null ) {
            $parent = $this->getElementByName( $name, $occurrence,$before=false, $node );
            if (is_array($nodes)) {
                if ($before) {
                    $keys = array_keys($nodes);
                    $num_keys = count($keys);
                    for ($i=$num_keys -1; $i >=0; $i--) {
                        $this->appendNode( $nodes[$keys[$i]], $parent,true );
                    }
                } else {
                    foreach ($nodes as $n) {
                        $this->appendNode( $n, $parent );
                    }
                }
            } elseif ($nodes instanceof DOMNodeList) {
                if ($before) {
                    for ($i=$node->length-1; $i >= 0; $i--) {
                        $this->appendNode($nodes->item($i),$parent,true);
                    }
                } else {
                    for ($i=0; $i < $nodes->length; $i++) {
                        $this->appendNode($nodes->item($i),$parent);
                    }
                }
            }
        }

        /**
         * Append a node to the given parent node if it exists.
         * @param DOMNode $node
         * @param DOMNode $parent
         * @param boolean $before If set then the node will be appended as the first child instead of the last.
         */
        public function appendNode( $node, $parent, $before=false ) {
            if ( $node && $node instanceof DOMNode && $parent && $parent instanceof DOMNode ) {
                if ( $before ) {
                    return $parent->insertBefore( $node, $parent->firstChild );
                }else{
                    return $parent->appendChild( $node );
                }
            } else {
                $this->raiseError( "Invalid node or parent to append to for appendNode." );
            }
        }
        /**
         * Remove a node with the given id attribute.
         * @param string $id
         * @param $node if non null gets the element relative to that node.  Defaults to null
         */
        public function removeNodeById( $id,$node=null ) {
            $n = $this->getElementById( $id,$node );
            return $this->removeNode( $n );
        }
        /**
         * Remove the given node from the document.
         * @param DOMNode $node
         */
        public function removeNode( $node ) {
            if ( $node instanceof DOMNode && $node->parentNode instanceof DOMNode) {
                return $node->parentNode->removeChild( $node );
            } else {
                $this->raiseError( "Invalid node passed to removeNode." );
            }
        }
        /**
         * Find and remove nodes.
         * 
         * Search the document xpath for any matching nodes and remove them from the document.
         * @param string $xpath
         * @param $node if non null gets the element relative to that node.  Defaults to null
         */
        public function findAndRemoveNodes( $xpath,$node = null ) {
            if ($node instanceof DOMNode) {
                $results = $this->query( $xpath, $node );
            } else {
                $results = $this->query( $xpath );
            }
            if (!$results instanceof DOMNodeList) {
                return;
            }
            for( $i = 0; $i < $results->length; $i++ ) {
                $this->removeNode( $results->item($i) );
            }
        
        }
    

        /**
         * Helper method.  Load a file into the spectified document as XML
         * @param DOMDocument $doc
         * @param string $contentfile the file to load
         * @returns boolean  False on failure, 
         */
        protected function _loadFile($doc,$contentfile) {
            $file = $this->findTemplate( $contentfile );
            if ( !$file || $file == "" ) {
                $this->raiseError( "Unable to find template file: $contentfile.", E_USER_WARNING );
                return false;
            }
            $contents = file_get_contents($file);
            if ($contents === false) {
                $this->raiseError("Cannot read contents of  file $file");
                return false;
            }
            return $this->_loadText($doc, $contents);
            //it needs to be done this way (via file_get_contents)  otherwise we will have problems when validating in trying to find a system DTD               
        }
    

        /**
         * Helper method.  Load text into the spectified document as XML
         * @param DOMDocument $doc
         * @param string $contentfile the file to load
         * @param string $setEncoding Defaults to true.  If true, set the encoding to be that of the docuemnt.  Only useful as false for loading root template files
         * @returns boolean  False on failure, 
         */
        protected function _loadText($doc,$text,$setEncoding = true) {
            libxml_use_internal_errors(true);
            libxml_clear_errors();
            if ($setEncoding && $this->doc instanceof DOMDocument && $this->doc->encoding ) {
                $text =  "<?xml version='1.0' encoding='{$this->doc->encoding}'?>" . $text;
            }
            $doc_uri = $doc->documentURI;
            if (!$doc->loadXML($text,$this->loadOptions)) {
                $this->raiseError($this->xmlError(libxml_get_errors(), "Could not load XML"));
                libxml_clear_errors();
                libxml_use_internal_errors(false);
                return false;
            }        
            $doc->documentURI = $doc_uri;
            libxml_clear_errors();
            $includes = $doc->xinclude();
            $errors = libxml_get_errors();
            while ( count($errors) > 0 || $includes > 0) {
                /* doing this in a while loop because of: http://us.php.net/manual/en/function.domdocument-xinclude.php#44954 */
                if (count($errors) > 0) {
                    $this->raiseError($this->xmlError($errors, "Could not load XML's xinclude with document URI:\n" . $doc->documentURI ));
                    libxml_clear_errors();
                    libxml_use_internal_errors(false);
                    return false;               
                }
                libxml_clear_errors();
                $includes = $doc->xinclude();
                $errors = libxml_get_errors();
            }
            libxml_use_internal_errors(false);
            return true;
        }
    
        
    

        /**
         * Load a template file into the current document.
         * 
         * This will load the given template file into the current document.  It will only load
         * based on the surrounding tag name given.  It doesn't place the given node into the document
         * yet, just makes it available for use.
         * 
         * @param string $contentfile
         * @param string $tag.  If non-null it is the tagname of a toplevel node we wish to import. if null, we import all nodes.
         * @return mixed DOMNode -- the node just created if $tag was non-nill.  array if $tag was null consisting of the imported nodes.
         */
        public function loadFile( $contentfile, $tag="div" ) {
            $newdoc = new DOMDocument();
            if (!$this->_loadFile($newdoc,$contentfile)) {
                I2CE::raiseError("Bad call to _loadFile for $contentfile");
                return null;
            }
            $xpath = new DOMXPath( $newdoc );
            if ($tag === null) {
                $qry = '/*'; //just get the top level nodes
                $single = false;

            } else if ( strlen(ltrim(strtolower($tag),'qwertyuiopasdfghjklzxcvbnm0123456789_')) == 0) {
                $qry = "//"  . $tag. "[1]";
                $single = true;
            } else {
                $qry = $tag;
                $single  = false;
            }
            $nodeList = $xpath->query($qry);

            if ($single ) {
                if ($nodeList instanceof DOMNodeList && $nodeList->length > 0) {
                    return $this->doc->importNode($nodeList->item(0),true);
                } else {
                    return null;
                }
            } else {
                $ret = array();
                if ($nodeList instanceof DOMNodeList) {
                    if ($nodeList->length == 1) {
                        return $this->doc->importNode($nodeList->item(0),true);
                    } else {
                        foreach ($nodeList as $node) {
                            $ret[] = $this->doc->importNode($node,true);
                        }
                    }
                }
                return $ret;
            }
        }
        /**
         * Load and add the  template file to the document
         * @param string $contentfile
         * @param string $tag
         * @param DOMNode $relative_node if non null gets the element relative to that node.  Defaults to null
         * @return DOMNode -- the node just created
         * @see loadFile
         * @see replaceNode
         */
        public function addFile( $contentfile, $tag="div", $relative_node  = null ) {
            $imported = $this->loadFile( $contentfile, $tag );
            if ($imported instanceof DOMNode) {
                $this->replaceNode( $imported, $relative_node );
                return $imported;
            } else {
                return null;
            }
        }
        /**
         * This will load the given template and append it to the node with the given id attribute.
         * @param string $contentfile
         * @param string $tag
         * @param string $parent_id
         * @param boolean $before If set the file will be appended as the first child instead of the last.
         * @param $node if non null gets the element relative to that node.  Defaults to null
         * @see loadFile
         * @see appendNodeById
         * @return DOMNode
         */
        public function appendFileById( $contentfile, $tag, $parent_id, $before=false, $node=null ) {
            $imported = $this->loadFile( $contentfile, $tag );
            return  $this->appendNodeById( $imported, $parent_id, $before, $node );
        }
        /**
         * This will load the given  template and append it to the node with the given name attribute
         * at the given occurrence.
         * @param string $contentfile
         * @param string $tag
         * @param string $parent_name
         * @param integer $occurrence
         * @param $node if non null gets the element relative to that node.  Defaults to null
         * @param boolean $before If set then the node will be appended as the first child instead of the last.
         * @return DOMNode
         * @see loadFile
         * @see appendNodeByName
         */
        public function appendFileByName( $contentfile, $tag, $parent_name, $occurrence = 0,$node = null,$before=false ) {
            $imported = $this->loadFile( $contentfile, $tag );
            return $this->appendNodeByName( $imported, $parent_name, $occurrence,$node,$before );
        }
        /**
         * This will load the given  Template and append it to the given node.
         * @param string $contentfile
         * @param string $tag.  The tagname we of the top-level imported node we wish to append.  if null, we import all nodes
         * @param DOMNode $parent
         * @return DOMNode if $tag is non-null, then it is the appended node, otherwise it is the parent node
         */
        public function appendFileByNode( $contentfile, $tag, $parent ) {
            $imported = $this->loadFile( $contentfile, $tag );            
            if ($tag === null) {            
                foreach ($imported as $child) {
                    $this->appendNode( $child, $parent );
                }
                return $parent;
            } else {
                if (!$imported instanceof DOMNode) {
                    return null;
                }
                return $this->appendNode( $imported, $parent );
            }
        }


    
        /**
         * Create and append an element to the given node by id.
         * @param DOMNode $parent
         * @param string $tag
         * @param array $attr
         * @param string $value
         * @param $node if non null gets the element relative to that node.  Defaults to null
         * @return DOMNode
         */
        public function appendElementById( $parent_id, $tag, $attr=array(), $value="" ,$node=null) {
            $n = $this->createElement( $tag, $attr, $value );
            return $this->appendNodeById( $n, $parent_id,$node );
        }
        /**
         * Create and append an element to the given node by id.
         * @param DOMNode $parent
         * @param string $tag
         * @param array $attr
         * @param string $value
         * @param boolean $before defaults to false
         * @return DOMNode
         */
        public function appendElementByNode( $parent, $tag, $attr=array(), $value="",$before = false ) {
            $n = $this->createElement( $tag, $attr, $value );
            return $this->appendNode( $n, $parent,$before );
        }


        /**
         * Add a string of text to the node with the given id attribute.
         * @param mixed $id string an id, or a DOMNode
         * @param string $text
         * @param $node if non null gets the element relative to that node.  Defaults to null
         * @return DOMNode -- the node just created
         */
        public function addTextNode( $id, $text,$node = null ) {
            $content = $this->getElementById( $id ,$node);
            if ( !$content instanceof DOMNode ) {
                $this->raiseError( "Trying to append text node to non-existant id: $id.", E_USER_WARNING );
            } else {
                $newtext = $this->createTextNode( $text );
                return $content->appendChild( $newtext );
            }               
        }
        /**
         * Add a string of text to the document.
         * @param string $text
         * @param string $tag Defaults to "div."  If null, all top-level nodes are returned in an array
         * @returns DOMNode or array od DOMNodes null on failure
         */
        public function importText( $text, $tag="div") {
            libxml_clear_errors();
            $newdoc = new DOMDocument();
            if (!$this->_loadText($newdoc, $text)) {
                return null;
            }
            if ($tag !== null) {
                $results = $newdoc->getElementsByTagName( $tag );
                if ( $results->length > 0 ) {
                    $node = $results->item( 0 );
                    return  $this->doc->importNode( $node, true );
                } else {
                    $this->raiseError( "Unable to find $tag in text:\n" . $text, E_USER_WARNING );
                }
            } else {
                $ret = array();
                for ($i=0; $i <  $newdoc->documentElement->childNodes->length; $i++) {
                    $ret[] = $this->doc->importNode($newdoc->documentElement->childNodes->item($i));
                }
                return $ret;
            }

        }

        /**
         * Set the verbosity of errors for validation
         * @param boolean $verbose
         */
        public function setVerboseErrors($verbose) {
            $this->verboseErrors = $verbose;
        }

        /**
         * the verbosity of errors for validation
         * @var protected  boolean $verboseErrors
         */
        protected $verboseErrors;
        /**
         * Validates the XML of this template.
         * @returns boolean true on sucess
         */
        public function validate() {       
            if ($this->working_dir !== null) {
                $dir = getcwd();
                chdir ($this->working_dir);
            }
            libxml_use_internal_errors(true);
            if (!$this->doc->validate()) { 
                $this->raiseError($this->xmlError(libxml_get_errors(), "Invalid XML Configuration" ));
                if ($this->working_dir !== null) {
                    chdir($dir);
                }
                return false;
            }      
            libxml_clear_errors();
            if (count($errors) > 0) {
                $this->raiseError("Invalid XML Configuaration");
                if ($this->working_dir !== null) {
                    chdir($dir);
                }
                return false;
            }
            if ($this->working_dir !== null) {
                chdir($dir);
            }
            libxml_use_internal_errors(false);
            return true;
        }
        /**
         * Prints an XML error nicely.  Based on http://us3.php.net/manual/en/function.libxml-get-errors.php
         * @param LIBXmlError $error or array or LIBXMLError
         * @param mixed $ret_string.  Defaults to false in which it raises an error for the errors .  If true, we return a string.
         * if a string, it returns a string with the this value at the begining.
         * @returns string
         */
        function xmlError($error, $ret_string = false) {
            if (is_string($ret_string)) {
                $return = $ret_string . ":\n";
            } else {
                $return = '';
            }
            if ($error instanceof libXMLError) {
                $error = array($error);
            }
            if (!is_array($error) || $this->verboseErrors === false || (is_int($this->verboseErrors) && $this->verboseErrors <= 1)) {
                $error = array();
            }
            foreach ($error as $er) {
                if (!$er instanceof libXMLError) {
                    continue;
                }
                $return .= trim($er->message);
                if ($er->file) {
                    $return .= "\n  File: " . $er->file . "\n";
                }
                if (isset($er->line) && isset($er->column)) {
                    $return .= "  Line: " . $er->line ."\n  Column: "  . $er->column;                
                }
                switch ($er->level) {
                case LIBXML_ERR_WARNING:
                    $return .= "Warning " . $er->code . ": ";
                    break;
                case LIBXML_ERR_ERROR:
                    $return .= "Error " .  $er->code. ": ";
                    break;
                case LIBXML_ERR_FATAL:
                    $return .= "Fatal Error " . $er->code. ": ";
                    break;
                }
            }
            if ($ret_string === false) {
                $this->raiseError("Invalid XML Configuaration:\n" . $return);
            } else {
                return $return;
            }
        }


        /**
         * Add a string of text to the document and replace the node with the same id attribute.
         * @param string $text
         * @param string $tag Defaults to "div."
         * @param DOMNode $relative_node if non null gets the element relative to that node.  Defaults to null
         * @return DOMNode -- the node created 
         */
        public function addText( $text, $tag="div" , $relative_node = null) {
            $node = $this->importText( $text, $tag );
            return $this->replaceNode( $node , $relative_node);
        }
        /**
         * Add a string of  text to the document and append it to the node with the given id attribute.
         * @param string $text
         * @param string $tag
         * @param string $parent_id
         * @param $node if non null gets the element relative to that node.  Defaults to null
         * @return DOMNode -- the node just created
         */
        public function appendTextById( $text, $tag, $parent_id ,$node=null) {
            $new_node = $this->importText( $text, $tag );
            return $this->appendNodeById( $new_node, $parent_id, false, $node );
        }
        /**
         * Add a string of  text to the document and append it to the node with the given name attribute
         * of the given occurrence.
         * @param string $text
         * @param string $tag
         * @param string $parent_name
         * @param integer $occurrence 
         * @param $node if non null gets the element relative to that node.  Defaults to null
         * @return DOMNode -- the node just created
         */
        public function appendTextByName( $text, $tag, $parent_name, $occurrence,$node=null ) {
            $node = $this->importText( $text, $tag );
            return $this->appendNodeByName( $node, $parent_name, $occurrence,$node );
        }
    



    


        /**
         * Create a text node
         * @param string $text -- the value of the text node
         */
        public function createTextNode($text) {
            return $this->doc->createTextNode($text);
        }

        /**
         * evaluates an Xpath expression on the document
         * @param string $qry an xpath query
         * @param DOMNode $node.  If non null, the node at wich we we start the query
         * @returns mixed
         */
        public function evaluate($qry,$node = null) {
            if ($node instanceof DOMNode) {            
                $ret =  @$this->xpath->evaluate($qry,$node);
            } else {
                $ret = @$this->xpath->evaluate($qry);
            }
            return $ret;
        }

        /**
         * Run an Xpath query on the document
         * @param string $qry an xpath query
         * @param DOMNode $node.  If non null, the node at wich we we start the query
         * @returns DOMNodeList
         */

        public function query($qry,$node = null) {
            if ($node instanceof DOMNode) {            
                $ret =  @$this->xpath->query($qry,$node);
            } else {
                $ret =@$this->xpath->query($qry);
            }
            if (!$ret instanceof DOMNodeList ) {
                $this->raiseError("Invalid query: $qry");
            }
            return $ret;
        }
    


        /**
         * A raise error method for template that behaves like I2CE::raiseError, except it also displays what
         * is calling the I2CE_Template methods.
         * Raises an error and redirect the user for any critical errors.
         * 
         * The default redirect will go to the home page for the site.
         * @param string/mixed $message The error message.
         * @param integer $type The error type.
         * @param string $redirect The page to redirect to for critical errors.
         * @global array
         */
        protected function raiseError( $message, $type=E_USER_NOTICE, $redirect="" ) {
            if ($this->verboseErrors === false || $this->verboseErrors === 0) {
                return;
            }
            if ( (is_int($this->verboseErrors) && $this->verboseErrors > 1) || ($this->verboseErrors == 'trace')) {
                $debug = debug_backtrace();
                if (is_array($debug) && count($debug) > 0) {
                    $message .="\n";
                    foreach ($debug as  $d) {
                        if (!array_key_exists('line',$d)) {
                            $d['line'] = '<NO LINE NUMBER>';
                        }
                        if (!array_key_exists('file',$d)) {
                            $d['file'] = '<NO FILE>';
                        }
                        if (array_key_exists('class',$d)) {
                            $message .= "Called from " . $d['class'] . '::' . $d['function'] . "() at line " .$d['line'] ." of " . $d['file']  . "\n";
                            if ($d['class'] instanceof I2CE_TemplateMeister) {
                                break;
                            }
                        } else {
                            $message .= "Called from  " . $d['function'] . "() at line " .$d['line'] ." of " . $d['file']  . "\n";
                            break;
                        }
                    }
                }
            }
            I2CE::raiseError($message, $type,$redirect);
        }



        public function renameNodes($old_name,$new_name,$node = null) {
            $this->changeAttributesOnNodes(array('name'=>$old_name),array('name'=>$new_name),$node);
        }

        public function reIdNodes($old_name,$new_name,$node = null) {
            $this->changeAttributesOnNodes(array('id'=>$old_name),array('id'=>$new_name),$node);
        }

        public function addAnchorIdByName( $name, $id, $node = null ) {
            if ($node instanceof DOMNode) {
                $qry = "./descendant-or-self::a[@name='$name']";
            } else {
                $qry = "//a[@name='$name']";
            }
            $results = $this->query( $qry, $node );
            if ( $results->length == 1 ) {
                $results->item(0)->setAttribute( "id", $id );
            }
        }

        public function changeAttributesOnNodes($old,$new,$node= null) {
            if (!is_array($old) || count($old) == 0 || !is_array($new)) {
                return;
            }
            $attrs = array();
            foreach ($old as $attr=>$val) {
                $attrs[] = '@' . $attr . '="' . $val  . '"';
            }
            $attr = implode(' and ' , $attrs);
            if ($node instanceof DOMNode) {
                $qry = "./descendant-or-self::*[$attr]";
            } else {
                $qry = "//*[$attr]" ;
            }
            $results = $this->query( $qry,$node);
            for ($i=0; $i < $results->length; $i++) {
                $node = $results->item($i);
                foreach ($new as $attr=>$val) {
                    $node->setAttribute($attr,$val);
                }
            }
        }
    }
}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
