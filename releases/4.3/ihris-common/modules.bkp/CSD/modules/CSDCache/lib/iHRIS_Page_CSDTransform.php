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
* Class iHRIS_Page_CSDTransform
* 
* @access public
*/


class iHRIS_Page_CSDTransform extends I2CE_PageXMLRelationshipSingle {

    public  static function getCSDArgs($csd_cache) {
	$args = array(
	    'relationship'=>$csd_cache,
	    'transform'=>'@'  . $csd_cache . '.xsl',
	    'get_all_fields'=>1
	    );
	if (!I2CE_MagicDataNode::checkKey($csd_cache)
	    || !is_array($t_args = I2CE::getConfig()->getAsArray("/modules/csd_cache/"  .  $csd_cache .'/args'))) {
	    $t_args = array();
	}
	I2CE_Util::merge_recursive($args,$t_args);
	return $args;
    }

    public  function __construct( $args,$request_remainder , $get = null, $post = null) {
	if (array_key_exists('csd_cache',$args)) {
	    $csd_args = self::getCSDArgs($args['csd_cache']);
	    I2CE_Util::merge_recursive($csd_args,$args);
	    parent::__construct($csd_args,$request_remainder , $get ,$post);
	}  else {
	    parent::__construct($args,$request_remainder , $get ,$post);
	}
    }
    

    protected function get_transform_vars() {
        $transform_vars = parent::get_transform_vars();
        $transform_vars['currentDateTime'] = date('c');
        return $transform_vars;
    }
    

}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
