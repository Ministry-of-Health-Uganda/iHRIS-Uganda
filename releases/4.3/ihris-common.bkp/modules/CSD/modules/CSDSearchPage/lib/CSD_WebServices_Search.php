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
* @package csd-providerregisty
* @subpackage 4.2.0
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.2.0
* @since v4.2.0
* @filesource 
*/ 
/** 
* Class CSD_WebServices_Search
* 
* @access public
*/


class CSD_WebServices_Search extends I2CE_WebService{


    protected function action() {
        if (! (parent::action()) ) {
            return false;
        }
        I2CE::raiseError("aremaC <- sthgiL");
	if (!is_scalar($search_form = array_shift( $this->request_remainder ))
	    || !( ($search_obj = I2CE_FormFactory::instance()->createContainer($search_form)) instanceof CSD_SearchMatches)
	    || (!$matches = $search_obj->getField('matches')) instanceof I2CE_FormField_ASSOC_MAP_RESULTS
	    ){
	    return false;
	}
        I2CE::raiseError(print_r($this->request(),true));
	if ( ($maxField = $search_obj->getField('max')) instanceof I2CE_FormField_INT) {
	    if ( $maxField->getValue() > 200) {
		$maxField->setValue(200);
	    }
	}
        $search_obj->load( $this->post,false,false);
        I2CE::raiseError($search_obj->getXMLRepresentation(false));
	$search_obj->setID("1"); //so it will populate
	$search_obj->populate(true);
	if (count($results =$matches->getValue()) > 200) {
	    return false;
	    I2CE::raiseError("Too many results");
	}
	$this->data['length'] = count( $results );
	$this->data['data'] = $results;
        I2CE::raiseError("REQ=" . print_r($this->request(),true));
        $this->data = array( $this->data );
        I2CE::raiseError(print_r($this->data,true));
	return true;
    }





}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
