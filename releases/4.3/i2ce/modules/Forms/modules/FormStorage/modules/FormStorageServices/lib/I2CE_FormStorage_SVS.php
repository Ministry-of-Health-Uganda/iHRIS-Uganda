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
* @package I2CE
* @subpackage Forms
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.2.0
* @since v4.2.0
* @filesource 
*/ 
/** 
* Class I2CE_FormStorage_SVS
* 
* @access public
*/


class I2CE_FormStorage_SVS extends I2CE_FormStorage_XMLDB{

    protected function call_service($form,$endpoint, $payload =false, $request_args = array()) {
        if ( $endpoint =='getRecords') {
	   $payload = false;
	}	
        return parent::call_service($form,$endpoint,$payload,$request_args);
    }   


    /**
     * Init form storage options
     * @param string $form 
     */
    protected function init_data( $form ) {
        if (in_array($form,$this->init_status)) {
            //already done
            return true;
        }
        if (! parent::init_data($form)) {
            return false;
        }
	if (!array_key_exists('svs',$this->namespaces[$form])) {
	    $this->namespaces[$form]['svs'] = "svs:urn:ihe:iti:svs:2008";
	}
        $options = $this->getStorageOptions($form);
        if ( !$options instanceof I2CE_MagicDataNode ) {
            I2CE::raiseError( "Invalid storage options for $form" );
            return false;
        }
	$url = false;
        if (is_array($this->global_options)
            && array_key_exists('url',$this->global_options)
            && is_string($this->global_options['url'])
            && strlen($this->global_options['url']) > 0
            ) {
            $url = $this->global_options['url'];
        }
	$options->setIfIsSet($url, "url",true);

	$curl_opts = array(
	    'HEADER'=>0,
	    );
	$cache_time = 0;
        if (is_array($this->global_options)
            && array_key_exists('cache_time',$this->global_options)
            && is_scalar($this->global_options['cache_time'])) {
            $cache_time = (int) $this->global_options['cache_time'];
        }	
        $options->setIfIsSet($cache_time, "cache_time");
        $cache_time = (int) $cache_time;

        $request_args = array();
	if (array_key_exists('request_args',$this->global_options) && is_array($this->global_options['request_args'])) {
	    $request_args = $this->global_options['request_args'];
	}
        $options->setIfIsSet($request_args, "request_args",true);
	
	
	$services = array(
	    'getRecords'=>array(
		'results'=>'//svs:Concept/@id',
		'url'=>$url,
		'request_args'=>$request_args,
		'cache_time'=>$cache_time,
		'transforms'=>array(
		    'out'=>'0',
		    'in'=> '@'. $form . '/getRecords_in.xsl'
		    )
		),
	    'getAllRecordData'=>array(
		'url'=>$url,
		'request_args'=>$request_args,
		'cache_time'=>$cache_time,
		'transforms'=>array(
		    'out'=>'0',
		    'in'=> '@'. $form . '/getAllRecordData_in.xsl'
		    )
		)
	    );
	foreach (array_keys($services) as $endpoint) {
	    if (!array_key_exists($endpoint,$this->services[$form])) {
		$this->services[$form][$endpoint] = array();
	    }
	}

	foreach ($this->services[$form] as $endpoint=>&$data) {
	    if (!array_key_exists($endpoint,$services)) {
		continue;
	    }
	    I2CE_Util::merge_recursive($services[$endpoint],$data);
	    $data = $services[$endpoint];
	}
	unset($data);
	return true;
    }




}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
