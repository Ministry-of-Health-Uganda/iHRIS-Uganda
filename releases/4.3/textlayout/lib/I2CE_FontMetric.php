
<?php

/**
 *  Class to contain information about  font metrics.  
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


class I2CE_FontMetric {
    /**
     * protected @var $bbox of numeric. the bounding box with values in order llx lly urx ury
     */
    protected $bbox;
    /**
     * protected @var numeric $ascender
     */
    protected $ascender = 0;

    /**
     * protected @var numeric $ascender
     */
    protected $descender = 0;

    /**
     * protected @var numeric $linegap
     */
    protected $linegap  = 0;
        
    /**
     * protected @var array of boolean $is_fixed_width whether or not this font is fixed width
     */
    protected $is_fixed_width =  false;

    /**
     * protected @var  int  the size of a fixed width character
     */
    protected $fixed_character_width;


    /**
     * protected @var  int  the size of a fixed width character
     */
    protected $fixed_character_height;


    /**
     * protected @var int $size  the font size in points
     */
    protected $size;



    /**
     * proteted @var I2CE_Encoding encoding  data  for the character set
     */
    protected $encoding; 


    /**
     * protected @var array of array of numbers $kerning_table_pairs  the kerning table for pairs.
     * the first index is the preceeding character the second index is the following character
     */
    protected  $kerning_table_pairs = array();

    /**
     * protected @var array of array of numbers $kerning_table_groups  the kerning table for groups.
     * the first index is the group for the preceeding character the second index is the group for the following character
     */
    protected  $kerning_table_groups = array();

    /**
     * protected $var array with keys character and values the kerning groups
     */
    protected $kerning_groups = array(array());


    /**
     * @var array  of floats containing $character_widths the character widths for the current font
     * to be super  redundant the keys will be either the character code
     * or the glyph name 
     */
    public $character_widths= array();

    /**
     * @var array of array of floats  $character_heights containing the character heights for the current font
     * to be super  redundant the keys will be either the character code
     * or the glyph name 
     */
    public $character_heights= array();

    /**
     * @var array of array of mixed $character_info containing character information (besides the width)
     */
    public $character_info = array();

    /**
     * protected @var array of mixed global font information
     */
    protected $font_info=array();


    /**
     * @param I2CE_Encoding $encoding -- the encoding used for the internal storage of strings/characters
     *  needs to be one that is a valid encoding for PHP multibyte strings.  
     */
    public function __construct(&$encoding) { 
        $this->encoding =  $encoding;
    }


    /**
     * Get the encoding used for the internal storage of strings/characters
     * @returns string
     */
    public function getEncoding() {
        return $this->encoding;
    }
    /**
     *  Caluclulate the length of a fixed width string
     * @param string $string  the string we wish to calculate the length of
     * @returns float/int the length of the string
     */
    protected function getFixedStringWidth($string) {
        if ($this->getEncoding()->useMB()) {
            return  mb_strlen($string,$this->getEncoding()->getEncodingType()) * $this->fixed_character_width;
        } else {
            return strlen($string) * $this->fixed_character_width;
        }

    }

    /**
     *  Caluclulate the length of a string
     * @param string $string  the string we wish to calculate the length of
     * @returns float/int the length of the string
     *  note: if it is a fixed width font, we assume that all characters are printable.  in particular
     *  a line break is considered a printable character. If you have line breaks to deal with
     * @param bool use_font_size default false -- 
     * you should see TextColumn
     */
    public  function getStringWidth($string,$use_font_size=false) {
        if ($string === null) {
            return 0;
        }
        if ($this->is_fixed_width) {
            $size = $this->getFixedStringWidth($string);
        } else {
            $size = $this->getVariableStringWidth($string);
        }
        if ($use_font_size) {
            return ((float) $size)*($this->font_size);
        } else {
            return $size;
        }
    }


    /**
     * Function caluclulate the length of a variable width string
     * @param string $string  the string we wish to calculate the length of
     * @param bool use_font_size default false -- 
     * @returns float the length of the string
     *
     */
    protected function getVariableStringWidth($string) {
        $size =0;
        if ($this->getEncoding()->useMB()) {
            $et = $this->getEncoding()->getEncodingType();
            $len = mb_strlen($string,$et);
            $nc = mb_substr($string,0,1,$et); //get the first character
            $string = mb_substr($string,1,$len-1, $et);
            $len--;
            while ($len > 0) {
                $c = $nc;
                $nc = mb_substr($string,0,1,$et);
                $string = mb_substr($string,1,$len-1, $et);
                $len--;
                $size  += $this->character_widths[$c];
                $size += $this->getKerningValue($c,$nc,$false);
            }
            $size += $this->character_widths[$nc];
        } else {
            $len = strlen($string);
            for ($i = 0; $i < $len-1; $i++) {
                if (array_key_exists($string[$i],$this->character_widths)) {
                    $size  += $this->character_widths[$string[$i]];
                    $size += $this->getKerningValue($string[$i],$string[$i+1],false);
                }
            }
            if ($len > 0) {
                if (array_key_exists($string[$len-1],$this->character_widths)) {
                    $size += $this->character_widths[$string[$len-1]];
                }
            }
        }
        return $size;
    }


    /**
     * Set whether or not this is a fixed width font
     * @parma boolean $is_fixed_width
     */
    public function setFixedWidth($is_fixed_width) { 
        $this->is_fixed_width = $is_fixed_width;
    }


    /**
     * set the asceneder value
     * @param numeric $ascender
     **/
    public function setAscender($ascender) {
        $this->ascender = $ascender;
    }


    /**
     * get the asceneder value
     * @returns numeric $ascender
     **/
    public function getAscender() {
        return $this->ascender;
    }

    /**
     * set the desceneder value
     * @param numeric $descender
     **/
    public function setDescender($descender) {
        $this->descender = $descender;
    }


    /**
     * get the desceneder value
     * @returns numeric $descender
     **/
    public function getDescender() {
        return $this->descender;
    }

    /**
     * set the line gap value
     * @param numeric $linegap
     **/
    public function setLinegap($linegap) {
        $this->linegap = $linegap;
    }


    /**
     * get the line gap value
     * @returns numeric $linegap
     **/
    public function getLinegap() {
        return $this->linegap;
    }



    /**
     * Set the bounding box
     * $param array $bbox of numeric. The bounding box in llx lly urx ury order
     */
    public function setBoundingBox($bbox) { 
        $this->bbox = $bbox;
    }

    /**
     * Get the bounding box
     * $returns array $bbox of numeric. The bounding box in llx lly urx ury order
     */
    public function getBoundingBox() {
        return $this->bbox;
    }

         
    /**
     * set the fixed width character size
     * @param numeric $width the width
     */
    public function setFixedWidthSize($width) {
        $this->fixed_character_width = $width;
    }

    /**
     * set the fixed height character size
     * @param numeric $height the width
     */
    public function setFixedHeightSize($height) {
        $this->fixed_character_height = $height;
    }

    /**
     * Check to see if the current font is fixed width 
     * @returns boolean true if it is fixed width
     */
    public function isFixedWidth() { 
        return $this->is_fixed_width;
    }




    /**
     *  Get the width of a character in a fixed width font 
     *  @returns number Returns null if called when the current font is not fixed width
     */
    public function getFixedWidth() {
        if ($this->is_fixed_width) {
            return $this->fixed_character_width;
        } else { 
            return 0;
        }
    }



    /**
     * Get information associated to  a character
     * @param mixed $ch a character (or glyphname)
     * @param mixed $key
     * @return mixed $value
     */
    public function getCharacterInfo($ch,$key) {
        if (($ch === null)|| ($key===null) || !array_key_exists($ch,$this->character_info) || !is_array($this->character_info[$ch])) {
            return;
        }
        return $this->character_info[$ch][$key];
    }


    /**
     * Sets information associated to  a character
     * @param mixed $ch a character (or glyphname)
     * @param mixed $key
     * @param mixed $value
     */
    public function setCharacterInfo($ch,$key,$value) {
        if (($ch === null) || ($key === null)) {
            return;
        }
        $this->character_info[$ch][$key] = $value;
    }


    /**
     * Gets the character width of a character
     * @param mixed $ch a character (or glyphname)
     * @param bool use_font_size default false -- 
     * @returns float the width
     */
    public function getCharacterWidth($ch,$use_font_size = false) {
        if ($this->is_fixed_width) {
            $w = $this->fixed_character_width;
        } else {
            if ($ch === null || !array_key_exists($ch,$this->character_widths)) {
                return 1;
            }
            $w =  $this->character_widths[$ch];
        }
        if ($use_font_size) {
            return $w*$this->font_size;
        } else {
            return $w;
        }
    }

    /**
     * Sets the character width of a character
     * @param mixed $ch a character (or glyphname)
     * @param numeriv $w the width
     */
    public function setCharacterWidth($ch,$w) {
        if ($this->is_fixed_width)  {
            $this->setFixedWidthSize($w);
        } else {
            if ($ch === null) {
                return;
            } 
            $this->character_widths[$ch] = $w;
        }
    }
        
    /**
     * Sets the character width of a character
     * @param mixed $ch a character (or glyphname)
     * @param numeric $h the $height
     */
    public function setCharacterHeight($ch,$h) {
        if ($ch === null) {
            return;
        }
        $this->character_height[$ch] = $h;
    }

    /**
     * Gets the character height of a character
     * @param mixed $ch a character
     * @param bool use_font_size default false -- 
     * @returns float the width
     */
    public function getCharacterHeight($ch, $use_font_size = false) {
        if ($ch === null || !array_key_exists($ch,$this->character_heights)) {
            return;
        }
        $h = $this->character_heights[$ch];
        if ($use_font_size) {
            return $this->font_size * $h;
        } else {
            return $h;
        } 
    }


    /**
     * Get the character widths
     * @returns array of number, the values of which are the widths
     * and the keys of which are some combination of
     * glpyh names, character codes, and unicodes codepoints, or characters
     */
    public function getCharacterWidths() {
        return $this->character_widths;
    }


    /**
     * Get the character heights
     * @returns array of number, the values of which are the heights
     * and the keys of which are some combination of
     * glpyh names, character codes, and unicodes codepoints, or characters
     */
    public function getCharacterHeights() {
        return $this->character_heights;
    }
        

    /**
     * Get all the font information
     */
    public function getAllFontCharacteristics() {
        return $this->font_info;
    }

        
        
    /**
     * Set a global font characteristic
     * @param string $key
     * @param mixed $value
     */
    public function setFontCharacteristic($key,$value) {
        if ($key === null) {
            return;
        }
        $this->font_info[$key] = $value;
    }
        
    /**
     * Get a global font characteristic
     * @param $key
     * @returns mixed  the value associated to the $key
     */
    public function getFontCharacteristic($key) {
        if ($key === null || !array_key_exists($key,$this->font_info)) {
            return;
        }
        return $this->font_info[$key];
    }

    /**
     * Set the font size
     * @params int $size
     */
    public function setFontSize($size) {
        $this->font_size = $size;
    }

    /**
     * Get the font size
     * @returns int 
     */
    public function getFontSize() {
        return $this->font_size;
    }


    /**
     * Get the kerning groups associated to a character
     * @param mixed $ch the character
     * @returns mixed the group
     */
    public function getKerningGroup($ch) { 
        if ($ch === null || !array_key_exists($ch,$this->kerning_groups)) {
            return;
        }
        return $this->kerning_groups[$ch];
    }


    /**
     * Set the kerning groups associated to a character
     * @param mixed $ch the character
     * @param mixed $group the group
     */
    public function setKerningGroup($ch,$group) { 
        if ($ch === null) {
            return;
        }
        $this->kerning_groups[$ch] = $group;
    }

    /**
     * get the kerning values associated to a pair of character
     * @param mixed $ch1 the preceeding character
     * @param mixed $ch2 the following character
     * @param bool use_font_size default false -- 
     * @return numeric the kerning value or null if there is none found
     */
    public function getKerningByPair($ch1,$ch2,$use_font_size = false) {
        if (($ch1 === null) || ($ch2 === null) || (!array_key_exists($ch1,$this->kerning_table_pairs)) ) {
            return;
        }
        $t = $this->kerning_table_pairs[$ch1];
        if (!is_array($t) || !array_key_exists($ch2,$t)) {
            return;
        }
        if ($use_font_size) {
            return $t[$ch2]*$this->font_size;
        } else {
            return $t[$ch2];
        }
    }

    /**
     * set the kerning values associated to a pair of character
     * @param mixed $ch1 the preceeding character
     * @param mixed $ch2 the following character
     * @params numeric $kern  the kerning value
     */
    public function setKerningByPair($ch1,$ch2,$kern) {
        if (($ch1 === null)  || ($ch2 === null)){
            return;
        }
        $t = &$this->kerning_table_pairs[$ch1];
        if ($t===null) { 
            $this->kerning_table_pairs[$ch1] = array();
            $t = &$this->kerning_table_pairs[$ch1];
        }
        $t[$ch2] = $kern;
    }

    /**
     * get the kerning values associated to a pair of groups
     * @param mixed $g1 the preceeding group
     * @param mixed $g2 the following group
     * @param bool use_font_size default false -- 
     * @return numeric $kern  the kerning value or null if none found
     */
    public function getKerningByGroup($g1,$g2,$use_font_size = false) {
        if (($g1 == null) || ($g2 == null) || !array_key_exists($g1,$this->kerning_table_groups)) {
            return null;
        }
        $t = &$this->kerning_table_groups[$g1];
        if (!is_array($t) || !array_key_exists($g2,$t)) {
            return;
        }
        if ($use_font_size) {
            return $t[$g2]*$this->font_size;
        } else {
            return $t[$g2];
        }
    }


    /**
     * get the kerning values associated to a pair of groups
     * @param mixed $g1 the preceeding group
     * @param mixed $g2 the following grou
     * @params numeric $kern  the kerning value
     */
    public function setKerningByGroup($g1,$g2,$kern) {
        if (($g1 === null) || ($g2===null)) {
            return;
        }
        $t = &$this->kerning_table_groups[$g1];
        if ($t===null) { 
            $this->kerning_table_pairs[$g1] = array();
            $t = &$this->kerning_table_pairs[$g1];
        }
        $t[$ch2] = $kern;
    }

    /**
     *  Return the (horizontal) kerning values for a pair of characters
     *  If there is kerning info for both groups and pairs,
     *  the pairs takes prescedence
     *  @params string $ch1 the left characcter
     *  @params string $ch2 the right character
     * @param bool use_font_size default false -- 
     *  @returns number the kerning value or null  if none is found
     */
    public function getKerningValue($ch1,$ch2,$use_font_size=false) {
        //try getting kerning pair info
        $kern = $this->getKerningByPair($ch1,$ch2,$use_font_size);
        if ($kern !== null) {
            return $kern;
        }
        //now try kerning group info
        $g1 = $this->getKerningGroup($ch1);
        $g2 = $this->getKerningGroup($ch2);
        $kern = $this->getKerningByGroup($g1,$g2,$use_font_size);
        return $kern; //Note: it is null if not found.  
    }


    /**
     *Get the tracking values for the current font size
     * @returns array $values of floats.  $values[0] is the minimum, $values[1] is the maximum or null if
     * if there is no tracking values
     */
    public  function getTrackingValues() {
        return null;
    }



        
}


# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
