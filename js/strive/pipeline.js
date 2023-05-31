jQuery(function($) {

    const pipeline = $('#strive-pipeline');
    // Pipeline
    $('#pipeline-columns').on('click', function(e) {
        e.preventDefault();
        pipeline.removeClass('rows');
        pipeline.addClass('columns');
    });

    $('#pipeline-rows').on('click', function(e) {
        e.preventDefault();
        pipeline.removeClass('columns');
        pipeline.addClass('rows');
        
    });

    // Open/close settings modal
    $('#settings-button, #close-settings').on('click', function(e) {
        e.preventDefault();
        $('#strive-settings-container').toggleClass('visible');
    });

    $('#strive-pipeline-settings').on('submit', function(e){
        e.preventDefault();

        $(this).addClass('saving');

        var data = {
            'action': 'save_pipeline_settings',
            'form_data': $(this).serialize(),
            'save_pipeline_settings_nonce': STRIVE_AJAX.save_pipeline_settings_nonce
        };

        jQuery.post(ajaxurl, data, function(response) 
        {
            $('#strive-pipeline-settings').removeClass('saving');
            $('#strive-settings-container').removeClass('visible');

            response = JSON.parse(response);

            if (response.layout == 'rows'){
                pipeline.removeClass('columns');
                pipeline.addClass('rows');
            } else {
                pipeline.removeClass('rows');
                pipeline.addClass('columns');
            }

            if (response.post_types) {
                reloadPipeline();
            }
        });
    });

    function reloadPipeline(){

        var data = {
            'action': 'reload_pipeline',
            'reload_pipeline_nonce': STRIVE_AJAX.reload_pipeline_nonce
        };

        jQuery.post(ajaxurl, data, function(response) 
        {
            $('#pipeline').html(response);
        });
    }
});