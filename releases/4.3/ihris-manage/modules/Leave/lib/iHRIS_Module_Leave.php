<?php
/**
* © Copyright 2008 IntraHealth International, Inc.
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
*/
/**
*  iHRIS_Module_Leave
* @package I2CE
* @subpackage Core
* @author Ally Shaban <allyshaban5@gmail.com>
* @copyright Copyright &copy; 2008 IntraHealth International, Inc.
* This file is part of I2CE. I2CE is free software; you can redistribute it and/or modify it under
* the terms of the GNU General Public License as published by the Free Software Foundation; either
* version 3 of the License, or (at your option) any later version. I2CE is distributed in the hope
* that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
* or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details. You should have
* received a copy of the GNU General Public License along with this program. If not, see <http://www.gnu.org/licenses/>.
* @version 2.1
* @access public
*/


class iHRIS_Module_Leave extends I2CE_Module {

    public static function getMethods() {
        return array(
            'iHRIS_PageView->action_leave_request' => 'action_leave_request',
            'iHRIS_PageView->action_leave_balance' => 'action_leave_balance'
            );
    }

    /**
     * Return the array of hooks available in this module.
     * @return array
     */
    public static function getHooks() {
        return array(
                'validate_form_leave_category' => 'validate_form_leave_category',
                'validate_form_leave_request' => 'validate_form_leave_request',
                );
    }



    public function action_leave_request($obj) {
      if (!$obj instanceof iHRIS_PageView) {
          I2CE::raiseError("invalid call");
          return false;;
      }

      //displaying leave balance
      $this->ff = I2CE_FormFactory::instance();
      $cat = I2CE_FormStorage::search("leave_category");
      if(count($cat > 0)) {
        $template = $obj->getTemplate();
    		$person_id=$obj->getPerson()->getNameId();
        $persObj=$this->ff->createContainer($person_id);
        $leave_balance_node = $template->appendFileById("view_leave_balance.html", "div", "leave_balance");
        foreach($cat as $index=>$leave_cat) {
          $row_node = $template->appendFileByName( "view_leave_balance_rows.html", "tr", "leave_balance_rows", 0, $leave_balance_node);
          $leaveCatObj = $this->ff->createContainer('leave_category|'.$leave_cat);
          $leaveCatObj->populate();
          $leaveCatName = $leaveCatObj->getField("name")->getDisplayValue();
          $maxDays = $leaveCatObj->getField("days")->getDisplayValue();
          $start_period = $leaveCatObj->getField("start_date")->getDBValue();
          $end_period = $leaveCatObj->getField("end_date")->getDBValue();
          $leave_cycle = $leaveCatObj->getField("leave_cycle")->getDBValue();
          /*
          calculating leave balance
          if leave cycle id defined then ignore start and end period which is based on date
          **/
          $balance = 0;
          $days_on_leave = 0;
          if($leave_cycle) {
            $levCycleObj = $this->ff->createContainer($leave_cycle);
            $levCycleObj->populate();
            $leaveCycle = $levCycleObj->getField("years")->getDBValue();
            $leave_period = "Last ".$leaveCatObj->getField("leave_cycle")->getDisplayValue();

            //get all rapproved requested leave
            $years = date("Y") - $leaveCycle;
            $check_date = $years."-".date("m-d");
            $where = array( "operator"=>"AND",
                            "operand"=>array(0=>array("operator"=>"FIELD_LIMIT",
                                                      "field"=>"leave_status",
                                                      "style"=>"equals",
                                                      "data" => array("value" => "leave_status|1")),
                                             1=>array("operator"=>"FIELD_LIMIT",
                                                      "field"=>"start_date",
                                                      "style"=>"greaterthan",
                                                      "data" => array("value" => $check_date)),
                                             2=>array("operator"=>"FIELD_LIMIT",
                                                      "field"=>"leave_category",
                                                      "style"=>"equals",
                                                      "data" => array("value" => 'leave_category|'.$leave_cat)))
                          );
            $leav_req_arr = I2CE_FormStorage::search("leave_request",false,$where);
            if(count($leav_req_arr) == 0) {
              $balance = $maxDays;
            }
            else {
              $balance = $maxDays;
              foreach($leav_req_arr as $leave_req_id) {
                $leavReqObj = $this->ff->createContainer("leave_request|".$leave_req_id);
                $leavReqObj->populate();
                $start_date = strtotime($leavReqObj->getField("start_date")->getDBValue());
                $end_date = strtotime($leavReqObj->getField("end_date")->getDBValue());
                $days = floor(($end_date-$start_date)/(60*60*24)) + 1;
                $balance = $balance - $days;
                $days_on_leave = $days_on_leave + $days;
              }
            }
          }
          if(!$leave_cycle) {
            $start_period = explode("-",$start_period);
            $start_period_month = $start_period[1];
            $end_period = explode("-",$end_period);
            $end_period_month = $end_period[1];
            if($start_period_month > $end_period_month) {
              $current_year = date("Y");
              $current_month = date("m");
              if($current_month >= $start_period_month) {
                $leave_period = $current_year."-".$start_period_month."/" . ++$current_year . "-" . $end_period_month;
              }
              else if($current_month < $start_period_month) {
                $leave_period = --$current_year."-".$start_period_month."/" . $current_year . "-" . $end_period_month;
              }
            }
            else if($start_period_month <= $end_period_month) {
              $current_year = date("Y");
              $current_month = date("m");
              if($current_month >= $start_period_month) {
                $leave_period = $current_year."-".$start_period_month."/" . $current_year . "-" . $end_period_month;
              }
              else if($current_month < $start_period_month) {
                $leave_period = --$current_year."-".$start_period_month."/" . --$current_year . "-" . $end_period_month;
              }
            }
            //calculating leave balance
            $check_date = explode("/",$leave_period);
            $check_date_start = $check_date[0] . "-" . "01";
            $check_date_end = $check_date[1] . "-" . "31";
            $where = array( "operator"=>"AND",
                            "operand"=>array(0=>array("operator"=>"FIELD_LIMIT",
                                                      "field"=>"leave_status",
                                                      "style"=>"equals",
                                                      "data" => array("value" => "leave_status|1")),
                                             1=>array("operator"=>"FIELD_LIMIT",
                                                      "field"=>"start_date",
                                                      "style"=>"greaterthan",
                                                      "data" => array("value" => $check_date_start)),
                                             2=>array("operator"=>"FIELD_LIMIT",
                                                      "field"=>"end_date",
                                                      "style"=>"lessthan",
                                                      "data" => array("value" => $check_date_end)),
                                             3=>array("operator"=>"FIELD_LIMIT",
                                                      "field"=>"leave_category",
                                                      "style"=>"equals",
                                                      "data" => array("value" => 'leave_category|'.$leave_cat)))
                          );
            $leav_req_arr = I2CE_FormStorage::search("leave_request",false,$where);
            if(count($leav_req_arr) == 0) {
              $balance = $maxDays;
            }
            else {
              $balance = $maxDays;
              foreach($leav_req_arr as $leave_req_id) {
                $leavReqObj = $this->ff->createContainer("leave_request|".$leave_req_id);
                $leavReqObj->populate();
                $start_date = strtotime($leavReqObj->getField("start_date")->getDBValue());
                $end_date = strtotime($leavReqObj->getField("end_date")->getDBValue());
                $days = floor(($end_date-$start_date)/(60*60*24)) + 1;
                $balance = $balance - $days;
                $days_on_leave = $days_on_leave + $days;
              }
            }
          }
          $template->setDisplayDataImmediate( "leave_balance_counter", ++$index, $row_node );
          $template->setDisplayDataImmediate( "leave_balance_period", $leave_period, $row_node );
          $template->setDisplayDataImmediate( "leave_balance_category", $leaveCatName, $row_node );
          $template->setDisplayDataImmediate( "leave_balance_max_days", $maxDays, $row_node );
          $template->setDisplayDataImmediate( "leave_balance_leave_days", $days_on_leave, $row_node );
          $template->setDisplayDataImmediate( "leave_balance_balance", $balance, $row_node );
        }
      }




      return $obj->addChildForms('leave_request', 'siteContent');
    }

    public function action_leave_balance($obj) {
      if (!$obj instanceof iHRIS_PageView) {
        I2CE::raiseError("invalid call");
        return false;;
      }
      $template = $page->getTemplate();
  		$person_id=$page->getPerson()->getNameId();
      $persObj=$this->ff->createContainer($person_id);
      $leave_balance = $this->template->appendFileById("view_leave_balance.html", "div", "leave_balance");
      $this->template->setDisplayDataImmediate( "leave_balance_counter", 1, $leave_balance );
    }

    public function validate_form_leave_category( $form ) {
        //if leave cycle is not selected,then make sure dates are valid and selected
        if($form->leave_cycle[0] != "leave_cycle") {
          if ( $form->start_date->isValid() && $form->end_date->isValid() ) {
              if ( $form->start_date->compare( $form->end_date ) == 0 ) {
                  $form->setInvalidMessage('end_date','Start date should not be equal to end date');
              }
          }
        }
    }

    public function validate_form_leave_request( $form ) {
        if ( $form->start_date->isValid() && $form->end_date->isValid() && $form->return_date->isValid()) {
            if ( $form->start_date->compare( $form->end_date ) == 0 ) {
                $form->setInvalidMessage('end_date','Start date should not be equal to end date');
            }
            else if ( $form->start_date->compare( $form->end_date ) < 0 ) {
                $form->setInvalidMessage('end_date','End date should not come before start date');
            }
            else if ( $form->end_date->compare( $form->return_date ) == 0 ) {
                $form->setInvalidMessage('return_date','Return date should not be equal to end date');
            }
            else if ( $form->end_date->compare( $form->return_date ) < 0 ) {
                $form->setInvalidMessage('return_date','Return date should not come before end date');
            }
        }
        else
        $form->setInvalidMessage('end_date','Invalid Dates');
    }
}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
