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
* Class iHRIS_Page_CSDCacheAdmin
* 
* @access public
*/


class iHRIS_Page_CSDCacheAdmin extends I2CE_Page{


    public function actionCommandLine($args,$request_remainder) {
        if (! $this->_display()) {
            exit(1);
        } else {
            exit(0);
        }
    }

    public function action() {
        if ( !parent::action()) {
	    return false;
	} 
	$csd_cache = false;
	$action = 'menu';
	$ids = false;
        if ($this->get_exists('action') 
            && ($action = $this->get('action')  )) {
            $action = $this->get('action');
            if ($action == 'full_stream') {
                $this->action_full_stream();
                exit(0);
            } else if ($action == 'full_soap') {
                $this->action_full_soap();
                exit(0);            
            } else if ($action == 'full_update') {
                return $this->action_full_update();
            } else if ($action == 'full_clear') {
                $this->action_full_clear();
                return $this->action_menu();
            }
        }else  if (count($this->request_remainder) > 0) {
            reset($this->request_remainder);
            $csd_cache = current($this->request_remainder);
            $action = 'menu';
            if (count ($this->request_remainder)  > 1) {
                next($this->request_remainder);
                $action = current($this->request_remainder);
            }
            if (count($this->request_remainder) > 2) {
                next($this->request_remainder);
                $ids = array(current($this->request_remainder));
            }
            reset($this->request_remainder);
            switch ($action) {
            case 'xsl':
                $this->action_xsl($csd_cache);
                return $this->action_xsl($csd_cache);
            case 'enable':
                $this->action_enable($csd_cache);
                return $this->action_menu($csd_cache);
            case 'disable':
                $this->action_disable($csd_cache);
                return $this->action_menu($csd_cache);         
            case 'stream':
                return $this->action_stream($csd_cache,$ids);
            case 'stream_raw':
                return $this->action_stream_raw($csd_cache,$ids);
            case 'stream_raw_trans':
                return $this->action_stream_raw($csd_cache,false,true);
            case 'update':
                return $this->action_update($csd_cache);
            case 'query_for_updated_services':
                return $this->action_soap($csd_cache);
            case 'clear':
                $this->action_clear($csd_cache);
                return $this->action_menu($csd_cache);
                break;
            default:
                break;
            }
        } 
        return $this->action_menu($csd_cache);

    }


    public function action_menu($csd_cache=false) {
	if (! (  $cachesNode = $this->template->getElementById('caches')) instanceof DOMNode
	    || ! ( $cachesNode->appendChild($ulNode = $this->template->createElement('ul')))
	    ) {
            return false;
	}
        $this->template->addHeaderLink("mootools-core.js");
        $this->template->addHeaderLink("mootools-more.js");	
        $cache_names = I2CE::getConfig()->getKeys("/modules/csd_cache");
        $only_one = false; 
	if ($csd_cache) {
            $only_one = true;
	    $cache_names = array_intersect(array($csd_cache),$cache_names);
	}
        if (!$only_one) {
            $soap_full_endpoint = self::getAccessedBaseURL() . "csd_cache?action=full_soap";
            $full_update_endpoint = self::getAccessedBaseURL() . "csd_cache?action=full_update";
            $full_clear_endpoint = self::getAccessedBaseURL() . "csd_cache?action=full_clear";
            $this->template->setDisplayDataImmediate('soap_full_stream',$soap_full_endpoint);
            $this->template->setDisplayDataImmediate('full_update',$full_update_endpoint);
            $this->template->setDisplayDataImmediate('full_clear',$full_clear_endpoint);
            $sample_full ='<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope 
    xmlns:soap="http://www.w3.org/2003/05/soap-envelope" 
    xmlns:wsa="http://www.w3.org/2005/08/addressing" 
    xmlns:csd="urn:ihe:iti:csd:2013"> 
  <soap:Header>
    <wsa:Action soap:mustUnderstand="1" >urn:ihe:iti:csd:2013:GetDirectoryModificationsRequest</wsa:Action>
      <wsa:MessageID>urn:uuid:def119ad-dc13-49c1-a3c7-e3742531f9b3</wsa:MessageID> 
        <wsa:ReplyTo soap:mustUnderstand="1">
         <wsa:Address>http://www.w3.org/2005/08/addressing/anonymous</wsa:Address> 
       </wsa:ReplyTo>
     <wsa:To soap:mustUnderstand="1">' . $soap_full_endpoint.'</wsa:To> 
  </soap:Header>
  <soap:Body> 
    <csd:getModificationsRequest>
      <csd:lastModified>2002-05-30T09:30:10.5</csd:lastModified> 
     </csd:getModificationsRequest>
  </soap:Body>
</soap:Envelope>';
            $this->template->setDisplayDataImmediate('sample_full',$sample_full);
            $this->template->setDisplayDataImmediate('show_full',1);
        } else {
            $this->template->setDisplayDataImmediate('show_full',0);
        }
            

	foreach ($cache_names  as $cache_name) {
	    if ( ! ($liNode = $this->template->createElement('li')) instanceof DOMNode
                 || ! ($ulNode->appendChild($liNode)) instanceof DOMNode
                 || ! ( $menuNode = $this->template->appendFileByNode("csd_cache_admin_menu_each.html",'div',$liNode)) instanceof DOMNode
		)  {
		continue;
	    }
	    try {
		$csd_cache = new iHRIS_CSDCache($cache_name);
	    } catch(Exception $e) {
                $this->template->removeNode($liNode);
                I2CE::raiseError("Cannot instantiate cache $cache_name");
		continue;
	    }
	    $base_url = "csd_cache/" . $cache_name;
	    $this->template->setDisplayDataImmediate('cache_name',$cache_name,$menuNode);
	    $this->template->setDisplayDataImmediate('cache_link',$base_url,$menuNode);
	    $this->template->setDisplayDataImmediate('modified',$csd_cache->getLastModified(),$menuNode);
	    $this->template->setDisplayDataImmediate('total',$csd_cache->getTotalRecords(),$menuNode);
	    $this->template->setDisplayDataImmediate('updated',$csd_cache->getUpToDateRecords(),$menuNode);
	    $this->template->setDisplayDataImmediate('stream',$base_url .'/stream',$menuNode);
	    $this->template->setDisplayDataImmediate('xsl',$base_url .'/xsl',$menuNode);
	    $this->template->setDisplayDataImmediate('stream_raw',$base_url .'/stream_raw',$menuNode);
	    $this->template->setDisplayDataImmediate('clear',$base_url .'/clear',$menuNode);
	    $this->template->setDisplayDataImmediate('update',$base_url .'/update',$menuNode);
	    $this->template->setDisplayDataImmediate('do_enable',$base_url .'/enable' ,$menuNode);
	    $this->template->setDisplayDataImmediate('do_disable',$base_url . '/disable' ,$menuNode);
	    $this->template->setDisplayDataImmediate('enabled',$csd_cache->enabled() ? 1: 0 ,$menuNode);
            $soap_endpoint = self::getAccessedBaseURL() . "csd_cache/" . $cache_name . "/query_for_updated_services";
            $this->template->setDisplayDataImmediate('soap',$soap_endpoint,$menuNode);
            
            $form = false;
            if ( ($relationship = $csd_cache->getRelationship())
                 && I2CE::getConfig()->setIfIsSet($form,'/modules/CustomReports/relationships/' . $relationship . '/form')
                 && is_string($form ) 
                 && strlen($form) > 0
                 && is_array( $ids = I2CE_FormStorage::search($form,false, array(),array(),3))
                ) {
                $i= 1;
                foreach ($ids as $id) {
                    $this->template->setDisplayDataImmediate('rel_example_' . $i, $base_url .'/stream_raw/' . $form . '|' . $id, $menuNode);
                    $this->template->setDisplayDataImmediate('csd_example_' . $i, $base_url .'/stream/' . $form . '|' . $id, $menuNode);
                    $i++;
                }
            }
            $sample ='<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope 
    xmlns:soap="http://www.w3.org/2003/05/soap-envelope" 
    xmlns:wsa="http://www.w3.org/2005/08/addressing" 
    xmlns:csd="urn:ihe:iti:csd:2013"> 
  <soap:Header>
    <wsa:Action soap:mustUnderstand="1" >urn:ihe:iti:csd:2013:GetDirectoryModificationsRequest</wsa:Action>
      <wsa:MessageID>urn:uuid:def119ad-dc13-49c1-a3c7-e3742531f9b3</wsa:MessageID> 
        <wsa:ReplyTo soap:mustUnderstand="1">
         <wsa:Address>http://www.w3.org/2005/08/addressing/anonymous</wsa:Address> 
       </wsa:ReplyTo>
     <wsa:To soap:mustUnderstand="1">' . $soap_endpoint.'</wsa:To> 
  </soap:Header>
  <soap:Body> 
    <csd:getModificationsRequest>
      <csd:lastModified>2002-05-30T09:30:10.5</csd:lastModified> 
     </csd:getModificationsRequest>
  </soap:Body>
</soap:Envelope>';
            $this->template->setDisplayDataImmediate('sample',$sample,$menuNode);
            $args = $csd_cache->get_args();
	    if ( ($svs_node = $this->template->getElementByID('svs',$menuNode)) instanceof DOMNode
                 && ($svs_node->appendChild( $ul2_node = $this->template->createElement('ul'))) 
                 && array_key_exists('svs_dependencies',$args)
                 && is_array($svs = $args['svs_dependencies'])
                ) {
                foreach ($svs as $list=>$oid) {
                    $ul2_node->appendChild($li2_node = $this->template->createElement('li'));
                    $li2_node->appendChild($this->template->createTextNode("$oid ($list)"));
                    $li2_node->appendChild($this->template->createElement('a',array('href'=>'SVS/RetrieveValueSet?id=' . $oid),"Retrieve "));
                    $li2_node->appendChild($this->template->createTextNode( " / " ));
                    $li2_node->appendChild($this->template->createElement('a',array('href'=>'SVS/publish?id=' . $oid),"Re-Publish "));
                }
            }
	}
	return true;
    }


    protected function action_enable($cache_name) {
	try {
	    $csd_cache = new iHRIS_CSDCache($cache_name);
	} catch(Exception $e) {
	    return false;
	}
        if ( ( $md = I2CE::getConfig()->traverse("/modules/csd_cache/" . $cache_name . "/args/enabled",true,false)) instanceof I2CE_MagicDataNode) {
            $md->setValue(1);
        }
        
    }

    protected function action_disable($cache_name) {
	try {
	    $csd_cache = new iHRIS_CSDCache($cache_name);
	} catch(Exception $e) {
	    return false;
	}
        if ( ( $md = I2CE::getConfig()->traverse("/modules/csd_cache/" . $cache_name . "/args/enabled",true,false)) instanceof I2CE_MagicDataNode) {
            $md->setValue(0);
        }
    }


    protected function _display($supress_output = false) {
        if ($this->get_exists('action') 
            &&  $this->get('action')  == 'full_update'
            )  {
	    parent::_display($supress_output);
	    if ( ($errors = I2CE_Dumper::cleanlyEndOutputBuffers())) {
		I2CE::raiseError("Errors:\n" . $errors);
	    }	     
	    return $this->action_full_update_ids();
        } 
	$action = false;
	$csd_cache = false;
	$ids = false;
	if (count($this->request_remainder) > 1) {
	    reset($this->request_remainder);
	    $csd_cache = current($this->request_remainder);
	    next($this->request_remainder);
	    $action = current($this->request_remainder);
	    if (count($this->request_remainder) > 2) {
		next($this->request_remainder);
		$ids = array(current($this->request_remainder));
	    }
	}
	if ($action == 'update') {
            if (array_key_exists('HTTP_HOST',$_SERVER)) {
                parent::_display($supress_output);
            }
	    if ( ($errors = I2CE_Dumper::cleanlyEndOutputBuffers())) {
		I2CE::raiseError("Errors:\n" . $errors);
	    }	     
	    return $this->action_update_ids($csd_cache,$ids);
        } else if ($action == 'stream') {
                return $this->action_stream($csd_cache,$ids);
        } else if ($action == 'stream_raw') {
                return $this->action_stream_raw($csd_cache,$ids);
	} else {
	    parent::_display($supress_output);
	}
    }

    public function action_full_update() {
	if (! (  $cachesNode = $this->template->getElementById('caches')) instanceof DOMNode
	    || ! ( $cacheNode = $this->template->appendFileByNode("csd_cache_admin_menu_update.html",'div',$cachesNode)) instanceof DOMNode
	    ) {
	    return false;
	}
        $this->template->setDisplayDataImmediate('show_full',0);
        $cache_names = I2CE::getConfig()->getKeys("/modules/csd_cache");
        foreach ($cache_names as $i=>$cache_name) {
            $enabled = 1;
            I2CE::getConfig()->setIfIsSet($enabled,"/modules/csd_cache/" . $cache_name . "/args/enabled");
            if (!$enabled) {
                unset($cache_names[$i]);
            }
        }
	$base_url = "csd_cache/";
	$this->template->setDisplayDataImmediate('cache_name',implode(", " , $cache_names) ,$cacheNode);
	$this->template->setDisplayDataImmediate('return',$base_url,$cacheNode);
	return true;        
    }

    public function action_update($cache_name) {
	if (! (  $cachesNode = $this->template->getElementById('caches')) instanceof DOMNode
	    || ! ( $cacheNode = $this->template->appendFileByNode("csd_cache_admin_menu_update.html",'div',$cachesNode)) instanceof DOMNode
	    ) {
	    return false;
	}
        $this->template->setDisplayDataImmediate('show_full',0);
	$base_url = "csd_cache/" . $cache_name;
	$this->template->setDisplayDataImmediate('cache_name',$cache_name,$cacheNode);
	$this->template->setDisplayDataImmediate('return',$base_url,$cacheNode);
	return true;
    }
     

    public function action_full_update_ids() {
        $cache_names = I2CE::getConfig()->getKeys("/modules/csd_cache");
        foreach ($cache_names as $i=>$cache_name) {
            $enabled = 1;
            I2CE::getConfig()->setIfIsSet($enabled,"/modules/csd_cache/" . $cache_name . "/args/enabled");
            if (!$enabled) {
                continue;
            }
            $this->action_update_ids($cache_name);
        }
        return true;
    }


    public $batch_size =1000;

    public function action_update_ids($cache_name,$ids = false) {
	try {
	    $csd_cache = new iHRIS_CSDCache($cache_name);
	} catch(Exception $e) {
	    return false;
	}
        if (is_array($ids)) {
            return $this->do_updates_on_ids($csd_cache,$ids);
        } else {
            while ( is_array( $ids = $csd_cache->getOutOfDateIDs($this->batch_size))
                    && count($ids) > 0) {
                if (!   $this->do_updates_on_ids($csd_cache,$ids)) {
                    I2CE::raiseError("Couldn't update cache for $cache_name on: " . implode(" " , $ids));
                    return false;
                }
            }
            return true;
        }
    }

    protected function do_updates_on_ids($csd_cache,$ids) {
	foreach ($ids as $id) {
	    list($f_name,$f_id) =array_pad(explode('|',$id,2),2,'');
	    $csd_cache->updateCacheOnID($f_id);
	    $msg = "Cache (" . $csd_cache->cache_name() . ") updated  $id";
            if (array_key_exists('HTTP_HOST',$_SERVER)) {
                $js_message = '<script type="text/javascript">addMessage("<div>' . $msg .'</div>");</script>';
                echo $js_message;
                flush();
            } else {
                echo $msg . "\n";
            }
	}
	return true;
    }

    public function action_clear($cache_name) {
	try {
	    $csd_cache = new iHRIS_CSDCache($cache_name);
	} catch(Exception $e) {
	    return false;
	}
	$csd_cache->clearCache();
	return true;

    }


    public function action_full_clear($cache_name) {
        $cache_names = I2CE::getConfig()->getKeys("/modules/csd_cache");
        foreach ($cache_names as $i=>$cache_name) {
            $enabled = 1;
            I2CE::getConfig()->setIfIsSet($enabled,"/modules/csd_cache/" . $cache_name . "/args/enabled");
            if (!$enabled) {
                continue;
            }
            $this->action_clear($cache_name);
        }
        return true;
    }

    public function action_stream($cache_name,$ids = false) {
	try {
	    $csd_cache = new iHRIS_CSDCache($cache_name);
	} catch(Exception $e) {
	    return false;
	}
	$csd_cache->streamCache($ids);
        exit(0);

    }



    public function action_full_stream($last_modified = -1 ,$cache_names = false,$headers = false, $do_headers  = true, $exit =true) {
        $directories = array();
        if (!is_array($cache_names)) {
            $cache_names = I2CE::getConfig()->getKeys("/modules/csd_cache");
        }
	if ($do_headers && array_key_exists('HTTP_HOST',$_SERVER)) {
            if (!is_array($headers)) {
                $headers = array ('Content-Type: text/xml');
            }
            foreach ($headers as $header) {
                header($header);
            }
	}

        foreach ($cache_names  as $cache_name) {

            try {
                $csd_cache = new iHRIS_CSDCache($cache_name);
            } catch(Exception $e) {
                I2CE::raiseError("$cache_name exception");
                continue;
            }
            
            if (!$csd_cache->enabled()) {
                continue;
            }
            if (!($directory = $csd_cache->directory())) {
                I2CE::raiseError("$cache_name error with directory");
                continue;
            }
            if (!array_key_exists($directory,$directories)) {
                $directories[$directory] = array();
            }
            $directories[$directory][] = $csd_cache;

        }        
        if ( ($errors  = I2CE_Dumper::cleanlyEndOutputBuffers())) {
            I2CE::raiseError("Got errors:\n$errors");
        }
        if ($do_headers) {
            echo '<?xml version="1.0" encoding="UTF-8"?>
';


            flush();
        }
        echo '<csd:CSD xmlns:csd="urn:ihe:iti:csd:2013" >
';
        foreach (array('organization','service','facility','provider') as $directory) {
            if (!array_key_exists($directory,$directories)) {
                $directories[$directory] = array();
            }
            echo "<csd:" . $directory . "Directory>";
            foreach ($directories[$directory] as $csd_cache) {
                $csd_cache->streamCache(false,$last_modified,array(),'','',false);
                flush();
            }
            echo "</csd:" . $directory . "Directory>";
          }
        echo '</csd:CSD>';
        flush();
        if ($exit) {
            exit(0);
        }
	return true;

    }



    public function action_stream_raw($cache_name,$ids = false,$transform = false) {
	try {
	    $csd_cache = new iHRIS_CSDCache($cache_name);
	} catch(Exception $e) {
	    return false;
	}
        
        $href = self::getAccessedBaseURL() . "csd_cache/{$cache_name}/xsl";
        $ss = '';
        if ($transform) {
            $ss = "<?xml-stylesheet type='text/xsl' href='$href'?>\n";
        }
	$csd_cache->streamRaw($ids,-1,false,$ss , '',true);
        exit(0);
	return true;

    }


    protected function action_xsl($cache_name,$headers = false) {
	try {
	    $csd_cache = new iHRIS_CSDCache($cache_name);
	} catch(Exception $e) {
	    return false;
	}       
        if (!is_array($headers)) {
            $headers = array('Content-Type: text/xsl');
        }
	if (array_key_exists('HTTP_HOST',$_SERVER)) {
            foreach ($headers as $header) {
                header($header);
            }
	}
        $file = $csd_cache->get_transform_file();
        readfile($file);
        exit(0);            

    }

    protected function action_soap($cache_name,$headers = false,$content = false) {
        if (!$content) {
            $content = file_get_contents("php://input");
        }
        if (!$content
            || ! ($req_doc = new DOMDocument("1.0", "UTF-8"))
            || ! $req_doc->loadXML($content)
            || ! ($xpath = new DOMXPath($req_doc)) 
            || ! ($xpath->registerNamespace('soap','http://www.w3.org/2003/05/soap-envelope'))
            || ! ($xpath->registerNamespace('wsa','http://www.w3.org/2005/08/addressing'))
            || ! ($xpath->registerNamespace('csd','urn:ihe:iti:csd:2013'))
            || ! ($results = $xpath->query('/soap:Envelope/soap:Body/csd:getModificationsRequest/csd:lastModified')) instanceof DOMNodeList
            || ! ($results->length == 1)
            || ! ($item = $results->item(0)) instanceof DOMElement
            || ! ($mod_time = $item->textContent)
            || ! ($results = $xpath->query('/soap:Envelope/soap:Header/wsa:MessageID')) instanceof DOMNodeList
            || ! ($results->length == 1)
            || ! ($item = $results->item(0)) instanceof DOMElement
            || ! ($msg_id = $item->textContent)
            ) {
            I2CE::raiseError("Invalid request");
            //NEED TO ADD IN ERROR HANDLING ACCORDING TO SPEC
            return false;
        }
	try {
	    $csd_cache = new iHRIS_CSDCache($cache_name);
	} catch(Exception $e) {
	    return false;
	}       
        if (!is_array($headers)) {
            $headers = array('Content-Type: text/xml');
        }
        foreach ($headers as $header) {
            header($header);
        }
        $pre = '<soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" xmlns:wsa="http://www.w3.org/2005/08/addressing" xmlns:csd="urn:ihe:iti:csd:2013"> <soap:Header>
            <wsa:Action soap:mustUnderstand="1" >urn:ihe:iti:csd:2013:GetDirectoryModificationsResponse</wsa:Action>
            <wsa:MessageID>urn:uuid:' . iHRIS_UUID_Map::generateUUID() . '</wsa:MessageID>
            <wsa:To soap:mustUnderstand="1"> http://www.w3.org/2005/08/addressing/anonymous</wsa:To> <wsa:RelatesTo>' . $msg_id .'</wsa:RelatesTo>
            </soap:Header>
            <soap:Body>
            <csd:getModificationsResponse xmlns:csd="urn:ihe:iti:csd:2013">
';
        $post = '</csd:getModificationsResponse>
    </soap:Body>
</soap:Envelope>';
	$csd_cache->streamCache(false,$mod_time,array(),$pre,$post);
        exit(0);            
        
    }





    protected function action_full_soap($last_modified = -1 ,$cache_names = false,$headers = false,$content = false) {
        if (!$content) {
            $content = file_get_contents("php://input");
        }
        $content = file_get_contents("php://input");
        if (!$content
            || ! ($req_doc = new DOMDocument("1.0", "UTF-8"))
            || ! $req_doc->loadXML($content)
            || ! ($xpath = new DOMXPath($req_doc)) 
            || ! ($xpath->registerNamespace('soap','http://www.w3.org/2003/05/soap-envelope'))
            || ! ($xpath->registerNamespace('wsa','http://www.w3.org/2005/08/addressing'))
            || ! ($xpath->registerNamespace('csd','urn:ihe:iti:csd:2013'))
            || ! ($results = $xpath->query('/soap:Envelope/soap:Body/csd:getModificationsRequest/csd:lastModified')) instanceof DOMNodeList
            || ! ($results->length == 1)
            || ! ($item = $results->item(0)) instanceof DOMElement
            || ! ($mod_time = $item->textContent)
            || ! ($results = $xpath->query('/soap:Envelope/soap:Header/wsa:MessageID')) instanceof DOMNodeList
            || ! ($results->length == 1)
            || ! ($item = $results->item(0)) instanceof DOMElement
            || ! ($msg_id = $item->textContent)
            ) {
            I2CE::raiseError("Invalid request");
            //NEED TO ADD IN ERROR HANDLING ACCORDING TO SPEC
            return false;
        }
        if ( ($errors  = I2CE_Dumper::cleanlyEndOutputBuffers())) {
            I2CE::raiseError("Got errors:\n$errors");
        }
        if (!is_array($headers)) {
            $headers = array('Content-Type: text/xml');
        }
        foreach ($headers as $header) {
            header($header);
        }
        echo '<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" xmlns:wsa="http://www.w3.org/2005/08/addressing" xmlns:csd="urn:ihe:iti:csd:2013"> <soap:Header>
            <wsa:Action soap:mustUnderstand="1" >urn:ihe:iti:csd:2013:GetDirectoryModificationsResponse</wsa:Action>
            <wsa:MessageID>urn:uuid:' . iHRIS_UUID_Map::generateUUID() . '</wsa:MessageID>
            <wsa:To soap:mustUnderstand="1"> http://www.w3.org/2005/08/addressing/anonymous</wsa:To> <wsa:RelatesTo>' . $msg_id .'</wsa:RelatesTo>
            </soap:Header>
            <soap:Body>
            <csd:getModificationsResponse>
';
        flush();
	$this->action_full_stream($mod_time,false,array(), false, false);
        echo '</csd:getModificationsResponse>
    </soap:Body>
</soap:Envelope>';

  
        
    }


    
    
    

}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
