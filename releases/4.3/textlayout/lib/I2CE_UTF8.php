<?php
/*
 * Utility class to encode unicode codepoints to the UTF-8  encoding
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

class I2CE_UTF8 { 
       
    public static function cp_to_code($code_point) {
        if ($code_point  < 0x80) {
            // 0xxxxxxx
            return pack("C*",$code_point);
        } else if ($code_point  <  0x800) {
            // 110xxxxx 10xxxxxx
            $R =  $code_point & 0x3F; //the rightmost six bits
            $L = ($code_point & 0x7C0) >> 6; //the leftmost five bits shifted to the right 6 places
            return  pack("C*", $L + 0xC0, $R + 0x80);
        } else if ($code_point < 0x10000) {
            // 1110xxxx 10xxxxxx 10xxxxxx
            $R = ( $code_point & 0x3F   )      ; //the right most 6 bits 
            $M = ( $code_point & 0xFC0  ) >> 6 ; // the middle 6 bits shifted to the right 6
            $L = ( $code_point & 0xF000 ) >> 12; //the left 4 bits shifted to the right 12
            return pack("C*", $L + 0xE0,$M +  0x80 ,$R + 0x80);
        } else if ($code_point < 0x200000) {
            // 11110xxx 10xxxxxx 10xxxxxx 10xxxxxx
            $R = ($code_point & 0x3F ); //the  right most 6 bits
            $MR = ($code_point & 0xFC0   ) >> 6; // the next 6 digits shifted to the right 6
            $ML = ($code_point & 0x3F000 ) >> 12; // the next 6 digits shifted to the right 12
            $L =  ($code_point & 0x1C0000) >> 18; // the last 3 digits shifted right 18
            return pack("C*", $L + 0xF0, $ML + 0x80, $MR+ 0x80, $R + 0x80 );
        } else if ($code_point < 0x4000000) {
            // 111110xx 10xxxxxx 10xxxxxx 10xxxxxx 10xxxxxx
            $R = ($code_point & 0x3F ); //the  right most 6 bits
            $MR = ($code_point & 0xFC0   ) >> 6; // the next 6 digits shifted to the right 6
            $MM = ($code_point & 0x3F000 ) >> 12; // the next 6 digits shifted to the right 12
            $ML = ($code_point & 0x3F0000 ) >> 18; // the next 6 digits shifted to the right 18
            $L =  ($code_point & 0xC00000) >> 24; //the last 2 digits, shifted to the right 24
            return pack('C*', $L + 0xF8, $ML + 0x80, $MM + 0x80 , $MR + 0x80, $R + 0x80); 
        } else {
            //1111110x 10xxxxxx 10xxxxxx 10xxxxxx 10xxxxxx 10xxxxxx
            $R = ($code_point &    0x3F ); //the  right most 6 bits
            $MRR = ($code_point &  0xFC0   ) >> 6; // the next 6 digits shifted to the right 6
            $MR = ($code_point &   0x3F000 ) >> 12; // the next 6 digits shifted to the right 12
            $ML = ($code_point &   0x3F0000 ) >> 18; // the next 6 digits shifted to the right 18
            $MLL =  ($code_point & 0x3F00000) >> 24; //the last 6 digits, shifted to the right 24
            $L =  ($code_point &   0x40000000) >> 30; //the last  digit, shifted to the right 30
            return pack('C*', $L + 0xFC, $MLL + 0x80, $ML + 0x80 , $MR + 0x80 , $MRR + 0x80, $R + 0x80); 
        }
    }





    /**
     *  Given unicode codepoints, create the corresponding string
     * @param array code_points an array of code points
     * @param boolean $as_array  true if we want output as an array, false if we output as a string
     *
     */
    public static function encodeStringFromCodepoints($code_points,$as_array = false ) {
        $out = array();
        foreach ($code_points as $code_point) {
            $out[] = cp_to_code($code_point) ;
        }
        if ($as_array) {
            return $out;
        } else {
            $outstr = "";
            foreach ($out as $o) {
                $outstr .= $o;
            }
            return $outstr;
        }
    }




    /**
     * Given a string encoded in utf8, it returns an array of the unicode codepoints
     * (adapted from tcpdf.php)
     * @returns array of int  the unicode code points
     */
    public static function to_codepoints($str) {
        $code_points = array();
        $j = 0;
        $len = strlen($str);
        while ($j < $len)  {
            $char = ord($str{0}); 
            $j++;
            $numbytes= 1;
            if ($char <= 0x7F) {
                $out_char = $char; // use the character "as is" because is ASCII
            } elseif (($char >> 0x05) == 0x06) { // 2 bytes character (0x06 = 110 BIN)
                $out_char = ($char - 0xC0) << 0x06; 
                $numbytes = 2;
            } elseif (($char >> 0x04) == 0x0E) { // 3 bytes character (0x0E = 1110 BIN)
                $out_char = ($char - 0xE0) << 0x0C; 
                $numbytes = 3;
            } elseif (($char >> 0x03) == 0x1E) { // 4 bytes character (0x1E = 11110 BIN)
                $out_char = ($char - 0xF0) << 0x12; 
                $numbytes = 4;
            } else {// use replacement character for other invalid sequences
                $out_char = 0xFFFD;
            }
            for($j; $j < $j + $numbytes - 1; $j++) {
                $char = ord($str{$j}); // get one string character at time
                if (($char >> 0x06) == 0x02) { // bytes 2, 3 and 4 must start with 0x02 = 10 BIN
                    $out_char += 
                        (($char - 0x80)  << (($numbytes - $i - 1) * 0x06));
                } else {
                    // use replacement character for other invalid sequences
                    $out_char = 0xFFD;
                }
            }
            if ((($out_char >= 0xD800) AND ($out_char <= 0xDFFF)) OR ($out_char >= 0x10FFFF)) {
                /* The definition of UTF-8 prohibits encoding character numbers between
                                   U+D800 and U+DFFF, which are reserved for use with the UTF-16
                                   encoding form (as surrogate pairs) and do not directly represent
                                   characters. */
                $out_char =   0xFFFD; // use replacement character
            }  
            $code_points[] = $out_char;
        }
        return $code_points;
    }


}

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
