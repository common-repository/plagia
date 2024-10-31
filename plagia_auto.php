
<div class="wrap">
	<H1><?php _e('Plagiarism automatique de contenu du site','wp_plagia') ?>  </H1>


<?php 
$query = new WP_Query( array( 'post_type' => array( 'page', 'product','post' ) ) );
global $wpdb;



          ?>
        
<div class="row">
<div class="col-md-8"></div>
<div class="col-md-4 text-right">
    <?php


          

              global $wpdb;
              $table_users=$wpdb->prefix.'plagia_users';

              $p_user= $wpdb->get_results( "SELECT * FROM  ".$table_users);

        if(sizeof($p_user))
          {
                $p_user=$p_user[0];
                


          ?>
	<button data-toggle="modal" data-target="#modeModal" class="btn btn-primary"  ><?php _e("Vérifier le contenu dupliqué","wp_plagia") ?></button>

  <?php  } else { 

    echo '<div class="notice notice-warning "><p>';
   _e("Activer le plugin pour vérifier les taux d'unicité , Il suffit d'allez dans ","wp_plagia") ;
   echo '<a href="'.admin_url(). 'admin.php?page=plagia'.'">';
   _e("Plagiarism > paramètres > Compte","wp_plagia");
    echo '</a></p></div>';


       
              }

        ?>


	</div>

</div>
<br>

<div class="table-responsive">
<table id="auto-table" class="table table-striped table-bordered table-sm" cellspacing="0" width="100%">
  <thead>
    <tr>

    	<th class="th-sm text-center" width="5%">
    		<input type="checkbox"  name="selectAll" id="selectAll">
 
      </th>
      <th class="th-sm"><?php _e('Post','wp_plagia') ?>

      </th>
      
      <th class="th-sm"><?php _e('Type','wp_plagia') ?>

      </th>
      <th class="th-sm"><?php _e('Statut','wp_plagia') ?>

      </th>
      <th width="15%" class="th-sm"><?php _e('Taux d\'unicité','wp_plagia') ?>

      </th>
      <th width="10%" class="th-sm text-center"><?php _e('Réponse','wp_plagia') ?>

      </th>
      
    </tr>
  </thead>
  <tbody>
<?php
 foreach($query->posts as $post)
 {  
    $table_name=$wpdb->prefix.'plagia_rapports';

 	$rapport= $wpdb->get_results( "SELECT * FROM ".$table_name." where post_id = ".$post->ID." order by created_at desc limit 1");
if(sizeof($rapport))
{
	$response=json_decode($rapport[0]->reponse);

 	
}

 	
?>

  <tr>

  	<td width="5%" class="text-center"  
         <?php 
if(strip_shortcodes($post->post_content)  == '') {
          echo " data-toggle='tooltip' data-placement='top' data-title='Post sans contenu'";
}

          ?>

    >


      <input type="checkbox"  
             name="selection" 
             value='<?php echo $post->ID ;?>'
             <?php 
               if(strip_shortcodes($post->post_content)  == '') {

                  echo 'disabled';

                 
               }?>
             >

    </td>
 
 	<td>
 		<span style="color:blue" data-toggle="modal" data-target="#ContentModal" class="viewContent" role="button" data-id="<?php echo $post->ID ?>"><?php 
    if($post->post_title  !='')
    echo $post->post_title ;
  else 
    echo 'sans titre';

    ?></span> 

 		<?php edit_post_link( '<span style="font-size: 16px;color:#000" class="dashicons dashicons-edit-large"></span>', '', '', $post->ID, '' )
       ?>
 	</td>

 	<td><?php echo $post->post_type ?></td>
 	<td><?php echo $post->post_status ?></td>
 	<td width="15%" class="text-center" id="result_<?php echo $post->ID ?>">


 	  <?php 
    if(sizeof($rapport))
    {

    if(isset($response) && $response->success==1 && $rapport[0]->post_id==$post->ID){

      //if(meme contenu de texte)
      echo "<span style='font-weight:bold ;color:".plagia_getColor($response->unique)."'>".$response->unique."%</span>";
    }

  }
    ?>
 	</td>

  <td width="10%" class="text-center" id="response_<?php echo $post->ID ?>">
  <?php 
    if(sizeof($rapport))
    {

    if(isset($response) && $response->success==1 && $rapport[0]->post_id==$post->ID){

      //if(meme contenu de texte)
      echo ' <span role="button" data-toggle="modal" data-target="#reponseModal" class="dashicons dashicons-visibility  viewRep" data-id="'.$rapport[0]->id .'"></span>';
     
    }

  }
    ?>
  </td>
      

 	</tr>

 	<?php
 }


?>
</tbody>
</table>
</div>

</div>

<div class="modal" id="reponseModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><?php _e('Réponse','wp_plagia') ?></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-12">
        <div id="reponse"></div>
      </div>

      </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php _e('terminé','wp_plagia') ?></button>
      </div>
    </div>
  </div>
</div>

<div class="modal " id="ContentModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><?php _e('Content of post','wp_plagia') ?></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div id="p_content"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php _e('terminé','wp_plagia') ?></button>
      </div>
    </div>
  </div>
</div>


  <div class="modal" id="modeModal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><?php _e('Mode de vérification','wp_plagia') ?></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <label><?php _e('Choisir le mode de vérification','wp_plagia') ?> :</label>
            <br>
            <div class="custom-control custom-radio">
                <input type="radio" value="0" class="custom-control-input" id="live" name="mode" checked>
                <label class="custom-control-label" for="live"><?php _e('Mode instantanée','wp_plagia') ?></label>
            </div>

            <div class="custom-control custom-radio">
                <input type="radio" value="1" class="custom-control-input" id="differe" name="mode" >
                 <label class="custom-control-label" for="differe"><?php _e('Mode différée','wp_plagia') ?> </label>
           </div>
      </div>
      <div class="modal-footer">
          <button id='checkAuto' class="btn btn-primary"  ><?php _e('Vérifier','wp_plagia') ?></button>

        <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php _e('Annuler','wp_plagia') ?></button>
      </div>
    </div>
  </div>
</div>