Flow :

1. Start Application - Login

2. Go to Configure Module -> iHRIS Common -> enable 'DHIS Dashboard' module

3. Create Designation report

4. Add xslt format in Export option of report view of newly created Designation report.
    (for different period id like monthly,yearly depend on the format which will be accepted in DHIS dxf2 module)
    Path to xslt files: (......./ihris-common/modules/DHIS-Dashboard/resource/xsltFiles/*)

5. Checkout DHIS code from location (https://code.launchpad.net/~dhis2-devs-core/dhis2/DHIS_IHRIS_SYNC_2.12) and build it
   using india-pom.xml in (.../dhis2/dhis-web/dhis-web-portal/india-pom.xml)

6. Add link to dhis application to view dashboard in file :
       ..../ihris-common/modules/DHIS-Dashboard/templates/en_US/dashboard.html
       eg - "http://localhost:8080/dhis/dhis-web-dashboard-integration/index.action"

7. Set cookies for dashboard and automatic login in dhis application in file:
    (......./ihris-common/modules/DHIS-Dashboard/lib/iHRIS_PageDashboard)
    at :
    // eg :
            $cookie = explode('=', $cookie);
            set_cookie('JSESSIONID', $cookie[1], time() + 6000, '/dhis', 'localhost',0);


8. Set path to store file xml file generated from report and to send to dhis
  a. Go to file (...../ihris-common/modules/DHIS-Dashboard/lib/I2CE_CustomReport_Display_DefaultDHISDashboard)
  b. Give path to create file and store xml output of report
    //enter data into file
            $fp = fopen(".../path/output.xml", "w");

9. Enter ihris login username and password at:
    a. Go to file (...../ihris-common/modules/DHIS-Dashboard/lib/I2CE_CustomReport_Display_DefaultDHISDashboard)
    b. $login_submission = array(

                'username' => "i2ce_admin",

                'password' => "root"

            );


10. Assign Data Elements to Data Set and Data set to organisation unit in DHIS application.
11. Data Elements and Organisation units are created in DHIS automatically once we send our first report
12. After that only updated organisation units information is sent to DHIS.
13. To send data element for now we are writing querie in :
    (...../ihris-common/modules/DHIS-Dashboard/lib/I2CE_CustomReport_Display_DefaultDHISDashboard)
    Need to make it user customisable.

14. TO send Data
    a. Click on send report
    b. Select period
    c. The data will be send to dhis and can be seen in Data entry screen for that month.

