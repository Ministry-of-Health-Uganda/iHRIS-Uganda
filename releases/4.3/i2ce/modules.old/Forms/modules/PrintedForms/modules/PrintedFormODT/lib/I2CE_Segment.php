<?php
/**
* © Copyright 2013 IntraHealth International, Inc.
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
* @subpackage ODF
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.1.0
* @since v4.1.0
* @filesource 
*/ 
/** 
* Class I2CE_Segment
* 
* @access public
*/


class I2CE_Segment extends Segment{
    /**
     * Assign a template variable as a picture
     *
     * @param string $key name of the variable within the template
     * @param string $value path to the picture
     * @throws OdfException
     * @return Segment
     */
    public function setImage($key, $value)
    {
        $filename = strtok(strrchr($value, '/'), '/.');
        $file = substr(strrchr($value, '/'), 1);
        $size = @getimagesize($value);
        if ($size === false) {
            throw new OdfException("Invalid image");
        }
        list ($width, $height) = $size;
        $width *= Odf::PIXEL_TO_CM;
        $height *= Odf::PIXEL_TO_CM;
        $xml = <<<IMG
<draw:frame draw:style-name="fr1" draw:name="$filename" text:anchor-type="char" svg:width="{$width}cm" svg:height="{$height}cm" draw:z-index="3"><draw:image xlink:href="Pictures/$file" xlink:type="simple" xlink:show="embed" xlink:actuate="onLoad"/></draw:frame>
IMG;
        $this->images[$value] = $file;
        $this->setVars($key, $xml, false);
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
    public function setImageFromField($key,$fieldObj, $extra=array()) {
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
            $px_width *= Odf::PIXEL_TO_CM;
            $img_width = $px_width . 'cm';
            $px_height *= Odf::PIXEL_TO_CM;
            $img_height = $px_height . 'cm';
        }
        
        $xml = <<<IMG
            <draw:frame draw:style-name="fr1" draw:name="$filename" text:anchor-type="as-char" svg:y="0in" svg:width="{$img_width}" svg:height="{$img_height}" draw:z-index="3"><draw:image xlink:href="Pictures/$file" xlink:type="simple" xlink:show="embed" xlink:actuate="onLoad"/></draw:frame>
IMG;
	$this->odf->addImageBlob($filename,$fieldObj->getValue());
        $this->setVars($key, $xml, false, 'UTF-8');
        return true;
    }



    public function getKeys() {
	$matches = array();
	preg_match_all('/' . preg_quote($this->odf->getConfig('DELIMITER_LEFT')) .'(.*?)' . preg_quote($this->odf->getConfig('DELIMITER_RIGHT')) .'/',$this->xml,$matches);
	return $matches[1];

    }



}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
