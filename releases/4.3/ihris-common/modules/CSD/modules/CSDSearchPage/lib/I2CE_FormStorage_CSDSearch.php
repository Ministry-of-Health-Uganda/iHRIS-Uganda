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
* Class I2CE_FormStorage_CSDSearch
* 
* @access public
*/


class I2CE_FormStorage_CSDSearch extends I2CE_FormStorage_XMLDB{

    
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
        $options = $this->getStorageOptions($form);
        if ( !$options instanceof I2CE_MagicDataNode ) {
            I2CE::raiseError( "Invalid storage options for $form" );
            return false;
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
        if ( ! is_array($remote_services = I2CE::getConfig()->getAsArray("/modules/forms/storage_options/CSD/remote_services"))
             || ! array_key_exists($directory,$remote_services)
             || ! is_scalar($selected= $remote_services[$directory])) {
            I2CE::raiseError("No remote service directory selected in global CSD  options");
            return false;
        }
        list($t_form,$id) =array_pad(explode('|',$selected,2),2,'');
        if ( $t_form != 'csd_info_manager'
             || $id== '0'
             || ! is_array( $urls  = I2CE_FormStorage::lookupField('csd_info_manager',$id,array('url'),false))
             || !array_key_exists('url',$urls)
             || ! $urls['url']
            ) {
            I2CE::raiseError("Invalid connection details from selected service: $selected");
        }


	if (!array_key_exists('csd',$this->namespaces[$form])) {
	    $this->namespaces[$form]['csd'] = "urn:ihe:iti:csd:2013";
	}
	$cache_time = 0;
        if (is_array($this->global_options)
            && array_key_exists('cache_time',$this->global_options)
            && is_scalar($this->global_options['cache_time'])) {
            $cache_time = (int) $this->global_options['cache_time'];
        }
        $options->setIfIsSet($cache_time, "cache_time");
        $cache_time = (int) $cache_time;
	if (!array_key_exists('populate', $this->services[$form])) {
	    $this->services[$form]['populate'] = array();
	}
	$populate =array(
	    'url'=>$urls['url'],
	    'curl_opts' => array(
		'HEADER'=>0,
		'POST'=>1,
		'HTTPHEADER'=>array('content-type'=>'content-type: text/xml')
		),
	    'cache_time'=>$cache_time
	    );
	I2CE_Util::merge_recursive($this->services[$form]['populate'],$populate);	
	return true;
    }

    


}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
