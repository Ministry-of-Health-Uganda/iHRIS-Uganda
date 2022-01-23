<?php 
/**
 * © Copyright 2008, 2009 IntraHealth International, Inc.
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
 */
/**
 *  iHRIS_Module_ManageCSD
 * @package iHRIS
 * @subpackage Manage
 * @author Carl Leitner <litlfred@ibibilio.org>
 * @version 4.2.0.9
 * @since 4.2.0
 * @access public
 */


class iHRIS_Module_CSDGeography extends I2CE_Module {


    public function post_update( $old_vers, $new_vers ) {
        if ( I2CE_Validate::checkVersion( $old_vers, '<', '4.2.0.3' ) ) {
            if ( !$this->intialize_csd_cache_metadata()) {
                return false;
            }
        }
        if ( I2CE_Validate::checkVersion( $old_vers, '<', '4.2.0.12' ) ) {
            if ( !$this->update_uuids()) {
                return false;
            }
        }
        return true;
    }


    
    protected function update_uuids() {
        foreach (array('country','region','district','county') as $form) {
            if (! (iHRIS_Module_CSDCache::add_uuids($form))) {
                return false;
            }
        }
        return true;
    }

    /**
     * Return the array of hooks available in this module.
     * @return array
     */
    public static function getHooks() {
        return array(
            'form_pre_save_country' => 'form_pre_save',
            'form_pre_save_region' => 'form_pre_save',
            'form_pre_save_district' => 'form_pre_save',
            'form_post_save_country' => 'form_post_save',
            'form_post_save_region' => 'form_post_save',
            'form_post_save_district' => 'form_post_save',
            'form_post_save_county' => 'form_post_save'
            );
    }


    public function form_pre_save($data) {
        iHRIS_Module_CSDCache::form_pre_save($data);
    }
    public function form_post_save($data) {
        iHRIS_Module_CSDCache::form_post_save($data);
    }



    public function intialize_csd_cache_metadata() {
        $cache_names = array('csd_county_default','csd_country_default','csd_region_default','csd_district_default');
        foreach ($cache_names as $cache_name) {
            if (! ( iHRIS_CSDCache::intialize_csd_cache_metadata($cache_name))) {
                return false;
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
