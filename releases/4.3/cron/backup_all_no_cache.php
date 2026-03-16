!/usr/bin/php

<?php


$backupdir = "/home/ihrisbackup/backup_no_cache";

$db_user = 'qualify'; 

$db_pass = 'cencer69'; 


$ignore_list = array( 'information_schema', 'mysql', 'phpmyadmin','test' );


$suffix = date('Y-m-d');

$db = mysql_connect( 'localhost', $db_user, $db_pass )
or die( "Could not connect: " . mysql_error() );
mysql_select_db( "information_schema" ) or die ( "Could not select database." );

$dbs = mysql_query( "SELECT schema_name FROM schemata where schema_name NOT IN ( '" . implode( "','", $ignore_list ) . "' )" )
or die( "Schema query failed: " . mysql_error() );
$backups = array();
while( $data = mysql_fetch_assoc( $dbs ) ) {
    $backups[] = $data['schema_name'];
}

mysql_free_result( $dbs );

foreach( $backups as $backup_db ) {

    $use_dir = $backupdir . "/" . $backup_db;
    if ( !is_dir( $use_dir ) ) {
        mkdir( $use_dir );
    }       
    $result = mysql_query( "SELECT table_name FROM tables WHERE table_schema = '$backup_db' AND table_name not like 'hippo_%' AND table_name not like 'zebra_%'" ) 
    or die( "Query failed: " . mysql_error() );
$tables = array();
    while ( $data = mysql_fetch_assoc( $result ) ) {
        $tables[] = $data['table_name'];
    }       
    mysql_free_result( $result );
    echo "Backing up $backup_db...\n";
    exec( "mysqldump -u $db_user --password=$db_pass $backup_db " . implode( " ", $tables ) . " > $use_dir/backup_${backup_db}_$suffix.sql" );
    exec( "bzip2 $use_dir/backup_${backup_db}_$suffix.sql" );
}
echo "Done Backing up ...\n";
mysql_close($db);
exec( "find $backupdir -type f -mtime +7 -not -name \"*-01.sql.bz2\" -print -exec rm {} \;" );
exec( "scp -P 20020 -q " . implode( " ", $backup_to_council ) . " 41.202.232.87:district_backups/" );

?>
