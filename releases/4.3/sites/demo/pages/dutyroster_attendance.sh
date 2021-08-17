#!/bin/bash

# show commands being executed, per debug
set -x

# define database connectivity
_db="dutyroster"
_db_user="manageatt"
_db_password="manage123"
_month=`date --date="$(date +%Y-%m-15) -1 month" +%B`
_year=`date +%Y`


# define directory containing CSV files
_csv_directory="/var/lib/iHRIS/releases/4.3/sites/districts/pages"

#  CSV file
_csv_file="ihrisdata.csv"

# go into directory
cd $_csv_directory

# export csv from iHRIS
php index.php --page=/CustomReports/show/1508584085/Export --post=export_style=CSV > $_csv_file

#truncate old table
echo "truncate table ihrisdata" | mysql -u $_db_user -p$_db_password -D$_db

# import csv into mysql
mysqlimport --ignore-lines=2 --fields-enclosed-by='"' --fields-terminated-by=',' --lines-terminated-by="\n" --verbose --local  -u $_db_user -p$_db_password $_db $_csv_directory/$_csv_file

#Delete last row

echo "DELETE FROM ihrisdata WHERE ihris_pid='</table>'" | mysql -u $_db_user -p$_db_password -D$_db
#echo "DELETE FROM ihris_pid WHERE person_id='</table>'" | mysql -u $_db_user -p$_db_password -D$_db


# define directory containing CSV files
_csv_directory="/var/lib/iHRIS/releases/4.3/sites/districts/pages"

#  CSV file
_csv_file="ihrisdata.csv"

# go into directory
cd $_csv_directory

# export csv from iHRIS
php index.php --page=/CustomReports/show/1612720624/Export --post=export_style=CSV > $_csv_file


# import csv into mysql
mysqlimport --ignore-lines=2 --fields-enclosed-by='"' --fields-terminated-by=',' --lines-terminated-by="\n" --verbose --local  -u $_db_user -p$_db_password $_db $_csv_directory/$_csv_file

#Delete last row

echo "DELETE FROM ihrisdata WHERE ihris_pid='</table>'" | mysql -u $_db_user -p$_db_password -D$_db
#echo "DELETE FROM ihris_pid WHERE person_id='</table>'" | mysql -u $_db_user -p$_db_password -D$_db
