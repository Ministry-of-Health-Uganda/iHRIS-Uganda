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
 */
/**
 * The I2CE_Template class that all display pages use.
 * 
 * @package I2CE
 * @author Luke Duncan <lduncan@intrahealth.org>
 * @since v1.0.0
 * @version v2.0.0
 */
if (!class_exists('I2CE_Template',false)) {
    class I2CE_Template extends I2CE_TemplateMeister {


        public function addClass($node,$class) {
            if (is_string($class)) {
                $class = array($class);
            }
            if (!$node instanceof DOMElement ||  !is_array($class)) {
                return false;
            }
            if ($node->hasAttribute('class')) {
                $classes = preg_split('/\s+/',$node->getAttribute('class'),-1, PREG_SPLIT_NO_EMPTY);
            } else {
                $classes = array();
            }
            $classes = array_merge($classes,$class);
            $node->setAttribute('class', implode(' ', $classes));
        }

        public function hasClass($node,$class) {
            if (!$node instanceof DOMElement || !$node->hasAttribute('class') || !is_string($class)) {
                return false;
            }
            $classes = preg_split('/\s+/',$node->getAttribute('class'),-1, PREG_SPLIT_NO_EMPTY);
            if (!is_array($classes)) {
                return false;
            }
            return in_array($class,$classes);
        }
        

        public function removeClass($node,$class) {
            if (is_string($class)) {
                $class = array($class);
            }
            if (!$node instanceof DOMElement || !$node->hasAttribute('class') || !is_array($class)) {
                return false;
            }
            $classes = preg_split('/\s+/',$node->getAttribute('class'),-1, PREG_SPLIT_NO_EMPTY);
            $classes = array_diff($classes,$class);
            $node->setAttribute('class',implode(' ', $classes));
        }

    
        /**
         * Helper method. Load a file into the spectified document as HTML 
         * @param DOMDocument $doc
         * @param string $contentfile the file to load
         * @returns boolean  False on failure, 
         */
        public function _loadFile($doc,$contentfile) {
            $file = $this->findTemplate( $contentfile );
            if ( !$file || $file == "" ) {
                $this->raiseError( "Unable to find template file: $contentfile.", E_USER_WARNING );
                return false;
            }
            libxml_clear_errors();
            $text = file_get_contents($file);
            if ($text === false) {
                $this->raiseError("File $file is either non-readable or does not exist");
                return false;
            }            
            if (preg_match("/^(.*?<\/?head((\s+(\w|\w[\w-]*\w)(\s*=\s*(?:\".*?\"|'.*?'|[^'\">\s]+))?)+\s*|\s*)\/?>)(.*)/ism", $text, $matches)) {
                //there is an existing head tag
                $text = $matches[1] . '<meta http-equiv="content-type" content="text/html; charset=utf-8">' . $matches[7];
            } else {
                //no head.  see if there is an html or not
                if (preg_match("/^(.*?<\/?html((\s+(\w|\w[\w-]*\w)(\s*=\s*(?:\".*?\"|'.*?'|[^'\">\s]+))?)+\s*|\s*)\/?>)(.*)/ism", $text, $matches)) {
                    //there is an html tag, but no head tag
                    $text = $matches[1] . '<head><meta http-equiv="content-type" content="text/html; charset=utf-8"></head>' . $matches[7];
                } else {
                    //no html tag, no head tag.  
                    //Check for a body tag
                    if (preg_match('/\<\s*body/m', $text)) {
                        $text =  '<html><head><meta http-equiv="content-type" content="text/html; charset=utf-8"></head>' . $text . "</html>";                    
                    } else {
                        $text = '<html><head><meta http-equiv="content-type" content="text/html; charset=utf-8"></head><body>'.$text.'</body></html>';
                    }
                }
            }
            $doc->substituteEntities = false;
            $doc->resolveExternals = false;
            $doc->encoding = 'UTF-8';
            $doc->validateOnParse = false;
            libxml_use_internal_errors(true);
            libxml_clear_errors();
            $sucess=  @$doc->loadHTML($text);
            $errors = libxml_get_errors();
            if (!$sucess) {
                $this->raiseError("Could not load HTML file $contentfile found at $file:\n" . print_r($errors,true));
            }
            if ($sucess && count($errors) > 0) {
                $this->raiseError("Problem loading HTML file $contentfile found at $file:\n" . print_r($errors,true));
            }
            libxml_clear_errors();
            libxml_use_internal_errors(false);
            if (!$sucess) {
                return;
            }
            $show_file_source = 1;
            I2CE::getConfig()->setIfIsSet($show_file_source,"/I2CE/template/show_file_source");            
            if ($show_file_source) {
                $xpath = new DOMXPath($doc);
                $bodyList = $xpath->query("/html/body");
                if ($bodyList instanceof DOMNodeList && $bodyList->length > 0) {
                    $body = $bodyList->item(0);
                    if ($body->hasChildNodes()) {
                        foreach ($body->childNodes as $child) {
                            if (!$child instanceof DOMElement) {
                                continue;
                            }
                            if ($child->hasChildNodes()) {
                                $child->insertBefore($doc->createComment("File $file"),$child->firstChild);
                            }else{
                                $child->appendChild($doc->createComment("File $file"));
                            }
                            return true;
                        }
                    }
                    if ($body->hasChildNodes()) {
                        $body->insertBefore($doc->createComment("File $file"),$body->firstChild);
                    }else{
                        $body->appendChild($doc->createComment("File $file"));
                    }
                } else {
                    $doc->documentElement->appendChild($doc->createComment("File $file"));                
                }
            }
            return true;
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
                $header = "<meta http-equiv='content-type' content='text/html; charset={$this->doc->encoding}'>";
                $text = $header . $text;
            }
            $sucess = $doc->loadHTML($text);
            if (!$sucess) {
                $this->raiseError($this->xmlError(libxml_get_errors(),"Could not load HTML"));
            }
            libxml_clear_errors();
            libxml_use_internal_errors(false);
            return $sucess;
        }
    


        /**
         * This method finds the location of a template file. If the file is not an absolute path searches the class path 'TEMPLATES'
         * 
         * This method searches the template directory path from the global configuration array
         * for the given template.  If it exists it returns the full path to the file and if not
         * it returns false.  It seaches the path backwards so that later directories
         * can override package versions of files.
         * @param string $template The name of the template file.
         * @param boolean $raise_error Defaults to true.  Raise error if template is not found
         * @return mixed
         */
        public function findTemplate( $template, $raise_error = true ) {      
            if (I2CE_FileSearch::isAbsolut($template)) {
                return $template;
            }
            $template_file = I2CE::getFileSearch()->search( 'TEMPLATES', $template );
            if ( $template_file ) {
                return $template_file;
            } else {
                if ($raise_error) {
                    I2CE::raiseError( "Couldn't find template file: $template.\nSearch Path is:\n" 
                                      . print_r(I2CE::getFileSearch()->getSearchPath('TEMPLATES'), true), E_USER_NOTICE );
                }
                return false;
            }
        }



        /**
         * An array of  files that have been loaded so they're aren't added in twice.
         * The keys are the file names, the value is true if it has been loaded, unset otherwise
         * @var array
         */
        protected $files_loaded;




        /** 
         * I2CE_Template constructor method.
         * 
         * This constructor sets up the basic variables for all I2CE_Template objects.
         *  $loadOptions is set to zero.
         * 
         * 
         */
        public function __construct() {
            parent::__construct();
            $this->verboseErrors = true;
            $this->files_loaded = array();
            $this->headers = array('Content-type: text/html; charset=utf-8');
            $this->loadOptions = LIBXML_DTDATTR | LIBXML_DTDLOAD;
        }
    
        /**
         * Fixes any href's starting with # so that they work properly
         * Also makes sure that any relative URL include an index.php if .htaccess is not used.
         */
        protected function fixupAnchors() {
            $pageURL = '';
            if (isset($_SERVER['PATH_INFO'])) {
                $pageURL = $_SERVER['PATH_INFO'];
                if ($pageURL[0] == '/') {
                    $pageURL = substr($pageURL,1);
                }
            }
            if (isset($_SERVER['QUERY_STRING']) && strlen($_SERVER['QUERY_STRING']) > 0) {
                $pageURL .= '?' . $_SERVER['QUERY_STRING'];
            }
            $anchors = $this->query("//*[starts-with(@href,'#')]");
            for( $i = 0; $i < $anchors->length; $i++ ) {
                $anchor = $anchors->item($i); 
                $href= $pageURL . $anchor->getAttribute('href'); 
                $anchor->setAttribute('href',$href); 
            } 
            if (I2CE_Page::rewrittenURLs()) {
                //there is (presumably) a .htaccess that is rewrite SITE_UR/a/blah/c?darp to SITE_URL/index.php/a/blah/c?darp
                //so we don't need to do anything.
                return;
            }        
//        $urls = $this->query("//*[not(matches(@href,'^([a-zA-Z]+:\/\/)|\/'))]"); //get all non-absolute urls. -- this should work but I get a : xmlXPathCompOpEval: function matches not found 
            //so iwill do it by hand below
            $links = array('src','href','action');
            foreach($links as $link) {            
                $urls = $this->query("//*[@$link]"); //get all non-absolute urls.
                for( $i = 0; $i < $urls->length; $i++ ) {
                    $url = $urls->item($i); 
                    $ref = self::ensureURL($url->getAttribute($link));
                    $url->setAttribute($link,$ref);
                }
            }
            //now fix any @import url's for style sheets
            $styles = $this->query("//style");
            for ($i=0; $i < $styles->length; $i++) {
                $style = $styles->item($i);
                $replace = self::ensureCSSURLs($style->textContent);
                while($style->hasChildNodes()) {
                    $style->removeChild($style->lastChild);
                }
                $style->appendChild($this->doc->createTextNode( $replace));
            }
            //now fixed ie hack type comments e.g 
            //<!--[if lt IE 7]><link href="file/iehacks.css?newman" rel="stylesheet" type="text/css" media="screen" /><![endif]-->
            $comments = $this->query('//comment()');
            foreach ($comments as $comment ) {
                $data = $comment->data;
                $comment->deleteData(0,$comment->length);
                $data = preg_replace('/href=([\'"])([a-zA-Z0-9])/','href=$1index.php/$2',$data,-1);
                $comment->appendData($data);
            }
        } 

        public static function ensureCSSURLs($css) {
            $css = preg_replace('/@import\s+\"(.*?)\"/','@import url("\1")',$css); //in case the url is not wrapped in parens
            $css = preg_replace_callback(
                '/@import\s+url\(\s*(\"?)(.*?)\1\s*\)/',
                function( $matches ) {
                    return "@import url(\"" . I2CE_Template::ensureURL($matches[2]) . "\")";
                }
                ,$css);
            return $css;
        }

        public static function ensureURL($url) {
            if (preg_match('/(^[a-zA-Z]+:\/\/)|(^\/)|(^index\.php\/)/',$url)) {
                return $url;
            } else {
                return  "index.php/$url"; 
            }
        }


        /**
         *  Called to prepare the display. 
         */
        public function prepareDisplay() {
            // Check role initially to skip processing any form elements that won't remain.
            //I2CE_ModuleFactory::callHooks('template_display',array('template'=>$this,'user'=>$this->user));
            $this->checkRolesTasksAndPermissions();
            $this->setBase();
            $this->fixupAnchors();
            $this->removeTranslatorComments();
        }

        /**
         * Returns the displayed page as a string
        * @param boolean $decode_entities Set if you want to decode HTML
         *                                 entities back to the original
         *                                 ignoring HTML specialchars
         * @param int $decode_flags default is ENT_NOQUOTES
         * @returns string
         */
        public function getDisplay( $decode_entities=false, 
                $decode_flags = ENT_NOQUOTES ) {
            $out = $this->doc->saveHTML();  

            if ( $decode_entities ) {
               foreach( get_html_translation_table( HTML_SPECIALCHARS, $decode_flags ) as $code ) { 
                    $out = str_replace( $code, htmlspecialchars( $code ), $out );
                }
                return html_entity_decode($out, $decode_flags ); 
            } else {
                return $out;
            }

            // Not doing tidy currently as it was causing problems
            /*
            if (function_exists('tidy_get_output')) {
                $this->removeProprietaryAttr();
                $out = $this->doc->saveHTML();
                //$out = htmlentities($out,ENT_COMPAT,'UTF-8'); 
                $tidy = new tidy();
                $config = array(
                    'indent'=>true,
                    'wrap'=>0,
                    'quote-ampersand'=>false,
                    'numeric-entities'=>false,
                    'quote-marks'=>false,                    
                    'preserve-entities'=>false,
                    'output-html'=>false,
                    'output-xml'=>false,
                    'output-xhtml'=>true,
                    'char-encoding'=>'utf8',
                    'input-encoding'=>'utf8',
                    'output-encoding'=>'utf8',
                    'input-xml'=>false
                    );
                $tidy->isXML();
                $tidy->parseString($out,$config,'UTF8');
                $tidy->cleanRepair();
                $out = tidy_get_output($tidy);
                return   $out;
            } else {
                return $this->doc->saveHTML();  
            }      
            */
        }

        protected function removeTranslatorComments() {
            $results = $this->query('//*[@translator_comment]');
            for ($i=0; $i < $results->length; $i++) {
                $results->item($i)->removeAttribute('translator_comment');
            }
        }

        /**
         * Remove the proprietary HTML attributes from tags before
         * outputting the content through tidy.
         * Commented out for now until the best course of action
         * can be determined.
         */
        protected function removeProprietaryAttr() {
            /*
            $results = $this->query('//span[@name]');
            for ($i=0; $i < $results->length; $i++) {
                $results->item($i)->removeAttribute('name');
            }
            $results = $this->query('//tr[@name]');
            for ($i=0; $i < $results->length; $i++) {
                $results->item($i)->removeAttribute('name');
            }
            $results = $this->query('//th[@name]');
            for ($i=0; $i < $results->length; $i++) {
                $results->item($i)->removeAttribute('name');
            }
            $results = $this->query('//tbody[@name]');
            for ($i=0; $i < $results->length; $i++) {
                $results->item($i)->removeAttribute('name');
            }
            $results = $this->query('//div[@name]');
            for ($i=0; $i < $results->length; $i++) {
                $results->item($i)->removeAttribute('name');
            }
            $results = $this->query('//input[@method]');
            for ($i=0; $i < $results->length; $i++) {
                $results->item($i)->removeAttribute('method');
            }
            */
        }

    
        protected function setBase() {
            $head = $this->getElementByTagName('head',0);        
            if (!$head instanceof DOMNode) {
                return;
            }
            $script = substr($_SERVER['SCRIPT_NAME'], 0, strrpos($_SERVER['SCRIPT_NAME'], "/"));
            $site_url = I2CE::getProtocol() . '://' . $_SERVER['HTTP_HOST']  . $script  . '/';
            $base = $this->createElement('base',array('href'=>$site_url));
            $head->insertBefore($base,$head->firstChild);
        }
        


        /**
         * Remove any restricted access elements of the page.
         * 
         * This method processes all elements in the document that have a role attribute.
         * If there is no known user or the user doesn't have access to that role then the
         * entire node will be removed from the document.
         * @param I2CE_User $user
         * @param DOMNode $node.  Defaults to null, meaning we check the whole document.  Otherwise, we check
         * relaive to that role
         */
        public function checkRolesTasksAndPermissions( $node =null) {
            if ($node instanceof DOMNode) {
                $results = $this->query( "./descendant-or-self::node()[@role!='' or @task!='' or @permission!='']", $node );
            } else {
                $results = $this->query( "//*[@role!='' or @task!=''  or @permission!='']" );
            }
            $permissionParser = new I2CE_PermissionParser($this,$this->user);
            for( $i = 0; $i < $results->length; $i++ ) {
                $node = $results->item($i);
                if (!$node instanceof DOMElement) {
                    return true; //not removed.
                }
                $role = trim($node->getAttribute('role'));
                $task = trim($node->getAttribute('task'));
                $permission = trim($node->getAttribute('permission'));
                if ($role) {
                    $role = 'role(' . $role .')';
                    if ($permission) {
                        $permission = $permission . ' or ' . $role;
                    } else {
                        $permission = $role;
                    }
                }
                if ($task) {                    
                    $task = 'task(' . $task .')';
                    if ($permission) {
                        $permission = $permission . ' or ' . $task;
                    } else {
                        $permission = $task;
                    }
                }
                $node->removeAttribute( "role" );
                $node->removeAttribute( "task" );
                $node->removeAttribute( "permission" );        
                $permission = trim($permission);
                if ($permission && !$permissionParser->hasPermission($permission)) {
                    $this->removeNode( $node );
                }
            }       
        }


        /**
         * Process any arguments sent to the page
         * @returns boolean true on sucess. false on failure
         */
        public function processArgs($args) {
            $prefix_title = 'iHRIS';
            I2CE::getConfig()->setIfIsSet($prefix_title,"/I2CE/template/prefix_title");
            if (array_key_exists('title', $args) && $args['title']) {
                $this->setTitle( $prefix_title . ": " . $args['title']);
            } else {
                $this->setTitle( $prefix_title);

            }
        
            if (!isset($args['attributes'])) {
                $args['attributes'] = array();
            }
            foreach( $args['attributes'] as $attr ) {  
                list($attr_name,$value) = explode('=',$attr);
                if  (  !($value === null || ( is_string($value) && strlen($value) == 0))
                       && !($attr_name === null || ( is_string($attr_name) && strlen($attr_name) == 0))) {
                    $this->setBodyAttr( $attr_name, $value );
                }
            }
            return true;
        }



        /**
         * Set the HTML title of the page.
         * 
         * This will find the first title element of the page and replace
         * the text in it with the given title.  There should only be one
         * title element in a valid HTML page.
         * @param string $title
         */
        public function setTitle( $title ) {
            $titletag = $this->getElementByTagName( "title" ,0 );
            if ( ! $titletag instanceof DOMNode) {
                return;
            }
            $newtitle = $this->createTextNode( $title );
            if ( $titletag->hasChildNodes() ) {
                // Should just have one child node of the previous text
                $oldtitle = $titletag->childNodes->item( 0 );
                $titletag->replaceChild( $newtitle, $oldtitle );
            } else {
                $titletag->appendChild( $newtitle );
            }
        }

        /**
         * Adds an attribute to the body tag of this document.
         * 
         * This will find the body tag of the document and add or replace
         * the given attribute with the given value.
         * @param string $attr
         * @param string $value
         */
        public function setBodyAttr( $attr, $value ) {
            $bodytag = $this->doc->getElementsByTagName( "body" )->item( 0 );
            if (!$bodytag instanceof DOMElement) {
                $this->raiseError("Trying to set body attribute, but body not found",E_USER_NOTICE);
                return false;
            }
            $bodytag->setAttribute( $attr, $value );                
            return true;
        }

        /**
         * Sets the id attribute for the body tag.
         * 
         * This will find the body tag of the document and add or replace the
         * id attribute with the given id.
         * @param string $id
         */
        public function setBodyId( $id ) {
            return $this->setBodyAttr( "id", $id );

        }



    
        /**
         * Recursively process all child nodes that are being added in a loop to replace the designator text.
         * @param DOMNode $node
         * @param string $designator
         * @param integer $count
         * @see replaceCount
         */
        protected function processLoopCount( $node, $designator, $count ) {
            $this->replaceCount( $node, $designator, $count );
            if ( $node->hasChildNodes() ) {
                foreach( $node->childNodes as $child ) {
                    $this->processLoopCount( $child, $designator, $count );
                }
            }
        }
        /**
         * Replace the given designator text with the loop count of the template file being added.
         * 
         * If the node is a DOM element then the designator text with the number of the count. 
         * @param DOMNode $node
         * @param string $designator
         * @param integer $count
         */
        protected function replaceCount( $node, $designator, $count ) {
            if ( $node->nodeType != XML_ELEMENT_NODE ) return;
            $attrs = array( "name", "id" );
            foreach( $attrs as $attr ) {
                if ( $node->hasAttribute( $attr ) ) {
                    $value = $node->getAttribute( $attr );
                    $value = str_replace( $designator, $count, $value );
                    $node->setAttribute( $attr, $value );
                }
            }
        }
    
    


        /**
         * Add  text to the header of the document.
         * 
         * @param string $text
         * @param string $type -- one of 'css',javascipt','vbscript'
         * @param mixed $as_separate_node whether or not we want add the node as a separate node or append it (default to false-- append).
         * It will append it to the last node if so.
         * If it is a string, then we create a new node and set the id of a node to  the given string, or append to an existing  node with that
         * id. If it is a DOMNode then we just append to that node
         * @param string $init_text -- the text we ar adding as a separate node and the node does not exist.  Defaults to the empty string
         * @return DOMNode -- the node just created or appended to.  False on failure
         * Note the text does not have to be wrapped in <$tag> node.  If not, it will put it in a <$tag> node with reasonalbe
         * attributes.
         */
        public function addHeaderText( $text,$type, $as_separate_node = false , $init_text = '' ) {
            $this->wrapHeaderTextInTag($text,$type);        //$type should now be  a valid tag
            $imported_node = $this->importText( $text, $type, LIBXML_NOENT);
            $head_node = $this->doc->getElementsByTagName( "head" )->item( 0 ); 
            if (!$head_node instanceof DOMElement) {
                //$this->raiseError("Could not find head node");
                return false;
            }
            if ($as_separate_node === false ) {
                $append_node = $this->getElementByTagName( $type, -1,$head_node );
            } else {
                $append_node = $this->getElementById($as_separate_node); 
                //note: we don't want to do this relative to head_node as script tags loaded
                //from templates may not have  been moved to the header yet
            }
            if ($append_node instanceof DOMNode) {
                //we need to append it to an existing header text node
                foreach( $imported_node->childNodes as $child ) {
                    $append_node->appendChild( $child );
                }
                return $append_node;            
            } else {//we need to append to the header node
                if (is_string($as_separate_node) && $imported_node instanceof DOMElement) {
                    if ($init_text) {
                        $imported_node->insertBefore($this->createTextNode($init_text), $imported_node->firstChild);
                    }
                    $imported_node->setAttribute('id',$as_separate_node);
                }
                return $head_node->appendChild($imported_node);
            }
        }

        /**
         * No validation occcurs so always returns true
         * @returns boolean
         */
        public function validate() {
            return true;
        }

        /**
         * Add a script or css to the header as a link 
         * 
         * @param string $file
         * @param mixed $attr   --   an array of attribute/value pairs.   Defaults to the empty array.  can also be a string
         *   in which case it is the id that you want to give the import node.
         * @param boolean $use_filedump -- default to true meaning that we should use the filedump utility 
         *       when looking for the script/css
         * @returm DOMNode -- the node just created or appended to
         *  You might use this by calling
         *  addHeaderLink("printer.css",'',array('media'=>'print'));
         * Note: Uses the file's extension to determine the proper behavior.  Valid ones are 'js', 'css', and 'vb'
         */
        public function addHeaderLink( $file,$attr = array(),$use_filedump = true) {
            if (  array_key_exists($file,$this->files_loaded) && $this->files_loaded[$file]) {
                // Check to make sure this  file hasn't already been loaded and ignore it if so.
                return;
            }
            // we have not loaded the file so start loading it
            $this->files_loaded[$file] = true;
            $ext =  strtolower(substr(strrchr($file, "."), 1));
            if ($use_filedump) {
                if (I2CE::rewrittenURLs()) {
                    $file_src = 'file/' .  $file;
                } else {
                    $file_src = 'index.php/file/' .  $file;
                }
            } else {
                $file_src = $file;
            }
            if (is_scalar($attr)) {
                $attr = array('id'=>$attr);
            } 
            if (!is_array($attr)) {
                $attr = array();
            }
            if ( array_key_exists( 'ext', $attr ) ) {
                $ext = $attr['ext'];
                unset( $attr['ext'] );
            }
            switch($ext) {
            case 'js':
                $node = null;
                $existed = false;
                foreach ($this->query('//script[@type="text/javascript"]') as $js_node) {
                    if (! ($src = $js_node->getAttribute('src'))) {
                        continue;
                    }
                    if (substr($src,0,15) == 'index.php/file/') {
                        $src = substr($src,15);
                    } else if ( substr($src,0,5) == 'file/') {
                        $src = substr($src,5);
                    }
                    if ($src == $file) {
                        $existed = true;
                        return $js_node;
                    }                    
                }
                if (array_key_exists('id',$attr) ) {
                    $node = $this->getElementById($attr['id']);
                    if ($node instanceof  DOMElement) {
                        $existed = true;
                    } else {
                        $node = $this->doc->createElement('script',''); 
                        $node->setAttribute('id',$attr['id']);
                    }
                    unset($attr['id']);
                } else {
                    $node = $this->doc->createElement('script',''); 
                }
                if ($existed) {
                    $old_file = $node->getAttribute('src');
                    if ($old_file) {
                        $node->removeAttribute('src');
                        $node->appendChild(
                            $this->doc->createTextNode(
                                'document.write(\'<script type="text/javascript" src="' . $old_file . '"></script>\');'
                                ));                        
                    } 
                    $node->appendChild(
                        $this->doc->createTextNode(
                            'document.write(\'<script type="text/javascript" src="' . $file_src . '"></script>\');'
                            ));                    
                } else {
                    $node->setAttribute('src',$file_src); 
                    $node->setAttribute('type','text/javascript'); 
                }
                break;
            case 'vb':
                $node = $this->doc->createElement('script',''); 
                $node->setAttribute('src',$file_src);
                $node->setAttribute('type','text/vbscript'); 
                break;
            case 'css':
                if ( array_key_exists( 'ie6', $attr ) && $attr['ie6']  ) {
                  $node = $this->doc->createComment( '[if lt IE 7]>' );
                  $link = $this->doc->createElement('link','');
                  $link->setAttribute('rel','stylesheet');
                  $link->setAttribute('media','screen');
                  $link->setAttribute('href',$file_src . "?newman"); 
                  $link->setAttribute('type','text/css'); 
                  foreach ($attr as $a=>$v) {
                      $link->setAttribute($a,$v);
                  }
                  $node->appendData( $this->doc->saveXML($link) );
                  $node->appendData( "<![endif]" );
                  $attr = array();
                } else {
                  $node = $this->doc->createElement('link','');
                  $node->setAttribute('rel','stylesheet');
                  $node->setAttribute('media','screen');
                  $node->setAttribute('href',$file_src); 
                  $node->setAttribute('type','text/css'); 
                }
                break;   
            default:
                $this->raiseError("Unknown file type ($ext) for linking in the header", E_USER_NOTICE);
                return;
            }
            foreach ($attr as $a=>$v) {
                $node->setAttribute($a,$v);
            }
            if ( ! ($head_node = $this->doc->getElementsByTagName( "head" )->item( 0 )) instanceof DOMNode) {
                return false;
            }
            return $this->appendNode($node,$head_node); //append the node to the begining of the header
        }


    

    

        protected function wrapHeaderTextInTag(&$text,&$tag) {
            $tag = strtolower($tag);
            $first_tag = null;
            if ( preg_match('/^\s*<\s*([a-zA-Z]+)/',$text,$matches)) {
                //we probably started with a tag. otherwise it is malformed
                //but i am not going to care
                $first_tag = strtolower($matches[1]);
            } else {
                //did not start with a tag, so wrap it
                $first_tag = $tag;
            }
            switch ($first_tag) {
            case 'script': //assume a 'script' is really a 'javascript'
            case 'javascript':
            case 'js':
                $tag = 'script';
                $text = '<script  type=\'text/javascript\'>' . "\n" .  $text . '</script>';
                break;
            case 'css':
            case 'style':
                $tag = 'style';
                $text = '<style type=\'text/css\' media=\'screen\'>' . "\n". $text . '</style>';
                break;
            case 'ecmascript':
                $tag = 'script';
                $text = '<script  type=\'application/ecmascript\'>' . "\n" . $text . '</script>';
                break;
            case 'vbscript':
                $tag = 'script';
                $text = '<script  type=\'text/vbscript\'>' . "\n" . $text   .'</script>';
                break;
            default:
                $text= htmlspecialchars($text);
                if ($first_tag != $tag) {
                    //they don't agree so wrap it 
                    $text = '<' .  $tag . '>' . "\n" . $text  . '</'  .$tag . '>';
                            
                }
                break;
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
