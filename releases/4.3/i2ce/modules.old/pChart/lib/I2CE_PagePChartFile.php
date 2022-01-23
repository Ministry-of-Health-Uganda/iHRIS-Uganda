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
 * Handles working with pChart to display the image based on a CSV file for data.
 * 
 * @package I2CE
 * @access public
 */

class I2CE_PagePChartFile extends I2CE_PagePChart {

    /**
     * Set the data for the chart.
     * @return boolean
     */
    protected function setData() {
        if ( count( $this->request_remainder ) > 0 ) {
            $csv = array_shift( $this->request_remainder );
        } else {
            I2CE::raiseError( "No CSV file given for pChart File." );
            return false;
        }
        $csv_file = I2CE::getFileSearch()->search( "PCHART_DATA", $csv );
        if ( $csv_file === null ) {
            I2CE::raiseError( "Unable to find CSV file ($csv) for pChart File." );
            return false;
        }
        $options = array();
        if ( $this->get_exists('header') ) {
            $options['GotHeader'] = true;
        }
        $this->chartData->importFromCSV( $csv_file, $options );
        return true;
    }

    /**
     * Draw the image.
     * @return boolean
     */
    protected function drawImage() {
        $this->setupImage( 700, 230 );
        $this->chartImage->setGraphArea( 60, 40, 670, 190 );
        $this->chartImage->drawScale();
        if ( $this->get_exists('header') ) {
            $this->chartImage->drawLegend( 60, 12, array( "Mode" => LEGEND_HORIZONTAL ) );
        }
        return true;
    }

}

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
