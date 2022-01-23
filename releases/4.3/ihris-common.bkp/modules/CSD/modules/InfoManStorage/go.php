<?php
$files = array();
exec('grep -rsl uuid * | grep -v \~ | grep xsl',$files);
$funcs = explode("\n",file_get_contents("/Users/litlfred/Desktop/stored_function_map.txt"));
foreach ($files as $file) {
    $data = file_get_contents($file);    
    foreach ($funcs as $func) {
	list($uuid,$urn) = array_pad(explode(" ",$func,2),2,'');
	if (!$uuid || !$urn) {
	    continue;
	}
	$data = str_replace($uuid,$urn,$data);
    }
    $data = str_replace("uuid=","urn=",$data);
    file_put_contents($file,$data);
}