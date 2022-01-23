use Test::More;                 # -*- cperl -*-
use File::Find qw(find);

my @php;                        # holds the list of files to be tested.

# Go through all the subdirectories looking for files that end with
# .php and add them to the list of files to run php lint on.
find(sub{
       if(/.php$/) {            # match files ending with .php
         push @php, $File::Find::name; # add them to the list of files
       }
     }, '.');

plan tests => scalar @php;      # Tell the test harness how many tests
                                # we're going to perform... one for
                                # each .php file.

foreach(@php) {
  my $check = `php -l $_ 2> /dev/null`; # Even though I typically
                                        # abhor using backticks like
                                        # this, I'm using it here to
                                        # capture the output for the
                                        # next step, so I'm ok with
                                        # that.
  is($check, "No syntax errors detected in $_\n", "Checking syntax of $_");
}
