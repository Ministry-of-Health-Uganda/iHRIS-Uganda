<?php

$args = getopt( "", array(
            "module:",
            "form:",
            "display::",
            "fields:",
            "id::",
            "who::",
            "file:",
            "version:",
            "internalid",
) );

$module = $args['module'];
$form = $args['form'];
$display = ( array_key_exists( "display", $args ) ? 
        $args['display'] : ucfirst( $form ) );
$fields = split( ',', $args['fields'] );
if ( array_key_exists( "id", $args ) ) {
    $id = $args['id'];
} else {
    $id = false;
}
$who = ( array_key_exists( "who", $args ) ?
        $args['who'] : 1 );
$file = $args['file'];
$version = $args['version'];
$internalid = array_key_exists( "internalid", $args );

$keyidx = -1;
if ( $id && ($keyidx = array_search( $id, $fields )) === false ) {
    die( "ID field isn't listed in the fields given!\n" );
}

$last_mod = date( 'Y-m-d H:i:s' );
$csv = fopen( $file, "r" );
if ( $csv === false ) {
    die( "Can't open file: $file\n" );
}

echo <<<HEAD
<?xml version="1.0"?>
<!DOCTYPE I2CEConfiguration SYSTEM "I2CE_Configuration.dtd">
<I2CEConfiguration name="$module">
  <metadata>
    <displayName>$display</displayName>
    <description>Data for form: $form</description>
    <version>$version</version>
    <path name="configs">
      <value>./configs</value>
    </path>
  </metadata>
  <configurationGroup name="$module" path="/I2CE/formsData/forms/$form">
    <displayName>Form data: $form</displayName>

HEAD;

while ( ($data = fgetcsv( $csv ) ) ) {
    if ( count($data) != count( $fields ) ) {
        die( "Data doesn't match given fields: " . print_r( $data, true ) . print_r( $fields, true ) );
    }

    if ( $keyidx >= 0 ) {
        $key = $data[$keyidx];
    } else {
        if ( !$key ) {
            $key = 1;
        } else {
            $key++;
        }
    }

    echo <<<DATAHEAD
    <configurationGroup name="$key">
      <displayName>$key</displayName>
      <configuration name="last_modified">
        <displayName>Last Modified</displayName>
        <value>$last_mod</value>
      </configuration>
      <configuration name="who">
        <displayName>Who</displayName>
        <value>$who</value>
      </configuration>
      <configurationGroup name="fields">
        <displayName>Fields</displayName>

DATAHEAD;

    foreach( $fields as $idx => $field ) {
        if ( $internalid && $idx == $keyidx ) {
            continue;
        }
        $value = $data[$idx];
        echo <<<DATA
        <configuration name="$field">
          <value>$value</value>
        </configuration>

DATA;
    }

    echo <<<DATAFOOT
      </configurationGroup>
    </configurationGroup>

DATAFOOT;

}

echo <<<FOOT
  </configurationGroup>
</I2CEConfiguration>

FOOT;



?>
