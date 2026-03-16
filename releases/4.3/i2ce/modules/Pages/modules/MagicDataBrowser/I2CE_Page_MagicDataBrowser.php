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
 * Default Admin Page
 *
 * @package I2CE
 * @subpackage Core
 * @author Carl Leitner <litlfred@ibiblio.org>
 * @version 2.1
 * @access public
 */

class I2CE_Page_MagicDataBrowser extends I2CE_Page {

        

    /**
     * @var protected array $config_path array of strings.  the configuration path requested for this page.
     */
    protected $config_path;

    /**
     * @var I2CE_MagicData protected The magic data pointed to by the config path
     */
    protected $config;
    
    /**
     * Constructor -- called from page wrangler for URL of form admin/(module_name)/action/arg1/arg2
     * @param string $shortname A module name to show the admin page for. Defaults to null to mean no module.
     * @param string $action The action this pages is to perform
     * @param array $args an array of strings
     * @param string $message. A message to display on the page.  Defaults to null
     */
    public function __construct($args,$request_remainder) {
        parent::__construct($args,$request_remainder);
        $this->config_path = $request_remainder;
        $create = !($this->page == 'load');
        $this->config = I2CE::getConfig()->traverse($this->config_path,$create,false);
    }


    protected function reshow($parent = false) {
        if ($redirect = $this->request('redirect')) {
            $this->setRedirect($redirect);
        } else {
            if ($parent) {
                $path = $this->config_path;
                array_pop($path);
                $path = implode('/',$path);
            } else   if ($this->get_exists('caller')) {
                $path = $this->get('caller');
            }  else if ($this->post_exists('caller')) {
                $path = $this->post('caller');
            } else {
                $path = implode('/',$this->config_path);
            }
            $url = $this->module() .'/show/' .$path;
            $this->setRedirect($url);
        }
    }

    


    /*
     * Perform the actions for this page
     */
    protected function action() {
        parent::action();
        if (!$this->config instanceof I2CE_MagicDataNode) {
            $this->reshow(true); //show the parent node.
            return;
        }
        $reshow = false;
        switch($this->page()) {
        case 'upload':
            I2CE::raiseError("Upload on " . $this->config->getPath(false) . "\n" . print_r($_FILES,true));
            if (!array_key_exists('upload',$_FILES) 
                || (array_key_exists('error',$_FILES['upload']) && $_FILES['upload']['error'] > 0)
                || ($upload = file_get_contents($_FILES['upload']['tmp_name'])) ===false) {
                $this->userMessage("Could not upload " . $_FILES['upload']['name'] );
                break;
            }
            if (strlen($upload) != $_FILES['upload']['size']) {
                I2CE::raiseError("Upload size mismatch " . strlen($upload) . ' != ' . $_FILES['upload']['size']);
                $this->userMessage("Could not upload " . $_FILES['upload']['name'] );
                break;
            }
            if ($this->config->is_parent()) {
                $this->userMessage("Cannot set value on parent node");
                break;
            } 
            $content =    $this->config;
            if ($content->is_parent()) {
                $this->userMessage("Cannot overwrite content node");
                break;
            }
            if ($content->hasAttribute('binary') && $content->getAttribute('binary')) {
                $mt = I2CE_MimeTypes::magicMimeType($upload);
                $upload = base64_encode($upload);
                $content->setValue($upload);
                $content->setAttribute('encoding','base64');
                $content->setAttribute('binary','1');
                if ($mt) {
                    $content->setAttribute('mime-type',$mt);
                } else if ($content->hasAttribute('mime-type')) {
                    $content->removeAttribute('mime-type');
                }
            } else {
                $content->setValue($upload);
            }
            break;
        case 'upload_binary':
            I2CE::raiseError("Upload Binary on " . $this->config->getPath(false) . "\n" . print_r($_FILES,true));
            if (!array_key_exists('upload',$_FILES) 
                || (array_key_exists('error',$_FILES['upload']) && $_FILES['upload']['error'] > 0)
                || ($upload = file_get_contents($_FILES['upload']['tmp_name'])) ===false) {
                $this->userMessage("Could not upload " . $_FILES['upload']['name'] );
                break;
            }
            if (strlen($upload) != $_FILES['upload']['size']) {
                I2CE::raiseError("Upload size mismatch " . strlen($upload) . ' != ' . $_FILES['upload']['size']);
                $this->userMessage("Could not upload " . $_FILES['upload']['name'] );
                break;
            }
            if ($this->config->is_scalar()) {
                $this->userMessage("Cannot set value on parent node");
                break;
            } 
            $content =    $this->config->traverse('content',true,false);
            $name =    $this->config->traverse('name',true,false);
            $type =    $this->config->traverse('type',true,false);
            if ($content->is_parent()) {
                $this->userMessage("Cannot overwrite content node");
                break;
            }
            if ($name->is_parent()) {
                $this->userMessage("Cannot overwrite content node");
                break;
            }
            if ($type->is_parent()) {
                $this->userMessage("Cannot overwrite type node");
                break;
            }
            //I2CE::raiseError("Setting " . $this->config->getPath() );
            $upload = base64_encode($upload);
            $content->setValue($upload);
            $name->setValue($_FILES['upload']['name']);
            $type->setValue($_FILES['upload']['type']);
            $content->setAttribute('binary',1);
            $content->setAttribute('encoding','base64');
            break;
        case 'load':
            I2CE::raiseError("Begin load:"  . print_r($this->request(),true));
            $transform = false;
            if (($transform_key = $this->request('transform_key')) 
                && I2CE_MagicDataNode::checkKey($transform_key)
                && I2CE::getConfig()->setIfIsSet($transform, "/modules/magicDataBrowser/transforms/" . $this->request('transform_key')) 
                && $transform) {
                if ( substr($transform,0,7) == 'file://'
                     && ( ! ($transform_file = I2CE::getFileSearch()->search('XSL',$file_name = substr($transform,7)))
                          || !($transform = file_get_contents($transform_file))
                         )
                    ) {
                    I2CE::raiseError("Could not load $file_name for transform");
                    $this->userMessage("Invalid registered transform");
                    return false;
                }
            } else if (array_key_exists('transform',$_FILES) && ! (array_key_exists('error',$_FILES['transform']) && $_FILES['transform']['error'] > 0)) {
                $transform = file_get_contents($_FILES['transform']['tmp_name']);
            }
            I2CE::raiseError("Loading with transform:$transform");
            if ($this->actionLoad($transform)) {
                $this->userMessage("Data successuly loaded");
            } else {
                $this->userMessage("There was a problem loading the data");
            }
            break;
        case 'erase':
            $name = $this->config->getName();
            $parent = $this->config->traverse('../');
            if ($this->isPost() && ($this->config->getPath() != $this->config->traverse('/')->getPath())) { //don't allow an erase of the top level node
                unset($parent->$name);
            }
            $reshow = true;
            break;
        case 'parent':
            if ($this->isPost() && $this->config->is_indeterminate()) {
                $this->config->set_parent();
            }
            break;
        case 'scalar':
            if ($this->isPost() && $this->config->is_indeterminate()) {
                $this->config->set_scalar();
            }
            break;
        case 'add':
            if ($this->post_exists('browser_magic_data_add_key')) {
                $key = $this->post('browser_magic_data_add_key');
                if (I2CE_MagicDataNode::checkKey($key) && !$this->config->pathExists($key)) {
                    $this->config->traverse($key,true,false);
                }
            }
            break;
        case 'add_parent':
            if ($this->post_exists('browser_magic_data_add_key')) {
                $key = $this->post('browser_magic_data_add_key');
                if (I2CE_MagicDataNode::checkKey($key) && !$this->config->pathExists($key)
                    && ($newNode = $this->config->traverse($key,true,false)) instanceof I2CE_MagicDataNode) {
                    $newNode->set_parent();
                }
            }
            break;
        case 'add_scalar':
            if ($this->post_exists('browser_magic_data_add_key')) {
                $key = $this->post('browser_magic_data_add_key');
                if (I2CE_MagicDataNode::checkKey($key) && !$this->config->pathExists($key)
                    && ($newNode = $this->config->traverse($key,true,false)) instanceof I2CE_MagicDataNode) {
                    $newNode->set_scalar();
                }
            }
            break;
        case 'add_scalar_binary':
            if ($this->post_exists('browser_magic_data_add_key')) {
                $key = $this->post('browser_magic_data_add_key');
                if (I2CE_MagicDataNode::checkKey($key) && !$this->config->pathExists($key)
                    && ($newNode = $this->config->traverse($key,true,false)) instanceof I2CE_MagicDataNode) {
                    $newNode->set_scalar();
                    $newNode->setAttribute('encoding','base64');
                    $newNode->setAttribute('binary','1');
                }
            }
            break;

        case 'set':
            if ($this->post_exists('browser_magic_data_value') && $this->post_exists('browser_magic_data_key')) {
                $key = $this->post('browser_magic_data_key');
                $value = $this->post('browser_magic_data_value');
                if ($this->config->offsetExists($key) && is_scalar($value)) {
                    if ($this->config->is_translatable($key)) {
                        $locales = I2CE_Locales::getPreferredLocales();
                        reset($locales);
                        $locale = current($locales);
                        $this->config->setTranslation($locale,$value,$key);
                        if ($locale == I2CE_Locales::DEFAULT_LOCALE) {
                            $this->config->__set($key, $value);
                        }
                    } else {
                        $this->config[$key] = $value;
                    }
                }
            }
            //we redirect so a reload does not post and so that if the page's display depends on the value that is being
            //set, we redisplay it.
            break;
        case 'download':                   
            if ( ($errors = I2CE_Dumper::cleanlyEndOutputBuffers())) {
                I2CE::raiseError("Errors:\n" . $errors);
            }
            $value = $this->config->getValue();
            header("Cache-Control: max-age=1, s-maxage=1, no-store, no-cache, must-revalidate");
            header( 'Cache-Control: post-check=0, pre-check=0', false );
            header( 'Pragma: no-cache' ); 
            header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); 
            header("Last-Modified: " . gmdate("D, d M Y H:i:s", time()-10) . " GMT");
            header("ETag: PUB" . time());
            header("Pragma: no-cache");
            header("Content-length: " . strlen($value));            
            header('Content-Disposition: attachment; filename="' . $this->config->getName(). '"');
            if ($this->config->hasAttribute('mime-type')) {
                $mime_type = $this->config->getAttribute('mime-type');
            } else {
                $mime_type = I2CE_MimeTypes::magicMimeType($value);
            }
            I2CE::raiseError($mime_type);
            header('Content-type:' . $mime_type);
            session_cache_limiter("nocache");
            echo $value;
            die();
        case 'trans':
            $this->config->setTranslatable(null, ! $this->config->is_translatable());
            break;
        case 'show':                    
        case 'mini':                    
            $this->actionDisplayConfig();
            $reshow = null;
            break;
        default:
            break;
        }
        if ($reshow !== null) {
            $this->reshow($reshow);
        }

    }


    function loadExcelIntoListsDoc($file,$doc) {
        include_once('PHPExcel/PHPExcel.php');        
        if (! class_exists('PHPExcel',false)) {
            I2CE::raiseError("Please install PHPExcel (http://phpexcel.codeplex.com/):\n\tpear channel-discover pear.pearplex.net\n\tpear install pearplex/PHPExcel\n");
            $this->userMessage("Please install PHPExcel (http://phpexcel.codeplex.com/):\n\tpear channel-discover pear.pearplex.net\n\tpear install pearplex/PHPExcel\n");
            return false;
        }
         //read in the existing data lists from inputs
        if (!is_readable($file) ) {
            $this->userMessage("The file $file is not readable\n");
            return false;
        }
        $inputFileType = PHPExcel_IOFactory::identify($file);

        $objReader = PHPExcel_IOFactory::createReader($inputFileType);
        $objReader->setReadDataOnly(true);
        $objReader->setLoadAllSheets();
        $objPHPExcel = $objReader->load($file);
        foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
            $title = $worksheet->getTitle();

            $row =  $worksheet->getHighestRow() ;
            $col = PHPExcel_Cell::columnIndexFromString( $worksheet->getHighestColumn() );
            if ($row <= 1 || $col <= 0) {
                continue;
            }
            $baseNode = $doc->createElement('List');
            $baseNode->setAttribute('name',$title);
            $doc->documentElement->appendChild($baseNode);
            //get headers
            $headers = array();
            for ($c = 0; $c < $col; $c++) {
                $headers[$c] =  trim($worksheet->getCellByColumnAndRow($c,1)->getValue());
                //do something
            }

            //get remaining
            for ($r = 2; $r <= $row; $r++) {
                $rowNode = $doc->createElement('row' );
                $baseNode->appendChild($rowNode);
                foreach ($headers as $c=>$header) {
                    $val = $worksheet->getCellByColumnAndRow($c,$r)->getValue();
                    $fieldNode = $doc->createElement('field');
                    $fieldNode->setAttribute('column',$header);
                    $rowNode->appendChild($fieldNode);
                    $fieldNode->appendChild($doc->createTextNode(trim($val)));
                }
            }
        }
        return true;
    }


    protected function loadCSVIntoListsDoc($file,$doc) {
        if (!is_readable($file) || ($fp = fopen($file,"r")) === false || !is_resource($fp)) {
            $this->userMesage("The file $file is not readable\n");
            return $doc;
        }
    
        $in_file_sep = false;
        foreach (array("\t",",") as $sep) {
            fseek($fp,0);
            $headers = fgetcsv($fp, 4000, $sep);
            if ( $headers === FALSE|| count($headers) < 2) {
                continue;
            }
            $in_file_sep = $sep;
        }
        if (!$in_file_sep) {
            $this->userMessage("Could not get headers for $file");
            return false;
        }    
    
        $baseNode = $doc->createElement('List');
        $baseNode->setAttribute('name','mdn_import');
        $doc->documentElement->appendChild($baseNode);

        while ( ($input_data = fgetcsv($fp,4000,$in_file_sep)) !== false) {
            if (count($input_data) === 0) {
                continue;
            }
            $rowNode = $doc->createElement('row' );
            $baseNode->appendChild($rowNode);
            foreach ($headers as $col=>$header) {
                $fieldNode = $doc->createElement('field');
                $fieldNode->setAttribute('column',$header);
                $rowNode->appendChild($fieldNode);
                if (!array_key_exists($col,$input_data)) {
                    $val ='';
                } else {
                    $val = $input_data[$col];
                }
                $fieldNode->appendChild($doc->createTextNode($val));
            }
        }
        fclose($fp);
        return true;
    }
    
    protected function actionLoad($transform = false) {
        if ( $csv_url = $this->request('csv_url')) {
            if (!$data = file($csv_url)) {
                I2CE::raiseError("Could not load url $csv_url");
                $this->userMessage("Could not load url $csv_url");
                return false;
            }
            $tmp_doc = tempnam(sys_get_temp_dir(), 'MDN_UPLOAD');
            if (!file_put_contents($tmp_doc,$data)) {
                $this->userMessage("Could not save data file");
                return false;
            }
            $doc = new DOMDocument('1.0', 'UTF-8');
            $doc->loadXML("<Lists/>");
            $doc->documentElement->setAttribute('version','0');
            $doc->documentElement->setAttribute('year',date("Y"));
            $doc->documentElement->setAttribute('month',date("m"));
            $doc->documentElement->setAttribute('day',date("d"));
            $doc->documentElement->setAttribute('timestampUnix',time());
            $doc->documentElement->setAttribute('timestampMysql',date ("Y-m-d H:i:s"));
            $doc->documentElement->setAttribute('date',date(DATE_RFC822));

            $parts = explode(".", $csv_url);
            end($parts);
            if ( strtolower(current($parts)) == 'csv') {
                if (! $this->loadCSVIntoListsDoc($tmp_doc,$doc)) {
                    $this->userMessage("Could not CSV: " . $_FILES['csv']['name']);
                    return false;
                }
            } else if (! $this->loadExcelIntoListsDoc($tmp_doc,$doc)) {
                $this->userMessage("Could not spreadsheet: " . $_FILES['csv']['name']);
                return false;
            }

        } else if ( $xml_url = $this->request('xml_url')) {
            if (!$data = file($xml_url)) {
                I2CE::raiseError("Could not load url $xml_url");
                $this->userMessage("Could not load url $xml_url");
                return false;
            }
            $doc = tempnam(sys_get_temp_dir(), 'MDN_UPLOAD');
            if (!file_put_contents($doc,$data)) {
                $this->userMessage("Could not save data file");
                return false;
            }
        } else if (array_key_exists('csv',$_FILES)
            && is_array($_FILES['csv']) 
            && ! ( array_key_exists('error',$_FILES['csv']) && $_FILES['csv']['error'] > 0)) {
            $doc = new DOMDocument('1.0', 'UTF-8');
            $doc->loadXML("<Lists/>");
            $doc->documentElement->setAttribute('version','0');
            $doc->documentElement->setAttribute('year',date("Y"));
            $doc->documentElement->setAttribute('month',date("m"));
            $doc->documentElement->setAttribute('day',date("d"));
            $doc->documentElement->setAttribute('timestampUnix',time());
            $doc->documentElement->setAttribute('timestampMysql',date ("Y-m-d H:i:s"));
            $doc->documentElement->setAttribute('date',date(DATE_RFC822));

            $parts = explode(".", $_FILES["csv"]["name"]);
            end($parts);
            if ( strtolower(current($parts)) == 'csv') {
                if (! $this->loadCSVIntoListsDoc($_FILES['csv']['tmp_name'],$doc)) {
                    $this->userMessage("Could not CSV: " . $_FILES['csv']['name']);
                    return false;
                }
            } else if (! $this->loadExcelIntoListsDoc($_FILES['csv']['tmp_name'],$doc)) {
                $this->userMessage("Could not spreadsheet: " . $_FILES['csv']['name']);
                return false;
            }
        } else if (array_key_exists('xml',$_FILES)
            && is_array($_FILES['xml']) 
            && ! ( array_key_exists('error',$_FILES['xml']) && $_FILES['xml']['error'] > 0)) {
            $doc  = $_FILES['xml']['tmp_name'];
        } else {
            I2CE::raiseError("No load document specified");
            $this->userMessage("No load document specified");
            return false;
        }
        $erase = ($this->request('erase') == 1);
        if (! $this->loadMDTemplate($doc,$transform,$erase)) {
            I2CE::raiseError("Load failed");
            $this->userMessage("Could not load transformed file into MD");
            return false;
        }
        
        return true;
    }


    protected function loadMDTemplate($doc,$transform  = false, $erase = false) { //doc is either a file name or a DOMDocument

        if ($transform) {

            //transform
            if (is_string($doc )) {
                $file = $doc;
                $doc = new DOMDocument();
                if ( !( $contents  = file_get_contents($file))) {
                    $this->userMessage("Could not load source file");
                    return false;                    
                }
                if (!$doc->loadXML($contents)) {
                    $this->userMessage("Could not load file source contents");
                    return false;
                }
            }
            if (!$doc instanceof DOMDocument) {
                $this->userMessage("Could not load xml into document");
                return false;
            }


            $proc = new XSLTProcessor();
            $xslt_doc = new DOMDocument();
            if (!$xslt_doc->loadXML($transform)) {
                $this->userMessage("Could not load transform: " . $_FILES['xsl']['name']);
                return false;
            }
            if (!  $proc->importStylesheet($xslt_doc)) {
                $this->userMessage("Could not import style sheet");
                return false;
            }
            $trans_doc = new DOMDocument('1.0', 'UTF-8');
            $trans_doc->appendChild( $trans_doc->importNode($doc->documentElement,true));
            if ( ($trans_out =   $proc->transformToXML($trans_doc)) === false) {
                $this->userMessage("Could not transform accoring to xsl");
                return false;
            }
        } else {
            $trans_doc = $doc;
        }
        if ($trans_doc instanceof DOMDocument) {
            $temp_file = tempnam(sys_get_temp_dir(), 'MDN_UPLOAD');
            if (!file_put_contents($temp_file,$trans_out)) {
                $this->userMessage("Could not save transformed files");
                return false;
            }
        } else {
            $temp_file = $trans_doc; 
        }
 

        $template = new I2CE_MagicDataTemplate();
        $template->setVerboseErrors(true);
        if (!$template->loadRootFile($temp_file)) {
            I2CE::raiseError("Unable to load transformed file as Magic Data");
            $this->userMessage("Unable to load transformed file as Magic Data");
            return false;
        }
        if (!$template->validate()) {
            I2CE::raiseError("Unable to validate transformed file as Magic Data");
            $this->userMessage("Unable to validate transformed file as Magic Data");
            return false;
        }
        $store = new I2CE_MagicDataStorageMem;
        $mem_config = I2CE_MagicData::instance("mdn_load");
        $mem_config->addStorage($store);
        $nodeList = $template->query("/configurationGroup");
        if (!$nodeList instanceof DOMNodeList || $nodeList->length == 0) {
            $nodeList = $template->query("/I2CEConfiguration/configurationGroup");
            //perhaps we really need to do something more if this is a module
        }
        foreach ( $nodeList as $configNode) {
            $locale = false;
            $status = $template->getDefaultStatus();
            if ($configNode->hasAttribute('locale')) {
                $locale = $configNode->getAttribute('locale');
            }
            $vers = '0';
            if ($template->setConfigValues($configNode,$mem_config, $status, $vers) === false ){
                I2CE::raiseError("Could not load configuration values");
                $this->userMessage("Could not load configuration values");
                return false;
            }
        }
        //I2CE::raiseError(print_r($mem_config->getAsArray(),true));
        if ($erase) {
            $this->config->eraseChildren();
        }

        $merges =$template->getMerges();
        foreach ($merges as $path=>$merge) {
            if ($this->config->is_scalar($path)) {
                I2CE::raiseError("Trying to merge arrays into $path where target is scalar valued. Skipping");
                continue;
            }
            if ($mem_config->is_scalar($path)) {
                I2CE::raiseError("Trying to merge arrays into $path where source is scalar valued. Skipping");
                continue;
            }
            $old_arr = $this->config->getAsArray($path);
            $new_arr = $mem_config->getAsArray($path);
            $mem_config->__unset($path);
            if (!is_array($old_arr)) { //in case the target did not exist
                $old_arr = array();
            }
            if (!is_array($new_arr)) { //in case no values were set for the source
                $new_arr = array();
            }
            switch ($merge) {
            case 'uniquemerge':
                $new_arr = I2CE_Util::array_unique(array_merge($old_arr,$new_arr));
                break;
            case 'merge':
                $new_arr =array_merge($old_arr,$new_arr);
                break;
            case 'mergerecursive':
                I2CE_Util::merge_recursive($old_arr, $new_arr);
                $new_arr = $old_arr;
                break;
            }
            $this->config->__unset($path);
            $this->config->$path = $new_arr;
        }
        //we took care of all array merges.  anything that is left is an overwrite.
        foreach ($mem_config as $k=>$v) {
            if (is_scalar($v) && $mem_config->is_translatable($k) && (!$this->config->is_parent($k)))  {
                $this->config->setTranslatable($k);
                $translations = $mem_config->traverse($k,true,false)->getTranslations();
                foreach ($translations as $locale => $trans) {
                    if (strlen($trans) == 0) {
                        continue;
                    }
                    $this->config->setTranslation($locale,$trans,$k);
                }
            } else {
                $this->config->$k->setValue($v,null,false);
            }
            if ($this->config->$k instanceof I2CE_MagicDataNode) {
                //free up some memory.  
                $this->config->$k->unpopulate(true);
            }
        }
        return true;
    }

    protected function displayConfig($config,$magicNode,$full) {
        foreach ($config as $key=>$child) {
            if($config->is_parent($key)) {
                $path = 'magicDataBrowser/show/' . $config->getPath(false);
                $node = $this->template->loadFile("browser_node.html",'li');
                $magicNode->appendChild($node);
                $this->template->setDisplayDataImmediate('erase_action','magicDataBrowser/erase/'. $config->getPath(false) . "/$key",$node);
                $this->template->setDisplayDataImmediate('trans_action','magicDataBrowser/trans/'. $config->getPath(false) . "/$key",$node);
                $subNode = $this->template->query( "./descendant-or-self::node()[@name='magic_data_list']",$node);
                $this->template->setDisplayDataImmediate('browser_magic_data_key_path',$path.'/'.$key,$node);
                $this->template->setDisplayDataImmediate('browser_magic_data_key',$key,$node); 
                if ($full && $subNode->length > 0) {
                    $this->displayConfig($child,$subNode->item(0),$full); 
                } else {
                    $valueNode = $this->template->getElementById('browser_magic_data_value',$node);
                    if (!$valueNode instanceof DOMElement) {
                        continue;
                    }
                    if ($this->hasAjax()) {
                        $replacementNode = $this->template->createElement('span',
                                                                          array('class'=>'clickable',
                                                                                'id'=>"magic_data_list_$path/$key"
                                                                              ), 
                                                                          'show values' 
                            ); 
                        $this->addAjaxUpdate("magic_data_list_$path/$key",
                                             "magic_data_list_$path/$key",
                                             'click',
                                             "$path/$key",
                                             'magic_data_list'
                            );
                        $this->addAjaxCompleteFunction("magic_data_list_$path/$key",
                                                             "$('magic_data_list_$path/$key').removeClass('clickable')"
                            );
                    } else {
                        $replacementNode = $this->template->createTextNode('*******');
                    }
                    $valueNode->parentNode->replaceChild($replacementNode,$valueNode);
                }
            } else    if($config->is_scalar($key)) {
                //just display the value
                $locale = '';
                if ($config->traverse($key,false,false)->hasAttribute('binary') && $config->traverse($key,false,false)->getAttribute('binary')) {
                    $bin = 1;
                    $valueNode = $this->template->loadFile("browser_binary_node.html",'li');
                    $this->template->setDisplayDataImmediate('browser_magic_data_value_binary_link','magicDataBrowser/download/'. $config->getPath(false) . "/$key",$valueNode);
                } else {
                    $bin = 0;
                    $valueNode = $this->template->loadFile("browser_value_node.html",'li');
                    if ($config->is_translatable($key)) {
                        if ($this->request_exists('locale')) {
                            $locale = $this->request('locale');
                        } else {
                            $locales = I2CE_Locales::getPreferredLocales();
                            reset($locales);
                            $locale = current($locales);
                        }
                        $child = $config->getTranslation($locale,true,$key);
                        $locale = ' [' . $locale . ']';
                    }
                    $this->template->setDisplayDataImmediate('browser_magic_data_key',$key,$valueNode);
                    $this->template->setDisplayDataImmediate('browser_magic_data_value',$child,$valueNode);
                
                }
                $this->template->setDisplayDataImmediate('bin_node',$bin,$valueNode);
                $magicNode->appendChild($valueNode);
                $this->template->setDisplayDataImmediate('browser_magic_data_key_label',$key . $locale,$valueNode);
                $change_value_action = 'index.php/magicDataBrowser/set/'. $config->getPath(false) ;
                $this->template->setDisplayDataImmediate('change_value_action',$change_value_action,$valueNode);
                if (!$bin && ($vNode = $this->template->getElementByName('browser_magic_data_value',0,$valueNode)) instanceof DOMNode) {
                    //if ($this->post_exists('browser_magic_data_value') && $this->post_exists('browser_magic_data_key')) {
                    $js = "var e = $(this); 
e.setStyle('background-color','yellow');
new Request({
'useSpinner':true,
url: '{$change_value_action}',
method: 'post',
'data':{'browser_magic_data_value': e.get('value'),'browser_magic_data_key':'$key'},
onComplete: function(response) { 
 e.setStyle('background-color','white');
}
}).send();";
                    $vNode->setAttribute('onchange',$js);
                }
                $this->template->setDisplayDataImmediate('erase_action','magicDataBrowser/erase/'. $config->getPath(false) . "/$key",$valueNode);
                $this->template->setDisplayDataImmediate('trans_action','magicDataBrowser/trans/'. $config->getPath(false) . "/$key",$valueNode);
                $this->template->setDisplayDataImmediate('upload_action','magicDataBrowser/upload/'. $config->getPath(false) . "/$key",$valueNode);
                $this->template->setDisplayDataImmediate('upload_binary_action','magicDataBrowser/upload_binary/'. $config->getPath(false) . "/$key",$valueNode);
                $this->template->setDisplayDataImmediate('load_action','magicDataBrowser/load/'. $config->getPath(false) . "/$key",$valueNode);
                $keys = array();
                foreach (I2CE::getConfig()->getKeys("/modules/magicDataBrowser/transforms") as $key) {
                    $keys[$key] = $key;
                }
                $this->template->setDisplayDataImmediate('transform_key',$keys,$valueNode);
                $this->useDropDown();
            } else if( $config->is_indeterminate($key)) {
                $valueNode = $this->template->loadFile("browser_value_node_notset.html",'li');
                $magicNode->appendChild($valueNode);
                $this->template->setDisplayDataImmediate('browser_magic_data_key',$key,$valueNode);
                $this->template->setDisplayDataImmediate('upload_action','magicDataBrowser/upload/'. $config->getPath(false) . "/$key",$valueNode);
                $this->template->setDisplayDataImmediate('upload_binary_action','magicDataBrowser/upload_binary/'. $config->getPath(false) . "/$key",$valueNode);
                $this->template->setDisplayDataImmediate('load_action','magicDataBrowser/load/'. $config->getPath(false) . "/$key",$valueNode);
                $this->template->setDisplayDataImmediate('erase_action','magicDataBrowser/erase/'. $config->getPath(false) . "/$key",$valueNode);
                $this->template->setDisplayDataImmediate('trans_action','magicDataBrowser/trans/'. $config->getPath(false) . "/$key",$valueNode);
                $this->template->setDisplayDataImmediate('parent_action','magicDataBrowser/parent/'. $config->getPath(false) . "/$key",$valueNode);
                $this->template->setDisplayDataImmediate('scalar_action','magicDataBrowser/scalar/'. $config->getPath(false) . "/$key",$valueNode);
                $keys = array();
                foreach (I2CE::getConfig()->getKeys("/modules/magicDataBrowser/transforms") as $key) {
                    $keys[$key] = $key;
                }
                $this->template->setDisplayDataImmediate('transform_key',$keys,$valueNode);
                $this->useDropDown();
            } else {
                //echo "weird $key\n";
            }
        }
        $valueNode = $this->template->loadFile("browser_add_node.html",'li');        
        if ($valueNode instanceof DOMElement) {
            $magicNode->appendChild($valueNode);
            $this->template->setDisplayDataImmediate('add_key_action','magicDataBrowser/add/'. $config->getPath(false),$valueNode);
            $this->template->setDisplayDataImmediate('add_scalar_key_action','magicDataBrowser/add_scalar/'. $config->getPath(false),$valueNode);
            $this->template->setDisplayDataImmediate('add_scalar_binary_key_action','magicDataBrowser/add_scalar_binary/'. $config->getPath(false),$valueNode);
            $this->template->setDisplayDataImmediate('add_parent_key_action','magicDataBrowser/add_parent/'. $config->getPath(false),$valueNode);
        }
        $url_tail = implode('/',$this->config_path);
        if ($this->get_exists('full')) {
            $url_tail .= '?full';
        }
        $this->template->setDisplayDataImmediate('caller',$url_tail,$magicNode);
    }



    function displayConfigMini($config,$magicNode,$full) {
        foreach ($config as $key=>$child) {
            if($config->is_parent($key)) {
                $path = 'magicDataBrowser/mini/' . $config->getPath(false);
                $node = $this->template->loadFile("browser_node_mini.html",'li');
                $magicNode->appendChild($node);
                $this->template->setDisplayDataImmediate('erase_action','magicDataBrowser/erase/'. $config->getPath(false) . "/$key",$node);
                $subNode = $this->template->query( "./descendant-or-self::node()[@name='magic_data_list']",$node);
                $this->template->setDisplayDataImmediate('browser_magic_data_key_path',$path.'/'.$key,$node);
                $this->template->setDisplayDataImmediate('browser_magic_data_key',$key,$node); 
                if ($full && $subNode->length > 0) {
                    $this->displayConfigMini($child,$subNode->item(0),$full); 
                } else {
                    $valueNode = $this->template->getElementById('browser_magic_data_value',$node);
                    if (!$valueNode instanceof DOMElement) {
                        continue;
                    }
                    if ($this->hasAjax()) {
                        $replacementNode = $this->template->createElement('span',
                                                                          array('class'=>'clickable',
                                                                                'id'=>"magic_data_list_$path/$key"
                                                                              ), 
                                                                          'show values' 
                            ); 
                        $this->addAjaxUpdate("magic_data_list_$path/$key",
                                             "magic_data_list_$path/$key",
                                             'click',
                                             "$path/$key",
                                             'magic_data_list'
                            );
                        $this->addAjaxCompleteFunction("magic_data_list_$path/$key",
                                                             "$('magic_data_list_$path/$key').removeClass('clickable')"
                            );
                    } else {
                        $replacementNode = $this->template->createTextNode('*******');
                    }
                    $valueNode->parentNode->replaceChild($replacementNode,$valueNode);
                }
            } else    if($config->is_scalar($key)) {
                //just display the value
                if ($config->traverse($key,false,false)->hasAttribute('binary') && $config->traverse($key,false,false)->getAttribute('binary')) {
                    $valueNode = $this->template->loadFile("browser_binary_node_mini.html",'li');
                    $this->template->setDisplayDataImmediate('browser_magic_data_value_binary_link','magicDataBrowser/download/'. $config->getPath(false) . "/$key",$valueNode);
                } else {
                    $valueNode = $this->template->loadFile("browser_value_node_mini.html",'li');
                    $this->template->setDisplayDataImmediate('browser_magic_data_value',$child,$valueNode);
                }
                $magicNode->appendChild($valueNode);
                $this->template->setDisplayDataImmediate('browser_magic_data_key',$key,$valueNode);
            } else if( $config->is_indeterminate($key)) {
                $valueNode = $this->template->loadFile("browser_value_node_notset_mini.html",'li');
                $magicNode->appendChild($valueNode);
                $this->template->setDisplayDataImmediate('browser_magic_data_key',$key,$valueNode);
                $this->template->setDisplayDataImmediate('parent_action','magicDataBrowser/parent/'. $config->getPath(false) . "/$key",$valueNode);
                $this->template->setDisplayDataImmediate('scalar_action','magicDataBrowser/scalar/'. $config->getPath(false) . "/$key",$valueNode);
            } else {
                //echo "weird $key\n";
            }
        }       
    }


    protected function actionDisplayConfig() {
        $this->template->setBodyId( "magicDataBrowserPage" );
        $this->template->addHeaderLink("MagicDataBrowser.css");
        if ($this->hasAjax() === true) {
            $this->template->addHeaderLink("mootools-core.js");
        }
        $path =  $this->config->getPath(false);
        $magicNode = $this->template->addFile( "browser.html", "div" );
        $this->template->setAttribute( "class", "active", "menuConfigure", "a[@href='configure']" );
        $this->template->appendFileById( "menu_configure.html", "ul", "menuConfigure" );
        $this->template->setAttribute( "class", "active", "menuBrowseMagicData", "a[@href='magicDataBrowser/{$this->page}']" );
        if (I2CE_ModuleFactory::instance()->isEnabled('magicDataExport')) {
            $exportNode = $this->template->addFile( "magicdata_export_controls.html", "div" );
            if ($exportNode instanceof DOMNode) {
                $this->template->addFormWorm('magicdata_export_form');
                $this->template->setDisplayDataImmediate('config_path', $path, $exportNode);

                $base_version  = "1.0";
                $site_module = false;
                if ( (I2CE::getConfig()->setIfIsSet($site_module,"/config/site/module"))
                     && I2CE_MagicDataNode::checkKey($site_module)) {
                    I2CE::getConfig()->setIfIsSet($base_version,"/config/data/" . $site_module .'/version');
                }

                $this->template->setDisplayDataImmediate('version', $base_version . '.' . date("Y") . '.' . date("m") . '.' . date("d"), $exportNode);
            }
        }
        if ($this->config->getPath(false) == '') {
            $this->template->setDisplayDataImmediate('load_action','magicDataBrowser/load/',$magicNode);
            $this->template->setDisplayDataImmediate('root_node',1);
        } else {
            $this->template->setDisplayDataImmediate('root_node',0);
        }
        $config_path_node = $this->template->getElementById('config_path_display');
        if ($config_path_node instanceof DOMNode) {
            $path_components = explode('/',$path);
            $t_path = '';
            foreach ($path_components as $path_component) {
                $t_path .=  $path_component .'/';
                $config_path_node->appendChild($this->template->createTextNode(' '));
                $attrs = array('href'=>'magicDataBrowser/' . $this->page   . $t_path);
                $config_path_node->appendChild($this->template->createElement('a',$attrs,$path_component .'/'));
            }

        }
        if (!$this->get_exists('full')) { 
            $this->template->setDisplayDataImmediate('full_view','magicDataBrowser/' . $this->page .   $path .'?full' , $magicNode); 
            $this->template->setDisplayDataImmediate('compressed_view','');
            if ($this->page == 'mini') {
                $this->template->setDisplayDataImmediate('nomini_view','magicDataBrowser/show'.   $path  , $magicNode); 
                $this->template->setDisplayDataImmediate('mini_view','');
            } else {
                $this->template->setDisplayDataImmediate('mini_view','magicDataBrowser/mini'.   $path  , $magicNode); 
                $this->template->setDisplayDataImmediate('nomini_view','');
            }
        } else {
            $this->template->setDisplayDataImmediate('compressed_view','magicDataBrowser/' . $this->page  . $path  , $magicNode); 
            $this->template->setDisplayDataImmediate('full_view','');
            if ($this->page == 'mini') {
                $this->template->setDisplayDataImmediate('nomini_view','magicDataBrowser/show'.   $path  .'?full' , $magicNode); 
                $this->template->setDisplayDataImmediate('mini_view','');
            } else {
                $this->template->setDisplayDataImmediate('mini_view','magicDataBrowser/mini'.   $path .'?full' , $magicNode); 
                $this->template->setDisplayDataImmediate('nomini_view','');
            }
        }
        if ($path !== $this->config->traverse('/')->getPath(false)) {
            $actionNodeList = $this->template->query("//*[@id='magicdata_erase']");
            for ($i=0; $i < $actionNodeList->length; $i++) {
                $actionNodeList->item($i)->setAttribute('action','magicDataBrowser/erase/' . $path);
            }
        } else {
            $this->template->removeNodeById('magicdata_erase');
        }
        $actionNodeList = $this->template->query("//*[@id='magicdata_export']");
        for ($i=0; $i < $actionNodeList->length; $i++) {
            $actionNodeList->item($i)->setAttribute('action','magicDataExport/export/' . $path);
        }
        $magicListNode =$this->template->query( "./descendant-or-self::node()[@name='magic_data_list']",$magicNode);
        if ($magicListNode->length > 0) {
            if ($this->page == 'mini') {
                $this->displayConfigMini($this->config,$magicListNode->item(0) ,$this->get_exists('full'));  
            } else {
                $this->displayConfig($this->config,$magicListNode->item(0) ,$this->get_exists('full'));  
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
