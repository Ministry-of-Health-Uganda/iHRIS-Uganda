<?php


$file = 'iso-currency-codes.html';
$cfile = 'list-en1-semic-3.txt';
$dom  = new DOMDocument();
$dom->loadHTMLFile($file);
$xpath = new DOMXPath($dom);
$dom  = new DOMDocument();
$dom->loadHTMLFile($file);
$xpath = new DOMXPath($dom);


$countries = array();

$ctemplate = '    <configurationGroup name="<<<CODE>>>">
      <configuration name="last_modified">
        <displayName>Last Modified</displayName>
        <value>2009-06-23 09:50:45</value>
      </configuration>
      <configuration name="who">
        <displayName>Who</displayName>
        <value>1</value>
      </configuration>
      <configurationGroup name="fields">
        <displayName>Fields</displayName>
        <configuration name="alpha_two">
          <displayName>Code</displayName>
          <value><<<CODE>>></value>
        </configuration>
        <configuration name="name" locale="en_US">
          <displayName>Name</displayName>
          <value><<<NAME>>></value>
        </configuration>
      </configurationGroup>
    </configurationGroup>

';
$cdata = array();
$codes = array();
foreach ( file($cfile) as $line) {
    $line = explode(';',$line);
    if (count($line) != 2) {
        continue;
    }

    list($country,$code) =$line;
    $country = mb_convert_encoding($country,'UTF-8');

    $code = strtoupper(trim($code));
    if (!array_key_exists($code,$codes)) {
        $codes[$code] = array();
    }
    $codes[$code][] = $country;
    $country = ucwords(strtolower(trim($country)));
    $countries[$country] = $code; 
    $cdata[] = str_replace('<<<NAME>>>',$country,str_replace('<<<CODE>>>',$code,$ctemplate));
}
foreach ($codes as $code=>$cs) {
    if (count($cs) > 1) {
        echo "$code is  repeated\n";
        print_r($cs);
    }
}





$trs = $xpath->query('//tr');
$data = array();


$template = '    <configurationGroup name="<<<CODE>>>">
      <configuration name="last_modified">
        <displayName>Last Modified</displayName>
        <value>2009-06-23 09:50:45</value>
      </configuration>
      <configuration name="who">
        <displayName>Who</displayName>
        <value>1</value>
      </configuration>
      <configurationGroup name="fields">
        <displayName>Fields</displayName>
        <configuration name="code">
          <displayName>Code</displayName>
          <value><<<CODE>>></value>
        </configuration>
        <configuration name="name" locale="en_US">
          <displayName>Name</displayName>
          <value><<<NAME>>></value>
        </configuration>
        <configuration name="country">
          <displayName>Country</displayName>
          <value><<<COUNTRYCODE>>></value>
        </configuration>
      </configurationGroup>
    </configurationGroup>

';
$codes = array();
foreach ($trs as $tr) {
    $tds = $xpath->query('./td',$tr);
    if ($tds->length !=4 ) {
        continue;
    }
    $d = array();
    foreach ($tds as $td) {
        //$d[] =  strtr(trim(htmlentities($td->textContent)),"\n",' ' );
        $d[] =  strtr(trim(htmlentities($td->textContent)),"\n",' ' );
    }
    //entity,currency,alpha3,numer
    $code = strtoupper(substr(trim($d[2]),0,3));
    if (!array_key_exists($code,$codes)) {
        $codes[$code] = array();
    }
    if (!preg_match('/^[A-Z]+$/',$code)) {
        echo "B<$code>\n";
        continue;
    }

    $d[0] = preg_replace('/\s\s*/'," ",$d[0]);
    $country = ucwords(strtolower(trim($d[0])));
    $d[1] = preg_replace('/\s\s*/'," ",$d[1]);
    if (array_key_exists(html_entity_decode($country),$countries)) {
        $countrycode = 'country|' . $countries[html_entity_decode($country)];
    } else {
        echo "Could not find country for: " . print_r($d,true) . "\n";
        $countrycode = '|';
    }

    $data[] = str_replace('<<<COUNTRYCODE>>>',$countrycode,str_replace('<<<NAME>>>',$d[1],str_replace('<<<CODE>>>',$code,$template)));

}
foreach ($codes as $code=>$cs) {
    if (count($cs) > 1) {
        echo "$code is  repeated\n";
        print_r($cs);
    }
}

$cout =  '<?xml version="1.0"?>
<!DOCTYPE I2CEConfiguration SYSTEM "I2CE_Configuration.dtd">
<I2CEConfiguration name="iso-country">
  <metadata>
    <displayName>ISO 3166 Country Codes</displayName>
    <description>ISO 3166 Country Codes</description>
    <version>4.1.0</version>
    <path name="configs">
      <value>./configs</value>
    </path>
    <requirement name="Geography">
      <atLeast version="4.0" />
    </requirement>
  </metadata>
  <configurationGroup name="iso-currency" path="/I2CE/formsData/forms/country">


';
$cout .= implode("\n",$cdata);
$cout .= '  </configurationGroup>
</I2CEConfiguration>';


$out =  '<?xml version="1.0"?>
<!DOCTYPE I2CEConfiguration SYSTEM "I2CE_Configuration.dtd">
<I2CEConfiguration name="iso-currency">
  <metadata>
    <displayName>ISO Currency Codes</displayName>
    <description>ISO Currency Codes</description>
    <version>4.1.0</version>
    <path name="configs">
      <value>./configs</value>
    </path>
    <requirement name="Currency">
      <atLeast version="4.0" />
    </requirement>
    <requirement name="iso-country">
      <atLeast version="4.0" />
    </requirement>
  </metadata>
  <configurationGroup name="iso-currency" path="/I2CE/formsData/forms/currency">


';
$out .= implode("\n",$data);
$out .= '  </configurationGroup>
</I2CEConfiguration>';



//file_put_contents("../../../../Geography/modules/iso-country/ISO_Country.xml",$cout);
file_put_contents("../ISO_Currency.xml",$out);



