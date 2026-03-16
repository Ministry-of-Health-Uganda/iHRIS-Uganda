<?php


$backupdir = ".";

$db_user = '';
$db_pass = '';

// This is the suffix to append to the end of the backup file.
$suffix = date('Y-m-d');

// Only populate this if you'd like to only backup certain databases, otherwise all databases will be backed up
// with a file for each database.
$include_only = array();


$db = mysql_connect( 'localhost', $db_user, $db_pass )
or die( "Could not connect: " . mysql_error() );
mysql_select_db( "information_schema" ) or die ( "Could not select dabase." );

$backups = array();

if ( !is_array( $include_only ) || count( $include_only ) == 0 ) {
    $dbs = mysql_query( "SELECT schema_name FROM schemata where schema_name NOT IN ( 'information_schema', 'mysql' )" )
    or die( "Schema query failed: " . mysql_error() );
    while( $data = mysql_fetch_assoc( $dbs ) ) {
        $backups[] = $data['schema_name'];
    }

    mysql_free_result( $dbs );
} else {
    $backups = $include_only;
}

foreach( $backups as $backup_db ) {

    $use_dir = $backupdir . "/" . $backup_db;
    if ( !is_dir( $use_dir ) ) {
        mkdir( $use_dir );
    }
    $result = mysql_query( "SELECT table_name FROM tables WHERE table_schema = '$backup_db' AND table_name NOT LIKE 'hippo_%' AND table_name NOT LIKE 'zebra_%' AND table_name NOT LIKE 'tmp_custom_report%'" ) 
    or die( "Query failed: " . mysql_error() );

    $tables = array();
    while ( $data = mysql_fetch_assoc( $result ) ) {
        $tables[] = $data['table_name'];
    }
    mysql_free_result( $result );
    exec( "mysqldump -u $db_user --password=$db_pass $backup_db " . implode( " ", $tables ) . " | bzip2 > $use_dir/backup_${backup_db}_$suffix.sql.bz2" );

}

mysql_close($db);

?>
