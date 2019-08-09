/**
 * Make AJAX call to note that a dashboard notice has been dismissed
 */
jQuery( document ).ready(
	function() {
		jQuery( 'div.pmprosl-notice button.notice-dismiss' ).click(
			function() {
				jQuery.ajax(
					{
						url: ajaxurl,
						type:'POST',
						timeout: 30000,
						dataType: 'html',
						data: 'action=pmprosl_dismiss_notice',
						error: function(xml){
							alert( 'There was an error dismissing the PMPro Social Login notice.' );
						},
						success: function(responseHTML){
							// quiet success
						}
					}
				);				
			}
		);
	}
);
