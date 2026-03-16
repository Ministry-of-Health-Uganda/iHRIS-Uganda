<?php

if ( $argc != 2 ) {
    die( "Syntax: $0 <CodeList File>\n" );
}
$file = $argv[1];

if ( !$file || !is_readable( $file ) ) {
    die( "Can't read $file!\n" );
}

$doc = new DOMDocument();
if ( !$doc->load( $file ) ) {
    die ( "Couldn't open $file!\n" );
}

$xpath = new DOMXPath( $doc );

$found = array();
$codelists = $xpath->query( "//structure:CodeList[@id!='']" );
foreach( $codelists as $codelist ) {
    $cl_id = $codelist->getAttribute( "id" );
    $name = $xpath->query( "structure:Name", $codelist );
    if ( $name->length != 1 ) {
        die( "Couldn't find the name for $cl_id!\n" );
    }
    $found[$cl_id] = $name->item(0)->textContent;
}

$task_desc = array();
$task_trickle_down = array();
$forms = array();

foreach ( $found as $cl_id => $name ) {
    $form = strtolower( $cl_id );
    $task_desc[] = '
      <configuration name="can_view_database_list_' . $form . '" locale="en_US">
        <value>Can view database code list ' . $form . '</value>
      </configuration>
      <configuration name="can_edit_database_list_' . $form . '" locale="en_US">
        <value>Can edit database code list ' . $form . '</value>
      </configuration>';

    $task_trickle_down[] = '
        <value>can_view_database_list_' . $form . '</value>';

    $forms[] = '
        <configurationGroup name="' . $form . '">
          <displayName>SDMX-HD Code List: ' . $form . '</displayName>
          <description>The SDMX-HD Code List: ' . $name . '</description>
          <configuration name="class" values="single">
            <value>I2CE_SimpleList</value>
          </configuration>
          <configuration name="display" values="single" locale="en_US">
            <value>' . $name . '</value>
          </configuration>
          <configuration name="storage" values="single">
            <value>SDMXHD</value>
          </configuration>
          <configurationGroup name="storage_options" path="storage_options/SDMXHD">
            <configuration name="file" values="single">
              <value>' . $file . '</value>
            </configuration>
            <configuration name="CodeListID" values="single">
              <value>' . $cl_id . '</value>
            </configuration>
          </configurationGroup>
        </configurationGroup>';
}

echo '
    <configurationGroup name="tasks" path="/I2CE/tasks/task_description" locale="en_US">
' . implode( "\n", $task_desc ) . '
    </configurationGroup>
    <configurationGroup name="tasks_trickle_down" path="/I2CE/tasks/task_trickle_down">
      <configuration name="can_view_all_database_lists" values="many">
' . implode( "\n", $task_trickle_down ) . '
      </configuration>
    </configurationGroup>
    <configurationGroup name="forms" path="/modules/forms">
      <configurationGroup name="forms">
' . implode( "\n", $forms ) . '
      </configurationGroup>
    </configurationGroup>
    ';

?>
