<?php
/**
 * Created by JetBrains PhpStorm.
 * User: root
 * Date: 3/19/13
 * Time: 9:50 AM
 * To change this template use File | Settings | File Templates.
 */
class iHRIS_PageFormDataElement extends I2CE_PageForm
{
    protected function loadObjects() {
        $factory = I2CE_FormFactory::instance();
        if ($this->isPost()) {
            $dataelement = $factory->createContainer('dataelement');
            if (!$dataelement instanceof iHRIS_DataElement) {
                I2CE::raiseError("Could not create Data Element form");
                return;
            }
            $dataelement->load($this->post);
        } else {
            if ($this->get_exists('id')) {
                $id = $this->get('id');
                if (strpos($id,'|')=== false) {
                    I2CE::raiseError("Depcreated use of id variable");
                    $id = 'dataelement|' . $id;
                }
            } else {
                $id = 'dataelement|0';
            }
            $dataelement = $factory->createContainer($id);
            if (!$dataelement instanceof iHRIS_DataElement) {
                I2CE::raiseError("Could not create valid Data Element form from id:$id");
                return;
            }
            $dataelement->populate();
            $dataelement->load($this->request());
        }
        $this->setObject( $dataelement);
    }

    protected function save() {
        parent::save();



        $fields = $this->post['forms']['dataelement'][0][0]['fields'];
        $dataElementName = $fields['name'];
        $dataElementShortName = $fields['shortname'];
        $dataElementCode = $fields['dataelement_code'];
        $today = date("F d, Y");
	
	$url = false;
        if (! I2CE::getConfig()->setIfIsSet($url , "/modules/DHIS-Dashboard/urls/data_sync")
            || !$url) {
            I2CE::raiseError("Bad url for data sync");
            return false;
        }



        $curl_parameters = array(
            'DataElementName' => $dataElementName,
            'OrganisationUnitName' => "default",
            'AggValue' => "4",
            'PeriodStart' => $today,
            'PeriodTypeString' => 'Monthly'
        );

	$username = false;
	$password = false;
        if (!I2CE::getConfig()->setIfIsSet($username,"/modules/DHIS-Dashboard/credentials/username") 
            || !$username) {
            I2CE::raiseError("No username set");
            return false;
        }
        if (!I2CE::getConfig()->setIfIsSet($password,"/modules/DHIS-Dashboard/credentials/password") 
            || !$username) {
            I2CE::raiseError("No password set");
            return false;
        }


        $ch = curl_init();

        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch, CURLOPT_USERPWD,"$username:$password");
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

        curl_setopt($ch,CURLOPT_POST,1);
        curl_setopt($ch,CURLOPT_POSTFIELDS,http_build_query( $curl_parameters ));

        $content = curl_exec ($ch);
        $info = curl_getinfo($ch);

        if ($content === false || $info['http_code'] != 200) {
            $output = "\nNo cURL data returned for $url [". $info['http_code']. "]";
            if (curl_error($ch))
                $output .= "\n". curl_error($ch);
            echo $output;

        }
        curl_close($ch);
        $this->setRedirect(  "view_list?type=dataelement&id=" . $this->getPrimary()->getNameId() );
    }

    protected  function displayControls( $save = false, $show_edit = true ) {
        if ( $save ) {
            parent::displayControls( $save, $show_edit );
        }  else {
            $this->template->addFile( 'button_confirm_notchild.html' );
        }
    }

}
