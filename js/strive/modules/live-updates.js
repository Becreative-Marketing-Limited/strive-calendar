import { Filters } from '/filters.js';

const $ = jQuery;

const LiveUpdates = {
    sortPosts: '',
    live: false,
    paused: false,
    timer: null,
    setup: function() {
        const self = this;
        if ($('#live-updates').hasClass('live')) {
            self.live = true;
        }
        setTimeout(this.updatePosts(), 5000);
        $('#strive_live_calendar').on('change', function() {
            self.toggleLiveUpdates($(this));
        });
        document.addEventListener('visibilitychange', function () {
            if (document.hidden) {
                self.paused = true;
            } else {
                self.paused = false;
            }
        });
    },
    updatePosts: function() {
        const self = this;

        if (self.live == false) {
            return;
        }

        if (self.paused == true) {
            self.timer = setTimeout(function(){
                self.updatePosts();
            }, 1000);
            return;
        }

        var data = {
            'action': 'live_update_calendar',
            'live_update_calendar_nonce': STRIVE_AJAX.live_update_calendar_nonce
        }

        jQuery.get(ajaxurl, data, function(response) {

            self.timer = setTimeout(function(){
                self.updatePosts();
            }, 5000);

            if (response == '') {
                return;
            }
            response = JSON.parse(response);

            for (var i = 0; i < response.length; i++) {

                // Handle Quick Edit
                if ($('#post-id').val() == response[i].ID) {
                    $('#edit-conflict').addClass('show');
                }

                // look for ID and remove the post if found
                $('#post-'+ response[i].ID).remove();
                
                // If status is draft, add to the sidebar
                if (response[i].status == 'draft') {
                    $('#unscheduled-drafts .post-container').append(response[i].html);
                } else {
                    const day = $('#calendar .day[data-date="'+ response[i].date +'"]');
                    day.find('.post-container').append(response[i].html);
                    
                    const posts = day.find('.post');
                    self.sortPosts(day, posts);  
                }

                Filters.applyFilters(); 
            }
        });
    },
    toggleLiveUpdates: function(input) {
        input.attr('disabled', true);
        const self = this;
        this.live = !this.live;

        if (this.live) {
            $('#live-updates').addClass('live');
        } else {
            $('#live-updates').removeClass('live');
            clearTimeout(self.timer);
        }

        var data = {
            'action': 'live_updates',
            'live': this.live,
            'live_updates_nonce': STRIVE_AJAX.live_updates_nonce
        }
        jQuery.post(ajaxurl, data, function() {
            if (self.live) {
                self.updatePosts();
            }
            input.attr('disabled', false);
        });
    }
}

export { LiveUpdates };