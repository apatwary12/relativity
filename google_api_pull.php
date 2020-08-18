<?php 
/*   
     version: 1.0.0
     title: Default(origin code)
     Description: API pull code for posts to scored.
     Author: Ari
     Date of Edit: 03/22/19

     Please add updates below this line in the:
     [version, title, Description, Author, Date of Edit]
     format seen above


     ********UPDATES BELOW THIS LINE********
     ---------------------------------------
     


*/
// Load the Google API PHP Client Library.
require_once plugin_dir_path( __FILE__ ) . '/vendor/autoload.php';

/**
 * Initializes an Analytics Reporting API V4 service object.
 *
 * @return An authorized Analytics Reporting API V4 service object.
 */
function initializeAnalytics()
{

     // Use the developers console and download your service account
     // credentials in JSON format. Place them in this directory or
     // change the key file location if necessary.
     $KEY_FILE_LOCATION = plugin_dir_path( __FILE__ ) . '/key.json';

     // Create and configure a new client object.
     $client = new Google_Client();
     $client->setApplicationName("Hello Analytics Reporting");
     $client->setAuthConfig($KEY_FILE_LOCATION);
     $client->setScopes(['https://www.googleapis.com/auth/analytics.readonly']);
     $analytics = new Google_Service_AnalyticsReporting($client);

     return $analytics;
}

/**
 * Queries the Analytics Reporting API V4.
 *
 * @param service An authorized Analytics Reporting API V4 service object.
 * @return The Analytics Reporting API V4 response.
 */
function getReport($analytics, $start_date, $end_date) {

     // Replace with your view ID, for example XXXX.
     $VIEW_ID = "";

     // Create the DateRange object.
     $dateRange = new Google_Service_AnalyticsReporting_DateRange();
     $dateRange->setStartDate($start_date);
     $dateRange->setEndDate($end_date);

     // User count per page
     $users = new Google_Service_AnalyticsReporting_Metric();
     $users->setExpression("ga:users");
     $users->setAlias("users");

     // Goal Completion Pull
     $goalCompletions = new Google_Service_AnalyticsReporting_Metric();
     $goalCompletions->setExpression("ga:goalCompletionsAll");
     $goalCompletions->setAlias("Goals Completed");

     $avgTimeOnPage = new Google_Service_AnalyticsReporting_Metric();
     $avgTimeOnPage->setExpression("ga:avgTimeOnPage");
     $avgTimeOnPage->setAlias("Avg Time on page");
     

     //Get per page basis.
     $page = new Google_Service_AnalyticsReporting_Dimension();
     $page->setName("ga:pagePath");



     // Create the ReportRequest object.
     $request = new Google_Service_AnalyticsReporting_ReportRequest();
     $request->setViewId($VIEW_ID);
     $request->setDateRanges($dateRange);
     $request->setDimensions(array($page));
     $request->setMetrics(array($goalCompletions, $users, $avgTimeOnPage));



     $body = new Google_Service_AnalyticsReporting_GetReportsRequest();
     $body->setReportRequests( array( $request) );
     return $analytics->reports->batchGet( $body );
}

function getLatestData(){
     $analytics = initializeAnalytics();
     $i = 0;
     $start_date = date('Y-m-t', strtotime("-3 months"));
     $end_date = date("Y-m-t");
     $response[0] = getReport($analytics, $start_date, $end_date);
     $arraylength = $response[0]['reports'][0]['data']['rowCount'];
     for($j = 0; $j<$arraylength; $j++){
          $i++;
          $output[$i] = new stdClass();

               //Format multiples page URL vs Overall conversions for json output//
               //Get URL
               $output[$i] -> pageURL = $response[0]['reports'][0]['data']['rows'][$j]['dimensions'][0];
               //Get ALL CONVERSIONS
               $output[$i] -> overAllConversions = $response[0]['reports'][0]['data']['rows'][$j]['metrics'][0]['values'][0];
               //Get ALL USER COUNT
               $output[$i] -> userCount = $response[0]['reports'][0]['data']['rows'][$j]['metrics'][0]['values'][1];
               //Get  time spent on page
               $output[$i] -> avgTimeSpent = $response[0]['reports'][0]['data']['rows'][$j]['metrics'][0]['values'][2];

               
     }
     return $output;
}
//pulls data from google only call when necessary.
function init_analytics_data(){
     return json_encode(getLatestData(), true);
}

// create REST API
/**/
/*
add_action( 'rest_api_init', function(){
   register_rest_route( 'ga/v1', '/analytics_data', array(
     'methods' => 'GET',
     'callback' => 'getLatestData'
    ));  
});

*/
$format_output ='';
// create option with Page Data (FIRST INIT)
$myOption = get_option('analytics_pull');
if ($myOption === '') {
     $format_output = init_analytics_data();
     update_option('analytics_pull', $format_output);     
}
?>