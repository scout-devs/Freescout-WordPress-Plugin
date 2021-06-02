jQuery(document).ready(function() {
	if(jQuery('#conversationsTable').length > 0) {
		yourTable = jQuery('#conversationsTable').dataTable({
			"bDestroy": true,
			"aaSorting": [],
			"bLengthChange" : false,
			"pageLength": 50,
			"language": {
				"emptyTable": "Please enter API key and Portal URL in settings."
			},
			"order": [[ 4, "desc" ]],
			
			"fnDrawCallback": function(oSettings) {
				if (oSettings._iDisplayLength > oSettings.fnRecordsDisplay()) {
					jQuery(oSettings.nTableWrapper).find('.dataTables_paginate').hide();
				} else {
					jQuery(oSettings.nTableWrapper).find('.dataTables_paginate').show();
				}
			}
		});
	}
});



function syncConversatioins(btnObj){
	jQuery(btnObj).prop('disabled',true);
	jQuery(".spinner").addClass("is-active"); 
	jQuery.ajax({ 
		type: "POST",
		url: fsc_script_ajax_object.ajax_url,
		data: { action: 'sync_conversatioins'},
		success:function(response){
			alert(response.message);
			if(response.error == true){
				jQuery(btnObj).prop('disabled',false);
				jQuery(".spinner").removeClass("is-active"); 
				return false;
			}else{
				window.location.reload();
			}
			
			
		}
	})
}

