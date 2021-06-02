<?php
//get stored conversations
function getConversations(){
    $args = array( 
        'numberposts'		=> -1, // -1 is for all
        'post_type'		=> 'fsc_conversations', // or 'post', 'page'
        'orderby' 		=> 'date', // or 'date', 'rand'
        'order' 		=> 'DESC', // or 'DESC'
        
    );
      
    $conversations = get_posts($args);
    return $conversations;
}

//get api conversations
function getConversationsApi($data=array()){
    $endpoint = 'api/conversations';
    $conversations = httpGet($endpoint,$data);
    return json_decode($conversations,true);
}

//get api mailboxes list
function getMailBoxesApi($data=array()){
    $endpoint = 'api/mailboxes';
    $conversations = httpGet($endpoint,$data);
    return json_decode($conversations,true);
}
//get api mailboxe custom fields
function getMailBoxeFieldsApi($mailbox_id=0){ 
    $endpoint = 'api/mailboxes/'.$mailbox_id.'/custom_fields';
    $fields = httpGet($endpoint);
    return json_decode($fields,true);
}


//ajax function for sync conversations
add_action('wp_ajax_sync_conversatioins', 'ajax_sync_conversatioins');
function ajax_sync_conversatioins(){
    global $wpdb;

    // delete all posts by post type.
        $sql = 'DELETE `posts`, `pm`
        FROM `' . $wpdb->prefix . 'posts` AS `posts` 
        LEFT JOIN `' . $wpdb->prefix . 'postmeta` AS `pm` ON `pm`.`post_id` = `posts`.`ID`
        WHERE `posts`.`post_type` = \'fsc_conversations\'';
        $result = $wpdb->query($sql);
    //

    $fsc_mailbox = get_option('fsc_mailbox');  //get selected mailbox

    $fsc_mailbox = get_option('fsc_mailbox');  //get selected mailbox
    
    if($fsc_mailbox==""){
        $response = array("error"=>true,"message"=>"mailbox not selected");
        wp_send_json($response);
        exit();
    }

    $data = array(
		"mailboxId"=>$fsc_mailbox
	);

	$conversations = getConversationsApi($data);

    if(!empty($conversations['_embedded']['conversations'])){
        foreach($conversations['_embedded']['conversations'] as $con){
            $createdAt = $con['createdAt'];
            //if (strtotime($createdAt) < strtotime('-30 days')){  //apply last 30 days check
                $post_id = wp_insert_post(array (
                    'post_type' => 'fsc_conversations',
                    'post_title' => $con['id'].'~'.$con['folderId'],
                    'post_content' => "",
                    'post_status' => 'publish',
                    'comment_status' => 'closed',   // if you prefer
                    'ping_status' => 'closed',      // if you prefer
                ));

                if ($post_id) {
                    // insert post meta
                    add_post_meta($post_id, 'conversation_data', json_encode($con));
                }
            //}   
        }
    }

    $response = array("error"=>false,"message"=>"sync completed");
    wp_send_json($response);
    exit();

}

// shortcode to display freescout EUP form in iframe
function freescout_eup($atts) { 

    $fsc_portal_url = get_option('fsc_portal_url');
    $fsc_eup_id = get_option('fsc_eup_id');	
    $fsc_eup_width = get_option('fsc_eup_width');	
    $fsc_eup_height = get_option('fsc_eup_height');	

    if($fsc_eup_width==""){
        $fsc_eup_width  = "100%";
    }

    if($fsc_eup_height==""){
        $fsc_eup_height  = "800px";
    }


    $iframe_url = $fsc_portal_url.'help/'.$fsc_eup_id;


    //return  file_get_contents($iframe_url);
    $iframe_html = '<iframe src="'.$iframe_url.'"  frameborder="0"
    style="width:'.$fsc_eup_width.'; height:'.$fsc_eup_height.';margin:0 auto !important;display:table">
    </iframe>';
    // Output needs to be return
    return $iframe_html;
} 
// register shortcode
add_shortcode('freescout_eup', 'freescout_eup'); 


// shortcode to display Knpwledge base  in iframe
function freescout_kb($atts) { 

    $fsc_portal_url = get_option('fsc_portal_url');
    $fsc_eup_id = get_option('fsc_eup_id');	

    $fsc_kb_width = get_option('fsc_kb_width');	
    $fsc_kb_height = get_option('fsc_kb_height');

    if($fsc_kb_width==""){
        $fsc_kb_width  = "100%";
    }

    if($fsc_kb_height==""){
        $fsc_kb_height  = "800px";
    }


    $iframe_url = $fsc_portal_url.'hc/'.$fsc_eup_id;


    //return  file_get_contents($iframe_url);
    $iframe_html = '<iframe src="'.$iframe_url.'"  frameborder="0"
    style="width:'.$fsc_kb_width.'; height:'.$fsc_kb_height.';margin:0 auto !important;display:table">
    </iframe>';
    // Output needs to be return
    return $iframe_html;
} 
// register shortcode
add_shortcode('freescout_kb', 'freescout_kb'); 

//display freescout widget in footer
add_action('wp_footer', 'fsc_display_widget');
function fsc_display_widget()
{
    $fsc_enabled = get_option('fsc_enabled');
    $fsc_widget_code = get_option('fsc_widget_code');

    if($fsc_enabled){
        echo $fsc_widget_code;
    }
}

/**************************************************************/
/*********************CURL FUNCTIONS***************************/
/**************************************************************/
function httpGet($endpoint,$data=array())
{
    $base_url = get_option('fsc_portal_url');

    $data['api_key'] = get_option('fsc_api_key');

    $params = '';
    foreach($data as $key=>$value){
        $params .= $key.'='.$value.'&';
    }     
    $params = trim($params, '&');

    $url = $base_url.$endpoint.'?'.$params;


    $ch = curl_init();  
 
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
//  curl_setopt($ch,CURLOPT_HEADER, false); 
 
    // Execute the cURL request for a maximum of 500 seconds
    curl_setopt($ch, CURLOPT_TIMEOUT, 500);

    // Do not check the SSL certificates
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $output=curl_exec($ch);
 
    curl_close($ch);
    return $output;
}

function httpPost($endpoint,$params)
{

   $base_url = get_option('fsc_portal_url'); 
   $api_key = get_option('fsc_api_key'); 

   $url = $base_url.$endpoint.'?api_key='.$api_key;

    $postData = '';
   //create name value pairs seperated by &
   foreach($params as $k => $v) 
   { 
      $postData .= $k . '='.$v.'&'; 
   }
   $postData = rtrim($postData, '&');
 
    $ch = curl_init();  
 
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch,CURLOPT_HEADER, false); 
    curl_setopt($ch, CURLOPT_POST, count($postData));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);    
 

    // Execute the cURL request for a maximum of 500 seconds
    curl_setopt($ch, CURLOPT_TIMEOUT, 500);

    // Do not check the SSL certificates
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);


    $output=curl_exec($ch);
 
    curl_close($ch);
    return $output;
}