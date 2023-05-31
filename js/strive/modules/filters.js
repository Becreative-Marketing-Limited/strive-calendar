const $ = jQuery;

const Filters = {
    context: 'calendar',
    canFilter: true,
    setup: function() {
        let self = this;
        var searchFilterTimer;

        $('#toggle-filters').on('click', function(e){
            self.toggleFilters(e, $(this));
        });

        // Use Select2 on all of the selects
        $('#filter-category, #filter-tag, #filter-post-type').select2({
            width: 'style',
            theme: 'classic'
        });

        // Apply filters when anything changes
        $('#filter-category, #filter-tag, #filter-post-type, #logic-any, #logic-all, #context-calendar, #context-sidebar, #context-both, #visibility-fade, #visibility-remove').on('change', function() {
            self.applyFilters();
        });
        // Apply filter on search with delay for typing
        $('#filter-search').on('input', function() {
            // Add a small delay so it doesn't run everything after EVERY single character
            clearTimeout(searchFilterTimer);
            searchFilterTimer = setTimeout(function() {
                self.applyFilters();
            }, 250);
        });
        $('#reset-filter').on('click', function(e){
            e.preventDefault();
            self.resetFilter();
        });
        $('#filter-form').on('submit', function(e){
            e.preventDefault();
        });
        $('#show-save-filter').on('click', function(e){
            e.preventDefault();
            self.showSaveForm();
        });
        $('#cancel-save-filter').on('click', function(e){
            e.preventDefault();
            self.hideSaveForm();
        });
        $('#save-filter').on('click', function(e){
            e.preventDefault();
            self.saveFilter();
        });
        $('#delete-filter').on('click', function(e){
            self.deleteFilter();
        });
        $('#select-filter').on('change', function(){
            if ($(this).val() == 'placeholder') {
                self.resetFilter();
                $('#filter-id').val("");
                $('#filter-name').val("");
                $('#show-save-filter').text('Save new filter');
                $('#delete-filter').removeClass('show');
            } else {
                let data = $(this).find('option:selected').data('fields');
                let id = $(this).find('option:selected').data('id');
                let name = $(this).find('option:selected').text();
                self.setFields(data, id, name);
                $('#show-save-filter').text('Update this filter');
                $('#delete-filter').addClass('show');
            }
            
        });
    },
    toggleFilters: function(e, button) {
        e.preventDefault();
        button.toggleClass('open');
        $('#filters').toggleClass('open');
    },
    applyFilters: function() {

        if (!this.canFilter)
            return;

        var categories = $('#filter-category').val();
        var tags = $('#filter-tag').val();
        var postTypes = $('#filter-post-type').val();
        var searchTerm = $('#filter-search').val().toLowerCase();
        var visibility = $('#filters .visibility input:checked').val();

        // Reset classes if context moved away from a location
        var newContext = $('#filters  .context input:checked').val();
        if (this.context != newContext) {
            if ( newContext == 'calendar' ) {
                $('#unscheduled-drafts').attr('data-filter-style', '');
                $('#unscheduled-drafts .post').removeClass('visible');
            } else if (newContext == 'sidebar') {
                $('#calendar').attr('data-filter-style', '');
                $('#calendar .post').removeClass('visible');
            }
            this.context = newContext;
        }

        // Make all posts visible if categories, tags, post types, and search are empty
        if ( 
            (!Array.isArray(categories) || !categories.length) 
            && (!Array.isArray(tags) || !tags.length) 
            && (!Array.isArray(postTypes) || !postTypes.length) 
            && searchTerm == '' 
            ) 
        {
            $('#calendar, #unscheduled-drafts').attr('data-filter-style', '');
            $('#calendar .post, #unscheduled-drafts .post').removeClass('visible');
            $('#toggle-filters').removeClass('active');
        } else {
            $('#toggle-filters').addClass('active');
            var posts;
            if ( this.context == 'calendar') {
                posts = $('#calendar .post');
                $('#calendar').attr('data-filter-style', visibility);
                $('#unscheduled-drafts').attr('data-filter-style', '');
            } else if ( this.context == 'sidebar') {
                posts = $('#unscheduled-drafts .post');
                $('#calendar').attr('data-filter-style', '');
                $('#unscheduled-drafts').attr('data-filter-style', visibility);
            } else {
                posts = $('#calendar .post, #unscheduled-drafts .post');
                $('#calendar, #unscheduled-drafts').attr('data-filter-style', visibility);
            }

            posts.each(function(){
                var postCats = $(this).attr('data-category').split(',');
                var postTags = $(this).attr('data-tag').split(',');
                var postPostType = $(this).attr('data-post-type');
                var post = $(this);
                var visible = false;
                var logicALL = false;
                if ($('#logic-all').prop('checked')) {
                    logicALL = true;
                }
                for (const category of categories) {
                    if (postCats.includes(category) ) {
                        post.addClass('visible');
                        visible = true;
                        if ( !logicALL ) {
                            break;
                        }
                    } else {
                        if ( logicALL ) {
                            visible = false;
                            break;
                        }
                    }
                }
                // If all categories required and any miss, return
                if ( logicALL && categories.length > 0 && !visible) {
                    post.removeClass('visible');
                    return true;
                } else if ( visible && !logicALL ) {
                    return true; // skip to next post
                }
                // Tags
                for (const tag of tags) {
                    if (postTags.includes(tag) ) {
                        post.addClass('visible');
                        visible = true;
                        if ( !logicALL ) {
                            break;
                        }
                    } else {
                        if ( logicALL ) {
                            visible = false;
                            break;
                        }
                    }
                };
                // If all tags required and any miss, return
                if ( logicALL && tags.length > 0 && !visible) {
                    post.removeClass('visible');
                    return true;
                } else if ( visible && !logicALL ) {
                    return true; // skip to next post
                }

                // Post types
                for (const postType of postTypes) {
                    if (postPostType == postType ) {
                        post.addClass('visible');
                        visible = true;
                        if ( !logicALL ) {
                            break;
                        }
                    } else {
                        if ( logicALL ) {
                            visible = false;
                            break;
                        }
                    }
                };
                // If all post types required and any miss, return
                if ( logicALL && postTypes.length > 0 && !visible) {
                    post.removeClass('visible');
                    return true;
                } else if ( visible && !logicALL ) {
                    return true; // skip to next post
                }

                if ( searchTerm != '') {
                    if ( $(this).find('.post-title').text().toLowerCase().search(searchTerm) !== -1 ) {
                        $(this).addClass('visible');
                        visible = true;    
                    } else {
                        if ( logicALL ) {
                            visible = false;
                        }
                    }
                }
                if ( visible ) {
                    return true; // skip to next post
                }
                // Hide if not found anymore
                post.removeClass('visible');
            });
        }
    },
    resetFilter: function(){

        let selected = $('#select-filter').find('option:selected');

        // Reset to defaults if new filter
        if (selected.val() == 'placeholder') {
            this.canFilter = false;
            $('#filter-category, #filter-tag, #filter-post-type').val(null).trigger('change');
            $('#filter-search').val("");
            $('#logic-any').prop('checked', true);
            $('#context-calendar').prop('checked', true);
            $('#visibility-fade').prop('checked', true);
            this.canFilter = true;
            this.applyFilters();
        } else {
            // Otherwise, return to the filter's saved state (handles applyFilters itself)
            this.setFields(selected.data('fields'), selected.data('id'), selected.text());
        }
    },
    showSaveForm: function() {
        $('#filters').addClass('save');
        $('#filter-name').focus();
    },
    hideSaveForm: function(){
        $('#filters').removeClass('save');
        $('#filter-name').val("");
    },
    saveFilter: function(){
        var filter_data = $('#filter-form').serializeArray();
        filter_data.push({
                name: "name", 
                value: $('#filter-name').val()
            }
        );

        // Prepare Ajax request
        var data = {
            'action': 'save_filter',
            'filter': $.param(filter_data),
            'save_filter_nonce': STRIVE_AJAX.save_filter_nonce
        }

        $('#filters').addClass('saving');

        // Make Ajax request
        jQuery.post(ajaxurl, data, function(response) {

            $('#filters').removeClass('save saving');
            
            response = JSON.parse(response);

            if (response.new) {
                $('#select-filter').append(response.data);
                $('#select-filter').find('option:last-child').prop('selected', true);
            } else {
                let option = $('#select-filter').find('option:selected');
                option.text(response.name);
                option.data('fields', response.data);
                option.attr('data-fields', JSON.stringify(response.data));
            }

            $('#delete-filter').addClass('show');
        });
    },
    setFields: function(data, id, name){
        
        // Turn off change events
        this.canFilter = false;

        // Set form ID
        $('#filter-id').val(id);
        $('#filter-name').val(name);
        
        // Set new field values
        $('#filter-category').val(data.category).trigger('change');
        $('#filter-tag').val(data.tag).trigger('change');
        $('#filter-post-type').val(data.post_type).trigger('change');
        $('#filter-search').val(data.search);
        $('#logic-'+ data.logic).prop('checked', true);
        $('#context-'+ data.context).prop('checked', true);
        $('#visibility-'+ data.visibility).prop('checked', true);

        // Re-enable change events
        this.canFilter = true;
        this.applyFilters();
    },
    deleteFilter: function(){

        if (confirm('Are you sure you want to delete this filter?')) {

            $('#filters').addClass('deleting');

            let option = $('#select-filter').find('option:selected');

            // Prepare Ajax request
            var data = {
                'action': 'delete_filter',
                'id': option.data('id'),
                'delete_filter_nonce': STRIVE_AJAX.delete_filter_nonce
            }

            // Make Ajax request
            jQuery.post(ajaxurl, data, function(response) {

                $('#filters').removeClass('deleting');
                $('#select-filter').find('option:first-child').prop('selected', true);
                $('#select-filter').trigger('change');
                option.remove();
            });
        }
    }
}

export { Filters };