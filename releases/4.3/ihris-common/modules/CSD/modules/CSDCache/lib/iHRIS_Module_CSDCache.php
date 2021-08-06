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
* @subpackage CSDProviderCache
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.2.0
* @since v4.2.0
* @filesource 
*/ 
/** 
* Class iHRIS_Module_CSDProviderCache
* 
* @access public
*/


class iHRIS_Module_CSDCache extends I2CE_Module{


    /**
     * Method called when the module is enabled for the first time.
     * @param boolean -- returns true on success. false on error.
     */
    public function action_initialize() {
        I2CE::raiseError("Initializing Provider Cache Table");
        if (!I2CE_Util::runSQLScript('initialize_csd_cache.sql')) {
            I2CE::raiseError("Could not initialize I2CE form tables");
            return false;
        }
	return true;
    }
    
    public function upgrade( $old_vers, $new_vers ) {
        if ( I2CE_Validate::checkVersion( $old_vers, '<', '4.2.0.4' ) ) {
            if ( !$this->update_keys()) {
                return false;
            }
        }
        if ( I2CE_Validate::checkVersion( $old_vers, '<', '4.2.0.6' ) ) {
            if ( !$this->fixup_svs_oids()) {
                return false;
            }
        }
        return true;
    }


    protected function fixup_svs_oids() {
        if ( ! ($all_svs_config = I2CE::getConfig()->traverse("/modules/SVS/lists",true,false)) instanceof I2CE_MagicDataNode
             || ! (  $csd_config =I2CE::getConfig()->traverse("/modules/csd_cache/",false,false)) instanceof I2CE_MagicDataNode
            ) {
            I2CE::raiseError("no SVS lists or CSD caches defined");
            return true;
        }        
        $oids = $all_svs_config->getKeys();
        foreach ($oids as $oid) {
            if (!preg_match('/2\\.25[0-9]/',$oid)) {
                I2CE::raiseMessage("OID $oid looks OK.   Skipping");
                continue;
            }
            if (! ($svs_config = $all_svs_config->traverse($oid,false,false)) instanceof I2CE_MagicDataNode) {
                I2CE::raiseMessage("Could not traverse to SVS with OID $oid");
                continue;
            }
            $new_oid = '2.25.' .substr($oid,4);
            $definition = $svs_config->getAsArray();
            if (!is_array($definition)
                || array_key_exists('code_system',$definition)
                ) {
                I2CE::raiseMessage("No code system set for SVS list $oid");
                continue;
            }
            $defintion['code_system'] = $new_oid;
            if (! ($svs_list_config = $all_svs_config->traverse($new_oid,true,false)) instanceof I2CE_MagicDataNode) {
                I2CE::raiseMessage("Could not create MD for $new_oid at " . $all_svs_config->getPath());
                continue;
            }
            $svs_list_config->setValue($definition);
            try {
                $svs = new iHRIS_SVS($new_oid);
                $svs->publishConceptList();
            } catch (Exception $e) {            
                I2CE::raiseMessage("Could not create SVS list on OID: $oid");
                continue;
            }
            $svs_config->erase();
        }
        $csd_caches = $csd_config->getKeys();
        foreach ($csd_caches as $csd_cache) {
            I2CE::raiseMessage("Examining CSD Cache $csd_cache");
            if (! is_array($transform_vars = $csd_config->getAsArray($csd_cache  . '/args/transform_vars'))
                ||! ($transform_vars_config = $csd_config->traverse($csd_cache  . '/args/transform_vars',false,false)) instanceof I2CE_MagicDataNode
                ) {
                I2CE::raiseMessage("No csd cache transform vars for  $csd_cache");
                continue;
            }
            foreach ($transform_vars as $transform_var => $val) {
                if (!preg_match('/CodingScheme$/',$transform_var) 
                    || !preg_match('/2\\.25[0-9]/',$val)
                    ) {
                    I2CE::raiseMessage("Skipping $transform_var => $val");
                    continue;
                }
                $new_oid = '2.25.' .substr($val,4);
                I2CE::raiseError("Changing $transform_var from $val to $new_oid");
                $transform_vars_config->$transform_var = $new_oid;
            }
        }
        return true;
    }


    protected function update_keys() {
        $db = I2CE::PDO();
        try {
            $qry = 'TRUNCATE TABLE csd_cache';
            $db->exec($qry);
        } catch ( PDOException $e ) {
            I2CE::pdoError( $e, "Error truncatingo csd_cache" );
            return false;
        }
        try {
            $qry = 'ALTER TABLE csd_cache ADD   UNIQUE  KEY `unique` (`record`,`relationship`,`transform`)';
            $db->exec($qry);
        } catch ( PDOException $e ) {
            I2CE::pdoError( $e, "Error adding unique key to csd_cache" );
            return false;
        }
        return true;
    }


    public static function set_uuid_on_form($form,$field = 'csd_uuid') {
        if (!$form instanceof I2CE_Form) {
            I2CE::raiseError("Bad form");
            return;
        }
        if ( $form->getID() == '0'   || $form->getID() == '' ) {
            return;
        }
        if ( !($field_obj = $form->getField($field)) instanceof I2CE_FormField  ) {
            I2CE::raiseError("$field does not exist on " . $form->getNameID());
            return;
        }
        if (!($field_obj->getDBValue() == '')) {
            return;
        }

        if (($id  = self::generate_uuid($form))) {
            I2CE::raiseMessage("Setting csd uuid for ". $form->getNameID());
            $field_obj->setFromDB($id);
        }
    }


    const NAMESPACE_UUID = "13F73A40-5E46-4DB7-A8C9-DDAF2DAA71AB";


    public static function generate_uuid($form) {
        if (!$form instanceof I2CE_Form ){
            return false;
        }
        $site_module = 'ihris';
        I2CE::getConfig()->setIfIsSet($site_module,'/config/site/module');
        $name = $site_module . ':'. $form->getNameID();
        return iHRIS_Module_UUID_Map::v3(self::NAMESPACE_UUID,$name);
    }
    

    public static function add_uuids($form,$field = 'csd_uuid') {
        I2CE::raiseError("ADDING UUIDS to $form on $field");
        try {
            //we probably don't need this, but 
            $cache = new I2CE_CachedForm($form);
            $cache->dropTable();
        } catch (Exception $e) {
            I2CE::raiseError("Could not clear cache");
            return false;
        }
        $ff = I2CE_FormFactory::instance();
        $user =new I2CE_User();
        $forms = I2CE_FormStorage::listFields($form,array($field));
        foreach ($forms as $id=>$fields) {
            if (is_array($fields)
                && array_key_exists('csd_uuid',$fields)
                &&  $fields['csd_uuid']
                ) {
                continue;
            }
            if (! ($form_obj = $ff->createContainer(array($form,$id))) instanceof I2CE_Form) {
                I2CE::raiseError("Could not instantiate $form|$id");
                return false;
            } 
            $form_obj->populate();
            self::set_uuid_on_form($form_obj);
            $form_obj->save($user);
            $form_obj->cleanup();
        }
        try {
            //we probably don't need this, but 
            $cache = new I2CE_CachedForm($form);
            $cache->dropTable();
            $cache->generateCachedTable();
        } catch (Exception $e) {
            I2CE::raiseError("Could not clear cache");
            return false;
        }
        return true;

    }

    protected static $saves =array();
    public static function form_pre_save($data) {
        if (is_array($data) 
            && array_key_exists('form',$data)
            && ($form = $data['form']) instanceof I2CE_Form
            &&  ! array_key_exists($id = $form->getNameID(),self::$saves)
            ) {
            self::$saves[$form->getNameID()] = true;
        }
    }

    public static function form_post_save($data) {
        if (is_array($data) 
            && array_key_exists('form',$data)
            && ($form = $data['form']) instanceof I2CE_Form
            && array_key_exists($id = $form->getNameID(),self::$saves)
            &&  self::$saves[$id]
            ) {
            self::$saves[$id] = false;
            $form->populate(); //in-case someone clear the data on us
            self::set_uuid_on_form($form);
            $form->save(new I2CE_User());
        }
    }






}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
