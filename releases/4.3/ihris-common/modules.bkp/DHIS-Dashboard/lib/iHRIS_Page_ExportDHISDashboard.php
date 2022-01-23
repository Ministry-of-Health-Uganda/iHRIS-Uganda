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
 *  I2CE_Page_ReportRelationship
 * @package I2CE
 * @subpackage Core
 * @author Carl Leitner <litlfred@ibiblio.org>
 * @version 2.1
 * @access public
 */


class iHRIS_Page_ExportDHISDashboard extends I2CE_Page {

    protected function action() {
        /*
         * Get org unit hierarchy from query and store these information in table
         *
         * */

//      Fetch last time stored magic data if it is created
        $lastreporttime = '2000-1-1 00:00:00';
        I2CE::getConfig()->setIfIsSet( $lastreporttime, "/modules/DHIS-Dashboard/Time/SendTime" );

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


        $last_update= date('y-m-d h:m:s');
        //      echo $last_update;

        $orgQuery = "select asd.row,asd.id,asd.name,asd.parent,asd1.row,asd.last_modified as parentid from

                (select (@row:=@row+1) as row,id,name,parent,last_modified from hippo_country,
                                (SELECT @row:=0) AS row_count
                                union
                                select (@row:=@row+1) as row,hr.id,hr.name,hr.country,hr.last_modified from hippo_region hr
                                inner join hippo_country hc on hc.id=hr.country,
                                (SELECT @row:=0) AS row_count
                                union
                                select (@row:=@row+1) as row,hd.id,hd.name,hd.region,hd.last_modified from hippo_district hd
                                inner join hippo_region hr on hd.region=hr.id,
                                (SELECT @row:=0) AS row_count
                                union
                                select (@row:=@row+1) as row,co.id,co.name,co.district,co.last_modified from hippo_county co
                                inner join hippo_district hd on co.district=hd.id,
                                (SELECT @row:=0) AS row_count
                                union
                                select (@row:=@row+1) as row,hf.id,hf.name,hf.location,hf.last_modified from hippo_facility hf,
                                (SELECT @row:=0) AS row_count
                                where location is not null
                                )asd

                                left join

                (select (@row1:=@row1+1) as row,id,name,parent from hippo_country,
                                (SELECT @row1:=0) AS row_count
                                union
                                select (@row1:=@row1+1) as row,hr.id,hr.name,hr.country from hippo_region hr
                                inner join hippo_country hc on hc.id=hr.country,
                                (SELECT @row1:=0) AS row_count
                                union
                                select (@row1:=@row1+1) as row,hd.id,hd.name,hd.region from hippo_district hd
                                inner join hippo_region hr on hd.region=hr.id,
                                (SELECT @row1:=0) AS row_count
                                union
                                select (@row1:=@row1+1) as row,co.id,co.name,co.district from hippo_county co
                                inner join hippo_district hd on co.district=hd.id,
                                (SELECT @row1:=0) AS row_count
                                union
                                select (@row:=@row+1) as row,hf.id,hf.name,hf.location from hippo_facility hf,
                                (SELECT @row:=0) AS row_count
                                where location is not null
                )asd1
                on asd.parent=asd1.id
                where asd.last_modified > '$lastreporttime' ";

        //query to get data elements
        $deQuery = "select hj.id,hj.title as name from hippo_job hj
            where hj.last_modified > '$lastreporttime' ";

        $db = I2CE::PDO();
        try {
            $orgRes = $db->query($orgQuery);
            $deRes =  $db->query($deQuery);

            $totalOrgUnitSend=0;
            $totalDESend=0;
            $totalOrgUnitReached=0;
            $totalDEReached=0;
            while( $data = $orgRes->fetch() ) {
                $orgId ='id';
                $orgName ='name';
                $orgParent ='parent';

                $orgIdArr[]=$data->$orgId;
                $orgNameArr[]=$data->$orgName;
                $orgIdParentArr[]=$data->$orgParent;


                $orgIdToSend =$data->$orgId;
                $orgNameToSend =$data->$orgName;
                $orgParentToSend =$data->$orgParent;
                $mdTypeToSend='OU';
                $deCodeToSend='';
                $deNameToSend='';


                //curl to send org structure Sagar start

                //Information from page
                $url = false;
                if (! I2CE::getConfig()->setIfIsSet($url , "/modules/DHIS-Dashboard/urls/meta_sync")
                        || !$url) {
                    I2CE::raiseError("Bad url for meta sync");
                    return false;
                }

                $curl_parameters = array(
                        'orgUnitCode' => $orgIdToSend,
                        'orgUnitName' => $orgNameToSend,
                        'parentOrgUnitCode' => $orgParentToSend,
                        'metaDataType' => $mdTypeToSend,
                        'dataElementCode' => $deCodeToSend,
                        'dataElementName' => $deNameToSend
                        );


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

                $totalOrgUnitSend=$totalOrgUnitSend+1;

                if ($content === false || $info['http_code'] != 200) {
                    $output = "\nNo cURL data returned for $url [". $info['http_code']. "]";
                    if (curl_error($ch))
                        $output .= "\n". curl_error($ch);

                }
                else {
                    $totalOrgUnitReached=$totalOrgUnitReached+1;
                }
                curl_close($ch);


            }


            while( $data = $deRes->fetch() ) {
                $deCode ='id';
                $deName ='name';

                $deIdArr[]=$data->$deCode;
                $deNameArr[]=$data->$deName;

                $orgIdToSend ='';
                $orgNameToSend ='';
                $orgParentToSend ='';
                $mdTypeToSend='DE';
                $deCodeToSend=$data->$deCode;
                $deNameToSend=$data->$deName;

                $rest1=substr($deCodeToSend,0,3);
                $rest=substr($deCodeToSend,4,8);
                $deCodeToSend = $rest1.$rest;


                $url = false;
                if (! I2CE::getConfig()->setIfIsSet($url , "/modules/DHIS-Dashboard/urls/meta_sync")
                        || !$url) {
                    I2CE::raiseError("Bad url for meta sync");
                    return false;
                }

                $curl_parameters = array(
                        'orgUnitCode' => $orgIdToSend,
                        'orgUnitName' => $orgNameToSend,
                        'parentOrgUnitCode' => $orgParentToSend,
                        'metaDataType' => $mdTypeToSend,
                        'dataElementCode' => $deCodeToSend,
                        'dataElementName' => $deNameToSend
                        );


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

                //     print_r($ch);

                $totalDESend=$totalDESend+1;

                if ($content === false || $info['http_code'] != 200) {
                    $output = "\nNo cURL data returned for $url [". $info['http_code']. "]";
                    //           echo curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
                    if (curl_error($ch))
                        $output .= "\n". curl_error($ch);
                    //                            echo $output;

                }
                else {
                    $totalDEReached=$totalDEReached+1;
                }
                curl_close($ch);

                //curl to send org structure Sagar end
            }
        } catch ( PDOException $e ) {
            I2CE::pdoError( $e, "Failed to get dashboard data:" );
            retrn false;
        }

        //store magic data in the
        I2CE::getConfig()->traverse("/modules/DHIS-Dashboard/Time/SendTime", true, false)->setValue(date('y-m-d h:m:s'));
// Get period_type start

        $period_type=$this->request('Period');
        //    echo $period_type;
// Get period_type end

// Get report start

        $report_view=$this->request('ReportViews');
// Get report end

        
        
        switch ($period_type) {
        case 'Q':
            $transform = 'dhis_quarterly';
            break;
        case 'Y':
            $transform = 'dhis_yearly';
            break;
        case 'MA':
            $transform = 'dhis_all_month';
            break;
        default:
        case 'M':
            $transform = 'dhis_monthly';
            break;
        }
        
        $xml_export = new I2CE_CustomReport_Display_Export($this,$report_view);
        $xml_export->setStyle('xml');
        $xml_export->setTransform($transform);
        if ( ! ($exported_xml = $xml_export->generateExport())) {
             I2CE::raiseError("Could not generate export report");
             return false;
        }
        
        $exported_xml=str_replace("b|","b",$exported_xml);
        $file_path = tempnam(sys_get_temp_dir(), 'DHIS_DASH_') . 'xml';
        $fp = fopen($file_path, "w");
        fwrite($fp,$exported_xml);
        fclose($fp);



        $url = false;
        if (! I2CE::getConfig()->setIfIsSet($url , "/modules/DHIS-Dashboard/urls/value_sets")
            || !$url) {
            I2CE::raiseError("Bad url for value sets");
            return false;
        }
        
        //Code to send data to DHIS
        $output =exec("curl -d @$file_path $url -H 'Content-Type:application/xml' -u $username:$password -v");

        try{
            $xml = new SimpleXMLElement($output);
            $status = $xml->dataValueCount;
            $shortStatus = ' Data Status-- '.' :Imported-'.$status['imported'].' :Updated-'.$status['updated'].' :Ignored-'.$status['ignored'].' ';
            $this->template->setDisplayData ("importedStatus", $shortStatus );
            
        } catch (Exception $ex){
            $this->template->setDisplayData ("importedStatus", $ex);
        }

        unlink ($file_path);

        return true;
    }




}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
