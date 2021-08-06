<?php

error_reporting( E_ERROR | E_PARSE );

global $file_done;
$file_done = array();
$space = "";


if ( count( $argv ) < 2 ) {
    die( "Usage: $argv[0] <new directory> <index.html>\n" );
} elseif ( count( $argv ) == 2 ) {
    $copy_dir = $argv[1];
    $index_file = "index.html";
} elseif ( count( $argv ) == 3 ) {
    $copy_dir = $argv[1];
    $index_file = $argv[2];
} elseif ( count( $argv ) > 3 ) {
    die( "Usage: $argv[0] <new directory> <index.html>\n" );
}


if ( file_exists( $copy_dir ) ) {
    die( "$copy_dir already exists.  Can't continue.\n" );
}

if ( !file_exists( $index_file ) ) {
    die( "$index_file doesn't exist.  Can't continue.\n" );
}

if ( !mkdir( $copy_dir ) ) {
    die( "Can't create $copy_dir\n" );
}


parseHTML( $index_file, $copy_dir, $space );

function parseHTML( $file_name, $copy_dir, $space ) {
    global $file_done;

    if ( array_key_exists( $file_name, $file_done ) ) return;
    $file_done[$file_name] = true;

    echo $space . "Working on file: $file_name\n";

    $index = new DOMDocument();
    $index->loadHTMLFile( $file_name );

    $found = array();
    
    $xpath = new DOMXPath( $index );

    // Get rid of some unneeded links
    $results = $xpath->query( "//a[starts-with(@href, 'file_')]" );
    foreach( $results as $result ) {
        //$result->parentNode->replaceChild( $result->firstChild, $result );
        $result->removeAttribute( "href" );
    }
    $results = $xpath->query( "//a[@href]" );
    foreach( $results as $result ) {
        $href = $result->getAttribute( "href" );
        if ( ( $href[0] != '#' && substr( $href, 0, 6 ) != 'ihris_' )
            || $href == 'ihris_qualify_form_fields.html' || $href == 'ihris_manage_form_fields.html' ) {
            $result->removeAttribute( "href" );
        }
    }
    $results = $xpath->query( "//div[@id='footerHacked']" );
    foreach( $results as $result ) {
        $result->parentNode->removeChild( $result );
    }
    $results = $xpath->query( "//div[@id='catlinks']" );
    foreach( $results as $result ) {
        $result->parentNode->removeChild( $result );
    }
    $results = $xpath->query( "//small/i/div[@class='dablink']" );
    foreach( $results as $result ) {
        $small = $result->parentNode->parentNode;
        $small->parentNode->removeChild( $small );
    }
    $results = $xpath->query( "//div[@id='contentSub']" );
    foreach( $results as $result ) {
        $result->parentNode->removeChild( $result );
    }
     
    $results = $xpath->query( "//@href" );
    foreach( $results as $result ) {
        $href = $result->value;
        if ( strpos( $href, '#' ) !== false ) {
            $href = substr( $href, 0, strpos( $href, '#' ) );
        }
        if ( $href == '' ) continue;
        if ( substr( $href, -5 ) == ".html" && substr( $href, 0, 6 ) != 'ihris_' ) continue;
        if ( substr( $href, 0, 7 ) == 'http://' ) continue;
        if ( substr( $href, 0, 8 ) == 'https://' ) continue;
        if ( $href == "favicon.ico" ) continue;
        $found[] = $href;
    }
    
    $results = $xpath->query( "//@src" );
    foreach( $results as $result ) {
        $src = $result->value;
        if ( $src == '' ) continue;
        $found[] = $src;
    }
    
    $results = $xpath->query( "//comment()" );
    foreach( $results as $result ) {
        $comment = $result->nodeValue;
        if ( $comment[0] != '[' ) continue;
        $match = array();
        preg_match( "/(href|src)=[\"']([^'\"]*)[\"']/", $comment, $match );
        if ( count( $match ) > 0 ) {
            $found[] = $match[2];
        }
    }

    $fd = fopen( "$copy_dir/$file_name", "w" );
    $content = $index->saveXML();
    $content = preg_replace( "/iHRIS:/", "", $content );
    $content = preg_replace( "/ \(4\.0\.5\)/", "", $content );
    $content = preg_replace( "/Osi:Books\//", "", $content );
    fwrite( $fd, $content );
    fclose( $fd );

    foreach( $found as $file ) {
        if ( file_exists( $file ) ) {
            if ( substr( $file, -5 ) == ".html" ) {
                parseHTML( $file, $copy_dir, $space . " " );
                continue;
            } elseif ( substr( $file, -4 ) == ".css" ) {
                parseCSS( $file, $copy_dir, $space . " " );
            }
            copy( $file, "$copy_dir/$file" );
        } else {
            echo $space . "$file does not exist.\n";
        }
    }

    echo $space . "Done with $file_name\n";

}

function parseCSS( $file_name, $copy_dir, $space ) {
    global $file_done;

    if ( array_key_exists( $file_name, $file_done ) ) return;
    $file_done[$file_name] = true;

    echo $space . "Working on file: $file_name\n";

    $data = file_get_contents( $file_name );
    preg_match_all( "/url\(\"?([^\")]*)\"?\)/", $data, $match );
    foreach ( $match[1] as $file ) {
        if ( !copy( $file, "$copy_dir/$file" ) ) {
            echo $space . "Failed to copy $file\n";
        }
    }
    echo $space . "Done with $file_name\n";
}


?>
