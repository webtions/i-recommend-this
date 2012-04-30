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

