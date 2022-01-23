use Apache::TestRunPHP;
use Apache::TestMB;
use Cwd qw(abs_path);

$t_dir = "t";
$script = "$t_dir/TEST";

$conf = Apache::TestMB->new
  (requires           => {Apache::Test => '1.30.1'},
   dist_name          => 'iHRIS Common',
   dist_version       => '3.0',
   apache_test_args   =>
   {t_dir             => abs_path($t_dir),
    httpd             => '/usr/sbin/apache2',
    tests             => "$t_dir/*.t"},
   apache_test_script => $script);
$conf->create_build_script;
$conf->generate_script($script, "Apache::TestRunPHP");
