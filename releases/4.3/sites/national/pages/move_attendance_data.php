<?php 
									
	$mysqli = mysqli_connect("localhost","manageatt","manage123","hrhdashboard");
        
//    $month="January";
//    $year = "2021";

       $currentMonth = date('F');
       $month = Date('F', strtotime($currentMonth . " last month"));
       $year = date("Y");
       $month; echo $year;
        $sql1 =	"SELECT * FROM temp_attendance WHERE month='$month' AND year='$year'";
	//echo $sql1;					
	$result1= mysqli_query($mysqli, $sql1);
                                        
                                       
                                while($row1 = mysqli_fetch_assoc($result1)){
                                  
                                    
                                    $person_id = $row1['person_id'];
                                    
                                    $district_id = $row1['district_id'];
                                    
                                    $district = $row1['district'];
                                     
                                    
                                    $facility_id = $row1['facility_id'];

				    $month = $row1['month'];
                                    
                                    $year = $row1['year'];
                                    
                                    $work_days = $row1['work_days'];
                                    
                                    $off_days = $row1['off_days'];
                                    
                                    
                                    $leave_days = $row1['leave_days'];	

				    $other_days= $row1['other_days'];
                                    
                                    $daysPresent = $row1['daysPresent'];
                                    
                                    $daysOffDuty = $row1['daysOffDuty'];

				    $daysOnLeave = $row1['daysOnLeave'];

				    $daysRequest = $row1['daysRequest'];

				    $daysAbsent = $row1['daysAbsent'];

				    $absolute_days_absent = $row1['absolute_days_absent'];

				    $days_not_at_facility = $row1['days_not_at_facility'];
                                    
                                  
                                    
								   
				$SQL3 = mysqli_query($mysqli, "INSERT INTO attendance (`person_id`,`district_id`,`district`,`facility_id`,`month`,`year`,`work_days`,`off_days`,`leave_days`,`other_days`,`daysPresent`,`daysOffDuty`,`daysOnLeave`,`daysRequest`,`daysAbsent`,`absolute_days_absent`,`days_not_at_facility`) VALUES ('$person_id','$district_id','$district','$facility_id','$month','$year','$work_days','$off_days','$leave_days','$other_days','$daysPresent','$daysOffDuty','$daysOnLeave','$daysRequest','$daysAbsent','$absolute_days_absent','$days_not_at_facility')");				 
								  
								  
                                    
                                  }


			$SQL = mysqli_query($mysqli,"TRUNCATE TABLE temp_attendance");
									
									?>     

                                



