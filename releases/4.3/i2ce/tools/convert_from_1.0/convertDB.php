<?php

/**
 * The best way to run this is:
 * php convertDB.php 2> convert.log
 * There's lots of notice messages you probably want to ignore for the most
 * part.
 * You'll need to change the include file to find the right config file
 * as well as the path to I2CE which may not work right using the one
 * from the config file.
 * The ID for the User object should be valid in your user table.
 * The $forms array is an associative array with the value being
 * an array of forms that are required for the given form to work e.g. 
 * region needs country first since it uses country as a map for a field.
 */

$script = array_shift( $argv );
if (file_exists(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'pages/local' . DIRECTORY_SEPARATOR . 'config.values.php')) {
    require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'pages/local' . DIRECTORY_SEPARATOR . 'config.values.php');
} else {
    require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR . 'pages/config.values.php');
}

$i2ce_site_i2ce_path = "../../I2CE";

require_once ($i2ce_site_i2ce_path . DIRECTORY_SEPARATOR . 'I2CE_config.inc.php');

I2CE::initialize($i2ce_site_database_user,
                 $i2ce_site_database_password,
                 $i2ce_site_database,
                 $i2ce_site_user_database,
                 $i2ce_site_module_config                     
    );

global $maps, $forms, $user, $db, $form_factory;
$maps = array();
$user = new I2CE_User(1);
$db = MDB2::singleton();
$form_factory = I2CE_FormFactory::instance();

$forms = array(
    "academic_level" => array(),
    "certificate" => array( "academic_level" ),
    "qualification" => array(),
    "cadre" => array( "qualification" ),
    "country" => array(),
    "region" => array( "country" ),
    "district" => array( "region" ),
    "county" => array( "district" ),
    "marital_status" => array(),    
    "training_disruption_category" => array(),
    "training_disruption_reason" => array( "training_disruption_category" ),
    "facility_agent" => array(),
    "facility_type" => array(),
    "out_migration_reason" => array(),
    "tribe" => array(),
    "facility_status" => array(),
    "health_facility" => array( "facility_agent", "facility_type", "facility_status" ),
    );

echo "Memory Limit: " . ini_get( "memory_limit" ) . "\n";
echo "Execution Time: " . ini_get( "max_execution_time" ) . "\n";

$cache_filename = "convert_maps.save";

if ( file_exists( $cache_filename ) ) {
    echo "Reading saved maps file...";
    $cache_file = fopen( $cache_filename, "r" );
    $cache_content = fread( $cache_file, filesize( $cache_filename ) );
    fclose( $cache_file );

    $maps = unserialize( $cache_content );
    echo "Done.\n";
}

if ( $argv[0] == "erase" ) {
    echo "Erasing all records and entries and map cache...";
    $db->query( "TRUNCATE TABLE record" );
    $db->query( "TRUNCATE TABLE entry" );
    $db->query( "TRUNCATE TABLE last_entry" );
    echo "Done\n";
    $maps = array();
    $tmp = array_shift( $argv );
}

if ( $argv[0] == "all" ) {
    $form_list = array_keys( $forms );
} else {
    $form_list = $argv;
}

foreach( $form_list as $form ) {
    do_convert( $form );
}

$cache_file = fopen( $cache_filename, "w" );
fwrite( $cache_file, serialize( $maps ) );
fclose( $cache_file );


function do_convert( $form ) {
    global $maps, $forms, $user, $db, $form_factory;
    if ( array_key_exists( $form, $maps ) ) {
        echo "Ignoring $form.  Already cached.\n";
    } else {
        if ( array_key_exists( $form, $forms ) ) {
            $required = $forms[$form];
            foreach( $required as $req_form ) {
                if ( !array_key_exists( $req_form, $maps ) )
                    do_convert( $req_form );
            }
        }
        convert( $form );
    }
}
function convert( $form ) {
    global $maps, $forms, $user, $db, $form_factory;
    switch ( $form ) {
    case "academic_level" :
        $sth = $db->query( "SELECT code,description FROM UNMCSupply.academic_level" );
        while ( $row = $sth->fetchRow() ) {
            $obj = $form_factory->createForm( "academic_level" );
            $obj->name = $row->description;
            $obj->save($user);
            $maps['academic_level'][ $row->code ] = $obj->getId();
            $obj->cleanup();
            unset( $obj );
        }
        $sth->free();
        echo "Finished with Academic Levels: " . memory_get_usage() . "\n";
        break;
            
    case "certificate" :
        $sth = $db->query( "SELECT code,description,academic_level FROM UNMCSupply.certificate_held" );
        while ( $row = $sth->fetchRow() ) {
            $obj = $form_factory->createForm( "certificate" );
            $obj->name = $row->description;
            $obj->academic_level = $maps['academic_level'][ $row->academic_level ];
            $obj->save( $user );
            $maps['certificate'][ $row->code ] = $obj->getId();
            $obj->cleanup();
            unset( $obj );
        }
        $sth->free();
        echo "Finished with Certificate Held: " . memory_get_usage() . "\n";
        break;
            
    case "qualification" :
        $sth = $db->query( "SELECT code,description FROM UNMCSupply.classification" );
        while ( $row = $sth->fetchRow() ) {
            $obj = $form_factory->createForm( "qualification" );
            $obj->name = $row->description;
            $obj->save($user);
            $maps['qualification'][ $row->code ] = $obj->getId();
            $obj->cleanup();
            unset( $obj );
        }
        $sth->free();
        echo "Finished with Classification -> Qualification: " . memory_get_usage() . "\n";
        break;
            
    case "cadre" :
        $sth = $db->query( "SELECT c.code,qt.description AS type,q.description AS qualification,c.classification FROM UNMCSupply.cadre c, UNMCSupply.qualification_type qt, UNMCSupply.qualification q WHERE qt.code = c.qualification_type AND q.code = c.qualification" );
        while ( $row = $sth->fetchRow() ) {
            $obj = $form_factory->createForm( "cadre" );
            $obj->name = $row->type . " " . $row->qualification;
            $obj->qualification = $maps['qualification'][ $row->classification ];
            $obj->save($user);
            $maps['cadre'][ $row->code ] = $obj->getId();
            $obj->cleanup();
            unset( $obj );
        }
        $sth->free();
        echo "Finished with Qualfication/type -> Cadre: " . memory_get_usage() . "\n";
        break;
            
    case "country" :
        $sth = $db->query( "SELECT code,description,alpha_two FROM UNMCSupply.country" );
        while ( $row = $sth->fetchRow() ) {
            $obj = $form_factory->createForm( "country" );
            $obj->name = $row->description;
            $obj->alpha_two = $row->alpha_two;
            $obj->code = $row->code;
            $obj->save($user);
            $maps['country'][ $row->code ] = $obj->getId();
            $obj->cleanup();
            unset( $obj );
        }
        $sth->free();
        echo "Finished with Country: " . memory_get_usage() . "\n";
        break;
            
    case "region" :
        $sth = $db->query( "SELECT code,description,country FROM UNMCSupply.region" );
        while ( $row = $sth->fetchRow() ) {
            $obj = $form_factory->createForm( "region" );
            $obj->name = $row->description;
            $obj->country = $maps['country'][ $row->country ];
            $obj->save($user);
            $maps['region'][ $row->code ] = $obj->getId();
            $obj->cleanup();
            unset( $obj );
        }
        $sth->free();
        echo "Finished with Region: " . memory_get_usage() . "\n";
        break;
            
    case "district" :
        $sth = $db->query( "SELECT d.code,d.description,d.region,r.country FROM UNMCSupply.district d, UNMCSupply.region r WHERE r.code = d.region" );
        while ( $row = $sth->fetchRow() ) {
            $obj = $form_factory->createForm( "district" );
            $obj->name = $row->description;
            $obj->region = $maps['region'][ $row->region ];
            $obj->country = $maps['country'][ $row->country ];
            $obj->save($user);
            $maps['district'][ $row->code ] = $obj->getId();
            $obj->cleanup();
            unset( $obj );
        }
        $sth->free();
        echo "Finished with District: " . memory_get_usage() . "\n";
        break;
            
    case "county" :
        $sth = $db->query( "SELECT c.code,c.description,c.district,d.region,r.country FROM UNMCSupply.county c, UNMCSupply.district d, UNMCSupply.region r WHERE d.code = c.district AND r.code = d.region" );
        while ( $row = $sth->fetchRow() ) {
            $obj = $form_factory->createForm( "county" );
            $obj->name = $row->description;
            $obj->district = $maps['district'][ $row->district ];
            $obj->region = $maps['region'][ $row->region ];
            $obj->country = $maps['country'][ $row->country ];
            $obj->save($user);
            $maps['county'][ $row->code ] = $obj->getId();
            $obj->cleanup();
            unset( $obj );
        }
        $sth->free();
        echo "Finished with County: " . memory_get_usage() . "\n";
        break;
            
    case "marital_status" :    
        $sth = $db->query( "SELECT code,description FROM UNMCSupply.marital_status" );
        while ( $row = $sth->fetchRow() ) {
            $obj = $form_factory->createForm( "marital_status" );
            $obj->name = $row->description;
            $obj->save($user);
            $maps['marital_status'][ $row->code ] = $obj->getId();
            $obj->cleanup();
            unset( $obj );
        }
        $sth->free();
        echo "Finished with Marital Status: " . memory_get_usage() . "\n";
        break;
            
    case "training_disruption_category" :
        $sth = $db->query( "SELECT code,description FROM UNMCSupply.disruption_category" );
        while ( $row = $sth->fetchRow() ) {
            $obj = $form_factory->createForm( "training_disruption_category" );
            $obj->name = $row->description;
            $obj->save($user);
            $maps['training_disruption_category'][ $row->code ] = $obj->getId();
            $obj->cleanup();
            unset( $obj );
        }
        $sth->free();
        echo "Finished with Disruption Category: " . memory_get_usage() . "\n";
        break;
            
    case "training_disruption_reason" :
        $sth = $db->query( "SELECT code,description,disruption_category FROM UNMCSupply.disruption" );
        while ( $row = $sth->fetchRow() ) {
            $obj = $form_factory->createForm( "training_disruption_reason" );
            $obj->name = $row->description;
            $obj->training_disruption_category = $maps['training_disruption_category'][ $row->disruption_category ];
            $obj->save($user);
            $maps['training_disruption_reason'][ $row->code ] = $obj->getId();
            $obj->cleanup();
            unset( $obj );
        }
        $sth->free();
        echo "Finished with Disruption Reason: " . memory_get_usage() . "\n";
        break;
            
    case "facility_agent" :
        $sth = $db->query( "SELECT code,description FROM UNMCSupply.facility_agent" );
        while ( $row = $sth->fetchRow() ) {
            $obj = $form_factory->createForm( "facility_agent" );
            $obj->name = $row->description;
            $obj->save($user);
            $maps['facility_agent'][ $row->code ] = $obj->getId();
            $obj->cleanup();
            unset( $obj );
        }
        $sth->free();
        echo "Finished with Facility Agent: " . memory_get_usage() . "\n";
        break;
            
    case "facility_type" :
        $sth = $db->query( "SELECT code,description FROM UNMCSupply.facility_type" );
        while ( $row = $sth->fetchRow() ) {
            $obj = $form_factory->createForm( "facility_type" );
            $obj->name = $row->description;
            $obj->save($user);
            $maps['facility_type'][ $row->code ] = $obj->getId();
            $obj->cleanup();
            unset( $obj );
        }
        $sth->free();
        echo "Finished with Facility Type: " . memory_get_usage() . "\n";
        break;
            
    case "out_migration_reason" :
        $sth = $db->query( "SELECT code,description FROM UNMCSupply.out_migration_reason" );
        while ( $row = $sth->fetchRow() ) {
            $obj = $form_factory->createForm( "out_migration_reason" );
            $obj->name = $row->description;
            $obj->save($user);
            $maps['out_migration_reason'][ $row->code ] = $obj->getId();
            $obj->cleanup();
            unset( $obj );
        }
        $sth->free();
        echo "Finished with Out Migration Reason: " . memory_get_usage() . "\n";
        break;
            
    case "tribe" :
        $sth = $db->query( "SELECT code,description FROM UNMCSupply.tribe" );
        while ( $row = $sth->fetchRow() ) {
            $obj = $form_factory->createForm( "tribe" );
            $obj->name = $row->description;
            $obj->save($user);
            $maps['tribe'][ $row->code ] = $obj->getId();
            $obj->cleanup();
            unset( $obj );
        }
        $sth->free();
        echo "Finished with Tribe: " . memory_get_usage() . "\n";
        break;
            
    case "facility_status" :
        $obj = $form_factory->createForm( "facility_status" );
        $obj->name = "Open";
        $obj->save( $user );
        $maps['facility_status'][ 0 ] = $obj->getId();
        $obj->cleanup();
        unset( $obj );
        $obj = $form_factory->createForm( "facility_status" );
        $obj->name = "Closed";
        $obj->save( $user );
        $maps['facility_status'][ 1 ] = $obj->getId();
        $obj->cleanup();
        unset( $obj );
        echo "Finished with Facility Status: " . memory_get_usage() . "\n";
        break;
            
    case "health_facility" :
        $sth = $db->query( "SELECT * FROM UNMCSupply.health_facility" );
        while ( $row = $sth->fetchRow() ) {
            $obj = $form_factory->createForm( "health_facility" );
            $obj->name = $row->name;
            $obj->id_code = $row->id_code;
            $obj->country = $maps['country'][ 800 ];
            $obj->district = $maps['district'][ $row->district ];
            $obj->county = $maps['county'][ $row->county ];
            $obj->facility_agent = $maps['facility_agent'][ $row->facility_agent ];
            $obj->facility_type = $maps['facility_type'][ $row->facility_type ];
            $obj->facility_status = $maps['facility_status'][ $row->closed ];
            $obj->save($user);
            $maps['health_facility'][ $row->code ] = $obj->getId();
                
            $contact = $form_factory->createForm( "contact" );
            $contact->setParent( $obj->getId() );
            $contact->contact_type = iHRIS_Contact::TYPE_FACILITY;
                
            $address = "";
            if ( !empty( $row->address1 ) ) $address .= $row->address1 . "\n";
            if ( !empty( $row->address2 ) ) $address .= $row->address2 . "\n";
            if ( !empty( $row->address3 ) ) $address .= $row->address3 . "\n";
            if ( !empty( $row->plot_num ) ) $address .= "Plot No: " . $row->plot_num . "\n";
                
            $contact->address = $address;
            $contact->telephone = $row->phone;
            $contact->fax = $row->fax;
            $contact->email = $row->email;
            $contact->save( $user );
                
            $obj->cleanup();
            $contact->cleanup();
            unset( $obj );
            unset( $contact );
        }
        $sth->free();
        echo "Finished with Health Facility: " . memory_get_usage() . "\n";
        break;
            
        /* Blank template:
        case "REPLACE" :
            $sth = $db->query( "SELECT * FROM UNMCSupply.REPLACE" );
            while ( $row = $sth->fetchRow() ) {
                $obj = $form_factory->createForm( "REPLACE" );
                $obj->name = $row->description;
                $obj->save($user);
                $maps['REPLACE'][ $row->code ] = $obj->getId();
                $obj->cleanup();
                unset( $obj );
            }
            $sth->free();
            echo "Finished with REPLACE: " . memory_get_usage() . "\n";
            break;
        */
    default :
        echo "No conversion written for $form yet.\n";
        break;
    }

}


# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
