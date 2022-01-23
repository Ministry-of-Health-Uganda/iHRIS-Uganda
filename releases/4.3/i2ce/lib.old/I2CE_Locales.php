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
* @package I2CE
* @subpackage Core
* @author Carl Leitner <litlfred@ibiblio.org>
* @version 2.1
* @access public
*/


/**
 * pull in dependencies
 */
require_once 'I2CE.php';
require_once 'I2CE_ModuleFactory.php';

/**
 * I2CE_Locales
 * @package I2CE
 * @todo Better Documentation
 */
class I2CE_Locales {

    /**
     * The 'defualt' locale for I2CE.   You should not need to change this unless you are doing something crazy.
     * @var string DEFAULT_LOCALE
     */
    const DEFAULT_LOCALE =  'en_US';

    
    /**
     *Get all locales that are either selectable or referenced in the reosltuion
     *of a selectable locale
     *@returns array of string, the locales
     */
    public static function getAvailableLocales() {
        $selectable = self::getSelectableLocales();
        $locales = array();
        foreach ($selectable as $locale) {
            $locales = array_unique(array_merge($locales , self::getLocaleResolution($locale)));
        }
        $locales = I2CE_Locales::validateLocales($locales);
        return $locales;
    }


    /**
     * Get the preffered  locales in order of decreasing preference
     * @returns array of string.  The prefferend locales e.g.  array('fr_FR','en_GB','en_US')
     */
    public static function getPreferredLocales() {
        if (!self::isSetPreferredLocales()) {
            return array(self::DEFAULT_LOCALE);            
        } else {
            return $_SESSION['preferred_locales'];
        }
    }


    /**
     * Get a list of the selectable locales for the site.. i.e. the locales that we know something about.
     * This should not be a big list.
     * @returns array of string
     */
    public static function getSelectableLocales() {
        return self::validateLocales(I2CE::getConfig()->getKeys("/locales/selectable"));
    }

    public static function ensureSelectableLocale($locale,$fallback_site_preferred = true) {
        $selectable = self::getSelectableLocales();
        if (!is_string($locale) || !in_array($locale,$selectable)) {
            if ($fallback_site_preferred) {
                I2CE::getConfig()->setIfIsSet($locale,"/locales/preferred_locale");
                if (!is_string($locale) || !in_array($locale,$selectable)) {
                    $locale = self::DEFAULT_LOCALE;
                }
            } else {
                $locale = self::DEFAULT_LOCALE;
            }
        }
        return $locale;
    }



    public static function isSetPreferredLocale($locale = null) {
        if ($locale === null) {
            return (is_array($_SESSION) && array_key_exists('preferred_locale',$_SESSION) && $_SESSION['preferred_locale']);
        } else {
            return (is_array($_SESSION) && array_key_exists('preferred_locale',$_SESSION) && $locale == $_SESSION['preferred_locale']);
        }
    }


    protected static $request_locale;

    /**
     * Set the preferred locale.  Checks against the selectable locales
     * @param string $locale
     */
    public static function setPreferredLocale($locale) {
        if (is_string($locale) && self::isSetPreferredLocale($locale) && $locale == self::$request_locale) {
            //we already know that the preffered locale is set to to be this locale, so do nothing.
            return;
        }
        self::$request_locale = self::ensureSelectableLocale($locale);
        $_SESSION['preferred_locale'] = self::$request_locale;
        self::setPreferredLocales(self::getLocaleResolution($locale));
    }


    /**
     * Get the preferred locale.  Checks against the selectable locales
     * @returns string $locale
     */
    public static function getPreferredLocale() {
        $locale = self::DEFAULT_LOCALE;
        if (self::isSetPreferredLocale()) {
            $locale = $_SESSION['preferred_locale'];
        } else {
            if (!I2CE::getConfig()->setIfIsSet($locale,"/locale/site_preferred")) {
                $locale = self::getBrowserPreferredLocale();
            }
        }
        return self::ensureSelectableLocale($locale);
    }



    /**
     * Validates and sets the preferred locales for the session
     * in order of decreasing preference.  It makes sure that self::DEFAULT_LOCALE is in the list
     * of preferred locales.
     * @param mixed @locales. string or array of  string.  The preferred locale or an array of prefered locales.
     * @returns array of string, the locales that were set.
     */
    protected static function setPreferredLocales($locales) {
        $old_locales = self::getPreferredLocales();
        $changed = false;
        if (!array_key_exists('preferred_locales',$_SESSION) || !is_array($_SESSION['preferred_locales'])) {
            $changed = true;
        } else {
            if (count($_SESSION['preferred_locales']) != count($locales)) {
                $changed = true;
            } else {
                foreach ($locales as $i=>$locale) {
                    if ($locale != $_SESSION['preferred_locales'][$i]) {
                        $changed = true;
                        break;
                    }
                }
            }
        }
        I2CE::getConfig()->setLocales($locales);
        if ($changed) {
            $_SESSION['preferred_locales'] = $locales;
            I2CE_ModuleFactory::callHooks('locales_changed',array('old_locales'=>$old_locales,'locales'=>$locales));
        }
        return $_SESSION['preferred_locales'];
    }



    protected static function isSetPreferredLocales() {
        return (array_key_exists('_SESSION', $GLOBALS) &&
                is_array($_SESSION) &&
                array_key_exists('preferred_locales',$_SESSION) &&
                is_array($_SESSION['preferred_locales']));
    }


    public static function validateLocales($locales) {
        if (is_string($locales)) {
            $locales = array($locales);
        }
        if (!is_array($locales)) {
            return array(self::DEFAULT_LOCALE);
        }
        if (!in_array(self::DEFAULT_LOCALE,$locales)) {
            $locales[] = self::DEFAULT_LOCALE;
        }
        return $locales;
    }


    protected static $preferred_locale;

    public static function getSitePreferredLocale() {
        if (!self::$preferred_locale) {
            I2CE::getConfig()->setIfIsSet($locale, "/locales/preferred_locale");
            self::$preferred_locale = self::ensureSelectableLocale($locale);
        }
        return self::$preferred_locale;
    }

    public static function setSitePreferredLocale($locale) {
        $locale = self::ensureSelectableLocale($locale,false);
        self::$preferred_locale = $locale;
        I2CE::getConfig()->__set("/locales/preferred_locale",$locale);
        return $locale;
    }



    public static function getBrowserPreferredLocale() {
        $locales = self::getBrowserPrefferedLocales();
        $selectable = self::getSelectableLocales();
        $selectable_langs = array();
        foreach ($selectable as $locale) {
            if ( ($pos = strpos($locale,'_')) !== false) {
                $lang = substr($locale,0,$pos);
            } else {
                $lang = $locale;
            }
            if (!array_key_exists($lang,$selectable_langs)) {
                $seletable_langs[$lang] = $locale;
            }
        }
        $lang_locale_found = false;
        foreach ($locales as $locale) {
            if ( ($pos = strpos($locale,'_')) !== false) {
                $lang = substr($locale,0,$pos);
            } else {
                $lang = $locale;
            }
            if (in_array($locale,$selectable)) {
                return $locale;
            }
            if ($lang_locale_found === false ){
                if (array_key_exists($lang,$selectable_langs)) {
                    $lang_locale_found = $selectable_langs[$lang];
                }
            }            
        }
        if ($lang_locale_found !== false) {
            return $lang_locale_found;
        }
        return self::DEFAULT_LOCALE;
    }

    /**
     *Get the locales that were requested by the browser in order of decreasing preferrence.
     * @param boolean $validate. Defaults to true
     *@returns array of string
     */
    public static function getBrowserPrefferedLocales() {
        if (!array_key_exists('HTTP_ACCEPT_LANGUAGE', $_SERVER)) {
            return array();
        }
        $browser_locales = explode(",",$_SERVER["HTTP_ACCEPT_LANGUAGE"]);
        $quotients = array();
        foreach ($browser_locales as $i=>$locale) {
            $loc = false;
            $quot = false;
            list($loc,$qout) = strpos($locale,';');
            if ($quot === false) {
                $qout = 1;
            }
            $quotients[$qout][]= $loc;
        }
        krsort($quotients, SORT_NUMERIC);
        $locales = array();
        foreach ($quotients as $q=>$locs) {
            foreach ($locs as $locale) {
                $locale = self::mapLanguageToLocale($locale);
                if ($locale) {
                    $locales[] = $locale;
                }
            }
        }
        $locales = self::validateLocales($locales);
        return $locales;
    }

    /**
     * attempt to map a language to a locale based on the selectable locales
     * @returns mixed. false on failure. string on success
     */
    public static function mapLanguageToLocale($locale) {
        if (!is_string($locale) || strlen($locale) == 0) {
            return false;
        }
        $locale = strtr($locale,'-','_');
        if (strpos($locale,'_') !== false) {
            return $locale;
        }
        $locale_len = strlen($locale);
        $selectable = self::getSelectableLocales();
        foreach ($selectable as $l) {
            if (substr($l,0,$locale_len) === $locale) {
                return $l;
            }
        }
        return false;
    }

    
    /**
     * Gets the resolution of search paths for a locale
     * @param string $locale
     * @returns array of string;
     */
    public static function getLocaleResolution($locale , $ensure_selectable = false) {
        if (!is_string($locale) || strlen($locale) == 0) {
            $locale = 'en_US';
        }
        if ($ensure_selectable) {
            $locale = self::ensureSelectable($locale);
        }
        $locales = array($locale);
        if (($pos = strpos($locale,'_'))!== false) {
            $locales[]  = substr($locale,0,$pos);
        }
        I2CE::getConfig()->setIfIsSet($locales,"/locales/selectable/$locale/resolution",true);
        return  self::ensureValidResolution($locale,$locales);
        return $locales;
    }

    public static function ensureValidResolution($locale, $resolution) {
        $resolution = self::validateLocales($resolution);
        if (count($resolution) == 0 || $resolution[0] !== $locale) {
            array_unshift($resolution,$locale);
        }
        return $resolution;
    }
    

}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
