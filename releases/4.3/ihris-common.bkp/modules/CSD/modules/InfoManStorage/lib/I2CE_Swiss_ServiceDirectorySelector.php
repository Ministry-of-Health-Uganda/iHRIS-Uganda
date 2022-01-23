<?php
/**
 * @copyright © 2008, 2009 Intrahealth International, Inc.
 * This File is part of I2CE
 *
 * I2CE is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by
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
*/
/**
*  I2CE_SwissConfig_FormRelationship_Joins
* @package I2CE
* @subpackage Core
* @author Carl Leitner <litlfred@ibiblio.org>
* @version 2.1
* @access public
*/


class I2CE_Swiss_ServiceDirectorySelector extends I2CE_Swiss {

    protected static $svs_forms = array(
        'csd_address_type'
        ,'csd_address_components'
        ,'csd_facility_status'
        ,'csd_provider_status'
        ,'csd_organization_status'
        ,'csd_service_status'
        ,'csd_provider_gender'
        ,'csd_contact_point_type'
        ,'csd_provider_type'
        ,'csd_language'
        ,'csd_provider_credential_type'
        ,'csd_facility_type'
        ,'csd_provider_specialty'
        ,'csd_service_type'
        ,'csd_organization_type'
        );	  

    /**
     * Update config for given values
     * @param array $vals.  An array of values (presumably from $_POST) to update magic data from
     * @returns  true on sucess
     */
    public function processValues($vals) {
        $changed = false;
        foreach (array('provider','service','facility','organization') as $directory) {
            if (array_key_exists($directory,$vals)) {
                $t_changed = $this->getField($directory) != $vals[$directory];
                $changed |= $t_changed;
                if ($t_changed) {
                    I2CE::raiseError("Setting remote CSD $directory to $vals[$directory]");
                    $this->setField($directory,$vals[$directory]);
                }
            }
        }
        I2CE::raiseError(print_r($vals,true));
        if (array_key_exists('url',$vals)
            && ($url = $vals['url'])
            && ($url_md = I2CE::getConfig()->traverse( '/modules/forms/storage_options/SVS/url',true,false)) instanceof I2CE_MagicDataNode
            ){
            $url_md->setValue($url);
        }
        $forms_md  = I2CE::getConfig()->traverse('/modules/forms/forms/',false,false);;
        $sets = array();
        foreach (self::$svs_forms as $svs_form) {
            if (!array_key_exists($svs_form,$vals)
                || ! ($new_id = $vals[$svs_form])
                || ! ($form_md  = $forms_md->traverse($svs_form . '/storage_options/SVS/request_args/ID',true,false)) instanceof I2CE_MagicDataNode
                ) {
                continue;
            }
            $sets[] = "$svs_form: $new_id ";
            $old_id =false;
            $form_md->getValue();
            $changed |= ($old_id != $new_id);
            $form_md->setValue($new_id);
        }
        I2CE::raiseError("Set:\n" . implode(', ', $sets));

        if ($changed) {
            I2CE::raiseError("Dropping all cached forms");
            $forms = I2CE_FormFactory::instance()->getNames();
            $failure = array();
            $config =   I2CE::getConfig()->modules->CachedForms;
            if ($config instanceof I2CE_MagicDataNode) {
                $config->cache_all->erase(); 
            }
            foreach ($forms as $form) {
                try {
                    $cachedForm = new I2CE_CachedForm($form);
                }
                catch(Exception $e) {
                    if (array_key_exists('HTTP_HOST',$_SERVER)) { //we don't need to check here, b/c it won't error out.  we are doing it to keep the log file clean
                        $this->page->userMessage ( "Unable to setup cached form $form");
                    }
                    continue;
                }
                $cachedForm->dropTable();
            }
            $this->page->userMessage ( "Dropped Cache For All Forms");
                
        }
        return true;
    }




    public function displayValues($content_node,$transient_options, $action) {
        $mainNode = $this->template->appendFileByNode('service_directory_selector.html','div',$content_node);        
        if (!$mainNode instanceof DOMNode) {
            return false;
        }
        $directories = array('provider','service','facility','organization');
        foreach ( $directories as $directory) {
            $where = array(
                'operator'=>'FIELD_LIMIT',
                'field'=>'is_' . $directory,
                'style'=>'true'
                );
            if (! ($selectNode= $this->template->getElementByName($directory,0,$mainNode)) instanceof DOMElement) {
                continue;
            }
            $avail_dirs = I2CE_FormStorage::listDisplayFields('csd_info_manager',array('name'),false,$where);
            $selected = $this->getField($directory);
            foreach($avail_dirs as $id=>$data) {
                if (!is_array($data)
                    || !array_key_exists('name',$data)) {
                    continue;
                }
                $id = 'csd_info_manager|' .$id;
                if ($id != $selected) {
                    $selectNode->appendChild($this->template->createElement('option',array('value'=>$id),$data['name']));
                } else{                    
                    $selectNode->appendChild($this->template->createElement('option',array('value'=>$id,'selected'=>'selected'),$data['name']));
                }
            }
        }
        $forms_md  = I2CE::getConfig()->traverse('/modules/forms/forms/',false,false);;
        I2CE::getConfig()->setIfIsSet($base_url , '/modules/forms/storage_options/SVS/url');
        $this->template->setDisplayDataImmediate('url',$base_url,$content_node);
        foreach (self::$svs_forms as $svs_form) {
            $id = false;
            $forms_md->setIfIsSet($id,$svs_form . '/storage_options/SVS/request_args/ID');
            $this->template->setDisplayDataImmediate($svs_form,$id, $content_node);
            $this->template->setDisplayDataImmediate('link_'. $svs_form,$base_url . '?id=' . $id, $content_node);
        }

        $this->renameInputs(array_merge($directories,self::$svs_forms,array('url')),$mainNode);
        return true;
    }

    
  }
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
