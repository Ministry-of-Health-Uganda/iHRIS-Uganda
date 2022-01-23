<?php
/**
* © Copyright 2014 IntraHealth International, Inc.
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
* @subpackage svs
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.2.0
* @since v4.2.0
* @filesource 
*/ 
/** 
* Class I2CE_Swiss_SVS_Lists
* 
* @access public
*/


class I2CE_Swiss_SVS_Lists extends I2CE_Swiss {
    

    protected function getChildType($child) {
	return 'SVS';
    }


        
    public function displayValues($content_node,$transient_options, $action) {
        if (! ($mainNode = $this->template->appendFileByNode('swiss_svs_lists.html','div',$content_node)) instanceof DOMNode
	    || ! ($listNode = $this->template->getElementByName('lists',0,$mainNode)) instanceof DOMNode
	    ) {
	    return false;
	}
        $publish_text = 'Publish a new version';
        $retrieve_text = 'Retrieve the most recent version';
        $edit_text = 'Edit %s configuration';
        $other_text = 'Other Versions:';
        $based_on_form_text = 'Based on the list %s';
        I2CE::getConfig()->setIfIsSet($publish_text,"/modules/SVS/messages/publish");
        I2CE::getConfig()->setIfIsSet($retrieve_text,"/modules/SVS/messages/retrieve");
        I2CE::getConfig()->setIfIsSet($edit_text,"/modules/SVS/messages/edit");
        I2CE::getConfig()->setIfIsSet($other_text,"/modules/SVS/messages/other");
        I2CE::getConfig()->setIfIsSet($based_on_form_text,"/modules/SVS/messages/basd_on_form_style");
	foreach ($this->storage->getKeys() as $oid) {
	    if (! ($swissChild = $this->getChild($oid)) instanceof I2CE_Swiss_SVS) {
		continue;
	    }
            $form = $swissChild->getField('list');
	    $id = 'list_' . $oid;
	    $listNode->appendChild($liNode = $this->template->createElement('li'));
	    $liNode->appendChild($spanNode=  $this->template->createElement('span',array('id'=>$id)));
	    $spanNode->appendChild($aNode =  $this->template->createElement('a',array('id'=>$id .'_link' )));
	    $aNode->appendChild($this->template->createElement('h3',array(),sprintf($edit_text, $oid)));
            if ($form ) {
                $spanNode->appendChild($pNode =  $this->template->createElement('p',array(), sprintf($based_on_form_text,$form)));
            }
	    $spanNode->appendChild($divNode =  $this->template->createElement('div',array('id'=>$id .'_ajax','class'=>'indented' )));
	    $spanNode->appendChild($linkDivNode =  $this->template->createElement('div',array('class'=>'indented' )));
	    $publish = I2CE_Page::getAccessedBaseURL() . '/SVS/publish?id='  .$oid;
	    $retrieve = I2CE_Page::getAccessedBaseURL() . '/SVS/RetrieveValueSet?id=' . $oid;
	    $linkDivNode->appendChild($this->template->createElement('a',array('href'=>$publish ),$publish_text));
	    $linkDivNode->appendChild($this->template->createElement('br'));
            $versions = iHRIS_SVS::getVersions($oid);
            if (count($versions) > 0) {
                $linkDivNode->appendChild($this->template->createElement('a',array('href'=>$retrieve ),$retrieve_text));
                $linkDivNode->appendChild($this->template->createElement('br'));
                $linkDivNode->appendChild($this->template->createTextNode($other_text));
                sort($versions);
                foreach ($versions as $i=> $version) {
                    if ($i != 0) {
                        $linkDivNode->appendChild($this->template->createTextNode(','));
                    }
                    $linkDivNode->appendChild($this->template->createElement('a',array('href'=>$retrieve .'&version=' . $version )," $version "));
                }
            }
	    $linkDivNode->appendChild($this->template->createElement('pre',array(),  $retrieve));
	    $swissChild->addAjaxLink( $id . '_link','contents', $id . '_ajax' ,$spanNode,$action, $transient_options);
	}
	return true;
    }




}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
