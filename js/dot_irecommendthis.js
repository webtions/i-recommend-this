jQuery(document).ready(function($){
	$(document).on('click', '.dot-irecommendthis',function() {
		var link = $(this);
		var unrecommend = link.hasClass('active')
		var id = $(this).attr('id'),
			suffix = link.find('.dot-irecommendthis-suffix').text();
		$.post(dot_irecommendthis.ajaxurl, { action:'dot-irecommendthis', recommend_id:id, suffix:suffix, unrecommend:unrecommend }, function(data){
			let title = unrecommend ? "Recommend this" : "You already recommended this";
			link.html(data).toggleClass('active').attr('title',title);
		});
		return false;
	});
});