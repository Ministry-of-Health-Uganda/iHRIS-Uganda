<?php

/**
 *  You need to make the variable $fields in I2CE_Form public!
 *  You need to make the variable $top_link in I2CE_FormField public!
 *  You need to make the variable $map_data in I2CE_FormField public!
 *  You need to make the variable $limit_forms in I2CE_ReportType public!
 *  You need to make the variable $fields in I2CE_Report public!
 *  You need to make the variable $options in I2CE_ReportForms public!
 */

$path_to_i2ce_old_site = '/home/litlfred/strange/repo/main/ihris-manage/sites/Demo/pages/main.inc.php';
$path_to_i2ce_old_site = '/home/litlfred/strange/repo/main/ihris-qualify/sites/Demo/pages/main.inc.php';
$path_to_i2ce_old_base = '/home/litlfred/strange/repo/main/';

require_once($path_to_i2ce_old_site);

require_once ("Console/Getopt.php");  
$cg = new Console_Getopt();  
$args = $cg->readPHPArgv(); 
$dir = getcwd(); 
array_shift($args );
if (count($args) != 2) {
    echo "Usage:  XXXXPageReportYYYY.php module";
    die();
}
$file = $args[0]; 
$module = $args[1]; 
require_once( $file);  
@mkdir($module.'-reports');
$className = basename($file,'.php'); 
$reportPage = new $className('convert',''); 
$reportPage->setup(); 
$factory = I2CE_ReportFactory::instance(); 
$typeIDs = array_keys($factory->getTypes()); 
foreach ($typeIDs as $typeID) { 
    $dom = new DOMDocument();
    $dom->loadXML(file_get_contents('report_bones.xml'));

    report_meta_data($dom,$module,$typeID);
        
    $typeObj = $factory->getType($typeID); 
    report_type_data($dom,$module,$typeObj);


    $reportIDs = array_keys($typeObj->getReports());
    foreach ($reportIDs as $reportID) {
        $reportPage->setup($typeID,$reportID);                                          
        $typeObj = $factory->getType($typeID);
        $reportObj = $typeObj->getReport($reportID);
        report_report_data($dom,$module,$typeObj,$reportObj);
    }
    report_type_data2($dom,$module,$typeObj); 
    //make sure we get the freshly created typeObj and reportObj for the IDs.
    @mkdir ("$module-reports/$typeID");
    $text = $dom->saveXML();
    $tidy = new tidy();
    $config = array(
        'input-xml'=>true,
        'output-xml'=>true,
        'indent'=>true,
        'wrap'=>0,
        );
    $tidy->isXML();
    $tidy->parseString($text,$config,'UTF8');
    $tidy->cleanRepair();
    $text = tidy_get_output($tidy);
    file_put_contents("$module-reports/$typeID/ihris_{$module}_report_{$typeID}.xml",$text);
    $class_file_in = file ( 'I2CE_Module_Report_UUUUUU.php');
    $class_file_out = fopen( "$module-reports/$typeID/iHRIS_Module_Report_" . ucwords($module) . '_' .ucwords($typeID) . '.php','w');
    foreach ($class_file_in as $line) {
        $line = preg_replace('/UUUUUU/',ucwords($module) . '_' . ucwords($typeID),$line);
        $line = preg_replace('/LLLLLL/',$typeID,$line);
        fwrite($class_file_out,$line);                  
    }
    fclose($class_file_out);
    @mkdir ("$module-reports/$typeID/templates");
    copy ($path_to_i2ce_old_base . "/ihris-$module/templates/report_$typeID.html", "$module-reports/$typeID/templates/report_$typeID.html");
}








function report_meta_data($dom,$module,$typeID) {
    $xpath = new DOMXPath($dom);
    $config = $xpath->query('//I2CEConfiguration')->item(0);
    $config->setAttribute('name',"ihris-$module-report-{$typeID}");
    $metaNode = $xpath->query('//metadata')->item(0);
    echo $metaNode->length;
    $node = $xpath->query('.//displayName',$metaNode)->item(0);
    set_text($node,ucwords($typeID) . ' Report');
    $node = $xpath->query('.//className',$metaNode)->item(0);
    set_text($node , 'iHRIS_Module_Report_' . ucwords($module) .'_' .ucwords($typeID));
    $node = $xpath->query('.//requirement',$metaNode)->item(0);
    $node->setAttribute('name','ihris-' . $module . '-reports');
        
}


function get_value_node($node) {
    $xpath = new DOMXPath($node->ownerDocument);
    return $xpath->query('./value',$node)->item(0);
}

function set_value($name,$value,$node,$deep=true) {
    $snode = get_config($name,$node,$deep);
    set_text(get_value_node($snode),$value);
}

function set_text($node,$text) {
    $node->appendChild($node->ownerDocument->createTextNode($text));
}

function get_config($name,$node,$deep = true) {
    $xpath = new DOMXPath($node->ownerDocument);
    if ($deep) {
        $res = $xpath->query(".//configurationGroup[@name='$name']",$node)->item(0);
        if ($res === null) {
            $res = $xpath->query(".//configuration[@name='$name']",$node)->item(0);
        }
        return $res;
    } else {
        $res = $xpath->query("./configurationGroup[@name='$name']",$node)->item(0);
        if ($res === null) {
            $res = $xpath->query("./configuration[@name='$name']",$node)->item(0);
        }
        return $res;
    }
}


function __create_element($node,$name) {
    if (! $node instanceof DOMNode) {
        print_r(debug_backtrace());
        die();
    }
    $snode = $node->ownerDocument->createElement($name,'');
    $node->appendChild($snode);
    return $snode;
}

function add_element($name,$node,  $attrs=null) {
    $snode = __create_element($node,$name);
    if (is_string($attrs)) {
        $attrs = array('name'=>$attrs);
    }
    if (is_array($attrs)) {
        foreach($attrs as $n=>$v) {
            $snode->setAttribute($n,$v);
        }
    }
    return $snode;
}

function add_text($name,$text,$node) {
    $snode = __create_element($node,$name);
    $node->appendChild($snode); 
    set_text($snode,$text);
    return $snode;
}

function human_text($text) {
    return ucwords(preg_replace('/[-_]/',' ',$text ));
}

function report_type_data($dom,$module,$typeObj) { 
    $typeID = $typeObj->getType();
    $xpath = new DOMXPath($dom);
    $config  = $xpath->query('/I2CEConfiguration/configurationGroup')->item(0);
    $config->setAttribute('name',"ihris-$module-report-$typeID");
    $config->setAttribute('path',"/modules/report/types/$typeID");

    //type name
    set_value('display_name', ucwords($typeID) . ' Report' , $config);

        
    //primary
    $pnode = get_config('primary',$config);
    set_value('form', $typeObj->getForm(),$pnode);
    set_value('field', $typeObj->getField(),$pnode);
        
    //where clause
    $where = $typeObj->getWhere();
    if (is_array($where)) {
        $wnode  =add_element('configurationGroup',$pnode,'where');
        add_text('displayName','Where Clauses',$wnode);
        add_text('description','Each where clause is \'and\'ed together. ',$wnode);
        add_text('status','required:false',$wnode);
        add_text('status','advanced:true',$wnode);                      
        foreach ($where as $field=>$data) {
            if (isset($data['comparison'])) {
                $cnode = add_element('configurationGroup',$wnode,$field);
                add_text('displayName', human_text($field),$cnode);
                $ccnode = add_element('configuration',$cnode,array('name'=>'comparison','values'=>'single')); 
                add_text('displayName', 'Comparison' ,$ccnode); 
                add_text('description','The MySQL operator used for the comparison',$ccnode); 
                add_text('value',$data['comparison'],$ccnode); 

                $ccnode = add_element('configuration',$cnode,array('name'=>'value','values'=>'single')); 
                add_text('displayName','Value',$ccnode);
                add_text('description','The value used for comparing the  field.  There are special values of MIN and MAX',$ccnode);
                if ($data['value'] == null) {
                    add_text('value','NULL',$ccnode);
                } else {
                    add_text('value',$data['value'],$ccnode);
                }
            } else if (isset($data['qry'])) {
                $cnode = add_element('configurationGroup',$wnode,$field);
                add_text('displayName', human_text($field),$cnode);

                $ccnode = add_element('configuration',$cnode,array('name'=>'qry','values'=>'single')); 
                add_text('displayName', 'Query' ,$ccnode); 
                add_text('description','A MySQL query.  The field name should be put in angle brackets -- for example &lt;end_date&gt; IS ?',$ccnode); 
                add_text('value',$data['qry'],$ccnode); 

                $num_questions = substr_count($data['qry'],'?');
                if (count($data['values'] != $num_questions)) {
                    die("We found $num_questions in {$data['qry']} but have " . count($data['values']) . " values to fill with.  These numbers should be the same. Here is what er have for the values:" .
                        print_r($data['values'],true));
                    
                }

                $vcnode = add_element('configuration',$cnode,array('name'>'values','values'=>'many'));
                add_text('displayName', 'Values',$vcnode);
                add_text('description','The values for the MySQL query.  There should be one for each ? in the query',$ccnode); 
                foreach ($data['values'] as $value) {
                    add_text('value',$value,$vcnode); 
                }
            } else {
                continue;
            }
        }
    } 

    //uses parent
    $parNode = get_config('usesParent',$pnode);
    switch($typeObj->useParent()) {
    case 1:
        add_text('value','primary',$parNode);
        break;
    case 2:
        add_text('value','secondary',$parNode);
        break;
    default: 
        break;
    }


}
function report_type_data2($dom,$module,$typeObj) { 
    $typeID = $typeObj->getType();
    $xpath = new DOMXPath($dom);
    $config  = $xpath->query('/I2CEConfiguration/configurationGroup')->item(0);

    //the forms used by the report
    $fnode = get_config('forms',$config);
    $forms = $typeObj->getForms();
    foreach ($forms as $form_name=>$form) {
        $ffnode = add_element('configurationGroup',$fnode,$form_name);
        add_text('displayName',human_text($form_name) . ' Form',$ffnode);
        $joinForm = $typeObj->getJoinForm($form_name);
        if ($joinForm instanceof I2CE_ReportForm) {
            $joins = $joinForm->getJoin(false);                 
            if (!is_array($joins)) {
                $joins = array();
            }
            $jnode = add_element('configurationGroup',$ffnode,'joins');
            add_text('displayName','Joins',$jnode);
            foreach ($joins as $n=>$join) {
                $jjnode = add_element('configurationGroup',$jnode,$n);
                add_text('displayName','Join on ' . human_text($n),$jjnode);
                add_join($jjnode,$join);                                        
            }                           
            $options = $joinForm->getOptions();
            if (!is_array($options)) {
                $options = array();
            }
            $onode = add_element('configurationGroup',$ffnode,'option_joins');
            add_text('displayName','Option Joins',$onode);
            add_text('description','Set the option join to the given form and set the header prefix when using this option join',$onode);
            foreach ($options as $n=>$join) {
                $join_data = $joinForm->options[$join];
                echo "Join $n for $join:\n";
                print_r($join_data);
                $header = $typeObj->getHeader($form_name,$join);
                $oonode = add_element('configurationGroup',$onode,$join);
                add_text('displayName',human_text($join),$oonode); 
                add_text('description','Option Join on ' . human_text($join),$oonode);  
                if ($header) {
                    $ooonode = add_element('configuration',$oonode,array('name'=>'header','values'=>'single'));
                    add_text('displayName','The header prefix',$ooonode);
                    add_text('status','required:false',$ooonode);
                    add_text('value',$header,$ooonode);
                }
                $wnode = add_element('configurationGroup',$oonode,'joins');
                add_text('displayName','Joins',$wnode);
                foreach ($join_data as $name=>$val) {
                    if (empty($name)) { 
                        continue;
                    }
                    echo "   We are Adding $name=>" . print_r($val,true) ."\n";
                    $jjnode = add_element('configurationGroup',$wnode,$name);
                    add_text('displayName','Join on ' . human_text($name),$jjnode);
                    add_join($jjnode,$val);
                }
            }
        }
        $fields = $form->getFields();
        if (!is_array($fields)) {
            $fields = array();
        }
        $fldnode = add_element('configurationGroup',$ffnode,'fields');
        add_text('displayName','Fields used by this form',$fldnode);
        add_text('status','required:true',$fldnode);
        foreach ($fields as $field) {
            $header = $typeObj->getHeader($form_name,$field);
            if ($header) {
                $flddnode = add_element('configurationGroup',$fldnode,$field);
                add_text('displayName',human_text($field),$flddnode);
                $fldenode = add_element('configuration',$flddnode,array('name'=>'header','values'=>'single'));
                add_text('displayName','The Header',$fldenode);
                add_text('value',$header,$fldenode);
            }                           
        }
                
    }


    $types = array(
        1=>'INT',
        2=>'INT_GENERATE',
        3=>'STRING_LINE',
        4=> 'STRING_MLINE',
        5=> 'STRING_TEXT',
        6=> 'STRING_PASS',
        7=> 'DATE_YMD',
        8=> 'DATE_MD',
        9=> 'DATE_Y',
        10=> 'DATE_HMS',
        11=> 'DATE_TIME',
        12=> 'BOOL',
        13=> 'YESNO',
        14=> 'INT_LIST',
        15=> 'CURRENCY'
        );
    //now do report limits
    $lnode = get_config('limits',$config);
    $llnode = get_config('fields',$lnode);
    //  $limit_form = I2CE_ReportFactory::createLimitForm( $type, $report );
    $limit_form = $typeObj->getLimitObj();
    $skip_fields = array('per_page'=>true,'type'=>true,'report'=>true,'sort'=>true,'page'=>true);
    foreach ($limit_form->fields as $field_name=>$field) {
        if ($skip_fields[$field_name]) {
            continue;
        }
        $ffnode = add_element('configurationGroup',$llnode,$field_name);
        add_text('displayName',human_text($field_name),$ffnode);
        $type = $types[$field->getType()];
        $fffnode = add_element('configuration',$ffnode,array('name'=>'type','values'=>'single'));
        add_text('displayName','Field Type',$fffnode);
        add_text('value',$types[$field->getType()],$fffnode);
        if ($field->isInDB()) {
            $fffnode = add_element('configuration',$ffnode,array('name'=>'inDB','values'=>'single','type'=>'boolean'));
            add_text('displayName','Store in Database',$fffnode);
            add_text('value','true',$fffnode);
        }
        if ($field->isMap()) {
            $fffnode = add_element('configuration',$ffnode,array('name'=>'setMap','values'=>'single','type'=>'boolean'));
            add_text('displayName','Data values are mapped',$fffnode);
            add_text('value','true',$fffnode);
                        
        }
        if($field->top_link) {                  
            $fffnode = add_element('configurationGroup',$ffnode,'link');
            add_text('displayName','Link',$fffnode);
            $gnode = add_element('configuration',$fffnode,array('name'=>'field','values'=>'single'));
            add_text('displayName','Limit Field',$gnode);
            add_text('value',$field->top_link->getName(),$gnode);
            $gnode = add_element('configuration',$fffnode,array('name'=>'link_func','values'=>'single'));
            add_text('displayName','Link Function',$gnode);
            add_text('value',$field->map_data['link'],$gnode);
        }
    }
    $forms = $typeObj->getForms();

    $llnode = get_config('limit_forms',$lnode);
    foreach ($typeObj->limit_forms as $limit_form) {
        add_text('value',$limit_form,$llnode);
                
    }
    //limit match       

    $limit_matches = $typeObj->getLimitMatch();
    $llnode = get_config('limit_match',$lnode);
    foreach ($limit_matches as $limit=>$limit_match) {
        if (is_string($limit_match)) {
            $lllnode = add_element('configuration',$llnode,array('name'=>$limit,'values'=>'single'));
            add_text('displayName',$limit,$lllnode);
            add_text('value',$limit_match,$lllnode);
        } else if (is_array($limit_match)) {
            $lllnode = add_element('configurationGroup',$llnode,$limit);
            add_text('displayName',$limit,$lllnode);
            $bnode = add_element('configuration',$lllnode,array('name'=>'comparison','values'=>'single'));
            add_text('displayName','SQL Comparison Operator. Defaults to =',$bnode);
            add_text('status','required:false',$bnode);
            add_text('value',$limit_match['comparison'],$bnode);
            if (isset($limit_match['func'])) {
                $bnode = add_element('configuration',$lllnode,array('name'=>'func','values'=>'single'));
                add_text('displayName','Function To Compare.  If no function is given, then the comparison field is used',$bnode);
                add_text('status','required:false',$bnode);                     
                add_text('value',$limit_match['func'],$bnode);
            } else if (isset($limit_match['field'])) {
                $bnode = add_element('configuration',$lllnode,array('name'=>'field','values'=>'single'));
                add_text('displayName','Comparison Field',$bnode);
                add_text('value',$limit_match['field'],$bnode);                 
            }
        }
                
    }   
}

 


function add_join($node,$join,$name=null) {
    if (is_string($join) || is_numeric($join)) {
        if ($name === null) {
            $jnode = add_element('configuration',$node,array('name'=>'value','values'=>'single'));
            add_text('displayName','The Value',$jnode);
            add_text('value',$join,$jnode);
        } else {
            $jnode = add_element('configuration',$node,array('name'=>$name,'values'=>'single'));
            add_text('displayName','The Value',$jnode); 
            add_text('value',$join,$jnode);                     

        }
    } else if (is_array($join)) {
        if (isset($join['form'])) {
            $jnode = add_element('configuration',$node, array('name'=>'form','values'=>'single'));
            add_text('displayName','The Form',$jnode);
            add_text('value',$join['form'],$jnode);
            $jnode = add_element('configuration',$node, array('name'=>'field','values'=>'single'));
            add_text('displayName','The Field',$jnode);
            add_text('value',$join['field'],$jnode);
        } else if (isset($join['comparison'])) {
            $jnode = add_element('configuration',$node,array('name'=>'comparison','values'=>'single'));
                                                        
            add_text('displayName', 'Comparison' ,$jnode); 
            add_text('description','The MySQL operator used for the comparison',$jnode); 
            add_text('value',$join['comparison'],$jnode); 
            $jnode = add_element('configuration',$node,array('name'=>'value','values'=>'single')); 
            add_text('displayName','Value',$jnode);
            add_text('description','The value used for comparing the  field.  There are special values of MIN and MAX',$jnode);
            if ($join['value'] == null) {
                add_text('value','NULL',$jnode);
            } else {
                add_text('value',$join['value'],$jnode);
            }                                                   
        }
    }
}


function report_report_data($dom,$module,$typeObj,$reportObj) {  
    $typeID = $typeObj->getType();
    $xpath = new DOMXPath($dom);
    $config  = $xpath->query('/I2CEConfiguration/configurationGroup')->item(0);
    $node = get_config('reports',$config);
    echo "Report " . $reportObj->getTitle() . "\n";
        
    $rnode = add_element('configurationGroup',$node,$reportObj->getReport());
    add_text('displayName',$reportObj->getTitle(),$rnode);
    add_text('description',$reportObj->getTitle() . " Report",$rnode);
    $cnode = add_element('configuration',$rnode,array('name'=>'display_name','values'=>'single'));
    add_text('displayName','Report Title',$cnode);
    add_text('description','The title of the report',$cnode);
    add_text('status','require:true',$cnode);
    add_text('value',$reportObj->getTitle(),$cnode);

    $fnode = add_element('configurationGroup',$rnode,'report_fields');
    add_text('displayName','Report Fields',$fnode);
    add_text('description','The fields displayed in this report',$fnode);


    foreach ($reportObj->fields as $form=>$fields) {
        foreach ($fields as $field=>$data) {
            echo "field $field has data:\n";
            print_r($data);
            $ffnode = add_element('configurationGroup',$fnode,$field);
            add_text('displayName',human_text($field),$ffnode);
                        
            $tnode = add_element('configuration',$ffnode,array('name'=>'form','values'=>'single'));
            add_text('displayName','Form',$tnode);
            add_text('value',$form,$tnode);

            $tnode = add_element('configuration',$ffnode,array('name'=>'field','values'=>'single'));
            add_text('displayName','Field',$tnode);
            add_text('value',$data['field'],$tnode);

            $tnode = add_element('configuration',$ffnode,array('name'=>'option','values'=>'single'));
            add_text('displayName','Option',$tnode);
            add_text('status','required:false',$tnode);
            if (is_string($data['option']) && (strlen($data['option']) > 0)) {
                echo "Setting option for " .$field . " to " . $data['option'] . "\n";
                add_text('value',$data['option'],$tnode);
            }

        }
    }




    $dnode = add_element('configurationGroup',$rnode,'report_displayed_fields');
    add_text('displayName','Field Displays',$dnode);
    add_text('description','The fields of the report which are displayed',$dnode);
    add_text('status','required:false',$dnode);
    if ($reportObj->isChart()) {
        $aggregates = array('AVG','SUM','COUNT','MAX','MIN','STD','VAR','BIT_','GROUP_CONCAT');
        $charts= $reportObj->getChart();
        $displays = array();
        foreach ($charts as $field=>$func) {
            if ($field == $func) {
                $displays[$field] = $field;
            } else {
                $displays[$field] =array(
                    'func'=>$func,
                    'header'=>$typeObj->getHeader('chart',$field)
                    ); 
                $aggregate = false;
                $func = strtoupper($func);
                foreach ($aggregates  as $a) {
                    if (strpos($func,$a) !== false) {
                        $aggregate = true;
                    }
                }
                if ($aggregate) {
                    $displays[$field]['aggregate']= true;
                }
            }
        }
    } else {
        $displays = $reportObj->getDisplay();
        $displays = $displays['fields'];
    }

        

    foreach ($displays as $field=>$details) {
        $ffnode = add_element('configurationGroup',$dnode,$field);
        add_text('displayName',human_text($field) ,$ffnode);
        add_text('description', 'The ' . human_text($field) . ' field',$ffnode);
        $fffnode = add_element('configuration',$ffnode,array('name'=>'header','values'=>'single'));
        add_text('displayName','Field Header',$fffnode);
        add_text('description', 'A field header that override the field header, if any, set for the field in the report type',$fffnode);
        if (is_array($details) && isset($details['header'])) {
            add_text('value',$details['header'],$fffnode);
        }
        $fffnode = add_element('configuration',$ffnode,array('name'=>'function','values'=>'single'));
        add_text('displayName','SQL Function',$fffnode);
        add_text('description','A sql function called to generate this report field.  If not set, it uses the field ' . $field,$fffnode);
        if (is_array($details) && isset($details['func'])) {
            add_text('value',$details['func'],$fffnode); 
        }

        $fffnode = add_element('configuration',$ffnode,array('name'=>'link','values'=>'single'));
        add_text('displayName','Link',$fffnode);
        add_text('description','A URL used to generate a link for this report field.' . $field,$fffnode);               
        foreach ($reportObj->fields as $form=>$fields) {
            foreach ($fields as $f=>$data) {
                if ($f != $field) {
                    continue;
                }
                $report_form = $typeObj->getJoinForm($form);
                $link = $report_form->getFieldLink($field);
                if ($link && is_string($link)) {
                    echo "$form $field has link $link\n";
                    add_text('value',$link,$fffnode); 
                }
            }
        }

        $fffnode = add_element('configuration',$ffnode,array('name'=>'aggregate','values'=>'single','type'=>'boolean'));
        add_text('displayName','Aggregate Function',$fffnode);
        add_text('description','Set to true if there is a SQL function defining this field and it is an aggregate function ',$fffnode);
        if (is_array($details) && isset($details['aggregate']) && $details['aggregate']) {
            add_text('value','true',$fffnode);
        }
    }




    //chart where.
    $where = $reportObj->getWhere(); 
    $fnode = add_element('configuration',$rnode,array('name'=>'where','values'=>'many'));
    add_text('displayName','Where Clauses',$fnode);
    add_text('description','Limit the report type by the given where clauses',$fnode);
    add_text('status','required:true',$fnode);  
    foreach ($where as $w) { 
        add_text('value',$w,$fnode); 
    }
        


    //default display
    $dnode = add_element('configurationGroup',$rnode,'display_options');
    add_text('displayName','Displays',$dnode);
    add_text('description','The options for the various report displays',$dnode);
    $ddnode = add_element('configurationGroup',$dnode,'default');
    add_text('displayName','Default Display',$ddnode);
    add_text('description','The options for the default report displays',$ddnode);
    add_text('status','required:false',$ddnode);        
    $snode = add_element('configuration',$ddnode,array('name'=>'sort','values'=>'many'));
    add_text('displayName','Default Sort Order',$snode);
    add_text('description','A list of the fields we wish to, by default, sort by',$snode);
    $sorts = $reportObj->getSort();
    foreach ($sorts as $sort) {
        add_text('value',$sort,$snode);
    }
        

       

                             
}  







# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
