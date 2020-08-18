<?php 
/*   
     version: 1.0.0
     title: Default(origin code)
     Description: Engine code optimized to score blog posts.
     Author: Ari
     Date of Edit: 03/22/19

     Please add updates below this line in the:
     [version, title, Description, Author, Date of Edit]
     format seen above


     ********UPDATES BELOW THIS LINE********
     ---------------------------------------
     


*/
// Validate URL to POST ID 
// Will be expanded for param regex
function check_URL($URL){
     if(isset($URL)){
           if(url_to_postid($URL) != 0 && strpos($URL, '/404') === false){
               return url_to_postid($URL);
           }
           else{
               return false;
           }
      }
      else{
          return false;
      }
}
//Array Cleanup 
//**Removes unnecessary items (none blog posts)
function finalize_array($curr_array){
     $array = array();
     foreach ($curr_array as $value) {
          //Verify URL to Page ID
          $URL_ID = check_URL($value['pageURL']);
          
          if($URL_ID != false){
               //Verify Page Type to be post type
               $the_post_type = get_post_type($URL_ID);
               if($the_post_type === "post"){
                    array_push($array, array(
                         "pageType"=> $the_post_type,
                         "pageID" => $URL_ID, 
                         "pageURL" => $value['pageURL'], 
                         "overAllConversions" => $value['overAllConversions'], 
                         "userCount" => $value['userCount'], 
                         "avgTimeSpent" => $value['avgTimeSpent'],
                         "avgTimeSpentScore" => NULL,
                         "userCountScore" => NULL,
                         "overAllConversionsScore" => NULL,
                         "pageScore" => NULL));  
               }
          }
     } 
     return $array;
                                 
}



//Scoring utilities
function Median($Array) {
  return Quartile_50($Array);
}

function Quartile_25($Array) {
  return Quartile($Array, 0.25);
}

function Quartile_50($Array) {
  return Quartile($Array, 0.5);
}

function Quartile_75($Array) {
  return Quartile($Array, 0.75);
}

function Quartile($Array, $Quartile) {
  sort($Array);
  $pos = (count($Array) - 1) * $Quartile;

  $base = floor($pos);
  $rest = $pos - $base;

  if( isset($Array[$base+1]) ) {
    return $Array[$base] + $rest * ($Array[$base+1] - $Array[$base]);
  } else {
    return $Array[$base];
  }
}

function Average($Array) {
  return array_sum($Array) / count($Array);
}

function StdDev($Array) {
  if( count($Array) < 2 ) {
    return;
  }

  $avg = Average($Array);

  $sum = 0;
  foreach($Array as $value) {
    $sum += pow($value - $avg, 2);
  }

  return sqrt((1 / (count($Array) - 1)) * $sum);
}

function mmmr($array, $output = 'mean'){ 
    if(!is_array($array)){ 
        return FALSE; 
    }else{ 
        switch($output){ 
            case 'mean': 
                $count = count($array); 
                $sum = array_sum($array); 
                $total = $sum / $count; 
            break; 
            case 'median': 
                rsort($array); 
                $middle = round(count($array),  2); 
                $total = $array[$middle-1]; 
            break; 
            case 'mode': 
                $v = array_count_values($array); 
                arsort($v); 
                foreach($v as $k => $v){$total = $k; break;} 
            break; 
            case 'range': 
                sort($array); 
                $sml = $array[0]; 
                rsort($array); 
                $lrg = $array[0]; 
                $total = $lrg - $sml; 
            break; 
        } 
        return $total; 
    } 
} 


function calc_increment($array, $inc){
     if (!empty($array)) {
          $total = 0;
          $max_val = max($array);
          $min_val = min($array);
     
          $increment = ($max_val - $min_val) / $inc;
          return $increment;
     } 
}

//Adds timeScore per item
// @param $array thats been fleshed and ready to have items scored
// @param $values_to_score thats been fleshed and ready to have items scored
//Returns nothing adds keyed value to array
function calc_sub_score($array, $values_to_score){
     $final_array = $array;
     foreach ($values_to_score as $metric_value) {
          /*start with finding max/mins*/
          /*Create ranges and quartiles for each item to be judged on*/
          $extracted_array = array();
          foreach ($array as $newVal) {
               array_push($extracted_array, $newVal[$metric_value]);
          }
          //define Range
          $max_val = max($extracted_array);
          $min_val = min($extracted_array);

          
          //Define Mean(avg) 
          $mean = mmmr($extracted_array);
          
          //Find Quartiles
          $Quartile_25 = Quartile_25($extracted_array);
          $Quartile_50 = Quartile_50($extracted_array);
          $Quartile_75 = Quartile_75($extracted_array);

          //Find Standard Deviation
          $StdDev = StdDev($extracted_array);

          //Scoring hash
          $C_score_range = [$Quartile_50-($StdDev/2) , $Quartile_50+($StdDev/2)];
          $D_score_range = [$C_score_range[0]-($StdDev/2) , $C_score_range[0]]; 
          $B_score_range = [$C_score_range[1] , $C_score_range[1]+($StdDev/2)]; 
          $F_score_range = [$D_score_range[0]]; 
          $A_score_range = [$B_score_range[1]]; 

          //Find Score Range 
          // Give items Letter Grades to dictate Range
          foreach ($final_array as $key => $Val) {
               $current_value = $Val[$metric_value];

               if($F_score_range[0] > $current_value){
                    $final_array[$key][$metric_value.'Score'] = 'F';
               }
               else if($D_score_range[1] > $current_value && $D_score_range[0] <= $current_value){
                    $final_array[$key][$metric_value.'Score'] = 'D';
               }
               else if($C_score_range[1] > $current_value && $C_score_range[0] <= $current_value){
                    $final_array[$key][$metric_value.'Score'] = 'C';
               }
               else if($B_score_range[1] > $current_value && $B_score_range[0] <= $current_value){
                    $final_array[$key][$metric_value.'Score'] = 'B';
               }
               else if($A_score_range[0] <= $current_value){
                    $final_array[$key][$metric_value.'Score'] = 'A';
               } 
          }

          //Bucket Sort Each item into Grade Arrays
          //Range / 10 = INCREMENTAL VALUE 
          //Find incremental Value of each item within the bucket 
          //Replace Letter Grades with specific Distinction of Grade

          //Find incremental Value with itemized loop
          $Array_A = array();
          $Array_B = array();
          $Array_C = array();
          $Array_D = array();
          $Array_F = array();

          foreach ($final_array as $currkey => $currVal) {
               if($final_array[$currkey][$metric_value.'Score'] == 'F'){
                    array_push($Array_F, $final_array[$currkey][$metric_value]);
               }
               if($final_array[$currkey][$metric_value.'Score'] == 'D'){
                    array_push($Array_D, $final_array[$currkey][$metric_value]);
               }
               if($final_array[$currkey][$metric_value.'Score'] == 'C'){
                    array_push($Array_C, $final_array[$currkey][$metric_value]);
               }
               if($final_array[$currkey][$metric_value.'Score'] == 'B'){
                    array_push($Array_B, $final_array[$currkey][$metric_value]);
               }
               if($final_array[$currkey][$metric_value.'Score'] == 'A'){
                    array_push($Array_A, $final_array[$currkey][$metric_value]);
               }
          }

          //incremental values per group
          $increment_A = calc_increment($Array_A, 9.99);
          $increment_B = calc_increment($Array_B, 9.99);
          $increment_C = calc_increment($Array_C, 9.99);
          $increment_D = calc_increment($Array_D, 9.99);
          $increment_F = calc_increment($Array_F, 59.99);


          //Turn Letter Grades into numbers using increments
          /**/
          foreach ($final_array as $met_key => $met_Val) {
               $number_value = $met_Val[$metric_value];
               $letter_value = $met_Val[$metric_value.'Score'];
               $Score = 0;
               if($letter_value == 'A'){
                    $Score = round((($number_value - min($Array_A)) / $increment_A) + 90, 4);
                    $final_array[$met_key][$metric_value.'Score'] = $Score*.01;

               }
               else if($letter_value == 'B'){
                    $Score = round((($number_value - min($Array_B)) / $increment_B) + 80, 4);
                    $final_array[$met_key][$metric_value.'Score'] = $Score*.01;

               }
               else if($letter_value == 'C'){
                    $Score = round((($number_value - min($Array_C)) / $increment_C) + 70, 4);
                    $final_array[$met_key][$metric_value.'Score'] = $Score*.01;

               }
               else if($letter_value == 'D'){
                    $Score = round((($number_value - min($Array_D)) / $increment_D) + 60, 4);
                    $final_array[$met_key][$metric_value.'Score'] = $Score*.01;

               }
               else if($letter_value == 'F'){
                    $Score = round((($number_value - min($Array_F)) / $increment_F), 4);
                    $final_array[$met_key][$metric_value.'Score'] = $Score*.01;

               }

          }

     }
     return $final_array;


}
function date_diff_days($postid){
     $currentdate = strtotime(date("Y-m-d"));
     $date_of_post = strtotime(get_the_time('Y-m-d', $postid));
     $datediff = $currentdate - $date_of_post;

     return round($datediff / (60 * 60 * 24));     
}


//Calculate Post relevency by Date (within 10 days)
function post_is_recent($postid){
     $days_apart = date_diff_days($postid);
     if ($days_apart <= 10) {
          return true;
     }
     else{
          return false;
     }

}


/*
function calc_non_analytics(){
     $allposts = get_posts(array('posts_per_page' => -1, 'post_type' => 'post', 'orderby' => 'menu_order'));     
     $currScore = 0;
     $curr_page_score = get_post_meta($thispost->ID,'pageScore');
     $curr_page_score = $curr_page_score[0];
     foreach ($allposts as $thispost) {
          if (post_is_recent()) {
               $start_score = 100;
               $date_diff = date_diff_days($thispost->ID);
               $currScore = $start_score - $date_diff;
               update_post_meta($thispost->ID,'pageScore' ,$currScore);

          }
          else if($curr_page_score == '' || !isset($curr_page_score)){
               $currScore = 0;
          }
          else{
               $currScore = 0;
          }          
          update_post_meta($thispost->ID,'pageScore' ,$currScore);
     }
}



*/


//Adds pageScore per item
//returns array with final page score item filled
/*weights:
     50%   Conversions (overAllConversionsScore)                 
     15%   User Count (userCountScore)                  
     10%   Avg time on page (avgTimeSpentScore)            
     10%   Conversions VS User Count               
     10%   Conversions VS Avg Time                 
     5%    Avg Time VS User Count                  
*/
function calc_page_score($fleshed_array){
     //init Scored Array
     $values_to_score = ['userCount','overAllConversions', 'avgTimeSpent'];
     $scored_arrayed = calc_sub_score($fleshed_array, $values_to_score);

     //init Variable Weights
     $conv_weight = .50;
     $user_weight = .15;
     $time_weight = .10;
     $conv_user_weight = .10;
     $conv_time_weight = .10;
     $time_user_weight = .05;

     foreach ($scored_arrayed as $key => $Val) {
          $overAllConversionsScore = $Val['overAllConversionsScore'];
          $userCountScore = $Val['userCountScore'];
          $avgTimeSpentScore = $Val['avgTimeSpentScore'];

          $conv_score = $overAllConversionsScore * $conv_weight;
          $user_score = $userCountScore * $user_weight;
          $time_score = $time_weight * $avgTimeSpentScore;
          $conv_user_score = mmmr([$overAllConversionsScore, $userCountScore]) * $conv_user_weight;
          $conv_time_score = mmmr([$overAllConversionsScore, $avgTimeSpentScore]) * $conv_time_weight;
          $time_user_score = mmmr([$avgTimeSpentScore, $userCountScore]) * $time_user_weight;

          $pageScore = round(array_sum([$conv_score, $user_score, $time_score, $conv_user_score, $conv_time_score, $time_user_score]), 2);
          if($pageScore > 1){
               $pageScore = 1;
          }
          $scored_arrayed[$key]['pageScore'] = $pageScore;

     }
     return $scored_arrayed;


}

function score_to_posts($array){
     $post_array = calc_page_score($array);
     $option_view = array();
     foreach ($post_array as $cat => $val) {
          $current_score = $val['pageScore'];
          $current_id = $val['pageID'];
          $up_items = [$current_id, $current_score];
          array_push($option_view, $up_items);
          update_post_meta( $current_id,'pageScore', $current_score);
          //calc_non_analytics();
     }


}


//DISPLAY TESTS
//[init_relativity] shortcode available
add_shortcode( 'init_relativity', 'init_relativity' );
function init_relativity(){
     $json_pull = get_option('analytics_pull');
     $json_pull = json_decode($json_pull, true);
     $array_fleshed = finalize_array($json_pull);
     score_to_posts($array_fleshed);
}
?>