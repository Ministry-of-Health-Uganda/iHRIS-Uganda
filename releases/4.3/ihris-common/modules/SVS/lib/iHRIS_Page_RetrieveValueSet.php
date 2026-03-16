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
* @subpackage svs
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.2.0
* @since v4.2.0
* @filesource 
*/ 
/** 
* Class iHRIS_Page_RetrieveValueSet
* 
* @access public
*/


class iHRIS_Page_RetrieveValueSet extends I2CE_Page{

    //  https://example.com/RetrieveValueSet?id=1.2.840.10008.6.1.308&version=20061023&lang=en-US


    protected function action_config() {
	$init_options = array(
            'root_path'=>'/modules/SVS',
            'root_path_create'=>true,                    
	    'root_url'=>'SVS/config',
            'root_type'=>'SVS_Menu');
        try {
            $swiss_factory = new I2CE_SwissMagicFactory($this,$init_options);
        } catch (Exception $e) {
            I2CE::raiseError("Could not create swissmagic for selectable" . $e->getMessage());
            return false;
        }
        try {
            $swiss_factory->setRootSwiss();
        } catch (Exception $e) {
            I2CE::raiseError("Could not create root swissmagic for selectable" . $e->getMessage());
            return false;
        }
        $swiss_path = $this->request_remainder;
        array_shift($swiss_path); 
        $action = array_shift($swiss_path); 
        if ($action == 'update' && $this->isPost()) {
            if ($swiss_factory->updateValues($this->post())) {
		$this->userMessage ( "Updated SVS");
                I2CE::raiseError("good update");
	    } else {
                I2CE::raiseError("bad update");                
		$this->userMessage ( "Unable To Update SVS");
	    }
        }
	$action = 'edit';
        return $swiss_factory->displayValues( $this->template->getElementById('siteContent'),$swiss_path, $action);
    }

    protected function action() {
        if ( !parent::action() ) {
	    return false;
        }
	$action = 'RetrieveValueSet';
	if (count($this->request_remainder) > 0) {
	    reset($this->request_remainder);
	    $action = current($this->request_remainder);
	}
	$task = 'SVS_allow_' . $action;
	if (I2CE_PermissionParser::taskExists($task) 
	    && !$this->hasPermission('task(' . $task . ')')) {
	    I2CE::raiseError("No permission");
	    return false;
	}
        if ($action =='config') {
            return $this->action_config();
        } else if ($this->isGet()) {
	    return $this->action_get($action);
	} else {
            return $this->action_post();
        }

    }

    
    protected function action_post() {
        $content = file_get_contents("php://input");
        if (!$content
            || ! ($req_doc = new DOMDocument("1.0", "UTF-8"))
            || ! $req_doc->loadXML($content)
            || ! ($xpath = new DOMXPath($req_doc)) 
            || ! ($xpath->registerNamespace('se','http://www.w3.org/2003/05/soap-envelope'))
            || ! ($xpath->registerNamespace('sa','http://www.w3.org/2005/08/addressing'))
            || ! ($xpath->registerNamespace('svs','urn:ihe:iti:svs:2008'))
            ) {
            I2CE::raiseError("Invalid request");
            return false;
        }
        if (is_array( $params = $this->loadDocParameters($xpath,self::$retrieve_single))
            && $params['id']) {
            //request for a single 
            if ( is_string($svs = iHRIS_SVS::getPublishedConceptList($params['id'],$params['version'],$params['lang']))
                 || !($svs_doc = new DOMDocument('1.0','UTF-8')) instanceof DOMDocument
                 || ! ($svs_doc->loadXML($svs))
                 || ! ($resp_doc = new DOMDocument('1.0','UTF-8')) instanceof DOMDocument
                 || ! ($resp_doc->loadXML(self::$response_single))
                 || ! ($resp_xpath = new DOMXPath($resp_doc)) instanceof DOMXPath		
                 || ! ($resp_xpath->registerNamespace('se','http://www.w3.org/2003/05/soap-envelope'))
                 || ! ($resp_xpath->registerNamespace('sa','http://www.w3.org/2005/08/addressing'))
                 || ! ($resp_xpath->registerNamespace('svs','urn:ihe:iti:svs:2008'))
                 || ! ($relatesList = $resp_xpath->query("/se:Envelope/se:Header/sa:RelatesTo")) instanceof DOMNodeList
                 || ! ($relatesList->length == 1)
                 || ! ($relates = $relatesList->item(0)) instanceof DOMElement
                 || ! ($respList = $resp_xpath->query("/se:Envelope/se:Body/svs:RetrieveValueSetResponse")) instanceof DOMNodeList
                 || ! ($respList->length == 1)
                 || ! ($resp = $respList->item(0)) instanceof DOMElement
                ) {
                I2CE::raiseError("Couldn't generate response");
            return false;
            }
            if ( ($errors  = I2CE_Dumper::cleanlyEndOutputBuffers())) {
                I2CE::raiseError("Got errors:\n$errors");
            }
            header('Content-Type: application/soap');
            $resp->appendChild($resp_doc->importNode($svs_doc->documentElement,true));
            $relates->appendChild($resp_doc->createTextNode($params['msgid']));        
            echo $resp_doc->saveXML();
            exit(0);
        } else {
            //fallback to multiple value set
            $params = $this->loadDocParameters($xpath,self::$retrieve_multi);
            if (! ( $svs_doc = $this->generateMultiDoc($params)) instanceof DOMDocument
                || ! ($resp_doc = new DOMDocument('1.0','UTF-8')) instanceof DOMDocument
                || ! ($resp_doc->loadXML(self::$response_multi))
                || ! ($resp_xpath = new DOMXPath($resp_doc)) instanceof DOMXPath		
                || ! ($resp_xpath->registerNamespace('se','http://www.w3.org/2003/05/soap-envelope'))
                || ! ($resp_xpath->registerNamespace('sa','http://www.w3.org/2005/08/addressing'))
                || ! ($resp_xpath->registerNamespace('svs','urn:ihe:iti:svs:2008'))
                || ! ($relatesList = $resp_xpath->query("/se:Envelope/se:Header/sa:RelatesTo")) instanceof DOMNodeList
                || ! ($relatesList->length == 1)
                || ! ($relates = $relatesList->item(0)) instanceof DOMElement
                || ! ($respList = $resp_xpath->query("/se:Envelope/se:Body/svs:RetrieveMultipleValueSetsResponse")) instanceof DOMNodeList
                || ! ($respList->length == 1)
                || ! ($resp = $respList->item(0)) instanceof DOMElement
                ) {
                I2CE::raiseError("Couldn't generate response");
                return false;
            }
            if ( ($errors  = I2CE_Dumper::cleanlyEndOutputBuffers())) {
                I2CE::raiseError("Got errors:\n$errors");
            }
            header('Content-Type: application/soap');
            $resp->appendChild($resp_doc->importNode($svs_doc->documentElement,true));
            $relates->appendChild($resp_doc->createTextNode($params['msgid']));        
            echo $resp_doc->saveXML();
            exit(0);            
        }
    }

    protected function generateMultiDoc($params) {
        $doc = new DOMDocument('1.0','UTF-8');
        if (! $doc->loadXML('<svs:RetrieveMultipleValueSetsResponse xmlns:svs="urn:ihe:iti:svs:2008"/>')) {
            return false;
        }
        $ids = iHRIS_SVS::getOIDs();
        if ($params['ID']) {
            if (in_array($params['ID'],$ids)) {
                $ids = array($params['ID']);
            } else {
                $ids = array();
            }
        }
        foreach ($ids as $id) {
            try {
                $svsObj = new iHRIS_SVS($id);
            } catch (Exception $e) {
                I2CE::raiseError("Could not access $id");
                continue;
            }
            $meta = $svsObj->getMeta();
            $versions = iHRIS_SVS::getVersions($id);
            foreach ($versions as $version) {
                $langs = iHRIS_SVS::getLangs($id,$version);
                foreach ($langs as $lang) {
                    if (! is_string($svs = iHRIS_SVS::getPublishedConceptList($id, $version ,$lang))
                        || ! ($svs_doc = new DOMDocument('1.0','UTF-8')) 
                        || ! ($svs_doc->loadXML($svs))
                        ){
                        I2CE::raiseError("Could not retrieve $id ($version) in $lang");
                        continue;
                    }
                    $doc->documentElement->appendChild($dvs = $doc->createElement('svs:DescribedValueSet'));
                    $dvs->setAttribute('version',$version);
                    $dvs->setAttribute('ID',$id);
                    $dvs->setAttribute('displayName',$id);
                    $dvs->appendChild($doc->importNode($svs_doc->documentElement,true));
                    $dvs->appendChild($source = $doc->createElement('svs:Source'));
                    $source->appendChild($doc->createTextNode($meta['Source']));
                    $dvs->appendChild($status = $doc->createElement('svs:Status'));
                    $status->appendChild($doc->createTextNode($meta['Status']));
                }

            }

        }
        


        return $doc;
    }



    protected static $retrieve_single = array(
        'id'=>array('default'=>false,'query'=>'//svs:RetrieveValueSetRequest/svs:ValueSet/@id'),
        'version'=>array('default'=>false,'query'=>'//svs:RetrieveValueSetRequest/svs:ValueSet/@version'),
        'lang'=>array('default'=>'en-US','query'=>'//svs:RetrieveValueSetRequest/svs:ValueSet/@xml:lang'),
        'msgid'=>array('default'=>false,'query'=>'/se:Envelope/se:Header/sa:MessageID')

        );
    
    protected static $response_single = '
    <se:Envelope xmlns:sa="http://www.w3.org/2005/08/addressing"
		xmlns:se="http://www.w3.org/2003/05/soap-envelope">
      <se:Header>
        <sa:Action se:mustUnderstand="1">urn:ihe:iti:2008:RetrieveValueSetResponse</sa:Action>
        <sa:RelatesTo/>
      </se:Header>
      <se:Body>
	  <svs:RetrieveValueSetResponse xmlns:svs="urn:ihe:iti:svs:2008" />
      </se:Body>
    </se:Envelope>
';


    protected static $retrieve_multi = array(
        'msgid'=>array('default'=>false,'query'=>'/se:Envelope/se:Header/sa:MessageID'),
        'ID'=>array('query'=>'//svs:RetrieveMultipleValueSetsRequest/@id','default'=>false),
        'DisplayNameContains'=>array('query'=>'//svs:RetrieveMultipleValueSetsRequest/@DisplayNameContains','default'=>false),
        'SourceContains'=>array('query'=>'//svs:RetrieveMultipleValueSetsRequest/@SourceContains','default'=>false),
        'PurposeContains'=>array('query'=>'//svs:RetrieveMultipleValueSetsRequest/@PurposeContains','default'=>false),
        'DefinitionContains'=>array('query'=>'//svs:RetrieveMultipleValueSetsRequest/@DefinitionContains','default'=>false),
        'GroupContains'=>array('query'=>'//svs:RetrieveMultipleValueSetsRequest/@GroupContains','default'=>false),
        'GroupOID'=>array('query'=>'//svs:RetrieveMultipleValueSetsRequest/@GroupOID','default'=>false),
        'EffectiveDateBefore'=>array('query'=>'//svs:RetrieveMultipleValueSetsRequest/@EffectiveDateBefore','default'=>false),
        'EffectiveDateAfter'=>array('query'=>'//svs:RetrieveMultipleValueSetsRequest/@EffectiveDateAfter','default'=>false),
        'ExpirationDateBefore'=>array('query'=>'//svs:RetrieveMultipleValueSetsRequest/@ExpirationDateBefore','default'=>false), 
        'ExpirationDateAfter'=>array('query'=>'//svs:RetrieveMultipleValueSetsRequest/@ExpirationDateAfter','default'=>false),
        'CreationDateBefore'=>array('query'=>'//svs:RetrieveMultipleValueSetsRequest/@CreationDateBefore','default'=>false),
        'CreationDateAfter'=>array('query'=>'//svs:RetrieveMultipleValueSetsRequest/@CreationDateAfter','default'=>false),
        'RevisionDateBefore'=>array('query'=>'//svs:RetrieveMultipleValueSetsRequest/@RevisionDateBefore','default'=>false),
        'RevisionDateAfter'=>array('query'=>'//svs:RetrieveMultipleValueSetsRequest/@RevisionDateAfter','default'=>false)
        );
    
    protected static $response_multi = '
    <se:Envelope xmlns:sa="http://www.w3.org/2005/08/addressing"
		xmlns:se="http://www.w3.org/2003/05/soap-envelope">
      <se:Header>
        <sa:Action se:mustUnderstand="1">urn:ihe:iti:2008:RetrieveValueSetResponse</sa:Action>
        <sa:RelatesTo/>
      </se:Header>
      <se:Body/>
    </se:Envelope>
';


    protected function loadRequestParameters($retrieve) {
        $params = array();
        foreach ($retrieve as $key=>$data) {
            $params[$key] = $data['default'];
            if ($this->request_exists($key)) {
                $params[$key] = $this->request($key);
            }
        }
        return $params;

    }
    
    protected function loadDocParameters($xpath,$retrieve) {
        $params = array();
        foreach ($retrieve as $key=>$data) {
            $params[$key] = $data['default'];
            if (! ($results = $xpath->query($data['query'])) instanceof DOMNodeList
                || ! ($results->length == 1)
                ) {
                continue;
            }
            $result = $results->item(0);
            if ($result instanceof DOMElement) {
                $params[$key] = $result->textContent;
            } else if ($result instanceof DOMElement) {
                $params[$key] = $result->value;
            }
        }
        return $params;
        
    }


    protected function action_get($action) {

	switch ($action) {
	case 'publish':	    
            $params = $this->loadRequestParameters(self::$retrieve_single);
	    try {
		$svs = new iHRIS_SVS($params['id']);
	    } catch (Exception $e) {
		I2CE::raiseError("Couldn't create SVS for {$params['id']}");
		return false;
	    }
	    if ( !$svs->publishConceptList($params['version'],$params['lang'])) {
		I2CE::raiseError("Couldn't publish SVS for {$params['id']}");
                $this->userMessage ( "Could not publish Shared Value Set " . $params['id']);
	    } else {
                $this->userMessage ( "Published Shared Value Set " . $params['id']);
            }
            $this->redirect($this->page . '/config') ;
	    return true;
	case 'RetrieveMultipleValueSets':
            $params = $this->loadRequestParameters(self::$retrieve_multi);        
            if (! ($svs_doc = $this->generateMultiDoc($params)) instanceof DOMDocument) {
                I2CE::raiseError("Could not retrieve multiples");
                return false;
            }
            if ( ($errors  = I2CE_Dumper::cleanlyEndOutputBuffers())) {
                I2CE::raiseError("Got errors:\n$errors");
            }
	    header('Content-Type: text/xml');
	    echo $svs_doc->saveXML();
	    exit(0);
	default:
	case 'RetrieveValueSet':
            $params = $this->loadRequestParameters(self::$retrieve_single);        
	    if (! is_string($svs = iHRIS_SVS::getPublishedConceptList($params['id'],$params['version'],$params['lang']))) {
		I2CE::raiseError("Couldn't retrieve SVS for {$params['id']} on version {$params['version']} language {$params['lang']}");
		return false;
	    }
            if ( ($errors  = I2CE_Dumper::cleanlyEndOutputBuffers())) {
                I2CE::raiseError("Got errors:\n$errors");
            }
	    header('Content-Type: text/xml');
            header('Content-Disposition: attachment; filename="'.$params['id']. '.xml"');
	    echo $svs;
	    exit(0);
	}
	
	
    }

    

}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
