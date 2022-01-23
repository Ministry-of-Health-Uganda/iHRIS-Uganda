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
*  I2CE_Module_LocaleSelector
* @package I2CE
* @subpackage Core
* @author Carl Leitner <litlfred@ibiblio.org>
* @version 2.1
* @access public
*/


class I2CE_Module_LocaleSelector extends I2CE_Module {


    public function showSelectableLocales($node,$template,$update_link = null, $selected = null) {
        $locales = I2CE_Locales::getSelectableLocales();        
        if ($update_link == null) {
            $update_link = 'localeSelector/userPreferred?redirect=' . urlencode($_SERVER['REQUEST_URI']) . '&locale=';
        }
        if (count(array_diff($locales,array(I2CE_Locales::DEFAULT_LOCALE))) == 0) { //do nothing.
            return;
        }
        $this->localeMenu($node,$template,$locales,$update_link, $selected);
    }

    public function showAvailableLocales($node,$template,$update_link_base, $selected = null ) {
        $this->localeMenu($node,$template,I2CE_Locales::getAvailableLocales(),$update_link_base, $selected);
    }


    public function hasSelectableLocales() {
        return ( count(I2CE_Locales::getSelectableLocales() ) > 1) ;
    }

    public static function getLocaleName($locale) {
        $trans = I2CE::getConfig()->traverse("/locales/languages",true,false);
        if (!$trans instanceof I2CE_MagicDataNode) {
            return $locale;
        }        
        if (($pos = strpos($locale,'_'))!== false) {
            $lang = substr($locale,0,$pos);
            $region = substr($locale,$pos+1);
        } else {
            $lang = $locale;
            $region = false;
        }
        $resolution = I2CE_Locales::getLocaleResolution($locale);        
        $t_lang = false;
        foreach ($resolution as $loc) {
            if ($trans->is_translated($loc,$locale)) {
                return  $trans->getTranslation($loc,false,$locale); 
            }                       
            if ($trans->is_translated($loc,$lang)) {
                $t_lang = $trans->getTranslation($loc,false,$lang); 
                if ($region) {
                    $t_lang .= ' (' . $region . ')';
                }
                return $t_lang;
            }           
        }
        if ($trans->is_scalar($locale)) {
            return $trans->$locale;
        }
        if (!$trans->setIfIsSet($t_lang, $lang)) {
            $t_lang =  $lang;
        }
        return  $t_lang;
    }

    protected function localeMenu($node,$template,$locales,$update_link_base, $selected = null) {
        $iconConfig = I2CE::getConfig()->traverse("/locales/icons",true,false);
        if (!$iconConfig instanceof I2CE_MagicDataNode) {
            return false;
        }
        if ($selected === null) {
            $selected = I2CE_Locales::getPreferredLocale();
        }
        if (($pos = strpos($selected,'_'))!== false) {
            $selected_lang = substr($selected,0,$pos);
        } else {
            $selected_lang = $selected;
        }
        foreach ($locales as $locale) {
            $name = $this->getLocaleName($locale);
            
            if (($pos = strpos($locale,'_'))!== false) {
                $lang = substr($locale,0,$pos);
                $region = strtolower(substr($locale,$pos+1));
            } else {
                $lang = $locale;
                $region = $locale;
            }


            $icon = false;
            foreach (array($locale, $region) as $key) {
                if ($iconConfig->setIfIsSet($icon,$key)) {
                    break;
                }
            }
            if (is_string($icon) && strlen(trim($icon)) > 0) {
                $icon = array('src'=>'file/' . trim($icon), 'alt_text'=>$name);
            } else {
                $icon = false;
            }
            if ($icon) {
                $choice_file = 'language_choice_icon.html';
            } else {
                $choice_file = 'language_choice.html';
            }
            $choiceNode = $template->appendFileByNode($choice_file,'//body/*',$node);
            if (!$choiceNode instanceof DOMNode) {
                continue;
            }
            if ($selected == $locale) {
                $name = '['  . $name .']';
            }
            $template->setDisplayDataImmediate('language_choice_text',$name,$choiceNode);
            $template->setDisplayDataImmediate('language_choice_link',$update_link_base . $locale,$choiceNode);
            if ($icon) {
                $template->setDisplayDataImmediate('language_choice_icon',$icon,$choiceNode);
            }
        }
    }




  }


# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
