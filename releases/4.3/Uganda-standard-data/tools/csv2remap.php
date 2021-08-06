<?php

$args = getopt( "", array(
            "who::",
            "file:",
) );

$who = ( array_key_exists( "who", $args ) ?
        $args['who'] : 1 );
$file = $args['file'];

$csv = fopen( $file, "r" );
if ( $csv === false ) {
    die( "Can't open file: $file\n" );
}

while ( ($data = fgetcsv( $csv ) ) ) {
    if ( count($data) != 2 ) {
        die( "There should only be two columns, the old value and the new value.\n" );
    }

    $old_value = $data[0];
    $new_value = $data[1];
    if ( !strstr( $old_value, '|' ) ) {
        die( "No | in old value $old_value\n" );
    }
    if ( !strstr( $new_value, '|' ) ) {
        die( "No | in new value $new_value\n" );
    }

    list( $old_form, $old_id ) = explode( '|', $old_value, 2 );
    list( $new_form, $new_id ) = explode( '|', $new_value, 2 );

    if ( $old_form != $new_form ) {
        die ( "Form name mismatch for $old_value and $new_value!\n" );
    }

}
fseek( $csv, 0 );
$temps = array();
$final = array();
while ( ($data = fgetcsv( $csv ) ) ) {
    $old_value = mysql_escape_string( $data[0] );
    $new_value = mysql_escape_string( $data[1] );

    $tables = array( 'last_entry' => 'string_value', 'entry' => 'string_value', 'config_alt' => 'value' );

    foreach( $tables as $table => $field ) {
        $temps[] = "UPDATE $table SET $field = REPLACE( 'temp+" . $old_value . "', '"
            . $old_value . "', $field ) WHERE $field = '" . $old_value . "';\n";
        $final[] = "UPDATE $table SET $field = REPLACE( '" . $new_value . "', 'temp+"
            . $old_value . "', $field ) WHERE $field = 'temp+" . $old_value . "';\n";
        $temps[] = "UPDATE $table SET $field = REPLACE( 'temp+" . $old_value . ",', '"
            . $old_value . ",', $field ) WHERE $field LIKE '" . $old_value . ",%';\n";
        $final[] = "UPDATE $table SET $field = REPLACE( '" . $new_value . ",', 'temp+"
            . $old_value . ",', $field ) WHERE $field = 'temp+" . $old_value . ",%';\n";
        $temps[] = "UPDATE $table SET $field = REPLACE( ',temp+" . $old_value . "', ',"
            . $old_value . "', $field ) WHERE $field LIKE '%," . $old_value . "';\n";
        $final[] = "UPDATE $table SET $field = REPLACE( '," . $new_value . "', ',temp+"
            . $old_value . "', $field ) WHERE $field = '%,temp+" . $old_value . "';\n";
        $temps[] = "UPDATE $table SET $field = REPLACE( ',temp+" . $old_value . ",', ',"
            . $old_value . ",', $field ) WHERE $field LIKE '%," . $old_value . ",%';\n";
        $final[] = "UPDATE $table SET $field = REPLACE( '," . $new_value . ",', ',temp+"
            . $old_value . ",', $field ) WHERE $field = '%,temp+" . $old_value . ",%';\n";
    }

}
echo implode( "", $temps );
echo implode( "", $final );

?>
