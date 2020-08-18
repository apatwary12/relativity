<?php 
/*
Plugin Name: Relativity
Plugin Name: https://ari-senpai.ninja
Description: Pulls Analytics data from google API and then is used to create reletive page scores for all feeds to be more conversion focused.
Author: Ari
Author URI: https://ari-senpai.ninja
Version: 1.0

Please add updates below this line in the:
[version, title, Description, Author, Date of Edit]
format seen above


********UPDATES BELOW THIS LINE********
---------------------------------------

*/

//include api functions and scoring engine
include plugin_dir_path( __FILE__ ) . '/google_api_pull.php';
include plugin_dir_path( __FILE__ ) . '/score_engine.php';



//INIT SETTING START 

add_action( 'admin_menu', 'relativity_add_admin_menu' );
add_action( 'admin_init', 'relativity_settings_init' );

function relativity_add_admin_menu(  ) { 
     add_options_page( 'Relativity', 'Relativity', 'manage_options', 'relativity', 'relativity_options_page' );
}
function relativity_settings_init(  ) { 

     register_setting( 'pluginPage_relativity', 'relativity_settings' );

     add_settings_section(
          'relativity_plugin_section_1', 
          __( 'Add relativity to the following post types:', 'wordpress' ), 
          'relativity_settings_section_callback', 
          'pluginPage_relativity'
     );

     // get CPTs, loop checkboxes
     $cpt = get_post_types(array('public'=>true), 'objects');
     foreach($cpt as $type){
          
          add_settings_field( 
               $type->name, 
               __( $type->labels->name, 'wordpress' ), 
               'relativity_checkbox_field_render', 
               'pluginPage_relativity', 
               'relativity_plugin_section_1',
               array('fieldname'=>$type->name,'cpt'=>$type)
          );
     }


}

function relativity_checkbox_field_render(array $args) { 
     $options = get_option( 'relativity_settings' );
     ?>
     <input type='checkbox' name='relativity_settings[<?php echo $args['fieldname'];?>]' <?php !empty($options[$args['fieldname']]) ?  checked( $options[$args['fieldname']], 1 ) : ''; ?> value='1'>
     <?php
}

function relativity_settings_section_callback(  ) { 
     echo __( '', 'wordpress' );
}


function relativity_options_page(  ) { 
     ?>

          
          <h2>Relativity</h2>
          <?php 
          echo '<form name="update_analytics" id="update_analytics" action="" class="update_analytics" method="GET">';     
          echo '<input value="Refresh Analytics" type="button" class="rvgamls_submit" style="background:#1c3364;color:#fff;padding:8px;border-radius:5px;cursor: pointer;" onClick="window.open(\'' .plugin_dir_url( __FILE__ ).'update_analytics.php' .'\', \'viewbox_iframe\'); return false"  />';               
          echo '<h5>Only hit submit once. Your data will take a few minutes to update. Once the process is complete your log will update below.</h5>';               
          echo '</form>';         
          echo '<hr />';          
          echo '<iframe width="100%" height="200" name="viewbox_iframe" id="rvct_viewbox_iframe" src="" frameborder="1" scrolling="yes"></iframe>';         
          ?>
          <form action='options.php' method='post'>
          <?php
          settings_fields( 'pluginPage_relativity' );
          do_settings_sections( 'pluginPage_relativity' );
          submit_button();
          ?>
          </form>
          <h2>Posts</h2>
          <?php 
          $posts = get_posts(array('posts_per_page' => -1, 'post_type' => 'post', 'orderby' => 'menu_order'));

          foreach ($posts as $currpost) {
               $current_score = get_post_meta( $currpost->ID, 'pageScore');
               $current_score = $current_score[0] * 100;
               echo '<div style="width:80%;background-color:#fff;padding:20px;margin-top:20px;margin-bottom:20px;float:left;border-radius:5px;" data-value="'. $current_score .'">';
               echo '<p><strong>Page ID:</strong>'. $currpost->ID . '<br><strong>Page Title:</strong> '. $currpost->post_title.'<br><strong>Page Score: </strong>'.$current_score.'%</p>';
               echo '</div>';
               echo '<div style="clear:both;"></div>';
          }

           ?>


     <?php
}



//INIT SETTING END






//global function to retrieve data
function update_analytics_pull(){
     $format_output = init_analytics_data();
     update_option('analytics_pull', $format_output);    
     init_relativity();
}








 