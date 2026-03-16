<?php

/**
 * Syntax:  php csv2module.php
 *      --form=FORMNAME
 *      --fields=FIELD1,FIELD2,ETC.
 *      --version=X.X.X
 *      [--module=MODULENAME]
 *      [--display="Module Display Name"]
 *      [--id=FIELD1]
 *      [--who=USERID]
 *      [--file=FILE.CSV]
 *      [--internalid]
 *      [--erase]
 *      [--reqs=REQUIRED_MOD1[:MINVER[:MAXVER]],REQUIRED_MOD2[:MINVER[:MAXVER]]]
 *
 * The output module will be displayed to the console, to save it to a file
 * redirect the output to the filename you'd like to use.
 *
 * The fields must be in the order they appear in the CSV file.
 * If any fields are MAPPED fields, then the mapped value needs to be in the
 * CSV file, e.g. district|10.  No lookups are done during this process, the exact
 * values must be used in the CSV.
 *
 * If no module name is given, the default will be "Default-data-FORMNAME"
 * If no display name is given, the default will be "Default data for: FORMNAME"
 * If no filename is given, the default will be "FORMNAME.csv"
 *
 * The id argument will be the fieldname from the fields argument to use
 * as the id of the form.  If the internalid argument is used, then the
 * id field will not be present as a field for the form and only used as the id.
 * If there is no id field, then an incremented number will be used.
 *
 * The who argument is the userid to use for the forms.  The default is 1.
 *
 * If the erase argument is used then all previous values in magic data
 * will be removed for this form.
 *
 * The reqs argument can be used to set any required modules for this module with
 * an optional minimum and maximum version to be used.
 *
 * A few examples:
 *   php csv2module.php --form=district --fields=code,name,region --id=code --version=4.1.8.0 --erase --reqs=Geography:4.1:4.2 > Default-data-district.xml
 *   The code field will be used as the ID for each district.  
 *   This would read the file district.csv in the current directory 
 *   and create the file Default-data-district.xml.
 *
 *   php csv2module.php --form=marital_status --fields=name --version=4.1.8.0 --erase --reqs=PersonDemographic:4.1:4.2 > Default-data-marital_status.xml
 *   This will use numbers for the ids starting at 1.  If you'd like to include 
 *   your own ids:
 *   php csv2module.php --form=marital_status --fields=id,name --id=id --internalid --version=4.1.8.0 --erase --reqs=PersonDemographic:4.1:4.2 > Default-data-marital_status.xml
 *   These would read the file marital_status.csv in the current directory 
 *   and create the file Default-data-marital_status.xml.
 *
 */

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
            "erase",
            "reqs:",
) );

$form = $args['form'];
$module = ( array_key_exists( "module", $args ) ?
        $args['module'] : "Default-data-" . $form );
$display = ( array_key_exists( "display", $args ) ? 
        $args['display'] : "Default data for: " . $form );
$fields = split( ',', $args['fields'] );
if ( array_key_exists( "id", $args ) ) {
    $id = $args['id'];
} else {
    $id = false;
}
$reqs = array();
if ( array_key_exists( "reqs", $args ) ) {
    foreach( split( ',', $args['reqs'] ) as $req ) {
        $reqs[] = split( ':', $req, 3 );
    }
}
$who = ( array_key_exists( "who", $args ) ?
        $args['who'] : 1 );
$file = ( array_key_exists( "file", $args ) ?
        $args['file'] : $form . ".csv" );
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

echo <<<HEAD1
<?xml version="1.0"?>
<!DOCTYPE I2CEConfiguration SYSTEM "I2CE_Configuration.dtd">
<I2CEConfiguration name="$module">
  <metadata>
    <displayName>$display</displayName>
    <description>Data for form: $form</description>
    <version>$version</version>

HEAD1;

foreach( $reqs as $req ) {
    echo "    <requirement name=\"$req[0]\">\n";
    if ( array_key_exists( 1, $req ) && $req[1] ) {
        echo "      <atLeast version=\"$req[1]\" />\n";
    }
    if ( array_key_exists( 2, $req ) && $req[2] ) {
        echo "      <lessThan version=\"$req[2]\" />\n";
    }
    echo "    </requirement>\n";
}

echo <<<HEAD2
    <path name="configs">
      <value>./configs</value>
    </path>
  </metadata>

HEAD2;
if ( array_key_exists( 'erase', $args ) ) {
    echo <<<ERASE
  <erase path="/I2CE/formsData/forms/$form">
    <lessThan version="$version" />
  </erase>

ERASE;
}
echo <<<HEAD3
  <configurationGroup name="$module" path="/I2CE/formsData/forms/$form">
    <displayName>Form data: $form</displayName>
    <version>$version</version>

HEAD3;

$key = false;
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
