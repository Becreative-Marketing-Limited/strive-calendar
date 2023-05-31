import { CalendarNavigation } from './modules/calendar-navigation.js';
import { Filters } from './modules/filters.js';
import { QuickEdit } from './modules/quick-edit.js';
import { LiveUpdates } from './modules/live-updates.js';

jQuery(function($) {

    // Make date translations available
    moment.locale(STRIVE_AJAX.locale);

    // Prepare filters
    Filters.setup();

    // Setup the calendar navigation
    CalendarNavigation.setup();

    // Enable Live Updates
    LiveUpdates.setup();

    // Open post details modal when edit button is clicked
    $('body').on('click','.quick-edit-link', function(e) {
        e.preventDefault();
        // day needs to be provided but isn't needed for the modal
        var postData = {
            'id': $(this).parent().data('id'),
            'date': $(this).parent().data('date'),
            'status': $(this).parent().data('status'),
            'post_type': $(this).parent().data('post-type'),
            'form_action': 'update-' + $(this).parent().data('status')
        }
        // openPostModal(postData);
        QuickEdit.open(postData);
    });    

    // Open post modal for a new post
    $('#add-new-post').on('click', function() {
        var postData = {
            'id': 0,
            'date': $(this).parent().parent().attr('data-date'),
            'status': null,
            'post_type': $('#insert-post-options').data('post-type'),
            'form_action': 'add-future'
        }
        // openPostModal(postData);
        QuickEdit.open(postData);
        $('#insert-post-options').removeClass('visible');
    });

    // Add new post to Unscheduled Drafts sidebar
    $('#add-new-draft').on('click', function() {

        // use today for day
        var postData = {
            'id': 0,
            'date': moment().format('YYYY-MM-DD'),
            'status': null,
            'post_type': $('#insert-post-options').data('post-type'),
            'form_action': 'add-draft'
        }
        // openPostModal(postData, 'sidebar');
        QuickEdit.open(postData);
    });

    // Open modal to add new post or insert existing draft
    $('body').on('click','.insert-new-post', function() {
        $('#insert-post-options').addClass('visible').attr('data-date', $(this).parent().data('date'));
    });

    // Change the post type they're going to create
    $('body').on('change', '#post-type-select', function(){
        $('#insert-post-options').data('post-type', $(this).val());
        $('#insert-post-options').attr('data-post-type', $(this).val());
        $('#add-new-post').text("Add New " + $(this).find('option:selected').text());
    });

    // Close modal to add new post
    $('#close-insert-post').on('click', function() {
        $('#insert-post-options').removeClass('visible');
    });

    // Open modal to insert a draft
    $('#insert-draft').on('click', function() {

        var date = $('#insert-post-options').attr('data-date');
        
        // Prepare Ajax request
        var data = {
            'action': 'post_draft_select',
            'date': date,
            'post_type': $('#insert-post-options').data('post-type'),
            'post_draft_select_nonce': STRIVE_AJAX.post_draft_select_nonce,
        }

        // Make Ajax request to open the draft select modal
        jQuery.post(ajaxurl, data, function(response) {

            // Add the post select modal to the page
            $('#strive-calendar').append(response);

            // Hide the modal for choosing a new post VS a draft
            $('#insert-post-options').removeClass('visible');

            // Use select2.js for the draft select
            $('#post-draft-select').select2();

            // Close modal to insert a draft
            $('#close-insert-draft').on('click', function() {
                $('#insert-draft-modal').remove();
            });

            // Insert the draft into the calendar
            $('#insert-draft-action').on('click', function() {

                var postData = {
                    'id': $('#post-draft-select').val(),
                    'post_date': $('#insert-draft-modal').attr('data-date')
                };

                var data = {
                    'action': 'insert_post_draft',
                    'post_data': postData,
                    'insert_post_draft_nonce': STRIVE_AJAX.insert_post_draft_nonce
                };

                // Update the post's date in the DB
                jQuery.post(ajaxurl, data, function(response) {

                    // Remove the popup
                    $('#insert-draft-modal').remove();

                    // Add the post into the right day
                    var day = $('#strive-calendar').find('[data-date="' + postData.post_date + '"]');
                    day.find('.post-container').append(response);

                    // Get all posts and sort by their publish time
                    var posts = day.find('.post');
                    sortPosts(day, posts);

                    // Remove from the Unscheduled Drafts sidebar
                    $('#unscheduled-drafts').find('#post-' + data.post_data.id).remove(); 

                    // Update the post # stat below the calendar
                    updatePostCount();

                    Filters.applyFilters()
                });
            });
        });
    });

    $('#settings-button, #close-settings').on('click', function(e) {
        e.preventDefault();
        $('#strive-settings-container').toggleClass('visible');
    });

    $('#strive-calendar-settings').on('submit', function(e){
        e.preventDefault();

        $(this).addClass('saving');

        let formData = $(this).serialize();

        var data = {
            'action': 'save_settings',
            'form_data': formData,
            'save_settings_nonce': STRIVE_AJAX.save_settings_nonce
        };

        // Update the post's date in the DB
        jQuery.post(ajaxurl, data, function(response) {

            $('#strive-settings-container').removeClass('visible');
            $('#strive-calendar-settings').removeClass('saving');

            let date = moment().format('YYYY-MM-DD');
            let day = $('#calendar').find('.day[data-date="'+ date +'"');
            
            // Reload the calendar
            CalendarNavigation.reloadCalendar(date, day);

            response = JSON.parse(response);

            // False or DoW label and navigation HTML
            if ( response.changed_dow ) {
                $('#strive-calendar .dow-labels').replaceWith(response.dow_labels);
                $('#calendar-navigation').replaceWith(response.calendar_nav);
                CalendarNavigation.setup(false);
            }
            if ( response.changed_colorblind ) {
                if ( response.colorblind ) {
                    $('#strive-content-calendar-parent').addClass('color-blind');
                } else {
                    $('#strive-content-calendar-parent').removeClass('color-blind');
                }
            }

            // If post type support changed
            if ( response.changed_post_types === true ) {

                // Refresh the Unscheduled Sidebar by searching with empty query
                var data = {
                    'action': 'search_unscheduled_posts',
                    'search_query': '',
                    'search_unscheduled_posts_nonce': STRIVE_AJAX.search_unscheduled_posts_nonce
                }

                // Lighten the posts to show that it's working
                $('#unscheduled-drafts .post').css('opacity', 0.5);
                
                // Get the HTML back from the DB query
                jQuery.post(ajaxurl, data, function(response) {
                    // Remove and replace the posts
                    $('#unscheduled-drafts .post').remove();
                    $('#unscheduled-drafts .posts').append(response);
                    $('#search-icon').css('display', 'inline');
                    $('#search-info').css('display', 'none');
                });

                // Update the available options in the #insert-post-options select
            }
        });
    });

    // Toggle Unscheduled Drafts sidebar
    $('#unscheduled-drafts-toggle').on('click', function() {
        $('#strive-content-calendar-parent').addClass('unscheduled-sidebar-open');

        var data = {
            'action': 'save_sidebar_display',
            'sidebar_open': 'open',
            'save_sidebar_display_nonce': STRIVE_AJAX.save_sidebar_display_nonce
        }
        jQuery.post(ajaxurl, data);
    });

    $('#close-drafts').on('click', function() {
        $('#strive-content-calendar-parent').removeClass('unscheduled-sidebar-open');

        var data = {
            'action': 'save_sidebar_display',
            'sidebar_open': 'closed',
            'save_sidebar_display_nonce': STRIVE_AJAX.save_sidebar_display_nonce
        }
        jQuery.post(ajaxurl, data);
    });

    var timer;
    // Ajax - search unscheduled posts
    $('#search-drafts').on('input', function() {
        clearTimeout(timer);

        var searchQuery = $(this).val();
        var data = {
            'action': 'search_unscheduled_posts',
            'search_query': searchQuery,
            'search_unscheduled_posts_nonce': STRIVE_AJAX.search_unscheduled_posts_nonce
        }

        // Add a 0.25s delay to prevent searching for every single letter change
        timer = setTimeout(function() {

            // Lighten the posts to show that it's working
            $('#unscheduled-drafts .post').css('opacity', 0.5);
        
            // Get the HTML back from the DB query
            jQuery.post(ajaxurl, data, function(response) {
                // Remove and replace the posts
                $('#unscheduled-drafts .post').remove();
                $('#unscheduled-drafts .posts').append(response);

                // Icon swap
                if ( searchQuery == '' ) {
                    $('#search-icon').css('display', 'inline');
                    $('#search-info').css('display', 'none');
                } else {
                    $('#search-icon').css('display', 'none');
                    $('#search-info').css('display', 'inline');
                }
            });
        }, 250);
    });

    // DnD
    var containers = document.querySelectorAll('.drop-target .post-container');
    containers = Array.prototype.slice.call(containers);

    // Add DnD to days in the calendar
    var DnD = dragula({
        containers: containers, 
        mirrorContainer: document.getElementById('strive-calendar')
    }).on('drop', function(el, container) {
        
        let status = 'future';
        let date = container.parentNode.dataset.date + ' ' + el.dataset.time;

        // Handle sidebar differently
        if ( $(container.parentNode).hasClass('unscheduled-drafts') ) {
            status = 'draft';
            date = moment().format('YYYY-MM-DD') + ' ' + el.dataset.time;
            // Update status
            $(el).data('status', 'draft');
            // Remove filtering
            $(el).removeClass('filtered');
        } else {
            // Change status
            $(el).data('status', 'future');
            // Reorder posts instantly based on time
            sortPosts($(container.parentNode), $(container).children('.post'));
        }
        
        // Add class temporarily to flash color
        $(container).parent().addClass('day-added');

        // Update statistics in legend
        updatePostCount();

        // Re-run filter
        Filters.applyFilters();

        // Ajax call to update the post's date in the database
        var postData = {
            'id': el.dataset.id,
            'post_date': date,
            'post_status': status
        };
        var data = {
            'action': 'drag_drop_update',
            'post_data': JSON.stringify(postData),
            'drag_drop_update_nonce': STRIVE_AJAX.drag_drop_update_nonce
        };
        jQuery.post(ajaxurl, data, function(response) {

            // Remove bg color change after update finished
            $(container).parent().removeClass('day-added');

        });
    });

    // Pass Dragula object
    CalendarNavigation.DnD = DnD;

    // Sort posts by their publication time
    var sortPosts = function(day, posts) {

        var sortList = Array.prototype.sort.bind(posts);

        sortList(function ( a, b ) {

            // Cache inner content from the first element (a) and the next sibling (b)
            var aTime = a.dataset.time;
            var bTime = b.dataset.time;
        
            // Returning -1 will place element `a` before element `b`
            if ( aTime < bTime ) {
                return -1;
            }

            // Returning 1 will do the opposite
            if ( aTime > bTime ) {
                return 1;
            }

            // Returning 0 leaves them as-is
            return 0;
        });

        // Append the provided posts after sorting
        day.find('.post-container').append(posts);
    }

    // Pass sortPosts function
    QuickEdit.sortPosts = sortPosts;
    LiveUpdates.sortPosts = sortPosts;

    var updatePostCount = function() {
        var count = 0;
        $('#calendar .post').each(function() {
            if ( $(this).parent().parent().hasClass('drop-target') ) {
                count++;
            }
        });
        $('#post-count').text(count);
    }

    // Pass updatePostCount function
    QuickEdit.updatePostCount = updatePostCount;

    // Color picker functionality for categories
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
        });
    });

    $('#expand-categories').on('click', function(event) {
        event.preventDefault();
        $(this).next().toggleClass('open');
        $(this).find('.dashicons').toggleClass('dashicons-arrow-down dashicons-arrow-up');
    });
});
