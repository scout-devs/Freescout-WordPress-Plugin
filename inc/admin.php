<?php
///////////////SAVE DATA ON SUBMIT///////////////////
add_action('init', 'fsc_save_settings');

function fsc_save_settings(){
	if(!empty($_POST['submitFscSettingsForm']) && isset($_POST['submitFscSettingsForm'])){

		//check for nonce
		$retrieved_nonce = $_REQUEST['_wpnonce'];
		if (!wp_verify_nonce($retrieved_nonce, 'fsc_settings_nonce' ) ) die( 'Failed security check' );
		//
	
		//save settings
		if(@$_POST['fsc_enabled']){
			update_option('fsc_enabled',1);
		}else{
			delete_option('fsc_enabled');
		}
	
		update_option('fsc_widget_code',sanitize_text_field(htmlentities($_POST['fsc_widget_code'])));
		update_option('fsc_mailbox',sanitize_text_field($_POST['fsc_mailbox']));
		
		//API Setitngs	
		update_option('fsc_portal_url',sanitize_text_field($_POST['fsc_portal_url']));
		update_option('fsc_api_key',sanitize_text_field($_POST['fsc_api_key']));
		
	
		//option for EUP shorcode
		update_option('fsc_eup_id',sanitize_text_field($_POST['fsc_eup_id']));
		update_option('fsc_eup_width',sanitize_text_field($_POST['fsc_eup_width']));
		update_option('fsc_eup_height',sanitize_text_field($_POST['fsc_eup_height']));
		
		update_option('fsc_kb_width',sanitize_text_field($_POST['fsc_kb_width']));
		update_option('fsc_kb_height',sanitize_text_field($_POST['fsc_kb_height']));
	}
}

//display admin menu
add_action('admin_menu', 'fsc_admin_menu');
function fsc_admin_menu(){
    add_menu_page('Freescout', 'Freescout', 'manage_options', 'fsc-conversations', 'fsc_conversations',FSC_PLUGIN_URL."/img/freescout.png");
    add_submenu_page('fsc-conversations', 'Conversations', 'Conversations', 'manage_options', 'fsc-conversations' );
    add_submenu_page('fsc-conversations', 'Settings', 'Settings', 'manage_options', 'fsc-settings','fsc_settings' );
}

//display freescout conversations
function fsc_conversations(){

	$fsc_mailbox = get_option('fsc_mailbox');
	$fsc_portal_url = get_option('fsc_portal_url');
	/*if(empty($fsc_mailbox)){
		echo '<div class="fsc_container">
				<div class="fsc_container_inner">
					<div class="fsc_title">
						<div class="caption">
							<h1 class="caption-subject font-green-sharp ">Freescout Conversations</h1>
						</div>
					</div>
					<p>Please select a mailbox from the settings</p>
				</div>
			</div>			
		';
		return;
	}

	$data = array(
		"type"=>"email",
		"mailboxId"=>$fsc_mailbox
	);
	$conversations = getConversationsApi($data);
	*/
	$conversations = fsc_getConversations();

	//sorting
	?>
	<div class="fsc_container">
		<div class="fsc_container_inner">
			<div class="fsc_title">
				<div class="caption">
					<h1 class="caption-subject font-green-sharp "><?php _e('Conversations', 'cqpim'); ?> </h1>
				</div>
			</div>
			<table id="conversationsTable" class=" wp-list-table widefat fixed striped table-view-list posts">
				<thead>
					<tr>
						<th>Customer</th>
						<th>Conversation</th>
						<th>Number</th>
						<th>Waiting Since</th>
						<th style="display:none">Updated At</th>
					</tr>
				</thead>
				<tbody>
				<?php					

					if(!empty($conversations)){
						foreach($conversations as $conversation){
							
							$con = json_decode(get_post_meta($conversation->ID, 'conversation_data',true),true);  // conversation related data	
							$email = $con['customer']['email'];
							$user = get_user_by( 'email', $email );
							$link="";
							if(!empty($user)){
								$link =  admin_url( 'user-edit.php?user_id=' . $user->data->ID);
							}
							//echo '<pre>';
							//print_r($con);
							//continue;
							if(!empty($con['subject']) && ($con['state'] == "published")){
							?>
								<tr>
								   								
									<td>
										<?php if($link != ""): ?>
											<a href="<?php echo $link; ?>"><?php echo !empty($con['customer']['firstName'])?$con['customer']['firstName'].' '.$con['customer']['lastName'] : $con['customer']['email']; ?></a>
										<?php else: ?>	
											<?php 
												echo !empty($con['customer']['firstName'])?$con['customer']['firstName'].' '.$con['customer']['lastName'] : $con['customer']['email'];
											?>
										<?php endif; ?>			
									</td>
									<td>
										<a href="<?php echo $fsc_portal_url; ?>conversation/<?php echo $con['id'] ?>?folder_id=<?php echo $con['folderId'] ?>" target="_blank">
											<strong><?php echo $con['subject'] ?></strong><br/>
											<?php echo $con['preview'] ?>
										</a>
									</td>
									<td>#<?php echo $con['number'] ?></td>
									<td><?php echo $con['customerWaitingSince']['friendly'] ?></td>
									<td style="display:none" data-order="<?php echo $con['customerWaitingSince']['time']; ?>"><?php echo  $con['customerWaitingSince']['time']; ?></td>		
								</tr>
							<?php } }
					}
				?>
				</tbody>
			</table>	
<?php }

function fsc_settings(){
		
		//get all settings
		$fsc_enabled = get_option('fsc_enabled');
		$fsc_mailbox = get_option('fsc_mailbox');
		$fsc_widget_code = get_option('fsc_widget_code');
		

		$fsc_portal_url = get_option('fsc_portal_url');
		$fsc_api_key = get_option('fsc_api_key');	
		$fsc_eup_id = get_option('fsc_eup_id');	
		$fsc_eup_width = get_option('fsc_eup_width');	
		$fsc_eup_height = get_option('fsc_eup_height');	

		$fsc_kb_width = get_option('fsc_kb_width');	
		$fsc_kb_height = get_option('fsc_kb_height');	
		
		//get mailboxes list from api
		$mailboxes = fsc_getMailBoxesApi();
		//$mailboxesFields = getMailBoxeFieldsApi($fsc_mailbox);

		?>
	
		<div class="fsc_container">
			<div class="fsc_container_inner">
				<div class="fsc_title">
					<div class="caption">
						<h1 class="caption-subject font-green-sharp "><?php _e('Settings', 'cqpim'); ?> </h1>
					</div>
				</div>
				<hr/>	
				<table class="settingsTable">
					<form method="post" action="" id="settingsForm">	
					<?php wp_nonce_field('fsc_settings_nonce'); ?>
					<tr>
						<td>Enable Widget</td>
						<td><input type="checkbox" name="fsc_enabled" id="fsc_enabled" value="1" <?php if($fsc_enabled==1) { echo "checked"; } ?>></td>
					</tr>

					
					<tr>
						<td>Widget JS</td>
						<td><textarea  rows=10 cols=70 class="widefat textarea" name="fsc_widget_code" id="fsc_widget_code" ><?php echo stripslashes($fsc_widget_code); ?></textarea></td>
					</tr>

					<tr>
						<td>Mailbox</td>
						<td>
							<select name="fsc_mailbox">
							<option value="">-Select Mailbox-</option>	
							<?php
							if(!empty($mailboxes['_embedded']['mailboxes'])){
							foreach($mailboxes['_embedded']['mailboxes'] as $mb){ ?>
								<option <?php if($fsc_mailbox == $mb['id']){ echo 'selected'; } ?> value="<?php echo  $mb['id'] ?>"><?php echo  $mb['name'] ?></option>
							<?php } }?>		
							</select>
							<?php if(empty($fsc_api_key)): ?>
							<p><i>Enter API Key and Portal URL to see the mailboxes</i></p>
							<?php endif; ?>

							
							<div style="margin-top:10px">
								<button type="button" class="button button-primary" onClick="syncConversatioins(this)">Sync Last 30 days conversations</button>
								<span class="spinner"></span>	
								<p>Note: <i>Mailbox must be selected in order to sync the conversations</i></p>
							</div>	

						</td>
					</tr>
					
					<tr>
						<td>Portal Url</td>
						<td> 
							<input type="text" name="fsc_portal_url" id="fsc_portal_url" value="<?php echo ($fsc_portal_url);  ?>" placeholder="https://yourdomain.com/">
							<p><i>Enter Portal URL in order to run functionality properly</i></p>
						</td>
					</tr>
					<tr>
						<td>API Key</td>
						<td> 
							<input type="text" name="fsc_api_key" id="fsc_api_key" value="<?php echo ($fsc_api_key);  ?>" placeholder="ff0d9fcceea7f65ffb15cf5f9fe30416">
							<p><i>Enter API Key in order to run functionality properly</i></p>	
						</td>
					</tr>

					<tr>
						<td>End-User Shortcode</td>
						<td class="eup_shortcode"> 
							<div class="eup_inner">
								<p>EUP ID</p>			
								<input type="text" name="fsc_eup_id" id="fsc_eup_id" value="<?php echo ($fsc_eup_id);  ?>" placeholder="2655780751">
							</div>	
							<div class="eup_inner">	
								<p>Iframe width(% OR px)</p>	
								<input type="text" name="fsc_eup_width" id="fsc_eup_width" value="<?php echo ($fsc_eup_width);  ?>" placeholder="100%">
							</div>	
							<div class="eup_inner">	
								<p>Iframe height(% OR px)</p>		
								<input type="text" name="fsc_eup_height" id="fsc_eup_height" value="<?php echo ($fsc_eup_height);  ?>" placeholder="800px">
							</div>	
							<?php if(!empty($fsc_eup_id)): ?>
							<p>Shortcode: <code>[freescout_eup id="<?php echo ($fsc_eup_id);  ?>"]</code></p>	
							<?php endif; ?>	
						</td>
					</tr>
					<tr>
						<td>Knowledge Base Shortcode</td>
						<td class="eup_shortcode"> 
							<div class="eup_inner">
								<p>Knowledge Base ID</p>			
								<input type="text" name="fsc_eup_id" id="fsc_eup_id" value="<?php echo ($fsc_eup_id);  ?>" placeholder="2655780751" disabled>
							</div>	
							<div class="eup_inner">	
								<p>Iframe width(% OR px)</p>	
								<input type="text" name="fsc_kb_width" id="fsc_kb_width" value="<?php echo ($fsc_kb_width);  ?>" placeholder="100%">
							</div>	
							<div class="eup_inner">	
								<p>Iframe height(% OR px)</p>		
								<input type="text" name="fsc_kb_height" id="fsc_kb_height" value="<?php echo ($fsc_kb_height);  ?>" placeholder="800px">
							</div>	
							<?php if(!empty($fsc_eup_id)): ?>
							<p>Shortcode: <code>[freescout_kb id="<?php echo ($fsc_eup_id);  ?>"]</code></p>	
							<?php endif; ?>	
						</td>
					</tr>
					<tr>
						<td></td><input type="hidden"  name="submitFscSettingsForm" value="submitFscSettingsForm" />
						<td><button type="submit" class="button button-primary">Save Settings</button></td>
					</tr>
					</form>		
					
				</table>
			</div>
		</div>
		<style>
			
		</style>		
	<?php				

			
}
