jQuery(function($) {

    // Save calendar settings by triggering click on hidden form submit button
    $('#submit.calendar').on('click', function(e) {
        e.preventDefault();
        $('.hidden-submit').click();
    });

    // Post statuses - update data attr to change color in select dropdown
    $('.status-select').on('change', function() {
        $(this).parent().attr('data-status', $(this).val());
    });

    // Save statuses for posts via Ajax on submission
    $('#submit.statuses').on('click', function(e) {
        e.preventDefault();
    
        let posts = [];

        // Create object with post data
        $('.post-row').each(function() {
            posts.push({
                id: $(this).attr('data-id'),
                status: $(this).find('select').val()
            });
        });

        // Prepare Ajax request
        var data = {
            'action': 'save_onboarding_settings',
            'post_status_data': posts,
            'save_onboarding_settings_nonce': STRIVE_DATA.save_onboarding_settings_nonce
        }

        let url = $(this).attr('href');

        // Make Ajax request to update the posts
        jQuery.post(ajaxurl, data, function(response) {
            // Redirect to next step after posts are updated
            window.location = url;
        });
    });

    // Save the new checklist
    $('#submit.checklist').on('click', function(e) {
        e.preventDefault();

        let url = $(this).attr('href');

        // Prepare Ajax request. Only passing a yes/no value.
        var data = {
            'action': 'import_starter_checklist',
            'import_checklist': $('input[name=import]:checked', '#import-checklist').val(),
            'import_starter_checklist_nonce': STRIVE_DATA.import_starter_checklist_nonce
        }

        // Make Ajax request to save the new checklist
        jQuery.post(ajaxurl, data, function(response) {
            // Redirect to next step after the checklist is saved
            window.location = url;
        });
    });
});