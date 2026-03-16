<?php
# import the Test::More emulation layer
# see
#   http://search.cpan.org/dist/Test-Simple/lib/Test/More.pm
# for Perl's documentation - these functions should behave
# in the same way
require 'test-more.php';

plan(3);

is(extension_loaded("APC"),   true, "APC is loaded");
is(ini_get("apc.enabled"),    true, "APC is enabled");
is(ini_get("apc.enable_cli"), true, "APC for CLI is enabled");


# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
