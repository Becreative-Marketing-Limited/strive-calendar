jQuery(function($) {

    // Get saved checklist item field
    const checklistSaveField = $('#strive_post_checklist_meta_box');

    // Get all checkboxes
    const checkboxes = $('#scc_post_checklists').find('.checklist-checkbox');
    
    // Get saved value
    var currentlyChecked = checklistSaveField.val();

    // Create array from saved value
    if ( currentlyChecked == '' ) {
        currentlyChecked = [];
    } else {
        currentlyChecked = JSON.parse(checklistSaveField.val());
    }

    // Add remove IDs from checklist field when boxes are ticked on/off
    checkboxes.on('change', function() {

        // Get task ID for current checkbox
        let ID = $(this).attr('id');

        // Add/remove ID from array
        if ( $(this).prop('checked') === true ) {
            currentlyChecked.push(ID);
        } else {
            let index = currentlyChecked.indexOf(ID);
            currentlyChecked.splice(index, 1);
        }

        // Stringify array and set as checklistField value
        checklistSaveField.val(JSON.stringify(currentlyChecked));
    });

    $('#strive_default_checklist_meta_box').on('change', function() {
        const selected = $(this).val();
        $('.checklist-section').each(function() {
            if ( $(this).attr('data-id') == selected ) {
                $(this).addClass('show');
            } else {
                $(this).removeClass('show');
            }
        });
    });
});