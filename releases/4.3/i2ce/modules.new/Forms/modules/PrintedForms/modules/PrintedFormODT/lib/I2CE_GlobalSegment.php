<?php
/**
* © Copyright 2010 IntraHealth International, Inc.
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
* @subpackage I2CE
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.0.4
* @since v4.0.4
* @filesource 
*/ 
/** 
* Class I2CE_PrintedForm_Render
* 
* @access public
*/

class I2CE_GlobalSegment extends I2CE_Segment {
    /**
     * Constructor
     *
     * @param string $name name of the segment to construct
     * @param string $xml XML tree of the segment
     */
    public function __construct($name, $xml, $odf){
        $reg = "/\<office:body.*?\>(.*)\<\\/office:body/";
	preg_match($reg, $xml,$matches);
	parent::__construct($name,$matches[1],$odf);
    }


    /**
     * Replace variables of the template in the XML code
     * All the children are also called
     *
     * @return string
     */
    public function merge()    {
        $xmlParsed = str_replace(array_keys($this->vars), array_values($this->vars), $this->xml);
        if ($this->hasChildren()) {
            foreach ($this->children as $child) {
                $xmlParsed = str_replace($child->xml, ($child->xmlParsed=="")?$child->merge():$child->xmlParsed, $xmlParsed);
                $child->xmlParsed = '';
            }
        }
        $this->file->open($this->odf->getTmpfile());
        foreach ($this->images as $imageKey => $imageValue) {
			if ($this->file->getFromName('Pictures/' . $imageValue) === false) {
				$this->file->addFile($imageKey, 'Pictures/' . $imageValue);
			}
        }
        $this->file->close();	
	$this->xmlParsed .= $xmlParsed  . $this->segment_break;
	return $this->xmlParsed;
    }

    public function setSegementBreak($break) {
	$this->segment_break = $break;
    }
    protected $segment_break = '<text:p text:style-name="P1"/>';

    public function setVars($key, $value, $encode = true, $charset = 'ISO-8859')    {
	$skey = $this->odf->getConfig('DELIMITER_LEFT') . $key . $this->odf->getConfig('DELIMITER_RIGHT');
        if (strpos($this->xml, $skey) === false) {
            throw new SegmentException("var $skey not found in\n". $this->xml);
        }
	$value = $encode ? htmlspecialchars($value) : $value;
	$value = ($charset == 'ISO-8859') ? utf8_encode($value) : $value;
        $this->vars[$skey] = str_replace("\n", "<text:line-break/>", $value);
        return $this;
    }



}

?>