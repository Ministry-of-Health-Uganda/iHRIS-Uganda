#!/bin/bash
reports="staff_uniform structure users dutyroster_attendance search_people facility_tree position_tree disciplinary_case facility_list facility_positions former_staff person_attendance_all staff_appraisal person_appraisals leave person_mentorship person_recent_training person_training position_list registration staff_album staff_chart training_institution training_type"
for report in $reports
do
    echo Generating $report >> /tmp/report_cron.log
    date >> /tmp/report_cron.log
    cd /var/lib/iHRIS/releases/4.3/sites/national/pages/ && php index.php --page=/CustomReports/generate_force/$report --nocheck=1
    date >> /tmp/report_cron.log
done
