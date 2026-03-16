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


abstract class I2CE_PrintedForm_Render extends I2CE_Fuzzy{

    /**
     * @var protected string $std_form.  The standardized printed form we are rendeding
     */
    protected $std_form;
    /**
     * @var protected array $ids.  The ids of the primary form in the relationship
     */
    protected $ids;


    /**
     * @var string $base_rel_config The magic data path that we look for the base relationship in.  
     */
    protected $base_rel_config;
    
    /**
     * Constructor
     * @param string $std_form The standardized printed form we are rendeding
     * @parm array $ids of string.  The ids of the primary form in the relationship
     * @param string $base_rel_config The magic data path that we look for the base relationship in.  Defaults to /modules/CustomReports/relationships
     */
    public function __construct($std_form,$ids,$base_rel_config = '/modules/CustomReports/relationships') {
        $this->std_form = $std_form;
        $this->ids = $ids;
        $this->base_rel_config = $base_rel_config;
    }


    /**
     *@var protected I2CE_FormRelationsip $rel The form relationshiop
     */
    protected $rel;
    /**
     * @var protected I2CE_MagicDataNode $stdConfg the magic data node for the standard config    
     */
    protected $stdConfig;



    /**
     *@var protected array $layoutOptions.  Main options for page layout     
     */
    protected $layoutOptions;
    /**
     *@var protected array $content.  Content descrtiption
     */
    protected $content; 

    /**
     *Abstract method to render the form. Makes sure all ducks are in a row
     * @returns boolean true on sucess.
     */
    public function render() {
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
        $this->layoutOptions = array(
            'encoding'=>'ASCII',
            'hyphenation_file'=>'hyph_en_US.dic',
            'orientation'=>'P',
            'size'=>'A4',
            'rows'=>1,
            'cols'=>1,
            'horiz_pad'=>10,
            'horiz_pad_border'=>0,
            'vert_pad'=>10,
            'vert_pad_border'=>0
            );
        if ($this->stdConfig->is_parent('layout_details')) {
            I2CE_Util::merge_recursive($this->layoutOptions,$this->stdConfig->getAsArray("layout_details"));
        }
        if (!in_array($this->layoutOptions['orientation'], array('P','L'))) {
            $this->layoutOptions['orientation'] = 'P';
        }
        $paper_sizes = array(
            'A0'=>array(841,1189),
            'A1'=>array(594,841),
            'A2'=>array(420,594),
            'A3'=>array(297,420),
            'A4'=>array(210,297),
            'A5'=>array(148,210),
            'A6'=>array(105,148),
            'A7'=>array(74,105),
            'A8'=>array(52,74),
            'A9'=>array(37,52),
            'A10'=>array(26,37),

            'B0'=>array(1000,1414),
            'B1'=>array(707,1000),
            'B2'=>array(500,707),
            'B3'=>array(353,500),
            'B4'=>array(250,353),
            'B5'=>array(176,350),
            'B6'=>array(125,176),
            'B7'=>array(88,125),
            'B8'=>array(62,88),
            'B9'=>array(44,62),
            'B10'=>array(31,44),
            'C0'=>array(917,1297),
            'C1'=>array(648,917),
            'C2'=>array(458,648),
            'C3'=>array(324,458),
            'C4'=>array(229,324),
            'C5'=>array(162,229),
            'C6'=>array(114,162),
            'C7'=>array(81,114),
            'C8'=>array(57,81),
            'C9'=>array(40,57),
            'C10'=>array(28,40),
            'LETTER'=>array(216,279),
            'LEGAL'=>array(216,356),
            'JUNIOR_LEGAL'=>array(203,127),
            'LEDGER'=>array(432,279),
            'TABLOID'=>array(279,432)
            );
        $this->layoutOptions['size'] = strtoupper($this->layoutOptions['size']);
        if (!in_array($this->layoutOptions['size'], array_keys($paper_sizes))) {
            $this->layoutOptions['size'] = 'A4';
        }
        if ($this->layoutOptions['orientation'] == 'P') {
            $this->layoutOptions['paper_size'] = $paper_sizes[$this->layoutOptions['size']];
        } else {
            $this->layoutOptions['paper_size'] = array_reverse($paper_sizes[$this->layoutOptions['size']]);
        }
        if (!array_key_exists('border',$this->layoutOptions)) {
            if ($this->layoutOptions['rows'] == 1 && $this->layoutOptions['cols'] == 1) {
                $this->layoutOptions['border'] =0;
            } else {
                $this->layoutOptions['border'] =1;
            }
        } 
        $this->layoutOptions['border'] = (int) $this->layoutOptions['border'];
        if ($this->layoutOptions['border'] < 0) {
            $this->layoutOptions['border'] = 0;
        }
        $this->layoutOptions['horiz_pad_border'] = (int) $this->layoutOptions['horiz_pad_border'];
        if ($this->layoutOptions['horiz_pad_border'] < 0) {
            $this->layoutOptions['horiz_pad_border'] = 0;
        }
        $this->layoutOptions['vert_pad_border'] = (int) $this->layoutOptions['vert_pad_border'];
        if ($this->layoutOptions['vert_pad_border'] < 0) {
            $this->layoutOptions['vert_pad_border'] = 0;
        }
        $this->layoutOptions['horiz_pad'] = (int) $this->layoutOptions['horiz_pad'];
        if ($this->layoutOptions['horiz_pad'] < 0) {
            $this->layoutOptions['horiz_pad'] = 10;
        }
        $this->layoutOptions['vert_pad'] = (int) $this->layoutOptions['vert_pad'];
        if ($this->layoutOptions['vert_pad'] < 0) {
            $this->layoutOptions['vert_pad'] = 10;
        }

        $this->layoutOptions['rows'] = (int)$this->layoutOptions['rows'];
        $this->layoutOptions['cols'] = (int)$this->layoutOptions['cols'];
        if ($this->layoutOptions['rows'] < 1) {
            I2CE::raiseError("Invalid rows");
            return false;
        }
        if ($this->layoutOptions['cols'] < 1) {
            I2CE::raiseError("Invalid cols");
            return false;
        }
        $this->layoutOptions['form_width'] = (int) (($this->layoutOptions['paper_size'][0] 
                                                    - 2*($this->layoutOptions['horiz_pad']) 
                                                    - ( ($this->layoutOptions['border']) * ($this->layoutOptions['cols']+1)) 
                                                     - 2 * ($this->layoutOptions['horiz_pad_border']) * ($this->layoutOptions['cols'])) / $this->layoutOptions['cols']);
        $this->layoutOptions['form_height'] = (int) (($this->layoutOptions['paper_size'][1] 
                                                      - 2*($this->layoutOptions['vert_pad']) 
                                                      - ( ($this->layoutOptions['border']) * ($this->layoutOptions['rows']+1)) 
                                                      - 2 * ($this->layoutOptions['vert_pad_border']) * ($this->layoutOptions['rows']))/ $this->layoutOptions['rows']);        
        if ($this->layoutOptions['form_width'] < 10) {
            I2CE::raiseError("Not enough width");
            return false;
        }
        if ($this->layoutOptions['form_height'] < 10) {
            I2CE::raiseError("Not enough height");
            return false;
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
        $textProps = array(
            'font' => 'helvetica',
            'style' => '',
            'size' => 12,
            'alignment'=>'L',
            'color'=>'#000000',
            'bg_color'=>'none',
            'style'=>''
            );
        if ($this->stdConfig->is_parent('text_properties')) {
            I2CE_Util::merge_recursive($textProps,$this->stdConfig->getAsArray("text_properties"));
        }
        $this->validateTextProps($textProps);
        I2CE::longExecution(  );
        return $this->_render($textProps);
    }

    /**
     * @var protected array $forms. Indedx by id's of the primary form it is an array of the forms satisfying the relationship
     */
    protected $forms = array();

    /**
     * Get the form data for the given id
     */ 
    public function getFormData($id) {
        if (!array_key_exists($id,$this->forms) || !is_array($this->forms[$id])) {
            return false;
        }
        return $this->forms[$id];
            
    }

    
    /**
     * The id of the form currently being processed
     * @var protected  string 
     */
    protected $id = false;
    /**
     * Gets the id of the form currently being processsed
     * @returns string
     */
    protected function getCurrentId() {
        return $this->id;
    }

    /**
     * Sets the id of the form currently being processsed
     * @param string $id
     */
    protected function setCurrentId($id) {
        $this->id = $id;
    }


    /**
     *business  method to render the forms
     * @param array $textProps
     * @returns boolean true on sucess.
     */
    protected function _render($textProps) {
        $counter = 0;
        $num_per_page = $this->layoutOptions['rows']*$this->layoutOptions['cols'];
        foreach ($this->forms as $id=>$formData) {
            $this->setCurrentId($id);
            $counter = $counter % $num_per_page;
            if ($counter == 0) {
                $this->addPage($textProps);
            }
            $pos_x = $counter % $this->layoutOptions['cols'];
            $pos_y = (int) ($counter / $this->layoutOptions['cols'])  ;
            $left_x = ($this->layoutOptions['border'] + $this->layoutOptions['horiz_pad_border'] + $this->layoutOptions['form_width'] ) * ( $pos_x)   + $this->layoutOptions['horiz_pad'];
            $top_y = ($this->layoutOptions['border'] + $this->layoutOptions['vert_pad_border'] + $this->layoutOptions['form_height'])  * ( $pos_y)   + $this->layoutOptions['vert_pad'];
            if (!$this->addForm($left_x,$top_y, $formData, $textProps )) {
                I2CE::raiseError("Could not add form: $id");
                return false;
            }
            $counter++;
        }
        return true;        
    }

    /**
     * Validates the text properties
     * @param array &$textProps
     */
    protected function validateTextProps(&$textProps) {
        if (!is_string($textProps['font'])) {
            $textProps['font'] = 'helvetica';
        }  
        $textProps['font'] = strtolower($textProps['font']);
        if ( !in_array($textProps['font'], array('helvetica','times','courier'))) {
            $textProps['font'] = 'helvetica';
        }
        if (!is_string($textProps['alignment']) || !in_array($textProps['alignment'], array('L','R','J','C'))) {
            $textProps['alignment'] = 'L';
        }        
        $textProps['size'] = (int) $textProps['size'];
        if ($textProps['size'] < 1) {
            $textProps['size'] = 12;
        }
        if (!is_string($textProps['color']) || strlen($textProps['color']) != 7) {
            $textProps['color'] = '#000000';
        } else {
            $textProps['color'] = strtoupper($textProps['color']);
            if (strlen(ltrim(substr($textProps['color'],1),'0123456789ABCDEF')) > 0) {
                $textProps['color'] = '#000000';
            }
        }
        if (!is_string($textProps['bg_color'])) {
            $textProps['bg_color'] = 'none';
        } else  if  ($textProps['bg_color'] != 'none') {
            if (strlen($textProps['color']) != 7) {
                $textProps['bg_color'] = 'none';
            } else {
                $textProps['bg_color'] = strtoupper($textProps['bg_color']);
                if (strlen(ltrim(substr($textProps['bg_color'],1),'0123456789ABCDEF')) > 0) {
                    $textProps['bg_color'] = 'none';
                }
            }
        }        
        $style = '';
        $textProps['style'] = strtoupper($textProps['style']);
        if (strpos($textProps['style'],'B') !== false) {
            $style .= 'B';
        }
        if (strpos($textProps['style'],'I') !== false) {
            $style .= 'I';
        }
        if (strpos($textProps['style'],'U') !== false) {
            $style .= 'U';
        }
        $textProps['style'] =$style;
    }

    /**
     * Adds the form data at the specified position on the current page.
     * @param int $left_x
     * @param int $top_y
     * @param array $formData of I2CE_Form
     * @param array $textProps
     * @returns boolean. True on success
     */
    protected function addForm($left_x,$top_y,$formData,$textProps) {
        if (!$this->stdConfig->is_parent("elements")) {
            I2CE::raiseError("No elements in printed form");
            return false;
        }        
        $keys = $this->stdConfig->elements->getKeys();
        sort($keys);
        foreach ($keys as $key) {                
            if (!$this->stdConfig->elements->is_parent($key)) {
                I2CE::raiseError("bad key: $key at" . $this->stdConfig->elements->getPath(false));
                continue;
            }
            $elementConfig = $this->stdConfig->elements->$key;
            $type = false;
            if ($elementConfig->setIfIsSet($type,"type") && $type && $elementConfig->is_parent("definition")) {
                $e_textProps = $textProps;
                if ($elementConfig->is_parent("text_properties")) {
                    I2CE_Util::merge_recursive($e_textProps, $elementConfig->getAsArray("text_properties"));
                }
                $this->validateTextProps($e_textProps);
                $method = 'processElement_' . $type;
                if (!$this->_hasMethod($method)) {
                    I2CE::raiseError("Do not know how to process $type");
                    return false;
                }
                if (!$this->$method($left_x,$top_y,$formData,$e_textProps,$elementConfig->definition)) {
                    return false;
                }
            }
        }
        return true;
    }




    /**
     * Processes the printf string and args
     * @param string $printf
     * @param array $printf_args
     * @pram array $formData of I2CE_Form.  
     * @returns string
     */
    protected function processTextString($printf,$printf_args, $formData) {
        if (!is_array($printf_args)) {
            return '';
        }
        $printf_vals = array();
        $user = false;
        ksort($printf_args);
        foreach ($printf_args as $printf_arg) {
            $printf_arg = trim($printf_arg);
            if (!is_string($printf_arg) || strlen($printf_arg) == 0){
                $printf_vals[] = '';
                continue;
            }
            if (substr($printf_arg,0,2) == '++') {
                //it is a special variable
                $spec = strtolower(substr($printf_arg,2,4));
                $arg = '';
                if (preg_match('/^\(\s*(.*?)\s*\)$/', substr($printf_arg,6), $matches)) {
                    $arg = $matches[1];
                }
                switch ($spec) {
                case 'date':
                    if (strlen($arg) == 0) {
                        $arg = '%x';
                    }
                    $printf_vals[] = @strftime($arg);
                    break;
                case 'user':
                    if (!$user instanceof I2CE_User) {
                        $user = new I2CE_User();
                    }
                    $printf_vals[] = $user->firstname . ' ' . $user->lastname;
                    break;
                case 'eval':
                    @eval('$printf_vals[] = ' . $arg . ';');
                    break;
                default:
                    $printf_vals[] = '';
                }
            } else if (substr($printf_arg,0,1) == '+') {
                //it is a relationship function
                $printf_vals[]  = $this->rel->evaluateFunction(substr($printf_arg,1),$formData);
            } else {
                //it is a form+field
                list($namedform,$field) = array_pad(explode("+",$printf_arg,2),2,'');
                $namedform = trim($namedform);
                $field = trim($field);
                if ($namedform == $this->stdConfig->relationship) {
                    $namedform = 'primary_form';
                }
                if (!$namedform || !$field || !array_key_exists($namedform,$formData) || !($formData[$namedform] instanceof I2CE_Form)) {
                    $printf_vals[] = '';
                    continue;
                }
                $fieldObj = $formData[$namedform]->getField($field);
                if (!$fieldObj instanceof I2CE_FormField) {
                    $printf_vals[] = '';
                    continue;
                }
                $printf_vals[] = $fieldObj->getDisplayValue();
            }
        }
        return vsprintf($printf,$printf_vals);        
    }


    /**
     * Abstract business method to render a text element from the elements tree
     * @param int $left_x
     * @param int $top_y
     * @param array $formData of I2CE_Form
     * @param array $textProps
     * @param I2CE_MagicDataNode $elementConfig The node defining the element
     * @returns boolean. True on success
     */
    abstract protected function processElement_text($left_x,$top_y,$formData,$textProps, $elementConfig);

    /**
     * Abstract business method to render a text element from the elements tree
     * @param int $left_x
     * @param int $top_y
     * @param array $formData of I2CE_Form
     * @param array $textProps
     * @param I2CE_MagicDataNode $elementConfig The node defining the element
     * @returns boolean. True on success
     */
    abstract protected function processElement_image($left_x,$top_y,$formData,$textProps, $elementConfig);


    /**
     * Add a page to the rendered document
     * @param array $textProps
     */
    abstract protected function addPage($textProps) ;


    
    /**
      *Abstract method to retreive/display the contents of the rendered forms
     * @param boolean $as_string.  Defaults to false 
     * @returns mixed.  If {$as_string} is false the it is a  boolean true on sucess.  If $as_string is true, then it is a string on success, false on failure
     */
    public abstract function display($as_string =false);
    
    public abstract function getFileName();
    public abstract  function getMimeType();


}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
