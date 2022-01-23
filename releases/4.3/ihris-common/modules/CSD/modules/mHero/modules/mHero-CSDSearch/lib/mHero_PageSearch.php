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
* @package ihris
* @subpackage common
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.2.0
* @since v4.2.0
* @filesource 
*/ 
/** 
* Class CSD_PageSearch
* 
* @access public
*/


class mHero_PageSearch extends I2CE_PageFormAuto {



    protected $server_host = false;
    protected $api_token = false;
    protected $slug = null;
    public $rapidpro = null;
    protected $csd_host = null;
    protected $action = false;
    protected $searchObj = null;
    protected $searchNode = null;
    protected $resultsNode = null;

    public  function __construct( $args,$request_remainder , $get = null, $post = null) {
	parent::__construct( $args,$request_remainder , $get, $post );
        foreach (array('server_host','api_token','slug','csd_host') as $key) {
            if (array_key_exists($key,$this->args)
                && is_scalar($this->args[$key])
                ){
                $this->$key = $this->args[$key];
            }
        }
        I2CE::raiseError("Examining" . print_r($request_remainder,true));
        if (count($request_remainder) > 0) {
            reset($request_remainder);
            $this->action  = array_shift($request_remainder);
        }
        if ($this->request_exists('action')) {
            $this->action = $this->request('action');
        }

	$this->rapidpro = new CSD_Interface_RapidPro($this->api_token,$this->server_host);
    }



    protected function save() {
	//does nothing.
    }

    public function isPost() {
        return parent::isPost() && $this->hasData();
    }

    public function isConfig() {
        return parent::isConfirm() && $this->hasData();
    }

    protected function hasData() {
        $path = array('form',$this->getPrimaryFormName(),'0','0','fields');
        $vals = $this->request();
        $walked = true;
        foreach ($path as $p) {
            if (!is_array($vals) || ! array_key_exists($p,$vals)) {
                $walkred  = false;
                break;
            }
            $vals = $vals[$p];
        }
        $hasValues = false;
        if ($walked && is_array($vals)) {
            foreach ($vals as $v) {
                $hasValues |= ($v !== '');
            }
        }
        return $hasValues;
    }



    protected function loadObjects() {
	parent::loadObjects();
        if ( ($this->isPost() || $this->hasData())
	    &&  ($this->searchObj = $this->getPrimary()) instanceof CSD_Search
	    && (I2CE_FormStorage::getStorageMechanism($this->searchObj->getName())) instanceof I2CE_FormStorage_CSDSearch
	    ) {
	    //this will populate "result" field
	    $this->searchObj->setID("1"); //so it will populate
            if ( ($entityIDField = $this->searchObj->getField('entityID')) instanceof I2CE_FormField_STRING_LINE) {
                list($form,$id) = array_pad(explode('|', $entityIDField->getValue(),2),2,'');
                if ($id) {
                    $entityIDField->setValue($id);
                }
                
            }
	    $this->searchObj->populate(true);
	}
	return true;
	
    }
    protected function getBaseTemplate() {
        return 'mhero_search_form.html';
    }


    protected function loadHTMLTemplates() {
        $this->template->addHeaderLink("mootools-core.js");
        $this->template->addHeaderLink("mootools-more.js");
        $this->template->addHeaderLink('I2CE_SubmitButton.js');
        $append_node = 'siteContent';
        if (array_key_exists('auto_template',$this->args)
            && is_array($this->args['auto_template']) ) {
            if ( array_key_exists('append_node',$this->args['auto_template']) 
                 && is_scalar($this->args['auto_template']['append_node']) 
                 && $this->args['auto_template']['append_node']) {
                $append_node = $this->args['auto_template']['append_node'];
            }
            $options = $this->args['auto_template'];
        } else {
            $options = array();
        }
        if (!array_key_exists('field_data',$options)
            || !is_array($opions['field_data'])) {
            $options['field_data']  = array();
        }

        if (! ($append_node_obj = $this->template->getElementById($append_node)) instanceof DOMNode 
            || ! ($node = $this->template->appendFileByNode($this->getBaseTemplate(), 'div', $append_node_obj )) instanceof DOMNode
            || ! ($this->searchNode = $this->template->getElementByID('search',$node)) instanceof DOMNode
            || ! ($this->resultsNode = $this->template->getElementByID('results',$node)) instanceof DOMNode
            ) {
            I2CE::raiseError("Could not load template:" . $this->getBaseTemplate());
            return false;
        }

        
        $s_options = $options;        
        $s_options['is_edit'] = true;
        $s_options['template']='mhero_search_form_search.html';
        $s_options['field_data']['matches'] = array('enabled'=>false);
        $s_options['field_data']['result'] =  array('enabled'=>false);
        $searchGizmo = new I2CE_Gizmo_Form($this,$this->primaryObject,$s_options);
        $searchGizmo->generate($this->searchNode);

        if (($this->isPost() || $this->hasData())) {
            $r_soptions = $options;
            $r_options['is_edit'] = false;
            $r_options['template']='mhero_search_form_results_providers.html';
            foreach ($this->primaryObject->getFieldNames() as $field) {
                if ($field != 'matches' && $field !='result') {
                    $r_options['field_data'][$field] = array('enabled'=>false);
                }
                $r_options['field_data']['result'] =  array('enabled'=>false);
            }
            $r_options['display_order'] = 'matches,result';
            $resultsGizmo = new I2CE_Gizmo_Form($this,$this->primaryObject,$r_options);
            $resultsGizmo->generate($this->resultsNode);
            if(! ($append_node_obj = $this->template->getElementById("results_summary")) instanceof DOMNode
               || ! ($this->resultsSummaryNode=$this->template->appendFileByNode("mhero_search_form_results.html", 'div', $append_node_obj )) instanceof DOMNode
              ) {
              I2CE::raiseError("Could not load template: mhero_search_form_results.html");
              return false;
            }
            if (($matches = $this->searchObj->getField('matches')) instanceof I2CE_FormField_ASSOC_MAP_RESULTS) {
					 $csd_uuids = array();            	
            	 $csd_uuids = array_keys($matches->getValue());
            	 foreach($csd_uuids as $key=>$csd_uuid) {
            	 	$csd_uuid=str_replace("csd_provider|","",$csd_uuid);
            	 	$csd_uuids[$key]=$csd_uuid;
            	 }
                $this->template->setDisplayData('num_matches',count($matches->getValue()),$this->resultsSummaryNode);
                $this->rapidpro->set_flow_options($this->template,$this->request('flow'),$this->resultsSummaryNode);
            } else {
                $this->template->setDisplayData('num_matches',0,$this->resultsSummaryNode);
            }
            $assigning_authority =  $this->server_host . '/' . $this->slug;
            $contacts = $this->rapidpro->getOtherIDs($this->csd_host,$this->csd_host_user,$this->csd_host_password,$csd_uuids,$assigning_authority);
            if (count($contacts) == 0) {
                I2CE::raiseError("No contacts found");
                $this->error("No mHero contacts were found in your selection");
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
            $this->template->setDisplayData('total_contacts',count($contact_list),$this->resultsSummaryNode);        
        } else {
            $this->template->removeNode($this->resultsSummaryNode);
        }
        return true;
    }


    public function action() {
        if ($this->action) {
            switch ($this->action) {
            case 'export_results':
                $this->export_results();
                return true;
                break;
            case 'kickoff':
                if ($this->isPost()) {
                    $this->start_workflow();
                    $this->error('Workflow started');
                    return true;
                }
                break;
            default:
                //do nothing
                break;
            }
        }        
        return parent::action();
    }


    protected function start_workflow() {
        I2CE::raiseError("Starting workflow");
        if (!($flow = $this->request('flow'))) {
            I2CE::raiseError("Bad flow");
	    $this->error("Invalid Flow");
            return false;
        }
        
        $csd_uuids = array();
        if (($matches = $this->searchObj->getField('matches')) instanceof I2CE_FormField_ASSOC_MAP_RESULTS) {
            $csd_uuids = array_keys($matches->getValue());  //key is uuid, value is forename/surname
            foreach($csd_uuids as $key=>$csd_uuid) {
            	 	$csd_uuid=str_replace("csd_provider|","",$csd_uuid);
            	 	$csd_uuids[$key]=$csd_uuid;
            	 }
        }

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
        $this->template->setDisplayData('total_contacts',count($contact_list),$this->resultsSummaryNode);        
        $this->template->setDisplayData('has_contacts',1);
        $this->rapidpro->set_flow_options($this->template,$this->request('flow'),$this->resultsSummaryNode);
        $this->rapidpro->start_workflow($flow,$contact_list);
        return true;
    }





    protected function export_results() {
        if (!($flow = $this->request('flow'))) {
            I2CE::raiseError("Bad flow");
	    $this->error("Invalid Flow");
            return false;
        }
        I2CE::longExecution();
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
        I2CE::raiseError("KEYS=" . print_r($keys,true));
        $csd_uuids = array();
        if (($matches = $this->searchObj->getField('matches')) instanceof I2CE_FormField_ASSOC_MAP_RESULTS) {
            $csd_uuids = array_keys($matches->getValue());  //key is uuid, value is forename/surname
        }
	$assigning_authority =  $this->host . '/' . $this->slug;        
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


        foreach ($results as $entityID=>$values) {
            if (!array_key_exists($entityID,$target_ids)
                || ! ( $target_id = $target_ids[$entityID])
                ) {
                continue;
            }
            if ($primary_form == 'csd_provider'
                       || $primary_form == 'csd_facility'
                       || $primary_form == 'csd_organization') {
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

            fputcsv($out,$fields);
            flush();
        }
        fclose($out);
        exit(0);
    }




}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
