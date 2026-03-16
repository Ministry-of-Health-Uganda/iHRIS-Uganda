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
 * @subpackage Qualify
 * @author Luke Duncan <lduncan@intrahealth.org>
 * @copyright Copyright &copy; 2007, 2008 IntraHealth International, Inc.
 * @since v2.0.0
 * @version v2.0.0
 */
/**
 * Report page for generating default reports for iHRIS Qualify.
 * @package iHRIS
 * @subpackage Qualify
 * @abstract
 * @access public
 */




class iHRIS_PageReportQualify extends iHRIS_PageReport {

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
     * Setup all the report types and reports for this page.
     * @param string $type The type of report to populate further information about.
     * @param string $report The report to populate further information about.
     */
    public function setup( $type=false, $report=false ) {
        if (!$type && !$report) {
            $factory = I2CE_FormFactory::instance();
            $factory->register( "report_limit", "I2CE_ReportLimit" );
        }
        $chart = new I2CE_ReportType( "chart", "Training Charts", "training", "record" );
        $chart->setParent( 2 );

        $chart->addReport( "enter_by_year", "Number of students that entered training by intake year." );
        $chart->addReport( "current_by_year", "Number of students currently in training by intake year." );
        $chart->addReport( "pie_exam", "How many students took the final exam and registered?" );
        $chart->addReport( "pie_pass", "What are the pass/fail results for the national exam?" );
        $chart->addReport( "exam_by_year", "What are the pass/fail results by exam year?" );
        $chart->addReport( "reg_by_year", "How many students passed the national exam and registered by year?" );
        $chart->addReport( "register", "How many students passed the national exam and registered by school district?" );
        $chart->addReport( "license", "Number of licensed health workers by cadre." );
                
        $reg = new I2CE_ReportType( "reg", "Registrations", "registration", "record", array(), true );
        $reg->addReport( "reg_list", "List of registered health workers." );
                
        $license = new I2CE_ReportType( "license", "Licenses", "license", "record", array(), true );
        $license->addReport( "lic_list", "List of licensed health workers." );
                
        $discontinuation = new I2CE_ReportType( "discontinuation", "Discontinuation Reports", 
                                                "training_disrupt", "record" );
        $discontinuation->setParent( 2 );
        $discontinuation->addReport( "by_reason", "Number of students who discontinued training by reason for discontinuation" );
        $discontinuation->addReport( "by_year", "Number of students who discontinued training by year of discontinuation." );
        $discontinuation->addReport( "resumption_rate", "Number of students who discontinued training and did or didn't resume training." );
                
                
        $people = new I2CE_ReportType( "people", "People Charts", "person", "record" );
        $people->addReport( "birth_district", "What are the top birth districts of students entering training?" );
        $people->addReport( "deployment", "Where are people deployed?" );
                
        $institution = new I2CE_ReportType( "institution", "Training Institutions", "training_institution", "record" );
        $institution->addReport( "list", "Display list of training institutions" );
                
        if ( $type && $report ) {
            switch( $type ) {
            case "chart" :
                $chart->addForm( "training", array( "record" => "primary" ) );
                $chart->addFormField( "training", "intake_date", "Intake Date" );
                $chart->addFormField( "training", "graduation", "Graduation Date" );
                                        
                $chart->addForm( "exam", array( "parent" => "primary" ) );
                $chart->addFormField( "exam", "results", "Exam Results" );
                $chart->addFormField( "exam", "exam_date", "Exam Date" );
                                        
                $chart->addForm( "registration", array( "parent" => "primary" ) );
                $chart->addFormField( "registration", "registration_number", "Registration Number" );
                $chart->addFormField( "registration", "registration_date", "Registration Date" );
                                        
                $chart->addForm( "license", array( "parent" => "primary" ) );
                $chart->addFormField( "license", "license_number", "License Number" );
                $chart->addFormField( "license", "start_date", "License Date" );
                                                                                
                $chart->addForm( "demographic", array( "parent" => "secondary" ) );
                $chart->addFormField( "demographic", "gender", "Gender" );
                                        
                $chart->addForm( "cadre", array( "record" => array( 'form' => "training", 'field' => "cadre" ) ) );
                $chart->addFormField( "cadre", "name", "Cadre" );
                                        
                $chart->addForm( "training_institution", array( "record" => array( 'form' => "training", 'field' => "training_institution" ) ) );
                $chart->addFormField( "training_institution", "name", "Training Institution" );
                                        
                $chart->addForm( "country", array( "record" => array( 'form' => "training_institution", 'field' => "country" ) ) );
                $chart->addFormField( "country", "name", "School Country" );
                $chart->addForm( "district", array( "record" => array( 'form' => "training_institution", 'field' => "district" ) ) );
                $chart->addFormField( "district", "name", "School District" );
                                        
                $chart->addForm( "training_disrupt", array( "parent" => "primary", 
                                                            "resumption_date" => array( 'comparison' => 'IS', 'value' => null ) ) );
                $chart->addFormField( "training_disrupt", "disruption_date", "Disruption Date" );
                                        
                $chart->addForm( "person", array( "record" => "secondary" ) );
                $chart->addFormField( "person", "surname", "Surname" );
                $chart->addFormField( "person", "firstname", "First Name" );

                $chart_limit = I2CE_ReportFactory::createLimitForm( $type, $report );
                $chart_limit->addField( "cadre", I2CE_FormField::INT, true );
                $chart_limit->getField( 'cadre' )->setMap();
                $chart_limit->addField( "country", I2CE_FormField::INT, true );
                $chart_limit->getField( 'country' )->setMap();
                $chart_limit->addField( "district", I2CE_FormField::INT, true );
                $chart_limit->getField( 'district' )->setMap();
                $chart_limit->getField( 'district' )->setLink( $chart_limit->getField( 'country' ), "getOptionsByCountry" );
                $chart_limit->addField( "range_start", I2CE_FormField::DATE_Y, true );
                $chart_limit->addField( "range_end", I2CE_FormField::DATE_Y, true );
                $chart_limit->addField( "show_text", I2CE_FormField::YESNO, false );
                $chart_limit->addField( "gender", I2CE_FormField::INT, true );
                $chart_limit->getField( 'gender' )->setMap( "demographic", "lookupGender", "listGenderOptions" );

                $chart->addLimitForms( array( "training", "cadre", "demographic", "training_institution", "country", "district" ) );
                $chart->setLimitObj( $chart_limit );
                $chart->setLimitMatch( array (
                                           "cadre" => "cadre_record",
                                           "country" => "country_record",
                                           "district" => "district_record",
                                           "gender" => "gender",
                                           "range_start" => array( "comparison" => ">=", "field" => "intake_date", "func" => "YEAR(intake_date)" ),
                                           "range_end" => array( "comparison" => "<=", "field" => "intake_date", "func" => "YEAR(intake_date)" ),
                                           ) );
                                        
                switch( $report ) {
                case "exam" :
                    //$chart->addReportField( $report, )
                    break;
                case "pie_exam" :
                    $chart->addReportField( $report, "registration_number", "registration", "registration_number" );
                    $chart->addReportField( $report, "results", "exam", "results" );
                    $chart->addReportField( $report, "gender", "demographic", "gender" );
                    $chart->addReportField( $report, "intake_date", "training", "intake_date" );
                    $chart->addChartField( $report, "pass", "CASE
WHEN registration_number IS NOT NULL THEN 'Passed and Registered'
WHEN results = " . iHRIS_Exam::RESULT_PASS . " THEN 'Passed, not Registered'
WHEN results = " . iHRIS_Exam::RESULT_FAIL . " THEN 'Did Not Pass Exam'
ELSE 'Did Not Take Exam' END", "Training Results" );
                    $chart->addChartField( $report, "num", "count(*)", "Number" );
                    $chart->setChartInfo( $report, array(
                                              'chart_type' => "pie",
                                              'chart_value'=> array( 'position' => "inside" ),
                                              //'axis_category' => array( 'size' => 10, 'orientation' => "diagonal_down" ),
                                              ) );
                    $chart->addReportDisplay( $report, "sort", array( "num DESC" ) );
                    break;
                case "pie_pass" :
                    $chart->addReportField( $report, "results", "exam", "results" );
                    $chart->addReportField( $report, "gender", "demographic", "gender" );
                    $chart->addReportField( $report, "intake_date", "training", "intake_date" );
                    $chart->addChartField( $report, "pass", "CASE
WHEN results = " . iHRIS_Exam::RESULT_PASS . " THEN 'Passed Exam'
WHEN results = " . iHRIS_Exam::RESULT_FAIL . " THEN 'Failed Exam' END", "Exam Results" );
                    $chart->addChartField( $report, "num", "count(*)", "Number" );
                    $chart->addChartWhere( $report, "results IN (" . iHRIS_Exam::RESULT_PASS . "," . iHRIS_Exam::RESULT_FAIL . " )" );
                    $chart->setChartInfo( $report, array(
                                              'chart_type' => "pie",
                                              'chart_value'=> array( 'position' => "inside" ),
                                              //'axis_category' => array( 'size' => 10, 'orientation' => "diagonal_down" ),
                                              ) );
                    $chart->addReportDisplay( $report, "sort", array( "num DESC" ) );
                    break;
                case "register" :
                    $chart->addReportField( $report, "registration_number", "registration", "registration_number" );
                    $chart->addReportField( $report, "results", "exam", "results" );
                    $chart->addReportField( $report, "gender", "demographic", "gender" );
                    $chart->addReportField( $report, "intake_date", "training", "intake_date" );
                    $chart->addReportField( $report, "district", "district", "name" );
                    $chart->addChartField( $report, "district", "district", "School District" );
                    $chart->addChartField( $report, "pass", "CASE
WHEN registration_number IS NULL THEN 'Passed Exam, Not Registered'
ELSE 'Passed Exam and Registered' END", "Registered?" );
                    $chart->addChartField( $report, "num", "count(*)", "Number" );
                    $chart->addChartWhere( $report, "results = " . iHRIS_Exam::RESULT_PASS );
                    $chart->setChartInfo( $report, array(
                                              'chart_type' => "stacked column",
                                              'chart_value'=> array( 'position' => "cursor" ),
                                              'axis_category' => array( 'size' => 10, 'orientation' => "diagonal_down" ),
                                              ) );
                    $chart->addReportDisplay( $report, "sort", array( "district" ) );
                    break;
                case "enter_by_year" :
                    $chart->addReportField( $report, "intake_date", "training", "intake_date" );
                    $chart->addReportField( $report, "gender", "demographic", "gender" );
                    $chart->addChartField( $report, "intake_year", "YEAR( intake_date )", "Year" );
                    $chart->addChartField( $report, "num", "count(*)", "Number" );
                    $chart->setChartInfo( $report, array(
                                              'chart_type' => "stacked column",
                                              'chart_value'=> array( 'position' => "cursor" ),
                                              'axis_category' => array( 'size' => 10, 'orientation' => "diagonal_down" ),
                                              ) );
                    $chart->addReportDisplay( $report, "sort", array( "intake_year" ) );
                    break;
                case "current_by_year" :
                    $chart->addReportField( $report, "intake_date", "training", "intake_date" );
                    $chart->addReportField( $report, "gender", "demographic", "gender" );
                    $chart->addReportField( $report, "graduation", "training", "graduation" );
                    $chart->addReportField( $report, "exam_date", "exam", "exam_date" );
                    $chart->addReportField( $report, "disruption_date", "training_disrupt", "disruption_date" );
                    $chart->addChartField( $report, "intake_year", "YEAR( intake_date )", "Year" );
                    $chart->addChartField( $report, "num", "count(*)", "Number" );
                    $chart->addChartWhere( $report, "disruption_date IS NULL" );
                    $chart->addChartWhere( $report, "graduation IS NULL" );
                    $chart->addChartWhere( $report, "exam_date IS NULL" );
                    $chart->setChartInfo( $report, array(
                                              'chart_type' => "stacked column",
                                              'chart_value'=> array( 'position' => "cursor" ),
                                              'axis_category' => array( 'size' => 10, 'orientation' => "diagonal_down" ),
                                              ) );
                    $chart->addReportDisplay( $report, "sort", array( "intake_year" ) );
                    break;
                case "exam_by_year" :
                    $chart->addReportField( $report, "exam_date", "exam", "exam_date" );
                    $chart->addReportField( $report, "gender", "demographic", "gender" );
                    $chart->addReportField( $report, "results", "exam", "results" );
                    $chart->addReportField( $report, "intake_date", "training", "intake_date" );
                    $chart->addChartField( $report, "exam_year", "YEAR( exam_date )", "Year" );
                    $chart->addChartField( $report, "pass", "CASE
WHEN results = " . iHRIS_Exam::RESULT_PASS . " THEN 'Passed Exam'
WHEN results = " . iHRIS_Exam::RESULT_FAIL . " THEN 'Failed Exam' END", "Exam Results" );
                    $chart->addChartField( $report, "num", "count(*)", "Number" );
                    $chart->addChartWhere( $report, "results IN (" . iHRIS_Exam::RESULT_PASS . "," . iHRIS_Exam::RESULT_FAIL . " )" );
                    $chart->setChartInfo( $report, array(
                                              'chart_type' => "stacked column",
                                              'chart_value'=> array( 'position' => "cursor" ),
                                              'axis_category' => array( 'size' => 10, 'orientation' => "diagonal_down" ),
                                              ) );
                    $chart->addReportDisplay( $report, "sort", array( "exam_year" ) );
                    break;
                case "reg_by_year" :
                    $chart->addReportField( $report, "registration_date", "registration", "registration_date" );
                    $chart->addReportField( $report, "gender", "demographic", "gender" );
                    $chart->addReportField( $report, "intake_date", "training", "intake_date" );
                    $chart->addChartField( $report, "registration_year", "YEAR( registration_date )", "Year" );
                    $chart->addChartField( $report, "num", "count(*)", "Number" );
                    $chart->addChartWhere( $report, "registration_date IS NOT NULL" );
                    $chart->setChartInfo( $report, array(
                                              'chart_type' => "stacked column",
                                              'chart_value'=> array( 'position' => "cursor" ),
                                              'axis_category' => array( 'size' => 10, 'orientation' => "diagonal_down" ),
                                              ) );
                    $chart->addReportDisplay( $report, "sort", array( "registration_year" ) );
                    break;
                case "license" :
                    $chart->addReportField( $report, "license_date", "license", "start_date" );
                    $chart->addReportField( $report, "cadre", "cadre", "name" );
                    $chart->addReportField( $report, "gender", "demographic", "gender" );
                    $chart->addReportField( $report, "intake_date", "training", "intake_date" );
                    $chart->addChartField( $report, "cadre", "cadre", "Cadre" );
                    $chart->addChartField( $report, "num", "count(*)", "Number" );
                    $chart->addChartWhere( $report, "license_date IS NOT NULL" );
                    $chart->setChartInfo( $report, array(
                                              'chart_type' => "column",
                                              'chart_value'=> array( 'position' => "cursor" ),
                                              'axis_category' => array( 'size' => 10, 'orientation' => "diagonal_down" ),
                                              'chart_rect' => array( 'height' => 200 ),
                                              ) );
                    $chart->addReportDisplay( $report, "sort", array( "cadre" ) );                                              
                    break;
                                                        
                }
                break;
            case "reg" :
                $reg->addForm( "registration", array( "record" => "secondary" ) );
                $reg->addFormField( "registration", "registration_number", "Registration Number" );
                $reg->addFormField( "registration", "registration_date", "Registration Date" );

                $reg->addForm( "training", array( "record" => "primary" ) );
                $reg->addFormField( "training", "intake_date", "Intake Date" );
                                        
                $reg->addForm( "cadre", array( "record" => array( 'form' => "training", 'field' => "cadre" ) ) );
                $reg->addFormField( "cadre", "name", "Cadre" );
                                        
                $reg->addForm( "person", array( "record" => array( 'form' => "training", 'field' => 'parent' ) ) );
                $reg->addFormField( "person", "surname", "Surname", "view?id=" );
                $reg->addFormField( "person", "firstname", "First Name", "view?id=" );

                $reg->addForm( "demographic", array( "parent" => array( 'form' => "training", 'field' => 'parent' ) ) );
                $reg->addFormField( "demographic", "gender", "Gender" );

                $reg->addForm( "contact", array( "parent" => array( 'form' => "training", 'field' => 'parent' ), "contact_type" => iHRIS_Contact::TYPE_PERSONAL ) );
                $reg->addFormField( "contact", "address", "Contact Address" );

                $reg_limit = I2CE_ReportFactory::createLimitForm( $type, $report );
                $reg_limit->addField( "cadre", I2CE_FormField::INT, true );
                $reg_limit->getField( 'cadre' )->setMap();
                $reg_limit->addField( "range_start", I2CE_FormField::DATE_Y, true );
                $reg_limit->addField( "range_end", I2CE_FormField::DATE_Y, true );
                $reg_limit->addField( "show_text", I2CE_FormField::YESNO, false );
                $reg_limit->addField( "gender", I2CE_FormField::INT, true );
                $reg_limit->getField( 'gender' )->setMap( "demographic", "lookupGender", "listGenderOptions" );

                $reg->addLimitForms( array( "training", "cadre", "demographic" ) );
                $reg->setLimitObj( $reg_limit );
                $reg->setLimitMatch( array (
                                         "cadre" => "cadre_record",
                                         "gender" => "gender",
                                         "range_start" => array( "comparison" => ">=", "field" => "intake_date", "func" => "YEAR(intake_date)" ),
                                         "range_end" => array( "comparison" => "<=", "field" => "intake_date", "func" => "YEAR(intake_date)" ),
                                         ) );
                                        
                switch( $report ) {
                case "reg_list" :
                    $reg->addReportField( $report, "surname", "person", "surname" );
                    $reg->addReportField( $report, "firstname", "person", "firstname" );
                    $reg->addReportField( $report, "contact", "contact", "address" );
                    $reg->addReportField( $report, "cadre", "cadre", "name" );
                    $reg->addReportField( $report, "gender", "demographic", "gender" );
                    $reg->addReportField( $report, "intake_date", "training", "intake_date" );
                    $reg->addReportField( $report, "registration_date", "registration", "registration_date" );
                    $reg->addReportField( $report, "registration_number", "registration", "registration_number" );

                    $reg->addReportDisplay( $report, array( "surname", "firstname", "contact", "cadre",
                                                            "registration_date", "registration_number" ) );
                    $reg->addReportDisplay( $report, "sort", array( "surname", "firstname" ) );
                    break;                                              
                }
                break;
            case "license" :
                $license->addForm( "license", array( "record" => "secondary", 
                                                     "start_date" => array( 'comparison' => '=', 'value' => 'MAX' ) ) );
                $license->addFormField( "license", "license_number", "License Number" );
                $license->addFormField( "license", "start_date", "License Start Date" );

                $license->addForm( "training", array( "record" => "primary" ) );
                $license->addFormField( "training", "intake_date", "Intake Date" );
                                        
                $license->addForm( "cadre", array( "record" => array( 'form' => "training", 'field' => "cadre" ) ) );
                $license->addFormField( "cadre", "name", "Cadre" );
                                        
                $license->addForm( "person", array( "record" => array( 'form' => "training", 'field' => 'parent' ) ) );
                $license->addFormField( "person", "surname", "Surname", "view?id=" );
                $license->addFormField( "person", "firstname", "First Name", "view?id=" );

                $license->addForm( "demographic", array( "parent" => array( 'form' => "training", 'field' => 'parent' ) ) );
                $license->addFormField( "demographic", "gender", "Gender" );
                                        
                $license->addForm( "contact", array( "parent" => array( 'form' => "training", 'field' => 'parent' ), "contact_type" => iHRIS_Contact::TYPE_PERSONAL ) );
                $license->addFormField( "contact", "address", "Contact Address" );
                                        
                $license->addForm( "deployment", array( "parent" => array( 'form' => "training", 'field' => 'parent' ), "deployment_date" => array( 'comparison' => '=', 'value' => 'MAX' ) ) );
                $license->addFormField( "deployment", "job_title", "Position" );
                                        
                $license->addForm( "health_facility", array( "record" => array( 'form' => "deployment", 'field' => "health_facility" ) ) );
                $license->addFormField( "health_facility", "name", "Health Facility" );

                $lic_limit = I2CE_ReportFactory::createLimitForm( $type, $report );
                $lic_limit->addField( "cadre", I2CE_FormField::INT, true );
                $lic_limit->getField( 'cadre' )->setMap();
                $lic_limit->addField( "range_start", I2CE_FormField::DATE_Y, true );
                $lic_limit->addField( "range_end", I2CE_FormField::DATE_Y, true );
                $lic_limit->addField( "show_text", I2CE_FormField::YESNO, false );
                $lic_limit->addField( "gender", I2CE_FormField::INT, true );
                $lic_limit->getField( 'gender' )->setMap( "demographic", "lookupGender", "listGenderOptions" );

                $license->addLimitForms( array( "training", "cadre", "demographic", "deployment" ) );
                $license->setLimitObj( $lic_limit );
                $license->setLimitMatch( array (
                                             "cadre" => "cadre_record",
                                             "gender" => "gender",
                                             "range_start" => array( "comparison" => ">=", "field" => "intake_date", "func" => "YEAR(intake_date)" ),
                                             "range_end" => array( "comparison" => "<=", "field" => "intake_date", "func" => "YEAR(intake_date)" ),
                                             ) );
                                        
                switch( $report ) {
                case "lic_list" :
                    $license->addReportField( $report, "surname", "person", "surname" );
                    $license->addReportField( $report, "firstname", "person", "firstname" );
                    $license->addReportField( $report, "contact", "contact", "address" );
                    $license->addReportField( $report, "cadre", "cadre", "name" );
                    $license->addReportField( $report, "gender", "demographic", "gender" );
                    $license->addReportField( $report, "intake_date", "training", "intake_date" );
                    $license->addReportField( $report, "start_date", "license", "start_date" );
                    $license->addReportField( $report, "license_number", "license", "license_number" );
                    $license->addReportField( $report, "job_title", "deployment", "job_title" );
                    $license->addReportField( $report, "health_facility", "health_facility", "name" );

                    $license->addReportDisplay( $report, array( "surname", "firstname", "contact", "cadre",
                                                                "start_date", "license_number", "health_facility", "job_title" ) );
                    $license->addReportDisplay( $report, "sort", array( "surname", "firstname" ) );
                    break;                                              
                }
                break;
            case "people" :
                $people->addForm( "demographic", array( "parent" => "primary" ) );
                $people->addFormField( "demographic", "gender", "Gender" );

                $people->addForm( "district", array( "record" => array( 'form' => "demographic", 'field' => "birth_district" ) ) );
                $people->addFormField( "district", "name", "Birth District" );
                                        
                $people->addForm( "deployment", array( "parent" => "primary", "deployment_date" => array( 'comparison' => "=", 'value' => "MAX" ) ) );
                $people->addFormField( "deployment", "deployment_date", "Deployment Date" );
                                        
                $people->addForm( "health_facility", array( "record" => array( 'form' => "deployment", 'field' => "health_facility" ) ) );
                $people->addFormField( "health_facility", "name", "Health Facility" );

                $people_limit = I2CE_ReportFactory::createLimitForm( $type, $report );
                $people_limit->addField( "show_text", I2CE_FormField::YESNO, false );
                $people_limit->addField( "gender", I2CE_FormField::INT, true );
                $people_limit->getField( 'gender' )->setMap( "demographic", "lookupGender", "listGenderOptions" );

                $people->addLimitForms( array( "demographic" ) );
                $people->setLimitObj( $people_limit );
                $people->setLimitMatch( array (
                                            "gender" => "gender",
                                            ) );
                switch( $report ) {
                case "birth_district" :
                    $people->addReportField( $report, "gender", "demographic", "gender" );
                    $people->addReportField( $report, "birth_district", "district", "name" );
                    $people->addChartField( $report, "birth_district", "birth_district", "Birth District" );
                    $people->addChartField( $report, "num", "count(*)", "Number" );
                    $people->addChartWhere( $report, "district_record IS NOT NULL" );
                    $people->setChartInfo( $report, array(
                                               'chart_type' => "pie",
                                               'chart_value'=> array( 'position' => "inside" ),
                                               //'axis_category' => array( 'size' => 10, 'orientation' => "diagonal_down" ),
                                               ) );
                    $people->addReportDisplay( $report, "sort", array( "num DESC" ) );
                    break;
                case "deployment" :
                    $people->addReportField( $report, "gender", "demographic", "gender" );
                    $people->addReportField( $report, "deployment_date", "deployment", "deployment_date" );
                    $people->addReportField( $report, "deployment", "health_facility", "name" );
                    $people->addChartField( $report, "deployment", "deployment", "Health Facility" );
                    $people->addChartField( $report, "num", "count(*)", "Number" );
                    $people->addChartWhere( $report, "deployment IS NOT NULL" );
                    $people->setChartInfo( $report, array(
                                               'chart_type' => "column",
                                               'chart_value'=> array( 'position' => "cursor" ),
                                               'axis_category' => array( 'size' => 10, 'orientation' => "diagonal_down" ),
                                               'chart_rect' => array( 'height' => 200 ),
                                               ) );
                    $people->addReportDisplay( $report, "sort", array( "deployment" ) );
                    break;
                }
                break;
            case "discontinuation" :
                $discontinuation->addForm( "training_disrupt", array( "record" => "primary" ) );
                $discontinuation->addFormField( "training_disrupt", "disruption_date", "Disruption Date" );
                $discontinuation->addFormField( "training_disrupt", "resumption_date", "Resumption Date" );
                                        
                $discontinuation->addForm( "training_disruption_reason", array( "record" => 
                                                                                array( 'form' => "training_disrupt", 'field' => "disruption_reason" ) ) );
                $discontinuation->addFormField( "training_disruption_reason", "name", "Disruption Reason" );
                                        
                $discontinuation->addForm( "training", array( "record" => "secondary" ) );
                $discontinuation->addFormField( "training", "cadre", "Cadre Code" );
                $discontinuation->addFormField( "training", "intake_date", "Intake Date" );

                $discontinuation->addForm( "training_institution", array( "record" => array( 'form' => "training", 'field' => "training_institution" ) ) );
                $discontinuation->addFormField( "training_institution", "name", "Training Institution" );
                $discontinuation->addForm( "country", array( "record" => array( 'form' => "training_institution", 'field' => "country" ) ) );
                $discontinuation->addFormField( "country", "name", "School Country" );
                $discontinuation->addForm( "district", array( "record" => array( 'form' => "training_institution", 'field' => "district" ) ) );
                $discontinuation->addFormField( "district", "name", "School District" );

                $disc_limit = I2CE_ReportFactory::createLimitForm( $type, $report );
                $disc_limit->addField( "show_text", I2CE_FormField::YESNO, false );
                $disc_limit->addField( "cadre", I2CE_FormField::INT, true );
                $disc_limit->getField( 'cadre' )->setMap();
                $disc_limit->addField( "country", I2CE_FormField::INT, true );
                $disc_limit->getField( 'country' )->setMap();
                $disc_limit->addField( "district", I2CE_FormField::INT, true );
                $disc_limit->getField( 'district' )->setMap();
                $disc_limit->getField( 'district' )->setLink( $disc_limit->getField( 'country' ), "getOptionsByCountry" );
                $disc_limit->addField( "range_start", I2CE_FormField::DATE_Y, true );
                $disc_limit->addField( "range_end", I2CE_FormField::DATE_Y, true );
                $discontinuation->addLimitForms( array( "training_disrupt", "training", "training_institution", "country", "district" ) );
                $discontinuation->setLimitObj( $disc_limit );
                $discontinuation->setLimitMatch( array (
                                                     "cadre" => "cadre",
                                                     "country" => "country_record",
                                                     "district" => "district_record",
                                                     "range_start" => array( "comparison" => ">=", "field" => "intake_date", "func" => "YEAR(intake_date)" ),
                                                     "range_end" => array( "comparison" => "<=", "field" => "intake_date", "func" => "YEAR(intake_date)" ),
                                                     ) );
                                        
                switch ( $report ) {
                case "by_reason" :
                    $discontinuation->addReportField( $report, "disruption_reason", "training_disruption_reason", "name" );
                    $discontinuation->addReportField( $report, "intake_date", "training", "intake_date" );
                    $discontinuation->addReportField( $report, "cadre", "training", "cadre" );
                    $discontinuation->addChartField( $report, "disruption_reason", "disruption_reason", "Disruption Reason" );
                    $discontinuation->addChartField( $report, "num", "count(*)", "Number" );
                    $discontinuation->setChartInfo( $report, array(
                                                        'chart_type' => "column",
                                                        'chart_value'=> array( 'position' => "inside" ),
                                                        'axis_category' => array( 'size' => 10, 'orientation' => "diagonal_down" ),
                                                        'chart_rect' => array( 'height' => 200 ),
                                                        ) );
                    $discontinuation->addReportDisplay( $report, "sort", array( "disruption_reason" ) );
                    break;
                case "by_year" :
                    $discontinuation->addReportField( $report, "disruption_date", "training_disrupt", "disruption_date" );
                    $discontinuation->addReportField( $report, "resumption_date", "training_disrupt", "resumption_date" );
                    $discontinuation->addReportField( $report, "intake_date", "training", "intake_date" );
                    $discontinuation->addReportField( $report, "cadre", "training", "cadre" );
                    $discontinuation->addChartField( $report, "disruption_year", "YEAR( disruption_date )", "Disruption Year" );
                    $discontinuation->addChartField( $report, "resumed", "CASE
WHEN resumption_date IS NULL THEN 'Not Resumed'
ELSE 'Resumed' END", "Resumption?" );
                    $discontinuation->addChartField( $report, "num", "count(*)", "Number" );
                    $discontinuation->setChartInfo( $report, array(
                                                        'chart_type' => "stacked column",
                                                        'chart_value'=> array( 'position' => "inside" ),
                                                        'axis_category' => array( 'size' => 10, 'orientation' => "diagonal_down" ),
                                                        ) );
                    $discontinuation->addReportDisplay( $report, "sort", array( "disruption_year" ) );
                                                        
                    break;
                case "resumption_rate" :
                    $discontinuation->addReportField( $report, "disruption_date", "training_disrupt", "disruption_date" );
                    $discontinuation->addReportField( $report, "resumption_date", "training_disrupt", "resumption_date" );
                    $discontinuation->addReportField( $report, "intake_date", "training", "intake_date" );
                    $discontinuation->addReportField( $report, "cadre", "training", "cadre" );
                    $discontinuation->addChartField( $report, "resumed", "CASE
WHEN resumption_date IS NULL THEN 'Not Resumed'
ELSE 'Resumed' END", "Resumption?" );
                    $discontinuation->addChartField( $report, "num", "count(*)", "Number" );
                    $discontinuation->setChartInfo( $report, array(
                                                        'chart_type' => "pie",
                                                        'chart_value'=> array( 'position' => "inside" ),
                                                        //'axis_category' => array( 'size' => 10, 'orientation' => "diagonal_down" ),
                                                        ) );
                    $discontinuation->addReportDisplay( $report, "sort", array( "num DESC" ) );
                    break;
                }
                break;
            case "institution" :
                $institution->addForm( "training_institution", array( "record" => "primary" ) );
                $institution->addFormField( "training_institution", "name", "Training Institution", "view_list?type=training_institution&id=" );
                $institution->addFormField( "training_institution", "id_code", "Institution Code" );
                                        
                $institution->addForm( "contact", array( "parent" => "primary" ) );
                $institution->addFormField( "contact", "address", "Mailing Address" );
                                        
                $institution->addForm( "facility_agent", array( "record" => array( 'form' => "training_institution", 'field' => "facility_agent" ) ) );
                $institution->addFormField( "facility_agent", "name", "Facility Agent" );
                                                                                
                $inst_limit = I2CE_ReportFactory::createLimitForm( $type, $report );
                $inst_limit->addField( "facility_agent", I2CE_FormField::INT, true );
                $inst_limit->getField( 'facility_agent' )->setMap();
                $inst_limit->addField( "show_text", I2CE_FormField::YESNO, false );
                                        
                $institution->addLimitForms( array( ) );
                $institution->setLimitObj( $inst_limit );
                $institution->setLimitMatch( array( "facility_agent" => "facility_agent_record" ) );
                                        
                switch( $report ) {
                case "list" :
                    $institution->addReportField( $report, "name", "training_institution", "name" );
                    $institution->addReportField( $report, "id_code", "training_institution", "id_code" );
                    $institution->addReportField( $report, "address", "contact", "address" );
                    $institution->addReportField( $report, "facility_agent", "facility_agent", "name" );
                    $institution->addReportDisplay( $report, array( "name", "id_code", "address", 
                                                                    "facility_agent" ) );
                    $institution->addReportDisplay( $report, "sort", array( "name" ) );
                    break;
                }
                break;
            }
        }
                
        $factory = I2CE_ReportFactory::instance();
        $factory->addType( $people );
        $factory->addType( $chart );
        $factory->addType( $reg );
        $factory->addType( $license );
        $factory->addType( $discontinuation );
        $factory->addType( $institution );

    }

}


# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
