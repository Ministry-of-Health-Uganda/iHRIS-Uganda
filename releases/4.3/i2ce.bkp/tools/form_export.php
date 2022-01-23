<?php
/*
 * © Copyright 2007, 2008 IntraHealth International, Inc.
 * 
 * This File is part of iHRIS
 * 
 * iHRIS is free software; you can redistribute it and/or modify
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
 */
/**
 * This page can be placed in any site pages directory and then be run to output
 * a list of commands that can be run to export all the magic data stored forms
 * in the system.
 *
 * @package iHRIS
 * @subpackage I2CE
 * @access public
 * @author Luke Duncan <lduncan@intrahealth.org>
 * @copyright Copyright &copy; 2007, 2008, 2009 IntraHealth International, Inc. 
 * @since 3.2.0
 * @version 3.2.0
 */


$i2ce_site_user_database = null;
require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR . 'config.values.php');

$local_config = dirname(__FILE__) . DIRECTORY_SEPARATOR .
'local' . DIRECTORY_SEPARATOR . 'config.values.php';
if (file_exists($local_config)) {
    require_once($local_config);
}

if(!isset($i2ce_site_i2ce_path) || !is_dir($i2ce_site_i2ce_path)) {
    echo "Please set the \$i2ce_site_i2ce_path in $local_config";
    exit(55);
}

require_once ($i2ce_site_i2ce_path . DIRECTORY_SEPARATOR . 'I2CE_config.inc.php');
@I2CE::initialize($i2ce_site_database_user,
                 $i2ce_site_database_password,
                 $i2ce_site_database,
                 $i2ce_site_user_database,
                 $i2ce_site_module_config         
    );

unset($i2ce_site_user_database);
unset($i2ce_site_database);
unset($i2ce_site_database_user);
unset($i2ce_site_database_password);
unset($i2ce_site_i2ce_path);
unset($i2ce_site_module_config);


$data_path = "/I2CE/formsData/forms";

$form_config = I2CE::getConfig()->traverse( $data_path );

foreach( $form_config as $form => $data ) {
    $display_name = null;
    I2CE::getConfig()->setIfIsSet( $display_name, "/modules/forms/forms/$form/display" );
    echo "php index.php --page=/magicDataExport/export --post='name=SampleData-$form&displayName=$display_name&version=3.2.0&config_path=/I2CE/formsData/forms/$form&description=Sample Data for form: $form' > SampleData-$form.xml\n";
}

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
