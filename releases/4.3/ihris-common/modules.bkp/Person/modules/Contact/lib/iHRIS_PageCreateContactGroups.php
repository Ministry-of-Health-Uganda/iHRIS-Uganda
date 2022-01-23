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
 * Manage adding or editing contact details to the database.
 * 
 * @package iHRIS
 * @subpackage Manage
 * @access public
 * @author Sovello Mgani <smgani@intrahealth.org>
 * @copyright Copyright &copy; 2007, 2008 IntraHealth International, Inc. 
 * @since v4.2.0
 * @version v4.2.0
 */

/**
 * Page Report Action to handle the adding or editing employees to contact groups.
 * 
 * @package iHRIS
 * @subpackage Common
 * @access public
 */
class iHRIS_PageCreateContactGroups extends I2CE_Page{

	protected $display;


	/**
	  * main actions for the page
	  *
	 */

	protected function action(){
		parent::action();
		$this->template->addHeaderLink("view.js");
		$this->template->addHeaderLink("set_contact_groups.js");
		$this->actionReport();	
		return true;
	}
	/*
     * This method is actually to read data from magicdata, but since we failed, I will hard code it here.
     */
    public function getActionHeader(){
        $config = I2CE::getConfig()->modules->CustomReports;
		return array( "Action", "Select Contact Groups");
      }

	/**
     * Load the HTML template files for editing and confirming the index and demographic information.
     */
    protected function loadHTMLTemplates() {
        parent::loadHTMLTemplates();
        $this->template->setAttribute( "class", "active", "menumHero", "a[@href='mHero']" );
        $this->template->appendFileById( "menu_mhero.html", "ul", "menumHero" );
        $this->template->setAttribute( "class", "active", "menumHero", "ul/li/a[@href='contact_groups']" );                        
            //}
    }


	protected function getContactGroups(){
		$ff = I2CE_FormFactory::instance();
		$contact_groups = array();
		$contactGroups = I2CE_FormStorage::listFields('contact_group', array('id', 'name'));
		foreach($contactGroups as $id=>$data){
			$contact_groups[$data['id']] = $data['name'];
		}
		return $contact_groups;
	}

	protected $contacts;
    public function getActionNode($field_args){
		$contact_groups = $this->getContactGroups();
		$contact_group_options = array();
		$contactGroupNode = $this->template->createElement("span");
		//$fields_args[0] for person id, $field_args[1] for contact_form id
		$person_id = explode('|', $field_args[0]);
		$actionNode = $this->template->createElement("a", "","Update");
		$actionNode->setAttribute("id",$person_id[1]);		
		if(empty($field_args[1])){
			// person doesn't have work contact
			$form_ids =  $person_id[1];
			$actionNode->setAttribute("contactgroup", ""); 
		}else{ //person has work contact
			$work_contact_id = explode('|', $field_args[1]);
			$ff = I2CE_FormFactory::instance();
			$contactObj = $ff->createContainer($field_args[1]);
			$contactObj->populate();
			$this->contacts = $contactObj->getField('contact_group')->getDBValue();
			$groups = $field_args[2];//create string
			
			$form_ids = $person_id[1]."|".$work_contact_id[1];
			$actionNode->setAttribute("contactgroup", str_replace(',','_',str_replace('contact_group|','',$this->contacts)));
			$actionNode->setAttribute("form_ids", $form_ids);
			$contactObj->cleanup();
		}
		foreach($contact_groups as $id=>$group){
			if(in_array("contact_group|$id", explode(",", $this->contacts))){
				$contact_group_options = $this->template->createElement( "input",
					array( "type" => "checkbox", "checked"=>"true", "name" => $id, "form_ids"=>$form_ids, "value"=>$id ) );
			}else{
				$contact_group_options = $this->template->createElement( "input",
					array( "type" => "checkbox", "name" => $id, "form_ids"=>$form_ids, "value"=>$id ) );
			}
			
			$js = "set_contact_group(this, ".$person_id[1].")";

			$contact_group_options->setAttribute('onchange', $js);			
			$txtNode = $this->template->createTextNode(' '.$group.' ');
			$this->template->appendNode($txtNode, $contactGroupNode);
			$this->template->appendNode($contact_group_options, $contactGroupNode);
        }		
		$actionNode->setAttribute('onclick', "updateContactGroup(this)");
        //$this->template->setDisplayData('leave_days', $field_args[4], $leave_days_field);		
        //$leave_days_field->setAttribute('onchange',$js_leave_days);
        return array($actionNode, $contactGroupNode);
    }

	public function getActionFields(){
		$config = I2CE::getConfig()->modules->CustomReports;
		return $config->getAsArray("reportViews/1445836866/default_display_options/fields");
	}


	/**
     * Create the report display and add it to the page.
     * @param string $query The query string to pass to the action for applying limits.
     * @return boolean
     */

	public function actionReport( $query='' ) {
       try {
	   I2CE::raiseError("Started action report");
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
