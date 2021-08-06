<?php


/**
 * A word-wrapping text cell class.   Uses the i2ce_hyphen class.
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

class I2CE_TextCell {
    /**
     * protected @var I2CE_FontMetrics $font_metric the font we are using
     */
    protected $font_metric;

    /**
     * protected @var I2CE_Encoding $encoding the encoding we are using
     */
    protected $encoding;

    /**
     * @var string $space_char The space character in this encoding
     */
    protected $space_char;
    /**
     * @var string $hyphen_char The hyphen character in this encoding
     */
    protected $hyphen_char;

    public function setEncoding($encoding) {
        $this->encoding = $encoding();
    }

    public function getEncoding($encoding) {
        return $this->encoding;
    }

    /**
     * Set the font metrics to be used in calculating word-wrapping
     * @param I2CE_FontMetric $font_metric if null we are word-wrapping on characters
     */
    public function setFontMetric($font_metric) {
        $this->font_metric = $font_metric;
    }

    /**
     * Get the font metrics to be used in calculating word-wrapping
     * @returns I2ce_FontMetric $font_metric if null we are word-wrapping on characters
     */
    public function &getFontMetric() {
        return $this->font_metric;
    }

    /**
     * protected @var mixed $width the width of the cell (float or int)
     */
    protected $width;

    /**
     * Set the column's width
     * @returns numeric $width
     */
    public function setWidth($width) {
        $this->width = max(1,$width);
    }

    /**
     * Gets the column's width
     * @param int/float $width
     */
    public function getWidth() {
        return $this->width;
    }


    /**
     * protected @var I2CE_Hyphen $hyphen a hyphenation dictionary
     */
    protected $hyphen;

    /**
     * Set the hyphenation dictionary to use
     * @param I2CE_Hyphen $hyphen  The hyphenation dictionary
     * If null, no hyphenation is used
     */
    public function setHyphenationDictionary($hyphen) { 
        $this->hyphen = $hyphen;
    }

    /**
     * Get the hyphenation dictionary to use
     * @returns I2CE_Hyphen $hyphen  The hyphenation dictionary
     */
    public function getHyphenDictionary() {
        return $this->hyphen;
    }

    /**
     *  Set the algorithm to use for word wrapping
     *  @param int $algorithm
     *  Values are: <ul><li> 'Truncate' </li>
     *                  <li> 'Greedy' </li>
     *                  <li> 'Knuth' </li>
     */
    public function setWordWrapAlgorithm($algorithm) {
        $this->algorithm = $algorithm;
    }

    /**
     * Construct a text cell object
     * @param I2CE_FontMetric $font_metric 
     * @param I2CE_Hyphen $hyphen  -- default is null
     * @param int $algorithm -- the word wrapping algorithm: default = 'Greedy'
     * @param I2CE_Encoding encoding -- the encoding we want to use
     */
    public function __construct(&$font_metric,&$hyphen= null,&$encoding,$algorithm = 'Greedy') {
        $this->width = 1;
        $this->font_metric = $font_metric;
        $this->hyphen = $hyphen;
        $this->algorithm = $algorithm;
        $this->encoding = $encoding;
        if ($encoding->useMB()) {
            $this->space_char = mb_convert_encoding(" " , $mb_encoding);
            $this->hyphen_char = mb_convert_encoding("-",$mb_encoding);
        } else {//use the usual PHP string functions
            $this->space_char = " ";
            $this->hyphen_char = "-";
        }
    }


    /**
     * Function to split up a text based on newlines, or the characters
     * corresponding to unicode codepoints:
     * <ul><li>0x2080 -- Line separator</li>
     *<li></li>0x2029 -- Paragraph separatory</ul> 
     *Note:  This works quickest if $text is ASCII friendly, then  UTF-8, then
     * in an arbitrary encoding
     * @returns array of (possibly multibyte) strings the paragraphs
     */
    public function getParagraphs ($text){
        $mb_encoding = $this->encoding->getEncodingType();
        if ($this->encoding->useMB()) {
            $newline_pattern = '[\n' . (string) 0xE2 . 
                (string) 0x80 .
                (string) 0xA8 . 
                (string) 0xE2 . 
                (string) 0x80 . 
                (string) 0xA9 . ']';
            //0x2028 codepoint is E2 80 A8 in UTF-8 
            //0x2029 codepoint is E2 80 A9 in UTF-8 
            if ($mb_encoding == 'UTF-8') {
                $paragraphs = mb_split ($newline_pattern,$text);
                $num_paragraphs = count($paragraphs) ;
                for ($i = 0; $i < $num_paragraphs; $i++) { 
                    //trim white space
                    $paragraphs[$i] = preg_replace('/^\p{Zs}+/u',"",$paragraphs[$i]);
                    $paragraphs[$i] = preg_replace('/\p{Zs}+$/u',"",$paragraphs[$i]);
                }
            } else {
                //we are in a horrible state of affairs with respect to PHP's mb regexp engine.
                $utf8_text = mb_convert_encoding($text,'UTF-8',$mb_encoding);
                $pargraphs = mb_split ($newline_pattern,$utf8_text);
                $num_paragraphs = count($paragraphs) ;
                for ($i = 0; $i < $num_paragraphs; $i++) {
                    //trim white space
                    $paragraphs[$i] = preg_replace('/^\p{Zs}+/u',"",$paragraphs[$i]);
                    $paragraphs[$i] = preg_replace('/\p{Zs}+$/u',"",$paragraphs[$i]);
                    //convert back to the user encoding
                    $paragraphs[$i] = mb_convert_encoding($paragraphs[$i],$mb_encoding,'UTF-8');
                }
            }
        } else {
            $paragraphs = explode("\n",$text);
            $num_paragraphs = count($paragraphs) ;
            for ($i = 0; $i < $num_paragraphs; $i++) {
                $paragraphs[$i] = trim($paragraphs[$i]);
            }
                        
        }
        return $paragraphs;
    }

        
    /**
     *  Function to get the line breaks in a paragraph
     *  for the text to fit in the current collumn
     *
     *  @param string $paragraph  The text we want to fit in the text column
     *  @returns array eof strings -- the rows of text
     */
    public function getLineBreaks($text) {
        $mb_encoding = $this->encoding->getEncodingType();
        $use_mb  =  $this->encoding->useMB();
        if (!is_string($text)) { 
            //make sure we have something
            if ($use_mb) {
                return array (mb_convert_encoding("",$mb_encoding));
            }else {
                return array("");
            }
        }
        //split up the paragaph along newlines
        $paragraphs = $this->getParagraphs($text);
        $text_lines = array();
        foreach ($paragraphs as $paragraph) {
            if ($use_mb) {
                $text_lines[] = mb_convert_encoding("", $mb_encoding);
            } else {
                $text_lines[] = "";
            }
            switch ($this->algorithm) {
            case 'Knuth':
                $this->Knuth($paragraph,$text_lines);
                break;
            case  'Greedy':
                $this->Greedy($paragraph,$text_lines);
                break;
            default: //truncate
                $this->Truncate($paragraph,$text_lines);
                break;
            }
        }
        return $text_lines;
    }


    public function getWords($paragraph) {
        $mb_encoding = $this->encoding->getEncodingType();
        if ($this->encoding->useMB()) {
            mb_regex_encoding($mb_encoding);
            // remove carriage returns
            $new_paragraph = mb_convert_encoding("",$mb_encoding);
            $len = mb_strlen($paragraph,$mb_encoding);
            for ($i = 0 ; $i < $len; $i++) {
                $c = mb_substr($paragraph,$i,1,$mb_encoding);
                if ($c != "\r") {
                    $new_paragraph .= $c;
                }
            }
            $words = mb_split('\s+',$new_paragraph); //split on whitespace
        } else {//use the usual PHP string functions
            $paragraph = str_replace("\r", '', $paragraph); // remove carriage returns
            $words = preg_split('/\s+/',$paragraph); //split on whitespace
        }
        return $words;
    }
        
    /**
     * Word-wrap a paragraph using simple truncation.
     * Assume no \n
     * Removes all \r
     * All white spaces are squased to a single space
     * @param string $paragraph the paragraph to encode
     * @param array $text_lines -- the array to store the text lines
     */
    protected function Truncate($paragraph,&$text_lines) {
        $fm = &$this->font_metric; 
        $mb_encoding = $this->encoding->getEncodingType();
        $use_mb = $this->encoding->useMB();
        $words = $this->getWords($paragraph);
        $line_length = 0;
        $current_line = count($text_lines) -1;
        $is_first_word_of_row = true;
        foreach ($words as $word) {
            if (!$is_first_word_of_line) {
                $word = $this->space_char . $word; 
            }
            $word_len = $fm->getStringWidth($word,true);
            if ($word_len + $line_length <= $this->width) {
                $text_lines[$current_line] .= $word;
                $is_first_word_of_line = false;
            } else {    //we need to truncate the word.  it may be across multiple lines.
                while ( $word) { //we still have characters to try and place
                    $i = 0; 
                    if ($use_mb) {
                        $sub_word = mb_convert_encoding("",$mb_encoding);
                        do {
                            $sub_word .= mb_substr($word,$i,1,$mb_encoding);
                            $i++;
                        }while ($fm->getStringWidth($sub_word,true)  + $line_length <= $this->width);
                                                
                    } else {
                        $sub_word = "";
                        do {
                            $sub_word .= $word[$i];
                            $i++;
                        }while ($fm->getStringWidth($sub_word,true)  + $line_length <= $this->width);
                    }
                    $i--; //$i is the number of characters we can place on the current line
                    if ($i == 0) {  //we cannot place any characters
                        if ($is_first_word_of_line) {
                            /*there is nothing on this line yet and we saw that the first letter of this word
                             *is too wide to fit in the column.  we need to place it anyways or else we
                             *will get into an infinite loop.  
                             */
                            if ($use_mb) {
                                $text_lines[$current_line] = mb_substr($word,0,1,$mb_encoding);
                            } else {
                                $text_lines[$current_line] = $word[0];
                            }
                        } else { //we have already placed some characters on this line
                            //we move to the next line without adding any more characters to this
                            //line.  This can only occur if the first character 
                            //is a space so we will delete it .
                            if ($use_mb) {
                                $word = mb_substr($word,1,$mb_encoding);
                            } else {
                                $word = substr($word,1);
                            }
                        }
                    } else { //we can place at least one character on this line
                        $text_lines[$current_line] .= substr($word,0,$i);
                        $word = substr($word,$i);
                    }
                    //we move to the next line
                    $current_line++;
                    $is_first_word_of_line = true;
                    $line_length = 0;
                    if ($use_mb) {
                        $text_lines[$current_line] = mb_convert_encoding("",$mb_encoding);
                    } else {
                        $text_lines[$current_line] ="";  
                    }
                }
            }
        }
        return $text_lines;
    }

    /**
     * Word-wrap a paragraph using simple truncation.
     * Assume no \n
     * Removes all \r
     * All white spaces are squased to a single space
     * @param array $text_lines -- the array to store the text lines.  It starts appending on
     * the last element of the array
     */
    protected function Greedy($paragraph,&$text_lines) {
        if ($this->hyphen ===null) { //no hyphenation dictionary has been defined. 
            I2CE::raiseError("No hypentation dictionary defined");
            //try and recover
            return $this->Truncate($paragraph);
        }
        $fm = &$this->font_metric;
        $space_length  = $fm->getCharacterWidth($this->space_char,true);
        $hyphen_length = $fm->getCharacterWidth($this->hyphen_char,true);
        $mb_encoding = $this->encoding->getEncodingType();
        $use_mb = $this->encoding->useMB();
        $words = $this->getWords($paragraph);
        $current_line = count($text_lines) -1 ;
        $line_length = 0;
        $is_first_word_of_row = true;
        foreach ($words as $word) {
            if ($is_first_word_of_row) { 
                $has_space = false;  //do we have a preceeding space for this word
            } else{
                //first to check if a space would cause a line break
                if ($space_length + $line_length > $this->width) {
                    //line break
                    $current_line++;
                    if ($use_mb) {
                        $text_lines[] = mb_convert_encoding("",$mb_encoding);
                    } else {
                        $text_lines[] = "";
                    }
                    $is_first_word_of_row = true;
                    $line_length = 0;
                    $has_space = false;
                } else {//otherwise we can move on with this line
                    $has_space = true;
                }
            }
            $word_length = $fm->getStringWidth($word,true);
            if ($has_space) {
                $word_length = $word_length + $space_length;
            }
            if ( $word_length + $line_length <= $this->width) { //we can place the word
                if ($has_space) {
                    $text_lines[$current_line] .= $this->space_char;
                }
                $text_lines[$current_line] .= $word;
                $is_first_word_of_row = false;
                //$line_length += $word_length;
                $line_length = $fm->getStringWidth($text_lines[$current_line],true); //slower but safer.
                //actually it is just that i am too lazy to compute the kerning for a possible space.
            } else {    
                /*we need to truncate the word.  word truncations should be
                 *allowed to occur in hyphenation points.  hyphenation points
                 * are only defined on letters.  we shall also allow hyphenations before
                 * or after any non-letter.  
                 */
                $word_parts = $this->hyphen->getWordParts($word);
                $num_word_parts = count($word_parts);
                $word_part = 0;  //the current word part we are trying to place
                $used_hyphen = false;
                while  ($word_part < $num_word_parts){ //we have a word part left to place
                    $remaining_space = max(1,$this->width - $line_length);
                    // $sub_word contains the part of the word  we know that we can place on the current line
                    // $new_sub_word contains what we are trying to place on the current line
                    if ($use_mb) {
                        $new_sub_word = mb_convert_encoding("",$mb_encoding);
                    } else {
                        $new_sub_word = '';
                    }
                    $new_sub_word_length = 0;
                    $prev_word_part = $word_part-1; //the word part we previously placed
                    $word_part--;
                    $possible_space = 0;
                    if (($prev_word_part == -1) && ($has_space)) {
                        $possible_space = $space_length;
                    }
                    do {
                        $sub_word = $new_sub_word;
                        $sub_word_length = $new_sub_word_length;
                        $word_part++;
                        if ($word_part < $num_word_parts) {
                            $new_sub_word .= $word_parts[$word_part]['Subword'];
                        }
                        $new_sub_word_length = $fm->getStringWidth($new_sub_word,true);
                        if (($word_part +1 >= $num_word_parts) || (!$word_parts[$word_part+1]['IsLetter'])) {
                            $possible_hyphen = 0; //we don't need a trailing hyphen since this is the last word part
                        }  else {
                            $indx = $word_parts[$word_part]['Length']-1;
                            if ($use_mb) {
                                $last_char = mb_substr($word_parts[$word_part]['Subword'],
                                                       $indx,1,$mb_encoding);
                            } else {
                                $last_char = $word_parts[$word_part];
                                $last_char = $word_parts[$word_part]['Subword'][$indx];
                            }
                            $kern = $fm->getKerningValue($last_char,$this->hyphen_char,true);
                            $possible_hyphen = $hyphen_length + $kern; 
                        }
                    } while (($new_sub_word_length + $possible_hyphen + $possible_space <= $remaining_space) && ($word_part   < $num_word_parts));
                    if ($new_sub_word_length + $possible_hyphen + $possible_space <= $remaining_space) {               
                        //we were able to place all of the sub-word.  This  happens if have
                        //already gpne to the next line  while trying to place word parts
                        if (($prev_word_part == -1) && ($has_space)) {
                            $text_lines[$current_line] .= $this->space_char;
                        }
                        $text_lines[$current_line] .= $new_sub_word;
                        $line_length += $new_sub_word_length;
                        $is_first_word_of_row = false;
                        $used_hyphen = false;
                    } else {//we exceeded the remaining space with the last word part 
                        //$word_part now temporairily contains the part of the word that exceeded the availble space
                        //                                              echo "for ($word) we have ({$word_part})<br/>\n";
                        if ($word_part -1 > $prev_word_part) {
                            //                                                  echo "for ($word) we have placed  before ". $word_parts[$word_part]['Subword'] . "<br/>";
                            //                                                  echo "for ($word) what we placed before was (". $text_lines[$current_line] . ")<br/>";
                            //we are able to place at least one word part
                            if (($prev_word_part == -1 ) && ($has_space) ) { 
                                //we are placing the first components of the word
                                $text_lines[$current_line] .= $this->space_char;
                            }
                            $text_lines[$current_line] .= $sub_word; 
                            if ($word_part == $num_word_parts) {
                                //                                                              echo "for ($word) no hyphen b/c last<br/>";
                            }
                            if (!$word_parts[$word_part]['IsLetter']) { 
                                //                                                              echo "for ($word) no hyphen b/c next piece is not letter<br/>";
                                //                                                              print_r($word_parts[$word_part]);
                            }
                            if (($word_part != $num_word_parts ) && ($word_parts[$word_part]['IsLetter'])) { 
                                $text_lines[$current_line] .= $this->hyphen_char;
                                $used_hyphen = true;
                            }
                            $line_length += $possible_space + $sub_word_length + $possible_hyphen;
                            $is_first_word_of_row = false;
                            //                                                  echo "for ($word) what we now placed is (". $text_lines[$current_line] . ")<br/>";
                            //on the next go around of the loop we will be trying to place $word_part
                        } else { 
                            //                                                  echo "for ($word) we have exceeded  on {$word_parts[$word_part]} <br/>";
                            /**
                             *the current word part exceeded the remaining space
                             *if it is the first word of the row we need to place a part of it
                             *by truncation.  We also want to do so it there is an excess
                             *amount of remaining space compared to the word with what is already used.
                             * I am arbitrairily deciding that more than 50% white space is excessive
                             */
                            $is_excessive = false;
                            if (($this->width > 1) && (!$is_first_word_of_row)) {

                                if (   (( (float) $remaining_space) / ((float) $this->width)) > 0.5) {
                                    $is_excessive = true;                                               
                                }
                            }
                            if  (($is_excessive) || ($is_first_word_of_row)) {
                                //                                                              echo "for ($word) we are forcing on {$word_parts[$word_part]} b/c exc or first<br/>";
                                /** 
                                 *We have to force at least one character to be put.
                                 *We will in fact place as many possible on this line
                                 *It could be the case that we have a really long word
                                 *with only one word part.  If the current word part is
                                 * the only word part remaining we will then recalculate 
                                 * the word parts on what is remaining of the word.  It
                                 * may make for incorrect hyphenation, but then we are
                                 * placing the word across several lines, so this is a nice
                                 * compromise
                                 */
                                if ($is_excessive && $used_hyphen) {
                                    //eat up the hyphen we have placed.
                                    if ($use_mb) {
                                        $ll = mb_strlen($text_lines[$current_line],$mb_encoding);
                                        $text_lines[$current_line]= mb_substr($text_lines[$current_line],0,$ll-1,$mb_encoding);
                                    } else {
                                        $ll = strlen($text_lines[$current_line]);
                                        $text_lines[$current_line] = substr($text_lines[$current_line],0,$ll-1);
                                    }
                                    //recalculate the remaining space
                                    $line_length = $fm->getStringWidth($text_lines[$current_line],true);
                                    $remaining_space = $this->width - $line_length;
                                } else {
                                    if (($prev_word_part == -1 ) && ($has_space) ) { 
                                        //we are placing the first components of the word
                                        $text_lines[$current_line] .= $this->space_char;
                                    }
                                }
                                if ($use_mb) {
                                    $trunc_word = mb_convert_encoding("",$mb_encoding);
                                } else {
                                    $trunc_word = '';
                                }
                                $trunc_length = 0;       
                                $chars = 0;
                                do {
                                    $chars++;
                                    $word_parts[$word_part]['Length']--;
                                    $word_parts[$word_part]['Offset']++;
                                    if ($use_mb) {
                                        $c = mb_substr($word_parts[$word_part]['Subword'],0,1,$mb_encoding);
                                        $word_parts[$word_part]['Subword'] 
                                            = mb_substr($word_parts[$word_part]['Subword'] ,
                                                        1,
                                                        $word_parts[$word_part]['Length'] ,
                                                        $mb_encoding);
                                    } else {
                                        $c = substr($word_parts[$word_part]['Subword'],0,1);
                                        $word_parts[$word_part]['Subword'] = substr($word_parts[$word_part]['Subword'],1); 
                                    }
                                    //add the character
                                    $old_trunc_word = $trunc_word;
                                    $trunc_word .= $c;
                                    $old_trunc_length = $trunc_length;
                                    $trunc_length = $fm->getStringWidth($trunc_word . $this->hyphen_char,true); //slow!
                                } while(($trunc_length   <= $remaining_space) && 
                                        ($word_parts[$word_part]['Length'] > 0)); 
                                //second condition should never  be satisfied, its there for safety
                                //we exceeded the available space on the last character placed
                                //so we need to return it if we places more than one character
                                if ($chars == 1) {
                                    //we only placed one character.   too bad.

                                } else {
                                    //return the last character
                                    $word_parts[$word_part]['Length']++;
                                    $word_parts[$word_part]['Offset']--;
                                    $word_parts[$word_part]['Subword'] = $c . $word_parts[$word_part]['Subword'];
                                    $trunc_word = $old_trunc_word;
                                    $trunc_length = $old_trunc_length;
                                }
                                if (($num_word_parts == $word_part +1) && ($word_parts[$word_part]['Length']==0)) {
                                    $text_lines[$current_line] .= $trunc_word;
                                } else {
                                    //check to see if the next word part begins with a letter
                                    //if so place a hyphen
                                    // if (!is_string($word_parts[$word_part])) {
                                    //     I2CE::raiseError("Bad " . print_r($word_parts[$word_part],true));
                                    // }
                                    //$utf8_part = mb_convert_encoding($word_parts[$word_part],'UTF-8',$mb_encoding);
                                    if ($word_parts[$word_part]['IsLetter']) {
                                        $text_lines[$current_line] .= $trunc_word . $this->hyphen_char;
                                    }
                                }
                                $used_hyphen  = true;
                                $is_first_word_of_row = false;
                                $line_length .= $trunc_length;
                                $is_first_word_of_row = false;
                                if ($word_parts[$word_part]['Length'] ==0) { //we reached the end of this word part
                                    $word_part++;
                                } else  if ($num_word_parts == $word_part + 1)  {
                                    //if we only had one word part remaining, we
                                    //now  reset the $word_parts by calculating a new hyphenation
                                    if ($use_mb) {
                                        $remaining_word = mb_convert_encoding("",$mb_encoding);
                                    } else {
                                        $remaining_word = "";
                                    } 
                                    for ($k=$word_part; $k < $num_word_parts; $k++) {
                                        $remaining_word .= $word_parts[$k]['Subword'];
                                    }
                                    $word_parts = $this->hyphen->getWordParts($remaining_word);
                                    $num_word_parts = count($word_parts);
                                    $word_part = 0;
                                }
                            }
                            //                                                  echo "for ($word) we have skip to next line on {$word_parts[$word_part]} <br/>";
                            //it is not the first word of the line, so we can happily skip to the next line
                            $line_length = 0;
                            $current_line++;
                            if ($use_mb) {
                                $text_lines[] = mb_convert_encoding("",$mb_encoding);
                            } else {
                                $text_lines[] = "";
                            }
                            $is_first_word_of_row = true;
                            $has_space = false;
                        }
                    }
                }

            }
        }
        return $text_lines;
                
    }
        


                
    protected function Knuth($paragraph,&$text_lines) {
                        
    }
}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
