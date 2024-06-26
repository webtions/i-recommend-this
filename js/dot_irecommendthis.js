jQuery(document).ready(function($) {
    $(document).on('click', '.dot-irecommendthis', function(event) {
        event.preventDefault();

        var link = $(this);
        var unrecommend = link.hasClass('active');
        var id = link.attr('id');
        var suffix = link.find('.dot-irecommendthis-suffix').text();
        
        // Generate a nonce
        var nonce = dot_irecommendthis.nonce;

        $.post(dot_irecommendthis.ajaxurl, {
            action: 'dot-irecommendthis',
            recommend_id: id,
            suffix: suffix,
            unrecommend: unrecommend,
            security: nonce // Pass the nonce as part of the request
        }, function(data) {
            var title = unrecommend ? "Recommend this" : "You already recommended this";
            link.html(data).toggleClass('active').attr('title', title);
        }).fail(function(xhr, status, error) {
            console.error("Error: " + error);
        });

        return false;
    });
});