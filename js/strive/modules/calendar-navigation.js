import { Filters } from 'filters.js';

const $ = jQuery;

const CalendarNavigation = {
    calendarNav: '',
    calendarDays: '',
    firstDays: [],
    DnD: '',
    setup: function(initial = true) 
    {
        // Set variables
        this.calendarNav = $('#calendar-navigation');
        this.calendarDays = this.calendarNav.find('.days');
        
        // Store first day of every month in this.firstDays
        this.getFirstDays();

        // Scroll calendar to today right away
        this.scrollToDay($('.day.selected'));

        // Add "current" class to every day of this month
        this.calendarNav.find('.day[data-month="'+ this.calendarNav.data('month') +'"]').addClass('current');

        // Enable all the events
        this.watchEvents(initial);
    },
    getFirstDays: function()
    {
        const self = this;
        let rowHeight = this.calendarNav.find('.day').outerHeight();
        this.calendarNav.find('.first').each(function() {
            self.firstDays.push({
                month: $(this).data('month'),
                year: $(this).data('year'),
                top: $(this).position().top - parseInt(rowHeight),
                bottom: $(this).position().top + rowHeight
            });
        });
    },
    watchEvents: function(initial)
    {
        const self = this;
        // Update the calendar when user scrolls
        this.calendarDays.on('scroll', function(){
            self.updateCalendarOnScroll($(this), self.firstDays);
        });   
        // When user clicks a day, reload the calendar and update the navigation
        this.calendarNav.find('.day-select').on('click', function(e) {
            self.userSelectedDate(e, $(this).parent());
        });
        // Don't run again for rebuilds
        if ( initial ) {
            // Open close the calendar navigation
            $('#date-select-link').on('click', function(e) {
                e.preventDefault();
                self.calendarNav.toggleClass('show');
                $('#date-select-arrow').toggleClass('flip');
            });
            // Reset calendar to today
            $('#reset-button').on('click', function() {
                self.resetCalendar($(this));
            });
        } 
    },
    userSelectedDate: function(e, day)
    {
        e.preventDefault();     
        
        // Reload the calendar
        this.reloadCalendar(day.data('date'), day);
        
        // Update the text and data attr's of the calendar nav
        let data = {
            month: day.data('month'),
            year: day.data('year')
        }
        this.updateCalendarNavigationData(data);
        this.calendarNav.data('date', day.data('date')).attr('data-date', day.data('date'));

        // Scroll so the selected day is centered
        this.scrollToDay(day);
    },
    resetCalendar: function(button)
    {
        this.reloadCalendar(button.data('date'), $('#calendar-navigation day.today'));
        let data = {
            month: button.data('month'),
            year: button.data('year')
        }
        this.updateCalendarNavigationData(data);
        this.calendarNav.data('date', button.data('date')).attr('data-date', button.data('date'));
        this.scrollToDay(this.calendarNav.find('.day.today'));
    },
    scrollToDay: function(day)
    {
        let theOffset = parseInt(day.position().top) - (this.calendarNav.find('.day').outerHeight() * 1.5);
        this.calendarDays.scrollTop(this.calendarDays.scrollTop() + theOffset);
    },
    updateCalendarOnScroll: function(e, firstDays) 
    {
        const self = this;
        let scroll = e.scrollTop();
        firstDays.forEach(function(day) {
            if ( day.top < scroll && scroll < day.bottom ) {
                if ( day.month != self.calendarNav.data('month') ) {
                    self.updateCalendarNavigationData(day);
                }
            }
        });
    },
    updateCalendarNavigationData: function(day) 
    {
        // Update data
        this.calendarNav.data('month', day.month).attr('data-month', day.month);
        this.calendarNav.data('year', day.year).attr('data-year', day.year);
        // Update heading month & year text
        this.calendarNav.find('.month').text( day.month );
        this.calendarNav.find('.year').text( day.year );
        // Highlight current month
        this.calendarNav.find('.day').removeClass('current');
        this.calendarNav.find('.day[data-month="'+ day.month +'"]').addClass('current');
    },
    reloadCalendar: function(date, day) 
    {
        const self = this;

        var data = {
            'action': 'reload_calendar',
            'calendar_date': date,
            'reload_calendar_nonce': STRIVE_AJAX.reload_calendar_nonce,
        }

        this.calendarNav.removeClass('show');
        this.calendarNav.find('.day.user-selected').removeClass('user-selected');
        day.addClass('user-selected');
        $('#date-select-arrow').removeClass('flip');

        // Display loading indicator
        $('#loading').css('display', 'flex');

        // Make Ajax request to open the draft select modal
        jQuery.post(ajaxurl, data, function(response) {

            // Display loading indicator
            $('#loading').css('display', 'none');   

            response = JSON.parse(response);
            
            $('#calendar').html(response.calendar);
            $('#the-date').text(response.day);

            $('#calendar').find('.day[data-date="'+ date +'"').addClass('user-selected');

            // Show reset button if not set to today
            if ( moment(date).format('YYYY-MM-DD') != moment().format('YYYY-MM-DD') ) {
                $('#reset-button').addClass('show');
            } else {
                $('#reset-button').removeClass('show');
            }

            self.resetDragulaContainers();
            Filters.applyFilters();
        });
    },
    resetDragulaContainers: function() {
        var containers = document.querySelectorAll('.drop-target .post-container');
        containers = Array.prototype.slice.call(containers);
        this.DnD.containers = containers;
    }
}

export { CalendarNavigation };