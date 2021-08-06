<?php


/**
 *  Class to contain information about  font metrics.  
 *   Setup to handle multiple directions.
 *
 * @package I2CE
 * @subpackage TextLayout
 * @author Carl Leitner <litlfred@ibiblio.org>
 * @copyright Copyright &copy; 2007 IntraHealth International, Inc. 
 * This file is part of I2CE. I2CE is free software; you can redistribute it and/or modify it under 
 * the terms of the GNU General Public License as published by the Free Software Foundation; either 
 * version 3 of the License, or (at your option) any later version. I2CE is distributed in the hope 
 * that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY 
 * or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details. You should have 
 * received a copy of the GNU General Public License along with this program. If not, see <http://www.gnu.org/licenses/>.
 * @version 0.1
 * @access public
 */
 

class I2CE_FontMetricMultiDirection  extends I2CE_FontMetric{

    /**
     * protecteed @var array of I2CE_FontMetric $font_metrics
     * contains the fonr metrics that are global or for  user defined directions
     */
    protected $font_metrics;


    /**
     * The constructor
     * @params array $directions of mixed the directions.  the value -1 is reserved for global information
     * array has a default value of  0 (horizontal) and 1 (vertical).  Sets the direction to be $directions[0]
     * @param I2CE_Encoding $encoding -- the encoding used for the internal storage of strings/characters
     *  needs to be one that is a valid encoding for PHP multibyte strings.  
     */
    public function __construct($directions,&$encoding ) {
        parent::__construct($encoding);
        $this->font_metrics = array();
        foreach ($directions as $direction) {
            if ($direction != -1) { //-1 is reserved for global font metric info
                $this->font_metrics[$direction] = new I2CE_FontMetric($encoding);
            }
        }
        $this->font_metrics[-1] = $this;
        $this->directions = $directions;
        $this->direction = $directions[0];
    }



    /*
     * protected @var mixed $direction  the current direction we are using
     *  we use -1 to indicate a global font metric information
     */ 
    protected $direction = -1;

    /*
     * protected @var array $directions all the possible directions
     */ 
    protected $directions;
        

    /**
     *  Caluclulate the length of a string
     * @param string $string  the string we wish to calculate the length of
     * @param bool use_font_size default false -- 
     * @returns float/int the length of the string
     */
    public function getStringWidth($string,$use_font_size =false) {
        if ($this->direction != -1) {
            return $this->font_metrics[$this->direction]->getStringWidth($string,$use_font_size);
        } else {
            I2CE::raiseError("No font direction specified", E_USER_ERROR);
        }
    }


    /**
     * Function caluclulate the length of a variable width string
     * @param string $string  the string we wish to calculate the length of
     * @param bool use_font_size default false -- 
     * @returns float the length of the string
     */
    protected function getVariableStringWidth($string,$use_font_size = false) {
        if ($this->direction != -1) {
            return $this->font_metrics[$this->direction]->getVariableStringWidth($string,$use_font_size);
        } else {
            return parent::getVariableStringWidth($string,$use_font_size);
        }
    }


    /**
     * Function caluclulate the length of a fixed width string
     * @param string $string  the string we wish to calculate the length of
     * @param bool use_font_size default false -- 
     * @returns float the length of the string
     */
    protected function getFixedStringWidth($string,$use_font_size = false) {
        if ($this->direction != -1) {
            return $this->font_metrics[$this->direction]->getFixedStringWidth($string,$use_font_size);
        } else {
            return parent::getFixedStringWidth($string,$use_font_size);
        }
    }

    /**
     * Check to see if the current font is fixed width 
     * @returns boolean true if it is fixed width
     */
    public function isFixedWidth() { 
        if ($this->direction != -1) {
            return  $this->font_metrics[$this->direction]->isFixedWidth();
        } else {
            return parent::isFixedWidth();
        }
    }



    /**
     * Get information associated to  a character
     * @param mixed $ch a character (or glyphname)
     * @param mixed $key
     * @return mixed $value
     */
    public function getCharacterInfo($ch,$key) {
        if ($this->direction != -1) {
            return $this->font_metrics[$this->direction]->getCharacterInfo($ch,$key);
        } else {
            return parent::getCharacterInfo($ch,$key);
        }
    }

    /**
     * Sets information associated to  a character
     * @param mixed $ch a character (or glyphname)
     * @param mixed $key
     * @param mixed $value
     */
    public function setCharacterInfo($ch,$key,$value) {
        if ($this->direction != -1) {
            $this->font_metrics[$this->direction]->setCharacterInfo($ch,$key,$value);
        } else {
            parent::setCharacterInfo($ch,$key,$value);
        }
    }



    /**
     *  Get the width of a character in a fixed width font (for the current direction)
     *  @returns number Returns null if called when the current font is not fixed width
     */
    public function getFixedWidth() {
        if ($this->direction != -1) {
            return $this->font_metrics[$this->direction]->getFixedWidth();
        } else {
            parent::getFixedWidth();
        }
    }

    /**
     * Gets the character width of a character
     * @param mixed $ch a character
     * @param bool use_font_size default false -- 
     * @returns float the width
     */
    public function getCharacterWidth($ch,$use_font_size = false) {
        if ($this->direction != -1) {
            return $this->font_metrics[$this->direction]->getCharacterWidth($ch,$use_font_size);
        } else {
            return parent::getCharacterWidth($ch,$use_font_size);
        }
    }

    /**
     * Gets the character height of a character
     * @param mixed $ch a character
     * @param bool use_font_size default false -- 
     * @returns float the width
     */
    public function getCharacterHeight($ch,$use_font_size = false) {
        if ($this->direction != -1) {
            return $this->font_metrics[$this->direction]->getCharacterHeight($ch,$use_font_size);
        } else {
            return parent::getCharacterHeight($ch,$use_font_size);
        }
    }



    /**
     * Sets the character width of a character
     * @param mixed $ch a character
     * @param float $w the width
     */
    public function setCharacterWidth($ch,$w) {
        if ($this->direction != -1) {
            $this->font_metrics[$this->direction]->setCharacterWidth($ch,$w);
        } else {
            parent::setCharacterWidth($ch,$w);
        } 
    }

    /**
     * Sets the character height of a character
     * @param mixed $ch a character
     * @param float $h  height
     */
    public function setCharacterHeight($ch,$h) {
        if ($this->direction != -1) {
            $this->font_metrics[$this->direction]->setCharacterHeight($ch,$h);
        } else {
            parent::setCharacterHeight($ch,$h);
        }
    }


    /**
     * Get the character widths
     * @returns array of number, the values of which are the widths
     * and the keys of which are some combination of
     * glpyh names, character codes, and unicodes codepoints, or characters
     */
    public function getCharacterWidths() {
        if ($this->direction != -1) {
            return $this->font_metrics[$this->direction]->getCharacterWidths();
        } else {
            return parent::getCharacterWidths();
        }
    }


    /**
     * Get the character heights
     * @returns array of number, the values of which are the heights
     * and the keys of which are some combination of
     * glpyh names, character codes, and unicodes codepoints, or characters
     */
    public function getCharacterHeights() {
        if ($this->direction != -1) {
            return $this->font_metrics[$this->direction]->getCharacterHeights();
        } else {
            return parent::getCharacterHeights();
        }
    }

    /**
     *  Function to set the font direction
     *  @params mixed $direction. -1 is global information, all other values are
     * user defined directions.
     */
    public function setDirection ($direction) {
        if ($direction == -1) {
            $this->direction = -1;
            return;
        }
        if (in_array($direction,$this->directions)) {
            $this->direction = $direction;
        } else {
            die ("Trying to set to an invlid direction <$direction>\n");
        }
    }

    /**
     * Specify that we are working with global font metric
     * information, e.g. glpyhnames, font size, etc.
     */
    public function setGlobal() { 
        $this->setDirection(-1);
    }


    /**
     * Set the bounding box
     * $param array $bbox of numeric. The bounding box in llx lly urx ury order
     */
    public function setBoundingBox($bbox) { 
        if ($this->direction == -1) {
            foreach ($this->directions as $direction) {
                $this->font_metrics[$direction]->setBoundingBox($bbox);
            }
            parent::setBoundingBox($bbox);
        } else {
            $this->font_metrics[$this->direction]->setBoundingBox($bbox);
        }
    }

    /**
     * Get the bounding box
     * $returns array $bbox of numeric. The bounding box in llx lly urx ury order
     */
    public function getBoundingBox() {
        if ($this->direction != -1) {
            return $this->font_metrics[$direction]->getBoundingBox();
        } else {
            return parent::getBoundingBox();
        }
    }


    /**
     * Function to get the possible directions 
     * @param boolena $add_global (defaults to false) whether or not to include the global direction, -1
     * @returns array of mixed -- the directions
     */
    public function getDirections ($add_global = false) { 
        $dirs = $this->directions;
        if ($add_global) {
            $dirs[] = -1; //add the global direction
        }
        return $dirs;
    }

    /**
     * Function to get the font direction
     * @returns mixed
     */
    public function getDirection () { 
        return $this->direction;
    }

        
    /**
     * Set a  font characteristic
     * @param string $key
     * @param mixed $value
     */
    public function setFontCharacteristic($key,$value) {
        if ($this->direction == -1) {
            foreach ($this->directions as $direction) {
                $this->font_metrics[$direction]->setFontCharacteristic($key,$value);
            }
            parent::setFontCharacteristic($key,$value);
        } else {
            $this->font_metrics[$this->direction]->setFontCharacteristic($key,$value);
        }
    }
        
    /**
     * Get a global font characteristic
     * @param $key
     * @returns mixed  the value associated to the $key
     */
    public function getFontCharacteristic($key) {
        if ($this->direction != -1) {
            return  $this->font_metrics[$this->direction]->getFontCharacteristic($key);
        } else {
            return parent::getFontCharacteristic($key);
        }
    }


    /**
     * Get all the font information
     */
    public function getAllFontCharacteristics() {
        if ($this->direction != -1) {
            return $this->font_metrics[$this->direction]->getAllFontCharacteristics();
        } else {
            return parent::getAllFontCharacteristics();
        }
    }




    /**
     *  Return the (horizontal) kerning values for a pair of characters
     *  @params string $ch1 the left characcter
     *  @params string $ch2 the right character
     * @param bool use_font_size default false -- 
     *  @returns float the kerning value or null if none is found
     */
    public function getKerningValue($ch1,$ch2,$use_font_size=false) {
        if ($this->direction != -1) {
            return $this->font_metrics[$this->direction]->getKerningValue($ch1,$ch2,$use_font_size);
        } else {
            return parent::getKerningValue($ch1,$ch2,$use_font_size);
        }
    }

    /**
     *Get the tracking values for the current font size
     * @returns array $values of floats.  $values[0] is the minimum, $values[1] is the maximum or null if
     * if there is no tracking values
     */
    public function getTrackingValues() {
        if ($this->direction != -1) {
            return $this->font_metrics[$this->direction]->getTrackingValues();
        }else {
            return parent::getTrackingValues();
        }
    }


    /**
     * Get the kerning groups associated to a character
     * @param mixed $ch the character
     * @returns mixed the group
     */
    public function getKerningGroup($ch) { 
        if ($this->direction != -1) {
            return $this->font_metrics[$this->direction]->getKerningGroup($ch);
        } else {
            return parent::getKerningGroup($ch);
        }
    }
    /**
     * Set the kerning groups associated to a character
     * @param mixed $ch the character
     * @param mixed $group the group
     */
    public function  setKerningGroup($ch,$group) { 
        if ($this->direction == -1) {
            foreach ($this->directions as $direction) {
                $this->font_metrics[$direction]->setKerningGroup($ch,$group);
            }
            parent::setKerningGroup($ch,$group);
        } else {
            $this->font_metrics[$this->direction]->setKerningGroup($ch,$group);
        }
    }
    /**
     * get the kerning values associated to a pair of character
     * @param mixed $ch1 the preceeding character
     * @param mixed $ch2 the following character
     * @param bool use_font_size default false -- 
     * @return numeric the kerning value or null if there is none found
     */
    public function getKerningByPair($ch1,$ch2,$use_font_size = false) {
        return $this->font_metrics[$this->direction]->getKerningByPair($ch1,$ch2,$use_font_size);
    }
    /**
     * set the kerning values associated to a pair of character
     * @param mixed $ch1 the preceeding character
     * @param mixed $ch2 the following character
     * @params numeric $kern  the kerning value
     */
    public function setKerningByPair($ch1,$ch2,$kern) {
        if ($this->direction == -1) {
            foreach ($this->directions as $direction) {
                $this->font_metrics[$direction]->setKerningByPair($ch1,$ch2,$kern);
            }
            parent::setKerningByPair($ch1,$ch2,$kern);
        } else {
            $this->font_metrics[$this->direction]->setKerningByPair($ch1,$ch2,$kern);
        }
    }
    /**
     * get the kerning values associated to a pair of groups
     * @param mixed $g1 the preceeding group
     * @param mixed $g2 the following group
     * @param bool use_font_size default false -- 
     * @return numeric $kern  the kerning value or null if none found
     */
    public function getKerningByGroup($g1,$g2,$use_font_size=false) {
        return $this->font_metrics[$this->direction]->getKerningByPair($g1,$g2,$use_font_size);
    }

    /**
     * set the kerning values associated to a pair of groups
     * @param mixed $g1 the preceeding group
     * @param mixed $g2 the following group
     * @params numeric $kern  the kerning value
     */
    public function setKerningByGroup($g1,$g2,$kern) {
        if ($this->direction == -1) {
            foreach ($this->directions as $direction) {
                $this->font_metrics[$direction]->setKerningByGroup($g1,$g2,$kern);
            }
            parent::setKerningByGroup($g1,$g2,$kern);
        } else {
            $this->font_metrics[$this->direction]->setKerningByGroup($g1,$g2,$kern);
        }
    }


    /**
     * Set whether or not this is a fixed width font
     * @parma boolean $is_fixed_width
     */
    public function setFixedWidth($is_fixed_width) { 
        if ($this->direction == -1) {
            foreach ($this->directions as $direction) {
                $this->font_metrics[$direction]->setFixedWidth($is_fixed_width);
            }
            parent::setFixedWidth($is_fixed_width);
        } else {
            $this->font_metrics[$this->direction]->setFixedWidth($is_fixed_width);
        }
    }

    /**
     * set the fixed width character size
     * @param numeric $width the width
     */
    public function setFixedWidthSize($width) {
        if ($this->direction == -1) {
            foreach ($this->directions as $direction) {
                $this->font_metrics[$direction]->setFixedWidthSize($width);
            }
            parent::setFixedWidthSize($width);
        } else {
            $this->font_metrics[$this->direction]->setFixedWidthSize($width);
        }
    }


    /**
     * set the fixed height character size
     * @param numeric $height the height
     */
    public function setFixedHeightSize($height) {
        if ($this->direction == -1) {
            foreach ($this->directions as $direction) {
                $this->font_metrics[$direction]->setFixedHeightSize($height);
            }
            parent::setFixedHeightSize($height);
        } else {
            $this->font_metrics[$this->direction]->setFixedHeightSize($height);
        }
    }

    /**
     * Get the encoding information for the font. This is global info
     *@returns I2CE_Encoding
     */
    public function getEncoding() { 
        return parent::getEncoding();
    }


    /**
     * set the asceneder value
     * @param numeric $ascender
     **/
    public function setAscender($ascender) {
        if ($this->direction == -1) {
            foreach ($this->directions as $direction) {
                $this->font_metrics[$direction]->setAscender($ascender);
            }
            parent::setAscender($ascender);
        } else {
            $this->font_metrics[$this->direction]->setAscender($ascender);
        }
    }


    /**
     * get the asceneder value
     * @returns numeric $ascender
     **/
    public function getAscender() {
        if ($this->direction != -1) {
            return $this->font_metrics[$this->direction]->getAscender();
        } else {
            return parent::getAscender();
        }
    }

    /**
     * set the desceneder value
     * @param numeric $descender
     **/
    public function setDescender($descender) {
        if ($this->direction == -1) {
            foreach ($this->directions as $direction) {
                $this->font_metrics[$direction]->setDescender($descender);
            }
            parent::setDescender($descender);
        } else {
            $this->font_metrics[$this->direction]->setDescender($descender);
        }
    }


    /**
     * get the desceneder value
     * @returns numeric $descender
     **/
    public function getDescender() {
        if ($this->direction != -1) {
            return $this->font_metrics[$this->direction]->getDescender();
        } else {
            return parent::getDescender();
        }
    }

    /**
     * set the line gap value
     * @param numeric $linegap
     **/
    public function setLinegap($linegap) {
        if ($this->direction == -1) {
            foreach ($this->directions as $direction) {
                $this->font_metrics[$direction]->setLinegap($linegap);
            }
            parent::setLinegap($linegap);
        } else {
            $this->font_metrics[$this->direction]->setLinegap($linegap);
        }
    }


    /**
     * get the line gap value
     * @returns numeric $linegap
     **/
    public function getLinegap() {
        if ($this->direction != -1) {
            return $this->font_metrics[$this->direction]->getLinegap();
        } else {
            return parent::getLinegap();
        }
    }


    /**
     * Set the font size.  This is global info.
     * @params int $size
     */
    public function setFontSize($size) {
        foreach ($this->directions as $direction) {
            $this->font_metrics[$direction]->setFontSize( $size);
        } 
        parent::setFontSize( $size);
    }

    /**
     * Get the font size.  This is global info.
     * @returns int 
     */
    public function getFontSize() {
        return parent::getFontSize();
    }   
        

        
}


# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
