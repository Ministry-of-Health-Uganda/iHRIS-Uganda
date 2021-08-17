<?php
/*
 * © Copyright 2013 IntraHealth International, Inc.
 * 
 * This File is part of iHRIS
 * 
 * iHRIS is free software; you can redistribute it and/or modify
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
 */
/**
 * @package iHRIS
 * @subpackage Uganda-Manage
 * @access public
 * @author Luke Duncan <lduncan@intrahealth.org>
 * @copyright Copyright &copy; 2013 IntraHealth International, Inc. 
 * @since v4.1.6
 * @version v4.1.6
 */

/**
 * The page class for displaying the audit report
 * @package iHRIS
 * @subpackage Uganda-Manage
 * @access public
 */
class iHRIS_PageUgandaDistrictsAuditReport extends I2CE_Page {
        
    /**
     * Perform the main actions of the page.
     */
    protected function action() {
        parent::action();
        $this->template->setAttribute( "class", "active", "menuAuditPage", "a[@href='audit_report_districts']" );
        if ( $this->get_exists('district') ) {
            $this->action_display( $this->get('district') );
        } else {
            $this->action_district();
        }
    }

    /**
     * Sort function for districts
     * @param array $a
     * @param array $b
     * @return integer
     */
    protected static function sort_district( $a, $b ) {
        return strcmp( $a['display'], $b['display'] );
    }

    /**
     * Display the district list for this page.
     */
    protected function action_district() {
        $districts = I2CE_List::listOptions( "district" );
        usort( $districts, "self::sort_district" );
        $qry = "SELECT DISTINCT `district+id` FROM zebra_staff_norm";
        $db = I2CE::PDO();
        try {
            $stmt = $db->prepare( $qry);
            $stmt->execute();
            $this->template->addFile( "audit_report_list.html" );
            $district_field = 'district+id';
            $found = array();
            while ( $data  = $stmt->fetch() ) {
                $found[ $data->$district_field ] = true;
            }
            $stmt->closeCursor();
            foreach( $districts as $district ) {
                if ( $found[ $district['value'] ] ) {
                    $node = $this->template->appendFileById( "audit_report_list_line.html",
                            "li", "district_list" );
                    $this->template->setDisplayDataImmediate( "district_name",
                            $district['display'], $node );
                    $this->template->setDisplayDataImmediate( "district_link", array("district" => $district['value'] ), $node );
                }
            }
        } catch ( PDOException $e ) {
            I2CE::pdoError( $e,  "Unable to get district list.");
            $this->template->addFile( 'audit_report_list_error.html' );
        }
    }

    /**
     * Display the CSV data for this page.
     * @param string $district 
     */
    protected function action_display( $district=null ) {

        $this->template->addHeaderLink( 'mootools-core.js' );
        $this->template->addHeaderLink( 'select-text.js' );
        $this->template->addHeaderLink( 'audit_report.css' );
        $districtNode = $this->template->addFile( "audit_report_district.html" );
        $this->template->addFile( "audit_report_select.html", "p" );
        $districtName = "Unknown";
        if ( strpos( $district, '|' ) !== false ) {
            list( $form, $id ) = explode( '|', $district );
            $districtName = I2CE_List::lookup( $id, $form );
        }
        $this->template->setDisplayData( "district_name", $districtName, $districtNode );

	    $db = I2CE::PDO();
        $qry = "SELECT `job+title` AS title, `salary_grade+name` AS salary_grade,`facility+id` AS facility, `facility+name` AS facility_name, `primary_form+amount` AS amount , `+filled_positions` AS filled_positions , `+variance` AS variance, `+percentage_filled` AS percentage_filled FROM `zebra_staff_norm` WHERE `district+id` = '" . I2CE_PDO::escape_string( $district ) . "' ORDER BY `facility+name`, `salary_grade+id`, `job+title`";
        //I2CE::raiseMessage("Query: " . $qry);
        try {
            $stmt = $db->prepare( $qry);
            $stmt->execute();
            $last_facility = 'NOTSET';
            $totals = array( 'amount' => 0, 'filled' => 0, 'variance' => 0,'percentage_filled' => 0 );
            $faciltyNode = null;
            while( $data = $stmt->fetch() ) {
                if ( $data->facility != $last_facility ) {
                    if ( $last_facility != 'NOTSET' ) {
                       if ($data->amount == 0 && $data->filled_positions == 0  ){
               		    }else{
		                    $totalNode = $this->template->appendFileByName( "audit_report_facility_total.html", "tr", 
                                "establishment_list", 0, $facilityNode );
                            foreach( $totals as $type => $total ) {
                                $this->template->setDisplayDataImmediate( "total_$type", $total, $totalNode );
                    	    }
		                }
                    }
		            if ($data->amount == 0 && $data->filled_positions == 0  ){
               	    } else {
                        $facilityNode = $this->template->appendFileById( "audit_report_facility.html", 'div', 'facility_list' );
                        $this->template->setDisplayDataImmediate( 'facility_name', $data->facility_name, $facilityNode );
                        $establishment_count = 1;
                        $totals = array( 'amount' => 0, 'filled' => 0, 'variance' => 0, 'percentage_filled' => 0 );
               	   }
                }
                if ($data->amount == 0 && $data->filled_positions == 0  ){
                } else {
                    $establishmentNode = $this->template->appendFileByName( "audit_report_facility_establishment.html", "tr", 
                            "establishment_list", 0, $facilityNode );
                    $this->template->setDisplayDataImmediate( 'establishment_no', $establishment_count, $establishmentNode );
                    foreach( array( 'title', 'salary_grade', 'amount', 'filled_positions', 'variance','percentage_filled' ) as $field ) {

                        $this->template->setDisplayDataImmediate( $field, $data->{$field}, $establishmentNode );

                    }
                    $totals['amount'] += $data->amount;
                    $totals['filled'] += $data->filled_positions;
                    $totals['variance'] += $data->variance;
                    $totals['percentage_filled'] = round((($totals['filled']/ $totals['amount']) * 100),1);
                    $last_facility = $data->facility;
                    $establishment_count++;
                }
            }
            if ( $facilityNode && $facilityNode instanceof DOMNode ) {
                $totalNode = $this->template->appendFileByName( "audit_report_facility_total.html", "tr", 
                        "establishment_list", 0, $facilityNode );
                foreach( $totals as $type => $total ) {
                    $this->template->setDisplayDataImmediate( "total_$type", $total, $totalNode );
                }
            }
        } catch ( PDOException $e ){
            I2CE::pdoError( $e,  "Unable to get audit results:");
            $this->userMessage("An error has occurred: " . $e->getMessage() );
            $this->action_district();
        }
    }

}


# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
