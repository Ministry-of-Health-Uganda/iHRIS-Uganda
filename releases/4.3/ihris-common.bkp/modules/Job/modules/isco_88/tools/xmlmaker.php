#!/usr/bin/php
<?php

main();
exit;

function main() {
  $type = array("major", "sub_major", "minor", "unit");

  $codes = read_csv_files($type);
  write_xml_files($codes, $type);
}

function write_xml_files($codes, $type) {
  foreach($codes as $code => $data) {
    echo "writing xml files for: $code\n";
    write_major_file($code, $data, $type);
  }
}

function write_major_file($code, $data, $type) {
  $pointer = new XMLWriter();
  $time = strftime("%Y-%m-%d %H:%m:%S");

  foreach(array_keys($data) as $group) {
    echo "writing major xml files for: $group\n";
    $dir = realpath(dirname(dirname(__FILE__))."/modules")."/isco_{$code}/modules/".
      "isco_{$code}_major_$group";
    if(!is_dir($dir)) {
      if(!mkdir($dir, 0777, TRUE)) {
        echo "Couldn't make $dir!";
        exit(1);
      }
    }
    $pointer->openURI("file://$dir/majorgroup" . $group . ".xml");
    xml_header($pointer, $code, $group);
    foreach($type as $i => $level) {
      write_group($pointer, $i, $level, $code, $group, $data[$group], $time, $type);
    }
    xml_footer($pointer);
    $pointer->flush();
  }
}

function xml_header($pointer, $code, $major) {
  $pointer->setIndent(TRUE);
  $pointer->setIndentString(" ");
  $pointer->startDocument("1.0", "UTF-8");
  $pointer->writeDTD("I2CEConfiguration", "SYSTEM",  'I2CE_Configuration.dtd');

  $pointer->startElement("I2CEConfiguration");
  $pointer->writeAttribute("name", sprintf("isco-$code-major-%02d", $major));

  $pointer->startElement("metadata");
  $pointer->writeElement("displayName", "ISCO $code Job Codes");
  $pointer->writeElement("description", "The ISCO $code Job Codes");
  $pointer->writeElement("version", "4.0.1");

  $pointer->startElement("requirement");
  $pointer->writeAttribute("name", "isco-$code");

  $pointer->startElement("atLeast");
  $pointer->writeAttribute("version", '4.0');
  $pointer->endElement();

  $pointer->startElement("lessThan");
  $pointer->writeAttribute("version", '4.1');
  $pointer->endElement();
        
  $pointer->endElement(); /* requirement */
  $pointer->endElement(); /* metadata */

  $pointer->startElement("configurationGroup");
  $pointer->writeAttribute("name", 'formsData');
  $pointer->writeAttribute("path", '/I2CE/formsData/forms');
  $pointer->writeElement("displayName", "Forms Data");
  $pointer->writeElement("description", "Form data saved in magic data");
  
}

function xml_footer($pointer) {
  $pointer->endElement();
  $pointer->endElement();
  $pointer->endDocument();
}

function read_csv_files($type) {
  $ret = array();

  foreach(glob(dirname(__FILE__)."/isco*codes.csv") as $file) {
    $match = array();
    preg_match("{isco(\d+)codes.csv}", $file, $match);
    $code = $match[1];
    $ret[$code] = parse_csv_file($file, $type);
  }

  return $ret;
}

function parse_csv_file($name, $type) {
  $ret = array();

  $h = fopen($name, "r");
  if(!$h) {
    echo "Problem opening $name!\n";
    exit(1);
  }

  while (($data = fgetcsv($h, 10000, "?")) !== FALSE) {
    $classification = $data[0];
    $set['code'] = $classification;
    $set['level'] = array();

    while(strlen($classification) > 1) { /* relying on stringification of numbers,
                                            just > 10 won't work because it'd fail
                                            for "01" */
      $remainder = $classification % 10;
      array_unshift($set['level'], $remainder);
      $classification = ($classification - $remainder) / 10;
    }
    array_unshift($set['level'], $classification);
    $set['fieldName'] = trim($data[1]);
    $set['fieldDescription'] = trim($data[2]);

    $ret[$set['level'][0]][$type[strlen($set['code'])-1]][] = $set;
  }
  return $ret;
}

function write_group_header($pointer, $code, $time, $data) {
  $pointer->startElement("configurationGroup");
  $pointer->writeAttribute("name", $code);

  $pointer->startElement("configuration");
  $pointer->writeAttribute("name", "last_modified");
  $pointer->writeElement("value", $time);
  $pointer->endElement();

  $pointer->startElement("configurationGroup");
  $pointer->writeAttribute("name", 'fields');

  $pointer->startElement("configuration");
  $pointer->writeAttribute("name", "name");
  $pointer->writeElement("value", $data["fieldName"]);
  $pointer->endElement();

  $pointer->startElement("configuration");
  $pointer->writeAttribute("name", "description");
  $pointer->writeElement("value", $data["fieldDescription"]);
  $pointer->endElement();
}

function write_group_footer($pointer) {
  $pointer->endElement();       /* configurationGroup */
  $pointer->endElement();       /* configurationGroup */
}

function write_group_oneup($pointer, $code, $type, $bit) {
    $pointer->startElement("configuration");
    $pointer->writeAttribute("name", "isco_{$code}_{$type}");
    $pointer->writeElement("value", "isco_{$code}_{$type}|". $bit);
    $pointer->endElement();
}

function write_group($pointer, $levelNum, $levelName, $code,
                     $group, $data, $time, $type) {
  if(array_key_exists($levelName, $data) && count($data[$levelName]) > 0) {
    $pointer->startElement("configurationGroup");
    $pointer->writeAttribute("name", "isco_{$code}_$levelName");
    foreach($data[$levelName] as $bit) {
      write_group_header($pointer, $bit['code'], $time, $bit);
      if($levelNum > 0) {
        write_group_oneup($pointer, $code, $type[$levelNum - 1],
                          //$bit['level'][$levelNum-1]
                          substr( $bit['code'], 0, -1 )
                          );
      }
      write_group_footer($pointer);
    }
    $pointer->endElement();
  }
}

