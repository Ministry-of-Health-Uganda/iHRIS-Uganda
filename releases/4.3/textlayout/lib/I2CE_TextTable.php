<?php

/**
 * A word-wrapping text tabke class.   Uses the i2ce_hyphen class.
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

class I2CE_TextTable extends I2CE_TextCell {

    /**
     * Construct a text table object
     * @param numeric $max_width -- the maximum width of the table
     * @param I2CE_FontMetric $font_metric 
     * @param I2CE_Hyphen $hyphen  -- default is null
     * @param int $algorithm -- the word wrapping algorithm: default = 'Greedy'
     * @param I2CE_Encoding encoding -- the encoding we want to use
     * @param numeric $col_spacing -- the spacing used between the columns. Default to 0
     * @param string $widthstyle -- how we determine the width  of the columns.  Defaults to 'Header'
     */
    public function __construct($max_table_width,&
                                $font_metric,
                                &$hyphen= null,
                                &$encoding,
                                $col_spacing = 0,
                                $widthstyle = 'Header', 
                                $algorithm = 'Greedy'){
        parent::__construct($font_metric,$hyphen,$encoding,$algorithm);
        $widths = array();
        $this->widthstyle = $widthstyle;
        $this->encoding = $encoding;
        $this->font_metric = $font_metric;
        $this->max_table_width = $max_table_width;
        $this->col_spacing = $col_spacing;
        $this->min_cell_width = 0;
    }

    /**
     * protected @var I2CE_FontMetric $font_metric
     */
    protected $font_metric;

    /**
     * protected @var I2CE_Encoding $encoding
     */
    protected $encoding;

    /*
     * protected @var array $widths of numeric the widths of the columns
     */
    protected $widths;

    /**
     * protected @var numeric $col_spacing -- the spacing between columns
     */
    protected $col_spacing; 

    /*
     * protected @var numeric $max_table_width -- the maximum width of the table;
     */
    protected $max_table_width;

    /*
     * protected @var numeric $min_cell_width -- the maximum width of the table;
     */
    protected $min_cell_width;

    /*
     * protected @var string $widthsstyle how we determine the width of the columns
     */
    protected $widthstyle;


    /*
     * Set the font metric
     * @param I2CE_FontMetric $fm
     */
    public function setFontMetric($fm) {
        $this->font_metric = $fm;
    }

    /*
     * Get the font metric
     * @returns I2CE_FontMetric 
     */
    public function &getFontMetric() {
        return $this->font_metric;
    }


    /*
     * set the maximum width of the table
     * @param numeric $table_width 
     * if $width is <0 then there is no maximum
     */
    public function setMaxTableWidth($table_width) {
        $this->max_table_width = $table_width;
    }


    /*
     * Sets the minimum cell width (default value is 0)
     * @param numeric $width the cell width
     */
    public function setMinCellWidth($width = 0) {
        $this->min_cell_width = $width;
    }

    /*
     * set the spacing between columns
     * @param numeric $col_spacing
     */
    public function setColSpacing($col_spacing) {
        $this->col_spacing = $col_spacing;
    }
        
    /*
     * get the spacing between columns
     * @param numeric $col_spacing
     */
    public function getColSpacing() {
        return $this->col_spacing;
    }
        
    /**
     * Set the table's column widths
     * @param sting $widthsstyle.  Possible values are 'Explicit' the user sets the widths via setWidths().
     * 'Header' --n we try to guess good widths
     * by looking at the first row of the table. 'All' then we try to guess good widths
     * by examining all rows.
     */
    public function setWidthStyle($widthstyle) {
        $this->widthstyle = strtolower($widthstyle);            
    }
        
    /**
     * Get the currently set width of the columns. If we are using one of the algorithms
     * to determine the widths, it will be set to the widths used in the generation of the last
     * text table
     * @returns array of numeric
     */
    public function getWidths() {
        return $this->widths;
    }

    /*
     * Sets the width of the columns to be used.  Will set the width style is set to 'Explicit'
     */
    public function setWidths($widths) {
        $this->widths = $widths ;
        $this->setWidthStyle('explicit');
    }


        
    /**
     * @param array of array of string $table_data a row/col array of the text we want in the table
     * @param array $font_size.
     * if $font_size[$i] describe the font_size to be used for row $i.  if $font_size[$i] is numeric
     * then the font size applies to the whole row $i.  othwise, if it is an array of numeric, the font_size[$i][$j]
     * is the font size of cell $i,$j.
     * @param boolean $pad_cell_height (Default true) whether or not we should ensure that for each table row, we have
     * the same number of rows of text per cell
     * @returns a row/col indexed array of arrays.  each text cell becomes an array of strings, each element of which
     * is a row of text of that cell.
     */
    public function wordWrapTable($table_data,$font_size,$pad_cell_height = true) {
        $rows = count($table_data);
        $cols = array_keys($table_data[0]);
        //make a row/col array of font sizes
        $font_sizes = array();
        for ($r = 0; $r < $rows; $r++) {
            $font_sizes[$r] = array();
            foreach ($cols as $col) {
                if (is_scalar($font_size) ) {
                    $font_sizes[$r][$col] = $font_size;
                } else if (is_scalar($font_size[$r])) {
                    $font_sizes[$r][$col] = $font_size[$r];
                } else {
                    $font_sizes[$r][$col] = $font_size[$r][$col];
                }
            }
        }
        //get the widths
        switch($this->widthstyle) {
        case 'explicit':
            //do nothing
            break;
        case 'all':
            $this->widths = $this->getWidthsFromAllCells($table_data,$font_sizes);
            break;
        default:
            $this->widths = $this->getWidthsFromHeader($table_data[0],$font_sizes[0]);
            break;
        }
        $word_wrapped_table = array();
        for ($r = 0; $r < $rows; $r++) {
            $word_wrapped_table[$r] = array();
            foreach ($cols as $col) {
                $this->setWidth($this->widths[$col]);
                $this->font_metric->setFontSize($font_sizes[$r][$col]);
                $word_wrapped_table[$r][$col] =
                    $this->getLineBreaks($table_data[$r][$col]);
            }
        }
        if ($pad_cell_height) {
            if ($this->encoding->useMB()) {
                $blank_line = mb_convert_encoding("",$this->encoding->getEncodingType());
            } else {
                $blank_line = "";
            }
            for ($r = 0; $r < $rows; $r++) {
                $max_h = 0;
                foreach ($cols as $col) {
                    $h = count($word_wrapped_table[$r][$col]);
                    if ($h > $max_h) {
                        $max_h = $h;
                    }
                }
                foreach ($cols as $col) {
                    $word_wrapped_table[$r][$col] = 
                        array_pad($word_wrapped_table[$r][$col], $max_h,$blank_line);
                }
            }
        }
        return $word_wrapped_table;
                
    }


    /**
     * helper function to adjust the desired width of a column to those allowable by the maxium size
     */
    protected function adjustDesiredWidths($desired_widths,$width) {
        $cols = array_keys($desired_widths);
        //we cannot fit all the rows as desired .   
        //we will  add width to the minimum width until we get to the maximum table width
        $width_to_add = $this->font_metric->getCharacterWidth('l',true); 
        if  ($width_to_add <= 0) {
            $width_to_add = 100;
        }
        $extra_width = $width - count($cols) * ($this->min_cell_width); //the extra width we have over from the minimum
        if (($extra_width <=0 ) || ($width_to_add == 0)){ //fail nicely
            return $desired_widths;
        }
        $widths = array();
        foreach ($cols as $col) {
            $widths[$col] = $this->min_cell_width; //set all widths to the minimum
        }
        $cells_in_need = TRUE;
        $changed = FALSE; 
        $col=$cols[0];
        $c =0;
        while (($extra_width > 0) && ($cells_in_need))  {
            $desired_extra = $desired_widths[$col] - $widths[$col];
            if ($desired_extra > 0) {  //our cell wants to be bigger then it currently is
                $changed = TRUE;
                if ($desired_extra < $width_to_add) {
                    if ($extra_width >= $desired_extra) {
                        $widths[$col] += $desired_extra;
                        $extra_width -= $desired_extra;
                    } else {
                        $widths[$col] += $extra_width;
                        $extra_width = 0;                                               
                    }
                } else {
                    if ($extra_width >= $width_to_add) {
                        $widths[$col] += $width_to_add;
                        $extra_width -= $width_to_add;
                    } else {
                        $widths[$col] += $extra_width;
                        $extra_width = 0;
                    }
                }
            }
            if ($c >= count($cols)-1) { //we restart looping through
                $c = 0;
                $col = $cols[0];
                $cells_in_need = $changed; //if none were changed then we are done
            }   else { //increment our loop counter
                $c++;
                $col = $cols[$c];
            }
        }
        return $widths;

    }

    /*
     * Try's to calculate reasonable column widths by looking only at the header row
     */
    protected function getWidthsFromHeader($row_data,$font_sizes) {
        $cols = array_keys($row_data);
        $padding  = ( 2 + (count($cols) -1)*2)*$this->col_spacing; //  padding for the column spacing
        $width = $this->max_table_width - $padding; //the width we have available
        if ( ($this->max_table_width >= 0) && ( $this->min_cell_width*count($cols) > $width)) { //can't fit all the columns even minimally
            $widths = array();
            $c = 0; //so we will try to fit as many as possible
            while  ( $this->min_cell_width * ((float)$c) <= $width) {
                $widths[$cols[$c]] = $this->min_cell_width;
                $c++;
            }
            $c--;
            if ( $this->min_cell_width*$c <= $width) {
                $widths[$cols[$c]] = $width - $this->min_cell_width * ($c);
            }
            return array_pad($widths,count($cols),0);
        }
        //we know now that we can fit all the columns minimally
        $desired_widths = array();
        $total_desired_width  = 0;
        foreach ($cols as $col) {//get the maximum line length for each cell of text.
            $max = $this->min_cell_width;
            $paragraphs = $this->getParagraphs($row_data[$col]);
            foreach ($paragraphs as $paragraph) {
                $this->font_metric->setFontSize($font_sizes[$col]);
                $w = $this->font_metric->getStringWidth($paragraph,true);
                if ($w > $max) {
                    $max = $w;
                }
            } 
            $desired_widths[$col] = $max;
            $total_desired_width += $max;
        }

        if (( $this->max_table_width < 0)  || ($total_desired_width < $width )) {
            return $desired_widths;
        }
        return $this->adjustDesiredWidths($desired_widths,$width);
    }





        
    /*
     * Try's to calculate reasonable column widths by looking at all the cells
     */
    protected function getWidthsFromAllCells($table_data,$font_sizes) {
        $rows = count($table_data);
        $cols = array_keys($table_data[0]);
        $padding  =  count($cols) *2*$this->col_spacing; //  padding for the column spacing
        $width = $this->max_table_width - $padding; //the width we have available
        if ( ($this->max_table_width >= 0) && ( $this->min_cell_width*count($cols) > $width)) { //can't fit all the columns even minimally
            $widths = array();
            $c = 0; //so we will try to fit as many as possible
            while  ( $this->min_cell_width * $c <= $width) {
                $widths[$cols[$c]] = $this->min_cell_width;
                $c++;
            }
            if ( $this->min_cell_width*($c-1) < $width) {
                $widths[$cols[$c]] = $width - $this->min_cell_width * ($c-1);
            }
            return array_pad($widths,count($cols),0);
        }
        //we can fit all the columns minimally
        $desired_widths = array();
        $total_desired_width  = 0;
        foreach ($cols as $col) {//get the maximum line length for each cell of text.
            $max = $this->min_cell_width;
            for ($r = 0; $r < $rows; $r++) {
                $paragraphs = $this->getParagraphs($table_data[$r][$col]);
                foreach ($paragraphs as $paragraph) {
                    $this->font_metric->setFontSize($font_sizes[$r][$col]);
                    $w = $this->font_metric->getStringWidth($paragraph,true);
                    if ($w > $max) {
                        $max = $w;
                    }
                } 
            }
            $desired_widths[$col] = $max;
            $total_desired_width += $max;
        }
        if (( $this->max_table_width < 0)  || ($total_desired_width < $width )) {
            return $desired_widths;
        }
        return $this->adjustDesiredWidths($desired_widths,$width);
    }


    /*
     * Visulize a word wrapped table of data in simple text. 
     * @param array of array of string $table_data a row/col array of the text we want in the table
     * @param array $font_size.
     * if $font_size[$i] describe the font_size to be used for row $i.  if $font_size[$i] is numeric
     * then the font size applies to the whole row $i.  othwise, if it is an array of numeric, the font_size[$i][$j]
     * is the font size of cell $i,$j.
     */
    public function Visualize($table_data,$font_size) {
        $ww_table = $this->wordWrapTable($table_data,$font_size,true);
        $rows = count($ww_table);
        $cols= array_keys ($ww_table[0]);
        $col_size = array();
        if ($this->encoding->useMB()) {
            $plus = mb_convert_encoding("+",$this->encoding->getEncodingType());
            $newline = mb_convert_encoding("\n",$this->encoding->getEncodingType());
            $dash = mb_convert_encoding("-",$this->encoding->getEncodingType());
            $space = mb_convert_encoding(" ",$this->encoding->getEncodingType());
            $vert = mb_convert_encoding('|',$this->encoding->getEncodingType());
            $out =  mb_convert_encoding("",$this->encoding->getEncodingType());
        } else {
            $plus = "+";
            $newline = "\n";
            $dash = "-";
            $space = " ";
            $vert = '|';
            $out = "";
        }
        $lengths = array();
        for ($r = 0; $r < $rows; $r++) {
            $lengths[$r] = array();
        }
        foreach ($cols as $col) {
            $max = 0;
            for ($r = 0; $r <$rows ; $r++) {
                $z = count($ww_table[$r][$col]);
                for ($i =0; $i < $z; $i++) {
                    $line = $ww_table[$r][$col][$i];
                    if ($this->encoding->useMB()) {
                        $w = mb_strlen($line,$this->encoding->getEncodingType());
                    } else {
                        $w = strlen($line);
                    }
                    $lengths[$r][$col][$i] = $w;
                    if ($w > $max) {
                        $max = $w;
                    }
                }
            }
            $col_size[$col] = $max;
        }
        //now do the display
        for ($r = 0; $r < $rows ; $r++) {
            foreach ($cols as $col) {
                $out .= $plus;
                for ($j=0 ; $j < $col_size[$col]; $j++) {
                    $out .= $dash;
                }
            }
            $out .= $plus . $newline;
            $z = count( ($ww_table[$r][$cols[0]]) ) ;
            for ($i = 0; $i < $z; $i++) {
                foreach ($cols as $col) {
                    $out .= $vert . $ww_table[$r][$col][$i];
                    for ($j = $lengths[$r][$col][$i]; $j < $col_size[$col]; $j++) {
                        $out .= $space;
                    }
                }
                $out .= $vert . $newline;
            }
        }
        foreach ($cols as $col) {
            $out .= $plus;
            for ($j=0 ; $j < $col_size[$col]; $j++) {
                $out .= $dash;
            }
        }
        $out .= $plus . $newline;
        return $out;
                
    }
        
}



# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
