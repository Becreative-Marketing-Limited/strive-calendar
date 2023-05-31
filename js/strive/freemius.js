jQuery(function($) {    

    // Make all the Account page forms redirect to my tab
    $('#fs_account').find('form').each(function() {

        // Skip for license deactivation or it won't redirect to the activation page properly
        if ( $(this).find('.fs-deactivate-license').length == 0 ) {
            $(this).attr('action', $(this).attr('action').replace('strive-content-calendar-account', 'strive-content-calendar&tab=account'));
        }
    });
});