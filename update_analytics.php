<?php 
global $wpdb;
if(!isset($wpdb)){
    require_once($_SERVER['DOCUMENT_ROOT'] .'/wp-config.php');
    require_once($_SERVER['DOCUMENT_ROOT'] .'/wp-includes/wp-db.php');
}

function update_scores_post($array){
     $post_array = calc_page_score($array);
     $option_view = array();
     foreach ($post_array as $cat => $val) {
          $current_score = $val['pageScore'];
          $current_id = $val['pageID'];
          $up_items = [$current_id, $current_score];
          array_push($option_view, $up_items);
          //print_r('Pageid:'.$current_id.'pageScore'. ($current_score * 100));
          echo '<p>Pageid: '.$current_id.' Page Score: '. ($current_score * 100).'%</p>';
          update_post_meta( $current_id,'pageScore', $current_score);
     }
     echo 'Update completed';

}


//DISPLAY TESTS
function display_relativity(){
     $json_pull = get_option('analytics_pull');
     $json_pull = json_decode($json_pull, true);
     $array_fleshed = finalize_array($json_pull);
     update_scores_post($array_fleshed);
     //calc_non_analytics();
}





function update_relativity(){
     $format_output = init_analytics_data();
     update_option('analytics_pull', $format_output);
}
display_relativity();
update_relativity();



 ?>