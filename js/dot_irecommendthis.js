jQuery(document).ready(function($) {
    // Add passive event listener for touchstart
    $(document).on('touchstart', '.irecommendthis', { passive: true }, function(event) {
        // Your touchstart handling code (if any) can go here
    });

    $(document).on('click', '.irecommendthis', function(event) {
        event.preventDefault();
        var link = $(this);

        // Check if the link is already processing
        if (link.hasClass('processing')) {
            return false;
        }

        var unrecommend = link.hasClass('active');
        var id = $(this).attr('id');
        var suffix = link.find('.irecommendthis-suffix').text();

        // Generate a nonce
        var nonce = dot_irecommendthis.nonce;

        // Add processing class to disable further clicks
        link.addClass('processing');

        $.post(dot_irecommendthis.ajaxurl, {
            action: 'dot-irecommendthis',
            recommend_id: id,
            suffix: suffix,
            unrecommend: unrecommend,
            security: nonce,
        }, function(data) {
            // Get the correct titles from the plugin settings
            var options = JSON.parse(dot_irecommendthis.options);
            var title_new = options.link_title_new || "Recommend this";
            var title_active = options.link_title_active || "You already recommended this";

            let title = unrecommend ? title_new : title_active;

            // Update all buttons with the same id
            $('.irecommendthis[id="' + id + '"]').each(function() {
                $(this).html(data).toggleClass('active').attr('title', title);

                // Check if the count is zero and hide if necessary
                var count = $(this).find('.irecommendthis-count').text().trim();

                if (parseInt(count) === 0 && parseInt(options.hide_zero) === 1) {
                    $(this).find('.irecommendthis-count').hide();
                } else {
                    $(this).find('.irecommendthis-count').show();
                }
            });

            // Remove processing class to allow future clicks
            $('.irecommendthis[id="' + id + '"]').removeClass('processing');
        });

        return false;
    });
});
