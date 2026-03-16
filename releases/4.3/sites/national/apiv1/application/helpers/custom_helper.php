<?php

/*
 * Custom Helpers
 *
 */




if (!function_exists('get_flash')) {
    function get_flash($key)
    {
        // Get a reference to the controller object
        $ci =& get_instance();
        return $ci->session->flashdata($key);
    }
}

//set flash data
if (!function_exists('set_flash')) {
    function set_flash($message,$isError=false)
    {
        // Get a reference to the controller object
        $ci =& get_instance();
        $msgClass =  ($isError)?'danger':'success';
        return $ci->session->set_flashdata($msgClass,$message);
    }
}

if (!function_exists('get_flash')) {
    function get_flash($key)
    {
        // Get a reference to the controller object
        $ci =& get_instance();
        return $ci->session->flashdata($key);
    }
}

//read from language file

if (!function_exists('lang')) {
    function lang($string)
    {
        $ci =& get_instance();
        return $ci->lang->line($string);
    }
}

//print old form data
if (!function_exists('old')) {
    function old($field)
    {
        $ci =& get_instance();
        return html_escape($ci->session->flashdata('form_data')[$field]);
    }
}

//print old form data
if (!function_exists('truncate')) {
    function truncate($content,$limit)
    {
      return (strlen($content)>$limit)? substr($content,0,$limit)."...":$content;
    }
}


if (!function_exists('paginate')) {
function paginate($route,$totals,$perPage=20,$segment=2)
    {
        $ci =& get_instance();
        $config = array();
        
        $config["base_url"] = base_url().$route;
        $config["total_rows"]     = $totals;
        $config["per_page"]       = $perPage;
        $config["uri_segment"]    = $segment;
        $config['full_tag_open']  = '<br> <nav><ul class="pagination">';
        $config['full_tag_close'] = '</ul></nav>';
        $config['first_link'] = 'first';
        $config['last_link'] = 'last';
        $config['first_tag_open'] = '<li class="page-item">';
        $config['first_tag_close'] = '</li>';
        $config['prev_link'] = '&laquo';
        $config['prev_tag_open'] = '<li class="page-item prev">';
        $config['prev_tag_close'] = '</li>';
        $config['next_link'] = '&raquo';
        $config['next_tag_open'] = '<li class="page-item">';
        $config['next_tag_close'] = '</li>';
        $config['last_tag_open'] = '<li class="page-item">';
        $config['last_tag_close'] = '</li>';
        $config['cur_tag_open'] = '<li class="active page-item"><a class="page-link" href="#">';
        $config['cur_tag_close'] = '</a></li>';
        $config['num_tag_open'] = '<li class="page-item">';
        $config['num_tag_close'] = '</li>';

        $ci->pagination->initialize($config);
       
        return $ci->pagination->create_links();
    }
}

if (!function_exists('time_ago')) {

function time_ago($timestamp)
{
    $time_ago = strtotime($timestamp);
    $current_time = time();
    $time_difference = $current_time - $time_ago;
    $seconds = $time_difference;

    $minutes = round($seconds / 60);           // value 60 is seconds
    $hours = round($seconds / 3600);           //value 3600 is 60 minutes * 60 sec
    $days = round($seconds / 86400);          //86400 = 24 * 60 * 60;
    $weeks = round($seconds / 604800);          // 7*24*60*60;
    $months = round($seconds / 2629440);     //((365+365+365+365+366)/5/12)*24*60*60
    $years = round($seconds / 31553280);     //(365+365+365+365+366)/5 * 24 * 60 * 60

    if ($seconds <= 60) {
        return "Just now";
    } else if ($minutes <= 60) {
        if ($minutes == 1) {
            return "1 " . "Minute" . " " . "ago";
        } else {
            return $minutes . " " ."Minutes" . " ago";
        }
    } else if ($hours <= 24) {
        if ($hours == 1) {
            return "1 " . "hour" . " " . "ago";
        } else {
            return $hours . " " . "hours" . " " . "ago";
        }
    } else if ($days <= 30) {
        if ($days == 1) {
            return "1 " . "day" . " " . "ago";
        } else {
            return $days . " " . "days". " " . "ago";
        }
    } else if ($months <= 12) {
        if ($months == 1) {
            return "1 " . "month" . " " . "ago";
        } else {
            return $months . " " . "months" . " " . "ago";
        }
    } else {
        if ($years == 1) {
            return "1 " . "year" . " " . "ago";
        } else {
            return $years . " " . "years". " " . "ago";
        }
    }
}
}


if (!function_exists('is_past')) {

    function is_past($date){
        $date_now = new DateTime();
        $date2    = new DateTime($date);
        return ($date_now > $date2);
    }
}

if (!function_exists('text_date')) {

    function text_date($date){
        return date("M jS, Y", strtotime($date));;
    }
}

if (!function_exists('setting')) {

    function setting(){
        $ci =& get_instance();
        return $ci->db->get('setting')->row();
    }
}


if (!function_exists('user')) {
    function user(){
        $ci =& get_instance();
        return (Object) $ci->session->userdata();
    }
}


if (!function_exists('get_subscriber')) {
    function get_subscriber_name($id){

        if(!$id)
         return 'N/A';

        $ci =& get_instance();
        $ci->db->where('id',$id);
        $query = $ci->db->get('ncda_subscribers')->row();
       return $query->subscriber_name;
    }
}

if (!function_exists('get_subscribers')) {
    function get_subscribers(){
        $ci =& get_instance();
        return $ci->db->get('ncda_subscribers')->result();
    }
}


if (!function_exists('dd')) {
    function dd($data){
        print_r($data);
        exit();
    }
}


if (!function_exists('poeple_titles')) {
    function poeple_titles(){
         $titles = ['Mr.','Mrs.','Ms.','Hon.','Dr.','Pr.','He.','Hh.'];
         return $titles;
    }
}






?>