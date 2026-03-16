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
*  I2CE_Module_CachedForms
* @package I2CE
* @subpackage Core
* @author Carl Leitner <litlfred@ibiblio.org>
* @version 2.1
* @access public
*/


class I2CE_Module_CachedForms  extends I2CE_Module{
    
    public static function getMethods() {
        return array(
	    'I2CE_FormField->cachedTableReference'=>'cachedTableReference'
            );

    }


    
    public static function getCLIMethods() {
        return array(
	    'I2CE_FormField->cachedTableReference'=>'cachedTableReference'
            );

    }

    public static function getHooks() {
        return array(
            'post_configure'=> 'refreshCaches',
            'form_post_save'=>'markFormDirty_hook',
            "form_post_delete"=>'dropTable_hook',

            'form_post_global_update'=>'globalUpdate_hook',
            'form_post_changeid'=>'globalUpdate_hook'
            );
    }


    /**
     * Hooked method to marks a form as dirty (needs to be cached).
     * @param mixed $args.   an array of two elements whose first element is a form object, the second is the user
     */
    public function  globalUpdate_hook($args) {
        if (!is_array($args)   ||  !array_key_exists('form',$args)) {
            return;
        }
        $form = $args['form'];
        if ($form instanceof I2CE_Form) {
            $form = $form->getName();
        }
        if (!is_string($form)) {
            return;
        }
        try {
            $cache = new I2CE_CachedForm($form);
        }
        catch (Exception $e) {
            return ;
        }
        $cache->dropTable();

    }



    /**
     * Hooked method to drop a cached table
     * @param mixed $args.   an array of two elements whose first element is a form object, the second is the user
     */
    public function  dropTable_hook($args) {
        if (!is_array($args)   ||  !array_key_exists('form',$args)) {
            return;
        }
        $form = $args['form'];
        if ($form instanceof I2CE_Form) {
            $form = $form->getName();
        }
        if (!is_string($form)) {
            return;
        }
        try {
            $cache = new I2CE_CachedForm($form);
        }
        catch (Exception $e) {
            return ;
        }
        $cache->dropTable();
    }

    /**
     * Hooked method to marks a form as dirty (needs to be cached).
     * @param mixed $args.   an array of two elements whose first element is a form object, the second is the user
     */
    public function  markFormDirty_hook($args) {
        if (!is_array($args)   ||  !array_key_exists('form',$args)) {
            return;
        }
        $form = $args['form'];
        if (!$form instanceof I2CE_Form) {
            return;
        }
        $form_name = $form->getName();
        try { 
            $cache = new I2CE_CachedForm( $form_name );
            if ( !$cache->updateCachedTable( $form->getID() ) ) {
                self::markFormDirty($form_name);
            }
        } catch ( Exception $e ) {
            self::markFormDirty($form_name);
        }
    }

    /**
     * Marks a form as dirty (needs to be cached).
     * @param mixed $form.  Either a form object, the name of a form 
     * @param int $timestamp.  The timestamp to mark the cleanliness of the form.  If not set (default) then we use now.
     */
    public static function  markFormDirty($form, $timestamp =null) {
        if (!is_int($timestamp)) {
            $timestamp = time();
        }
        if ($form instanceof I2CE_Form) {
            $form = $form->getName();
        }
        if (!is_string($form) || strlen($form) == 0) {
            return;
        }
        $path = '/modules/CachedForms/dirty';
        $dirty_node = I2CE::getConfig()->traverse($path,true,false);
        if (!$dirty_node instanceof I2CE_MagicDataNode || $dirty_node->is_scalar()) {
            I2CE::raiseError("Bad magic data at $path");
            return ;
        }        
        $dirty_node->volatile(true);
        //need to make sure the existing dirty time is not more than the one we are trying to set it at
        $dirty_time = 0;
        $dirty_node->setIfIsSet($dirty_time,$form);        
        $dirty_time = max($timestamp,$dirty_time);
        $dirty_node->$form = $dirty_time;
    }

    /**
     * Marks a form as clean (does not need to be cached) if the dirty time does not exceed the given time stamp
     * @param mixed $args.  Either a form object, or the name of a form
     * @param int $timestamp.  The timestamp to try and mark the cleanliness of the form.  If not set (default) then we use now.
     */
    public static function  markFormClean($args, $timestamp = null) {
        if (!is_int($timestamp)) {
            $timestamp = time();
        }
        $form = false;
        if ($args instanceof I2CE_Form) {
            $form = $args->getName();
        } else {
            $form = $args;
        }
        if (!is_string($args) || strlen($args) == 0) {
            return;
        }
        $path = '/modules/CachedForms/dirty';
        $dirty_node = I2CE::getConfig()->traverse($path,true,false);
        if (!$dirty_node instanceof I2CE_MagicDataNode || $dirty_node->is_scalar()) {
            I2CE::raiseError("Bad magic data at $path");
            return ;
        }        
        $dirty_node->volatile(true);
        $dirty_time = 0;
        $dirty_node->setIfIsSet($dirty_time,$form);        
        if (($dirty_time > 0) && ($dirty_time > $timestamp)) {
            //it was marked dirty after the time we are trying to set it to be clean, so we can't mark it clean
            return;
        }
        $dirty_node->$form = -1;  //we can mark the form as clean.
    }

    /**
     * Checks to see if a form as dirty (needs to be cached)
     * @param mixed $form.  Either a form object or a strgin.
     * @returns boolean
     */
    public static function formIsDirty($form) {
        if ($form instanceof I2CE_Form) {
            $form = $form->getName();
        } 
        if (!is_string($form) || strlen($form) == 0) {
            return false;
        }
        $path = '/modules/CachedForms/dirty';
        $dirty_node = I2CE::getConfig()->traverse($path,true,false);
        if (!$dirty_node instanceof I2CE_MagicDataNode || $dirty_node->is_scalar()) {
            I2CE::raiseError("Bad magic data at $path");
            return false;
        }        
        $dirty_node->volatile(true);
        $dirty_time = 0;
        $dirty_node->setIfIsSet($dirty_time,$form);         
        return ($dirty_time >= 0);  //dirrty_time = -1 is clean. 
        //note, a form will be considered dirty if it was never marked clean. this way we don't have to worry about new forms or the module upgrade process
    }


    public static function cachedTableReference($fieldObj) {
        return '`' . $fieldObj->getContainer()->getName() . '`.`' . $fieldObj->getName() . '`';
    }
    
    public function refreshCaches() {
        $config = I2CE::getConfig()->traverse("/modules/CachedForms",true);
        $stale_time = 10; //stale time defaults to 10 minutes
        $config->setIfIsSet($stale_time,"times/background_time");
        if (!is_numeric($stale_time) || $stale_time <= 0)  {
            //launching of background pages is turned off
            return true;
        } 
        $stale_time = 60*((int)$stale_time);
        $last_cache = 0;
        $config->cache_all->volatile(true);
        $config->setIfIsSet($last_cache,"cache_all/time");
        if (($last_cache > 0) && ( (time() - $last_cache) <= $stale_time)){
            //we are not stale for the purposes of launching a background page
            return true;
        }



        $cache_status = '';
        $config->setIfIsSet($cache_status,"cache_all/status");
        if (  ($cache_status != 'done')) {
            //check to see if we have exceeded the fail time.
            $max_stale_time = 15;  //15 minutes
            $config->setIfIsSet($max_stale_time,"times/fail_time");
            if (!is_numeric($max_stale_time) || $max_stale_time < 0)  {
                $max_stale_time = 15;
            }
            $max_stale_time = 60*((int)$max_stale_time);//convert seconds to minutes.  
            if ( (time() - $last_cache) <=  $max_stale_time) { 
                //we have not exceeded our max fail time
                return true;
            }
            //we have exceeded our max statle time.  lets force the cache
            $config->cache_all->status = 'starting';
            I2CE::raiseError("Exceeded max wait time for caching forms (status=$cache_status:$last_cache:$max_stale_time).  Forcing");
            $config->cache_all->time = time(); //we do this so we dont spawn off a whole bunch of force processes
            $this->launchBackgroundPage("/CachedForms/cacheAllForce");        
            return true;
        }
        //if we made it here, we need to update our cache.
        $config->cache_all->status = 'starting';
        $config->cache_all->time = time(); 
        $this->launchBackgroundPage("/CachedForms/cacheAll");        
    }



}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
