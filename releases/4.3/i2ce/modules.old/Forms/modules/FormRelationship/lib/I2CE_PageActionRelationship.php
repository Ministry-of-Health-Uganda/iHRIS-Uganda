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
 * @package I2CE
 * @author Carl Leitner <litlfred@ibiblio.org>
 * @since v1.0.0
 * @version v4.0.0
 */

if (version_compare(PHP_VERSION, '5.3.0') >= 0) {
    require_once('classDefs/I2CE_PageActionRelationship.5.3.php');
} else {
    require_once('classDefs/I2CE_PageActionRelationship.5.2.php');
}