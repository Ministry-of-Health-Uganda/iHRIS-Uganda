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
* @package i2ce
* @subpackage forms
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.2.0
* @since v4.2.0
* @filesource 
*/ 
/** 
* Class I2CE_FormStorage_SVSFile
* 
* @access public
*/


class I2CE_FormStorage_SVSFile extends I2CE_FormStorage_XML_Base {

    protected function getNamespaces($form) {
	return array('svs'=>'urn:ihe:iti:svs:2008');
    }

    protected function getDOMData( $form ) {
        return  parent::getDOMData($form);
    }

    protected function getLocationId($form, $node, $count) {
        return $node->getAttribute('codeSystem') . '@@@' . $node->getAttribute('code') ;
        
    }

    protected function getSearchCategory($form) {
        return '';
    }
    protected function getDataNodesQuery($form) {        
        return 'svs:Concept';
    }
    protected function getBaseQuery($form) {        
        return '/svs:ValueSet/svs:ConceptList[1]';
    }


    protected function getFormData($form,$node) {
        if ( !$node instanceof DOMElement) {
            return array();
        }
        $data =array();
        $attrs = array('name'=>'displayName','code'=>'code','codeSystem'=>'codeSystem');
        foreach ($attrs as $field=>$attr) {
            $data[$field] = $node->getAttribute($attr);
        }
        return $data;
    }


    protected function _getFile($form) {        
        return 'mdn://modules/forms/forms/' . $form . '/storage_options/SVSFile/source';
    }

}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
