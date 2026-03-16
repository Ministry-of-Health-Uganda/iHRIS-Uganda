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
* @subpackage SVS
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.2.0
* @since v4.2.0
* @filesource 
*/ 
/** 
* Class iHRIS_SVS
* 
* @access public
*/


class iHRIS_SVS {
    

    protected $oid ='';
    protected $listObj = false;
    protected $list = false;
    public function __construct($oid) {
	$this->oid = $oid;
	$this->list =false;
	if (! I2CE_MagicDataNode::checkKey($this->oid)
	    || ! I2CE::getConfig()->setIfIsSet($this->list,"/modules/SVS/lists/" . $this->oid . "/list")
	    || ! $this->listObj = I2CE_FormFactory::instance()->createContainer($this->list)
	    ) {
	    throw new Exception("Bad List");
	}
    }

    public static function getOIDs() {
        $ids = I2CE::getConfig()->getKeys("/modules/SVS/lists");
        if (!is_array($ids)) {
            $ids = array();
        }
        return $ids;
    }


    public function getMeta() {
        $meta = I2CE::getConfig()->getAsArray("/modules/SVS/lists/" . $this->oid . "/meta");
        if (!is_array($meta)) {
            $meta = array();
        }
        $req_keys = array(
            'displayName'=>$this->listObj->getDisplayName(),
            'Status'=>'Active',
            'Source'=>'iHRIS',
            'SourceURI'=>I2CE_Page::getAccessedBaseURL(),
            'Type'=>'Expanded'
            );
        I2CE::getConfig()->setIfIsSet($meta['Source'],"/config/site/module");
        foreach ($req_keys as $key=>$val) {
            if (!array_key_exists($key,$meta)
                || !$meta[$key]) {
                $meta[$key] = $val;
            }
        }
        return $meta;
    }

    public static function getVersions($oid) {
        $versions = array();
        if (I2CE_MagicDataNode::checkKey($oid)) {
            $versions = I2CE::getConfig()->getKeys("/modules/SVS/lists/$oid/published");
            if (!is_array($versions)) {
                $versions = array();
            }
        }
        return $versions;
    }
    public static function getLangs($oid,$version) {
        $langs = array();
        if (I2CE_MagicDataNode::checkKey($oid)
            && I2CE_MagicDataNode::checkKey($version)
            ) {
            $langs = I2CE::getConfig()->getKeys("/modules/SVS/lists/$oid/published/$version");
            if (!is_array($langs)) {
                $langs = array();
            }
        }
        return $langs;
    }
    
    public static function  getPublishedConceptList($oid, $version =false ,$lang = 'en-US') {
	$doc = false;
        $versions = self::getVersions($oid);
	if (!$version 
	    && count($versions) > 0
	    ) {
	    $version = max($versions);
	}
	if (!in_array($version,$versions)) {
	    return false;
	}
	$available_langs = self::getLangs($oid,$version);
	$available_locales = array();
	foreach ($available_langs as $available_lang) {
	    $available_locales[ strtolower(strtr($available_lang,'-','_'))] = $available_lang;
	}
	$found_lang =false;
	foreach (I2CE_Locales::getLocaleResolution(strtr($lang,'-','_')) as $search_locale) {
	    $search_locale = strtolower($search_locale);
	    if (array_key_exists($search_locale,$available_locales)) {
		$found_lang = $available_locales[$search_locale];
		break;
	    }
	}

	if (! I2CE_MagicDataNode::checkKey($found_lang)
	    || ! ($path = "/modules/SVS/lists/" . $oid . "/published/" . $version .'/' . $found_lang)	    
	    || ! I2CE::getConfig()->setIfIsSet($doc,$path)) {
	    return false;
	}
	return $doc;
    }
    

    public function publishConceptList($version = false,$lang = 'en-US',$doc = false) {
	if ( $version === false ) {
            $versions = self::getVersions($this->oid);
	    if (count($versions) > 0) {
		$version = max($versions) + 1;
	    } else {
		$version =1;
	    }
	}
	if (!$doc) {
	    $doc = $this->generateConceptList($version,$lang,false);
	}
	if (!is_string($doc)
	    || !I2CE_MagicDataNode::checkKey($version) 
	    || !I2CE_MagicDataNode::checkKey($lang) 
	    || ! ($path = "/modules/SVS/lists/" . $this->oid . "/published/" . $version .'/' . $lang)
	    || !($md = I2CE::getConfig()->traverse($path,true,false)) instanceof I2CE_MagicDataNode
	    ) {
	    return false;
	}
	$md->setValue(base64_encode($doc));        
	$md->setAttribute('binary',1);
	$md->setAttribute('encoding','base64');
	$md->setAttribute('mime-type','text/xml');
	return true;
    }

    public function generateConceptList($version,$lang = 'en-US',$as_doc = true) {
	$doc = new DOMDocument("1.0", "UTF-8");
	$doc->appendChild($vs = $doc->createElement('svs:ValueSet'));
	$vs->setAttribute('xmlns:svs',"urn:ihe:iti:svs:2008");
	$vs->setAttribute('id',$this->oid);
        $meta = $this->getMeta();
	$vs->setAttribute('displayName',$meta['displayName']);
	$vs->setAttribute('version',$version);
	$data = $this->generateListData($lang);
	$vs->appendChild($cl = $doc->createElement('svs:ConceptList'));
	$cl->setAttribute('xml:lang',$lang);
	foreach ($data as $attributes) {
	    $cl->appendChild($c = $doc->createElement('svs:Concept'));
	    foreach ($attributes as $k=>$v) {
		$c->setAttribute($k,$v);
	    }
	}
	if ($as_doc) {
	    return $doc;
	} else {
	    return $doc->saveXML();
	}
    }


    public  function generateListData($lang = 'en-US') {
	I2CE_Locales::setPreferredLocale(strtr($lang,'-','_'));
	$display_style ='default';
	I2CE::getConfig()->setIfIsSet($display_style,"/modules/SVS/lists/" . $this->oid . "/meta/display_style");
	$code_style ='code';
	I2CE::getConfig()->setIfIsSet($code_style,"/modules/SVS/lists/" . $this->oid . "/meta/code_style");
	$code_system_style = false;
	I2CE::getConfig()->setIfIsSet($code_system_style,"/modules/SVS/lists/" . $this->oid . "/meta/code_system_style");
        $meta = $this->getMeta();
	$code_system  = $meta['Source'];
	I2CE::getConfig()->setIfIsSet($code_system,"/modules/SVS/lists/" . $this->oid . "/meta/code_system");

	$where = I2CE::getConfig()->getAsArray("/modules/SVS/lists/" . $this->oid . "/where");
        if (!is_array($where)) {
            $where = array();
        }
        $inc_hidden = false;
        I2CE::getConfig()->setIfIsSet($inc_hidden,"/modules/SVS/lists/" . $this->oid . "/hidden"); //include hidden
        if (!$inc_hidden) {
            $nohidden =  array(
                'operator'=>'OR',
                'operand'=>array(
                    array(
                        'operator'=>'FIELD_LIMIT',
                        'field'=>'i2ce_hidden',
                        'style'=>'no',
                        'data'=>array( )
                        ),
                    array(
                        'operator'=>'FIELD_LIMIT',
                        'field'=>'i2ce_hidden',
                        'style'=>'null',
                        'data'=>array( )
                        )

                    )
                );
            if(count($where) > 0) {
                $where  = array(
                    'operator'=>'AND',
                    'operand'=>array(0=>$where, 1=>$nohidden)
                    );
            } else {
                $where = $nohidden;
            }
        }
	
	$styles = array('displayName'=>$display_style,'code'=>$code_style);
	if ($code_system_style) {
	    $styles['codeSystem']=$code_system_style;
	}
	$all_fields = array();
	$disp_strings = array();
	$disp_fields = array();
	foreach ($styles as $out=>$style) {
            if ($style == 'id') {
                continue;
            } 
            $disp_strings[$out] = I2CE_List::getDisplayString($this->list,$style);
            $disp_fields[$out] = I2CE_List::getDisplayFields($this->list,$style);
            ksort($disp_fields[$out]);            
            $all_fields = array_merge($all_fields,$disp_fields[$out]);
	}
	$all_fields = array_unique($all_fields);
	$field_data = I2CE_FormStorage::listDisplayFields($this->list, $all_fields,  false , $where,array());
	$data = array();
	foreach ($field_data as $id=>$fields) {
	    $data[$id] = array();
	    foreach ($styles as $out=>$style) {
                if ($style =='id') {
                    $data[$id][$out] = $id;
                } else {
                    $values = array();
                    foreach ($disp_fields[$out] as $field) {
                        if (array_key_exists($field,$fields)) {
                            $val   =  $fields[$field];
                        } else {
                            $val = '';
                        }
                        $values[] = $val;
                    }
                    $data[$id][$out] = @vsprintf($disp_strings[$out] , $values );
                }
	    }
	    if (!in_array('codeSystem',$styles)) {
		$data[$id]['codeSystem'] = $code_system;
	    }

	}
	return $data;	
    }


    public static function getAvailableStyles($list) {
        $styles = array();
        if ( ($listObj  = I2CE_FormFactory::instance()->createContainer($list)) instanceof I2CE_List
             && is_array($lists = $listObj->getMeta('list'))
            ) {
            $styles = array_keys($lists);
        }
        $styles =array_unique(array_merge($styles,array('id')));
        return $styles;
    }


}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
