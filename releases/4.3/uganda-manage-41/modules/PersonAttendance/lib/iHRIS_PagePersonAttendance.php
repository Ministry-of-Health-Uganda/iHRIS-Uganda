<?php
/**
* © Copyright 2014 IntraHealth International, Inc.
*
* This File is part of I2CE
*
* I2CE is free software; you can redistribute it and/or modify
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
* @package I2CE
* @author Sovello Hildebrand <sovellohpmgani@gmail.com>
* @version v4.2.0
* @since v4.2.0
* @filesource
*/
/**
* Class I2CE_Dashboard
*
* @access public
*/


class iHRIS_PagePersonAttendance extends  I2CE_Page {

    protected $display;
    /**
     * main actions for the page
     *
     */
    protected function action() {
      parent::action();
      $this->template->addHeaderLink("view.js");
      $this->template->addHeaderLink("person_attendance.js");
      $this->actionReport();
      return true;
    }

    /**
     * Load the HTML template files for editing and confirming the index and demographic information.
     */
    protected function loadHTMLTemplates() {
        parent::loadHTMLTemplates();
        $this->template->setAttribute( "class", "active", "menuAttendance", "a[@href='editpersonattendance']" );
        $this->template->appendFileById( "menu_attendance.html", "ul", "menuAttendance" );
        $this->template->setAttribute( "class", "active", "menuAttendance", "ul/li/a[@href='editpersonattendance']" );                        
            //}
    }
    
        /*
     * This method is actually to read data from magicdata, but since we failed, I will hard code it here.
     */
    public function getActionHeader(){
        $config = I2CE::getConfig()->modules->CustomReports;
		return array("days_present", "days_absent","days_or","days_ph","days_od","days_leave");
      }

    public function getActionNode($field_args){
        //$fields = $this->getActionFields();
	//Days Present
        $days_present_field = $this->template->createElement( "input",
                                                       array( "type" => "text", "name" => "days_present", "id" => "days_present" ) );
        $js_days_present = "isNumeric(this, '".$field_args[0]."', '".$field_args[1]."', '".$field_args[2]."');";
        $this->template->setDisplayData('days_present', $field_args[3], $days_present_field);		
        $days_present_field->setAttribute('onchange',$js_days_present);
        //Days absent
	$days_absent_field = $this->template->createElement( "input",
                                                       array( "type" => "text", "name" => "days_absent", "id" => "days_absent" ) );
        $js_days_absent = "isNumeric(this, '".$field_args[0]."', '".$field_args[1]."', '".$field_args[2]."');";
        $this->template->setDisplayData('days_absent', $field_args[3], $days_absent_field);		
        $days_absent_field->setAttribute('onchange',$js_days_absent);
		
	//Official Request
	$days_or_field = $this->template->createElement( "input",
                                                       array( "type" => "text", "name" => "days_or", "id" => "days_or" ) );
        $js_days_or = "isNumeric(this, '".$field_args[0]."', '".$field_args[1]."', '".$field_args[2]."');";
        $this->template->setDisplayData('days_or', $field_args[3], $days_or_field);		
        $days_or_field->setAttribute('onchange',$js_days_or);
	
	//Public Holiday Request
	$days_ph_field = $this->template->createElement( "input",
                                                       array( "type" => "text", "name" => "days_ph", "id" => "days_ph" ) );
        $js_days_ph = "isNumeric(this, '".$field_args[0]."', '".$field_args[1]."', '".$field_args[2]."');";
        $this->template->setDisplayData('days_ph', $field_args[3], $days_ph_field);		
        $days_ph_field->setAttribute('onchange',$js_days_ph);

	//Off Duty Request
	$days_od_field = $this->template->createElement( "input",
                                                       array( "type" => "text", "name" => "days_od", "id" => "days_od" ) );
        $js_days_od = "isNumeric(this, '".$field_args[0]."', '".$field_args[1]."', '".$field_args[2]."');";
        $this->template->setDisplayData('days_od', $field_args[3], $days_od_field);		
        $days_od_field->setAttribute('onchange',$js_days_od);
	
	//Leave
	$days_leave_field = $this->template->createElement( "input",
                                                       array( "type" => "text", "name" => "days_leave", "id" => "days_leave" ) );
        $js_days_leave = "isNumeric(this, '".$field_args[0]."', '".$field_args[1]."', '".$field_args[2]."');";
        $this->template->setDisplayData('days_leave', $field_args[3], $days_leave_field);		
        $days_leave_field->setAttribute('onchange',$js_days_leave);
		
        return array($days_present_field, $days_absent_field,$days_or_field,$days_ph_field,$days_od_field,$days_leave_field);
      }

      public function getActionFields(){
        $config = I2CE::getConfig()->modules->CustomReports;
       //  $f = $config->getAsArray("reportViews/1442998916/default_display_options/fields");
       // I2CE::raiseError("display fields" .$f[0].$f[1].$f[2]);
      //  return $f;
        return $config->getAsArray("reportViews/1442998916/default_display_options/fields");
      }

    /**
     * Create the report display and add it to the page.
     * @param string $query The query string to pass to the action for applying limits.
     * @return boolean
     */
    public function actionReport( $query='' ) {
        try {
            $this->display = new I2CE_CustomReport_Display_DefaultAction( $this, $this->args['report_view'] );
        } catch (Exception $e) {
            I2CE::raiseError("Could not get for " . $this->args['report_view'] . "\n" . $e);
            return false;
        }

        $this->template->addHeaderLink("CustomReports.css");
        $this->template->addHeaderLink("CustomReports_iehacks.css", array('ie6' => true));
        $this->template->setDisplayData( "limit_description", false );

        $contentNode = $this->template->getElementById("siteContent");
        if ( !$contentNode instanceof DOMNode || !$this->display->display( $contentNode ) ) {
            I2CE::raiseError( "Couldn't display report.  Either no content node or an error occurred displaying the report." );
            return false;
        }

        $reportLimitsNode = $this->template->getElementById('report_limits');
        if ( !$reportLimitsNode instanceof DOMNode ) {
            I2CE::raiseError("Unable to find report_limits node.");
        } else {
            $applyNode = $this->template->appendFileByNode(
                    "customReports_display_limit_apply_Default.html", "tr",
                    $reportLimitsNode );
            $form = $this->template->query( ".//*[@id='limit_form']", $contentNode );
            if ( $form->length == 1 ) {
                $form = $form->item(0)->setAttribute('action', $this->page() . "?$query");
            }
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
