#!/bin/bash

# show commands being executed, per debug
set -x

# define database connectivity
_db="hrh_dashboard"
_db_user="ihris_manage"
_db_password="managi123"
_db_host="172.27.1.109"
_month=`date --date="$(date +%Y-%m-15) -1 month" +%B`
_year=`date +%Y`

#STAFF TABLE FACILITY LEVEL
 #define directory containing CSV files
_csv_directory="/var/lib/iHRIS-Uganda/releases/4.3/sites/national/pages"

#  CSV file
_csv_file="staff.csv"

# go into directory
cd $_csv_directory

# export csv from iHRIS
php index.php --page=/CustomReports/show/1536096246/Export --post=export_style=CSV > $_csv_file

#truncate old table
echo "truncate table staff" | mysql -u $_db_user -p$_db_password -h$_db_host $_db

# import csv into mysql
#mysqlimport --ignore-lines=2 --fields-enclosed-by='"' --fields-terminated-by=',' --lines-terminated-by="\n" --verbose --local  -u $_db_user -p$_db_password $_db $_csv_directory/$_csv_file
mysqlimport --ignore-lines=2 --fields-enclosed-by='"' --fields-terminated-by=',' --lines-terminated-by="\n" --verbose --local  -u $_db_user -p$_db_password -h$_db_host  $_db $_csv_directory/$_csv_file

#Delete last row
echo "DELETE FROM staff WHERE person_id='</table>'" | mysql -u $_db_user -p$_db_password -h$_db_host $_db


#STAFF TABLE FACILITY
# define directory containing CSV files
_csv_directory="/var/lib/iHRIS-Uganda/releases/4.3/sites/national/pages"

#  CSV file
_csv_file="staff.csv"

# go into directory
cd $_csv_directory

# export csv from iHRIS
php index.php --page=/CustomReports/show/staff_analysis_other/Export --post=export_style=CSV > $_csv_file


# import csv into mysql
mysqlimport --ignore-lines=2 --fields-enclosed-by='"' --fields-terminated-by=',' --lines-terminated-by="\n" --verbose --local  -u $_db_user -p$_db_password -h$_db_host $_db $_csv_directory/$_csv_file

#Delete last row
echo "DELETE FROM staff WHERE person_id='</table>'" | mysql -u $_db_user -p$_db_password -h$_db_host $_db


#STAFF TABLE  MOH
# define directory containing CSV files
_csv_directory="/var/lib/iHRIS-Uganda/releases/4.3/sites/national/pages"

#  CSV file
_csv_file="staff.csv"

# go into directory
cd $_csv_directory

# export csv from iHRIS
php index.php --page=/CustomReports/show/staff_analysis_moh/Export --post=export_style=CSV > $_csv_file


# import csv into mysql
mysqlimport --ignore-lines=2 --fields-enclosed-by='"' --fields-terminated-by=',' --lines-terminated-by="\n" --verbose --local  -u $_db_user -p$_db_password -h$_db_host $_db $_csv_directory/$_csv_file

#Delete last row
echo "DELETE FROM staff WHERE person_id='</table>'" | mysql -u $_db_user -p$_db_password -h$_db_host $_db

#STAFF TABLE  II III
# define directory containing CSV files
_csv_directory="/var/lib/iHRIS-Uganda/releases/4.3/sites/national/pages"

#  CSV file
_csv_file="staff.csv"

# go into directory
cd $_csv_directory

# export csv from iHRIS
php index.php --page=/CustomReports/show/1612679783/Export --post=export_style=CSV > $_csv_file


# import csv into mysql
mysqlimport --ignore-lines=2 --fields-enclosed-by='"' --fields-terminated-by=',' --lines-terminated-by="\n" --verbose --local  -u $_db_user -p$_db_password -h$_db_host $_db $_csv_directory/$_csv_file

#Delete last row
echo "DELETE FROM staff WHERE person_id='</table>'" | mysql -u $_db_user -p$_db_password -h$_db_host $_db

#STRUCTURE TABLE 
�# csv_directory="/var/lib/iHRIS-Uganda/releases/4.3/sites/national/pages"

#  CSV file
 _csv_file="structure.csv"

# go into directory
 cd $_csv_directory

# export csv from iHRIS
 php index.php --page=/CustomReports/show/1601629155/Export --post=export_style=CSV > $_csv_file

#truncate old table
 echo "truncate table structure" | mysql -u $_db_user -p$_db_password -h$_db_host $_db

# import csv into mysql
 mysqlimport --ignore-lines=2 --fields-enclosed-by='"' --fields-terminated-by=',' --lines-terminated-by="\n" --verbose --local  -u $_db_user -p$_db_password -h$_db_host $_db $_csv_directory/$_csv_file

#Delete last row
 echo "DELETE FROM structure WHERE id='</table>'" | mysql -u $_db_user -p$_db_password -h$_db_host $_db
