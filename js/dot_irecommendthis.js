jQuery(document).ready(function($){

	$(document).on('click', '.dot-irecommendthis',function() {
		var link = $(this);
		if(link.hasClass('active')) return false;
		
		var id = $(this).attr('id'),
			suffix = link.find('.dot-irecommendthis-suffix').text();
		
		$.post(dot_irecommendthis.ajaxurl, { action:'dot-irecommendthis', recommend_id:id, suffix:suffix }, function(data){
			link.html(data).addClass('active').attr('title','You already recommended this');
		});
		
		return false;
	});
 
});