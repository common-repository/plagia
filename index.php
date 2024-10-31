<?php 
/*
Plugin Name:  plagia
Plugin URI: https://logicielantiplagiat.fr
Description: un plugin de detetction de taux de plagiarisme de contenu
Version: 0.9.3
Author: Shayma Braham
Author URI: https://profiles.wordpress.org/shaymabraham/
License: GPL2
Text Domain: wp_plagia

*/

include(plugin_dir_path( __FILE__ ).'functions.php');
define ('PLAGIA_VERSION', '0.9.3');


function plagia_page()
{
  add_menu_page( 'Plagiarism settings','Plagiarism', 'administrator', 'plagia', 
    'plagia_function', 'dashicons-editor-alignleft', 40 );

  add_submenu_page( '','Check automatique', 'check automatique', 'administrator', 'plagia-auto', 'plagia_auto_function');


  
  
  
}

add_action( 'admin_menu','plagia_page' );





function  plagia_function()
{
  include('plagia_admin.php');


}

function  plagia_auto_function()
{
  include('plagia_auto.php');
  

}


function plagia_admin_settings(){


global $wpdb;
global $Puser;

      $table_name=$wpdb->prefix.'plagia_users';

$Puser= $wpdb->get_results( "SELECT * FROM  ".$table_name);



}

add_action( 'admin_menu','plagia_admin_settings' );

function plagia_admin_js(){


  wp_enqueue_script('palgia_js_admin', plugins_url( 'wp_plagiarism.js', __FILE__), array('jquery'), '1.0');
  wp_localize_script( 'palgia_js_admin', 'ajax_url',
            admin_url( 'admin-ajax.php' )  );
  wp_localize_script( 'palgia_js_admin', 'site_url',
            get_option("siteurl") );


    global $wpdb;
    global $Puser;
    $table_name=$wpdb->prefix.'plagia_users';

        $Puser= $wpdb->get_results( "SELECT * FROM  ".$table_name);

    wp_localize_script('palgia_js_admin', 'script_vars', array(
        'user'  => $Puser,));

  wp_enqueue_style( 'bt_css', plugins_url( 'assets/css/bootstrap.min.css',__FILE__) );
  wp_enqueue_script( 'bt_js', plugins_url( 'assets/js/bootstrap.bundle.min.js',__FILE__) );


  wp_enqueue_script( 'dataTables_plagia_js',  plugins_url( 'assets/js/jquery.dataTables.js',__FILE__) );
  wp_enqueue_style( 'dataTables_plagia_css', plugins_url( 'assets/css/jquery.dataTables.css',__FILE__) );

   wp_enqueue_script( 'swal_plagia_js',  plugins_url( 'assets/js/sweetalert2@10.js',__FILE__) );
 
       



}


add_action( 'admin_menu', 'plagia_admin_js');







function metabox_plagia()
{  
     $box='';
      global $wpdb;
      $table_name=$wpdb->prefix.'plagia_users';
       $p_user= $wpdb->get_results( "SELECT * FROM ".$table_name);

  if(sizeof($p_user))
  {
      $p_user=$p_user[0];



// TEST IF C EST LE MME TEXTE DANS LE POSTE ACTUEL ET CELUI DE DERNIER RAPPORT

      $table_rapport=$wpdb->prefix.'plagia_rapports';

 $rapports= $wpdb->get_results( "SELECT * FROM ".$table_rapport." where post_id = ".get_the_ID()." order by created_at desc");
 if(sizeof($rapports))
 {
  if($rapports[0]->reponse != '')
   {


 $last_reponse=json_decode($rapports[0]->reponse);

 
  if($last_reponse->success)
  {
   
    echo '<span style="font-weight:bold">'. __("Derniere modification de contenu le","wp_plagia").' : '.date('d M Y à H:i',strtotime(get_the_modified_date('Y-m-d H:i:s'))).'</span><br><br>';

     echo '<span style="font-weight:bold">'. __("Dernier rapport de plagirisme le ","wp_plagia").': '.date('d M Y à H:i',strtotime($rapports[0]->received_at)).'</span><br><br>';


if(get_the_modified_date('Y-m-d H:i:s') < $rapports[0]->received_at){
 


                     $box.= '<H2 style="font-weight:bold;font-size:19px;color:'.plagia_getColor($last_reponse->unique).'">'.$last_reponse->unique.' % '.__("unique","wp_plagia").'</H2>';
                        $box.= '<table  class="table table-bordered" >';
                        $box.= '<thead><tr>';
                        $box.= '<th>Results</th>';
                        $box.= '<th>Query</th>';
                        $box.= '<th>Domains</th>';
                        $box.= '</tr>';
                        $box.= '</thead>';
                        $box.= '<tbody>';

                        foreach ($last_reponse->results as $k => $v) {
                         

                         $box.="<tr>";
                            $box.="<td id='result_".$k."'>".$v->nb."</td>";
                            $box.="<td id='query_".$k."'><a target='_blank' href='".$v->url_req."'>".$v->req."</a></td>";

                            $box.="<td id='domains_".$k."'>";
                             

                              foreach ($v->domains as $c => $d){
                              $box.="<a target='_blank' href='".$d->url."'>".$d->domain."</a><br>";
                              }
                             
                            $box.="</td>";
                            $box.="</tr>";
                        }

                       
                        $box .='</tbody></table><br>';
    
 
}
    
  }

}
 }

$box.='<input type="hidden" id="status" value="'.get_post_status().'">';
$box.='<input type="hidden" id="postID" value="'.get_the_ID().'">';

   $box .='<label>'.__("Choisir le mode de vérification","wp_plagia").' :</label>';
    $box.='<br>
            <div class="custom-control custom-radio">
                <input type="radio" value="0" class="custom-control-input" id="live" name="mode" checked>
                <label class="custom-control-label" for="live">'.__("Mode instantanée","wp_plagia").'</label>
            </div>

            <div class="custom-control custom-radio">
                <input type="radio" value="1" class="custom-control-input" id="differe" name="mode" >
                 <label class="custom-control-label" for="differe">'.__("Mode différée","wp_plagia").' </label>
           </div>';
   $box.='<br><div id="btn_check_div"><button id="checkPlagia"  class="btn btn-primary">'.__("Vérifier le contenu dupliqué","wp_plagia").' </button></div><br>';
  
   $box.='<div class="row">';
   $box.='<div class="col-md-12"><h2></h2>';
    $box.='<div id="response"></div>';
   $box.='</div>';;
   $box.='</div>';

   $box.='<input type="hidden" id="post_id" value="'.get_the_ID().'">';


  }

  else{
 
 $box .='<div class="alert alert-danger" role="alert">';
   $box .= __("Pour vérifier le contenu dupliqué veuillez activer le plugin dans ","wp_plagia") ;
   $box .= '<a href="'.admin_url(). 'admin.php?page=plagia'.'">';
  $box .= __("Plagiarism > paramètres > Compte","wp_plagia");
   
     $box .='</a></div>';
      
      }
  echo $box;



}

function plagia_buttons_menu($wp_admin_bar){

require_once(ABSPATH . 'wp-admin/includes/screen.php');
$get_current_screen=get_current_screen();


if(isset($get_current_screen))
{
  if($get_current_screen->base== 'post' )
      {
          
       add_meta_box( 'plagiarism', 'Plagiarism', 'metabox_plagia', ['post','page','product']);
  
      }
}

 
 $title=__('Plagiarism','wp_plagia');
  $args = array(
      'id' => 'plagia',
      'title' => $title,
      'meta' => array(
         'class' => 'custom-button-class',
         
         )
     );

  //print_r($get_current_screen);
    $wp_admin_bar->add_node($args);


 $title=__('Tableau de bord','wp_plagia');

    $args = array(
      'id' => 'plagia_settings',
      'title' => $title,
      'parent'=>'plagia',
      'href' => admin_url(). 'admin.php?page=plagia',
      'meta' => array(
         'class' => 'custom-button-class ',
         
         )
     );

    $wp_admin_bar->add_node($args);


   

 $title=__('Check automatique','wp_plagia');

    $args = array(
      'id' => 'plagia_auto',
      'title' => $title,
      'parent'=>'plagia',
      'href' => admin_url(). 'admin.php?page=plagia-auto',
      'meta' => array(
         'class' => 'custom-button-class',
         
         )
     );

    $wp_admin_bar->add_node($args);
        
}

add_action('admin_bar_menu', 'plagia_buttons_menu', 100);

function plagia_activation()
{
    global $wpdb;
    $plugin_name_db_version = '0.9.3';
    $table_name = $wpdb->prefix . "plagia_users"; 
    $charset_collate = $wpdb->get_charset_collate();

   $table_name2 = $wpdb->prefix . "plagia_rapports"; 

    $sql = "CREATE TABLE $table_name (
          id mediumint(9) NOT NULL AUTO_INCREMENT,
          name tinytext NULL,
          email varchar(254) DEFAULT '' NOT NULL,
          token longtext DEFAULT '' NOT NULL,
          status int DEFAULT 1,
          domaine varchar(256) ,
          pays varchar(254),
          langue  varchar(254),
          PRIMARY KEY id (id)
        ) $charset_collate;
        ";

        $sqls=[];

        array_push($sqls, $sql);

        array_push($sqls, 

          "CREATE TABLE $table_name2 (
          id mediumint(9) NOT NULL AUTO_INCREMENT,
          ref varchar(254) NULL,
          texte longtext DEFAULT '' NOT NULL,
          reponse longtext DEFAULT '' NOT NULL,
          mode mediumint(9) DEFAULT '0',
          post_id int DEFAULT NULL,
          created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          received_at timestamp  NULL, 
          PRIMARY KEY id (id)
        ) $charset_collate;");

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

foreach($sqls as $s)
{

   dbDelta( $s );
}

  
   add_option( 'plagiarism_version', $plugin_name_db_version );



                   $langue=explode('_',get_locale())[0];

            $table_users=$wpdb->prefix.'plagia_users';
                   $inserted=$wpdb->insert($table_users, 
                   array(
                      'email'   => get_option('admin_email'),
                      'status'  => 0,
                      'domaine' => get_option( 'siteurl'),
                      
                      'langue'  => $langue
                     ) ); 
                     



}
register_activation_hook( __FILE__, 'plagia_activation' );


function plagia_uninstall(){

  global $wpdb;

$table_rapport=$wpdb->prefix.'plagia_rapports';

$wpdb->query( "DROP TABLE IF EXISTS " .$table_rapport);

$table_users=$wpdb->prefix.'plagia_users';

$wpdb->query( "DROP TABLE IF EXISTS ".$table_users );

    delete_option("my_plugin_db_version");

}

register_uninstall_hook(__FILE__, 'plagia_uninstall' );


function plagia_current_version(){
    $version = get_option( 'plagiarism_version' );
    return version_compare($version, PLAGIA_VERSION, '=') ? true : false;
}




function plagia_load_text_domain()
{
  load_plugin_textdomain( "wp_plagia",false, dirname(plugin_basename( __FILE__ )) );
}

add_action( 'plugins_loaded', 'plagia_load_text_domain' );

function plagia_plugin_update_message( $data, $response ) {
  
}
add_action( 'in_plugin_update_message-plagia/index.php', 'plagia_plugin_update_message', 10, 2 );




