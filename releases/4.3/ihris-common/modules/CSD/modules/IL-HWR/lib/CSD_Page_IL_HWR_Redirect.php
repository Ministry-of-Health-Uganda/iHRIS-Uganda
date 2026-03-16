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
* @subpackage CSD
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.2.0
* @since v4.2.0
* @filesource 
*/ 
/** 
* Class CSD_Page_RapidPro_Base
* 
* @access public
*/


class CSD_Page_IL_HWR_Redirect extends I2CE_Page {


    protected $il_hwr_host = null;

    public  function __construct( $args,$request_remainder , $get = null, $post = null) {
	parent::__construct( $args,$request_remainder , $get, $post );
	
	if (array_key_exists('il_hwr_host',$this->args)
	    && is_scalar($this->args['il_hwr_host'])
	    ){
	    $this->il_hwr_host = $this->args['il_hwr_host'];
	}
	if ($this->request_exists('id')) {
	    $id = $this->request('id');
            I2CE::raiseError("Recevied ($id)");
	    if  ( ($this->person = I2CE_FormFactory::instance()->createContainer($id)) instanceof I2CE_Form) {
                $this->person->populate();
            }
	}
        $this->setForm($this->person);
    }

    public function action() {
        if (!$this->person instanceof iHRIS_Person
	    || !  ($csd_uuid_field = $this->person->getField('csd_uuid')) instanceof I2CE_FormField
	    || ! ($csd_uuid = $csd_uuid_field->getValue())
	    ) {
	    $this->error("Invalid Person");
	    return false;
	}
        I2CE::raiseError("Setting form");
        $this->setForm($this->person);
        $this->template->setDisplayDataImmediate('id',$this->request('id'));
        $this->template->setDisplayDataImmediate('view_url','view?id=' . $this->request('id'));
        $urn = 'urn:uuid:' . $csd_uuid;
        
        $url = $this->il_hwr_host . '/view_csd_provider?id=csd_provider|' . $urn;
        I2CE::raiseError("Redirecting to $url");
        $this->redirect( $url );
        exit();
    }


}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
