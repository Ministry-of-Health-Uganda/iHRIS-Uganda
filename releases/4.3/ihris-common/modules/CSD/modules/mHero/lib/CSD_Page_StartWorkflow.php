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


class CSD_Page_StartWorkflow extends CSD_Page_RapidPro_Base {

    
    protected $target = null;
    public  function __construct( $args,$request_remainder , $get = null, $post = null) {
	parent::__construct( $args,$request_remainder , $get, $post );
	if ($this->request_exists('id')) {
	    $id = $this->request('id');
            I2CE::raiseError("Recevied ($id)");
	    if  ( ($this->target = I2CE_FormFactory::instance()->createContainer($id)) instanceof I2CE_Form) {
                $this->target->populate();
            }
	}
        
    }
    protected function error($msg) {
	if (($node = $this->template->getElementById('error')) instanceof DOMNode) {
	    $node->appendChild($p = $this->template->createElement('p'));
            $p->appendChild($this->template->createTextNode($msg));
	}
    }



    public function action() {
        if ($this->isPost()) {
            $this->start_workflow();
            $this->error('Workflow started');
        }
        return $this->display_menu();
    }

    protected $flows  = false;
    protected function getFlows($flow = null) {
        if (! is_array($this->flows)) {
            $this->flows = $this->rapidpro->getFlows();
        }
        if (is_scalar($flow)) {
            foreach ($this->flows as $flow_details) {
                if (!array_key_exists('flow',$flow_details)
                    || $flow_details['flow'] != $flow
                    ) {
                    continue;
                }
                return $flow_details;
            }
            return array();
        } else {
            return $this->flows;
        }
    }

    protected function display_menu() {
        $flow = $this->request('flow');

        if ( ($s_node   = $this->template->getElementByID('start_flow')) instanceof DOMNode) {
            $this->rapidpro->set_flow_options($this->template,false,$s_node);
        }
        if ( ($v_node   = $this->template->getElementByID('view_results')) instanceof DOMNode) {
            $this->rapidpro->set_flow_options($this->template,$this->request('flow'),$v_node);
        }


        $has_person = I2CE_ModuleFactory::instance()->isEnabled('Person-CSD');
        $has_provider = I2CE_ModuleFactory::instance()->isEnabled('csd-provider-data-model');
     
        
	if ($has_person && $this->target instanceof iHRIS_Person)  {
            if (! ($csd_uuid_field = $this->target->getField('csd_uuid')) instanceof I2CE_FormField
                || ! ($csd_uuid = $csd_uuid_field->getValue())
                ) {
                $this->error("Invalid Person");
                $this->template->setDisplayDataImmediate('has_flow', 0 );
                return false;
            }
            $csd_uuid = 'urn:uuid:' . $csd_uuid;
	}  else if ($has_provider && ($this->target instanceof CSD_Provider
                                      || $this->target instanceof CSD_Facility
                                      || $this->target instanceof CSD_Organization
                        )
            ) {
            $csd_uuid = $this->target->getID();
        } else {
            return false;
        }

        I2CE::raiseError("Setting form");
        $this->setForm($this->target);
        $this->template->setDisplayDataImmediate('id',$this->request('id'));
        $this->template->setDisplayDataImmediate('view_url','view?id=' . $this->request('id'));

	$assigning_authority =  $this->server_host . '/' . $this->slug;
	if (!($contact = $this->rapidpro->lookupOtherID($this->csd_host,$this->csd_host_user,$this->csd_host_password, $csd_uuid,$assigning_authority))) {
	    $this->error("No contact found for rapidpro in hwr with entity ID $csd_uuid");
            $this->template->setDisplayDataImmediate('has_flow', 0);
	    return false;
	}
        $this->template->setDisplayDataImmediate('has_flow', $flow != 0 );
        I2CE::raiseError("Setting contact URL");
        $this->template->setDisplayDataImmediate('contact_url', $this->server_host .'/contact/read/' . $contact);
        if ($this->get('show_results')
            && ($results_node = $this->template->getElementByID('results')) instanceof DOMNode) {
            
            $this->display_results($flow,$contact,$results_node);
        }
        $this->template->setDisplayDataImmediate('show_results',$this->get('show_results') == 1);
        $this->template->addHeaderLink('mootools-core.js');
        $this->template->addHeaderLink('mootools-more.js');
        $this->template->addHeaderLink('stub.css');
        if ( ($js_node = $this->template->getElementByID('mHero_show_results')) instanceof DOMNode
             && ($flow = $this->request('flow'))
            ) {
            $url = 'index.php/'  . $this->page() . '?flow=' . $flow . '&id=' .$this->target->getFormID() . '&show_results=1';
            $onrequest = 'function() { rn.addClass("stub-ajax-loading");  }';
            $onsuccess = 'function() { rn.empty(); rn.removeClass("stub-ajax-loading");  var r = this.response.elements.filter("#results");  rn.adopt(r);}';
            $js = '                
window.addEvent("domready",  function() {
   var rn = $("results_ajax"); 
   if (rn) {
      var request = new Request.HTML({url:"' . $url . '",method:"get",onSuccess:'. $onsuccess . ',onRequest:'. $onrequest .  '});
      request.send();
   }                             
});
';
            $js_node->appendChild($this->template->createTextNode($js));
        }
        return true;
    }

    protected function display_results($flow,$contact,$results_node) {
        if ( ! ($results_value_node = $this->template->getElementByID('results_value',$results_node)) instanceof DOMNode
             || ! ($results_step_node = $this->template->getElementByID('results_step',$results_node)) instanceof DOMNode
            ) {
            return ;
        }
        $flow_url = $this->server_host . '/flow/editor/' . $flow .'/';  
        $this->template->setDisplayDataImmediate('flow_editor',$flow_url,$results_node);
        
        $flow_details = $this->getFlows($flow);
        $all_results =  $this->rapidpro->getFlowValues($flow,$contact);        

        $labels = array();
        if (array_key_exists('rulesets',$flow_details)
            && is_array($rulesets =$flow_details['rulesets'])
            ) {
            foreach  ($rulesets as $ruleset) {
                if (!is_array($ruleset)
                    || !array_key_exists('node',$ruleset)
                    || !array_key_exists('label',$ruleset)
                    ) {
                    continue;
                }
                $labels[$ruleset['node']] = $ruleset['label'];
            }
        }
        if (array_key_exists('name',$flow_details)) {
            $this->setDisplayDataImmediate('flow_name',$flow_details['name']);
        }


        foreach (array_slice($all_results,0,1) as $results) {
            if (array_key_exists('phone',$results)) {
                $this->setDisplayDataImmediate('phone',$results['phone'],$results_node);
            }

            if (! array_key_exists('values',$results)
                || !is_array($values = $results['values'])
                ) {
                continue;
            }


            foreach ($values as $value) {
                if (!array_key_exists('label',$value)) {
                    continue;
                }
                $results_value_node->appendChild($tr_node = $this->template->createElement('tr'));
                $keys = array('label','time','value','rule_value','text');
                foreach ($keys as $key) {
                    $tr_node->appendChild($td_node = $this->template->createElement('td'));
                    if (array_key_exists($key,$value)) {
                        $td_node->appendChild($this->template->createTextNode($value[$key]));
                        $td_node->appendChild($this->template->createTextNode(' '));
                    }
                }
            }                           
        }
        foreach ($all_results as $results) {
            if (! array_key_exists('steps',$results)
                || !is_array($steps = $results['steps'])
                ) {
                continue;
            }
            $results_step_node->appendChild($tr_node = $this->template->createElement('tr'));
            $tr_node->appendChild($td_node = $this->template->createElement('td',array('colspan'=>5,'style'=>'background:#E6E6FA')));
            $td_node->appendChild($center_node = $this->template->createElement('center'));
            $center_node->appendChild($this->template->createTextNode('New Run'));
            $keys = array('label','value','text','arrived_on','left_on');
            foreach ($steps as $step) {
                if (!array_key_exists('node',$step)) {
                    continue;
                }
                $step['label'] = '';
                if (array_key_exists($step['node'],$labels)) {
                    $step['label'] = $labels[$step['node']];
                }
                $results_step_node->appendChild($tr_node = $this->template->createElement('tr',array('name'=>$step['node'])));
                foreach ($keys as $key) {
                    $tr_node->appendChild($td_node = $this->template->createElement('td'));
                    if (array_key_exists($key,$step)) {
                        $td_node->appendChild($this->template->createTextNode($step[$key]));
                    }
                }
            }
        }
    }



    protected function start_workflow() {
        $has_person = I2CE_ModuleFactory::instance()->isEnabled('Person-CSD');
        $has_provider = I2CE_ModuleFactory::instance()->isEnabled('csd-provider-data-model');

	if ($has_person && $this->target instanceof iHRIS_Person) {
            if (! ($csd_uuid_field = $this->target->getField('csd_uuid')) instanceof I2CE_FormField
                || ! ($csd_uuid = $csd_uuid_field->getValue())
                ) {
                $this->error("Invalid Person");
                return false;
            }
            $csd_uuid = 'urn:uuid:' . $csd_uuid;
        } else if ($has_provider && ($this->target instanceof CSD_Provider
                                     || $this->target instanceof CSD_Facility
                                     ||$this->target instanceof CSD_Organization
                       )
            ){
            $csd_uuid = $this->target->getID();
        }
	$assigning_authority =  $this->server_host . '/' . $this->slug;
	if (!($contact = $this->rapidpro->lookupOtherID($this->csd_host,$this->csd_host_user,$this->csd_host_password, $csd_uuid,$assigning_authority))) {
	    $this->error("No contact found for rapidpro in hwr with entity ID $csd_uuid");
	    return false;
	} 
        if (!($flow = $this->request('flow'))) {
	    $this->error("No flow specified");
            return false;
        } 
        $this->rapidpro->start_workflow($flow,$contact);
    }

}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
