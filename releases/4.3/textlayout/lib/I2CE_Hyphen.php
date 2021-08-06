<?php
/**
 * PHP  script implement Knuth's and Liang's hyphenation algorithm
 * as described in  http://lingucomponent.openoffice.org/hyphenator.html
 * In particular it uses the 'mashed up' dictionary files
 *
 * Note: Internally, by default, all strings are encoded as UTF-8.
 * This is highly recommended to enable the unicode preg to work
 * quickly (without having to covert to UTF=8 and then back).
 *
 *  Note:  Does not (yet) support the non-standard hyphenation of hungarian,
 * swedish, etc.
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




class I2CE_Hyphen {

    /**
     * protected @var I2CE_Encoding $enc the encoding used for internal storage of strings
     */
    protected $enc;

    /**
     *  @param I2CE_Encoding $enc -- specify the encoding the internal storage of this hyphenation dictionaty
     *  to the specified  encoding. 
     */
    public function __construct ($enc) { 
        $this->enc = $enc;
    }

    /**
     *  Load the hyphenation dictionary.
     *
     *  The file is expected to be a 'mashed up' version of a .tex
     *  hyphenation dictionary geneareted by using substrings.pl
     *  as in the stand-along hyphenation code of
     *  http://lingucomponent.openoffice.org/hyphenator.html
     *  @param string $file file containing the dictionary
     */
    public function LoadHyphenDictionary($file) {
        /* we are working on the assumpition that $file is in ASCII  compatible ecoding */
        $found_file = I2CE::getFileSearch()->search('HYPHEN_PATH',$file);
        if (!$found_file) {
            die ("Cannot find hyphenation file <$file>\n");
        }
        $f = fopen($found_file, 'rb');
        if (!$f) die ("Cannot open hyphenation file: <{$found_file}>");
        $line = trim(fgets($f));   //read in the characterset line
        if (!$line) {
            die("Empty hyphenation dictionary file/Cannot read: <{$found_file}>");
        }
        if (strpos($line,'ISO8') === 0) { //we forgot a hyphen
            $character_set = 'ISO-' . substr($line,3);  //the character set of the  hyphenation dictionary
        } else {
            $character_set = $line;
        }
        $convert = false;
        $mb_encoding = $this->enc->getEncodingType();
        $convert = ($mb_encoding == $character_set);
        $this->patterns = array();
        $this->trans = array();
        $nums = array();
        for ($i=0; $i <= 9; $i++) {
            $nums[$i] = mb_convert_encoding("$i",$mb_encoding);
        }
        while ( $line = trim(fgets($f)) ) { //read  in a line of the dictionary file
            if ($convert){
                $line = mb_convert_encoding($line,$mb_encoding,$character_set);
            }
            if (mb_substr($line,0,1,$mb_encoding) != '%') { // this line is not a comment  
                $pattern = array();
                $word = mb_convert_encoding('',$mb_encoding);
                $prev_char_was_letter = true;
                $line_len = mb_strlen($line,$mb_encoding);
                for ($i = 0; $i < $line_len; $i++) {
                    $curr_char = mb_substr($line,$i,1,$mb_encoding);
                    $not_a_number = true;
                    $j =-1;
                    do {
                        $j++;
                        $not_a_number = ($curr_char != $nums[$j]);
                    } while (($j < 9) && ($not_a_number));
                    if (!$not_a_number) {
                        $pattern[] = $j;
                        $prev_char_was_letter = false;
                    } else {
                        $word  .= $curr_char;
                        if ($prev_char_was_letter) {
                            $pattern[] = 0;
                        }
                        $prev_char_was_letter =true;
                    }
                }
                if ($prev_char_was_letter) {
                    $pattern[] = 0;
                }
                $this->patterns[$word] =  $pattern;
                $word_len = mb_strlen($word,$mb_encoding);
                for ($i =1; $i <= $word_len; $i++) {
                    $this->trans[mb_substr($word,0,$i)] = true;
                }
            }
        }
        fclose($f);
    }
        

    /**
     * @var $patterns  An associative array contating the hyphenation patterns
     */
    protected $patterns;
    protected $trans;


    /**
     * Hyphenates a word according to the loaded dictionary
     * @param string $word the word to be hyphenated 
     * WARNING the word is assumed to be only letters.  if you need something more general
     * see getWordParts()
     * @param bool $supress true (default)to suppress hyphenation points at the beginning/end of a word.
     * @returns array of int containing the hyphenation points.  the hyphenation points are the offsets for begining of each
     * subword.  of course, 0 is a hyphenation point.
     */
    public function HyphenateWord($word,$supress=true) {
        //I2CE::raiseError("($word)");
        $mb_encoding = $this->enc->getEncodingType();
        $use_mb = $this->enc->useMB();
        if ($use_mb) {
            $word = mb_convert_case($word,MB_CASE_LOWER,$mb_encoding);
            $word_size = mb_strlen($word,$mb_encoding);
            $period = mb_convert_encoding('.',$mb_encoding);
            $prep_word = $period . $word . $period;
        } else {
            $word = strtolower($word);
            $word_size = strlen($word);
            $prep_word = '.' . $word . '.';
        }
        if ($word_size == 0) {
            return array();
        }
        $hyphens = array();
        $hyphens = array_pad($hyphens, $word_size + 2, 0);              
        if ($use_mb) {
            $state = mb_convert_encoding('',$mb_encoding);
        } else { 
            $state = ''; 
        }
        $i = 0; //the next character
        $j = 0; //the leftmost matching character of the current pattern
        $state_len = 0;
        //I2CE::raiseError(print_r($this->patterns,true));
        while ($i < $word_size + 2) {
            if ($use_mb) {
                $c = mb_substr($prep_word,$i,1,$mb_encoding);
            } else {
                $c = $prep_word[$i];
            }
            $new_state =  $state . $c;
            //I2CE::raiseError("state($state) character($c) new_State($new_state)");
            if (array_key_exists($new_state,$this->trans) && $this->trans[$new_state]) {
                //we have found a transition
                $trans = $this->trans[$new_state];
                //I2CE::raiseError("new state has transition ($trans)");
                $state = $new_state;
                $state_len++;  
                $i++;
            } else {
                //I2CE::raiseError("No transition");
                //we have a non-empty state but no transition
                //we need to peel off the beginning characters one at a time from newstate until we find a transition
                $trans = false;
                $state_len++;
                while ( ($state_len > 0) && (!$trans) ) {
                    $j++;
                    if ($use_mb) {
                        $state = mb_substr($state,1,$state_len -1,$mb_encoding);
                    } else {
                        $state = mb_substr($state,1);
                    }
                    $state_len--;
                    if (array_key_exists($state,$this->trans) ) {
                        $trans = $this->trans[$state];
                    }
                }
                if ($trans) {
                    //I2CE::raiseError("found transition ($trans) on state ($state)");                
                }
            }
            if (array_key_exists($state,$this->patterns) && is_array($this->patterns[$state])) {
                //I2CE::raiseError("Checking paterns for state($state)");
                $pattern = $this->patterns[$state];  //try to find a matching pattern -- empty state returns empty pattern
                foreach ($this->patterns[$state] as $k=>$pattern) {
                    if (!array_key_exists($j+$k, $hyphens)) {
                        $hyphens[$j+$k]  = 0;
                    }
                    if ($hyphens[$j+$k] < $pattern) {
                        $hyphens[$j+$k] = $pattern;
                    }
                }
            } else {
                //state was not found
                break;
            }
        }
        //I2CE::raiseError("Out");
        $hyphens[0] = 0; //hyphen before the word
        $hyphens[1] = 0; //hyphen before the word
        $hyphens[$word_size+1] = 0; //hyphen after the word
        //I2CE::raiseError(print_r($hyphens,true));
        if ($supress) {  
            //clear away hyphens near word boundary
            $hyphens[2] = 0; //hyphen after first letter
            $hyphens[$word_size] = 0; //hyphen before last letter
        }
        $hyphen_positions = array(0);
        for ($i = 1; $i <= $word_size; $i++) {
            if (($hyphens[$i] % 2) == 1) {
                $hyphen_positions[] = $i-1;
            }
        }
        //I2CE::raiseError(print_r($hyphen_positions,true));
        //die();
        return $hyphen_positions;
    }

    /**
     * Visualize a hyphenation for a word
     * @param string $word the word that is to be hyphenated
     * WARNING the word is assumed to have no whitespace or periods and to be only one word
     * no digits or other special characters (unless they are already in your hypehnation dictionary)
     * @param bool $supress true (default)to suppress hyphenation points at the beginning/end of a word.
     * @returns string the hyphenated word
     */
    public function Visualize($word,$supress=TRUE) {
        $hp = $this->HyphenateWord($word,$supress);
        $num_hp = count($hp);
        if ($this->enc->useMB()) {
            $mb_encoding = $this->enc->getEncodingType();
            $out = mb_convert_encoding('',$mb_encoding);
            $hyphen = mb_convert_encoding('-',$mb_encoding);
            for ($i = 0; $i < $num_hp -1; $i++) {
                $out .= mb_substr($word,$hp[$i],$hp[$i+1] - $hp[$i],$mb_encoding) 
                    . $hyphen;
            }
            $word_length =  mb_strlen($word,$mb_encoding);
            $out .= mb_substr($word,$hp[$i],$word_length - $hp[$i],$mb_encoding);
        } else {
            $out = '';
            for ($i = 0; $i < $num_hp -1; $i++) {
                $out .= substr($word,$hp[$i],$hp[$i+1] - $hp[$i])
                    . '-';
            }
            $out .= substr($word,$hp[$i]);
        }
        return $out;
    }
        

        

        
    /**
     *  Get the  parts of a word which breaks along hyphenation points or any non-letter.
     * @param string $word the word we wish to break up
     * @param bool $supress true (default)to suppress hyphenation points at the beginning/end of a word.
     *  @returns an  the associative array has 
     *  a string 'Subword' which tells what the subword is, the int 'Offset' tells where the subword started,
     *  the int 'Length' the length of the subword, and the boolean 'IsLetter' which tells us if the 
     *  subword is a composed of letters (by the Unicode convention) or not.
     */

    public function getWordParts($word ,$supress = true){
        $word_parts = array();
        $mb_encoding = $this->enc->getEncodingType();
        $use_mb = $this->enc->useMB();
        mb_regex_encoding($mb_encoding);
        if ($use_mb) {
            $word_len = mb_strlen($word,$mb_encoding);
        } else {
            $word_len = strlen($word);
        }
        if  ($mb_encoding != 'UTF-8') {
            if ($mb_encoding) {
                $utf8_word = mb_convert_encoding($word,'UTF-8',$mb_encoding);
            } else {
                $utf8_word = mb_convert_encoding($word,'UTF-8');
            }
        } else {
            $utf8_word = $word;
        }
        $i=0;
        $prev_i = 0;
        $c =''; 
        do {
            $sub_word_len = 0;
            $is_letter = true;
            while ( ($i < $word_len) && ($is_letter) ){
                $c = mb_substr($utf8_word,0,1,'UTF-8'); //get the first character
                $utf8_word = mb_substr($utf8_word,1);  //delete the first character
                if (!preg_match('/^\p{L}/',$c)) {  
                    //the character is not a letter
                    $is_letter=false;
                } 
                $sub_word_len++;
                $i++;
            }
            if (!$is_letter) { //the last character we read was a non-letter
                $sub_word_len--;
            }
            if ($use_mb) {
                $subword =  mb_substr($word,$prev_i,$sub_word_len,$mb_encoding);
            } else {
                $subword = substr($word,$prev_i,$sub_word_len);
            } 
            if ($sub_word_len > 0 ) { 
                //get the hyphenation points for the word.
                $hp = $this->HyphenateWord($subword,$mb_encoding,$supress);
                $num_hp = count($hp);
                if ($use_mb) {
                    for ($k = 0; $k < $num_hp -1; $k++) {
                        $word_parts[] = 
                            array( 'Offset' => $prev_i + $hp[$k],
                                   'Length' => $hp[$k+1] - $hp[$k],
                                   'Subword' => mb_substr($subword,$hp[$k],$hp[$k+1] - $hp[$k],$mb_encoding),
                                   'IsLetter' => True
                                );
                    }
                    $word_parts[] =
                        array( 'Offset' => $prev_i + $hp[$num_hp-1],
                               'Length' => $sub_word_len - $hp[$num_hp -1],
                               'Subword' => mb_substr($subword,$hp[$num_hp-1],$sub_word_len - $hp[$num_hp-1],$mb_encoding),
                               'IsLetter' => True
                            );
                } else {
                    for ($k = 0; $k < $num_hp -1; $k++) {
                        $word_parts[] = 
                            array( 'Offset' => $prev_i + $hp[$k],
                                   'Length' => $hp[$k+1] - $hp[$k],
                                   'Subword' => substr($subword,$hp[$k],$hp[$k+1] - $hp[$k]),
                                   'IsLetter' => True
                                );
                    }
                    $word_parts[] = 
                        array( 'Offset' => $i + $hp[$num_hp-1],
                               'Length' => $sub_word_len - $hp[$num_hp - 1],
                               'Subword' => substr($subword,$hp[$num_hp -1]),
                               'IsLetter' => True
                            );
                }
            }
            if (!$is_letter) {
                $word_parts[] = array(   
                    'Offset'=> $i,
                    'Length'=> 1,
                    'Subword'=> $c,
                    'IsLetter' => False
                    );
            }
            $prev_i = $i;
        } while ($i < $word_len);
                
        return $word_parts;
    }

}

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
