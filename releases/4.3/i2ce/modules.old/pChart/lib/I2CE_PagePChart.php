<?php
/**
 * @copyright © 20011 Intrahealth International, Inc.
 * This File is part of I2CE
 *
 * I2CE is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by
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
 * @package I2CE
 * @author Luke Duncan <lduncan@intrahealth.org>
 * @since v4.1.0
 * @version v4.1.0
 */
/**
 * Handles working with pChart to set up any defaults and display the image.
 * 
 * @package I2CE
 * @access public
 */

require_once( "pData.class.php" );
require_once( "pDraw.class.php" );
require_once( "pImage.class.php" );

abstract class I2CE_PagePChart extends I2CE_Page {

    /**
     * The pData object for this chart.
     * @var pData
     */
    protected $chartData;

    /**
     * The pImage object for this chart.
     * @var pImage
     */
    protected $chartImage;

    /**
     * The display style of the chart (pie/bar/etc.)
     * @var string
     */
    protected $style;

    /**
     * The available styles with mapped to the function to draw them.
     * @var array 
     */
    protected $styles = array(
            'area' => 'drawAreaChart',
            'bar' => 'drawBarChart',
            'filledspline' => 'drawFilledSplineChart',
            'line' => 'drawLineChart',
            'plot' => 'drawPlotChart',
            'spline' => 'drawSplineChart',
            'stackedarea' => 'drawStackedAreaChart',
            'stackedbar' => 'drawStackedBarChart',
            );
      

    /**
     * Handles creating the templates which aren't needed for this page.
     * @returns boolean
     */
    protected function initializeTemplate() {
        return true;
    }

    /**
     * Initializes any data for the page.
     * @returns boolean
     */
    protected function initPage() {
        if ( count( $this->request_remainder ) > 0 ) {
            $this->style = strtolower( array_shift( $this->request_remainder ) );
            if ( !array_key_exists( $this->style, $this->styles ) ) {
                I2CE::raiseError( "No valid style (" . $this->style . ") given for pChart." );
                return false;
            }
        } else {
            I2CE::raiseError( "No style given for pChart." );
            return false;
        }
        $this->chartData = new pData();
        if ( $this->chartData instanceof pData ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Set the data for the chart.
     * @return boolean
     */
    abstract protected function setData();

    /**
     * Draw the image.
     * @return boolean
     */
    abstract protected function drawImage();

    /**
     * Main display method for command line interface
     */
    protected function displayCommandLine() {
        $this->completeChart();
        if ( count($this->request_remainder) > 0 ) {
            $output_file = array_shift( $this->request_remainder );
        } else {
            $output_file = "pChart.out.png";
        }
        $this->chartImage->render( $output_file );
    }

    /**
     * Main display method for the web interface.
     * @param boolean $supress_output
     */
    protected function displayWeb( $supress_output = false ) {
        $this->completeChart();
        if ( !$supress_output ) {
            $this->chartImage->stroke();
        }
    }

    /**
     * Draw the chart
     */
    public function completeChart() {
        if ( $this->initPage() ) {
            if ( $this->setData() ) {
                if ( !$this->drawImage() ) {
                    $this->setupError();
                }
                $this->drawStyle();
            } else {
                $this->setupError();
            }
        } else {
            I2CE::raiseError( "Page initialization failed.");
            $this->setupError();
        }
    }

    /**
     * Draw the chart and return the pImage object.
     * @return pImage
     */
    public function getImage() {
        return $this->chartImage;
    }

    /**
     * Draw the style function for this chart.
     */
    protected function drawStyle() {
        $func = $this->styles[$this->style];
        $this->chartImage->$func( $this->getStyleOptions( $this->style ) );
    }

    /**
     * Get the options for drawing the given style.
     * @param string $style
     */
    protected function getStyleOptions( $style ) {
        return array();
    }


    /**
     * Setup the image to display an error message if something went wrong.
     */
    protected function setupError() {
        $this->chartImage = new pImage( 590, 350 );
        if ( array_key_exists( 'error_message', $this->args ) ) {
            $error_msg = $this->args['error_message'];
        } else {
            $error_msg = "An error occurred with the chart.";
            I2CE::getConfig()->setIfIsSet( $error_msg, "/modules/pChart/defaults/error_message" );
        }
        $msgs = explode( "\n", wordwrap( $error_msg, 50, "\n", true ) );
        $this->setFont( null, 20 );
        for( $place = 30; $place <= 350; $place += 30 ) {
            if ( count($msgs) == 0 ) {
                break;
            }
            $msg = array_shift( $msgs );
            $this->chartImage->drawText( 10, $place, $msg );
        }
    }


    /**
     * Setup the pImage object.
     * @param int $width
     * @param int $height
     */
    protected function setupImage( $width=590, $height=350 ) {
        $this->chartImage = new pImage( $width, $height, $this->chartData );
        $this->setFont();
    }

    /**
     * Set the font for the image object.
     * @param string $font
     * @param int $fontSize
     */
    protected function setFont( $font=null, $fontSize=null ) {
        $prop = array();
        $fontFile = null;
        if ( $font !== null ) {
            $fontFile = I2CE::getFileSearch()->search( "PCHART_FONTS", $font . ".ttf" );
        }
        if ( $fontFile === null ) {
            I2CE::getConfig()->setIfIsSet( $font, "/modules/pChart/defaults/font" );
            if ( !$font ) {
                $font = "calibri";
            }
            $fontFile = I2CE::getFileSearch()->search( "PCHART_FONTS", $font . ".ttf" );
            if ( $fontFile === null ) {
                I2CE::raiseError( "Couldn't find font $font for pChart." );
                return;
            }
        }
        $prop['FontName'] = $fontFile;
        if ( $fontSize === null ) {
            I2CE::getConfig()->setIfIsSet( $fontSize, "/modules/pChart/defaults/font_size" );
            if ( !$fontSize ) {
                $fontSize = 10;
            }
        }
        $prop['FontSize'] = $fontSize;

        $this->chartImage->setFontProperties( $prop );
    }

    /**
     * Set the palette for the data object.
     * @param string $palette
     */
    protected function setPalette( $palette=null ) {
        $paletteFile = null;
        if ( $palette !== null ) {
            $paletteFile = I2CE::getFileSearch()->search( "PCHART_PALETTES", $palette . ".color" );
        }
        if ( $paletteFile === null ) {
            I2CE::getConfig()->setIfIsSet( $palette, "/modules/pChart/defaults/palette" );
            if ( !$palette ) {
                $palette = "light";
            }
            $paletteFile = I2CE::getFileSearch()->search( "PCHART_PALETTES", $palette . ".color" );
            if ( $paletteFile === null ) {
                I2CE::raiseError( "Couldn't find palette $palette for pChart." );
                return;
            }
        }
        $this->chartData->loadPalette( $paletteFile, true );
    }



}

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
