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
* Class CSD_Page_StartWorkflow
* 
* @access public
*/


class CSD_Page_mHeroBulk extends CSD_Page_RapidPro_Base implements I2CE_ShowReport_Interface{

    //Expected URL Pattern:  $action/($view)



    
    protected $relationship = false;
    protected $relationship_obj = null;
    protected $action = false;
    protected $view = false;
    protected $flow = false;
    protected $display = false;
    public  function __construct( $args,$request_remainder , $get = null, $post = null) {
	parent::__construct( $args,$request_remainder , $get, $post );
        $request = $this->request_remainder();
        $this->view = $this->request('view');
        $this->flow = $this->request('flow');
        I2CE::raiseError("Examining" . print_r($request,true));
        if (count($request_remainder) > 0) {
            reset($request_remainder);
            $this->action  = array_shift($request_remainder);
            if (count($request_remainder) > 0) {
                reset($request_remainder);
                $this->view = array_shift($request_remainder);
            }       
        }        
        if ($this->request_exists('action')) {
            $this->action = $this->request('action');
        }
    }



    protected function error($msg) {
	if (($node = $this->template->getElementById('error')) instanceof DOMNode) {
	    $node->appendChild($p = $this->template->createElement('p'));
            $p->appendChild($this->template->createTextNode($msg));
	}
    }


    /**
     * Load the HTML template files for editing and confirming the index and demographic information.
     */
    protected function loadHTMLTemplates() {
        parent::loadHTMLTemplates();
        $this->template->setAttribute( "class", "active", "menumHero", "a[@href='mHero']" );
        $this->template->appendFileById( "menu_mhero.html", "ul", "menumHero" );
        $this->template->setAttribute( "class", "active", "menumHero", "ul/li/a[@href='mHero']" );                        
            //}
    }

    public function action() {
        if ($this->action) {
            switch ($this->action) {
            case 'export_results':
                $this->display_menu();
                $this->export_results();
                return true;
                break;
            case 'kickoff':
                if ($this->isPost()) {
                    $this->display_menu();
                    $this->start_workflow();
                    $this->error('Workflow started');
                    return true;
                }
                break;
            case 'redirect':
                if ($this->view) {
                    $this->setRedirect('mHero/select/' . $this->view);
                    return true;
                }
                break;
            case 'select':
                return $this->select_people();
            default:
                //do nothing
                break;
            }
        }        
        return $this->display_menu();
    }

    protected function get_valid_views() {
        $views = array();
        $reports = array();
        $relationships = array();

        $has_person = I2CE_ModuleFactory::instance()->isEnabled('Person-CSD');
        $has_provider = I2CE_ModuleFactory::instance()->isEnabled('csd-provider-data-model');


        foreach (I2CE::getConfig()->traverse('/modules/CustomReports/relationships') as $relationship=>$rel_config) {
            $primary_form = false;
            if (!$rel_config instanceof I2CE_MagicDataNode
                || ! ($rel_config->setIfIsSet($primary_form,'form'))
                || ( ! ($has_person && $primary_form != 'person' ) 
                     && !  ($has_provider &&  ( $primary_form != 'csd_provider'
                                                || $primary_form != 'csd_facility'
                                                ||  $primary_form != 'csd_organization'
                                )
                         )
                    )
                ){
                continue;
            }
            $relationships[] = $relationship;
        }
        foreach (I2CE::getConfig()->traverse('/modules/CustomReports/reports') as $report=>$rep_config) {
            $relationship = false;
            if (!$rep_config instanceof I2CE_MagicDataNode
                || ! ($rep_config->setIfIsSet($relationship,'relationship'))
                || ! in_array($relationship,$relationships)
                || ! is_array($form_data = $rep_config->getAsArray("reporting_forms/primary_form"))
                || ! array_key_exists('fields',$form_data)
                || ! is_array( $fields = $form_data['fields'])
                || ! array_key_exists('csd_uuid',$fields)
                || ! is_array($fields['csd_uuid'])
                || (array_key_exists('enabled',$fields['csd_uuid']) && ! $fields['csd_uuid']['enabled']) 
                ){
                continue;
            }
            $reports[] = $report;
        }
        foreach (I2CE::getConfig()->traverse('/modules/CustomReports/reportViews') as $view=>$view_config) {
            $report = false;
            $view_name = false;
            if (!$view_config instanceof I2CE_MagicDataNode
                || ! ($view_config->setIfIsSet($report,'report'))
                || ! in_array($report,$reports)
                || ! ($view_config->setIfIsSet($view_name,'display_name'))
                || ! $view_name
                ){
                continue;
            }
            $views[$view] = $view_name;
        }
        return $views;
    }

    protected function display_menu() {

        if (! ($content_node = $this->template->getElementById('siteContent')) instanceof DOMNode
            || ! ($menu_node = $this->template->appendFileByNode('mHero_bulk_menu.html','div',$content_node)) instanceof DOMNode
            || ! ($view_node = $this->template->getElementByName('view',0,$menu_node)) instanceof DOMNode
            ){
            return false;
        }
        $this->setDisplayData('view',$this->view,$menu_node);
        $views = $this->get_valid_views();
        foreach ($views as $view=>$view_name) {
            $view_node->appendChild($this->template->createElement('option',array('value'=>$view),$view_name));
        }
        $this->show_detailed_results();
        return true;
    }


    protected function select_people() {
        $this->instantiateDisplay('Default',$this->view);
        if (! ($content_node = $this->template->getElementById('siteContent')) instanceof DOMNode
            || !$this->display_obj instanceof I2CE_CustomReport_Display_mHero
            ) {
            I2CE::raiseError("Invalid Display");
	    $this->error("Invalid Display");
            return false;            
        }
        $this->template->setDisplayData('view',$this->view,$content_node);
        //we are good to go at this point.
        $this->template->addHeaderLink("CustomReports.css");
        $this->template->addHeaderLink("customReports_display_Search.css");
        $this->template->addHeaderLink("CustomReports_iehacks.css", array('ie6' => true ));
        $this->template->setDisplayData( "limit_description", false );
        return $this->display_obj->display($content_node);
    }


    protected function start_workflow() {
        I2CE::raiseError("Starting workflow");
        $this->instantiateDisplay('Default',$this->view);
        if (!($flow = $this->request('flow'))) {
            I2CE::raiseError("Bad flow");
	    $this->error("Invalid Flow");
            return false;
        }
        if (!$this->display_obj instanceof I2CE_CustomReport_Display_mHero) {
            I2CE::raiseError("Bad display");
	    $this->error("Invalid Display");
            return false;            
        }
        $csd_uuids = $this->display_obj->get_csd_uuids();        
	$assigning_authority =  $this->server_host . '/' . $this->slug;
        $contacts = $this->rapidpro->getOtherIDs($this->csd_host,$this->csd_host_user,$this->csd_host_password,$csd_uuids,$assigning_authority);
        if (count($contacts) == 0) {
            I2CE::raiseError("No contacts found");
            $this->error("No mHero contacts were found in your selection");
            return false;
        }
        $contact_list = array();
        foreach ($contacts as $contact) {
            if (!is_array($contact)
                || !array_key_exists('otherID',$contact)
                || ! is_array($contact['otherID'])
                || count($contact['otherID']) != 1
                || ! reset($contact['otherID'])
                || ! is_array($otherID = current($contact['otherID']))
                || ! array_key_exists('value',$otherID)
                || ! ($rapidpro_id =  $otherID['value'])
                ) {
                continue;
            }
            $contact_list[] = $rapidpro_id;
        }
        I2CE::raiseError("Starting $flow on " . implode(" " , $contact_list));
        $this->template->setDisplayData('total_contacts',count($contact_list));        
        $this->rapidpro->set_flow_options($this->template,$this->request('flow'),$results_node);
        $this->rapidpro->start_workflow($flow,$contact_list);
        return true;
    }


    
    protected $display_fields = false;
    protected function get_display_fields($target_id){
        if (!$this->display_obj instanceof I2CE_CustomReport_Display_mHero) {            
            return array();
        }
        if (!is_array($this->display_fields)) {
            $this->display_fields = $this->display_obj->get_display_fields();
            I2CE::raiseError("DF=" . print_r($this->display_fields,true));
        }
        if (!array_key_exists($target_id,$this->display_fields)
            || ! is_array($df = $this->display_fields[$target_id])
            ) {
            return array();
        }
        return $df;
    }
    protected function get_display_field_titles() {
        if (!$this->display_obj instanceof I2CE_CustomReport_Display_mHero) {            
            return array();
        }
        return $this->display_obj->get_display_field_titles();
    }

    protected function export_results() {
        if (!($flow = $this->request('flow'))) {
            I2CE::raiseError("Bad flow");
	    $this->error("Invalid Flow");
            return false;
        }
        $this->instantiateDisplay('Default',$this->view);
        if (!$this->display_obj instanceof I2CE_CustomReport_Display_mHero
            || ! ($relationship = $this->display_obj->getRelationshipName())
            || ! ( I2CE::getConfig()->setIfIsSet($primary_form ,' /modules/CustomReports/relationships/'.$relationship . '/form') )
            ){
            I2CE::raiseError("Bad display");
	    $this->error("Invalid Display");
            return false;       
     
        }
        I2CE::longExecution();
        $titles = $this->get_display_field_titles();
        I2CE::raiseError("TITLES=" . print_r($titles,true));
        $labels = $this->rapidpro->get_flow_field_labels($flow);
        $headers = array();
        if (array_key_exists( 'HTTP_USER_AGENT', $_SERVER ) && preg_match('/\s+MSIE\s+\d\.\d;/',$_SERVER['HTTP_USER_AGENT'])) {
            $headers[] = "application/vnd.ms-excel";
        } else{
            $headers[] =  "text/csv; charset=UTF-8";
        } 
        $filename = 'mHero-' . '-' .  $this->slug . '-export-' . date('Y') . '-' . date('m') . '-' . date('d') . '.csv';
        $headers[] = "Content-disposition: attachment; filename=\"$filename\"";
        $out = fopen("php://output", 'w');
        $keys = array('entityID'=>'Health Worker Registry Entity ID','iHRIS_link'=>'iHRIS Source Record');
        $keys = array_merge($keys, $labels); 
        $keys = array_merge($keys,$titles);
        I2CE::raiseError("KEYS=" . print_r($keys,true));
        $csd_uuids = $this->display_obj->get_csd_uuids();        
        $target_ids = array_flip($csd_uuids);        
	$assigning_authority =  $this->server_host . '/' . $this->slug;        
        $contacts = $this->rapidpro->getOtherIDs($this->csd_host,$this->csd_host_user,$this->csd_host_password,$csd_uuids,$assigning_authority);
        $contact_list = array();
        foreach ($contacts as $entityID=>$contact) {
            if (!is_array($contact)
                || !array_key_exists('otherID',$contact)
                || ! is_array($contact['otherID'])
                || count($contact['otherID']) != 1
                || ! reset($contact['otherID'])
                || ! is_array($otherID = current($contact['otherID']))
                || ! array_key_exists('value',$otherID)
                || ! ($rapidpro_id =  $otherID['value'])
                ) {
                continue;
            }
            $contact_list[$rapidpro_id] = $entityID;
        }
        I2CE::raiseError(print_r($contacts,true));
        I2CE::raiseError(print_r($contact_list,true));
        $runs = $this->rapidpro->getFlowValues($flow); //would be better if we could put the streaming function as a callback after each hit on rapidpro
        $results = array();
        foreach ($runs as $run) {
            I2CE::raiseMessage($run['contact']);
            if (!is_array($run)
                || !array_key_exists('contact',$run)
                || ! ($rapidpro_id = $run['contact'] )
                || ! array_key_exists($rapidpro_id,$contact_list)
                || ! ($entityID = $contact_list[$rapidpro_id])
                || ! array_key_exists('values',$run)
                || ! is_array( $run['values'])
                ){
                continue;
            }
            $values = array();
            foreach ($run['values'] as $val_set) {
                if (!is_array($val_set)
                    ||!array_key_exists('label',$val_set)
                    || !($label = $val_set['label'])
                    ||!array_key_exists('text',$val_set)
                    ) {
                    continue;
                }
                $values[$label] = $val_set['text'];
            }
            $results[$entityID] =  $values;
        }
        I2CE::raiseError(print_r($results,true));
        if ( ($errors  = I2CE_Dumper::cleanlyEndOutputBuffers())) {
            I2CE::raiseError("Got errors:\n$errors");
        }
        I2CE::longExecution();
        foreach ($headers as $header) {
            header($header);
        }
        fputcsv($out,$keys);


        $has_person = I2CE_ModuleFactory::instance()->isEnabled('Person-CSD');
        $has_provider = I2CE_ModuleFactory::instance()->isEnabled('csd-provider-data-model');


        foreach ($results as $entityID=>$values) {
            if (!array_key_exists($entityID,$target_ids)
                || ! ( $target_id = $target_ids[$entityID])
                ) {
                continue;
            }
            $iHRIS_link = false;
            if ($has_person && $primary_form == 'person') {
                $iHRIS_link = I2CE::getAccessedBaseURL() . 'view?id=' . $target_id; 
            } else if ($has_provider && ($primary_form == 'csd_provider'
                                         || $primary_form == 'csd_facility'
                                         || $primary_form == 'csd_organization')
                ){
                $iHRIS_link = I2CE::getAccessedBaseURL() . 'view_' . $primary_form . '/view?id=' . $target_id; 
            }
            $fields = array('entityID'=>$entityID,'iHRIS_link'=>$iHRIS_link) ;
            foreach ($labels as $label) {
                if (!array_key_exists($label,$values)) {
                    $fields[] = '';
                } else {
                    $fields[] = $values[$label];
                }
            }

            $fields = array_merge($fields,$this->get_display_fields($target_id));
            foreach ($titles as $title=>$label) {
                if (!array_key_exists($title,$fields)
                    || !is_scalar($val =$fields[$title])
                    ){
                    $fields[$title] = '';
                } else {
                    $fields[$title] = $val;
                }
            }
            fputcsv($out,$fields);
            flush();
        }
        fclose($out);
        exit(0);
    }



    protected function show_workflow_results() {
        I2CE::raiseError("Show workflow");
        $this->instantiateDisplay('Default',$this->view);
        if (!($flow = $this->request('flow'))) {
            I2CE::raiseError("Bad flow");
	    $this->error("Invalid Flow");
            return false;
        }
        if (!$this->display_obj instanceof I2CE_CustomReport_Display_mHero) {
            I2CE::raiseError("Bad display");
	    $this->error("Invalid Display");
            return false;            
        }
        $csd_uuids = $this->display_obj->get_csd_uuids();        
	$assigning_authority =  $this->server_host . '/' . $this->slug;
        $contacts = $this->rapidpro->getOtherIDs($this->csd_host,$this->csd_host_user,$this->csd_host_password,$csd_uuids,$assigning_authority);
        if (count($contacts) == 0) {
            I2CE::raiseError("No contacts found");
            $this->error("No mHero contacts were found in your selection");
            return false;
        }
        $contact_list = array();
        foreach ($contacts as $contact) {
            if (!is_array($contact)
                || !array_key_exists('otherID',$contact)
                || ! is_array($contact['otherID'])
                || count($contact['otherID']) != 1
                || ! reset($contact['otherID'])
                || ! is_array($otherID = current($contact['otherID']))
                || ! array_key_exists('value',$otherID)
                || ! ($rapidpro_id =  $otherID['value'])
                ) {
                continue;
            }
            $contact_list[] = $rapidpro_id;
        }
        I2CE::raiseError("Starting $flow on " . implode(" " , $contact_list));
        if (($results_node = $this->template->getElementByID('kickoff_results')) instanceof DOMNode) {
             $this->template->appendFileByNode('mHero_bulk_results.html','div',$results_node);
        }
        $this->template->setDisplayData('total_contacts',count($contact_list));        
        $this->rapidpro->set_flow_data($this->template,$this->request('flow'),$results_node);

        return true;
    }


    protected function show_detailed_results() {
        $flow = $this->request('flow');
        if ( $flow
             && ($results_node = $this->template->getElementByID('kickoff_results')) instanceof DOMNode
            ) {
            $this->template->appendFileByNode('mHero_bulk_results.html','div',$results_node);
        }
        $this->rapidpro->set_flow_options($this->template,$flow);        


        if  (! $flow
             ||! ($content_node = $this->template->getElementById('detailed_results')) instanceof DOMNode
             || ! ($menu_node = $this->template->appendFileByNode('mHero_detailed_results.html','div',$content_node)) instanceof DOMNode
             || ! ($ul_node = $this->template->getElementById('detailed_results_list'))
            ){
            return true;
        }
        $details = $this->rapidpro->get_detailed_results_on_flow($flow);

        foreach ($details as $detail) {
            if (!is_array($detail)
                || ! array_key_exists('label',$detail)
                || ! array_key_exists('result',$detail)
                || ! ($label = $detail['label'])
                || ! is_array($result = $detail['result'])
                || ! array_key_exists('categories',$result)
                || ! is_array($cats = $result['categories'])
                ) {
                continue;
            }
            $ul_node->appendChild($li_node = $this->template->createElement('li',array(),$label));
            $li_node->appendChild($ul_sub_node= $this->template->createElement('ul'));
            foreach ($cats as $cat) {
                if (!is_array($cat) 
                    || !array_key_exists('label',$cat)
                    || !array_key_exists('count',$cat)
                    ) {
                    continue;
                }
                $msg = $cat['label'] . ": "  . $cat['count']  . ' Responses';
                $ul_sub_node->appendChild($this->template->createElement('li',array(),$msg));
            }
        }
        return true;
    }

    /*****************************************
     *
     *  I2CE_ShowReport_Interface
     *
     *****************************************/


    /*
     *Determine all the allowed for the indicated report view
     * @param string $view
     *@returns array of string. 
     */
    public function getAllowedDisplays($view) {
        return array('Default');
    }



    protected $display_obj =false;
    /**
     *Try to instantiate display object
     * @param string $display
     * @param string $view
     * @returns mixed.  false on failure I2CE_CustomReport_Display on succcess
     */
    public function instantiateDisplay($display,$view) {
        if (!$this->view ) {
            I2CE::raiseErorr("No view");
            return false;
        }
        if (!$this->display_obj instanceof I2CE_CustomReport_Display_mHero) {
            try {
                $this->display_obj = new I2CE_CustomReport_Display_mHero($this,$this->view);                
            }
            catch (Exception $e) {
                $msg = $e->getMessage();
                I2CE::raiseError($msg);
                $this->userMessage($msg);
                $this->display_obj = false;
            }
            if (!$this->display_obj instanceof I2CE_CustomReport_Display) {
                $this->display_obj = false;
            }
        }
        return $this->display_obj;
    }
}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
