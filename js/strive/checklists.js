jQuery(function($) {

    // Add a new checklist item
    $('body').on('click', '.add-item', function(e) {
        e.preventDefault();
        addNewTask($(this));
    });

    function addNewTask(button, focus = null) {

        // Get the status of the list
        var status = button.attr('data-status');
        // Get checklist index
        var index = button.attr('data-checklist');

        // Set key used for new task
        let key = 0;

        // Make key +1 the current highest key
        button.parent().find('.task').each(function() {
            var taskKey = parseInt($(this).attr('data-key'));
            if ( key <= taskKey ) {
                key = taskKey + 1;
            }
        });

        // Create base HTML for task
        var task = $('<div class="task" data-key="'+ key + '"></div>')

        // Add visible instructions
        task.append($('<input type="text" class="instructions-input" name="task-instructions" value="" />'));
        // Add hidden ID
        let uid = Math.round((Math.random() * 36 ** 12)).toString(36);
        task.append('<input type="hidden" class="id-input" name="task-id" value="'+ uid +'" />');
        // Add save input
        task.append('<input type="hidden" class="save-input" id="strive_post_checklists['+ index +'][checklist]['+ status +']['+ key +']" name="strive_post_checklists['+ index +'][checklist]['+ status +']['+ key +']" value="" />');
        // Remove button
        task.append($('<a class="remove-item"><span class="dashicons dashicons-dismiss"></span></a>'));
        // Drag handle
        task.append($('<img class="drag-handle" src="'+ STRIVE_DATA.plugin_url +'img/drag-handle.svg" />'));

        if (focus) {
            focus.parent().after(task);
        } else {
            button.prev().append(task);
        }

        // Move the cursor focus to the instructions input
        task.find('.instructions-input').focus();
    }

    // Add a new item anytime user hits the Enter key inside the form
    $('#strive-post-checklists form').keypress(function(e){
        if (e.which == 13){
            e.preventDefault();

            if ($(":focus").hasClass('add-item')) {
                addNewTask($(":focus"));
            } else if ($(":focus").hasClass('instructions-input')) {
                let button = $(":focus").parents('.task-container').siblings('.add-item');
                addNewTask(button, $(":focus"));   
            }
        }
    });

    // Set variable to track if there are unsaved changes
    var unsaved = false;

    // Warn user about leaving before saving the form
    $(window).bind('beforeunload', function() {
        if(unsaved){
            return 'You have unsaved changes';
        }
    });

    // Set unsaved changes to false when they save
    $('#save-checklists').on('click', function() {
        unsaved = false;
    })

    // When a user types anything in a step, update the task's save input and mark unsaved changes
    $('body').on('input', '.instructions-input', function() {
        updateSaveInput($(this));
        unsaved = true;
    });

    // Update the save input as the user changes the instruction text
    function updateSaveInput(input) {
        // Remove any ~ since I'm using it as my delimiter
        let newValue = input.val().replace('~', '');
        if ( newValue == '' ) {
            input.siblings('.save-input').val('');
        } else {
            // Add delimeter and UID if there is instruction text
            newValue += '~' + input.next().val();
            input.siblings('.save-input').val(newValue);
        }
    }

    // Remove a task on button click
    $('body').on('click', '.remove-item', function(e) {
        removeTask(e, $(this) );
        unsaved = true;
    });

    // Remove task by emptying save input and hiding task
    function removeTask(e, button) {
        e.preventDefault();
        button.prev('input').val('').attr('type', 'hidden');
        button.parent().hide();
    }

    // Drag-and-drop handling
    var containers = document.querySelectorAll('.task-container');
    containers = Array.prototype.slice.call(containers);
    var taskStatus = '';

    var DnD = dragula({
        containers: containers, 
        mirrorContainer: document.getElementById('strive-post-checklists'),
        moves: function (el, container, handle) {
            return handle.classList.contains('drag-handle');
          }
    } 
    ).on('drag', function(el, container) {
        // Get the editorial status on drag start
        taskStatus = container.dataset.status;
        checklistIndex = container.dataset.checklist;
    }
    ).on('drop', function(el, container) {
        unsaved = true;
        let saveInput = $(el).find('.save-input')

        // Formatting: strive_post_checklists[0][checklist][not-started][0]

        // Replace old status with new container status
        let newID = saveInput.attr('id').replace(taskStatus, container.dataset.status);

        // Replace key with new key
        let key = 0;
        $(container).find('.task').each(function() {
            if ( $(this)[0] == $(el)[0] ) return;
            var taskKey = parseInt($(this).attr('data-key'));
            if ( key <= taskKey ) {
                key = taskKey + 1;
            }
        });

        // Cuts off closing bracket and task key
        // strive_post_checklists[0][checklist][not-started][0] => strive_post_checklists[0][checklist][not-started]
        newID = newID.substring(0, newID.lastIndexOf('['));

        newID = newID + '[' + key + ']';

        // Update ID and Name attributes based on new status/section        
        saveInput.attr('id', newID).attr('name', newID);

        // Update the data attribute on the task too
        el.dataset.key = key;

    });

    $('#create-checklist').on('click', function(e) {
        e.preventDefault();
        createNewChecklist();
    });

    function createNewChecklist()
    {
        // Display loading indicator
        $('#loading').css('display', 'flex');

        let index = 0;
        $('.checklist-container').each(function() {
            let thisIndex = $(this).attr('data-index');
            if ( thisIndex >= index ) {
                index = parseInt(thisIndex) + 1;
            }
        });
        // Prepare Ajax request
        var data = {
            'action': 'get_the_checklist',
            'index': index,
            'get_the_checklist_nonce': STRIVE_DATA.get_the_checklist_nonce
        }
        
        // Make Ajax request to get the modal
        jQuery.post(ajaxurl, data, function(response) {

            // Hide loading indicator
            $('#loading').css('display', 'none');
            // Parse the JSON (create object from string)
            response = JSON.parse(response);
            // Hide current checklist
            $('.checklist-container.current').removeClass('current');
            // Remove current from tab
            $('#checklist-tabs').find('.tab.current').removeClass('current');
            // Add new tab
            $('#checklist-tabs').append(response.tab);
            // Add the new checklist HTML
            $('#checklists').append(response.checklist);
            // Reset Dragula to include new containers
            resetDragulaContainers();
            // Add new option to the default dropdown
            let option = '<option value="'+ $('.tab.current').attr('data-id') +'">'+ $('.tab.current').text() +'</option>';
            $('#strive_default_checklist').append(option); 
            // Focus cursor in title input (end of text)
            let titleInput = $(".checklist-container.current .checklist-title");
            let length = titleInput.val().length;
            titleInput[0].focus();
            titleInput[0].setSelectionRange(length, length);   
        });
    }

    // Tab navigation
    $('body').on('click', '.tab', function(e) {    
        updateTabs(e, $(this));
    });

    function updateTabs(e, tab) {
        e.preventDefault();

        // Highlight current tab
        $('#checklist-tabs').find('.tab.current').removeClass('current');
        tab.addClass('current');

        // Show correct checklist
        const checklistID = tab.attr('data-id');
        $('.checklist-container').each(function() {
            if ( $(this).attr('data-id') == checklistID ) {
                $(this).addClass('current');
            } else {
                $(this).removeClass('current');
            }
        });
    }

    // Update tab name as title is edited
    $('body').on('input', '.checklist-title', function() {
        $('.tab.current').text($(this).val());
        // And update text in default dropdown
        let id = $('.tab.current').attr('data-id');
        $('#strive_default_checklist option[value="'+ id +'"]').text($(this).val());
    });

    $('body').on('click', '.delete-button', function(e) {
        deleteChecklist(e);
    });

    function deleteChecklist(e) {
        e.preventDefault();
        if (confirm('Are you sure you want to delete this checklist?')) {
            // Remove the checklist and tab HTML
            let id = $('.checklist-container.current').attr('data-id');
            $('.checklist-container.current').remove();
            $('.tab.current').remove();
            // Add new checklist if last one was deleted
            if ( $('#checklists').children().length == 0 ) {
                createNewChecklist();
            }
            // Switch to first tab
            updateTabs(e, $('#checklist-tabs a').first());
            // Remove option from the default dropdown
            $('#strive_default_checklist option[value="'+ id +'"]').remove();
            unsaved = true;
        }
    }

    // Fill in the only default option when a user views the checklist page and doesn't have a checklist saved yet
    function addDefaultOption() {
        // Check if there are no default options
        if ( $('#strive_default_checklist option').length == 0 ) {
            let option = '<option value="'+ $('.tab.current').data('id') +'" selected>'+ $('.tab.current').text() +'</option>';
            $('#strive_default_checklist').append(option);
        }    
    }
    addDefaultOption();

    $('body').on('click', '.export-button', function(e) {
        e.preventDefault();

        let index = $('.checklist-container.current').attr('data-index');

        // Prepare Ajax request
        var data = {
            'action': 'export_checklist',
            'index': index,
            'export_checklist_nonce': STRIVE_DATA.export_checklist_nonce
        }

        // Display loading indicator
        $('#loading').css('display', 'flex');
        
        // Make Ajax request to get the modal
        jQuery.post(ajaxurl, data, function(response) {

            // Hide loading indicator
            $('#loading').css('display', 'none');

            if ( response.success == false ) {
                alert(response.data);
            } else {
                var fileName = $('.tab.current').text() + '.json';
                var contentType = 'application/json';
                var a = document.createElement("a");
                var file = new Blob([response], {type: contentType});
                a.href = URL.createObjectURL(file);
                a.download = fileName;
                a.click();
            }
        });
    });

    // Start import on button press
    $('body').on('click', '.import-button', function(e) {
        e.preventDefault();
        // Trigger file selection
        $(this).next('.import-file').click();
    });

    // Import checklist tasks
    $('body').on('change', '.import-file', function() {

        const inputField = $(this);

        if ( inputField.val() == '' ) {
            return;
        }
        
        let currentChecklist = $('.checklist-container.current');
        let index = currentChecklist.attr('data-index');
        let id = currentChecklist.attr('data-id');
        let title = currentChecklist.find('.checklist-title').val();

        let file = inputField[0].files[0];
        let reader = new FileReader();
        reader.readAsText(file);

        reader.onload = function() {
            
            // Prepare Ajax request
            var data = {
                'action': 'import_checklist',
                'index': index,
                'checklist_id': id,
                'checklist_title': title,
                'checklist_tasks': reader.result,
                'import_checklist_nonce': STRIVE_DATA.import_checklist_nonce
            }

            // Display loading indicator
            $('#loading').css('display', 'flex');
            
            // Make Ajax request to get the modal
            jQuery.post(ajaxurl, data, function(response) {

                // Hide loading indicator
                $('#loading').css('display', 'none');

                if ( response.success == false ) {
                    alert(response.data);
                } else {
                    $('.checklist-container.current').replaceWith(response);
                    resetDragulaContainers();
                }

                // Clear input so same file can be uploaded again
                inputField.val(null);
            });

        };

        reader.onerror = function() {
            console.log(reader.error);
        };
    });

    // Reset Dragula to include new containers
    function resetDragulaContainers() {
        let containers = document.querySelectorAll('.task-container');
        containers = Array.prototype.slice.call(containers);
        DnD.containers = containers;
    }

    // Open/close settings modal
    $('#settings-button, #close-settings').on('click', function(e) {
        e.preventDefault();
        $('#strive-settings-container').toggleClass('visible');
    });

    $('#save-checklists-settings').on('click', function(){
        $('#strive-checklist-settings').addClass('saving');
    });

    $('#strive-checklist-settings').on('submit', function(e){
        e.preventDefault();

        $(this).addClass('saving');

        var data = {
            'action': 'save_checklist_settings',
            'form_data': $(this).serialize(),
            'save_checklist_settings_nonce': STRIVE_DATA.save_checklist_settings_nonce
        };

        // Update the post's date in the DB
        jQuery.post(ajaxurl, data, function() 
        {
            $('#strive-settings-container').removeClass('visible');
            $('#strive-checklist-settings').removeClass('saving');
        });
    });



    var tabContainer = document.querySelectorAll('.checklist-tabs');
    tabContainer = Array.prototype.slice.call(tabContainer);
    var tabIndex;

    dragula({
        containers: tabContainer, 
        mirrorContainer: document.getElementById('strive-post-checklists'),
        direction: 'horizontal',
        axis: 'x'
    }).on('drag', function(el) {
        tabIndex = $(el).index();
        $('.tab.gu-mirror').css('top', 200 + 'px !important');
    }).on('drop', function(el) {
        var up = true;
        if (tabIndex < $(el).index())
            up = false;
        moveChecklist($(el).data('id'), $(el).index(), up);
    });

    function moveChecklist(id, index, up){
        var checklist = $('.checklist-container[data-id="'+ id +'"]');
        var target = $('#checklists .checklist-container').eq(index);
        if (up)
            target.before(checklist);
        else
            target.after(checklist);
    }
});