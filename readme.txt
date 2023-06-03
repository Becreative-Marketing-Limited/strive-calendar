=== Strive Content Calendar ===
Contributors: bensibley
Tags: content calendar, editorial calendar, content marketing
Requires at least: 5.2
Tested up to: 6.0.2
Requires PHP: 7.3.29
Stable tag: 1.32.1
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

== Description ==

Strive Content Calendar is an all-in-one toolkit for publishers striving to succeed online.

== Changelog ==

= 1.32.1 August 31st, 2022 = 

* **Update:** Changed free trial length from 30 days to 14 days

= 1.32 July 15th, 2022 = 
* **Update:** Day of the week labels stay visible when scrolling down the calendar
* **Update:** checklist tasks added with the Enter key show up below the current task instead of at the bottom
* **Fix:** display errors with custom time formats
* **Fix:** block editor scripts ran in the Customizer
* **Fix:** note in settings panels about custom post types could be output multiple times
* **Fix:** error in welcome page when Freemius requires an email activation first

= 1.31 July 6th, 2022 = 
* **Feature:** added Live Updates! Toggle live updates on/off from the calendar and it will refresh posts as they're updated in WordPress
* **Update:** added German translation
* **Fix:** compatibility issues with some languages
* **Fix:** made it easier to upgrade after license expires

= 1.30 June 10th, 2022 = 
* **Update:** improved the style of the checklists in the Classic Editor
* **Update:** button to create checklist added to the editor sidebar when no checklists are present
* **Fix:** default time for new posts stopped using the local timezone after the previous update
* **Fix:** some checklist items could be saved without an internal ID
* **Fix:** increased required PHP version to 7.3.29 or higher

= 1.29 - April 13th, 2022 =

* **Update:** checklists can now be reordered
* **Fix:** warning added if you delete a checklist and forget to save before leaving the page
* **Fix:** hitting Enter with the "Add Item" button highlighted adds a new checklist item

= 1.28 - April 1st, 2022 =

* **Update:** added the Settings menu back with tabs for the calendar, pipeline, and checklists
* **Update:** up to 12 weeks can now be shown at once in the calendar
* **Update:** the Unscheduled Drafts sidebar stays fixed on the side of the screen and scrolls independently from the calendar

= 1.27 - March 21st, 2022 =

* **Feature:** complete overhaul of the filters
  * Filter posts by category, tag, post type, and search term
  * Apply multiple filters at once
  * Filter posts in the calendar, sidebar, or both
  * Save the filters you create
  * Fade out or completely hide filtered posts
* **Fix:** Quick Edit author option wasn't working properly for sites using custom roles 

= 1.26 - March 7th, 2022 =

* **Feature:** the calendar now supports Pages and Custom Post Types. You may enable support for additional post types via the Settings menu.
* **Update:** Revision label added to posts in the Pipeline
* **Fix:** the default checklist was not saving for new posts unless explicitly set
* **Fix:** styling incorrect for tags on posts published today

= 1.25 - February 28th, 2022 =

* **Feature:** Checklists now work with Pages and Custom Post Types. Enable support via the settings in the Checklists page.
* **Update:** Authors can now view the Calendar and Pipeline and edit their posts only
* **Update:** the current user is auto-assigned as the author when adding new posts in the calendar
* **Fix:** checklists in post editor sometimes displayed incorrect tasks

= 1.24 - February 21st, 2022 =

* **Feature:** the Pipeline now works with Pages and Custom Post Types
* **Update:** the Drafts Sidebar in the calendar now automatically saves whether it's open or closed. This means if you open it and refresh the page, it will stay open and vice-versa.

= 1.23 - February 14th, 2022 =

* **Update:** the settings now open in a popup in the calendar page and save without a full page reload

= 1.22 - February 7th, 2022 =

* **Update:** calendar navigation and filter stay visible when scrolling down the calendar
* **Update:** usability enhancements to Quick Edit menu
* **Fix:** new posts added to calendar sometimes showed up one day earlier

= 1.21 - January 29th, 2022 =

This update brings a major redesign to Strive's calendar that keeps the current week at the top of the calendar instead of displaying the full current month.

* **Feature:** the first week in the calendar is now the current week
* **Feature:** new scrollable calendar navigation makes it much easier to navigate to future and previous weeks
* **Feature:** new setting lets you choose 0-2 previous weeks to display in the calendar
* **Update:** minor design improvements to the unscheduled drafts sidebar
* **Fix:** PHP error message on Settings page

= 1.20 - December 21st, 2021 =

* **Feature:** revisions now work with Pages and Custom Post Types
* **Feature:** added search bars to the category and tag filters
* **Update:** new posts can be added to today in the calendar
* **Update:** post categories are enabled by default
* **Fix:** limit of 10 visible posts per day in calendar removed
* **Fix:** checklists tasks couldn't be dragged and dropped immediately after importing a checklist

= 1.19 - December 9th, 2021 =

* **Feature:** added setup wizard for new users
* **Fix:** posts scheduled for today could appear published if WP and the server used different timezones
* **Fix:** color of tags was incorrect for published posts
* **Fix:** fade-out color for published posts that don't fit in the calendar was incorrect

= 1.18 - November 22nd, 2021 =

* **Feature:** you can now import and export checklists. This lets you quickly duplicate checklists on your site and transfer checklists to other sites.
* **Update:** moving posts to Trash from the Quick Edit menu now requires confirmation
* **Fix:** unable to schedule posts for next year in the calendar

= 1.17 - November 15th, 2021 =

* **Feature:** you can now create multiple checklists! Create separate checklists for revisions, guest posts, and any other workflows you need.
* **Update:** moved submenu items into the main Content Calendar submenu
* **Fix:** Calendar and Pipeline not showing for Editors.

= 1.16 - October 22nd, 2021 =

* **Feature:** added Post Notes! Write notes in the Quick Edit menu as you create new drafts, and edit them from the post editor too.
* **Update:** restyled the Quick Edit menu
* **Update:** added link to quickly unschedule posts that have missed their deadline
* **Fix:** new posts could be blocked from the RSS feed in certain situations
* **Fix:** child categories in Quick Edit menu weren't getting the color from their parent category

= 1.15 - October 14th, 2021 =

* **Feature:** tags added to the Quick Edit menu
* **Update:** button added to sidebar for creating new unscheduled drafts
* **Update:** usability enhancements for category & tag selection in the Quick Edit menu
* **Fix:** inserting drafts wasn't removing them from the sidebar
* **Fix:** empty calendar rows sometimes collapsing
* **Fix:** calendar week option displayed poorly on small screens
* **Fix:** removed scrollbars from sidebar for Microsoft Edge users

= 1.14 - October 11th, 2021 =

* **Feature:** added post filters to the calendar. Use the filter to show/hide posts in the calendar based on a category, tag, or search term.

= 1.13 - October 8th, 2021 =

* **Feature:** added option to display post tags in the calendar. Visit the Settings menu to enable this option.
* **Update:** improved usability of category selection in Quick Edit menu
* **Update:** added explanation to search results in sidebar
* **Fix:** some words in the Settings menu weren't translatable

= 1.12 - October 2nd, 2021 =

* **Feature:** Strive can now display post categories in the calendar! Visit the Settings tab to enable the display of categories and assign custom colors to each category

= 1.11 - September 27th, 2021 =

* **Update:** Strive is now fully translatable. Please contact us if you'd like to translate Strive to your language
* **Fix:** creating revisions redirects to the post editor again
* **Fix:** sometimes displaying an extra week from the next month
* **Fix:** date formatting for Spanish fixed
* **Fix:** dates next month occassionally marked as last month
* **Fix:** 1 day from last month sometimes labeled as the 1st

= 1.10 - September 16th, 2021 =

* **Fix:** permalink field hidden when using Classic Editor
* **Fix:** white-screen-of-death on reusable block editing page fixed
* **Fix:** date field not displaying in Quick Edit menu after adding a post from the Unscheduled Drafts sidebar into the calendar

= 1.09 - September 14th, 2021 =

* **Feature:** you can now choose a starting day of the week for the calendar (available in Settings menu)
* **Update:** Affiliation sub-menu added for users that would like to join Strive's affiliate program
* **Fix:** post dates in calendar broken for many non-English languages
* **Fix:** "Missed Deadline" notice not accounting for timezone
* **Fix:** posts in current month could appear as unpublished when viewing previous month
* **Fix:** dates formatted like "23h15" not supported

= 1.08 - September 2nd, 2021 =

* **Update:** major interface redesign! The tabs, Pipeline, Checklists, and Settings have all been updated with a fresh, modern style.
* **Update:** posts in the Pipeline now only display the date if scheduled
* **Fix:** Next/Previous month buttons skipping a month in certain situations

= 1.07 - August 31st, 2021 =

* **Feature:** New "Unscheduled Drafts" sidebar added to the calendar. Easily schedule your drafts by dragging and dropping them from the sidebar into the calendar. Likewise, you can drag and drop scheduled posts into the sidebar to unschedule them.

= 1.06 - August 23rd, 2021 =

* **Fix:** Quick Edit menu now scrollable in smaller screens
* **Fix:** Additional warning messages added for posts that missed their deadline

Note: more exciting updates are on the way in version 1.07 ;)

= 1.05 - July 5th, 2021 =

* **Feature:** added Color Blind Mode to the settings
* **Feature:** "Revisions" filter added to the Posts menu
* **Fix:** "Missed Deadline" label was only showing for posts that missed publication today

= 1.04 - June 25th, 2021 =

* **Feature:** scheduled posts are no longer published if they don't have the "Complete" post status. This stops unfinished posts from being published by mistake, but you can disable this option from the Settings tab.
* **Feature:** added checklist progress to posts in the calendar
* **Fix:** upgrade advertisement still displayed for some paying customers

= 1.03 - June 21st, 2021 =

* **Feature:** new option to set a default time for new posts
* **Feature:** new option set a default category for new posts
* **Update:** the "Create Revision" admin toolbar link is now visible when viewing posts
* **Fix:** posts could be scheduled without titles in the calendar

= 1.02 - June 14th, 2021 =

* **Feature:** Classic Editor support added for post statuses & checklists
* **Update:** post revisions now copy meta data added by plugins too
* **Fix:** "Create revisions" limited to published posts
* **Fix:** Permalink option hidden for revisions in Classic Editor

**Please read this minor but important change to revisions:**

Starting with this update, all post revisions will copy and overwrite meta data added by plugins i.e. things in the editor sidebar. For example, SEO titles set by Yoast SEO or RankMath will be copied to the revision, and edits you make to these fields in the revision will override the original post when published.

Existing post revisions you’ve created before updating to version 1.02 of Strive won’t copy or overwrite post data added by plugins.

= 1.01 - June 7th, 2021 =

* **Fix:** Revisions were affecting live posts under some hosting configurations
* **Fix:** Revisions now copy & override Featured Images

= 1.00 - June 7th, 2021 =

* Strive is available now!