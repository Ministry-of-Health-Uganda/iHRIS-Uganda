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
* @package csd-provider-registry
* @subpackage search
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.2.0
* @since v4.2.0
* @filesource 
*/ 
/** 
* Class CSD_SearchMatches
* 
* @access public
*/


class CSD_SearchMatches extends CSD_Search{

    public static function checkStyle_csd_search($formfield) {
        $path = "meta/display_style_options/csd_search/form";       
        $fpath = "meta/display_style_options/csd_search/field";       
	if(!$formfield instanceof I2CE_FormField
	   || ! ($formfield->optionsHasPath($path))
	   || ! ($formfield->optionsHasPath($fpath))
	   || ! is_scalar( $field = $formfield->getOptionsByPath($fpath))
	   || ! is_scalar( $form = $formfield->getOptionsByPath($path))
	   || ! ($formObj = I2CE_FormFactory::instance()->createContainer($form)) instanceof CSD_SearchMatches
	   || ! ($formObj->getField($field) instanceof I2CE_FormField)
	    ) {
	    return false;
	}
	return true;
    }


    public static function processDOMEditable_csd_search($formfield, $node, $template, $form_node,$show_hidden = 0) {
        $path = "meta/display_style_options/csd_search/form";       
        $fpath = "meta/display_style_options/csd_search/field";       
	if(!$formfield instanceof I2CE_FormField
	   || ! ($template instanceof I2CE_Template)
	   || ! ($formfield->optionsHasPath($path))
	   || ! ($formfield->optionsHasPath($fpath))
	   || ! is_scalar( $search_form = $formfield->getOptionsByPath($path))
	   || ! is_scalar( $search_field = $formfield->getOptionsByPath($fpath))
	   || ! ($search_formObj = I2CE_FormFactory::instance()->createContainer($search_form)) instanceof CSD_SearchMatches
	   || ! ($search_formObj->getField($search_field) instanceof I2CE_FormField)
	    ) {
	    return false;
	}
	


        $ele_name = $formfield->getHTMLName();
	$selected =$formfield->getDBValue();
	$hiddenNode = $template->createElement( 'input', array( 'type' => 'hidden', 'name' => $ele_name, 
								'id' => $ele_name, 'value' => $selected ) );
	if ( $selected == '' ) {
	    $default_display = '';
	} else {
	    $default_display = $formfield->getDisplayValue();
	}
	$displaySection = $template->createElement( 'span', array( 'class' => 'field_selection'.($formfield->hasInvalid()?' error':'') ) );
	$displayNode = $template->createElement( 'span', array( 'id' => $ele_name . '_display' ), 
						 $default_display );
	$formfield->setElement( $hiddenNode );
    
	$clearNode = $template->createElement( 'span', array( 'id' => $ele_name . '_clear', 
							      'onclick' => "resetAjaxList('$ele_name');", 'style' => 'float: right; vertical-align: text-top; font-style: italics; display: ' . ($selected == ''?'none':'inline') . ';' ), 
					       ' - ' . $clear_text );

	$node->appendChild( $hiddenNode );

	$displaySection->appendChild( $displayNode );
	$displaySection->appendChild( $clearNode );
	$node->appendChild( $displaySection );

	$acNode = $template->createElement('input', array( 'type' => 'text', 'name' => "ac-$ele_name", 'id' => "ac-$ele_name" ,'class'=>'field_ac' ) );
	$node->appendChild( $acNode );
        $js = array();
	$js[] = "window.addEvent('domready', function() {\n"
	    ."  new Autocompleter.Request.JSON( 'ac-$ele_name', 'index.php/csd_search_results/$search_form',\n"
	    ."    {'minLength' : 3, 'postVar' : 'form[". urlencode($search_form) . '][0][0][fields][' . urlencode($search_field ). "]',\n"
	    ."    'injectChoice' : function(token) {\n"
	    ."      for( key in token.data ) {\n"
	    ."        var choice = new Element('li', {'html' : this.markQueryValue(token.data[key])});\n"
	    ."        choice.inputValue = key;\n"
	    ."        this.addChoiceEvents(choice).inject(this.choices);\n"
	    ."      }\n"
	    ."    },\n"
	    ."    'onSelection' : function( element, selected, value, input ) {\n"
	    ."      //resetCSDList('$ele_name');\n"
	    ."      $('$ele_name').set('value', value);\n"
	    ."      $('${ele_name}_display').set('text', selected.get('text'));\n"
	    ."      element.set('value', '' );\n"
	    ."    }\n"
	    ."  } ); \n"
	    ."} );\n";




        $ff = $formfield->getContainer()->getName() . '+' . $formfield->getName();
  
	$defaultNode = $template->createElement( 'span', array( 'id' => "default_${ele_name}", 'style' => 'display: none;' ), $formfield->getDBValue() );
	$node->appendChild( $defaultNode );

        $first = true;
        $count = 1;

	$disp_field = $formfield->getName();

	$block_name = $ele_name . '_block';
	$opts = array( 'id' => $block_name, 'style' => 'display: block;' );
	$disp_form_name =$search_formObj->getDisplayName();
	$spanNode = $template->createElement( 'span', $opts );

	$node->appendChild( $spanNode );

        $template->addHeaderLink( 'mootools-core.js' );
        $template->addHeaderLink( 'mootools-more.js' );
        $template->addHeaderLink( 'Observer.js' );
        $template->addHeaderLink( 'Autocompleter.js' );
        $template->addHeaderLink( 'Autocompleter.css' );
        $template->addHeaderLink( 'Autocompleter.Request.js' );
        $template->addHeaderLink( 'iHRIS_CSDList.js' );
        $template->addHeaderText( "window.addEvent('domready', function() {\n" . implode( "\n", $js ) . " });\n",
                'script', 'csd_search');
    }






}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
