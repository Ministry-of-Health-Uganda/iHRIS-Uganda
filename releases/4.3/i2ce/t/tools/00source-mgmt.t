use Test::More tests => 24;      # -*- perl -*-
use File::Temp qw(tempfile);
use File::stat qw(stat);
use POSIX qw(strftime);

use strict;
use warnings;

my $cmd = "tools/add_mode_lines.php";
my ($pre, $post, $cons);

sub do_test {
  my %arg = @_;
  my ($pre_stat, $post_stat, $console);
  my ($fh, $filename) = tempfile("testXXXXX", SUFFIX => ".php");
  my ($contents);
  my $options = $arg{options} || "";
  my $prefix = $arg{prefix} || "";

  $prefix &&= "$prefix: ";

  $fh->print($arg{input})
    if $arg{input};
  close $fh;

  $pre_stat = stat($filename);
  if($arg{pre_hook}) {
    $arg{pre_hook}->($filename);
  }
  $console = `$cmd $options $filename`;
  if($arg{post_hook}) {
    $arg{post_hook}->($filename);
  }
  $post_stat = stat($filename);

  if($arg{output}) {
    if(open($fh, $filename)) {
      local $/;                   # slurp mode
      $contents = <$fh>;
      is($contents, $arg{output}, "${prefix}File output is correct");
    } else {
      ok(0, "Couldn't open temp file for reading!");
    }
  }

  unlink($filename);
  return ($pre_stat, $post_stat, $console);
}

# Test no options
$cons = `$cmd`;
is($cons,
   "Usage: [-l] [-h] [-m] [--mode='mode'] dir_1 dir_2 dir_3 ... dir_n\n",
   "Usage message displayed.");

# Test with non-existant file
my ($fh, $filename) = tempfile("testXXXXX", SUFFIX => ".php");
unlink $filename;
$cons = `$cmd $filename`;
like($cons,
     qr(Problem opening [^ ]+ for reading\.),
     "Error message displayed.");

# Test -h option
($pre, $post, $cons) = do_test(options => "-h");
is($cons,
   qq{Usage: [-l] [-h] [-m] [--mode='mode'] dir_1 dir_2 dir_3 ... dir_n
 -h        Display this help message.
 -l        Update the license as well as the mode line.
 -m        Print the default mode line and exit.
 -n        Just print what would be changed
 --mode="line"
           Override the default mode line
 --maxlen=#
           Maximum line length to warn on.
},
   "Help message displayed.");

# Test -m option
($pre, $post, $cons) = do_test(options => "-m");
is($cons,
   qq{Mode Line: c-default-style: "bsd"; indent-tabs-mode: nil; c-basic-offset: 4;\n},
   "Mode line displayed.");

# Set --mode and echo back -m
($pre, $post, $cons) = do_test(options => "--mode='test1' -m");
is($cons,
   qq{Mode Line: test1\n},
   "Updated mode line displayed.");

# Move a -*- modeline -*- to the end
($pre, $post, $cons) = do_test(prefix => "Move modeline",
input => q{<? // -*- mode: php; c-default-style "bsd"; indent-tags-mode: nil; c-basic-offset: 4 -*-
Some fake php
?>
}, output => q{<?
Some fake php

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
});

# Move a -*- modeline -*- to the end with # instead of // comment delimeter
($pre, $post, $cons) = do_test(prefix => "Comment is hash",
input => q{<? # -*- mode: php; c-default-style "bsd"; indent-tags-mode: nil; c-basic-offset: 4 -*-
Some fake php
?>
}, output => q{<?
Some fake php

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
});

# Add modeline to file without Modeline or Local Variables
($pre, $post, $cons) = do_test(prefix => "No modeline or Local Variables",
input => q{<?
Some fake php
?>
}, output => q{<?
Some fake php

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
});

# LocalVariables updated in place, even if not the end of the file
($pre, $post, $cons) = do_test(prefix => "Local Vars correct",
options => q{--mode='c-default-style: "bsd"'},
input => q{<?
# Local Variables:
# End:


Some fake php
?>
}, output => q{<?
# Local Variables:
# mode: php
# c-default-style: "bsd"
# End:


Some fake php
?>
});

# Set --mode, verify LocalVars is set correctly
($pre, $post, $cons) = do_test(prefix => "Mode set, Local Vars correct",
options => q{--mode='c-default-style: "bsd"'},
input => q{<?
Some fake php
?>
}, output => q{<?
Some fake php

# Local Variables:
# mode: php
# c-default-style: "bsd"
# End:
});

# Set --mode and have it modify a file with the equiv Local Variables Section, Verify file isn't touched at all.
($pre, $post, $cons) = do_test(prefix => "Mode set, Local Vars same, file not touched",
options => q{--mode='c-default-style: "bsd"'},
input => q{<?
Some fake php

# Local Variables:
# mode: php
# c-default-style: "bsd"
# End:
}, output => q{<?
Some fake php

# Local Variables:
# mode: php
# c-default-style: "bsd"
# End:
},
pre_hook => sub {diag "Sleeping 1 for mtime"; sleep 1});
is($pre->mtime, $post->mtime, "Mtime not changed");

# Set --mode and have it update a file with different Local Variables section
($pre, $post, $cons) = do_test(prefix => "Mode set, Local Vars updated",
options => q{--mode='c-default-style: "bsd"'},
input => q{<?
Some fake php

# Local Variables:
# mode: php
# c-default-style: "fred"
# c-default-style: "fred"
# indent-tabs-mode: nil
# End:
}, output => q{<?
Some fake php

# Local Variables:
# mode: php
# c-default-style: "bsd"
# End:
});

# Set --maxlen to 0 and verify it just does LOC
($pre, $post, $cons) = do_test(prefix => "Mode set, Local Vars updated",
options => q{--maxlen=0},
input => q{<?
Some fake php

# c-default-style: "fred"
# c-default-style: "fred"
# indent-tabs-mode: nil
# End:
});
like($cons, qr(Number of long lines: 7), "Just got LOC from maxlen=0");

# Set --maxlen to 65000 and verify no warning
($pre, $post, $cons) = do_test(prefix => "Mode set, Local Vars updated",
options => q{--maxlen=65000},
input => q{<?
Some fake php

# c-default-style: "fred"
# c-default-style: "fred"
# indent-tabs-mode: nil
# End:
});
unlike($cons, qr(Number of long lines: \d), "No LOC warning from maxlen=65000");

# Set --maxlen to 5 and test on file with two lines of 10 and 4 and
# that it warns on one.

# NOTE: This test doesn't count the Local Vars lines because we count
# long lines before we add the Local Vars section.  You are welcome to
# consider this a bug, but I don't.
($pre, $post, $cons) = do_test(options => '--maxlen=5',
input => qq{<?
Some fake php
tiny
});
like($cons, qr(Number of long lines: 1\D), "Warn on one line");

# Set -l and test with a file that contains the current year
my $year = strftime('%Y', localtime);
($pre, $post, $cons) = do_test(options => q{-l},
input => qq{<?
Some fake php

# © Copyright $year IntraHealth International, Inc.

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
},
pre_hook => sub {diag "Sleeping 1 for mtime"; sleep 1});
is($pre->mtime, $post->mtime, "File not touched where copyright year was right (character version)");

# Test with &copy entity.
($pre, $post, $cons) = do_test(options => q{-l},
input => qq{<?
Some fake php

# Copyright &copy; $year IntraHealth International, Inc.

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
},
pre_hook => sub {diag "Sleeping 1 for mtime"; sleep 1});
is($pre->mtime, $post->mtime, "File not touched where copyright year was right (entity version)");

# Test with a file doesn't contain this year
($pre, $post, $cons) = do_test(prefix => "year updated (entity version)",
options => q{-l},
input => qq{<?
Some fake php

# Copyright &copy; 2000 IntraHealth International, Inc.

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
},
output => qq{<?
Some fake php

# Copyright &copy; 2000, $year IntraHealth International, Inc.

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
});

($pre, $post, $cons) = do_test(prefix => "year updated (character version)",
options => q{-l},
input => qq{<?
Some fake php

# © Copyright 2000 IntraHealth International, Inc.

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
},
output => qq{<?
Some fake php

# © Copyright 2000, $year IntraHealth International, Inc.

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
});

# Set -n and -l and verify that nothing is touched but changes are echoed.
($pre, $post, $cons) = do_test(prefix => "With -n, nothing touched",
options => q{-l -n},
input => qq{<?
Some fake php

# © Copyright 2000 IntraHealth International, Inc.

# Local Variables:
# End:
},
pre_hook => sub {diag "Sleeping 1 for mtime"; sleep 1},
output => qq{<?
Some fake php

# © Copyright 2000 IntraHealth International, Inc.

# Local Variables:
# End:
});
is($pre->mtime, $post->mtime, "File not touched with -n flag");
like($cons, qr(Updating copyright), "Possible copyright update with -n flag");
like($cons, qr(Updating Local Vars), "Possible local var update with -n flag");

