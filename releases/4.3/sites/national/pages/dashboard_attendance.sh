#!/bin/bash

# show commands being executed, per debug
set -x

# define database connectivity
_db="hrhdashboard"
_db_user="manageatt"
_db_password="manage123"
_month=`date --date="$(date +%Y-%m-15) -1 month" +%B`
_year=`date +%Y`

#DISTRICT SYSTEM
# define directory containing CSV files
_csv_directory="/var/lib/iHRIS/releases/4.3/sites/districts/pages"

#  CSV file
_csv_file="temp_attendance.csv"

# go into directory
cd $_csv_directory

# export csv from iHRIS
php index.php --page=/CustomReports/show/1549971701/Export --post=export_style=CSV > $_csv_file
#php index.php --page=/CustomReports/show/1549971701/Export --post=export_style=CSV > $_csv_file2

#truncate old table
echo "truncate table temp_attendance" | mysql -u $_db_user -p$_db_password -D$_db

# import csv into mysql
mysqlimport --ignore-lines=2 --fields-enclosed-by='"' --fields-terminated-by=',' --lines-terminated-by="\n" --verbose --local  -u $_db_user -p$_db_password $_db $_csv_directory/$_csv_file
#Delete last row

echo "DELETE FROM temp_attendance WHERE person_id='</table>'" | mysql -u $_db_user -p$_db_password -D$_db



