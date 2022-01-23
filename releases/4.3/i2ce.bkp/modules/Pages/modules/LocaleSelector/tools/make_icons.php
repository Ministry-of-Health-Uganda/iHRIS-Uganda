#!/usr/bin/php
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

echo "  <configuration name='icons' path='/config/site/locales/icons' values='many' type='delimited'>\n";
$dir_path = '..'. DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'flags' . DIRECTORY_SEPARATOR;
if (!is_dir($dir_path)) {
    die("Not a direcotory");
}
foreach (glob(preg_replace('/(\*|\?|\[)/', '[$1]', $dir_path).'*.png') as $file) {
    $icon = basename($file);
    $lang = basename($icon , '.png');
    echo "      <value>$lang:flags/$icon</value>\n";
}
echo "  </configuration>\n";
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
