<?php
/*
Plugin Name: Twilio API Ends for Wordpress
Description: A wordpress plugin that sets up API Endpoints for you to use for your AJAX Front-ends
Version:  1.0.0
Author: Anthony Ahn - Antimony Software
*/
require_once( plugin_dir_path( __FILE__ ) .'/vendor/twilio-php-main/src/Twilio/autoload.php');
use Twilio\Rest\Client;

class TwilioApiEndsWP {
   public $pluginName = 'twapi';
   public $restName = '/v1/';

   public function displayTwapiSettingsPage(){
      include_once "twapi-admin-settings.php";
   }
   public function addTwapiOptions(){
      add_options_page(
         "Twilio API Endpoint Creator Settings",
         "Twilio API Endpoint Settings",
         "manage_options",
         $this->pluginName,
         [$this, "displayTwapiSettingsPage"]
      );
   }
   public function twapiOptionsSave(){
      register_setting(
         $this->pluginName,
         $this->pluginName,
         [$this,"pluginOptionsValidate"]
      );
      add_settings_section(
         "twapi_main",
         "General Settings",
         [$this, "twapiSectionText"],
         "twapi-settings-page",
      );
      add_settings_field(
         'twapi_sid',
         "Twilio API SID",
         [$this, "twapiSettingSid"],
         "twapi-settings-page",
         "twapi_main",
      );
      add_settings_field(
         'twapi_auth_token',
         "Twilio Auth Token",
         [$this, "twapiSettingToken"],
         "twapi-settings-page",
         "twapi_main",
      );
      add_settings_field(
         'twapi_from_num',
         "Twilio From Number",
         [$this, "twapiFromNumber"],
         "twapi-settings-page",
         "twapi_main",
      );
   }
   //Header Text
   public function twapiSectionText() {
      echo '<h2 style="text-decoration: underline;">Twilio API Details</h2>';
      echo '<h3>Get these credentials from your Twilio Account after Logging In</h3>';
   }
   //Input Field for SID
   public function twapiSettingSid() {
      $options = get_option($this->pluginName);
      echo "
         <input id='$this->pluginName[twapi_sid]'
         name='$this->pluginName[twapi_sid]'
         size='40'
         type='text'
         value='{$options['twapi_sid']}'
         placeholder='Enter your API SID' 
         />";
   }
   //Input Field for SID
   public function twapiSettingToken() {
      $options = get_option($this->pluginName);
      echo "
         <input id='$this->pluginName[twapi_auth_token]'
         name='$this->pluginName[twapi_auth_token]'
         size='40'
         type='text'
         value='{$options['twapi_auth_token']}'
         placeholder='Enter your API Auth Token' 
         />";
   }
   //Input Field for SID
   public function twapiFromNumber() {
      $options = get_option($this->pluginName);
      echo "
         <input id='$this->pluginName[twapi_from_num]'
         name='$this->pluginName[twapi_from_num]'
         size='40'
         type='text'
         value='{$options['twapi_from_num']}'
         placeholder='Enter your From Number (optional)' 
         />";
   }
   public function pluginOptionsValidate($input)
   {
      $newinput["twapi_sid"] = trim($input["twapi_sid"]);
      $newinput["twapi_auth_token"] = trim($input["twapi_auth_token"]);
      $newinput["twapi_from_num"] = trim($input["twapi_from_num"]);
      return $newinput;
   }
   public function registerTwapiTestPage(){
      add_submenu_page(
         'tools.php', // parent slug
         __("Twilio Test SMS Page", $this->pluginName . "-sms"),
         __("Twilio Test SMS", $this->pluginName . "-sms"),
         "manage_options",
         $this->pluginName . "-sms",
         [$this, "displayTwapiTestPage"]
      );
   }
   public function displayTwapiTestPage(){
      include_once "twilio-test-page.php";
   }
   public function send_message_test(){
      if(!isset($_POST["send_sms_message"])){
         return;
      }
      //gets our api details from the database.
      $api_details = get_option($this->pluginName);
      $to         = (isset($_POST["numbers"])) ? $_POST["numbers"] : '';
      $sender_id  = (isset($_POST["sender"])) ? $_POST["sender"] : $api_details["twapi_from_num"];
      $message    = (isset($_POST["message"])) ? $_POST["message"] : '';


      if (is_array($api_details) and count($api_details) != 0) {
         $TWILIO_SID = $api_details["twapi_sid"];
         $TWILIO_TOKEN = $api_details["twapi_auth_token"];
      }

      try {
         $client = new Client($TWILIO_SID, $TWILIO_TOKEN);
         $response = $client->messages->create(
               $to,
               array(
                  "from" => $sender_id,
                  "body" => $message
               )
         );
         self::DisplaySuccess();
      } catch (Exception $e) {
         self::DisplayError($e->getMessage());
      }
   }
   public static function adminNotice($message, $status=true){
      $class = ($status) ? "notice notice-success" : "notice notice-error";
      $message = __($message, "sample-text-domain");
      printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
   }

   public static function DisplayError($message = "There was an error sending the mesage.") {
      add_action( 'adminNotices', function() use($message) {
          self::adminNotice($message, false);
      });
   }
   public static function DisplaySuccess($message = "Message Sent Successfully!") {
      add_action( 'adminNotices', function() use($message) {
          self::adminNotice($message, true);
      });
   }
   //Now Use the Wordpress REST API
   public function registerTwapiAPIConfig(){
      add_options_page(
         "Twilio API Register Endpoints",
         "Twilio API Register Endpoints",
         "manage_options",
         $this->pluginName . '-api',
         [$this, "displayTwapiAPIEndsPage"]
      ); 
   }
   public function displayTwapiAPIEndsPage(){
      include_once "twapi-api-ends-creator.php";
   }
   
   public function twapiAPIEndsSave(){
      register_setting(
         $this->pluginName . '-api',
         $this->pluginName . '-api',
         [$this,"pluginAPIEndsValidate"]
      );
      add_settings_section(
         "twapi_api_ends",
         "Register API Endpoint",
         [$this, "twapiAPIText"],
         "twapi-api-ends-page",
      );
      add_settings_field(
         'twapi_user_end',
         "Enter API URL (no spaces, all lowercase)",
         [$this, "twapiApiUserEnd"],
         "twapi-api-ends-page",
         "twapi_api_ends",
      );
      add_settings_field(
         'twapi_user_allow',
         "Enable API Endpoing (must be checked to use)",
         [$this, "twapiApiUserAllow"],
         "twapi-api-ends-page",
         "twapi_api_ends",
      );
   }
   public function twapiAPIText(){
      $options = get_option($this->pluginName . '-api');
      $cleanUrl = home_url() . '/wp-json/' . $this->pluginName . $this->restName. $options['twapi_user_end'];
      echo '<h3>API Endpoints should be all lower case with no spaces or special characters...add Security to Prevent Unwanted Use of Twilio Texting</h3>';
      if($options['twapi_user_end'] && $options['twapi_user_allow']){
         echo "<span style='color:green;' class='dashicons dashicons-admin-plugins'></span> Current Active Endpoint: <strong>$cleanUrl</strong>";
      } else if ($options['twapi_user_end'] && !$options['twapi_user_allow']) {
         echo "<span class='dashicons dashicons-controls-pause' style='color:blue;'></span> <em>REST API Endpoint Currently Disabled</em>";
      } else {
         echo "<em>Please Register an API Endpoint</em>";
      }
   }
   public function twapiApiUserEnd() {
      $options = get_option($this->pluginName . '-api');
      $baseUrl = home_url() . '/wp-json/' . $this->pluginName . $this->restName;
      echo "
         <span>$baseUrl</span>
         <input id='$this->pluginName-api[twapi_user_end]'
         name='$this->pluginName-api[twapi_user_end]'
         size='40'
         type='text'
         value='{$options['twapi_user_end']}'
         placeholder='Enter slug' 
         />";   
   }
   public function twapiApiUserAllow() {
      $options = get_option($this->pluginName . '-api');
      $checkStatus = $options['twapi_user_allow'] || $options['twapi_user_allow'] == '1' ? '1' : '0'; 
      echo "
         <input id='$this->pluginName-api[twapi_user_allow]'
         name='$this->pluginName-api[twapi_user_allow]'
         size='40'
         type='checkbox'
         value='1' ";
         checked(1 == $options['twapi_user_allow']);
         echo " />";   
   }


   public function pluginAPIEndsValidate($input)
   {
      $newinput["twapi_user_end"] = strtolower(str_replace(" ","",trim($input["twapi_user_end"])));
      $newinput["twapi_user_allow"] = $input["twapi_user_allow"] == '1' ? 1 :0;
      return $newinput;
   }

   public function processAPIsend(WP_REST_Request $request){
      $phoneNum = $request['phonenum'];
      $daUrl = $request['da-url'];
      //gets our api details from the database.
      $api_details = get_option($this->pluginName);
      $to         = $phoneNum;
      $sender_id  = $api_details["twapi_from_num"];
      $message    = 'test' . $daUrl;
      $TWILIO_SID = $api_details["twapi_sid"];
      $TWILIO_TOKEN = $api_details["twapi_auth_token"];

      try {
         $client = new Client($TWILIO_SID, $TWILIO_TOKEN);
         $response = $client->messages->create(
               $to,
               array(
                  "from" => $sender_id,
                  "body" => $message
               )
         );
         $statusMsg = 'Sent Successfully';
      } catch (Exception $e) {
         $statusMsg = $e->getMessage();
      }     
      
      $response = array(
         'status'  => 200,
         'message' => $statusMsg
      );       
      return new WP_REST_Response($response);

   }
   public function isValidPhone($param, $request, $key){      
      $cleanPhone = preg_replace('/\D+/', '', $request['phonenum']);
      $cleanPhone  = '+' . $cleanPhone;
      return strlen($cleanPhone) > 7 && strlen($cleanPhone) < 15 ? true:false;
   }
   public function sanitizePhone($param, $request, $key){
      $cleanPhone = preg_replace('/\D+/', '', $request['phonenum']);
      $cleanPhone  = '+' . $cleanPhone;
      return $cleanPhone;
   }
   public function addRESTEnd(){
      //register_rest_route('twapi/v1', '/yeah', array(
      $options = get_option($this->pluginName . '-api');
      $goAheadAPI = $options['twapi_user_allow'] == '1' ? true : false;
      $daSlug = $options['twapi_user_end'];
      if($goAheadAPI == true){
      register_rest_route($this->pluginName . $this->restName, "$daSlug/(?P<phonenum>\d+)", array(
          'methods' => 'GET',
          'callback' => array($this,'processAPIsend'),
          'permission_callback' => "__return_true",
          'args' => array(
             'phonenum' => array(
                'validate_callback' => [$this, "isValidPhone"],
                'sanitize_callback' => [$this, "sanitizePhone"]
             ),
             'da-url' => array()
            )
      ));
      }
   }
}
//Execute the stuff
$twapInit = new TwilioApiEndsWP();
add_action("admin_menu", [$twapInit,"addTwapiOptions"]);
add_action("admin_init", [$twapInit,"twapiOptionsSave"]);
add_action("admin_menu", [$twapInit,"registerTwapiTestPage"]);
add_action("admin_init", [$twapInit,"send_message_test"]);
add_action("admin_menu", [$twapInit,"registerTwapiAPIConfig"]);
add_action("admin_init", [$twapInit,"twapiAPIEndsSave"]);
add_action("rest_api_init", [$twapInit, "addRESTEnd"]);