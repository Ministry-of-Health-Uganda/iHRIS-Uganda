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
* @subpackage FormStorage
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.2.0
* @since v4.2.0
* @filesource 
*/ 
/** 
* Class I2CE_FormStorage_Service
* 
* @access public
*/


abstract class I2CE_FormStorage_Service extends I2CE_FormStorage_Mechanism {

    /**
     *$var protected array $service service used to populate
     */
    protected $services = array();




    /**
     *$var protected array $init_status
     */
    protected $init_status = array();




    /**
     * Init form storage options
     * @param string $form 
     */
    protected function init_data( $form ) {
        if (in_array($form,$this->init_status)) {
            //already done.
            return true;
        }
        $options = $this->getStorageOptions($form);
        if ( !$options instanceof I2CE_MagicDataNode ) {
            I2CE::raiseError( "Invalid storage options for $form" );
            return false;
        }
        $url = '';
        $options->setIfIsSet($url, "url");
        if (!is_string($url)) { $url ='';}
        $curl_opts = array();
        $options->setIfIsSet($curl_opts, "curl_opts",true);
        if (!is_array($curl_opts)) { $curl_opts = array();}
        $this->services[$form] = array();
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

        $options->setIfIsSet($this->services[$form], "services",true);
        if (!is_array($this->services[$form])) {
            $this->services[$form] = array(); 
        }
        foreach ($this->services[$form] as $i=>&$services) {
            if (!is_array($services)) {
                unset($this->services[$form][$i]);
                continue;
            }
            if (!array_key_exists('url',$services)
                || !$services['url']) {
                $services['url'] = $url;
            }
            if (!array_key_exists('curl_opts',$services)
                ||!is_array($services['curl_opts'])
                || count($services['curl_opts']) == 0) {
                $services['curl_opts']  = $curl_opts;
            }
            if ($cache_time && 
                !array_key_exists('cache_time',$services)) {
                $services['cache_time'] = $cache_time;
            }
            if (!array_key_exists('request_args',$services)
                ||!is_array($services['request_args'])
                ) {
                $services['request_args'] =$request_args;
            }

       
        }
        unset($services);
        $this->init_status[] = $form;
	return true;
    }

    protected function has_service($form,$endpoint) {
        return  (array_key_exists($endpoint,$this->services[$form])
                 &&  is_array($args = $this->services[$form][$endpoint])
                 &&  array_key_exists('url', $args)
                 &&  (is_string($url = $args['url']))
                 &&  $url
                 && ! (
                     array_key_exists('enabled',$this->services[$form][$endpoint]) 
                     && ! $this->services[$form][$endpoint]['enabled']
                     )
            );
    }



    protected function clear_service_cache($form) {
        if (function_exists('apc_fetch')) {
            $apc_root  = 'FS_Service_(' .$form .')_' . get_class($this) .'_';
            $iter = new APCIterator('user','/^' .preg_quote($apc_root) .'/');
            foreach ($iter as $item) {
                I2CE::raiseMessage("Deleting $apc_root:  " . $item['key']);
                apc_delete($item['key']);
            }
        }
    }


    protected $last_cache_key;
    protected function unset_last_cache() {
        if (function_exists('apc_fetch') && is_string($this->last_cache_key)) {
            I2CE::raiseError("Deleting last cache");
            apc_delete($this->last_cache_key);
            $this->last_cache_key =false;
        }
    }


    

    protected function call_service($form,$endpoint, $payload =false, $request_args = array()) {
	if (! ($this->init_data($form))
            || ! is_array($this->services[$form])
            || ! array_key_exists($endpoint,$this->services[$form])
            ) {
            I2CE::raiseError("Could not initialize service data for $form at endpoint $endpoint\n" . print_r($this->services[$form],true));
            return false;
        }
        $args = $this->services[$form][$endpoint];
        if (!is_array($args)) {
            $args = array();
        }
        $url = $args['url'];

        if (is_array($args) && array_key_exists('request_args',$args) && is_array($args['request_args'])) {
            I2CE_Util::merge_recursive($request_args ,$args['request_args']);
        }

        if (!$this->has_service($form,$endpoint)) {
            I2CE::raiseError("No service details defined for $endpoint:" . print_r($this->services,true));
            return false;
        }


        //check to see if the repsponse is cached:
        $curl_opts = array();
        if (array_key_exists('curl_opts',$args)
            && is_array($args['curl_opts'])) {
            $curl_opts = $args['curl_opts'];
        }
        if (is_array($request_args)
            && count($request_args) >0
            && ($append = http_build_query($request_args))
            ) {
            if (strpos($url,'?') > 0) {
                $sep = '&';
            } else {
                $sep = '?';
            }
            $url .= $sep . $append;
        }
        $this->last_cache_key = false;
        $cache_time = 0;
        if (function_exists('apc_fetch')) {
            $this->last_cache_key = 'FS_Service_(' .$form .')_' . get_class($this) .'_'.  md5($url . $payload . print_r($curl_opts,true));            	            
            $success= false;
            $out = apc_fetch($this->last_cache_key,$success);
            if ($success) {
                I2CE::raiseError("Got cached $endpoint for $form:" . $out) ;
                return $out;
            }
            if (array_key_exists('cache_time',$this->services[$form][$endpoint])) {
                $cache_time = (int) $this->services[$form][$endpoint]['cache_time'];
            }
        }

        I2CE::raiseError("Service at $url");
        if ( ! is_resource($ch = curl_init($url)) ) {
            I2CE::raiseError("Could not create curl resource");
            return false;
        }        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        $excludes = array( 'RETURNTRANSFER','INFILE','FILE');
        if ($payload) {
            $excludes[] = 'POSTFIELDS';
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            I2CE::raiseError("Sending payload to $url:\n$payload");
        }

        foreach ($curl_opts as $opt=>$val) {
            $opt = strtoupper($opt);
            if (in_array($opt,$excludes)
                || ($opt_code = @constant('CURLOPT_' . $opt)) === null) {
                continue;
            }
            if (! (curl_setopt($ch,$opt_code,$val))) {
                I2CE::raiseError("Could not set option $opt");
                return false;
            }
        }
        $curl_out = curl_exec($ch);
        if ($err = curl_errno($ch) ) {
            I2CE::raiseError("Curl response error ($err) on endpoint $endpoint of $form:\n" . curl_error($ch));
            return false;
        }
        curl_close($ch);
        I2CE::raiseError("From $url Reveived:[" . $curl_out ."]");
        if ($this->last_cache_key && $cache_time ) {
            //already check above that apc is here.
            apc_store($this->last_cache_key,$curl_out,$cache_time);
            I2CE::raiseError("Caching $endpoint result of $form for $cache_time");
        }
        return $curl_out;
    }
    


}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
