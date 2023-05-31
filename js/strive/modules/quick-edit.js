import { Filters } from '/filters.js';

const $ = jQuery;

// Manage all functionality related to the Quick Edit menu

const QuickEdit = {
    id: '',
    date: '',
    status: '',
    postType: '',
    form: '',
    submit: '',
    action: '',
    sortPosts: '',
    updatePostCount: '',
    missedDeadline: false,
    open: function(postData) 
    {
        const self = this;
        this.id = postData.id;
        this.date = postData.date;
        this.status = postData.status;
        this.postType = postData.post_type;
        this.action = postData.form_action;

        // Display loading indicator
        $('#loading').css('display', 'flex');

        // Prepare Ajax request to get the post
        var data = {
            'action': 'open_post_details_modal',
            'open_post_details_modal_nonce': STRIVE_AJAX.open_post_details_modal_nonce,
            'post_data': postData
        }
        
        // Make Ajax request to get the modal
        jQuery.get(ajaxurl, data, function(response) {

            // Hide loading indicator
            $('#loading').css('display', 'none');

            // Add the modal to the page
            $('#strive-calendar').append(response);

            // Add class so Quick Edit menu appears
            $('#post-details-container').addClass('visible');

            // Trigger scroll so that hidden scrollbars on Mac show up for a second and visitors know it's scrollable
            if ( window.innerHeight < 900 ) {
                $('#post-data').scrollTop(1).scrollTop(0);
            }
            
            self.form = $('#post-data'); 
            self.submit = self.form.find('#submit');

            // Add class to style new posts differently
            if ( self.action == 'add-draft' || self.action == 'add-future' ) {
                self.form.addClass('new-post');   
            }
            // Add class for drafts in the sidebar
            if ( self.action == 'update-draft' || self.action == 'add-draft' ) {
                self.form.addClass('sidebar-draft');
            }

            // Close form 
            $('#cancel-update, #close-post-details').on('click', function() {
                self.close();
            })   

            // Never allow saving with an empty title or permalink
            self.form.find($('#post-title, #post-permalink').on('input', function() {
                self.setQuickEditButtonState(self.submit);
            }));

            // Update status attribute used to change bg color
            self.form.find('#post-status').on('change', function() {
                $(this).parent().attr('data-status', $(this).val());
            });

            // Place the cursor in the title field when adding a new post
            if( self.id === 0) {
                // Clear the value and disable the button by default
                $('#post-title').val('');
                self.submit.prop('disabled', true);
                $('#post-title').focus();
            }

            // Handle form submission with Ajax
            self.form.submit(function(e) {
                self.submitPostUpdate(e);
            });

            self.watchEvents();
            self.setupDateCal();
            self.updateSubmitButtonText();
            self.hidePostDate();
            self.select2Customization();
        });
    },
    watchEvents: function()
    {
        const self = this;

        $('#unschedule-post').on('click', function(e) {
            self.unschedulePost(e);
        });
        // Watch for delete post button
        $('#delete-post').on('click', function() {
            self.deletePost();
        });
    },
    unschedulePost: function(e)
    {
        e.preventDefault();
        this.action = 'unschedule';
        this.form.submit();
    },
    updateSubmitButtonText: function()
    {
        const self = this;
        
        let missedDeadline = false;
        if ($('#missed-deadline-explanation').length > 0) {
            missedDeadline = true;
        }
        let originalDate = moment($('#post-date').val());
        let label = this.form.data('post-type-label');

        this.form.find('#post-date').on('change', function() {  
            let newDate = moment($('#post-date').val());  
            let now = moment();
            $('#publish-warning-publish').css('display', 'none');
            $('#publish-warning-schedule').css('display', 'none');

            if ( self.status == 'draft' ) {
                if ( newDate > now ) {
                    self.submit.val('Schedule ' + label);
                } else if ( newDate <= now ) {
                    self.submit.val('Publish ' + label);
                    $('#publish-warning-publish').css('display', 'block');
                } else {
                    self.submit.val('Update ' + label);
                }
            } else if ( self.status == 'future' ) {
                if ( newDate > now && newDate != originalDate ) {
                    self.submit.val('Re-schedule ' + label);
                } else if ( newDate <= now ) {
                    self.submit.val('Publish ' + label);
                    if ( !missedDeadline ) {
                        $('#publish-warning-publish').css('display', 'block');
                    }
                } else {
                    self.submit.val('Update ' + label);
                }
            } else if ( self.status == 'publish' ) {
                if ( newDate <= now ) {
                    self.submit.val('Update ' + label);
                } else if ( newDate > now ) {
                    self.submit.val('Unpublish & Schedule ' + label);
                    $('#publish-warning-schedule').css('display', 'block');
                }
            } else if ( self.action == 'add-future' ) {
                if ( newDate <= now ) {
                    self.submit.val('Publish ' + label);
                } else {
                    self.submit.val('Schedule ' + label);
                }
            }
        });
    },
    hidePostDate: function()
    {
        const self = this;
    
        // Hide date for posts in the sidebar (including new drafts)
        if ( self.action == 'update-draft' || self.action == 'add-draft') {
            self.form.find('.date').hide();
        } else {   
            // Have to reverse display if going from sidebar to calendar
            self.form.find('.date').show();
        }
    },
    select2Customization: function() 
    {
        const self = this;

        // Add bg color inline from option's data-color attribute
        function colorCategories(data, container) {
            if (data.element) {
                $(container).css('background-color', $(data.element).attr('data-color'));
            }
            return data.text;
        }

        // Use Select2 on the category select
        $('#post-categories').select2({
            width: 'style',
            theme: 'classic',
            selectionCssClass: 'category-selection',
            dropdownCssClass: 'category-dropdown',
            templateSelection: colorCategories
        });

        // Use Select2 on the tag select
        $('#post-tags').select2({
            width: 'style',
            theme: 'classic',
            selectionCssClass: 'tag-selection',
            dropdownCssClass: 'tag-dropdown'
        });
        
        // Don't automatically open dropdown when a category or tag is removed
        $('#post-categories, #post-tags').on('select2:unselecting', function(e) {
            if ( $('#post-categories').val().length !== 1 ) {
                $(this).data('unselecting', true);
            }
        }).on('select2:opening', function(e) {
            if ($(this).data('unselecting')) {
                $(this).removeData('unselecting');
                e.preventDefault();
            }
        });
        
        // Disable update button if no categories are selected
        $('#post-categories').on('select2:unselect', function(e) {
            self.setQuickEditButtonState(self.submit);
            // Add class for warning if empty
            if ( $('#post-categories').val() == '' ) {
                $(this).next().addClass('no-category');
            }
        }).on('select2:select', function(e) {
            // Re-enable when category added
            self.setQuickEditButtonState(self.submit);
            // Remove class since there must be a category
            $(this).next().removeClass('no-category');
        });
    },
    setupDateCal: function()
    {
        // Get the user's preferred format and convert from PHP syntax to Moment.js Syntax
        var dateFormat = this.toMoment($('#post-date-format').val());

        var militaryTime = false; 
        if ( $('#post-date-format').val().includes('H') || $('#post-date-format').val().includes('G') ) {
            militaryTime = true;
        }

        var calOpens = 'down';
        if ( window.innerHeight < 800 ) {
            calOpens = 'up';
        }

        // Setup the date picker
        $('#post-date').daterangepicker({
            "singleDatePicker": true,
            "timePicker": true,
            "startDate": $('#post-date').val(),
            "opens": "center",
            "drops": calOpens,
            "applyButtonClasses": "button-primary",
            "cancelClass": "button-secondary",
            "timePicker24Hour": militaryTime,
            locale: {
                format: dateFormat
              }
        });

        // Update attr so the new date actually shows up (updating only data() doesn't show the user)
        $('#post-date').on('apply.daterangepicker', function(ev, picker) {
            $(this).attr('value', picker.startDate.format(dateFormat));
        });
    },
    deletePost: function()
    {
        const self = this;
        // Prevent accidental clicks
        if ( confirm('Are you sure you want to move this ' + this.form.data('post-type-label') + ' to Trash?') ) {

            // Display loading indicator
            $('#loading').css('display', 'flex');

            // Prepare Ajax request
            var data = {
                'action': 'delete_post',
                'post_id': self.id,
                'delete_post_nonce': STRIVE_AJAX.delete_post_nonce
            }

            // Make Ajax request
            jQuery.post(ajaxurl, data, function(response) {

                // Remove the post
                $('#post-' + self.id).remove();

                // Close the modal
                $('#post-details-container').remove();

                // Hide loading indicator
                $('#loading').css('display', 'none'); 

                // Update statistics in legend
                self.updatePostCount();
            });
        }
    },
    setQuickEditButtonState: function(button) 
    {
        let error = false;
        // Disable if there is no title
        if ( this.form.find('#post-title').val() == '' ) {
            button.prop('disabled', true);
            this.form.find('#post-title').addClass('empty');
            error = true;
        } else {
            this.form.find('#post-title').removeClass('empty');
        }
        // Disable if the permalink is empty
        if ( this.form.find('#post-permalink').val() == '' ) {
            button.prop('disabled', true);
            this.form.find('#post-permalink').addClass('empty');
            error = true;
        } else {
            this.form.find('#post-permalink').removeClass('empty');
        }

        // Disable if there are no categories (posts only)
        if ( this.postType == 'post' ) {
            if ( $('#post-categories').val().length == 0 ) {
                button.prop('disabled', true);
                error = true;
            }
        }
        
        if ( !error ) {
            // Enable if no reason to disable it
            button.prop('disabled', false);
        }
    },
    close: function()
    {
        $('#post-details-container').remove();
        this.id = '';
        this.date = '';
        this.status = '';
        this.form = '';
        this.submit = '';
        this.action = '';
        this.missedDeadline = false;
    },
    submitPostUpdate: function(e) 
    {
        e.preventDefault();
        const self = this;

        // Display loading indicator
        $('#loading').css('display', 'flex'); 

        const dateFormat = this.toMoment($('#post-date-format').val());
        let date = moment($('#post-date').val(), dateFormat);

        // Fill save field with date converted to WP's required format
        $('#post-date-save').val(date.format('YYYY-MM-DD HH:mm:ss'));

        var data = {
            'action': 'update_post_data',
            'form_data': self.form.serialize(),
            'form_action': self.action,
            'update_post_nonce': STRIVE_AJAX.update_post_nonce
        }
    
        // PHP updates the post in the DB and returns the new calendar HTML for it
        jQuery.post(ajaxurl, data, function(response) {

            response = JSON.parse(response);

            // Hide loading indicator
            $('#loading').css('display', 'none'); 

            // If new post added to the sidebar
            if ( self.action == 'add-draft' || self.action == 'update-draft' || self.action == 'unschedule' ) {

                let index = 0;

                if ( self.action == 'update-draft') {
                    index = $('#post-' + response.id).index();
                    $('#post-' + response.id).remove();
                }
                if ( self.action == 'unschedule' ) {
                    // Remove the post
                    $('#calendar #post-' + response.id).remove();
                }
                // Insert updated version in the same place
                if (index == 0) {
                    $('#unscheduled-drafts .post-container').prepend(response.markup);
                } else {
                    $('#unscheduled-drafts .post:nth-child('+ index +')').after(response.markup);
                }  
            } 
            // Post in calendar (new or updated)
            else {

                if ( self.action == 'update-future' || self.action == 'update-publish' ) {
                    // Remove the post
                    $('#post-' + response.id).remove();
                }

                // Add the post into the right day (date var is created above)
                var day = $('#strive-calendar').find('[data-date="' + date.format('YYYY-MM-DD') + '"]')
                day.find('.post-container').append(response.markup);

                // Sort the posts by publication time
                var posts = day.find('.post');
                self.sortPosts(day, posts);  

                // Re-run filter
                Filters.applyFilters(); 
            }

            // Close the modal
            self.close();

            // Update statistics in legend
            self.updatePostCount();
        });
    },
    toMoment: function(dateString) {
        // Convert PHP date format to Moment.js format
        var conversions = {
          '\\h': '[h]',
          '\\d': '[d]',
          '\\e': '[e]',
          'd': 'DD',
          'D': 'ddd',
          'j': 'D',
          'l': 'dddd',
          'N': 'E',
          'S': 'o',
          'w': 'e',
          'z': 'DDD',
          'W': 'W',
          'F': 'MMMM',
          'm': 'MM',
          'M': 'MMM',
          'n': 'M',
          't': '',
          'L': '',
          'o': 'YYYY',
          'Y': 'YYYY',
          'y': 'YY',
          'a': 'a',
          'A': 'A',
          'B': '',
          'g': 'h',
          'G': 'H',
          'h': 'hh',
          'H': 'HH',
          'i': 'mm',
          's': 'ss',
          'u': 'SSS',
          'e': 'zz',
          'I': '',
          'O': '',
          'P': '',
          'T': '',
          'Z': '',
          'c': '',
          'r': '',
          'U': 'X'
        };
        
        return dateString.replace(/((?:\\\w)|[A-Za-z])/g, function(match) {
            return conversions[match] || match;
        });
    }
};

export { QuickEdit };