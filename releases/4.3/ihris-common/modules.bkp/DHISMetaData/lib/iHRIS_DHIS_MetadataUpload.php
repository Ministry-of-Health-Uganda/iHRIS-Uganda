<?php
/**
* © Copyright 2013 IntraHealth International, Inc.
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
* @package ihris-common
* @subpackage dhis
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.1.6
* @since v4.1.6
* @filesource 
*/ 
/** 
* Class iHRIS_DHIS_DashboardSubmit
* 
* @access public
*/


class iHRIS_DHIS_MetadataUpload extends  I2CE_Page {

    protected static $md_path = "/modules/DHIS_Metadata/export.xml";

    protected function action() {
        if (! ($node = $this->template->getElementById('siteContent')) instanceof DOMNode) {
            return false;
        }
        $this->template->appendFileByNode("dhis_metadata_upload.html","div",$node);
        if ($this->isPost() && ! $this->processUpload()) {
            $this->template->appendFileByID("dhis_metadata_error.html","div",'error_messages');
        }
        if (!  is_string($export = I2CE::getConfig()->traverse(self::$md_path))) {
            $export = '';
        }
        $this->template->setDisplayDataImmediate('dhis_export_filesize',strlen($export));
    }


    protected function processUpload() {
        if (! ($mdn = I2CE::getConfig()->traverse(self::$md_path,true,false)) instanceof I2CE_MagicDataNode
            || $mdn->is_parent()
            || !array_key_exists('dhisexport',$_FILES)
            || ! (array_key_exists('error',$_FILES['dhisexport']) && count($_FILES['dhisexport']['error']) > 0)
            || ($_FILES["dhisexport"]["size"] == 0) 
            || !array_key_exists('tmp_name',$_FILES['dhisexport'])
            || !is_readable($file = $_FILES["dhisexport"]["tmp_name"])
            ) {
            I2CE::raiseError("Could not access save DHIS export to " . self::$md_path);
            return false;
        }        
        $mdn->setAttribute('binary',1);
        I2CE::raiseError("Setting dhis export at ". $mdn->getPath());
        $mdn->setValue(file_get_contents($file));        
        $this->launchBackgroundPage("CachedForms/dropAndCacheForce",array('--post=profile=dhis_metadata'));
        return true;
    }

}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
