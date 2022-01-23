<?php
/**
 * @copyright © 2007, 2008, 2009 Intrahealth International, Inc.
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
 * The plural forms for various languages according to http://translate.sourceforge.net/wiki/l10n/pluralforms#plural_forms
 * 
 * @package I2CE
 * @subpackage Common
 * @access public
 * @author Frederick Leitner <litlfred@ibiblio.org>
 * @since v2.0.0
 * @version v2.0.0
 */

/**
 * The page class for displaying the home page.
 * @package I2CE
 * @subpackage Common
 * @access public
 */
class I2CE_PluralForms extends I2CE_Fuzzy {
 

    /**
     * Given a locale/launguage and a integer, evaluates the plural form
     * @returns mixed. false on failure, int the plural form used on success
     */
    public function getPluralForm($lang,$n) {
        if (!is_string($lang)) {
            return false;
        }
        if (!is_numeric($n)) {
            return false;
        }
        $method  = 'getPluralForm_' . $lang;
        if (! $this->_hasMethod($method)) {
            if ( ($pos = strpos('_',$lang)) !== false) {
                $method  = 'getPluralForm_' . substr($lang,0,$pos);
                if (! $this->_hasMethod($method)) {
                    if ( ($pos = strpos('-',$lang)) !== false) {
                        $method  = 'getPluralForm_' . substr($lang,0,$pos);
                        if (! $this->_hasMethod($method)) {
                            return false;
                        }
                    }
                }
            }
        } 
        return $this->$method($n);
    }


    public function getPluralForm_af($n) {
        $plural = (int) (($n != 1));
        if ($plural > 2 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_am($n) {
        $plural = (int) (($n > 1));
        if ($plural > 2 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_ar($n) {
        $plural = (int) ( $n==0 ? 0 : $n==1 ? 1 : $n==2 ? 2 : $n%100>=3
&& $n%100<=10 ? 3 : $n%100>=11 && $n%100<=99 ? 4
: 5);
        if ($plural > 6 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_arn($n) {
        $plural = (int) (($n > 1));
        if ($plural > 2 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_az($n) {
        $plural = (int) (($n != 1));
        if ($plural > 2 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_be($n) {
        $plural = (int) (($n%10==1 && $n%100!=11 ? 0 : $n%10>=2
&& $n%10<=4 && ($n%100<10 or $n%100>=20) ? 1 :
2));
        if ($plural > 3 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_bg($n) {
        $plural = (int) (($n != 1));
        if ($plural > 2 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_bn($n) {
        $plural = (int) (($n != 1));
        if ($plural > 2 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_bo($n) {
        $plural = (int) (0);
        if ($plural > 1 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_bs($n) {
        $plural = (int) (($n%10==1 && $n%100!=11 ? 0 : $n%10>=2
&& $n%10<=4 && ($n%100<10 or $n%100>=20) ? 1 :
2));
        if ($plural > 3 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_ca($n) {
        $plural = (int) (($n != 1));
        if ($plural > 2 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_cs($n) {
        $plural = (int) (($n==1) ? 0 : ($n>=2 && $n<=4) ? 1 : 2);
        if ($plural > 3 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_cy($n) {
        $plural = (int) ( ($n==1) ? 0 : ($n==2) ? 1 : ($n != 8 && $n != 11) ? 2 : 3);
        if ($plural > 4 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_da($n) {
        $plural = (int) (($n != 1));
        if ($plural > 2 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_de($n) {
        $plural = (int) (($n != 1));
        if ($plural > 2 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_dz($n) {
        $plural = (int) (0);
        if ($plural > 1 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_el($n) {
        $plural = (int) (($n != 1));
        if ($plural > 2 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_en($n) {
        $plural = (int) (($n != 1));
        if ($plural > 2 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_eo($n) {
        $plural = (int) (($n != 1));
        if ($plural > 2 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_es($n) {
        $plural = (int) (($n != 1));
        if ($plural > 2 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_es_AR($n) {
        $plural = (int) (($n != 1));
        if ($plural > 2 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_et($n) {
        $plural = (int) (($n != 1));
        if ($plural > 2 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_eu($n) {
        $plural = (int) (($n != 1));
        if ($plural > 2 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_fa($n) {
        $plural = (int) (0);
        if ($plural > 1 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_fi($n) {
        $plural = (int) (($n != 1));
        if ($plural > 2 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_fil($n) {
        $plural = (int) ($n > 1);
        if ($plural > 2 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_fo($n) {
        $plural = (int) (($n != 1));
        if ($plural > 2 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_fr($n) {
        $plural = (int) (($n > 1));
        if ($plural > 2 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_fur($n) {
        $plural = (int) (($n != 1));
        if ($plural > 2 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_fy($n) {
        $plural = (int) (($n != 1));
        if ($plural > 2 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_ga($n) {
        $plural = (int) ($n==1 ? 0 : $n==2 ? 1 : $n<7 ? 2 : $n<11 ? 3 : 4);
        if ($plural > 5 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_gl($n) {
        $plural = (int) (($n != 1));
        if ($plural > 2 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_gu($n) {
        $plural = (int) (($n != 1));
        if ($plural > 2 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_gun($n) {
        $plural = (int) ( ($n > 1));
        if ($plural > 1 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_ha($n) {
        $plural = (int) (($n != 1));
        if ($plural > 2 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_he($n) {
        $plural = (int) (($n != 1));
        if ($plural > 2 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_hi($n) {
        $plural = (int) (($n != 1));
        if ($plural > 2 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_hy($n) {
        $plural = (int) (0);
        if ($plural > 1 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_hr($n) {
        $plural = (int) (($n%10==1 && $n%100!=11 ? 0 : $n%10>=2
&& $n%10<=4 && ($n%100<10 or $n%100>=20) ? 1 :
2));
        if ($plural > 3 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_hu($n) {
        $plural = (int) (0);
        if ($plural > 1 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_id($n) {
        $plural = (int) (0);
        if ($plural > 1 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_is($n) {
        $plural = (int) (($n != 1));
        if ($plural > 2 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_it($n) {
        $plural = (int) (($n != 1));
        if ($plural > 2 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_ja($n) {
        $plural = (int) (0);
        if ($plural > 1 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_jv($n) {
        $plural = (int) ($n!=0);
        if ($plural > 2 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_ka($n) {
        $plural = (int) (0);
        if ($plural > 1 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_km($n) {
        $plural = (int) (0);
        if ($plural > 1 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_kn($n) {
        $plural = (int) (0);
        if ($plural > 1 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_ko($n) {
        $plural = (int) (0);
        if ($plural > 1 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_ku($n) {
        $plural = (int) (($n!= 1));
        if ($plural > 2 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_ky($n) {
        $plural = (int) (0);
        if ($plural > 1 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_lb($n) {
        $plural = (int) (($n != 1));
        if ($plural > 2 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_ln($n) {
        $plural = (int) ($n>1);
        if ($plural > 2 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_lt($n) {
        $plural = (int) (($n%10==1 && $n%100!=11 ? 0 : $n%10>=2 && ($n%100<10 or $n%100>=20) ? 1 : 2));
        if ($plural > 3 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_lv($n) {
        $plural = (int) (($n%10==1 && $n%100!=11 ? 0 : $n != 0 ? 1 : 2));
        if ($plural > 3 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_mk($n) {
        $plural = (int) ( $n==1 or $n%10==1 ? 0 : 1);
        if ($plural > 3 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_mg($n) {
        $plural = (int) (($n > 1));
        if ($plural > 2 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_mi($n) {
        $plural = (int) (($n > 1));
        if ($plural > 2 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_ml($n) {
        $plural = (int) (($n != 1));
        if ($plural > 2 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_ms($n) {
        $plural = (int) (0);
        if ($plural > 1 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_mt($n) {
        $plural = (int) (($n==1 ? 0 : $n==0 or ( $n%100>1 &&
$n%100<11) ? 1 : ($n%100>10 && $n%100<20 ) ? 2 : 3));
        if ($plural > 4 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_mr($n) {
        $plural = (int) (($n != 1));
        if ($plural > 2 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_mn($n) {
        $plural = (int) (($n != 1));
        if ($plural > 2 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_nah($n) {
        $plural = (int) (($n != 1));
        if ($plural > 2 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_nb($n) {
        $plural = (int) (($n != 1));
        if ($plural > 2 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_ne($n) {
        $plural = (int) (($n != 1));
        if ($plural > 2 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_nl($n) {
        $plural = (int) (($n != 1));
        if ($plural > 2 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_nn($n) {
        $plural = (int) (($n != 1));
        if ($plural > 2 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_no($n) {
        $plural = (int) (($n != 1));
        if ($plural > 2 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_nso($n) {
        $plural = (int) (($n > 1));
        if ($plural > 2 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_or($n) {
        $plural = (int) (($n != 1));
        if ($plural > 2 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_pa($n) {
        $plural = (int) (($n != 1));
        if ($plural > 2 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_pap($n) {
        $plural = (int) (($n != 1));
        if ($plural > 2 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_pl($n) {
        $plural = (int) (($n==1 ? 0 : $n%10>=2 && $n%10<=4 && ($n%100<10 or $n%100>=20) ? 1 : 2));
        if ($plural > 3 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_pt($n) {
        $plural = (int) (($n != 1));
        if ($plural > 2 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_pt_BR($n) {
        $plural = (int) (($n > 1));
        if ($plural > 2 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_ro($n) {
        $plural = (int) (($n==1 ? 0 : ($n==0 or ($n%100 > 0 && $n%100 < 20)) ? 1 : 2));
        if ($plural > 3 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_ru($n) {
        $plural = (int) (($n%10==1 && $n%100!=11 ? 0 : $n%10>=2
&& $n%10<=4 && ($n%100<10 or $n%100>=20) ? 1 :
2));
        if ($plural > 3 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_sco($n) {
        $plural = (int) (($n != 1));
        if ($plural > 2 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_sk($n) {
        $plural = (int) (($n==1) ? 0 : ($n>=2 && $n<=4) ? 1 : 2);
        if ($plural > 3 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_sl($n) {
        $plural = (int) (($n%100==1 ? 0 : $n%100==2 ? 1 : $n%100==3 or $n%100==4 ? 2 : 3));
        if ($plural > 4 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_so($n) {
        $plural = (int) ($n != 1);
        if ($plural > 2 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_sq($n) {
        $plural = (int) (($n != 1));
        if ($plural > 2 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_sr($n) {
        $plural = (int) ($n==1? 3 : $n%10==1 && $n%100!=11 ? 0 :
$n%10>=2 && $n%10<=4 && ($n%100<10 or
$n%100>=20) ? 1 : 2);
        if ($plural > 4 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_su($n) {
        $plural = (int) (0);
        if ($plural > 1 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_sv($n) {
        $plural = (int) (($n != 1));
        if ($plural > 2 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_ta($n) {
        $plural = (int) (($n != 1));
        if ($plural > 2 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_te($n) {
        $plural = (int) (($n != 1));
        if ($plural > 2 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_tg($n) {
        $plural = (int) (($n != 1));
        if ($plural > 2 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_ti($n) {
        $plural = (int) ($n > 1);
        if ($plural > 2 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_th($n) {
        $plural = (int) (0);
        if ($plural > 1 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_tk($n) {
        $plural = (int) (($n != 1));
        if ($plural > 2 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_tr($n) {
        $plural = (int) (0);
        if ($plural > 1 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_uk($n) {
        $plural = (int) (($n%10==1 && $n%100!=11 ? 0 : $n%10>=2
&& $n%10<=4 && ($n%100<10 or $n%100>=20) ? 1 :
2));
        if ($plural > 3 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_ur($n) {
        $plural = (int) (($n != 1));
        if ($plural > 2 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_uz($n) {
        $plural = (int) (0);
        if ($plural > 1 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

    public function getPluralForm_vi($n) {
        $plural = (int) (0);
        if ($plural > 1 || $plural < 0) {
            return false;
        } else { 
            return $plural;
        }
    }

}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
