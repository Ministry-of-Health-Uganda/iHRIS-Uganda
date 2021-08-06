# iHRIS-Uganda
# iHRIS
Integrated Human Resource Information System

## Linux (Ubuntu) Installation - Supporting Software - 18.04 /20.04 LTS

Here are instructions for installing the supporting software for iHRIS on a Linux (Ubuntu) system. If you need help installing Ubuntu you may want to take a look at these directions for installing a Server or a Desktop system. For a server setup, we recommend using a LTS (long term support) version of Ubuntu.

Note: Unless specifically mentioned, all the commands below are run using a terminal. You can start this in Ubuntu by going to Applications -> Accessories -> Terminal. Any time a command begins with sudo it will prompt for your password because this will be run with administrative privileges. When you run sudo multiple times, only the first time will ask for your password.

Note: Some installation commands will prompt for inputs in the terminal window, usually with a blue background. The mouse doesn't work to click on options here. You can use Tab to move between options and the space bar to check or uncheck selections.

Note: Some commands will launch the nano file editor. Look at the documentation if you need additional help.

We begin by install a Lamp server (You can find more help here): 
```shell script
$ sudo tasksel install lamp-server 
```
If you have never used mysql on your system, you will be asked to set the 'root' password for mysql. We will refer to this password as XXXXX below.

Important: Make sure your email system is correctly configured. Under a default Ubuntu installation, you can do this with one of two commands: 
```shell script
$ sudo apt install postfix sudo dpkg-reconfigure postfix
```

Follow the on-screen instructions to set up email on your system. For additional help with installing Postfix, look at these instructions. On Debian systems, the same commands can be used, but exim4 is the default MTA instead of postfix
If you are using another Linux distribution, make sure your system can send email properly before continuing.

## Configuring MYSQL

Make sure you have in /etc/mysql/mysql.conf.d/mysqld.cnf the following values set: 
```shell script
$ sudo gedit /etc/mysql/mysql.conf.d/mysqld.cnf 
```

```ini
query_cache_limit = 4M query_cache_size = 64M 
```
 
Create /etc/mysql/mysql.conf.d/sql-mode.cnf and set the sql-mode variable. 
```shell script
$ sudo gedit /etc/mysql/mysql.conf.d/sql-mode.cnf
```

```ini
[mysqld] sql-mode = "ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION"
```

If not already configured, set up the MySQL system and root login: 
```shell script
$ sudo mysql_secure_installation
```

To configure MySQL so iHRIS can create needed functions: 
```shell script
$ mysql -u root -p 
```

Enter the password you set above (XXXXX) for MySQL. If the password isn't working, try running it as the root user as the auth_socket authorization may be enabled: 
 ```shell script
$ sudo mysql 
```
 
 
You will now be able to send commands to MySQL and the prompt should always begin with 'mysql> '. Type these commands:
```mysql
SET GLOBAL log_bin_trust_function_creators = 1; exit
```

Now restart mysql so these changes take affect. 
```shell script
$ sudo service mysql restart
```

[SETTING THE PASSWORD MANUALLY IS OPTIONAL]
If the password you set above doesn't work, you can run the following set of commands to set it manually in the database;
Replace _putyourpasswordhere_ with a MEDIUM strength password by the following criteria. (Only Medium or Strong password will work)

LOW Length >= 8 MEDIUM Length >= 8, numeric, mixed case, and special characters STRONG Length >= 8, numeric, mixed case, special characters and dictionary

```shell script
$ sudo mysql > ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY 'putyourpasswordhere';
```

To confirm the password is set run this and you should see the column with User=root has a password hash. 
```mysql
SELECT User, Host, HEX(authentication_string) FROM mysql.user;
```

## Installing PHP Packages

We need to install a few Pear and PECL packages for PHP. For the Pear packages you can do: 

```shell script
$ sudo apt install php-pear php-gd php-tidy php-intl php-bcmath php-text-password php-mbstring php-uuid
```

##### APCu
To install APCu you need to run this command:  

```shell script
$ sudo apt install php-apcu 
```

During certain activities like installation and upgrades you may need more memory than APC uses by default. We also want to turn off the slam defense. We need to edit the configuration file file for apcu: 

 ```shell script
sudo nano /etc/php/7.2/mods-available/apcu.ini 
```
 
It should look like this: 

```ini
extension=apcu.so apc.enabled=1 apc.write_lock=1 apc.shm_size=100M apc.slam_defense=0 apc.enable_cli=1
```

See <a href="http://pecl.php.net/bugs/bug.php?id=16843">slam defense</a> and <a href="http://t3.dotgnu.info/blog/php/user-cache-timebomb"></a>this.

##### Debian Squeeze
If you are using Debian Squeeze, then the value of apc.shm_size should be: 

```ini
apc.shm_size=100
```


##### Install Memcached
With version 4.0.4 and greater of iHRIS you can use memcached to improve performance
Note: Memcached is used to cache data from the database. Thus if you are an a sitaution where you would need to restart the webserver by

```shell script
$ sudo systemctl restart apache2
```

you should now do
```shell script
$ sudo systemctl restart apache2 && sudo systemctl restart memcached
```

To install, simply do 

```shell script
$ sudo apt install php-memcached memcached
```

Set ZendOpcache options
Edit the opcache config file with this command:  
```shell script
$ sudo nano /etc/php/7.2/mods-available/opcache.ini  
```

It should look like this for a production system: 
configuration for php ZendOpcache module

```ini
priority=05
zend_extension=opcache.so 
opcache.memory_consumption=128M 
opcache.interned_strings_buffer=8 
opcache.max_accelerated_files=4000 
opcache.revalidate_freq=60 
opcache.fast_shutdown=1 
opcache.enable_cli=1 
```

For a development system you should modify revalidate_freq from 60 to 2: 

```ini
opcache.revalidate_freq=2 
```

Configuring Apache Web Server

##### Document Root
In Ubuntu 18.04, the default document root is /var/www/html so when installing any iHRIS applications you will need to use the new directory to place the symlinks. If you are upgrading you may or may not need to update these depending on if you replaced the Apache configuration files during the previous upgrade.

Enable Rewrite Module
You will see later we are using the apache rewrite module. To enable the module: 
```shell script
$ sudo a2enmod rewrite 
``` 
 
Enable .htaccess Configuration
Now we need to make sure we can use the .htaccess file. 
```shell script
$ sudo nano /etc/apache2/apache2.conf
```

Change: 
```apacheconfig
<Directory /var/www/>
    Options Indexes FollowSymLinks
    AllowOverride None 
    Require all granted 
</Directory>
```
to: 
```apacheconfig
<Directory /var/www/> 
    Options Indexes FollowSymLinks MultiViews 
    AllowOverride All 
    Require all granted 
</Directory>
```
Save and quit.

Restart Apache

You'll need to restart Apache after making these changes.
```shell script
$ sudo service apache2 restart
```

## Download Source Packages (4.3)

```shell script
$ cd /var/lib
$ sudo git clone https://github.com/MOH-Zambia/iHRIS.git
```

### Configuring a Site

Once you've downloaded the packages for your application, you will need to configure a site. You can use a site you've created or a demo site which you can find in the particular application you're working on in sites/Demo.

##### Create a Database
First you'll need to create a database and user for your site. Run this command from the terminal and then enter the password for the root database user you created when installing MySQL. 
```shell script
$ mysql -u root -p
```

Then at the mysql> prompt type these commands replacing DATABASE with your database and PASSWORD with the password you'd like to use for this connection. 
```mysql
create database DATABASE; grant all privileges on DATABASE.* to ihris@localhost identified by 'PASSWORD'; exit
```

##### Configure Site
Next you'll need to configure the config.values.php file for your site. Run these commands after you've changed into the site pages directory. For example if you're working with the iHRIS Manage Demo site you would run: 
```shell script
$ cd /var/lib/iHRIS/lib/4.3.3/ihris-manage/sites/Demo/pages
```

```shell script
$ mkdir local/ 
$ cp config.values.php local/ 
$ cp htaccess.TEMPLATE .htaccess 
$ sudo nano local/config.values.php
```
 
Now find the configuration variables in the file that opens and change to the appropriate values. Again for this example using iHRIS Manage, but replace with appropriate values for the application and site you're using.

  Variable Name	            | Value                                                                   
  ------------------------- | ------------------------------------------------------------------------
  $i2ce_site_i2ce_path      | /var/lib/iHRIS/lib/4.3.3/I2CE                                           
  $i2ce_site_dsn    	    | mysql://ihris:PASWORD@localhost/DATABASE                                
  $i2ce_site_module_config  | var/lib/iHRIS/lib/4.3.3/ihris-manage/sites/Demo/iHRIS-Manage-Demo.xml 

Now edit the .htaccess file to set the RewriteBase: 
```shell script
$ sudo nano .htaccess
``` 

Change the RewriteBase line to be the path in the web server for your site. 

```apacheconfig
RewriteBase /manage-demo
```

Set Up the Site in the Web Server
The last step is to create a symbolic link in the web root directory for your site. For our example we'll use /manage-demo. For Ubuntu 14.04 and later the web root is /var/www/html. Prior to 14.04 it is just /var/www. Be sure to use the correct directory in the following steps.
```shell script
$ cd /var/www/html 
$ sudo ln -s /var/lib/iHRIS/lib/4.3.3/ihris-manage/sites/Demo/pages manage-demo
```


