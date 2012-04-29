/*
function recommendThis(postId) {
	if (postId != '') {
		
		jQuery.post(blogUrl + "/wp-content/plugins/i-recommend-this/recommend.php",{ id: postId },
			function(data){
				jQuery('#iRecommendThis-'+postId+' a').addClass('active');
				jQuery('#iRecommendThis-'+postId+' .counter').text(data);
			});
	}
}
*/

function recommendThis(postId) {
	if (postId != '') {
		jQuery('#iRecommendThis-'+postId+' .recommendThis').text('...');
		
		jQuery.post(blogUrl + "/wp-content/plugins/i-recommend-this/recommend.php",{ id: postId },
			function(data){
				jQuery('#iRecommendThis-'+postId+' .recommendThis').text(data);
				jQuery('#iRecommendThis-'+postId+' .recommendThis').addClass('active');
				
			});
	}
}

