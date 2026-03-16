<?php
/*
 * © Copyright 2007, 2008 IntraHealth International, Inc.
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
 * @subpackage Manage
 * @author Luke Duncan <lduncan@intrahealth.org>
 * @copyright Copyright &copy; 2007, 2008 IntraHealth International, Inc.
 * @since v2.0.0
 * @version v2.0.0
 */
/**
 * Report page for generating default reports for iHRIS Manage.
 * @package iHRIS
 * @subpackage Manage
 * @abstract
 * @access public
 */
class iHRIS_PageReportManage extends iHRIS_PageReport {

    /**
     * Return the base chart details for the PHP/SWF chart function.
     * @return array
     */
    public function getChartDefault() {
        $defaults = parent::getChartDefault();
        $defaults['series_color'] = array( '467495', '7b9f0e', 'ff9900', '00cc99', '003399', '999999' );
        //      'series_color' => array( '99cc00', '6666cc', '339933', '00cc99', 'ff9900', '003399', '999999' ),
        return $defaults;
    }

    /**
     * Setup all the report types and reports for this factory.
     * @param string $type The type of report to populate further information about.
     * @param string $report The report to populate further information about.
     */
    public function setup( $type=false, $report=false ) {
                
        $staff = new I2CE_ReportType( "staff", "Staff Lists",
                                      "person_position", "record",
                                      array( 'end_date' => array( 'comparison' => 'IS', 'value' => null ) ), true
            );
        $staff->addReport( "directory", "Staff Directory" );
        $staff->addReport( "home", "Home Contact List" );
        $staff->addReport( "emergency", "Emergency Contact List" );
        $staff->addReport( "salary", "Salary List" );

        $facility = new I2CE_ReportType( "facility", "Facility Lists", "facility", "record" );
        $facility->addReport( "facility", "Facility List" );

        $position = new I2CE_ReportType( "position", "Position Lists", "position", "record" );
        $position->addReport( "position", "Position List" );
        $position->addReport( "open", "Position Open Duration" );

        $chart = new I2CE_ReportType( "chart", "Staff Statistics",
                                      "person_position", "position",
                                      array( 'end_date' => array( 'comparison' => 'IS', 'value' => null ) ), true
            );
        //$chart->addReport( "cadreXdistrict", "Cadre by District" );
        $chart->addReport( "age", "Age Distribution" );
        $chart->addReport( "hire", "Hires per Year" );
        $chart->addReport( "nationality", "Nationality Distribution" );
        $chart->addReport( "retirement", "Retirement Planning" );

        $pie = new I2CE_ReportType( "pie", "Pie Charts", "person_position", "position",
                                    array( 'end_date' => array( 'comparison' => 'IS', 'value' => null ) ), true );
        $pie->addReport( "classification", "Classification Breakdown" );
        $pie->addReport( "job", "Job Breakdown" );
        $pie->addReport( "nationality", "Nationality Breakdown" );
                
        if ( $type && $report ) {
            switch( $type ) {
            case "staff" :
                $staff->addForm( "person", array( 'record' => 'primary' ) );
                $staff->addFormField( "person", "surname", "Surname", "view?id=" );
                $staff->addFormField( "person", "firstname", "First Name", "view?id=" );
                                        
                $staff->addForm( "person_position", array( 'record' => 'secondary', 'end_date' => array( 'comparison' => 'IS', 'value' => null ) ) );
                $staff->addFormField( "person_position", "start_date", "Hire Date" );

                $staff->addForm( "demographic", array( 'parent' => 'primary' ) );
                $staff->addFormField( "demographic", "gender", "Gender" );
                $staff->addFormField( "demographic", "birth_date", "Date of Birth" );

                $staff->addForm( "position", array( 'record' =>  array( 'form' => 'person_position', 'field' => 'position' ))) ;
                $staff->addFormField( "position", "title", "Position", "view_position?id=" );

                $staff->addForm( "contact", array( 'parent' => 'primary' ) );
                $staff->setOptionalJoin( "contact", "work", array( "contact_type" => iHRIS_Contact::TYPE_WORK ), "Work" );
                $staff->setOptionalJoin( "contact", "home", array( "contact_type" => iHRIS_Contact::TYPE_PERSONAL ), "Home" );
                $staff->setOptionalJoin( "contact", "emergency", array( "contact_type" => iHRIS_Contact::TYPE_EMERGENCY ), "Emergency" );
                $staff->addFormField( "contact", "telephone", "Telephone" );
                $staff->addFormField( "contact", "email", "Email" );
                $staff->addFormField( "contact", "address", "Address" );

                $staff->addForm( "salary", array( 'parent' => 'secondary' ) );
                $staff->setOptionalJoin( "salary", "current", array( 'end_date' => array( 'comparison' => "IS", 'value' => null ) ), "Current" );
                $staff->setOptionalJoin( "salary", "start", array( 'start_date' => array( 'comparison' => "=", 'value' => "MIN" ) ), "Starting" );
                $staff->addFormField( "salary", "salary", "Salary" );

                $staff->addForm( "facility", array( 'record' => array( 'form' => 'position', 'field' => 'facility' ) ) );
                $staff->addFormField( "facility", "name", "Facility" );

                $staff->addForm( "department", array( 'record' => array( 'form' => 'position', 'field' => 'department' ) ) );
                $staff->addFormField( "department", "name", "Department" );

                $staff->addForm( "country", array() );
                $staff->setOptionalJoin( "country", "facility", array( 'record' => array( 'form' => 'facility', 'field' => 'country' ) ), "" );
                $staff->addFormField( "country", "name", "Country" );

                $staff->addForm( "district", array( 'record' => array( 'form' => 'facility', 'field' => 'district' ) ) );
                $staff->addFormField( "district", "name", "District" );

                $staff_limit = I2CE_ReportFactory::createLimitForm( $type, $report );
                $staff_limit->addField( "country", I2CE_FormField::INT, true );
                $staff_limit->getField('country')->setMap();
                $staff_limit->addField( "facility", I2CE_FormField::INT, true );
                $staff_limit->getField('facility')->setMap();
                $staff_limit->getField('facility')->setLink( $staff_limit->getField('country'), "getOptionsByCountry" );
                $staff_limit->addField( "department", I2CE_FormField::INT, true );
                $staff_limit->getField('department')->setMap();

                $staff->addLimitForms( array( "position", "facility", "department", "country", "district" ,"person_position" ) );
                $staff->setLimitObj( $staff_limit );
                $staff->setLimitMatch( array (
                                           "country" => "country_facility_record",
                                           "facility" => "facility_record",
                                           "department" => "department_record"
                                           ) );

                switch( $report ) {
                case "directory" :
                    $staff->addReportField( $report, "surname", "person", "surname" );
                    $staff->addReportField( $report, "firstname", "person", "firstname" );
                    $staff->addReportField( $report, "position", "position", "title" );
                    $staff->addReportField( $report, "department", "department", "name" );
                    $staff->addReportField( $report, "facility", "facility", "name" );
                    $staff->addReportField( $report, "telephone", "contact", "telephone", "work" );
                    $staff->addReportField( $report, "email", "contact", "email", "work" );
                    $staff->addReportDisplay( $report, array( "surname", "firstname", "position", 
                                                              "department", "facility", "telephone", "email" ) );
                    $staff->addReportDisplay( $report, "sort", array( "surname", "firstname" ) );
                    break;
                case "home" :
                    $staff->addReportField( $report, "surname", "person", "surname" );
                    $staff->addReportField( $report, "firstname", "person", "firstname" );
                    $staff->addReportField( $report, "position", "position", "title" );
                    $staff->addReportField( $report, "department", "department", "name" );
                    $staff->addReportField( $report, "facility", "facility", "name" );
                    $staff->addReportField( $report, "telephone", "contact", "telephone", "home" );
                    $staff->addReportField( $report, "email", "contact", "email", "home" );
                    $staff->addReportField( $report, "address", "contact", "address", "home" );
                    $staff->addReportDisplay( $report, array( "surname", "firstname", "position", "department", 
                                                              "facility", "telephone", "email", "address" ) );
                    $staff->addReportDisplay( $report, "sort", array( "surname", "firstname" ) );
                    break;
                case "emergency" :
                    $staff->addReportField( $report, "surname", "person", "surname" );
                    $staff->addReportField( $report, "firstname", "person", "firstname" );
                    $staff->addReportField( $report, "position", "position", "title" );
                    $staff->addReportField( $report, "department", "department", "name" );
                    $staff->addReportField( $report, "facility", "facility", "name" );
                    $staff->addReportField( $report, "telephone", "contact", "telephone", "emergency" );
                    $staff->addReportField( $report, "email", "contact", "email", "emergency" );
                    $staff->addReportField( $report, "address", "contact", "address", "emergency" );
                    $staff->addReportDisplay( $report, array( "surname", "firstname", "position", "department", "facility",
                                                              "telephone", "email", "address" ) );
                    $staff->addReportDisplay( $report, "sort", array( "surname", "firstname" ) );
                    break;
                case "salary" :
                    $staff->addReportField( $report, "surname", "person", "surname" );
                    $staff->addReportField( $report, "firstname", "person", "firstname" );
                    $staff->addReportField( $report, "position", "position", "title" );
                    $staff->addReportField( $report, "department", "department", "name" );
                    $staff->addReportField( $report, "facility", "facility", "name" );
                    $staff->addReportField( $report, "start_date", "person_position", "start_date" );
                    $staff->addReportField( $report, "curr_salary", "salary", "salary", "current" );
                    $staff->addReportField( $report, "start_salary", "salary", "salary", "start" );
                    $staff->addReportDisplay( $report, array( "surname", "firstname", "position", "department", "facility", "start_date",
                                                              "curr_salary", "start_salary" ) );
                    $staff->addReportDisplay( $report, "sort", array( "surname", "firstname" ) );
                    break;
                }
                break;
            case "chart" :
                $chart->addForm( "person", array( 'record' => 'primary' ) );
                $chart->addFormField( "person", "nationality", "Nationality" );

                $chart->addForm( "demographic", array( 'parent' => 'primary' ) );
                $chart->addFormField( "demographic", "gender", "Gender" );
                $chart->addFormField( "demographic", "birth_date", "Date of Birth" );

                $chart->addForm( "position", array( 'record' => 'secondary' ) );
                $chart->addFormField( "position", "title", "Position", "view_position?id=" );

                $chart->addForm( "person_position", array( 'parent' => 'primary' ) );
                $chart->addFormField( "person_position", "start_date", "Start Date" );
                                        
                $chart->addForm( "facility", array( 'record' => array( 'form' => 'position', 'field' => 'facility' ) ) );
                $chart->addFormField( "facility", "name", "Facility" );

                $chart->addForm( "department", array( 'record' => array( 'form' => 'position', 'field' => 'department' ) ) );
                $chart->addFormField( "department", "name", "Department" );

                $chart->addForm( "country", array() );
                // Anything using the country doesn't need to use the join option unless using the nationality.  facility is chosen by default.
                // Should change this to a default option in the addForm method when there's time.
                $chart->setOptionalJoin( "country", "facility", array( 'record' => array( 'form' => 'facility', 'field' => 'country' ) ), "" );
                $chart->setOptionalJoin( "country", "nationality", array( 'record' => array( 'form' => 'person', 'field' => 'nationality' ) ), "" );
                $chart->addFormField( "country", "name", "Country" );

                $chart->addForm( "district", array( 'record' => array( 'form' => 'facility', 'field' => 'district' ) ) );
                $chart->addFormField( "district", "name", "District" );

                $chart->addForm( "job", array( 'record' => array( 'form' => 'position', 'field' => 'job' ) ) );
                $chart->addFormField( "job", "title", "Job Title" );

                $chart->addForm( "classification", array( 'record' => array( 'form' => 'job', 'field' => 'classification' ) ) );
                $chart->addFormField( "classification", "name", "Classification" );

                $chart_limit = I2CE_ReportFactory::createLimitForm( $type, $report );
                $chart_limit->addField( "classification", I2CE_FormField::INT, true );
                $chart_limit->getField( 'classification' )->setMap();
                $chart_limit->addField( "job", I2CE_FormField::INT, true );
                $chart_limit->getField( 'job' )->setMap();
                $chart_limit->addField( "country", I2CE_FormField::INT, true );
                $chart_limit->getField( 'country' )->setMap();
                $chart_limit->addField( "facility", I2CE_FormField::INT, true );
                $chart_limit->getField( 'facility' )->setMap();
                $chart_limit->getField( 'facility' )->setLink( $chart_limit->getField('country'), "getOptionsByCountry" );
                $chart_limit->addField( "range_start", I2CE_FormField::DATE_Y, true );
                $chart_limit->addField( "range_end", I2CE_FormField::DATE_Y, true );
                $chart_limit->addField( "show_text", I2CE_FormField::YESNO, false );
                $chart_limit->addField( "show_gender", I2CE_FormField::YESNO, false );
                $chart_limit->addField( "show_blank", I2CE_FormField::YESNO, false );

                $chart->addLimitForms( array( "person", "position", "facility", "country", "district", "classification", "job" ) );
                $chart->setLimitObj( $chart_limit );
                $chart->setLimitMatch( array (
                                           "classification" => "classification_record",
                                           "job" => "job_record",
                                           "country" => "country_facility_record",
                                           "facility" => "facility_record",
                                           "range_start" => array( "comparison" => ">=", "field" => "start_date", "func" => "YEAR(start_date)" ),
                                           "range_end" => array( "comparison" => "<=", "field" => "start_date", "func" => "YEAR(start_date)" ),
                                           ) );

                switch( $report ) {
                case "age" :
                    $chart->addReportField( $report, "birth_date", "demographic", "birth_date" );
                    $chart->addReportField( $report, "gender", "demographic", "gender" );
                    $chart->addReportField( $report, "start_date", "person_position", "start_date" );
                    $chart->addChartField( $report, "age", "CASE
WHEN birth_date IS NULL THEN '?'
WHEN DATE_ADD( birth_date, INTERVAL 17 YEAR ) > NOW() THEN '-18'
WHEN DATE_ADD( birth_date, INTERVAL 23 YEAR ) > NOW() THEN '18-23'
WHEN DATE_ADD( birth_date, INTERVAL 29 YEAR ) > NOW() THEN '24-29'
WHEN DATE_ADD( birth_date, INTERVAL 34 YEAR ) > NOW() THEN '30-34'
WHEN DATE_ADD( birth_date, INTERVAL 39 YEAR ) > NOW() THEN '35-39'
WHEN DATE_ADD( birth_date, INTERVAL 44 YEAR ) > NOW() THEN '40-44'
WHEN DATE_ADD( birth_date, INTERVAL 49 YEAR ) > NOW() THEN '45-49'
WHEN DATE_ADD( birth_date, INTERVAL 54 YEAR ) > NOW() THEN '50-54'
WHEN DATE_ADD( birth_date, INTERVAL 59 YEAR ) > NOW() THEN '55-59'
WHEN DATE_ADD( birth_date, INTERVAL 64 YEAR ) > NOW() THEN '60-64'
ELSE '65+' END", "Age" );
                    $chart->addChartField( $report, "gender", "CASE
WHEN gender = 1 THEN 'Female'
WHEN gender = 2 THEN 'Male'
ELSE '?' END", "Gender" );
                    $chart->addChartField( $report, "num", "count(*)", "Number" );
                    $chart->setChartInfo( $report, array(
                                              'chart_type' => "stacked column",
                                              'chart_value'=> array( 'position' => "cursor" ),
                                              'axis_category' => array( 'size' => 10, 'orientation' => "diagonal_down" ),
                                              ) );
                    $chart->addReportDisplay( $report, "sort", array( "age", "gender" ) );
                    break;
                case "hire" :
                    $chart->addReportField( $report, "start_date", "person_position", "start_date" );
                    $chart->addReportField( $report, "gender", "demographic", "gender" );
                    $chart->addChartField( $report, "start_year", "YEAR(start_date)", "Start Year" );
                    $chart->addChartField( $report, "gender", "CASE
WHEN gender = 1 THEN 'Female'
WHEN gender = 2 THEN 'Male'
ELSE '?' END", "Gender" );
                    $chart->addChartField( $report, "num", "count(*)", "Number" );
                    $chart->setChartInfo( $report, array(
                                              'chart_type' => "stacked column",
                                              'chart_value'=> array( 'position' => "cursor" ),
                                              'axis_category' => array( 'size' => 10, 'orientation' => "diagonal_down" ),
                                              ) );
                    $chart->addReportDisplay( $report, "sort", array( "start_year", "gender" ) );
                    break;
                case "nationality" :
                    $chart->addReportField( $report, "nationality", "country", "name", "nationality" );
                    $chart->addReportField( $report, "gender", "demographic", "gender" );
                    $chart->addReportField( $report, "start_date", "person_position", "start_date" );
                    $chart->addChartField( $report, "nationality", "nationality", "Nationality" );
                    $chart->addChartField( $report, "gender", "CASE
WHEN gender = 1 THEN 'Female'
WHEN gender = 2 THEN 'Male'
ELSE '?' END", "Gender" );
                    $chart->addChartField( $report, "num", "count(*)", "Number" );
                    $chart->setChartInfo( $report, array(
                                              'chart_type' => "stacked column",
                                              'chart_value'=> array( 'position' => "cursor" ),
                                              'axis_category' => array( 'size' => 10, 'orientation' => "diagonal_down" ),
                                              ) );
                    $chart->addReportDisplay( $report, "sort", array( "num DESC" ) );
                    break;
                case "retirement" :
                    $chart->addReportField( $report, "birth_date", "demographic", "birth_date" );
                    $chart->addReportField( $report, "start_date", "person_position", "start_date" );
                    $chart->addReportField( $report, "gender", "demographic", "gender" );
                    //$chart->addReportField( $report, "classification", "classification", "name" );
                    $chart->addChartField( $report, "retirement", "YEAR(DATE_ADD( birth_date, INTERVAL 65 YEAR ))", "Retirement Year" );
                    //$chart->addChartField( $report, "classification", "classification", "Classification" );
                    $chart->addChartField( $report, "gender", "CASE
WHEN gender = 1 THEN 'Female'
WHEN gender = 2 THEN 'Male'
ELSE '?' END", "Gender" );
                    $chart->addChartField( $report, "num", "count(*)", "Number" );
                    $chart->addChartWhere( $report, "YEAR(birth_date) BETWEEN 1900 AND YEAR(NOW())-40 AND YEAR(DATE_ADD(birth_date, INTERVAL 65 YEAR)) > YEAR(NOW())" );
                    $chart->setChartInfo( $report, array (
                                              'chart_type' => 'line',
                                              'chart_value' => 'above',
                                              'axis_category' => array( 'size' => 10, 'orientation' => "diagonal_down" ),
                                              ) );
                    $chart->addReportDisplay( $report, "sort", array( "retirement" ) );
                    break;
                }
                break;
            case "pie" :
                $pie->addForm( "position", array( 'record' => 'secondary' ) );
                $pie->addFormField( "position", "title", "Position", "view_position?id=" );
                                        
                $pie->addForm( "person", array( 'record' => 'primary' ) );
                                        
                $pie->addForm( "demographic", array( 'parent' => 'primary' ) );
                $pie->addFormField( "demographic", "gender", "Gender" );
                $pie->addFormField( "demographic", "birth_date", "Birth Date" );

                $pie->addForm( "facility", array( 'record' => array( 'form' => 'position', 'field' => 'facility' ) ) );
                $pie->addFormField( "facility", "name", "Facility" );
                                        
                $pie->addForm( "facility_type", array( 'record' => array( 'form' => 'facility', 'field' => 'facility_type' ) ) );
                $pie->addFormField( "facility_type", "name", "Facility Type" );

                $pie->addForm( "country", array() );
                $pie->setOptionalJoin( "country", "facility", 
                                       array( 'record' => array( 'form' => 'facility', 'field' => 'country' ) ), "" );
                $pie->setOptionalJoin( "country", "nationality",
                                       array( 'record' => array( 'form' => 'person', 'field' => 'nationality' ) ), "" );
                $pie->addFormField( "country", "name", "Country" );

                $pie->addForm( "district", array( 'record' => array( 'form' => 'facility', 'field' => 'district' ) ) );
                $pie->addFormField( "district", "name", "District" );

                $pie->addForm( "job", array( 'record' => array( 'form' => 'position', 'field' => 'job' ) ) );
                $pie->addFormField( "job", "title", "Job Title" );

                $pie->addForm( "classification", array( 'record' => array( 'form' => 'job', 'field' => 'classification' ) ) );
                $pie->addFormField( "classification", "name", "Classification" );

                $pie_limit = I2CE_ReportFactory::createLimitForm( $type, $report );
                $pie_limit->addField( "country", I2CE_FormField::INT, true );
                $pie_limit->getField( 'country' )->setMap();
                $pie_limit->addField( "district", I2CE_FormField::INT, true );
                $pie_limit->getField( 'district' )->setMap();
                $pie_limit->getField( 'district' )->setLink( $pie_limit->getField( 'country' ), "getOptionsByCountry" );
                $pie_limit->addField( "facility", I2CE_FormField::INT, true );
                $pie_limit->getField( 'facility' )->setMap();
                $pie_limit->getField( 'facility' )->setLink( $pie_limit->getField('district') );
                $pie_limit->addField( "facility_type", I2CE_FormField::INT, true );
                $pie_limit->getField( 'facility_type' )->setMap();
                $pie_limit->addField( "nationality", I2CE_FormField::INT, true );
                $pie_limit->getField( 'nationality' )->setMap( "country" );
                $pie_limit->addField( "gender", I2CE_FormField::INT, true );
                $pie_limit->getField( 'gender' )->setMap( "demographic", "lookupGender", "listGenderOptions" );
                $pie_limit->addField( "show_text", I2CE_FormField::YESNO, false );

                $pie->addLimitForms( array( "position", "facility", "person", "country", "district", "job", "facility_type" ) );
                $pie->setLimitObj( $pie_limit );
                $pie->setLimitMatch( array (
                                         "country" => "country_facility_record",
                                         "district" => "district_record",
                                         "facility" => "facility_record",
                                         "facility_type" => "facility_type_record",
                                         "nationality" => "country_nationality_record",
                                         "gender" => "gender",
                                         ) );

                switch( $report ) {
                case "classification" :
                    $pie->addReportField( $report, "classification", "classification", "name" );
                    $pie->addReportField( $report, "nationality", "country", "name", "nationality", "Nationality" );
                    $pie->addReportField( $report, "gender", "demographic", "gender" );
                    $pie->addReportField( $report, "birth_date", "demographic", "birth_date" );
                    $pie->addChartField( $report, "classification", "classification", "Classification" );
                    $pie->addChartField( $report, "num", "count(*)", "Number" );
                    $pie->setChartInfo( $report, array(
                                            'chart_type' => "pie",
                                            'chart_value'=> array( 'position' => "inside" ),
                                            //'axis_category' => array( 'size' => 10, 'orientation' => "diagonal_down" ),
                                            ) );
                    $pie->addReportDisplay( $report, "sort", array( "num DESC" ) );
                    break;
                case "job" :
                    $pie->addReportField( $report, "job", "job", "title" );
                    $pie->addReportField( $report, "nationality", "country", "name", "nationality", "Nationality" );
                    $pie->addReportField( $report, "gender", "demographic", "gender" );
                    $pie->addReportField( $report, "birth_date", "demographic", "birth_date" );
                    $pie->addChartField( $report, "job", "job", "Job" );
                    $pie->addChartField( $report, "num", "count(*)", "Number" );
                    $pie->setChartInfo( $report, array(
                                            'chart_type' => "pie",
                                            'chart_value'=> array( 'position' => "cursor" ),
                                            //'axis_category' => array( 'size' => 10, 'orientation' => "diagonal_down" ),
                                            ) );
                    $pie->addReportDisplay( $report, "sort", array( "num DESC" ) );
                    break;
                case "nationality" :
                    $pie->addReportField( $report, "nationality", "country", "name", "nationality", "Nationality" );
                    $pie->addReportField( $report, "gender", "demographic", "gender" );
                    $pie->addReportField( $report, "birth_date", "demographic", "birth_date" );
                    $pie->addChartField( $report, "nationality", "nationality", "Nationality" );
                    $pie->addChartField( $report, "num", "count(*)", "Number" );
                    $pie->setChartInfo( $report, array(
                                            'chart_type' => "pie",
                                            'chart_value'=> array( 'position' => "inside" ),
                                            //'axis_category' => array( 'size' => 10, 'orientation' => "diagonal_down" ),
                                            ) );
                    $pie->addReportDisplay( $report, "sort", array( "num DESC" ) );
                    break;
                }
                break;
            case "facility" :
                $facility->addForm( "facility", array( 'record' => 'primary' ) );
                $facility->addFormField( "facility", "name", "Facility" );
                $facility->addFormField( "facility", "facility_type", "Facility Type" );

                $facility->addForm( "contact", array( 'parent' => 'primary' ) );
                $facility->setOptionalJoin( "contact", "facility", array( "contact_type" => iHRIS_Contact::TYPE_FACILITY ), "Facility" );
                $facility->addFormField( "contact", "telephone", "Telephone" );
                $facility->addFormField( "contact", "email", "Email" );
                $facility->addFormField( "contact", "address", "Address" );
                $facility->addFormField( "contact", "notes", "Notes" );

                $facility->addForm( "country", array() );
                $facility->setOptionalJoin( "country", "facility", array( 'record' => array( 'form' => 'facility', 'field' => 'country' ) ), "" );
                $facility->addFormField( "country", "name", "Country" );

                $facility->addForm( "district", array( 'record' => array( 'form' => 'facility', 'field' => 'district' ) ) );
                $facility->addFormField( "district", "name", "District" );

                $facility_limit = I2CE_ReportFactory::createLimitForm( $type, $report );
                $facility_limit->addField( "country", I2CE_FormField::INT, true );
                $facility_limit->getField('country')->setMap();
                $facility_limit->addField( "district", I2CE_FormField::INT, true );
                $facility_limit->getField('district')->setMap();
                $facility_limit->getField('district')->setLink( $facility_limit->getField('country'), "getOptionsByCountry" );
                $facility_limit->addField( "facility_type", I2CE_FormField::INT, true );
                $facility_limit->getField('facility_type')->setMap();

                $facility->addLimitForms( array( "facility", "country", "district" ) );
                $facility->setLimitObj( $facility_limit );
                $facility->setLimitMatch( array (
                                              "country" => "country_facility_record",
                                              "facility_type" => "facility_type",
                                              ) );
                switch( $report ) {
                case "facility" :
                    $facility->addReportField( $report, "name", "facility", "name" );
                    $facility->addReportField( $report, "facility_type", "facility", "facility_type" );
                    $facility->addReportField( $report, "country", "country", "name", "facility" );
                    $facility->addReportField( $report, "district", "district", "name" );
                    $facility->addReportField( $report, "address", "contact", "address", "facility", "Address" );
                    $facility->addReportField( $report, "telephone", "contact", "telephone", "facility", "Telephone" );
                    $facility->addReportField( $report, "email", "contact", "email", "facility", "Email" );
                    $facility->addReportField( $report, "notes", "contact", "notes", "facility", "Contact Notes" );
                    $facility->addReportDisplay( $report, array( "name", "facility_type", "country", "district", "address", 
                                                                 "telephone", "email", "notes" ) );
                    $facility->addReportDisplay( $report, "sort", array( "name" ) );
                    break;
                }
                break;
            case "position" :

                $position->addForm( "position", array( 'record' => 'primary' ) );
                $position->addFormField( "position", "title", "Title", "view_position?id=" );
                $position->addFormField( "position", "code", "Code", "view_position?id=" );
                $position->addFormField( "position", "status", "Status" );
                $position->addFormField( "position", "posted_date", "Date Posted" );

                $position->addForm( "person_position", array( 'position' => 'primary' ) );
                $position->setOptionalJoin( "person_position", "current", array( 'end_date' => array( 'comparison' => "IS", 'value' => null ) ), "Current" );
                $position->setOptionalJoin( "person_position", "last", array( 'end_date' => array( 'comparison' => "=", 'value' => "MAX" ) ), "Last" );
                $position->addFormField( "person_position", "start_date", "Start Date" );
                $position->addFormField( "person_position", "end_date", "End Date" );

                $position->addForm( "job", array( 'record' => array( 'form' => 'position', 'field' => 'job' ) ) );
                $position->addFormField( "job", "title", "Job Title", "view_job?id=" );

                $position->addForm( "classification", array( 'record' => array( 'form' => 'job', 'field' => 'classification' ) ) );
                $position->addFormField( "classification", "name", "Classification" );

                $position->addForm( "cadre", array( 'record' => array( 'form' => 'job', 'field' => 'cadre' ) ) );
                $position->addFormField( "cadre", "name", "Cadre" );

                $position->addForm( "facility", array( 'record' => array( 'form' => 'position', 'field' => 'facility' ) ) );
                $position->addFormField( "facility", "name", "Facility" );

                $position->addForm( "department", array( 'record' => array( 'form' => 'position', 'field' => 'department' ) ) );
                $position->addFormField( "department", "name", "Department" );

                $position->addForm( "country", array() );
                $position->setOptionalJoin( "country", "facility", array( 'record' => array( 'form' => 'facility', 'field' => 'country' ) ), "" );
                $position->addFormField( "country", "name", "Country" );

                $position->addForm( "district", array( 'record' => array( 'form' => 'facility', 'field' => 'district' ) ) );
                $position->addFormField( "district", "name", "District" );

                $pos_limit = I2CE_ReportFactory::createLimitForm( $type, $report );
                $pos_limit->addField( 'country', I2CE_FormField::INT, true );
                $pos_limit->getField( 'country')->setMap();
                $pos_limit->addField( 'facility', I2CE_FormField::INT, true );
                $pos_limit->getField( 'facility' )->setMap();
                $pos_limit->getField( 'facility' )->setLink( $pos_limit->getField('country'), "getOptionsByCountry" );
                $pos_limit->addField( 'department', I2CE_FormField::INT, true );
                $pos_limit->getField( 'department' )->setMap();
                $pos_limit->addField( 'job', I2CE_FormField::INT, falsetrue );
                $pos_limit->getField( 'job' )->setMap();
                $pos_limit->addField( 'classification', I2CE_FormField::INT, true );
                $pos_limit->getField( 'classification' )->setMap();
                $pos_limit->addField( 'cadre', I2CE_FormField::INT, true );
                $pos_limit->getField( 'cadre' )->setMap();
                $pos_limit->addField( 'status', I2CE_FormField::INT, true );
                $pos_limit->getField( 'status' )->setMap( "position", "lookupStatus", "listStatusOptions" );

                $position->addLimitForms( array( "position", "facility", "department", "job", "classification", "cadre", "country", "district" ) );
                $position->setLimitObj( $pos_limit );
                $position->setLimitMatch( array (
                                              "country" => "country_facility_record",
                                              "facility" => "facility_record",
                                              "department" => "department_record",
                                              "job" => "job_record",
                                              "classification" => "classification_record",
                                              "cadre" => "cadre_record",
                                              "status" => "status",
                                              ) );

                switch( $report ) {
                case "position" :
                    $position->addReportField( $report, "title", "position", "title" );
                    $position->addReportField( $report, "code", "position", "code" );
                    $position->addReportField( $report, "status", "position", "status" );
                    $position->addReportField( $report, "job", "job", "title" );
                    $position->addReportField( $report, "classification", "classification", "name" );
                    $position->addReportField( $report, "cadre", "cadre", "name" );
                    $position->addReportField( $report, "facility", "facility", "name" );
                    $position->addReportField( $report, "department", "department", "name" );
                    $position->addReportDisplay( $report, array( "title", "code", "status", "job", "classification", "cadre",
                                                                 "facility", "department" ) );
                    $position->addReportDisplay( $report, "sort", array( "title", "code" ) );
                    break;
                case "open" :
                    $position->addReportField( $report, "title", "position", "title" );
                    $position->addReportField( $report, "code", "position", "code" );
                    $position->addReportField( $report, "status", "position", "status" );
                    $position->addReportField( $report, "posted_date", "position", "posted_date" );
                    $position->addReportField( $report, "start_date", "person_position", "start_date", "current" );
                    $position->addReportField( $report, "end_date", "person_position", "end_date", "last" );
                    $position->addReportDisplay( $report, array( "title", "code", "status", "posted_date" ) );
                    $position->addReportDisplay( $report, "open_days", array( 
                                                     "func" => "DATEDIFF( IFNULL(start_date, NOW()), IFNULL(end_date, posted_date))",
                                                     "header" => "Days Open",
                                                     ) );
                    $position->addReportDisplay( $report, "start_date", array( "header" => "Date Filled" ) );
                    $position->addReportDisplay( $report, "end_date", array( "header" => "Date Opened" ) );
                    $position->addReportDisplay( $report, "sort", array( "title", "code" ) );
                    break;
                }
                break;
            case "skill" :
                $skill->addForm( "person", array( 'record' => 'primary' ) );
                $skill->addFormField( "person", "nationality", "Nationality" );

                $skill->addForm( "demographic", array( 'parent' => 'primary' ) );
                $skill->addFormField( "demographic", "gender", "Gender" );

                $skill->addForm( "competency", array( 'parent' => 'primary' ) );
                $skill->addFormField( "competency", "name", "Competency" );

                $skill->addForm( "competency_type", array( 'record' => array( 'form' => 'competency', 'field' => 'competency_type' ) ) );
                $skill->addFormField( "competency_type", "name", "Competency Type" );

                $skill_limit = I2CE_ReportFactory::createLimitForm( $type, $report );
                $skill_limit->addField( "competency_type", I2CE_FormField::INT, true );
                $skill_limit->getField( 'competency_type' )->setMap();
                $skill_limit->addField( "show_text", I2CE_FormField::YESNO, false );
                $skill_limit->addField( "show_gender", I2CE_FormField::YESNO, false );

                $skill->addLimitForms( array( "person", "competency_type" ) );
                $skill->setLimitObj( $skill_limit );
                $skill->setLimitMatch( array (
                                           "competency_type" => "competency_type_record",
                                           ) );

                switch( $report ) {
                case "competency" :
                    $skill->addReportField( $report, "competency", "competency", "name" );
                    $skill->addReportField( $report, "gender", "demographic", "gender" );
                    $skill->addChartField( $report, "competency", "competency", "Competency" );
                    $skill->addChartField( $report, "gender", "CASE
WHEN gender = 1 THEN 'Female'
WHEN gender = 2 THEN 'Male'
ELSE '?' END", "Gender" );
                    $skill->addChartField( $report, "num", "count(*)", "Number" );
                    $skill->setChartInfo( $report, array(
                                              'chart_type' => "stacked column",
                                              'chart_value'=> array( 'position' => "cursor" ),
                                              'axis_category' => array( 'size' => 10, 'orientation' => "diagonal_down" ),
                                              ) );
                    $skill->addReportDisplay( $report, "sort", array( "competency", "gender" ) );
                    break;                                              
                }
                break;
            }


        }

        $factory = I2CE_ReportFactory::instance();
        $factory->addType( $staff );
        $factory->addType( $facility );
        $factory->addType( $position );
        $factory->addType( $chart );
        $factory->addType( $pie );

    }

}


# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
