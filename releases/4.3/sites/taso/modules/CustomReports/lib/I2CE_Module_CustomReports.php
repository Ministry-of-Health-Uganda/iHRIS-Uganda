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
*  I2CE_Module_CustomReports
* @package I2CE
* @subpackage Core
* @author Carl Leitner <litlfred@ibiblio.org>
* @version 2.1
* @access public
*/


class I2CE_Module_CustomReports extends I2CE_Module{


    public static function getHooks() {
        return array(
            'post_configure'=> 'refreshGenerates',
            'form_post_global_update'=>'globalUpdate_hook',
            'form_post_changeid'=>'globalUpdate_hook'
            );
    }


    /**
     * Return a list of the report views as an array.
     * For use with an ENUM field type.
     * @return array
     */
    public function listViews() {
        $views = array();
        foreach( I2CE::getConfig()->modules->CustomReports->reportViews as $key => $data ) {
            if ( $data instanceof I2CE_MagicDataNode ) {
                $views[$key] = $data->display_name;
            }
        }
        return $views;
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
        $reports = I2CE_CustomReport::getReports();
        foreach ($reports as $report) {
            try {
                $rep = new I2CE_CustomReport($report);
            }
            catch (Exception $e) {
                continue;
            }
            if (I2CE_CustomReport::getStatus($report) == 'not_generated') {
                continue;
            }
            $forms = $rep->getFormsRequiredByReport();
            if (!in_array($form,$forms)) {
                continue;
            }
            I2CE::raiseError("Dropping $report as it uses $form which has been changed in a global update");
            $rep->dropTable();
        }

    }

 


    public function refreshGenerates() {
        if (!array_key_exists('HTTP_HOST',$_SERVER)) {
            return; //don't do on command line
        }
        $config = I2CE::getConfig()->traverse("/modules/CustomReports/",true);
        $stale_time = 10; //stale time defaults to 10 minutes
        $config->setIfIsSet($stale_time,"times/background");
        if (!is_numeric($stale_time) || $stale_time <= 0)  {
            //launching of background pages is turned off
            return true;
        } 
        $stale_time = 60*((int)$stale_time);
        $last_generate = 0;
        $config->generate_all->volatile(true);
        $config->setIfIsSet($last_generate,"generate_all/time");
        if (($last_generate > 0) && ( (time() - $last_generate) <= $stale_time)){
            //we are not stale for the purposes of launching a background page
            return true;
        }


        $generate_status = '';
        $config->setIfIsSet($generate_status,"generate_all/status");
        if (  (($generate_status == 'starting') ||($generate_status == 'in_progress' )))  {
                //check to see if we have exceeded the fail time.
                $max_stale_time = 15;  //15 minutes
                $config->setIfIsSet($max_stale_time,"times/fail");
                if (!is_numeric($max_stale_time) || $max_stale_time < 0)  {
                    $max_stale_time = 15;
                }
                $max_stale_time = 60*$max_stale_time;//convert seconds to minutes.  
                if ( (time() - $last_generate) >  $max_stale_time) { 
                    //we have exceeded our max statle time.  lets force the generate
                    $config->generate_all->status = 'starting';
                    I2CE::raiseError("Exceeded max wait time for caching forms (status=$generate_status).  Forcing");
                    $config->generate_all->time = time(); //we do this so we dont spawn off a whole bunch of force processes
                    $this->launchBackgroundPage("/CustomReports/generate_force");        
                    return true;
                } else {
                    //we have not exceeded our max fail time
                    return true;
                }
        }
        //we need to update our generate.
        $config->generate_all->status = 'starting';
        $config->generate_all->time = time(); 
        $this->launchBackgroundPage("/CustomReports/generate");        
        return true;
    }

       

       
    public static function getMethods() {
        return  array(      
            'I2CE_Form->isNumeric'=>'isNumeric',
            'I2CE_FormField->isNumeric'=>'isNumericField'
            );
    }


    public function isNumeric($formObj,$field) {
        $fieldObj = $formObj->getField($field);
        if (!$fieldObj instanceof I2CE_FormField) {
            return false;
        }
        if ($fieldObj instanceof I2CE_FormField_MAPPED) {
            return false;  //assume that mapped fields are not numeric
        }
        return $fieldObj->isNumeric();
    }


    public function isNumericField($fieldObj) {
        $type= $fieldObj->getTypeString();
        switch($type) {
        case 'float':
        case 'decimal':
        case 'integer':
            return true;
            break;
        default:
            return false;
            break;
        }
    }

}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
