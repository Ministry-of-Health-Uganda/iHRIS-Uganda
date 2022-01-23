<?php 

$depends[] =  'mysql-server';
$depends[] =  'libapache2-mod-php5';
$depends[] = 'debconf (>= 0.5) | debconf-2.0';
$depends[] = 'dbconfig-common';
$depends[] = 'php-mdb2' ;
$depends[] = 'php-mdb2-driver-mysql';
$depends[] ='php-apc';
$recommends[] = 'memcached';
$recommends[] = ' php5-memcached';



$scripts['postinst'] = '#!/bin/sh
# postinst script for i2ce-site

a2enmod rewrite

set -e
if [ -f /etc/init.d/apache2 ] ; then
       if [ -x /usr/sbin/invoke-rc.d ]; then
                invoke-rc.d apache2 reload 3>/dev/null || true
              else
                /etc/init.d/apache2 reload 3>/dev/null || true
             fi
fi
if [ -f /etc/init.d/memcached ] ; then
             if [ -x /usr/sbin/invoke-rc.d ]; then
                invoke-rc.d memcached restart 3>/dev/null || true
              else
                /etc/init.d/memcached restart 3>/dev/null || true
            fi
fi
exit 0
';