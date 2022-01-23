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


class I2CE_Odf extends Odf {

    protected $stylesXml;			// To store content of styles.xml file for headers


    /**
     * Class constructor
     *
     * @param string $filename the name of the odt file
     * @throws OdfException
     */
    public function __construct($file, $config = array()){
	if (is_resource($file)) {
	    $tmp_filename = tempnam(sys_get_temp_dir(), 'ODT_');
	    file_put_contents($tmp_filename,$file);
	    parent::__construct($tmp_filename,$config);
	} else {
	    $tmp_filename = $file;
	    parent::__construct($file,$config);
	}
	$zipHandler = $this->config['ZIP_PROXY'];
	$this->file = new $zipHandler($this->tmpdir);
	if ($this->file->open($tmp_filename) !== true) {	// This also create the tmpdir directory
	    throw new OdfException("Error while Opening the file '$filename' - Check your odt filename");
	}
	if (($this->stylesXml = $this->file->getFromName('styles.xml')) === false) {
	    throw new OdfException("Nothing to parse - Check that the styles.xml file is correctly formed in source file '$tmp_filename'");
	}
	$this->file->close();

    }

    protected $styleVars = array();
    /**
     * Assing a style template variable
     *
     * @param string $key name of the variable within the template
     * @param string $value replacement value
     * @param bool $encode if true, special XML characters are encoded
     * @throws OdfException
     * @return odf
     */
    public function setStyleVars($key, $value, $encode = true, $charset = 'ISO-8859') {
	if (strpos($this->stylesXml, $this->config['DELIMITER_LEFT'] . $key . $this->config['DELIMITER_RIGHT']) === false) {
	    throw new OdfException("var $key not found in the document");
	}
	$value = $encode ? htmlspecialchars($value) : $value;
	$value = ($charset == 'ISO-8859') ? utf8_encode($value) : $value;
	$this->styleVars[$this->config['DELIMITER_LEFT'] . $key . $this->config['DELIMITER_RIGHT']] = str_replace("\n", "<text:line-break/>", $value);
	return $this;
    }

    /**
     * Merge template variables
     * Called automatically for a save
     *
     * @return void
     */
    protected function _parse() {
	parent::_parse();
	$this->stylesXml = str_replace(array_keys($this->styleVars), array_values($this->styleVars), $this->stylesXml);
    }




    public function setField($key,$fieldObj,$extra = array()) {
        ///really should turn this into magic methods in the I2CE_FormField_Image class
        if ($fieldObj instanceof I2CE_FormField_IMAGE) {
            return $this->setImageFromField($key,$fieldObj,$extra);
        } else if  ($fieldObj instanceof I2CE_FormField) {
            $replacement = $fieldObj->getDisplayValue();
            $this->setVars($key,$replacement, true, 'UTF-8');
            return true;
        }
    }

    protected $global_segment = false;
    public function setGlobalSegment() {
	if (!$this->global_segment) {
	    $this->global_segment = new I2CE_GlobalSegment('GLOBAL',$this->contentXml,$this);
	}
	return $this->global_segment;
    }


    /**
     * Add the merged segment to the document
     *
     * @throws OdfException
     * @return odf
     */
    public function mergeGlobalSegment() {
	if (! $this->global_segment) {
	    throw new OdfException('global segement cannot be parsed');
	}
        $reg = "/(.*\<office:body.*?\>)(.*)(\<\\/office:body.*)/";
	$matches = array();
	preg_match($reg, $this->contentXml,$matches);
	$this->contentXml = $matches[1] . $this->global_segment->getXmlParsed() . $matches[3];


	return $this;
    }



    /**
     * Declare a segment in order to use it in a loop
     *
     * @param string $segment
     * @throws OdfException
     * @return Segment
     */
    public function setSegment($segment)  {
	if (array_key_exists($segment, $this->segments)) {
	    return $this->segments[$segment];
	}
	// $reg = "#\[!--\sBEGIN\s$segment\s--\]<\/text:p>(.*)<text:p\s.*>\[!--\sEND\s$segment\s--\]#sm";
	   $reg = "#\[!--\s*BEGIN\s+$segment\s*--\](.*)\[!--\s*END\s+$segment\s*--\]#sm";
	   
	if (preg_match($reg, html_entity_decode($this->contentXml), $m) == 0) {
	    throw new OdfException("'$segment' segment not found in the document");
	}
        I2CE::raiseError("Segment $segment found:\n " . $m[1]);
	$this->segments[$segment] = new I2CE_Segment($segment, $m[1], $this);
	return $this->segments[$segment];
    }


    /**
     * Add the merged segment to the document
     *
     * @param Segment $segment
     * @throws OdfException
     * @return odf
     */
    public function mergeSegment(Segment $segment)
    {
        if (! array_key_exists($segment->getName(), $this->segments)) {
            throw new OdfException($segment->getName() . 'cannot be parsed, has it been set yet ?');
        }
        $string = $segment->getName();
        $reg = "#\[!--\s*BEGIN\s+$string\s*--\](.*)\[!--\s*END\s+$string\s*--\]#sm";
        // $reg = '@<text:p[^>]*>\[!--\sBEGIN\s' . $string . '\s--\](.*)\[!--.+END\s' . $string . '\s--\]<\/text:p>@smU';
        //$reg = '@\[!--\s*BEGIN\s+' . $string . '\s*--\](.*)\[!--.+END\s+' . $string . '\s*--\]@smU';
        $this->contentXml = preg_replace($reg, $segment->getXmlParsed(), $this->contentXml);
        return $this;
    }


    /**
     * Assign a template variable as a picture
     *
     * @param string $key name of the variable within the template
     * @param string $value path to the picture
     * @throws OdfException
     * @return odf
     */
    protected function setImageFromField($key,$fieldObj, $extra=array()) {
        $filename = 'ihris' . $fieldObj->getHTMLName() . '.' . $fieldObj->getExtension();        
        $filename = preg_replace('/[^a-zA-Z0-9\.]/', '', $filename);
        $filename = md5($filename)  . strrchr($filename,'.');
              
        $file = $filename;
        $img_width = "1.000in";
        $img_height = "1.000in";                        
        //get the ratio 
        $px_width = $fieldObj->getImageWidth();
        $px_height = $fieldObj->getImageHeight();
        if ($px_height == 0 || $px_width == 0) {
            I2CE::raiseError("Bad image");
            return false;
        }
        $measure =false;
        $unit = false;
        if (array_key_exists('width',$extra) &&  2 == sscanf($extra['width'],"%f%s", $measure,$unit)  ) {
            $calc = ((float) $px_height) * $measure / ((float) $px_width);
            $m_measure =false;
            $m_unit = false;
            if (array_key_exists('maxheight',$extra) && 2 == sscanf($extra['maxheight'],"%f%s", $m_measure,$m_unit) && $calc > $m_measure ) {
                //we need to rescale  the width and use the maximum height, m_measure
                $calc = ((float) $px_width) * $m_measure / ((float) $px_height);
                $img_width  =  sprintf("%f%s",$calc , $unit);
                $img_height =  sprintf("%f%s",$m_measure , $unit);
            } else if (array_key_exists('height',$extra) && 2 == sscanf($extra['height'],"%f%s", $m_measure,$m_unit)) {
                //no rescaling.
                $img_width = $extra['width'];
                $img_height =  $extra['height'];
            } else {
                $img_width = $extra['width'];
                $img_height =  sprintf("%f%s",$calc , $unit);
            }                            
        }else  if (array_key_exists('height',$extra) &&  2 == sscanf($extra['height'],"%f%s", $measure,$unit)   ) {
            $calc = ((float) $px_width) * $measure / ((float) $px_height);
            $m_measure =false;
            $m_unit = false;
            if (array_key_exists('maxwidth',$extra) && 2 == sscanf($extra['maxwidth'],"%f%s", $m_measure,$m_unit) && $calc > $m_measure ) {
                //we need to rescale  the height and use the maximum width, m_measure
                $calc = ((float) $px_height) * $m_measure / ((float) $px_width);
                $img_height  =  sprintf("%f%s",$calc , $unit);
                $img_width =  sprintf("%f%s",$m_measure , $unit);
            } else if (array_key_exists('width',$extra) && 2 == sscanf($extra['width'],"%f%s", $m_measure,$m_unit)) {
                //no rescaling. (asssuming valid width and height were entered this should be pick up in elseif of the first parent block above.. but keeping it for symmetry)
                $img_height =  $extra['height'];
                $img_width = $extra['width'];
            } else {
                $img_height = $extra['height'];
                $img_width =  sprintf("%f%s",$calc , $unit);
            } 
        } else {
            $width *= self::PIXEL_TO_CM;
            $img_width = $width . 'cm';
            $height *= self::PIXEL_TO_CM;
            $img_height = $height . 'cm';
        }
        
        $xml = <<<IMG
            <draw:frame draw:style-name="fr1" draw:name="$filename" text:anchor-type="as-char" svg:y="0in" svg:width="{$img_width}" svg:height="{$img_height}" draw:z-index="3"><draw:image xlink:href="Pictures/$file" xlink:type="simple" xlink:show="embed" xlink:actuate="onLoad"/></draw:frame>
IMG;
	$this->addImageBlob($filename,$fieldObj->getValue());

        $this->setVars($key, $xml, false, 'UTF-8');
        return true;
    }
    
    public function addImageBlob($filename,$value) {
        $this->image_blobs[$filename] = $value;
    }
    
    protected $image_blobs = array();



    /**
     * Internal save
     *
     * @throws OdfException
     * @return void
     */
    protected function _save()
    {
        $res=$this->file->open($this->tmpfile);
        $this->_parse();
        if (! $this->file->addFromString('content.xml', $this->contentXml)) {
            throw new OdfException('Error during file export addFromString');
        }
        if (! $this->file->addFromString('styles.xml', $this->stylesXml)) {
            throw new OdfException('Error during file export addFromString');
        }
        foreach ($this->images as $imageKey => $imageValue) {
            $this->file->addFile($imageKey, 'Pictures/' . $imageValue);
            $this->addImageToManifest($imageValue);
        }
        foreach ($this->image_blobs as $imageKey => $imageBlob) {
            $this->file->addFromString('Pictures/' . $imageKey, $imageBlob);
            $this->addImageToManifest($imageKey);
        }
        if (! $this->file->addFromString('META-INF/manifest.xml', $this->manifestXml)) {
            throw new OdfException('Error during file export: manifest.xml');
        }
        $this->file->close();
    }



    /**
     * Update Manifest file according to added image files
     *
     * @param string	$file		Image file to add into manifest content
     */
    public function addImageToManifest($file)
    {
        $extension = explode('.', $file);
        $add = ' <manifest:file-entry manifest:media-type="image/'.$extension[1].'" manifest:full-path="Pictures/'.$file.'"/>'."\n";
        $this->manifestXml = str_replace('</manifest:manifest>', $add.'</manifest:manifest>', $this->manifestXml);
    }

    
    
  }
