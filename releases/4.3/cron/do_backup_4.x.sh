SUFFIX=`date +%F`
cd /home/ihrisbackup/backup_no_cache
scp backup_districtdbname_$SUFFIX.sql.bz2 -p 20020 council@41.202.232.87:/home/ihrisbackup/distict_backups
