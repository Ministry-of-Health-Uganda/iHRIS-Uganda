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
 */
/**
 * @package I2CE
 * @author Luke Duncan <lduncan@intrahealth.org> carl leitner <litlfred@ibiblio.org>
 * @since v1.0.0
 * @version v2.0.0
 */

class I2CE_Module_Forms extends I2CE_Module {


    public static function getHooks() {
        return array(
            'process_templatedata_FORM'=> 'processForms',
            'pre_page_prepare_display'=>'setFormPriority',
            'autoload_search_for_class'=>'invisibleClass',
            'final_page_prepare_display'=>'finalizeDisplay',
            'form_post_delete'=>'removeCache_hook'
            );         
    }

    


    /**
     * Hooked method to marks a form as dirty (needs to be cached).
     * @param mixed $args.   an array of two elements whose first element is a form object, the second is the user
     */
    public function  removeCache_hook($args) {
        if (!is_array($args)   ||  !array_key_exists('form',$args)) {
            return;
        }
        $form = $args['form'];
        if (!$form instanceof I2CE_Form) {
            return;
        }
        $ff = I2CE_FormFactory::instance();
        $ff->removeFromCache($form);
        $form->cleanup();
    }




    public function invisibleClass($class_name) {
        $classConfig = I2CE::getConfig()->traverse("/modules/forms/formClasses/$class_name",false,false);
        if (!$classConfig instanceof I2CE_MagicDataNode) {
            return null;
        }
        if (!$classConfig->is_scalar('extends') || ! $classConfig->extends) {
            I2CE::raiseError("Checking for invisible class $class_name -- no extension");
            return null;
        }
        return  'class ' . $class_name . ' extends ' . $classConfig->extends . ' {}';
    }


    public function finalizeDisplay($page) {
        if (!$page instanceof I2CE_Page) {
            return;
        }
        if (! ($template = $page->getTemplate())instanceof I2CE_Template) {
            return;
        }
        if ( ($dataTables = $template->query("//*[@class='dataTable']/descendant-or-self::table")) instanceof DOMNodeList) {
            foreach ($dataTables as $dataTable) {
                if ( ! ($rows = $template->query('.//tr', $dataTable)) instanceof DOMNodeList) {
                    continue;
                }
                $even = true;
                foreach ($rows as $row) {
                    if ((  ($headers = $template->query('.//th', $row)) instanceof DOMNodeList) && ($headers->length > 0)) {
                        $even = true;
                        continue;
                    }
                    $even = !$even;
                    $class ='';
                    if ($row->hasAttribute('class')) {
                        $class = $row->getAttribute('class');
                    }
                    $class = trim(preg_replace('/\seven\s/' , ' ',' ' . $class . ' '));
                    if ($even) {
                        if ($class) {
                            $class .= ' ';
                        } 
                        $class .= 'even';
                    }
                    $row->setAttribute("class", $class);
                }
            }
        }

    }


    public function setFormPriority($page) {
        if (!$page instanceof I2CE_Page) {
            I2CE::raiseError("Did not receive expected page");
        }
        $template = $page->getTemplate();
        if (!$template instanceof I2CE_Template) {
            return false;
        }
        $template->setDataTypePriority('FORM',0);
    }


    public static function getMethods() {
        return array(
            'I2CE_PermissionParser->hasPermission_form' => 'hasPermission_form',
            'I2CE_Template->setForm' => 'setForm',
            'I2CE_Template->getForm' => 'getForm',
            'I2CE_Template->getField' => 'getField',
            'I2CE_Template->setReview'=>'setReview',
            'I2CE_Template->isReview'=>'isReview',
            'I2CE_Page->setForm' => 'setForm',
            'I2CE_Page->getForm' => 'getForm',
            'I2CE_Page->getField' => 'getField',
            'I2CE_Page->setReview'=>'setReview',
            'I2CE_Page->isReview'=>'isReview',
            );
    }



    protected function hasPermission_form($node,$args) {
        if (count($args) < 2) {
            I2CE::raiseError("Two few arguments for permision form() method");
            return null;
        }
        $formname = array_shift($args);
        $method = array_shift($args);
        $form = $this->template->getForm($formname,$node);
        if (!$form instanceof I2CE_Form) {
            I2CE::raiseError("Unable to find form $formname");
            return null;
        }
        if (!$form->_hasMethod($method)) {
            I2CE::raiseError("Method $method is not callable in $formname:" . get_class($form));
            return null;
        }
        return call_user_function_array(array($form,$method), $args);
    }



    /**
     * Upgrade module method
     */
    public function upgrade($old_vers,$new_vers) {
        if (I2CE_Validate::checkVersion($old_vers,'<','3.0.1001')) {
            if (! $this->createDateIndexOnLastEntry()) {
                return false;
            }
        }
        return true;
    }



    /**
     * Checks to make sure there is an index on the date column last_entry table named date.  If it does
     * not exist it adds it.
     *
     * Note: this should seem to need be in the last entry mdoule, but this existed before it was created.  probably msotyl dead code at this point as noone should be less than 3.1
     */
    protected function createDateIndexOnLastEntry() {
        $db = I2CE::PDO();
        $qry =
            "SELECT  null FROM information_schema.statistics WHERE table_schema = '{$db->database_name}' and table_name = 'last_entry' and index_name = 'date'";
        try {
            $result = $db->query($qry);
            if ($result->rowCount() > 0) {
                //the index has already been created.
                unset( $result );
                return true;
            }
            //the index has not been created.
            ini_set('max_execution_time',6000);
            I2CE::raiseError("Creating index 'date' on last_entry");
            $qry = "CREATE INDEX date ON last_entry (date)";
            $result = $db->query($qry);
            unset( $result );
        } catch ( PDOException $e ) {
            I2CE::pdoError($e,"Cannot execute  query:\n$qry");
            return false;
        }
        return true;

    }


    /**
     * A flag to determine if the page is a confirmation form.
     * 
     * This will cause the form display to make all elements "hidden" and display the values
     * as text for a confirmation view.
     * @var boolean
     */
    protected $isReview;
    /**
     * Set the {@link isReview} variable to true.
     */
    public function setReview($template) {
        $this->isReview = true;
    }
    /**
     * Get the review status
     */
    public function isReview($template) {
        return $this->isReview;
    }

    

    public function __construct() {
        parent::__construct();
        $this->isReview = false;
    }


    public function getForm($template,$form,$node = null) {
        if ($template instanceof I2CE_Page) {
            $template = $template->getTemplate();
        }
        if (!$node instanceof DOMNode) {
            $node = $template->ensureNode($node,true);
        }
        return $template->getData('FORM',$form,$node,false,true);
    }

    public function setForm($template,$form,$node = null) {
        if ($template instanceof I2CE_Page) {
            $template = $template->getTemplate();
        }
        if (!$form instanceof I2CE_Form) {
            I2CE::raiseError("Not Form");
            return false;
        }
        $template->setData($form,$node,'FORM',$form->getName(),true);
    }




    /**
     * returns the indicated field from the indicated form that is responsible for the data  in this node.
     * @param string $form_field  The form name and field name separataed by a colon. Example $form_field = "salary:begin";
     * @param mixed $node Specfies the node at which the data is set. If $node is a DOMNode
     * then it is the node.  If null (default) then the data applies to the whole.  Otherwise $node should
     * specify the ID of some node in the DOM.
     * @returns I2CE_Field
     */
    public function getField($template,$form_field,$node = null) {
        if ($template instanceof I2CE_Page) {
            $template = $template->getTemplate();
        }
        $template->ensureNode($node);
        $data = explode( ':', $form_field );
        if (count($data) ==2) {
            $form = $template->getData('FORM',$data[0],$node, false, false);
            $field_name = $data[1];
        } else {
            $field_name = $form_field;
            $form = $template->getDefaultData('FORM');
        }
        if ($form instanceof I2CE_Form) {
            return $form->getField($field_name);
        } else {
            return null;
        }
                
    }




    /**
     * Process any form elements on the page.
     * 
     * This method will parse all form elements on the page to replace the values if they are known.
     * There are three type of form directives for a span.  The first two are as follows
     * <span type='form' name='form_name:form_field_name'></span>
     * and <span type='form' name='form_name->method_name(args)'></span>
     * In each case we find the form with form name 'form_name'.
     * In the first case, we  call the processDom() method for the form field identified by form_field_name.
     * For the seconcd case we call the method_name method of the form with the arguments specified by args.
     *
     * The last case is for 
     *   <span type='form' name='form_field_name'></span>
     * In this case is like the first case except that the form used is the default form for this page.
     *
     */
    public function processForms($page){
        if (!$page instanceof I2CE_Page) {
            I2CE::raiseError("Did not receive expected page");
            return false;
        }
        $template = $page->getTemplate();
        $user = $page->getUser();
        $qry = '//span[@type=\'form\']';
        $results = $template->query($qry);
        for( $i = 0; $i < $results->length; $i++ ) {
            $node = $results->item($i);
            $node->removeAttribute('type');
            if (!$node->hasAttribute('name')) {
                continue;
            }
            $name= trim($node->getAttribute( "name" ));                                       
            if (!$name) {
                continue;
            }
            //the following regular expression matches any valid PHP function or label: http://us3.php.net/functions
            $phpfunc = '[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*';
            if (preg_match("/^($phpfunc):($phpfunc)\$/",$name,$matches)) {
                $form = $template->getData('FORM',$matches[1],$node,false,true);                  
                if ( $form instanceof I2CE_Form && $form->getName() != $matches[1]) {
                    I2CE::raiseError("Form name mismatch " . $form->getName() . '!=' .  $matches[1]);
                    $form = null;
                }
                $method = 'displayField';
                if (!array_key_exists(3,$matches)) {
                    $matches[3] = null;
                }
                $args = array($matches[2],substr($matches[3],1,-1));
            } else if (preg_match("/^($phpfunc)\$/",$name,$matches)) {
                $form = $template->getDefaultData('FORM');
                $method = 'displayField';
                if (!array_key_exists(2,$matches)) {
                    $matches[2] = null;
                }
                $args = array($matches[1],substr($matches[2],1,-1));
            }else if (preg_match("/^($phpfunc)->($phpfunc)(\(.*\))\$/",$name,$matches)) {
                $node->removeAttribute('name');
                $form = $template->getData('FORM',$matches[1],$node,false,true);
                if (!$form instanceof I2CE_Form) {
                    continue;
                }
                if (!$form->_hasMethod($matches[2])) {
                    continue;
                }
                $args = I2CE_Module_Tags::getArguments($page,$node,$matches[3],true);
                if (count($args) < 2) {
                    continue;
                }
                @call_user_func_array(array($form,$matches[2]),$args);
                continue;
            } else {
                continue;
            }
            if ($form instanceof I2CE_Form) {
                $form->processDOM($node,$template,$method,$args);
            } else {
                $node->parentNode->removeChild( $node );
            }
        } 
        
        $results = $template->query( '//*[@form]');
        for( $i = 0; $i < $results->length; $i++ ) {
            $node = $results->item($i);
            $form = $node->getAttribute('form');
            $orig_form = $form;
            $node->removeAttribute('form');
            if (!$form) {
                continue;
            }
            try {
                //example <span form='$user->displayField("surname")'/> is the same as <span type='form' name='user:surname'>
                //which is the same as <span form='$user->surname->processDOM()'/>
                I2CE_Module_Tags::callModuleFunction('',false,$form,true, 'FORM'); //try to call a template function
            } catch (Exception $e) {
                I2CE::raiseError("Could not evaluate $orig_form:\n" . $e->getMessage());
                continue;
            }
        }        
        
        //now we fix up any <form> tags that have a file input
        $forms = $template->query( '//form');
        for( $i = 0; $i < $forms->length; $i++ ) {
            $form = $forms->item($i);
            $file_inputs = $template->query('.//input[@type="file"]',$form);
            if ($file_inputs->length == 0) {
                continue;
            }
            $form->setAttribute('enctype','multipart/form-data');
        }
    }


}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
