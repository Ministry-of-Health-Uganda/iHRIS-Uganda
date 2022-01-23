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
* @subpackage CSD
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.2.0
* @since v4.2.0
* @filesource 
*/ 
/** 
* Class CSD_Page_RapidPro_Base
* 
* @access public
*/


abstract class CSD_Page_RapidPro_Base extends I2CE_Page {


    protected $server_host = false;
    protected $api_token = false;
    protected $slug = null;
    public $rapidpro = null;
    protected $csd_host = null;
    protected $csd_host_user = null;
    protected $csd_host_password = null;

    public  function __construct( $args,$request_remainder , $get = null, $post = null) {
	parent::__construct( $args,$request_remainder , $get, $post );
        foreach (array('server_host','api_token','slug','csd_host','csd_host_user','csd_host_password') as $key) {
            if (array_key_exists($key,$this->args)
                && is_scalar($this->args[$key])
                ){
                $this->$key = $this->args[$key];
            }
        }
	$this->rapidpro = new CSD_Interface_RapidPro($this->api_token,$this->server_host);
    }




}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
