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
 * @subpackage CSDCache
 * @author Carl Leitner <litlfred@ibiblio.org>
 * @version v4.2.0
 * @since v4.2.0
 * @filesource 
 */ 
/** 
 * Class iHRIS_CSDCache
 * 
 * @access public
 */


class iHRIS_CSDCache extends I2CE_Fuzzy{


    protected $pageObj;
    protected $relationship;
    protected $relObj;
    protected $transform;
    protected $insert_statement;    
    protected $primary_form ;
    protected $directory;
    protected $cache_name ;
    protected $enabled =true;
    public function cache_name() {
        return $this->cache_name;
    }
    public function directory() {
        return  $this->directory;
    }
    public function enabled() {
        return $this->enabled;
    }

    public static function bchexdec($hex) {
        //from http://php.net/manual/en/ref.bc.php
        if(strlen($hex) == 1) {
            return hexdec($hex);
        } else {
            $remain = substr($hex, 0, -1);
            $last = substr($hex, -1);
            return bcadd(bcmul(16, self::bchexdec($remain)), hexdec($last));
        }
    }

    public function get_transform_file() {
        return $this->pageObj->get_transform_file();
    }
    public function get_args() {
        return $this->pageObj->get_args();
    }

    public static function generateUUIDasOID() {
        $uuid = iHRIS_UUID_Map::generateUUID();
        return  '2.25.' . self::bchexdec(strtr($uuid,'-',''));
    }
    /**
     * @param string $cache_name
     * Example: csd_query_updated_services
     */
    public function __construct($cache_name) {
        if (!I2CE_MagicDataNode::checkKey($cache_name)
                ||! in_array($cache_name, I2CE::getConfig()->getKeys('/modules/csd_cache'))
                || !($this->pageObj = new iHRIS_Page_CSDTransform(array('csd_cache'=>$cache_name),array(),array(),array()))       
                || !($this->pageObj->loadRelationship())
                || ! is_array($args = $this->pageObj->get_args())
                || !array_key_exists('relationship',$args)
                || !is_string($this->relationship = $args['relationship'])
                || !I2CE_MagicDataNode::checkKey($this->relationship)
                || !array_key_exists('transform',$args)
                || ! I2CE::getConfig()->setIfIsSet($this->primary_form,"/modules/CustomReports/relationships/" . $this->relationship . "/form")
                || !$this->primary_form
                || !is_string($this->transform = $args['transform'])
                || !$this->transform
           ) {
            throw new Exception("Invalid page");
        }
        $this->cache_name = $cache_name;
        $this->relObj = new I2CE_FormRelationship($this->relationship); //may throw exception.
        $db = I2CE::PDO();
        $insert_qry ="INSERT INTO csd_cache (relationship,transform,record,last_modified,xml_entry) "
            . "VALUES ( ?, ?, ?, ? , ?)" 
            . " ON DUPLICATE KEY UPDATE xml_entry = values(xml_entry), last_modified = values(last_modified)";
        try {
            $this->insert_statement = $db->prepare($insert_qry);
        } catch( PDOException $e ) {
            I2CE::pdoError($e, "Bad prepared statement");
            throw new Exception("Invalid prepared statement: " . $insert_qry);
        }
        if (array_key_exists('enabled',$args)) {
            $this->enabled =$args['enabled'];
        }
        if (!array_key_exists('directory',$args)
                || !is_string($args['directory'])
                || ! $args['directory']
           ) {
            throw new Exception("Invalid directory");            
        }
        $this->directory = $args['directory'];
    }

    public function clearCache() {
        I2CE::raiseError("clearing CSD cache");
        $db = I2CE::PDO();
        $qry = "DELETE FROM `csd_cache` WHERE `relationship` = ? and `transform` = ?";
        try {
            I2CE_PDO::execParam($qry, array( $this->relationship, $this->transform ) );
        } catch( PDOException $e ) {
            I2CE::pdoError($e,"Could not clear cache");
            return false;
        }
        return true;
    }

    public function getExistingIDs() {
        $qry = "SELECT record FROM `csd_cache` WHERE `relationship` = ? and `transform` = ? ";
        $db = I2CE::PDO();
        $ids = array();
        try {
            $result = $db->prepare($qry);
            $result->execute( array( $this->relationship, $this->transform ) );
            while( $row = $result->fetch() ) {
                list($form,$id) = array_pad(explode("|",$row->record,2),2,'');
                if ($id) {
                    $ids[] = $id;
                }
            }
            $result->closeCursor();
            unset( $result );
        } catch ( PDOException $e ) {
            I2CE::pdoError( $e, "Could not get ids" );
        }
        return $ids;
    }


    public function getLastModified() {
        $qry = "SELECT max(last_modified) AS last_modified FROM `csd_cache` WHERE `relationship` = ? and `transform` = ?";
        try {
            $res = I2CE_PDO::getRow($qry, array( $this->relationship, $this->transform ) );
            return $res->last_modified;
        } catch ( PDOException $e ) {
            I2CE::pdoError($e,"Could not clear cache");
            return false;
        }

    }

    public function updateCache($last_modified = -1) {
        $ff = I2CE_FormFactory::instance();
        $form_name = $this->relObj->getPrimaryForm();
        I2CE::raiseError("Primary form = $form_name");
        $ids =   $ff->getRecords(  $form_name, $last_modified);
        return $this->processRecords($ids);
    }


    public function processRecords($ids) {
        $failures = array();
        $ids = array_slice($ids,0,10);
        foreach ($ids as $id) {
            if (! ($this->updateCacheOnID($id))) {
                $failures[]  = $id;
            }
        }
        if (count($failures) > 0) {
            I2CE::raiseError("Had " . count($failures) . " " . implode(",",$failures));
        }
        return true;
    }



    public function processMissingRecords() {
        $existing_ids = $this->getExistingIDs();
        $ff = I2CE_FormFactory::instance();
        $form_name = $this->relObj->getPrimaryForm();
        I2CE::raiseError("Primary form = $form_name");
        $ids =   $ff->getRecords(  $form_name,-1);
        $ids = array_diff($ids,$existing_ids);
        return $this->processRecords($ids);
    }


    public function getTotalRecords()  {
        $form_name = $this->relObj->getPrimaryForm();
        $qry = "select count(*) as tot from `hippo_{$form_name}`";
        try {
            $results = I2CE_PDO::getRow($qry);
            return $results->tot;
        } catch ( PDOException $e ) {
            I2CE::pdoError($e,"Cannot access cached results for $this->relationship $this->transform");
            return false; 
        }

    }

    public function getRelationship() {
        return $this->relationship;
    }

    public function getUpToDateRecords()  {
        $form_name = $this->relObj->getPrimaryForm();
        $qry = "select count(*) as tot from `hippo_{$form_name}` h join csd_cache c on h.id = c.record  where relationship = ? and transform = ? and h.last_modified <= c.last_modified";
        try {
            $results = I2CE_PDO::getRow($qry, array( $this->relationship, $this->transform ) );
            return $results->tot;
        } catch ( PDOException $e ) {
            I2CE::pdoError($e,"Cannot access cached results for $this->relationship $this->transform");
            return false; 
        }
    }

    public function getOutOfDateIDs($limit = false) {
        $form_name = $this->relObj->getPrimaryForm();
        $qry = "select h.id as id from `hippo_{$form_name}` h left join  csd_cache as c  on h.id = c.record   " .
            " where ((c.last_modified is null) or (c.last_modified <  h.last_modified) )  " .
            " and  (c.relationship = ? or c.relationship is null) " .
            " and  ( c.transform = ? or c.transform is null) ";
        if ($limit) {
            $qry .= "limit 0,$limit";
        }
        try {
            $db = I2CE::PDO();
            $results = $db->prepare($qry);
            $results->execute( array( $this->relationship, $this->transform ) );
            $ids = array();
            while ($row = $results->fetch()) {
                $ids[] =  $row->id;
            }
            $results->closeCursor();
            unset( $results );
            return $ids;
        } catch ( PDOException $e ) {
            I2CE::pdoError($e,"Cannot access cached results for $this->relationship $this->transform");
            return array();
        }


    }





    public function updateCacheOnID($id) {
        $matches = array()           ;
        if ( ! is_string($xml = $this->pageObj->generate($id,false))
                || !  preg_match('/<(csd:)?record\s(.*?)\supdated=[\'"](.*?)[\'"]/',$xml,$matches)
           ) {
            I2CE::raiseError("No entity found: '" . $xml . "' for $id");
            return false;
        }
        //echo "Caching:\n" . $xml . "\n";
        $last_modified = strtr($matches[3],'T','');
        if (($pos = strpos('+',$last_modified)) !== false) {
            $last_modifed = substr($last_modified,0,$pos);
        }
        $formid = $this->relObj->getPrimaryForm() . '|' . $id;
        if ( ($pos = strpos($last_modified,'+')) !== false) {
            $last_modified = substr($last_modified,0,$pos);
        }
        try {
            $this->insert_statement->execute(array($this->relationship,$this->transform,$formid,$last_modified,$xml));
        } catch ( PDOException $e ) {
            I2CE::pdoError($e, "Could not insert csd cache");
            return false;
        }
        return true;

    }


    /**
     * Returns mysql results set for given time
     * @returns false on failire
     */
    public function getCache($ids = false,$last_modified = -1) {
        $qry = "SELECT xml_entry from `csd_cache` WHERE `relationship` = ? and `transform` = ? ";
        $qry_args = array( $this->relationship, $this->transform );
        if (is_numeric($last_modified) && $last_modified >= 0) {
            $qry .=  "and `last_modified` >= ?";
            $qry_args[] = date('Y-m-d H:i:s' , $last_modified);
        } else if (is_string($last_modified) && $last_modified) {
            $qry .=  "and `last_modified` >= ?";
            $qry_args[] = date("Y-m-d H:i:s", strtotime($last_modified));
        }
        $db = I2CE::PDO();
        if (is_array($ids)) {
            $ids = array_map(function($id) {return $db->quote($id);},$ids);
            if (count($ids) > 0) {
                $qry .= 'and record IN (' . implode(' , ', $ids) . ') ';
            }
        }
        try {
            $results = $db->prepare($qry);
            $results->execute( $qry_args );
            return $results;
        } catch ( PDOException $e ) {
            I2CE::pdoError($e,"Cannot access cached results for $this->relationship $this->transform");
            return false; 
        }
    }

    public function streamRaw($ids = false, $last_modified = -1,$headers =false,$pre ='',$post='',$wrap  = true) {	
        $ff = I2CE_FormFactory::instance();
        $form_name = $this->relObj->getPrimaryForm();
        I2CE::raiseError("Primary form = $form_name");
        if (!is_array($ids)) {
            $ids =   $ff->getRecords(  $form_name,$last_modified);
        } else {
            $t_ids = $ids;
            $ids = array();
            foreach ($t_ids as $id) {
                list($form,$fid) = array_pad(explode("|",$id,2),2,'');
                if ($fid != '' and $fid != '0') {
                    $ids[] = $fid;
                }
            }
        }
        try {            
            $formRelationship = new I2CE_FormRelationship($this->relationship);
        } catch (Exception $e) {
            I2CE::raiseError("Could not create form relationship : " . $this->args['relationship']);
            return false;
        }
        $formRelationship->useRawFields();

        $formfields = array();        
        foreach ($formRelationship->getFormNames() as $formName) {
            if (! ($form = $formRelationship->getForm($formName))
                    || ! ($formObj= $ff->createContainer(array($form,0))) instanceof I2CE_Form
               ) {
                continue;
            }
            $formfields[$formName] = array();
            foreach ($formObj->getFieldNames() as $fieldName) {
                if (!($fieldObj = $formObj->getField($fieldName)) instanceof I2CE_FormField
                        || ! $fieldObj->isInDB()
                   ) {
                    continue;
                }
                $formfields[$formName][] = $fieldName;
            }

            $cachefields = array("created", "last_modified","parent");
            foreach ($cachefields as $cachedfieldName) {                
                $formfields[$formName][] = $cachedfieldName;
            }
        }
        I2CE::longExecution( array( "max_execution_time" => 1800 ) );
        $results  =$this->getCache($ids,$last_modified);
        if ( ($errors  = I2CE_Dumper::cleanlyEndOutputBuffers())) {
            I2CE::raiseError("Got errors:\n$errors");
        }
        if (array_key_exists('HTTP_HOST',$_SERVER)) {
            if (!is_array($headers)) {
                $headers = array ('Content-Type: text/xml');
            }
            foreach ($headers as $header) {
                header($header);
            }
        }
        echo "<?xml version='1.0'?>\n";
        flush();
        if ($pre) {
            echo $pre; 
            flush();
        }
        echo  "<!-- Contains " . count($ids) . " records -->\n"; 
        flush();
        if ($wrap) {
            echo "<relationshipCollection name='" .$formRelationship->getPrimaryFormName() ."'>\n";
            flush();
        }
        $rec =0;
        foreach ($ids as $id) {            
            I2CE::longExecution( array( "max_execution_time" => 1800 ) );
            $doc = new DOMDocument();
            $doc->loadXML("<relationship/>");
            $node = $doc->documentElement;            
            $formRelationship->getFormData($form_name,$id,$formfields,array(),$node);      
            echo  "<!-- Record #: " . $rec . "-->";
            echo $doc->saveXML($doc->documentElement) . "\n";
            flush();
            $rec++;
        }
        if ($wrap) {
            echo "</relationshipCollection>";
            flush();
        }
        if ($post) {
            echo $post; 
            flush();
        }
        flush();
        $results->closeCursor();
        unset( $results );
    }



    //hard-coding to person/csd_provider but should be more flexible
    public function streamCache($ids = false, $last_modified = -1,$headers =false,$pre ='',$post='',$wrap  = true) {	
        I2CE::longExecution( array( "max_execution_time" => 1800 ) );
        $results  =$this->getCache($ids,$last_modified);
        if ( ($errors  = I2CE_Dumper::cleanlyEndOutputBuffers())) {
            I2CE::raiseError("Got errors:\n$errors");
        }
        if (array_key_exists('HTTP_HOST',$_SERVER)) {
            if (!is_array($headers)) {
                $headers = array ('Content-Type: text/xml');
            }
            foreach ($headers as $header) {
                header($header);
            }
        }
        flush();
        if ($pre) {
            echo $pre; 
            flush();
        }
        if ($wrap) {
            echo '
                <csd:CSD xmlns:csd="urn:ihe:iti:csd:2013" >
                ';
        }
        foreach (array('organization','service','facility','provider') as $directory) {
            if ($wrap) {
                echo "<csd:" . $directory . "Directory>";
                flush();
            }
            if ($this->directory == $directory && $results) {
                while($result = $results->fetch()) {
                    I2CE::longExecution( array( "max_execution_time" => 1800 ) );
                    $entity = $result->xml_entry;;
                    echo $entity;
                    flush();
                }
                $results->closeCursor();
                unset( $results );
            }
            if ($wrap) {
                echo "</csd:" . $directory . "Directory>
                    ";
                flush();
            }
        }
        flush();
        if ($wrap) {
            echo '</csd:CSD>
                ';
        }
        if ($post) {
            echo $post;
        }
        flush();
        if ( $results ) {
            $results->closeCursor();
            unset( $results );
        }
    }




    public static function intialize_csd_cache_metadata($csd_cache,$lists=array(),$requested_oids = array(),$options = array()) {
        if (!I2CE_MagicDataNode::checkKey($csd_cache)
                || !is_array($lists)) {
            I2CE::raiseError("Badness on CSD cache metadata args");
            return false;
        }
        if (!is_array($requested_oids)) {
            $requested_oids = array();
        }
        if (!is_array($options)) {
            $options = array();
        }

        $config = I2CE::getConfig();
        if ( ! ($svs_config = $config->traverse("/modules/SVS/lists",true,false)) instanceof I2CE_MagicDataNode) {
            I2CE::raiseError("bad SVS root node");
            return false;
        }
        if (! (  $csd_cache_config =$config->traverse("/modules/csd_cache/" . $csd_cache . "/args",true,false)) instanceof I2CE_MagicDataNode)  {
            I2CE::raiseError("bad csd_cache config node");
            return false;
        }
        $site_module = 'ihris';
        $config->setIfIsSet($site_module,'/config/site/module');

        $source_uri = "http://localhost/" . $site_module;
        if (array_key_exists('HTTP_HOST',$_SERVER)) {
            $source_uri =I2CE_Page::getAccessedBaseURL();
        } 
        if ( !is_array($oids =     $csd_cache_config->getAsArray('svs_dependencies'))) {
            $oids = array();
        }
        foreach ($lists as $list) {
            if (array_key_exists($list,$oids)) {
                continue;
            }
            if (array_key_exists($list,$requested_oids)) {
                $oids[$list] = ($oid = $requested_oids[$list]);
            } else {
                $oids[$list] = (  $oid = iHRIS_CSDCache::generateUUIDasOID());
            }
            if (!I2CE_MagicDataNode::checkKey($oid)) {
                I2CE::raiseError("Invalid OID: $oid");
                return false;
            }            
            if (array_key_exists($list,$options)
                    && is_array($options[$list])
               ) {
                $opts= $options[$list];
            } else {
                $opts= array();
            }
            $styles = iHRIS_SVS::getAvailableStyles($list);
            if (array_key_exists('code_style',$opts)
                    && is_scalar($opts['code_style']) 
               ) {
                $code_style = $opts['code_style'];
            } else {
                $code_style ='code';
            }

            if (!in_array($code_style,$styles)) {
                $code_style = 'id';
            }
            $definition = array(
                    'list'=>$list,
                    'meta'=>array(
                        'Source'=>$site_module,
                        'SourceURI'=>$source_uri,
                        'code_style'=>$code_style,
                        'code_system'=>$oid
                        )
                    );
            if (! ($svs_list_config = $svs_config->traverse($oid,true,false)) instanceof I2CE_MagicDataNode) {
                I2CE::raiseError("Could not create MD for $oid at " . $svs_config->getPath());
                return false;
            }
            $svs_list_config->setValue($definition);
            try {
                $svs = new iHRIS_SVS($oid);
                $svs->publishConceptList();
            } catch (Exception $e) {            
                I2CE::raiseError("Could not create SVS list on OID: $oid");
                return false;
            }
        }
        $transform_vars = array(
                'sourceDirectory'=>$source_uri,
                'rootProviderURN'=>'urn:ihris.org:' . $site_module . ':providers',
                'rootFacilityURN'=>'urn:ihris.org:' . $site_module . ':facilities',
                'rootOrganizationURN'=>'urn:ihris.org:' . $site_module . ':organizations',
                'rootServicesURN'=>'urn:ihris.org:' . $site_module . ':services'
                );
        foreach ($lists as $list) {
            $transform_vars[$list . 'CodingScheme'] = $oids[$list];
        }
        $csd_cache_config->svs_dependencies = $oids;
        foreach ($transform_vars as $key=>$val) {
            I2CE::raiseError("Setting $key at " . $csd_cache_config->getPath());
            if (!$csd_cache_config->__isset("transform_vars/" .$key)) {
                $csd_cache_config->transform_vars->$key = $val;
            }
        }
        return true;
    }




}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
