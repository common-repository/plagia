

<div class="wrap">
	<H1><?php _e('Tableau de bord ','wp_plagia') ?> </H1>

<div class="row">
<div class="col-md-8"></div>
<div class="col-md-4 text-right">

  <a  href="<?php echo admin_url() ?>admin.php?page=plagia-auto" class="btn btn-info"  ><?php _e("Check automatique","wp_plagia") ?></a>

</div>

</div>


	<ul class="nav nav-tabs" id="myTab" role="tablist">
  <li class="nav-item">
    <a class="nav-link active" id="home-tab" data-toggle="tab" href="#home" role="tab" aria-controls="home"
      aria-selected="true"><?php _e('Compte','wp_plagia') ?> </a>
  </li>
  <li class="nav-item">
    <a class="nav-link" id="profile-tab" data-toggle="tab" href="#profile" role="tab" aria-controls="profile"
      aria-selected="false"><?php _e('Rapports','wp_plagia') ?> </a>
  </li>
  <li class="nav-item">
    <a class="nav-link" id="contact-tab" data-toggle="tab" href="#contact" role="tab" aria-controls="contact"
      aria-selected="false"><?php _e('Préferences','wp_plagia') ?> </a>
  </li>
</ul>
<div class="tab-content" id="myTabContent">
  <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">

    <div class="row">
          <div class="col-md-2"></div>
    	<div class="col-md-6">

    		<?php


    		  

              global $wpdb;
              $table_name = $wpdb->prefix . "plagia_users"; 

              $t_user= $wpdb->get_results( "SELECT * FROM  ".$table_name);

        if(sizeof($t_user))
          {
              	$p_user=$t_user[0];
                


          ?>
         

           <br>
            <table class="table table-striped table-responsive-md btn-table">



                   <tbody>
                      <tr>
                          <th scope="row"><?php _e('Identifiant','wp_plagia' ) ?></th>
    
                          <td id="name"></td>
    
                     </tr>
                     <tr>
                         <th scope="row"><?php _e('Email','wp_plagia' ) ?></th>
                         <td id="email"></td>
    
                     </tr>
                      <tr>
                         <th scope="row"><?php _e('Clé API','wp_plagia' ) ?></th>
                         <td id="tokenu"></td>
    
                      </tr>
                      <tr>
                         <th scope="row"><?php _e('Quota journalière','wp_plagia' ) ?></th>
                         <td id="quota"></td>
    
                      </tr>
                    </tbody>
                    <tfoot>
                      <tr>
                       
                        <td>
                          
  <button class="btn btn-secondary pull-right" type="button" name="Deactivate" id="deactivate"><?php _e('Désactiver','wp_plagia' ) ?></button>
                        </td>
                         <td></td>
                      </tr>
                    </tfoot>

            </table>

<div class="row">

</div>
    

     <?php } else { ?>
  <div class="card">
  <div class="card-header">
    <h3 class="text-center">Activation de plugin</h3>
  </div>
  <div class="card-body">
  <!-- Default form contact -->
<form class="text-center border border-light p-5" >

    <p class="h4 mb-4"><?php _e('ENTRER VOTRE CLE API','wp_plagia' ) ?></p>
     
    <!-- Name -->

    <input type="text" id="token" name="token" class="form-control mb-4" placeholder="API key">
      <div class="invalid-feedback">
       API Key is invalid !
      </div>
      <div class="valid-feedback">
       API Key is valid !
      </div>
      


    <!-- Send button -->
    <button class="btn btn-info btn-block" type="button" name="activate" id="activate"><?php _e('Activer','wp_plagia' ) ?></button>

</form>
<!-- Default form contact -->
  </div>

  <div class="card-footer text-center">

  <span><?php _e('Obtenez votre clé API ','wp_plagia' ) ?>
    <a  target="_blank" href="https://logicielantiplagiat.fr/check/register"><?php _e('ici ','wp_plagia' ) ?></a></span>
  </div>

  </div>


     <?php } ?>
    	</div>
    	<div class="col-md-3"></div>
    </div>
  </div>



  <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">
<br>

<?php   
$table_rapports=$wpdb->prefix.'plagia_rapports';
$rapports= $wpdb->get_results( "SELECT * FROM ".$table_rapports." order by created_at desc");
?>
  	<table id="rapportsTab" class="table table-striped table-bordered table-sm" cellspacing="0" width="100%">
  <thead style="background:#17a2b8;color:#ffff">
    <tr>
      <th class="th-sm text-center"><?php _e('Réference','wp_plagia') ?>

      </th>
      
      <th class="th-sm text-center"><?php _e('Texte','wp_plagia') ?>

      </th>
      <th class="th-sm text-center"><?php _e('Date de création','wp_plagia') ?>

      </th>

      <th class="th-sm text-center"><?php _e('Unicité','wp_plagia') ?>

      </th>
      <th class="th-sm text-center"><?php _e('Réponse','wp_plagia') ?>

      </th>

       
      <th class="th-sm text-center"><?php _e('Date de réponse','wp_plagia') ?>

      </th>
      
      <th class="th-sm text-center"><?php _e('Actions','wp_plagia') ?>

      </th>
    </tr>
  </thead>
  <tbody>

  
   <?php foreach($rapports as $elm){ ?>

    
    
    <tr>
      <td class="text-center"><?php echo $elm->ref ?></td>
      <td role="button" class="text-center"><span data-toggle="modal" data-target="#textModal" class="dashicons dashicons-text-page  viewText" data-id="<?php echo $elm->id ?>"></span></td>
      
      <td class="text-center"><?php echo $elm->created_at ?></td>
      <?php if($elm->reponse != '') {

        $color=plagia_getColor(json_decode($elm->reponse)->unique);
      }

      ?>

        <td class="text-center" style="font-weight:bold ;color:<?php echo $color?>" id="unicity_<?php echo $elm->id ?>"><?php 
          if($elm->reponse != '') {


            echo json_decode($elm->reponse)->unique.'%' ;

          }?></td>
      <td class="text-center">
         <?php if($elm->reponse != '') { ?>
        <span role="button" data-toggle="modal" data-target="#reponseModal" class="dashicons dashicons-visibility  viewRep" data-id="<?php echo $elm->id ?>"></span>

        <?php } ?></td>

      

      <td class="text-center"><?php echo $elm->received_at ?></td>

      <td class="text-center">

<a id="dwn_<?php echo $elm->id ?>" ><span role="button" class="dashicons dashicons-pdf exportPDF" data-id="<?php echo $elm->id ?>"></span></a>
   
      </td>
    </tr>

    <?php } ?>
  </tbody>
 
</table>


  </div>


  <div class="tab-pane fade" id="contact" role="tabpanel" aria-labelledby="contact-tab">

  	<?php 

//if plugin activated with api key 

    
     if(sizeof($t_user))
          {
                $p_user=$t_user[0];
                


         

    $response_lng=wp_remote_post( 'https://logicielantiplagiat.fr/check/api/language',
                  array('method'      => 'POST',
                       'body' => array('token'=>$p_user->token)
                     ));
          $langues=json_decode($response_lng['body']);
               
            $langue=$p_user->langue;

          $response_pays=wp_remote_post( 'https://logicielantiplagiat.fr/check/api/pays',
                  array('method'      => 'POST',
                       'body' => array('token'=>$p_user->token)
                     ));
          $pays=json_decode($response_pays['body']);

          $domaine=$p_user->domaine;
               
          }
          else{
            $sURL    = site_url(); 
            $asParts = parse_url( $sURL );
            $domaine=$asParts['host'];
            $langue=explode('_',get_locale())[0];
          }
                 ?>
  
  <div class="row">
          <div class="col-md-2"></div>
    	<div class="col-md-6">

    		<div class="card">
  <div class="card-body">
  <!-- Default form contact -->
<form class="text-center border border-light p-5" id="settingsForm">

    
    <!-- Name -->
    <label class="label-control"><?php _e('Domaine à exclure','wp_plagia') ?></label>
    <input type="text" id="domaine" name="domaine" class="form-control mb-4" placeholder="domaine à exclure" value="<?php echo $domaine ?>">

    <!-- Name -->

    <label class="label-control"><?php _e('Langue de recherche ','wp_plagia') ?></label>
    <select class="form-control mb-4" name="langue">
        <?php 

        if(sizeof($t_user)){
         $selected_lng="";
        
        foreach($langues as $lng)  { 

           if($langue==$lng->code)
             
              $selected_lng='selected';
            else
              $selected_lng='';

              echo '<option value="'.$lng->code.'"  '.$selected_lng.' >'.$lng->name.'- '.$lng->code.'</option>';
            
              
         }

         }

         else{
           echo '<option value="'.$langue.'" selected >'.locale_get_display_language($langue).'- '.$langue.'</option>';
         }  ?>
    </select>
   
   <div style="display:none">
    <!-- location -->
    <label class="label-control"><?php _e('Pays de recherche','wp_plagia') ?></label>
    <select class="form-control mb-4" name="pays">
        <?php 
        $selected_py="";
        foreach($pays as $py)  { 

          if($p_user->pays==$py->location_code)
           $selected_py='selected';
          else 
           $selected_py="";


              echo '<option value="'.$py->location_code.'" '.$selected_py.'>'.$py->location_name.'</option>';

            
         }  ?>
    </select>
     
      </div>


    <!-- Send button -->
    <button class="btn btn-info " type="button" name="save" id="save"><?php _e('Enregistrer les modifications','wp_plagia')?></button>

</form>
<!-- Default form contact -->
  </div>

  </div>
   </div>

  <div class="col-md-2"></div>


  </div>


</div>


</div>


<!-- Frame Modal Bottom -->
<div class="modal" id="textModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><?php _e('Texte à vérifier','wp_plagia') ?></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div id="texte"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php _e('terminé','wp_plagia') ?></button>
      </div>
    </div>
  </div>
</div>
  
<style>
.modal-body  {
    word-wrap: break-word;
}

</style>

  <div class="modal" id="reponseModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><?php _e('Réponse','wp_plagia')?></h5>
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
  

