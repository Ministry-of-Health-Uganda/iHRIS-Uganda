<?php
/**
* © Copyright 2010 IntraHealth International, Inc.
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
* @package I2CE
* @subpackage I2CE
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.0.5
* @since v4.0.5
* @filesource 
*/ 
/** 
* Class I2CE_Module_PrintedForms
* 
* @access public
*/


class I2CE_Module_PrintedForms extends I2CE_Module {



    /**
     * @var protected array $valid of array.  An array indexed by form names containing the valid printed forms whose primary form is the key
     */
    protected static $valid = array();
    /**
     * @var protected array $archive of array.  An array indexed by form names containing the valid printed forms whose archive is the key
     */
    protected static $archive = array();
    /**
     * Get the valid standardized letters for the given form
     * @param string $form
     * @returns array of string, the keys are the valid letters, the values are the displayname
     */
    public static function getValidPrintedForms($form) {
        if (!array_key_exists($form,self::$valid)) {
            $valid = array();        
            $pfsConfig = I2CE::getConfig()->traverse("/modules/PrintedForms/forms",true,false);
            $relConfig = I2CE::getConfig()->traverse("/modules/CustomReports/relationships",true,false);
            foreach ($pfsConfig as $pf => $pfConfig) {
                $rel = '';
                if (!$pfConfig instanceof I2CE_MagicDataNode) {
                    continue;
                }
                if (!$pfConfig->setIfIsSet($rel,"relationship") || !is_string($rel) || strlen($rel) == 0) {
                    continue;
                }
                if (!$relConfig->is_parent($rel) || !$relConfig->is_scalar("$rel/form")) {
                    continue;
                }
                if ($relConfig->$rel->form != $form) {
                continue;
                }
                $dn = $pf;
                $pfConfig->setIfIsSet($dn,"displayName");
                $archive = false;
                $pfConfig->setIfIsSet($archive,"archive");
                $valid[$pf] = array('displayName'=>$dn,'archive'=>$archive);
            }
            self::$valid[$form] = $valid;
        }
        return self::$valid[$form];
    }
    /**
     * Get the valid archived letters for the given form
     * @param string $form
     * @returns array of string, the keys are the valid letters, the values are the displayname
     */
    public static function getValidArchivedForms($form) {
        if (!array_key_exists($form,self::$archive)) {
            $archives = array();        
            $pfsConfig = I2CE::getConfig()->traverse("/modules/PrintedForms/forms",true,false);
            foreach ($pfsConfig as $pf => $pfConfig) {
                $archive = '';
                if (!$pfConfig->setIfIsSet($archive,"archive") || $archive != $form) {
                    continue;
                }
                $dn = $pf;
                $pfConfig->setIfIsSet($dn,"displayName");
                $archives[$pf] = array('displayName'=>$dn,'archive'=>$archive);
            }
            self::$archive[$form] = $archives;
        }
        return self::$archive[$form];
    }


    /**
     * Checks to see if there are  valid standardized or archived letters for the given form
     * @param string $form
     * @returns boolean
     */
    public static function hasValidForms($form) {
        return (count(self::getValidPrintedForms($form)) + count(self::getValidArchivedForms($form))) > 0 ;
    }


    /**
     * Checks to see if there are  valid standardized letters for the given form
     * @param string $form
     * @returns boolean
     */
    public static function hasValidPrintedForms($form) {
        return (count(self::getValidPrintedForms($form)) > 0) ;
    }

    /**
     * Checks to see if there are  valid archived letters for the given form
     * @param string $form
     * @returns boolean
     */
    public static function hasValidArchviedForms($form) {
        return (count(self::getValidArchivedForms($form)) > 0) ;
    }





}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
