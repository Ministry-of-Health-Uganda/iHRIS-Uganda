#!/bin/bash
reports="staff_analysis_other staff_analysis staff_analysis_moh analysis_ii_iii"
for report in $reports
do
    echo Generating $report >> /tmp/report_cron.log
    date >> /tmp/report_cron.log
    cd /var/lib/iHRIS/releases/4.3/sites/national/pages/ && php index.php --page=/CustomReports/generate_force/$report --nocheck=1
    date >> /tmp/report_cron.log
done
