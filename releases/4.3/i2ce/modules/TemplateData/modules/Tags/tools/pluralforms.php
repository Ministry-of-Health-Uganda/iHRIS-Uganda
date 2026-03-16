<?php


// reads in table from http://translate.sourceforge.net/wiki/l10n/pluralforms#plural_forms 

$dom = new DOMDocument();
$dom->loadHTMLFile('pluralforms.html');
$xpath = new DOMXPath($dom);
$results = $xpath->query('//table/tbody/tr');
if (count($results) != 1) {
    die('no results' . "\n");
}

$forms = array();
for ($i = 0; $i < $results->length; $i++) {
    $row = $results->item($i);
    $cells = $xpath->query('./td',$row);
    if ($cells->length == 0) {
        fwrite(STDERR, "No cell on row $i\n");
        continue;
    }
    if ($cells->length !== 3) {
        fwrite(STDERR, "Unpxected number of cells " . $cells->length ." on row $i\n\t" . $row->textContent . "\n");
        continue;
    }
    $lang = trim($cells->item(0)->textContent);
    list($nplurals,$eq) = explode(';',trim($cells->item(2)->textContent));
    if (!preg_match('/^\s*nplurals\s*=\s*(\d+)\s*$/',$nplurals,$matches)) {
        fwrite(STDERR, "Could not find nplurals for $lang:\n\t" . $cells->item(2)->textContent . "\n");
        continue;
    } else {
        $nplurals = $matches[1];
    }
    $forms[$lang] = array('nplurals'=>$nplurals, 'equation'=>$eq);
}
//print_r($forms);
echo '<?php
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
 * @package iHRIS
 * @subpackage Common
 * @access public
 * @author Frederick Leitner <litlfred@ibiblio.org>
 * @since v2.0.0
 * @version v2.0.0
 */

/**
 * The page class for displaying the home page.
 * @package iHRIS
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
        $method  = "getPluralForm_" . $lang;
        if (! $this->_hasMethod($method)) {
            if ( ($pos = strpos("_",$lang)) !== false) {
                $method  = "getPluralForm_" . substr($lang,0,$pos);
                if (! $this->_hasMethod($method)) {
                    if ( ($pos = strpos("-",$lang)) !== false) {
                        $method  = "getPluralForm_" . substr($lang,0,$pos);
                        if (! $this->_hasMethod($method)) {
                            return false;
                        }
                    }
                }
            }
        } 
        return $this->$method($n);
    }

   

';

foreach ($forms as $lang=>$data) {
    echo "
    public function getPluralForm_$lang(\$n) {\n";
    $eq = explode('=',trim($data['equation']),2);
    $eq = $eq[1];
    $eq = preg_replace('/n/','$n',$eq);
    //make sure there is no spacing in our operators
    $eq = preg_replace('/<\s+=/','<=',$eq);
    $eq = preg_replace('/>\s+=/','>=',$eq);
    $eq = preg_replace('/!\s+=/','!=',$eq);
    echo "        \$plural = (int) ($eq);\n";
    echo "        if (\$plural > {$data['nplurals']} || \$plural < 0) {
            return false;
        } else { 
            return \$plural;
        }
    }
";
}


echo "\n}\n";





# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
