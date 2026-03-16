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


class iHRIS_DHIS_DashboardSubmit extends  I2CE_Page {

    protected function action_menu($node) {
        if ( !$node instanceof DOMNode
             || !array_key_exists('exports',$this->args)
             || !is_array($exports = $this->args['exports'])
             || ! ($ulNode = $this->template->createElement( 'ul' )) instanceof DOMNode
             || ! $node->appendChild($ulNode)
             
            ) {
            return true;
        }
        foreach ($exports as $export=>$data) {
            if (!is_array($data)
                || !array_key_exists('name',$data)
                ) {
                continue;
            }
            $link = "dhis_submit?" . http_build_query(array('export'=>$export));
            $ulNode->appendChild($liNode= $this->template->createElement('li'));
            $liNode->appendChild($aNode = $this->template->createElement('a',array('href'=>$link)));
            $aNode->appendChild($this->template->createTextNode($data['name']));
        }
        return true;

    }

    protected $statusNode = false;

    protected function get_status_node() {
        if (!$this->statusNode) {
            if ( ($node = $this->template->getElementById('siteContent'))) {
                $this->statusNode = $this->template->createElement('span',array('class'=>'response'));
                $node->appendChild($this->statusNode);
            }
        }
        return $this->statusNode;
    }

    protected $expander_count = 0;
    protected function show_status_expander($header,$text) {
        if (!$statusNode = $this->get_status_node()) {
            return true;
        }
        if ($this->expander_count == 0) {
            $this->template->addHeaderLink("mootools-core.js");
            $js = 'function expandText(e) {  f = $(e.get("id") + "_text"); if (f) {if (f.getStyle("display") == "block") {f.setStyle("display","none");f.setStyle("visibility","invisible") } else {f.setStyle("display","block");f.setStyle("visibility","visible");}} return false}';
            $statusNode->appendChild($this->template->createElement('script',array('type'=>"text/javascript"),$js));
        }
        $this->expander_count++;
        $a = $this->template->createElement(
            'span',
            array('href'=>'','id'=>'expander_' . $this->expander_count,
                  'onclick'=>'expandText(this)',
                  'style'=>'color:blue;text-decoration:underline'
                )
            );
        $a->appendChild($this->template->createTextNode($header));
        $pre = $this->template->createElement(
            'pre',
            array(
                'id'=>'expander_' . $this->expander_count . '_text' ,
                'style'=>'display:none;visibility:invisible;background-color:#ffffcc;border:dashed;border-width:3px;border-color:#ffcc99;opacity:0.8;padding:1em;margin:1em'
                )
            );
        $pre->appendChild($this->template->createTextNode($text ));
        $this->statusNode->appendChild($a);
        $this->statusNode->appendChild($pre);

        return true;
    }
    protected function show_status($a) {
        if (!$statusNode = $this->get_status_node()) {
            return true;
        }
        $this->statusNode->appendChild($pre = $this->template->createElement('pre'));
        $pre->appendChild($this->template->createTextNode($a ));
        return true;
    }


    protected function action() {
        $node = $this->template->getElementById('siteContent');
        try {
            if (!$this->request_exists('export')
                || !($export = $this->request('export'))
                || ! array_key_exists('exports',$this->args)
                || ! is_array($this->args['exports'])
                || ! array_key_exists($export,$this->args['exports'])
                || ! is_array($args = $this->args['exports'][$export])
                ) {
                return $this->action_menu($node);
            }
            $this->show_status("Beginning Export");
            if (! array_key_exists('curl_opts',$args)
                || ! is_array($curl_opts = $args['curl_opts'])
                || ! array_key_exists('url', $args['curl_opts'])
                || ! array_key_exists('pass', $args['curl_opts'])
                || ! array_key_exists('user', $args['curl_opts'])
                || ! array_key_exists('report_opts',$args)
                || ! is_array($report_opts = $args['report_opts'])
                || ! array_key_exists('report_view',$report_opts)
                || ! array_key_exists('transform',$report_opts)
                || ! ($xml_export = new I2CE_CustomReport_Display_Export($this,$report_opts['report_view'])) 
                ) {
                return false;
            }
        } catch (Exception $e) {
            return false;//need better error message/handling
        }

        $xml_export->setTransform($report_opts['transform']);
        $xml_export->setStyle('xml');
        $this->show_status("Validated Export Paramaters");
        if ( ! ($export = $xml_export->generateExport()) 
             || ! $this->show_status_expander("Generated Export", $export)
             || ! ($file = (tempnam(sys_get_temp_dir(), 'DHIS_POST_') . '.xml'))
             || ! file_put_contents($file, $export)
             || ! is_resource($fp = fopen($file,"r"))
            ){
            return false;
        }
        $this->show_status("Sending file sized:" . filesize($file));
        if ( ! is_resource($ch = curl_init($curl_opts['url'])) 
             || ! $this->show_status("Opening Connection to DHIS Dashboard")
             || ! curl_setopt($ch, CURLOPT_POST, 1)
             || ! curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1)
             || ! curl_setopt($ch, CURLOPT_HEADER, 0)
             || ! curl_setopt($ch, CURLOPT_USERPWD,$curl_opts['user'] .':' . $curl_opts['pass'])
             || ! curl_setopt($ch, CURLOPT_HTTPHEADER,array('Content-Type:application/xml'))
             || ! $this->show_status("Sending Data to DHIS Dashboard")
            ) {
            return false;
        }

        curl_setopt($ch, CURLOPT_POSTFIELDS, $export);
        $curl_out = curl_exec($ch);
        if ($err = curl_errno($ch) ) {
            $this->show_status('Data Error ('. $err . ') ' . curl_error($ch));
        } else {
            $this->show_status("Data Sent Succesfully");
        }
        curl_close($ch);
        unlink($file) ;
        if ( ($outNode = $this->template->createElement('pre')) instanceof DOMNode
             && $node->appendChild($outNode)
            ) {
            $this->show_status_expander("DHIS2 Response",$curl_out);
            if (!$this->process_response($curl_out)) {
                $this->show_status("Invalid Response From DHIS");
            }
        }
        return true;
    }
    
    protected function process_response($curl_out) {
        $doc = new DOMDocument();
        if ( !@$doc->loadXML($curl_out)
             || ! ($xpath = new DOMXPath($doc)) 
             || !  $xpath->registerNameSpace('dxf','http://dhis2.org/schema/dxf/2.0')
             || ! ($descNodes = $xpath->query("//dxf:description[1]")) instanceof DOMNodeList 
             || !  $descNodes->length == 1
             || ! ($dvNodes = $xpath->query("//dxf:dataValueCount[1]/@*")) instanceof DOMNodeList 
            ) {
            return false;
        }
        $resp = array();
        foreach ($dvNodes as $attrNode) {
            $resp[] = $attrNode->name . " = " . $attrNode->value;
        }
        $this->show_status("Response: " . $descNodes->item(0)->textContent . "\n\t" .implode("\n\t",$resp) . "\n");
        return true;
    }

}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
