<?php
/*
 * © Copyright 2007, 2008 IntraHealth International, Inc.
 * 
 * This File is part of iHRIS
 * 
 * iHRIS is free software; you can redistribute it and/or modify
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
 */
/**
 * Translate Templates
 *
 * @package I2CE
 * @subpackage Core
 * @author Carl Leitner <litlfred@ibiblio.org>
 * @copyright Copyright &copy; 2013, IntraHealth International, Inc. 
 * @version 1.0
 */


require_once (dirname(__FILE__) . DIRECTORY_SEPARATOR . 'CLI.php');
require_once (dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'I2CE_MagicDataTemplate.php');
require_once (dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'I2CE_FileSearch.php');



if (count($arg_files) != 2) {
    usage("Please specify the name of a spreadsheet to process and an .xml data module to process");
}
$dir = getcwd();
reset($arg_files);
$xml_file = false;
$spread_file = false;
foreach ($arg_files as $file) {
    if($file[0] == '/') {
	$file = realpath($file);
    } else {
	$file = realpath($dir. '/' . $file);
    }
    if (!is_readable($file)) {
	usage("File is not readable: " . $file );	
    }

    $file_ext = strtolower(substr($file, strrpos($file, '.') + 1));
    if ($file_ext == 'csv') {
	//although CSV can be processed by PHPExcel, we keep this separate in case PHPExcel cannot be installed, we can still export the file as a CSV and process it
	$data_file = new CSVDataFile($file);
    } else if (substr($file_ext,0,3) == 'xls') {
	$data_file = new ExcelDataFile($file);	
    } else {
	$xml_file = $file;
    }
}

if (!$xml_file || !$data_file) {
    usage("Please specify the name of a spreadsheet to process and an .xml data module to process");
}
$form = trim(ask("What form are you remapping?"));

$old_head =false;
$new_head = false;
$headers = $data_file->getHeaders();
$old_head = chooseMenuIndex("Please select the column with the old values that will be remapped from:",$headers);
$new_head = chooseMenuIndex("Please select the column with the new values that will be remapped to:",$headers);

$remaps = array();
while($data_file->hasDataRow()) {
    if (! is_array($row = $data_file->getDataRow())
	|| !array_key_exists($old_head,$row)
	|| !array_key_exists($new_head,$row)
	) {
	continue;
    }
    $remaps[trim(strtoupper($row[$old_head]))] = trim($row[$new_head]);
}
//print_r($remaps);

$template = new I2CE_MagicDataTemplate();
if (!$template->loadRootFile($xml_file)) {
    usage("You need to specify a data .xml file");
}


if ( ! ($versionNodes = $template->query('//I2CEConfiguration/metadata/version')) instanceof DOMNodeList  || !($versionNodes->length) == 1) {
    usage("Could not find current version in data .xml file");
}
$versionNode = $versionNodes->item(0);
$version_comps = explode(".", trim($versionNode->textContent));
end($version_comps);
$key = key($version_comps);
$version_comps[$key]++;
$version = implode(".",$version_comps);
while ($versionNode->hasChildNodes()) {
    $versionNode->removeChild($versionNode->firstChild);
}
$versionNode->appendChild($template->createTextNode($version));
//echo "Version = $version\n";


$ids = array();
foreach ($template->query('//*[@name="fields"]/configuration[@name="name"]/value') as $nameNode) {
    $name = strtoupper(trim($nameNode->textContent));
    $ids[$name] = $nameNode->parentNode->parentNode->parentNode->getAttribute('name');
}

//print_r($ids);


foreach ($template->query('//*[@name="fields"]') as $fieldNode) {
    $nameNodes = $template->query('.//*[@name="name"]/value',$fieldNode);
    if (!$nameNodes instanceof DOMNodeList 
	|| !$nameNodes->length == 1) {
	continue;
    }
    $old_value = trim(strtoupper($nameNodes->item(0)->textContent));
    if (!array_key_exists($old_value,$remaps) 
	|| !array_key_exists($remaps[$old_value],$ids)) {
	continue;
    }
    $remapNodes = $template->query('.//*[@name="remap"]',$fieldNode);
    if ($remapNodes instanceof DOMNodeList)  {
	foreach ($remapNodes as $remapNode) {	    
	    $remapNode->parentNode->removeChild($remapNode);
	}
    }
    $value = $form. '|' . $ids[$remaps[$old_value]];
    $fieldNode->appendChild($remapNode =$template->createElement('configuration',array('name'=>'remap')));
    $remapNode->appendChild($template->createElement('version',array(),$version));
    $remapNode->appendChild($valueNode = $template->createElement('value',array(),$value));
}



file_put_contents($xml_file,$template->getDisplay());



/*************************************************************************
 *
 *  Classes to handle reading headers and rows from data files
 *
 ************************************************************************/


abstract class DataFile {
    
    /**
     * @var protected string $file
     */
    protected $file;    
    
    abstract public function getDataRow();
    abstract public function hasDataRow();
    abstract public function getHeaders();
    public function __construct($file) {
        $this->file = $file;
    }
    
    /**
     * get the file name for the file we are going to deal with
     * @returns string
     */
    public function getFileName() {
        return $this->file;
    }
    
    /**
     * closes a file that was open
     */
    public function close() {

    }

}

class ExcelDataFile extends DataFile {    

    protected $rowIterator;

    public function __construct($file) {
        parent::__construct($file);
        include_once('PHPExcel/PHPExcel.php'); 
        if (!class_exists('PHPExcel',false)) {
            usage("You must have PHPExcel installed to load excel spreadsheets");
        }
        $readerType = PHPExcel_IOFactory::identify($this->file);
        $reader = PHPExcel_IOFactory::createReader($readerType);
        $reader->setReadDataOnly(false);
        $excel = $reader->load($this->file);        
        $worksheet = $excel->getActiveSheet();
        $this->rowIterator = $worksheet->getRowIterator();
    }

    
    /**
     * confirms if the excel file we are reading has rows with data
     * @returns boolean
     */
    public function hasDataRow() {
        return $this->rowIterator->valid();
    }

    /**
     * reads the file to get the headers
     * @returns array
     */
    public function getHeaders() {
        $this->rowIterator->rewind();
        $row = $this->rowIterator->current();
        if (!$this->rowIterator->valid()) {
            I2CE::raiseMessage("Could not find header row");
            return false;
        }
        return $this->_readRow($row);
    }
    
    /**
     * reads one data row at a time
     * @returns array
     */
    public function getDataRow() {
        $this->rowIterator->next();
        if (!$this->rowIterator->valid()) {
            return false;
        }
        return $this->_readRow($this->rowIterator->current());
    }
    
    /**
     * read the entire row and parse for data
     * @param string $row. If not an excel worksheet row, issue a message and return false
     * @returns array
     */
    protected function _readRow($row) {
        if (!$row instanceof PHPExcel_Worksheet_Row) {
            I2CE::raiseMessage("Invalid row object" . get_class($row));
            return false;
        }
        $cellIterator = $row->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(false);
        $data = array();
        foreach ($cellIterator as $cell) {
            $data[] =  $cell->getValue();
        }
        return $data;
    }


}
class CSVDataFile extends DataFile {
    protected $fp;
    protected $in_file_sep = false;
    protected $file_size = false;
    public function __construct($file) {
        parent::__construct($file);
        $this->filesize = filesize($file);
        if ( ($this->fp = fopen($this->file,"r")) === false) {
            usage("Please specify the name of a spreadsheet to import: " . $file . " is not openable");
        }
    }
    
    /**
     * checks to confirm if the file has rows of data
     * @returns string
     */
    public function hasDataRow() {
        $currpos =  ftell($this->fp);
        if ($currpos === false) {
            return false;
        } else {
            return ($currpos < $this->filesize);
        }
    }
    
    /**
     * reads all the column headers from the CSV file
     * @returns array
     */
    public function getHeaders() {
        $this->in_file_sep = false;
        fseek($this->fp,0);
        foreach (array("\t",",",";") as $sep) {
            $headers = fgetcsv($this->fp, 4000, $sep);
            if ( $headers === FALSE|| count($headers) < 2 || !simple_prompt("Do these look like the correct headers?\n". print_r($headers,true))) {
                fseek($this->fp,0);
                continue;
            }
            $this->in_file_sep = $sep;
            break;
        }
        if (!$this->in_file_sep) {
            die("Could not get headers\n");
        }
        foreach ($headers as &$header) {
            $header = trim($header);
        }
        unset($header);
        return $headers;
    }

    public function getDataRow() {
        return $data = fgetcsv($this->fp, 4000, $this->in_file_sep);
    }
    
    /**
     * closes the open CSV file
     */
    public function close() {
        fclose($this->fp);
    }
}

