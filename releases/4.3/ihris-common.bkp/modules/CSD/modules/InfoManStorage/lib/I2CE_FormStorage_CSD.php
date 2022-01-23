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
* Class I2CE_FormStorage_CSD
* 
* @access public
*/


class I2CE_FormStorage_CSD extends I2CE_FormStorage_XMLDB {

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
	if (!array_key_exists('csd',$this->namespaces[$form])) {
	    $this->namespaces[$form]['csd'] = "urn:ihe:iti:csd:2013";
	}
        $options = $this->getStorageOptions($form);
        if ( !$options instanceof I2CE_MagicDataNode ) {
            I2CE::raiseError( "Invalid storage options for $form" );
            return false;
        }
        $directory =false;
        if (! ($options->setIfIsSet($directory,'csd_directory'))
            || !in_array($directory,array('provider','service','facility','organization'))
            ) {
            I2CE::raiseError("No valid defined for $form");
            return false;
        }
        if (!is_array($this->global_options)
            ||! array_key_exists('remote_services',$this->global_options)
            || !is_array($remote_services = $this->global_options['remote_services'])
            || ! array_key_exists($directory,$remote_services)
            || ! is_scalar($selected= $remote_services[$directory])) {
            I2CE::raiseError("No remote service directory selected in global CSD  options");
            return false;
        }
        list($t_form,$id) =array_pad(explode('|',$selected,2),2,'');
        if ( $t_form != 'csd_info_manager'
             || $id== '0'
             || ! is_array( $urls  = I2CE_FormStorage::lookupField('csd_info_manager',$id,array('url','url_updating'),false))
             || !array_key_exists('url',$urls)
             || ! $urls['url']
             || !array_key_exists('url_updating',$urls)
            ) {
            I2CE::raiseError("Invalid connection details from selected service: $selected");
        }
            
	$curl_opts = array(
	    'HEADER'=>0,
	    'POST'=>1,
	    'HTTPHEADER'=>array('content-type'=>'content-type: text/xml')
	    );
        if (is_array( $auth  = I2CE_FormStorage::lookupField('csd_info_manager',$id,array('user','password'),false))
            && array_key_exists('user',$auth)
            && $auth['user']
            ){
            $curl_opts['USERPWD'] = $auth['password'];
            $curl_opts['USERAGENT'] = $auth['user'];
        }
        if (is_array( $ssl  = I2CE_FormStorage::lookupField('csd_info_manager',$id,array('ssl_version'),false))
            && array_key_exists('ssl_version',$ssl)
            && $ssl['ssl_version']) {
            $curl_opts['SSLVERSION'] = $ssl['ssl_version'];
            $curl_opts['SSL_VERIFYPEER'] = false;
            $curl_opts['SSL_VERIFYHOST'] = false; //or 2?
        }

	$cache_time = 0;
        if (is_array($this->global_options)
            && array_key_exists('cache_time',$this->global_options)
            && is_scalar($this->global_options['cache_time'])) {
            $cache_time = (int) $this->global_options['cache_time'];
        }
        $options->setIfIsSet($cache_time, "cache_time");
        $cache_time = (int) $cache_time;
	$updating = array('delete','create','update');
	$reading = array('getRecords','populate');
	foreach ($reading as $endpoint) {
	    if (!array_key_exists($endpoint,$this->services[$form])) {
		$this->services[$form][$endpoint] = array();
	    }
	}
	if ($urls['url_updating']) {
	    foreach ($updating as $endpoint) {
		if (!array_key_exists($endpoint,$this->services[$form])) {
		    $this->services[$form][$endpoint] = array();
		}
	    }
	}

	foreach ($this->services[$form] as $endpoint=>&$data) {
            if (in_array($endpoint,$updating)) {
                if ( $urls['url_updating']) {
                    $data['url'] = $urls['url_updating'];
                }
            } else { 
                if ( $urls['url']) {
                    $data['url'] = $urls['url'];
                }
	    }
	    I2CE_Util::merge_recursive($data['curl_opts'],$curl_opts);
            if ($cache_time 
		&&  in_array($endpoint,$reading)
                && !array_key_exists('cache_time',$data)
		) {
                $data['cache_time'] = $cache_time;
            }

	}
	return true;
    }



    /**
     * @param string $form  The form name.
     * @param boolean $parent. Defaults to false.    If it is scalar and non-boolean, it is consider to be the ID of the parent, 
     * and then we get all forms with parent the given id.
     * @param mixed $where_data array or class implementing ArrayAccess, Iterator, and Countable (e.g. MagicDataNode) . the where data.  
     * @param array $ordering. An array of fields to order by.  Defaults to the empty array.  Prepend a - to order by in descending order.
     * @param mixed $limit. Defaults to false.  It true, returns only one result.  If an integer it is the numeber of records to limit to.
     *  If it is as an array of two integers, it is the offset and then number of results to limit to.  
     * @returns mixed an array of matching form ids.  However, ff $limit_one is true or 1 or 
     * array ($offset,1) then then we return either the id or false,  if none found or there was an error.
     */
    public  function search($form, $parent=false, $where_data=array(), $ordering=array(), $limit = false) {                                      
	if (! $this->init_data($form)
            || !$this->has_service($form,'getRecords')            
            || (is_array($where_data) && count($where_data) > 0)
            || $parent
            ) {
            return parent::search($form,$parent,$where_data,$ordering,$limit);
        }
        //we have a limit.  use getRecords
        return $this->getRecords_worker( $form,  -1, false ,$limit);
    }


}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
