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
* @package ihris-common
* @subpackage csd
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.2.0
* @since v4.2.0
* @filesource 
*/ 
/** 
* Class I2CE_CustomReport_Display_mHero
* 
* @access public
*/


class I2CE_CustomReport_Display_mHero extends I2CE_CustomReport_Display_Default{
    public function get_csd_uuids() {
        I2CE::longExecution();
	$this->unsetPaging();
	if (!is_array(	$data = $this->getResults())
	    || !array_key_exists('results',$data)
	    || ! $results = $data['results']
	    ){
	    I2CE::raiseError("No data");
	    return array();
	}
	$target_ids = array();
        $id = 'primary_form+id';
        
        $uuid = false;
        $rel = $this->reportObj->getFormRelationship();
        $primary_form = $rel->getPrimaryForm();
        if ($this->formObjs[$primary_form] instanceof CSD_Provider
            || $this->formObjs[$primary_form] instanceof CSD_Facility
            || $this->formObjs[$primary_form] instanceof CSD_Organization) {
            $uuid = 'primary_form+id';
        } else if ($this->formObjs[$primary_form] instanceof iHRIS_Person) {
            $uuid = 'primary_form+csd_uuid';
        }
        if ($uuid) {
            try {
                while ($row = $results->fetch()) { 
                    I2CE::longExecution();
                    if (! is_scalar($pers_id = $row->$id)
                            || !$pers_id
                            || !is_scalar($csd_uuid = $row->$uuid)
                            || ! $csd_uuid
                       ) {
                        continue;
                    }
                    $target_ids[$pers_id] = 'urn:uuid:'. $csd_uuid;
                }
            } catch ( PDOException $e ) {
            }
        }
        $results->free();
        return $target_ids;
    }


    public function get_display_fields() {
        I2CE::longExecution();
	$this->unsetPaging();
	if (!is_array(	$data = $this->getResults())
	    || !array_key_exists('results',$data)
	    || ! $results = $data['results']
	    ){
	    I2CE::raiseError("No data");
	    return array();
	}


        $display_fields = array();
        
        $titles = $this->get_display_field_titles();
        while (is_array($row = $results->fetch(PDO::FETCH_ASSOC))) {
            I2CE::longExecution();
            if (  ! array_key_exists('primary_form+id',$row)
                  || ! ($target_id = $row['primary_form+id'])
                ){
                I2CE::raiseError("Bad:" . print_r($row,true));
                continue;
            }
            $data = array();
            foreach ($titles as $formfield => $label) { ///this is just to ensure the same order as the display name of the fields
                if (!array_key_exists($formfield,$row))  {
                    continue;
                }
                $data[$formfield] = $row[$formfield];
            }
            $display_fields[$target_id] = $data;
        }
        $results->free();
        return $display_fields;

    }


    public function get_display_field_titles() {
        $titles=array();
        I2CE::raiseError("dfd" . print_r($this->getDisplayFieldsData(),true));
        foreach ($this->getDisplayFieldsData() as $field=>$d) {
            if (!is_array($d)
                || !array_key_exists('header',$d)
                ) {
                continue;
            } 
            $titles[$field] = $d['header'];
        }
        return $titles;
    }





    protected function getDefaultOptions($get,$options = array() ) {
	$options = parent::getDefaultOptions($get,$options);
	$defaults = array(
	    'limit_paginated'=>1,
	    'limit_per_page'=>50,
	    'nested_limits'=>array()
	    );
	foreach ($defaults as $k=>$v) {
	    if (!array_key_exists($k,$options) || !$options[$k]) {
		$options[$k] = $v;
	    }
	}
	return $options;

    }

    protected function displayReportControl($contentNode) {
        parent::displayReportControl($contentNode);
        if ($this->page instanceof CSD_Page_RapidPro_Base            
            && $this->page->rapidpro instanceof CSD_Interface_RapidPro
            ) {
            $this->page->rapidpro->set_flow_options($this->page->getTemplate(),$this->page->request('flow'),'siteContent');
        }
        return true;
    }



    protected function processWhere($limitValues, $report = null) {        
        $rel = $this->reportObj->getFormRelationship();
        $primary_form = $rel->getPrimaryForm();
        $where = trim(parent::processWhere($limitValues,$report));
        if ($this->formObjs[$primary_form] instanceof iHRIS_Person) {
            list($formObj,$fieldObj)= $this->getFormFieldObjects('primary_form+csd_uuid',$report);
            if ($formObj instanceof I2CE_Form) {
                $uuid_where =  trim($formObj->generateLimit(array('field'=>'csd_uuid','style'=>'not_null','data'=>array())));
                if ($where) {
                    $uuid_where = "( ( " . $uuid_where . ") AND (" . $where . "))";
                }
                return $uuid_where;
            } else {
                I2CE::raiseError("Cannot get csd_uuid where clause");
                return $where;
            }
        } else if (! $this->formObjs[$primary_form] instanceof CSD_Provider
                   && ! $this->formObjs[$primary_form] instanceof CSD_Facility
                   && ! $this->formObjs[$primary_form] instanceof CSD_Organization) {
            I2CE::raiseError("Invalid CSD form");
        }
        
    }


    public function getDisplayFieldsData() {
	$data = parent::getDisplayFieldsData();
	$rel = $this->reportObj->getFormRelationship();
        $primary_form = $rel->getPrimaryForm();
        if ($this->formObjs[$primary_form] instanceof iHRIS_Person) {
            if (!array_key_exists('primary_form+id',$data)) {
                $data['primary_form+id'] = 
                    array('header'=>'Person ID','link'=>false, 'target'=>false, 'link_append'=>false, 'link_type'=>false);
            }	
            if (!array_key_exists('primary_form+csd_uuid',$data)) {
                $data['primary_form+csd_uuid'] = 
                    array('header'=>'CSD EnitityID','link'=>false, 'target'=>false, 'link_append'=>false, 'link_type'=>false);
            }

        } else if ($this->formObjs[$primary_form] instanceof CSD_Provider
                   ||$this->formObjs[$primary_form] instanceof CSD_Facility
                   || $this->formObjs[$primary_form] instanceof CSD_Organization
            ) {
            if (!array_key_exists('primary_form+id',$data)) {
                $data['primary_form+csd_uuid'] = 
                    array('header'=>'CSD EnitityID','link'=>false, 'target'=>false, 'link_append'=>false, 'link_type'=>false);
            }

        }
	return $data;
    }


    protected function getBasePage() {
        $module = $this->page->module();
        if ($module == 'I2CE') {
            $base_page = $this->page->page();
        } else {
            $base_page = $module .'/' . $this->page->page();
        }
        return $base_page . '/select';
    }


}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
