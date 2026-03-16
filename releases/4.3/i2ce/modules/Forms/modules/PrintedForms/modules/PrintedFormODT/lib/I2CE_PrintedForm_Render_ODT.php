<?php
/**
* © Copyright 2010 IntraHealth International, Inc.
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
* @package I2CE
* @subpackage I2CE
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.0.4
* @since v4.0.4
* @filesource 
*/ 
/** 
* Class I2CE_PrintedForm_Render
* 
* @access public
*/


class I2CE_PrintedForm_Render_ODT extends I2CE_PrintedForm_Render{

    protected function processElement_text($left_x,$top_y,$formData,$textProps, $elementConfig) {
        
    }

    protected function processElement_image($left_x,$top_y,$formData,$textProps, $elementConfig) {

    }
    
    protected function addPage($textProps)  {

    }

    /**
     *  @var procted array $images  of binary image data .  Keys are the file names to be stored in the Pictures directory, values are the blob data.
     */
    protected $images;
    
    /**
     * Constructor
     * @param string $std_form The standardized printed form we are rendeding
     * @parm array $ids of string.  The ids of the primary form in the relationship
     * @param string $base_rel_config The magic data path that we look for the base relationship in.  Defaults to /modules/CustomReports/relationships
     */
    public function __construct($std_form,$ids,$base_rel_config = '/modules/CustomReports/relationships') {
        parent::__construct($std_form,$ids,$base_rel_config);
        $this->images = array();
    }


    /**
     * @var protected Odf odf
     */
    protected $odf;

    /**
     *Abstract method to render the form. Makes sure all ducks are in a row
     * @returns boolean true on sucess.
     */
    public function render() {
        if (count ($this->ids) != 1) {
            I2CE::raiseError("Exactly one ID must be specifed (currently)");
            return false;
        }
        if (!is_string($this->std_form) || strlen($this->std_form) == 0) {
            I2CE::raiseError("No standard printed form set");
            return false;
        }
        $this->stdConfig = I2CE::getConfig()->traverse( '/modules/PrintedForms/forms/' . $this->std_form, false);
        if (!$this->stdConfig instanceof I2CE_MagicDataNode) {
            I2CE::raiseError("No standard printed form   /modules/PrintedForms/forms/" . $this->std_form);;
            return false;
        }
        if (!  $this->stdConfig->setIfIsSet($relationship, 'relationship' )) {
            I2CE::raiseError("No relationship set");
            return false;
        }
        try {
            $this->rel = new I2CE_FormRelationship($relationship, $this->base_rel_config);
        } catch (Exception $e) {
            I2CE::raiseError("Could not instatiate relationship $relationship");
            return false;
        } 


        $template = false;
        $template_upload = false;
        if ( $this->stdConfig->setIfIsSet($template_upload, 'template_upload',true ) 
             && array_key_exists('content',$template_upload) && $template_upload['content']
             && array_key_exists('name',$template_upload) && $template_upload['name'] ) {
            $name = $template_upload['name'];
            $pos = strrpos($name,'.');
            if ($pos !== false) {
                $name = substr($name, 0,$pos);
            }
	    $this->template_file = tempnam(sys_get_temp_dir(), basename($name .'_' )) . '.odt';
	    file_put_contents($this->template_file,$template_upload['content']);            
        } else  if ( $this->stdConfig->setIfIsSet($template, 'template' )) {
            $this->template_file = I2CE::getFileSearch()->search('ODT_TEMPLATES',$template);
            if (!$this->template_file) {
                I2CE::raiseError("No template file found from $template");
                return false;
            }
        } else {
            I2CE::raiseError("No template  set");
            return false;
        }

        $template_contents = new ZipArchive();
        if ($template_contents->open($this->template_file)!==TRUE) {
            I2CE::raiseError("Could not extract odt file");
            return;
        }
        $this->template_vars = array();
        for ($i=0; $i<$template_contents->numFiles;$i++) {
            $stats = $template_contents->statIndex($i);
            if (  $stats['name'] != 'content.xml') {
                continue;
            }
            $matches = array();
            //pull out all the template variables for processing.
            preg_match_all( '/{{{([0-9a-zA-Z_\-\+\,\=\.]+(\(.*?\))?)}}}/', $template_contents->getFromIndex($i), $matches, PREG_SET_ORDER);
            foreach ($matches as $match) {
                if (!is_array($match) || count($match) < 2 || !is_string($match[1]) || strlen($match[1]) == 0) {
                    continue;
                }
                $this->template_vars[] = $match[1];
            }
            $this->template_vars = array_unique($this->template_vars);
            
        }
        
        $this->content = $this->stdConfig->getAsArray('content');
        $forms = array();
        foreach ($this->ids as $id) {
            if (!is_string($id)) {
                continue;
            }
            $fs = $this->rel->getFormsSatisfyingRelationship($id);
            if (!is_array($fs) || count($fs) == 0) {
                continue;
            }
            $forms[$id] = $fs;
        }
        if (count($forms) == 0) {
            I2CE::raiseError("No valid forms");
            return false;
        }

        $this->forms = $forms;
        $textProps = array();
        I2CE::longExecution(  );
        $success = $this->_render($textProps);
        return $success;
    }

    /**
     * @var protected string $template_file. The absolute file location of the template we are reading from
     */
    protected $template_file = false;

    /**
     * @var protected  array $template_vars An array of the referenced template variables in content.xml
     */
    protected $template_vars = array();

    /**
     * @var protected array $output. Indedx by id's of the output files
     */
    protected $output = array();

    protected $formData = array();
    
    protected $user;
    /**
     *business  method to render the forms
     * @param array $textProps
     * @returns boolean true on sucess.
     */
    protected function _render($textProps) {
        $odf_config = array('DELIMITER_LEFT' => '{{{',    'DELIMITER_RIGHT' => '}}}', 'ZIP_PROXY' => 'PhpZipProxy');

        $this->odf = new I2CE_Odf($this->template_file, $odf_config);
        
        reset($this->forms);
        $this->setCurrentId(key($this->forms));
        $this->formData = current($this->forms);
    

        foreach ($this->template_vars as $template_var) {           
            $replacement = '';
            if (substr($template_var,0,2) == '++') {
                //it is a special variable
                $spec = strtolower(substr($template_var,2,4));
                $arg = '';
                if (preg_match('/^\(\s*(.*?)\s*\)$/', substr($template_var,6), $matches)) {
                    $arg = $matches[1];
                }
                switch ($spec) {
                case 'date':
                    if (strlen($arg) == 0) {
                        $arg = '%x';
                    }
                    $replacement = @strftime($arg);
                    break;
                case 'user':
                    if (!$this->user instanceof I2CE_User) {
                        $this->user = new I2CE_User();
                    }
                    $replacement = $this->user->firstname . ' ' . $this->user->lastname;
                    break;
                case 'eval':
                    @eval('$replacement = ' . $arg . ';');
                    break;
                default:
                    //do nothing
                    break;

                }
                $this->odf->setVars($template_var,$replacement, true, 'UTF-8');
                continue;
            }
            if (substr($template_var,0,1) == '+') {
                //it is a relationship function
                $replacement  = $this->rel->evaluateFunction(substr($template_var,1),$this->formData);
                $this->odf->setVars($template_var,$replacement, true, 'UTF-8');
                continue;
            }
            //it is a form+field
            $replacement =  '';
            list($namedform,$field,$t_extra) = array_pad(explode("+",$template_var,3),3,'');
            $extra = array();
            if (is_string($t_extra) && strlen($t_extra) > 0) {
                $t_extra = explode(',',$t_extra);
                foreach ($t_extra as $ex) {
                    list($ex_k,$ex_v) = array_pad(explode('=',$ex,2),2,'');
                    if (!$ex_k || !$ex_v) {
                        continue;
                    }
                    $extra[$ex_k] = $ex_v;
                }
            } 
            
            $namedform = trim($namedform);
            $field = trim($field);
            if ($namedform == $this->stdConfig->relationship) {
                $namedform = 'primary_form';
            }                        
                
            if ($namedform && $field && array_key_exists($namedform,$this->formData) && ($this->formData[$namedform] instanceof I2CE_Form)
                &&  ($fieldObj = $this->formData[$namedform]->getField($field)) instanceof I2CE_FormField) {

                $this->odf->setField($template_var,$fieldObj,$extra);
                continue;
            }
            $this->odf->setVars($template_var,'');
        }
        return true;
    }


    /**
     *Abstract method to retreive/display the contents of the rendered forms
     * @param boolean $as_string.  Defaults to false 
     * @returns mixed.  If {$as_string} is false the it is a  boolean true on sucess.  If $as_string is true, then it is a string on success, false on failure
     */
    public function display($as_string = false) {
        if ( ($errors  = I2CE_Dumper::cleanlyEndOutputBuffers())) {
            I2CE::raiseError("Got errors:\n$errors");
        }
        
        if ($as_string) {
            //we don't want mime type.  this goes to the archive
            $this->odf->saveToDisk(); //
            return file_get_contents($this->odf->getTmpFile());
        } else {
            //we do want mime type.  this goes to the browser
            $this->odf->exportAsAttachedFile(addslashes($this->getFileName()));
            exit; // we want to make sure there is no further output or that the $this->page->display() method is not called
        }
    }

    public  function getMimeType() {
        return 'application/vnd.oasis.opendocument.text';
    }

    public  function getFileName() {
        return basename($this->template_file);
    }


    
  }
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
