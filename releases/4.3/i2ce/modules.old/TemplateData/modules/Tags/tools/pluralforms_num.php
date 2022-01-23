<?php
/**
 * @copyright © 2009 Intrahealth International, Inc.
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
echo "    array(\n        ";
$count = 0;
foreach ($forms as $lang=>$data) {
    $count ++;
    if ($count !== count($forms)) {
        echo " '$lang'=>" . $data['nplurals']  . ", ";
        if ($count % 10 == 0) {
            echo "\n        ";
        }
    } else {
        echo " '$lang'=>" . $data['nplurals']  . "\n";
    }
} 

echo "    );\n";
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
