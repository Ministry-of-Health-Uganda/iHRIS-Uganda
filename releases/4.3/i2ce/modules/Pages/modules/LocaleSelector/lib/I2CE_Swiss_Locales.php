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
*  I2CE_Swiss_Locales
* @package I2CE
* @subpackage Core
* @author Carl Leitner <litlfred@ibiblio.org>
* @version 2.1
* @access public
*/


class I2CE_Swiss_Locales extends I2CE_Swiss {

    public function getChildType($name) {        
        return 'Locale';
    }

    public function displayValues($contentNode, $transient_options,$action) {
        if ( ! ($mainNode = $this->template->appendFileByNode('site_locale_base_' . $action . '.html','div',$contentNode)) instanceof DOMNode) {
            return false;
        }
        $default = I2CE_Locales::DEFAULT_LOCALE;
        $this->template->setDisplayDataImmediate('locale_default', $default, $mainNode);
        $this->template->setDisplayDataImmediate('locale_default_name', I2CE_Module_LocaleSelector::getLocaleName($default), $mainNode);
        if ($action === 'edit') {
            if (!$this->addNewLocaleMenu($contentNode,$transient_options)) {
                return false;
            }
        }
        if ( ! ($appendNode = $this->template->getElementById('locale_list', $mainNode)) instanceof DOMNode) {
            return false;
        }
        
        $locales = I2CE_Locales::validateLocales($this->getChildNames());        
        foreach ($locales as $locale) {
            $swissLocale = $this->getChild($locale,true);
            if (!$swissLocale  instanceof I2CE_Swiss_Locale) {
                continue;
            }
            if (!($localeNode = $this->template->appendFileByNode('site_locale_each.html', 'li', $appendNode)) instanceof DOMNode) {
                return false;
            }
            $swissLocale->addAjaxLink('locale_link','locale_container',  'locale_ajax' ,$localeNode,$action, $transient_options);
        }
        return true;
    }


    protected function addNewLocaleMenu($contentNode,$transient_options) {
        $locales = I2CE_Locales::validateLocales($this->getChildNames());
        $def_locales = array(); //the locales packaged with iHRIS by default
        I2CE::getConfig()->setIfIsSet($def_locales,"/locales/default-available",true);
        $def_locale_list = array();
        foreach ($def_locales as $locale=>$locale_lang) {
            if (in_array($locale_lang,$locales)) {
                continue;
            }
            $disp =I2CE_Module_LocaleSelector::getLocaleName($locale_lang);
            if ($disp != $locale_lang) {
                $disp .=   ' (' . $locale_lang . ')';
            }
            $def_locale_list[$locale_lang] = $disp;
        }
        if (count($def_locale_list) > 0) {
            $this->template->setDisplayDataImmediate('has_def_locale',1,$contentNode);
            $this->template->setDisplayDataImmediate('def_locale',$def_locale_list,$contentNode);
            $this->addAjaxOptionMenu('add_def_locale','locales_container', $contentNode);
        } else {
            $this->template->setDisplayDataImmediate('has_def_locale',0,$localeNode);
        }

        if ( !($localeNode = $this->template->getElementById('new_locale',$contentNode)) instanceof DOMNode) {            
            return false;
        }
        $this->template->setClassValue($localeNode,'validate_data',array('notinlist'=>array_unique(array_merge($def_locales,$locales))), '%');
        return $this->addAjaxOptionMenu('add_locale','locales_container', $contentNode);
    }

    public function processValues($vals) {
        $desired_locales = array();
        if (array_key_exists('def_locale',$vals)) {
            $desired_locales[] = $vals['def_locale'];
        }
        if (array_key_exists('new_locale',$vals))  {
            $desired_locales[] = $vals['new_locale'];
        }
        $success =  true;
        foreach ($desired_locales as $locale) {
            if (!$locale) {
                continue;
            }
            if (!preg_match('/^(\w+)_(\w+)$/',$locale, $matches) ) {
                $msg = "Invalid Locale specified";
                $this->userMessage($msg,'notice',false);             
                I2CE::raiseError($msg);
                $success = false;
                continue;
            }
            $locale = strtolower($matches[1]) . '_' . strtoupper($matches[2]);
            $locales = I2CE_Locales::validateLocales($this->getChildNames());
            if (!$locale) {
                $msg = "Invalid Locale specified";
                $this->userMessage($msg,'notice',false); 
                I2CE::raiseError($msg);
                $success = false;
                continue;
            }
            if (in_array($locale,$locales)) {
                $msg = "Locale $locale is already used";
                $this->userMessage($msg,'notice',false); 
                I2CE::raiseError($msg);
                continue;
            }
            $child = $this->getChild($locale,true);        
            if (!$child instanceof I2CE_Swiss_Locale) {
                $msg = "Could not add locale $locale";
                $this->userMessage($msg,'notice',false); 
                $success = false;
                continue;
            }
        }
        return $success;
    }


}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
