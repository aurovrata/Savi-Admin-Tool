<?php
/*
Plugin Name: SAVI-VOLUNTEER-Connect
Plugin URI: http://codex.wordpress.org/Adding_Administration_Menus
Description: SAVI-VOLUNTEER-Connect NEW PLUGIN FOR create new volunteer to the opportunity
Author: Syllogic
Author URI: http://wordpress.syllogic.in
*/

if (!class_exists("Enquiry_Review_List_Table"))
    include_once("enquiry-review.php");

//if (!class_exists("Edit_Profile_List_Table"))
   // include_once("edit-profile.php");
class SaviVolunteerConnect {
    public function __construct() {
        $enquiry_review_Obj = new Enquiry_Review_init();
      
    }
}
$savivolunteer_obj = new SaviVolunteerConnect();
?>
