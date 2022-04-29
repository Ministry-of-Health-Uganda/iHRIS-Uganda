<?php 

class I2ceBrige extends I2CE_Page{

    public  function __construct(){

        $this->CI = & get_instance();
    }


 public function curlsendHttpPost($url,$headers,$body){
    $ch = curl_init($url);
     //post values
    curl_setopt($ch,CURLOPT_POSTFIELDS,json_encode($body));
    // Option to Return the Result, rather than just true/false
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    // Set Request Headers
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers
    );
    //time to wait while waiting for connection...indefinite
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);

    curl_setopt($ch,CURLOPT_POST,1);
    //set curl time..processing time out
    curl_setopt($ch, CURLOPT_TIMEOUT, 200);
    // Perform the request, and save content to $result
    $result = curl_exec($ch);
      //curl error handling
      $curl_errno = curl_errno($ch);
              $curl_error = curl_error($ch);
              if ($curl_errno > 0) {
                     curl_close($ch);
                    return  "CURL Error ($curl_errno): $curl_error\n";
                  }
        $info = curl_getinfo($ch);
       curl_close($ch);
       $decodedResponse =json_decode($result);
       return $decodedResponse;
}

public function curlgetHttp($url,$headers,$body){

    $ch = curl_init($url);

     //post values
    // curl_setopt($ch,CURLOPT_POSTFIELDS,json_encode($body));
    // Option to Return the Result, rather than just true/false
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    // Set Request Headers
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers
    );
    //time to wait while waiting for connection...indefinite
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);

    // curl_setopt($ch,CURLOPT_POST,1);
    //set curl time..processing time out
    curl_setopt($ch, CURLOPT_TIMEOUT, 200);
    // Perform the request, and save content to $result
    $result = curl_exec($ch);
      //curl error handling
      $curl_errno = curl_errno($ch);
              $curl_error = curl_error($ch);
              if ($curl_errno > 0) {
                     curl_close($ch);
                    return  "CURL Error ($curl_errno): $curl_error\n";
                  }
        $info = curl_getinfo($ch);
       curl_close($ch);
       $decodedResponse =json_decode($result);
       return $decodedResponse;
}
  

}



?>