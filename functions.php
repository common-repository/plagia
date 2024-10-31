<?php 

function plagia_redirect($url){
    $string = '<script type="text/javascript">';
    $string .= 'window.location = "' . $url . '"';
    $string .= '</script>';
    echo $string;
}
add_action( 'init', 'plagia_postmode' );

function plagia_postmode()
{
  global $wpdb;

  $data=file_get_contents('php://input');

if ( isset( $_GET['ref'] ))
{
  

  $obj=json_decode($data);
  $id=$obj->ref;


$table_name=$wpdb->prefix.'plagia_rapports';
  $wpdb->query( $wpdb->prepare("UPDATE $table_name 
                SET reponse = %s , received_at = %s
             WHERE id = %s",$data, date('Y-m-d H:i:s'),$id)
    );
}




}


use Dompdf\Dompdf;
function plagia_pdf(){

if(isset($_POST['rapp_id'])){
  $id_rapport=(int)$_POST['rapp_id'];
  ob_start();  
require_once (plugin_dir_path( __FILE__ ).'include/dompdf/autoload.inc.php');

$dompdf = new DOMPDF();
 global $wpdb;
$table_name=$wpdb->prefix.'plagia_rapports';
 $res= $wpdb->get_results( "SELECT * FROM ".$table_name." where id= ".$id_rapport);

//print_r($res[0]);

$html = "Rapport ".$res[0]->ref;

$html.='<h3>Texte</h3>'.$res[0]->texte;
$html.='<br><br><h3>Réponse</h3>';
$reponse=json_decode($res[0]->reponse);

                  $html.= '<H2 style="font-weight:bold;font-size:19px;color:'.plagia_getColor($reponse->unique).'">'
                           .$reponse->unique.'% unique</H2>';
                        $html.= '<table border="2px" >';
                        $html.= '<thead><tr>';
                        $html.= '<th>Results</th>';
                        $html.= '<th>Query</th>';
                        $html.= '<th>Domains</th>';
                        $html.= '</tr>';
                        $html.= '</thead>';
                        $html.= '<tbody>';

                        foreach ($reponse->results as $k => $v) {
                         

                         $html.="<tr>";
                            $html.="<td>".$v->nb."</td>";
                            $html.="<td><a>".$v->req."</a></td>";

                            $html.="<td>";
                             

                              foreach ($v->domains as $c => $d){
                              $html.="<a>".$d->domain."</a><br>";
                              }
                             
                            $html.="</td>";
                            $html.="</tr>";
                        }

                       
                        $html .='</tbody></table><br>';

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
$pdf = $dompdf->output();
file_put_contents("rapport_".$id_rapport.".pdf", $pdf);

  
  

}

}

add_action('wp_ajax_nopriv_plagia_pdf', 'plagia_pdf');
add_action('wp_ajax_plagia_pdf', 'plagia_pdf');
 
function plagia_add_rapport(){
global $wpdb;
	
 //print_r($_POST);
if(isset($_POST['mode']))
  {

  $mode=(int)$_POST['mode'];
  $ref="Ref".rand ( );
  $text=sanitize_textarea_field($_POST['text']);

if(isset($_POST['postID']))
  $postID=(int)$_POST['postID'];
else
  $postID=null;


   $table_name = $wpdb->prefix . 'plagia_rapports';  

    

 $inserted=$wpdb->insert($table_name, array('mode' => $mode, 'ref' => $ref,'texte'=>$text,'reponse'=>'','post_id'=>$postID)); 
 $lastid = $wpdb->insert_id;

  //echo $lastid;


 $res= $wpdb->get_results( "SELECT * FROM ".$table_name." where id=".$lastid);
 echo  json_encode($res[0]);
  //redirect("http://wordpress.test/wp-admin/admin.php?page=plagia");

}
wp_die();

}


  


add_action('wp_ajax_nopriv_plagia_add_rapport', 'plagia_add_rapport');
add_action('wp_ajax_plagia_add_rapport', 'plagia_add_rapport');


function plagia_activation_account()
{
	global $wpdb;  

$table_name = $wpdb->prefix . 'plagia_users';  
   

if(isset($_POST['token']))
  {
     $token=sanitize_text_field($_POST['token']);
     $response=wp_remote_post( 'https://logicielantiplagiat.fr/check/api/checkuser',
                  array('method'      => 'POST',
                       'body' => array('token'=>$token)
                     ));

     $r_pays=wp_remote_post( 'https://logicielantiplagiat.fr/check/api/pays',
                  array('method'      => 'POST',
                       'body' => array('token'=>$token)
                     ));
       $pays=json_decode($r_pays['body']);
  	    
  	    $reponse=json_decode($response['body']);

              	if($reponse->status==1 || $reponse->status==3)
              	{
              		$user=$reponse->user;
              		$email=$user->email;
              		$name=$user->name;
              		$status=$reponse->status;


            $sURL    = site_url(); 
            $asParts = parse_url( $sURL );
            $domaine=$asParts['host'];
                  
                 
                  $langue=explode('_',get_locale())[0];

                  $item = null;
                  $code=explode('_',get_locale())[1];
                  foreach($pays as $struct) {
                     if ($code == $struct->country_iso_code) {
                           $item = $struct;
                           break;
                       }
                  }

                   $pays=$item->location_code;



              $inserted=$wpdb->insert($table_name, 
                array('token'   => $token,
                      'email'   => $email,
                      'name'    => $name,
                      'status'  => $status,
                      'domaine' => $domaine,
                      'pays'    => $pays,
                      'langue'  => $langue
                     ) ); 
                      
              	echo json_encode( array('error'=>0,"message"=>"api key valide"));
                      //redirect("http://wordpress.test/wp-admin/admin.php?page=plagia");
              	}
              	
              	else{

              		echo json_encode( array('error'=>1,"message"=>"api key invalide"));

              	}

  	    



   } 
   wp_die();
}



add_action('wp_ajax_nopriv_plagia_activation_account', 'plagia_activation_account');
add_action('wp_ajax_plagia_activation_account', 'plagia_activation_account');

function plagia_deactivation_account()
{
  global $wpdb; 



$table_name = $wpdb->prefix . 'plagia_users'; 

if(isset($_POST['id_user']))
{
  $id_user=(int)$_POST['id_user']; 
   
    $wpdb->query( $wpdb->prepare("DELETE from $table_name 
                
                                  WHERE id = %s",$id_user)
    );

}

 wp_die();
}

add_action('wp_ajax_nopriv_plagia_deactivation_account','plagia_deactivation_account');
add_action('wp_ajax_plagia_deactivation_account','plagia_deactivation_account');


function plagia_get_rapport(){

	
global $wpdb; 
 
if(isset($_POST['id']))
  {

$table_name=$wpdb->prefix.'plagia_rapports';

  $id=(int)$_POST['id'];
 $res= $wpdb->get_results( "SELECT * FROM ".$table_name." where id= ".$id);
 echo  json_encode($res[0]);

}
wp_die();

}


  


add_action('wp_ajax_nopriv_plagia_get_rapport', 'plagia_get_rapport');
add_action('wp_ajax_plagia_get_rapport', 'plagia_get_rapport');


function plagia_update_rapport()
{
global $wpdb; 
 
if(isset($_POST['id']))
  {

  	     $id_rapport=(int)$_POST['id'];
  	     $reponse=sanitize_text_field(json_encode($_POST['response']));


 $table_name  = $wpdb->prefix."plagia_rapports";

    $wpdb->query( $wpdb->prepare("UPDATE $table_name 
                SET reponse = %s , received_at = %s
             WHERE id = %s",$reponse, date('Y-m-d H:i:s'),$id_rapport)
    );

$res= $wpdb->get_results( "SELECT * FROM ".$table_name." where id= ".$id_rapport);
 echo  json_encode($res[0]);


  }

	wp_die();
}



add_action('wp_ajax_nopriv_plagia_update_rapport', 'plagia_update_rapport');
add_action('wp_ajax_plagia_update_rapport', 'plagia_update_rapport');

function plagia_getpost_byID()
{

 
global $wpdb; 
 
if(isset($_POST['id']))
  {


  $id=(int)$_POST['id'];
  $myPost=get_post($id);
  $content=sanitize_text_field($myPost->post_content);
  
 echo json_encode(array('content'=>strip_shortcodes($content),'Post'=>$myPost));;

}
wp_die();

}



add_action('wp_ajax_nopriv_plagia_getpost_byID', 'plagia_getpost_byID');
add_action('wp_ajax_plagia_getpost_byID', 'plagia_getpost_byID');


function plagia_getpost_byID_parent()
{

 
global $wpdb; 
 
if(isset($_POST['id']))
  {


  $id=(int)$_POST['id'];
  $res= $wpdb->get_results( "SELECT * FROM wp_posts where post_parent= ".$id .' order by id desc limit 1');
 

  $myPost=$res[0];
  $content=sanitize_text_field($myPost->post_content);
  
 echo json_encode(array('content'=>strip_shortcodes($content),'Post'=>$myPost));;

}
wp_die();

}



add_action('wp_ajax_nopriv_plagia_getpost_byID_parent', 'plagia_getpost_byID_parent');
add_action('wp_ajax_plagia_getpost_byID_parent', 'plagia_getpost_byID_parent');

function plagia_update_settings()
{

  global $wpdb;  

$table_name = $wpdb->prefix . 'plagia_users';  
   //print_r($_POST);

if(isset($_POST['form']) && isset($_POST['id_user']))
  {
   //print_r($_POST['form']);
    $domaine='';
    $pays='';
    $langue='';
 $id_user=$_POST['id_user'];

   foreach ($_POST['form'] as $x) {

       if($x['name']=='domaine')
              $domaine=$x['value'];
      
      if($x['name']=='langue')
             $langue=$x['value'];

        if($x['name']=='pays')
            $pays=$x['value'];
    }


    $wpdb->query( $wpdb->prepare("UPDATE $table_name 
                SET domaine = %s , pays = %s , langue = %s
             WHERE id = %s",$domaine, $pays,$langue,$id_user)
    );



   }
   wp_die();


}
add_action('wp_ajax_nopriv_plagia_update_settings', 'plagia_update_settings');
add_action('wp_ajax_plagia_update_settings', 'plagia_update_settings');


function plagia_update_rapport_post($id,$data)
{
global $wpdb; 
 
if(isset($id))
  {

         $id_rapport=$id;
         $reponse=$data;


 $table_name  = $wpdb->prefix."plagia_rapports";

    $wpdb->query( $wpdb->prepare("UPDATE $table_name 
                SET reponse = %s , received_at = %s
             WHERE id = %s",$reponse, date('Y-m-d H:i:s'),$id_rapport)
    );

$res= $wpdb->get_results( "SELECT * FROM ".$table_name." where id= ".$id_rapport);
 echo  json_encode($res[0]);


  }

  wp_die();
}

function plagia_getColor($per){

  if($per< 60)
  return  'red';

  elseif($per < 70)

    return 'orange';

  else
 return 'green';

  wp_die();
}



