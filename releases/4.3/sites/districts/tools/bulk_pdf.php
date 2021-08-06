<?php

  //  Copy this file into the pages directory of your site then run it with "php bulk_pdf.php"
  //
  //  This assumes that you can joined in the "person_photo_passport" form in the "staff" relationship with the limit of date is max_parent



  // Defintion of the PDF

$fac_id = 1;
$pdf_layout = array(
    'relationship'=> 'staff',
    'layout_details'=>array(
        'size'=>'A4', //size is 210mm wide and  297mm high.  all coordinates below are in mm
        'form_height'=>50,
        'rows'=>5,
        
        ),
    'elements'=> array(
        0=>array(
            'type'=>'image',
            'definition'=>array(
                'image'=>'form://photo+image',
                'horiz_min'=>10,
                'vert_min'=>10,
                'horiz_max'=>50,
                'vert_max'=>50
                )
            ),/*
        1=>array(
            'type'=>'image',
            'definition'=>array(
                'image'=>dirname(__FILE__) . "/moh_logo.png",
                'horiz_min'=>0,
                'vert_min'=>100,
                )
            ),
        2=>array(
            'type'=>'text',
            'definition'=>array(
                'horiz_min'=>40,
                'vert_min'=>120,
                'printf'=>'Mulago National Referral  Hospital'
                )
            ),

        */
        10=>array(
            'type'=>'text',
            'definition'=>array(
                'horiz_min'=>60,
                'vert_min'=>5,
                'printf'=>'Name: %s, %s',            
                'printf_args'=>array(
                    0=>'person+surname',
                    1=>'person+firstname'
                    )
                )
            ),
            
           
        11=>array(
            'type'=>'text',
            'definition'=>array(
                'horiz_min'=>60,
                'vert_min'=>10,
                'printf'=>'Birth Date: %s',            
                'printf_args'=>array(
                    0=>'demographic+birth_date',
                    )
                )
            ),
            
        12=>array(
            'type'=>'text',
            'definition'=>array(
                'horiz_min'=>60,
                'vert_min'=>15,
                'printf'=>'Post: %s',            
                'printf_args'=>array(
                    0=>'position+title',
                    )
                )
            ),
            
         13=>array(
            'type'=>'text',
            'definition'=>array(
                'horiz_min'=>60,
                'vert_min'=>20,
                'printf'=>'Department: %s',            
                'printf_args'=>array(
                    0=>'department+name',
                    )
                )
            ),
        
        14=>array(
            'type'=>'text',
            'definition'=>array(
                'horiz_min'=>60,
                'vert_min'=>30,
                'printf'=>'Computer Number: %s',            
                'printf_args'=>array(
                    0=>'computer_no+id_num',
                    )
                )
            ),
            
        15=>array(
            'type'=>'text',
            'definition'=>array(
                'horiz_min'=>60,
                'vert_min'=>35,
                'printf'=>'Date of Appointment: %s',            
                'printf_args'=>array(
                    0=>'primary_form+start_date',   //person_position is the primary_form in the relationship
                    )
                )
            )    
        )
    );




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
 * The page wrangler
 * 
 * This page loads the main HTML template for the home page of the site.
 * @package iHRIS
 * @subpackage DemoManage
 * @access public
 * @author Carl Leitner <litlfred@ibiblio.org>
 * @copyright Copyright &copy; 2007, 2008 IntraHealth International, Inc. 
 * @since Demo-v2.a
 * @version Demo-v2.a
 */


//$dir = getcwd();
//chdir("../pages");
$i2ce_site_user_access_init = null;
$i2ce_site_user_database = null;
require_once( getcwd() . DIRECTORY_SEPARATOR . 'config.values.php');

$local_config = getcwd() . DIRECTORY_SEPARATOR .'local' . DIRECTORY_SEPARATOR . 'config.values.php';
if (file_exists($local_config)) {
    require_once($local_config);
}

if(!isset($i2ce_site_i2ce_path) || !is_dir($i2ce_site_i2ce_path)) {
    echo "Please set the \$i2ce_site_i2ce_path in $local_config";
    exit(55);
}

require_once ($i2ce_site_i2ce_path . DIRECTORY_SEPARATOR . 'I2CE_config.inc.php');

I2CE::raiseMessage("Connecting to DB");
putenv('nocheck=1');
if (isset($i2ce_site_dsn)) {
    @I2CE::initializeDSN($i2ce_site_dsn,   $i2ce_site_user_access_init,    $i2ce_site_module_config);         
} else if (isset($i2ce_site_database_user)) {    
    I2CE::initialize($i2ce_site_database_user,
                     $i2ce_site_database_password,
                     $i2ce_site_database,
                     $i2ce_site_user_database,
                     $i2ce_site_module_config         
        );
} else {
    die("Do not know how to configure system\n");
}

I2CE::raiseMessage("Connected to DB");

require_once($i2ce_site_i2ce_path ."/tools/CLI.php");





$output_dir = "../bulk_pdf/run_" . date("Ymd_H_i");
exec("mkdir -p $output_dir");
exec("mkdir -p $output_dir/individuals");


if (! ( trim(`which pdftk`))) {
    die("Please run 'sudo apt-get install pdftk'\n");
}



$std_form = "bulk_pdf" ;

$stdConfig = I2CE::getConfig()->traverse( '/modules/PrintedForms/forms', true);
$bulkConfig = $stdConfig->traverse($std_form);
if ($bulkConfig instanceof I2CE_MagicDataNode) {
    $bulkConfig->erase();
}
$stdConfig->$std_form = $pdf_layout;
$bulkConfig = $stdConfig->traverse($std_form);


I2CE::raiseMessage("Gathering all position for facility|$fac_id");
$where = 
    array(
        'field'=>'facility',
        'operator'=>'FIELD_LIMIT',
        'style'=>'equals',
        'data'=>array(
            'value'=>'facility|' . $fac_id
            )
        );
$pos_ids = I2CE_FormStorage::listFields('position', array('department'),false, $where);


$ids = array();
foreach ($pos_ids as $pos_id=>$data) {
    //get the department
    if (!is_array($data) || !array_key_exists('department',$data) || !$data['department']) {
        continue;
    }
    list($dept_form,$dept_id) = array_pad(explode("|",$data['department'],2),2,'');
    if (!$dept_id) {
        continue;
    }
    $dept = I2CE_FormStorage::lookupField('department',$dept_id);
    if (!$dept) { 
        continue;
    }
    $where = array(
        'operator'=>'AND',
        'operand'=>array(
            0=>array(
                'operator'=>'FIELD_LIMIT',
                'field'=>'position',
                'style'=>'equals',
                'data'=>array(
                    'value'=>'position|' . $pos_id
                    )
                ),
            1=>array(
                'operator'=>'FIELD_LIMIT',
                'field'=>'end_date',
                'style'=>'null',
                'data'=>array()
                ),
            )                
        );
    //$pers_pos = I2CE_FormStorage::search('person_position',false,$where,array('-start_date'),1);        
    $pers_pos_data = I2CE_FormStorage::listFields('person_position',array('parent'),true,$where,array('-start_date'),1);        
      if (!is_array($pers_pos_data) || count($pers_pos_data) != 1) {
        continue;
    }
    reset($pers_pos_data);
    $pers_pos = key($pers_pos_data);
    $pers_pos_data = current($pers_pos_data);
    if (!is_array($pers_pos_data) || !array_key_exists('parent',$pers_pos_data) || !$pers_pos_data['parent'] || $pers_pos_data['parent'] == '|') {
        continue;
    }
    $pers_id = substr($pers_pos_data['parent'],7);
    $where = 
        array(
            'field'=>'id',
            'operator'=>'FIELD_LIMIT',
            'style'=>'equals',
            'data'=>array(
                'value'=>$pers_id
                )
            );
    $p_data = I2CE_FormStorage::listFields('person',array('firstname','surname'),true,$where,array(),1);        
    if (!is_array($p_data) || count($p_data) != 1) {
        continue;
    }
    reset($p_data);
    $p_data = current($p_data);
    if (!is_array($p_data)) {
        continue;
    }
    $person_name = $p_data['surname'] . ' ' . $p_data['firstname'];
    //now ccheck to see if there is a passport
    $ph_where = 
        array(
            'field'=>'id',
            'operator'=>'FIELD_LIMIT',
            'style'=>'equals',
            'data'=>array(
                'value'=>'person|' . $pers_id
                )
            );
    $ph_data = I2CE_FormStorage::search('person_photo_passport',false,$ph_where);        
    if (!is_array($ph_data) || count($ph_data) == 0) {
        continue;
    }

    $ids[$dept . 'ZZZZZ' . $person_name . rand(0,1000)] = "person_position|" .$pers_pos;
}
I2CE::raiseError("Sorting by person name");
ksort($ids);

I2CE::raiseMessage("Rendering PDF on ids:\n " . implode(" " , $ids));

$count = 1;
$t_ids = array();
foreach ($ids as $id) {
    $t_ids[] = $id;
    if (($count % $pdf_layout['layout_detaila']['rows'] ) == 0) {
        if (!render($t_ids,$count)) {
            I2CE::raiseMessage("Could not render for person_position $id");
        }
        $t_ids = array();
    }
    $count++;
}
if (count($t_ids) != 0 ){
    if (!render($t_ids,$count+1)) {
        I2CE::raiseMessage("Could not render for person_position $id");
    }
    
}

I2CE::raiseMessage("Trying to produce directory in $output_dir/directory.pdf");
exec("pdftk $output_dir/individuals/* output $output_dir/directory.pdf");
I2CE::raiseMessage("Hopefully produced directory in $output_dir/directory.pdf");

if ( ( trim(`which evince`) && simple_prompt("Show directory?"))) {
    exec( "sh  -c  " . escapeshellarg("evince $output_dir/directory.pdf > /dev/null 2>&1 &"));
}






function render($id,$count) {
    global $output_dir;
    global $std_form;
    if (!is_array($id)) {
        $id = array($id);
    }
    echo "Rendering " . implode(" ", $id) . "\n";
    $renderObj = new I2CE_PrintedForm_Render_PDF($std_form,$id);
    if (!$renderObj->render()) {
        I2CE::raiseMessage("Error rendering");
        return false;
    }
    $contents =  $renderObj->display(true);
    if (!$contents) {
        return false;
    }
    $filename = $output_dir . "/individuals/bulk_$count.pdf";
    file_put_contents($filename,$contents);
    return true;
}
