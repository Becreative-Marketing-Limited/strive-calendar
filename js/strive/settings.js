jQuery(function($) {    

    $('.color-picker-button').each(function(){
        let saveInput = $(this).prev();
        let button = '#' + $(this).attr('id');
        const pickr = new Pickr({
            el: button,
            theme: 'nano',
            default: saveInput.val(),
            position: 'top-start',
            swatches: ['#CC1F1F','#E65F1C','#D99100','#839917','#429917','#179983','#227BA8','#003BB3','#5900B3','#B300B3','#FC6D6D','#FFAA00','#5DA9CF','#4DBFAC'],
            components: {
                preview: true,
                hue: true,
                interaction: {
                    input: true,
                    clear: false,
                    cancel: true,
                    save: true
                }
            }
        });
        pickr.on('save', (color) => {
            saveInput.val(color.toHEXA().toString());
            pickr.hide();
        })
    });

    $('#expand-categories').on('click', function(event) {
        event.preventDefault();
        $(this).next().toggleClass('open');
        $(this).find('.dashicons').toggleClass('dashicons-arrow-down dashicons-arrow-up');
    });

    let tabs = $('#settings-tabs .tab');
    let settingsGroups = $('.settings-group');

    tabs.on('click', function(e){
        e.preventDefault();
        tabs.removeClass('current');
        $(this).addClass('current');
        let id = $(this).data('id');
        settingsGroups.each(function(){
            if ($(this).data('id') == id){
                $(this).addClass('current');
            } else {
                $(this).removeClass('current');
            }
        });
    });

    $('#strive-calendar-settings').on('submit', function(e){
        e.preventDefault();

        $(this).addClass('saving');

        var data = {
            'action': 'save_settings',
            'form_data': $(this).serialize(),
            'save_settings_nonce': STRIVE_AJAX.save_settings_nonce
        };

        // Update the post's date in the DB
        jQuery.post(ajaxurl, data, function(response) {
            $('#strive-calendar-settings').removeClass('saving');
        });
    });

    $('#strive-checklist-settings').on('submit', function(e){
        e.preventDefault();

        $(this).addClass('saving');

        var data = {
            'action': 'save_checklist_settings',
            'form_data': $(this).serialize(),
            'save_checklist_settings_nonce': STRIVE_AJAX.save_checklist_settings_nonce
        };

        jQuery.post(ajaxurl, data, function() 
        {
            $('#strive-checklist-settings').removeClass('saving');
        });
    });

    $('#strive-pipeline-settings').on('submit', function(e){
        e.preventDefault();

        $(this).addClass('saving');

        var data = {
            'action': 'save_pipeline_settings',
            'form_data': $(this).serialize(),
            'save_pipeline_settings_nonce': STRIVE_AJAX.save_pipeline_settings_nonce
        };

        jQuery.post(ajaxurl, data, function() 
        {
            $('#strive-pipeline-settings').removeClass('saving');
        });
    });
});