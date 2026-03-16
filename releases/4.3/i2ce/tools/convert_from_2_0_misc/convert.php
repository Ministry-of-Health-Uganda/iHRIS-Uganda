<?php
$contents = file('ihris_manage_config.inc.php');
foreach ($contents as $line) {
    if (!preg_match('/^\$factory->register\(\s*[\'"](\w*)[\'"]\s*,\s*[\'"](\w*)[\'"]\s*,\s*[\'"]([\s\w]*)[\'"]/',$line,$matches)) {
        continue;
    }
    $name=$matches[1];
    $form=$matches[2];
    $display=$matches[3];
    echo "<configurationGroup name='$name'>
 <displayName>$display</displayName>
 <description>The $display Form</description>
 <configuration name='class' values='single'>
   <displayName>Class Name</displayName>
   <description>The name of the class providing the form</description>
   <value>$form</value>
 </configuration>
 <configuration name='display' values='single'>
   <displayName>Display name</displayName>
   <description>The display name for this form</description>
   <value>$display</value> 
 </configuration> 
</configurationGroup>";
}


# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
