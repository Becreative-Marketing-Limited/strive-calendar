<?php
if (!class_exists('SCC_Calendar')) {
    class SCC_Calendar
    {
        public function __construct()
        {
            add_action('wp_ajax_open_post_details_modal', [$this, 'open_post_details_modal']);
            add_action('wp_ajax_update_post_data', [$this, 'update_post_data']);
            add_action('wp_ajax_drag_drop_update', [$this, 'drag_drop_update']);
            add_action('wp_ajax_delete_post', [$this, 'delete_post']);
            add_action('wp_ajax_post_draft_select', [$this, 'post_draft_select']);
            add_action('wp_ajax_insert_post_draft', [$this, 'insert_post_draft']);
            add_action('wp_ajax_search_unscheduled_posts', [$this, 'search_unscheduled_posts']);
            add_action('wp_ajax_reload_calendar', [$this, 'reload_calendar']);
            add_action('wp_ajax_save_sidebar_display', [$this, 'save_sidebar_display']);
            add_action('publish_future_post', [$this, 'prevent_incomplete_post_publishing'], 1, 1);
        }

        public function build_calendar()
        {
            // Add class for keeping unscheduled drafts sidebar open
            $classes = 'strive-calendar';
            $scheduled_posts = get_posts([
                'numberposts' => -1,
                'post_status' => 'future',
            ]);
            $scheduled_posts = count($scheduled_posts);
            $today = new DateTimeImmutable('now', wp_timezone()); ?>
            <div id="strive-calendar" class="<?php echo esc_attr($classes); ?>">
                <div class="heading">
                    <div class="heading-title">
                        <span class="title"><?php esc_html_e('Content Calendar', 'strive'); ?></span>
                        <span class="sub-title">
                            <?php printf(_n('You have <span id="post-count" class="post-count">%s</span> post scheduled', 'You have <span id="post-count" class="post-count">%s</span> posts scheduled', $scheduled_posts, 'strive'), number_format_i18n($scheduled_posts)); ?>
                        </span>
                    </div>
                    <?php
                    SCC()->live_updates->live_update_toggle();
            if (SCC()->permission_manager('admin')) : ?>
                        <div class="settings">
                            <a id="settings-button" class="settings-button" href="#"><img class="settings-icon" src="<?php echo STRIVE_CC_URL . 'img/settings.svg' ?>" /> <?php esc_html_e('Settings', 'strive'); ?></a>
                        </div>
                    <?php endif; ?>
                    <button id="unscheduled-drafts-toggle" class="unscheduled-drafts-toggle"><span class="dashicons dashicons-arrow-left-alt"></span> <?php esc_html_e('Open Drafts Sidebar', 'strive'); ?></button>
                </div>
                <div class="calendar-container">
                    <div class="calendar-controls">
                        <?php echo self::calendar_date_select($today); ?>
                        <button id="reset-button" class="reset-button" data-date="<?php echo $today->format('Y-m-d'); ?>" data-month="<?php echo $today->format('F'); ?>" data-year="<?php echo $today->format('Y'); ?>"><span class="dashicons dashicons-image-rotate"></span> <?php esc_html_e('Today', 'strive'); ?></button>
                        <?php SCC()->filters->build_filters(); ?>
                        <?php echo self::calendar_navigation(); ?>
                    </div>
                    <?php echo self::dow_labels(); ?>
                    <div id="calendar" class="calendar" data-filter-style="">
                        <?php echo self::get_the_calendar($today); ?>
                    </div>
                </div>  
                <div class="calendar-footer">
                    <div class="legend">
                        <span class="label"><?php esc_html_e('Status:', 'strive'); ?></span>
                        <?php foreach (SCC()->post_statuses() as $key => $value) { ?>
                            <span class="<?php echo esc_attr($key); ?>"><span>&#9679;</span> <?php echo esc_html($value); ?></span>
                        <?php } ?>
                    </div>
                </div>
                <div id="insert-post-options" class="insert-post-options" data-date="" data-post-type="post">
                    <div class="inner"><?php
                        $supported_types = SCC()->get_supported_post_types('strive_calendar_post_types');
            $first_label = get_post_type_object($supported_types[0])->labels->singular_name; ?>
                        <div class="title"><?php printf(esc_html__('Schedule a %s', 'strive'), $first_label); ?></div><?php
                        if (count($supported_types) > 1) {
                            echo '<label>' . esc_html__('Choose a post type', 'strive') . '</label>';
                            echo '<select id="post-type-select" class="post-type-select">';
                            foreach ($supported_types as $post_type) {
                                $label = get_post_type_object($post_type)->labels->singular_name;
                                echo '<option value="' . esc_attr($post_type) . '">' . esc_html($label) . '</option>';
                            }
                            echo '</select>';
                        } ?>
                        <button id="add-new-post" class="add-new-post button button-primary">
                            <?php printf(esc_html__('Add New %s', 'strive'), $first_label); ?>
                        </button>
                        <button id="insert-draft" class="insert-draft button button-primary">
                            <?php esc_html_e('Insert Draft', 'strive'); ?>
                        </button>
                        <button id="close-insert-post" class="close-button"><span class="dashicons dashicons-no"></span></button>
                    </div>
                </div>
                <div id="loading" class="loading">
                    <img src="<?php echo trailingslashit(STRIVE_CC_URL) . 'img/loading.svg'; ?>" />
                </div>
            </div>
            <div id="unscheduled-drafts" class="unscheduled-drafts drop-target" data-filter-style="">
                <div class="drafts-heading">
                    <span class="title"><?php esc_html_e('Unscheduled Drafts', 'strive'); ?></span>
                    <button id="close-drafts" class="close-drafts"><span class="dashicons dashicons-no-alt"></span></button>
                </div>
                <div class="search-container">
                    <input type="search" id="search-drafts" class="search" placeholder="<?php esc_html_e('Search drafts', 'strive'); ?>" value="" />
                    <span id="search-icon" class="dashicons dashicons-search"></span>
                    <span id="search-info" class="dashicons dashicons-info-outline"></span>
                    <div id="search-behavior" class="search-behavior tooltip"><?php esc_html_e('Posts with the search term in the title are ranked first followed by matches in the post content.', 'strive'); ?></div>
                </div>
                <div class="add-new-drafts">
                    <button id="add-new-draft" class="add-new-draft button-primary button"><?php esc_html_e('Add New Draft', 'strive'); ?></button>
                </div>
                <div class="posts post-container">
                    <?php
                    $drafts = self::get_unscheduled_drafts();
            foreach ($drafts as $draft) {
                echo self::post_markup($draft->ID);
            } ?>
                </div>
            </div>
        <?php
        }

        public function dow_labels()
        {
            $html = '<div class="dow-labels">';
            $dow_array = [
                esc_html_x('Sun', 'Abbreviation for Sunday', 'strive'),
                esc_html_x('Mon', 'Abbreviation for Monday', 'strive'),
                esc_html_x('Tue', 'Abbreviation for Tuesday', 'strive'),
                esc_html_x('Wed', 'Abbreviation for Wednesday', 'strive'),
                esc_html_x('Thu', 'Abbreviation for Thursday', 'strive'),
                esc_html_x('Fri', 'Abbreviation for Fri', 'strive'),
                esc_html_x('Sat', 'Abbreviation for Sat', 'strive'),
            ];
            $starting_day = SCC()->strive_get_option('strive_starting_dow', 0);

            // Sort array based on user's preferred starting day
            $counter = 0;
            while ($starting_day > $counter) {
                // Remove first day and then add to end
                $dow = array_shift($dow_array);
                $dow_array[] = $dow;
                $counter++;
            }
            foreach ($dow_array as $dow) :
                $html .= '<div class="dow">' . esc_html($dow) . '</div>';
            endforeach;

            $html .= '</div>';

            return $html;
        }

        public function preceding_days($input_date)
        {
            $preceding_days = SCC()->strive_get_option('strive_starting_dow', 0) - $input_date->format('w');
            if ($preceding_days < 0) {
                $preceding_days = absint($preceding_days);
            } elseif ($preceding_days > 0) {
                $preceding_days = 7 - $preceding_days;
            }

            return $preceding_days;
        }

        public function get_starting_day($input_date)
        {
            $preceding_days = self::preceding_days($input_date);

            // Add more days based on how many previous weeks user wants
            $prev_weeks = SCC()->strive_get_option('calendar_prev_weeks', 0);
            $preceding_days += $prev_weeks * 7;

            $starting_date = DateTime::createFromImmutable($input_date);
            $starting_date->modify('-' . $preceding_days . ' days');

            return $starting_date;
        }

        public function get_the_calendar($input_date)
        {
            $today = new DateTime('now', wp_timezone());
            $cal_day = self::get_starting_day($input_date);
            $total_days = 4 + SCC()->strive_get_option('calendar_weeks', 1);
            $total_days = $total_days * 7;
            $calendar = '';

            $x = 0;
            while ($x < $total_days) {
                $day_classes = 'day';
                if ($cal_day->format('Y-m-d') < $today->format('Y-m-d')) {
                    $day_classes .= ' previous';
                } elseif ($cal_day->format('Y-m-d') == $today->format('Y-m-d')) {
                    $day_classes .= ' today';
                } else {
                    $day_classes .= ' drop-target';
                }

                if ($cal_day->format('Y-m') < $input_date->format('Y-m')) {
                    $day_classes .= ' last-month';
                }
                if ($cal_day->format('Y-m') > $input_date->format('Y-m')) {
                    $day_classes .= ' next-month';
                }

                $query = self::get_todays_posts($cal_day);
                $calendar .= self::day_markup($day_classes, $query, $cal_day, $x);

                $cal_day->modify('+1 day');
                $x++;
            }

            return  $calendar;
        }

        public function calendar_date_select($input_date)
        {
            $html = '<div class="date-select">';
            $html .= '<div class="inner">';
            $html .= '<a id="date-select-link" class="date-select-link" href="#"><span class="dashicons dashicons-calendar-alt"></span> ';
            $html .= '<span id="the-date" class="the-date">' . $input_date->format(get_option('date_format')) . '</span> ';
            $html .= '<span id="date-select-arrow" class="dashicons dashicons-arrow-down-alt2"></span></a>';
            $html .= '</div>';
            $html .= '</div>';

            return $html;
        }

        public function calendar_navigation()
        {
            $today = new DateTime('now', wp_timezone());
            $starting_day = clone $today;
            $starting_day = $starting_day->modify('-' . self::preceding_days($starting_day) . ' days');

            $nav = '<div id="calendar-navigation" class="calendar-navigation" data-date="' . $today->format('Y-m-d') . '" data-month="' . $today->format('F') . '" data-year="' . $today->format('Y') . '">';
            $nav .= '<div class="nav-heading">';
            $nav .= '<span class="date"><span class="month">' . $today->format('F') . '</span> <span class="year">' . $today->format('Y') . '</span></span>';
            $nav .= '</div>';
            $nav .= self::dow_labels();
            $nav .= '<div id="calendar-navigation-days" class="days">';
            $nav .= self::calendar_navigation_days($starting_day);
            $nav .= '</div>';
            $nav .= '</div>';

            return $nav;
        }

        public function calendar_navigation_days($date)
        {
            $today = new DateTime('now', wp_timezone());
            $origin_date = DateTimeImmutable::createFromMutable($date);

            $begin = $origin_date->modify('-6 months');
            $begin = $begin->modify('-' . self::preceding_days($begin) . ' days');

            $end = $origin_date->modify('+6 months');
            $end = $end->modify('+1 day');

            // Finish with an even week
            $diff = $begin->diff($end);
            $extra_days = $diff->format('%a') % 7;
            if ($extra_days != 0) {
                $extra_days = 7 - $extra_days;
            }
            $end = $end->modify('+' . $extra_days . ' days');

            $interval = new DateInterval('P1D');
            $daterange = new DatePeriod($begin, $interval, $end);
            $nav = '';
            foreach ($daterange as $date) {
                $day_classes = 'day';
                if ($date->format('Y-m-d') == $today->format('Y-m-d')) {
                    $day_classes .= ' today';
                }
                if ($date->format('Y-m-d') == $origin_date->format('Y-m-d')) {
                    $day_classes .= ' selected';
                }
                if ($date->format('j') == 1) {
                    $day_classes .= ' first';
                }
                $nav .= '<div class="' . esc_attr($day_classes) . '" data-date="' . $date->format('Y-m-d') . '" data-month="' . $date->format('F') . '" data-year="' . $date->format('Y') . '">';
                if ($date->format('j') == 1) {
                    $nav .= self::first_three_letters_of_month($date->format('m')) . ' ';
                }
                $nav .= $date->format('j');
                $nav .= '<a href="#" class="day-select"></a>';
                $nav .= '</div>';

                $date->modify('+1 day');
            }

            return $nav;
        }

        // Query the scheduled and published posts for the given day
        public function get_todays_posts($date)
        {
            $post_types = SCC()->get_supported_post_types('strive_calendar_post_types');

            $args = [
                'numberposts' => -1,
                'posts_per_page' => -1,
                'post_type' => $post_types,
                'date_query'  => [
                    [
                        'year'  => $date->format('Y'),
                        'month' => $date->format('m'),
                        'day'   => $date->format('d'),
                    ], ],
                'order'       => 'ASC',
                'orderby'     => 'publish_date',
                'post_status' => 'future, publish',
            ];

            return new WP_Query($args);
        }

        // Return HTML for a day in the calendar
        public function day_markup($classes, $query, $date, $day)
        {
            // Include data formatted as Y-m-d
            $html = '<div class="' . esc_attr($classes) . '" data-date="' . $date->format('Y-m-d') . '">';

            // Add first 3 letters of month name to first day of the month or first day of the calendar
            if ($date->format('j') == 1 || $day == 0) {
                $html .= '<span class="month-label"><span>' . self::first_three_letters_of_month($date->format('m')) . '</span> ' . $date->format('j') . '</span>';
            } else {
                $html .= '<span>' . $date->format('j') . '</span>';
            }

            // Add posts
            $html .= '<div class="post-container">';
            if ($query->have_posts()) {
                while ($query->have_posts()) {
                    $query->the_post();
                    $html .= self::post_markup(get_the_ID());
                }
                wp_reset_postdata();
            }
            $html .= '</div>';
            $html .= '<button class="insert-new-post">';
            $html .= '<span class="dashicons dashicons-plus-alt"></span>';
            $html .= '</button>';
            $html .= '</div>';

            return $html;
        }

        // Return HTML for a post within a day
        public function post_markup($id)
        {
            $title = get_the_title($id);
            $classes = self::calendar_post_classes($id);
            $status = get_post_status($id);
            $show_categories = get_option('strive_show_categories', true);
            // Prepare categories for data attribute
            $categories = [];
            foreach (get_the_category($id) as $category) {
                $categories[] = $category->term_id;
            }
            $categories = implode(',', $categories);
            // Prepare tags for data attribute
            $tags = [];
            if (get_the_tags($id)) {
                foreach (get_the_tags($id) as $tag) {
                    $tags[] = $tag->term_id;
                }
            }
            $tags = empty($tags) ? '' : implode(',', $tags);
            $post_type = get_post_type($id);
            $can_edit = true;
            // Limit posts based on capabilities
            if (!SCC()->can_edit_this_post($id)) {
                $can_edit = false;
                $classes .= ' cannot-edit';
            }
            // Build the output
            $html = '<div id="post-' . esc_attr($id) . '" class="' . esc_attr($classes) . '" data-id="' . esc_attr($id) . '" 
                    data-date="' . get_the_date('Y-m-d', $id) . '" data-time="' . get_the_time('H:i:s', $id) . '" data-status="' . esc_attr($status) . '" 
                    data-category="' . esc_attr($categories) . '" data-tag="' . esc_attr($tags) . '" data-post-type="' . esc_attr($post_type) . '">';
            if ($can_edit) {
                $html .= '<a class="quick-edit-link" role="button" tabindex="0">' . esc_html($title) . '</a>';
            }
            $html .= '<div class="title">';
            $html .= self::post_categories_markup($id);
            $html .= '<div class="post-title">';
            $html .= '<span>' . esc_html($title) . '</span>';
            if (count(SCC()->get_supported_post_types('strive_calendar_post_types')) > 1) {
                $html .= SCC()->get_post_type_icon($post_type);
            }
            $html .= '</div>';
            $html .= '<span class="time">' . get_the_time(self::user_time_format_cleaned(), $id) . '</span>';
            if (!empty(get_post_meta($id, '_strive_copy_of', true))) {
                $html .= '<span class="revision-label"> &#8226; ' . esc_html__('Revision', 'strive') . '</span>';
            }
            $html .= self::checklist_progress($id, $post_type);
            $html .= self::post_tags_markup($id);
            $html .= '</div>';
            // Add notice for missed deadline
            if (self::did_post_miss_deadline($id)) {
                $html .= '<div class="missed-deadline-notice">' . esc_html__('Missed Deadline', 'strive') . '</div>';
            }
            if ($can_edit) {
                $html .= '<a class="edit-post" href="' . esc_url(get_edit_post_link($id)) . '" target="_blank"><span class="dashicons dashicons-edit-page"></span></a>';
            }
            $html .= '</div>';

            return $html;
        }

        // HTML for the Quick Edit and Add New Post modal
        public function post_details_modal($id, $day, $post_type, $action)
        {
            $publish_status = get_post_status($id);
            $post_type_label = get_post_type_object($post_type)->labels->singular_name; ?>
            <div id="post-details-container" class="post-details-container">
                <div id="post-details" class="post-details">
                    <div class="quick-edit-header">
                        <div class="quick-edit-heading">
                            <?php if ($id == 0) : ?>
                                <?php echo esc_html__('Add New', 'strive') . ' ' . esc_html($post_type_label); ?>
                            <?php else: ?>
                                <?php esc_html_e('Quick Edit', 'strive'); ?>
                            <?php endif; ?>
                        </div>
                        <?php if ($id != 0) :
                            if ($publish_status == 'publish' || $publish_status == 'private') {
                                $view_text = esc_html__('View', 'strive') . ' ' . esc_html($post_type_label);
                            } else {
                                $view_text = esc_html__('Preview', 'strive') . ' ' . esc_html($post_type_label);
                            } ?>
                            <div class="quick-edit-links">
                                <a id="view-post" href="<?php echo esc_url(get_permalink($id)); ?>" target="_blank"><span class="dashicons dashicons-visibility"></span> <?php echo esc_html($view_text); ?></a>
                                <a id="edit-post" href="<?php echo esc_url(get_edit_post_link($id)); ?>" target="_blank"><span class="dashicons dashicons-edit-page"></span> <?php echo esc_html_e('Open Editor', 'strive'); ?></a>    
                                <a id="delete-post" role="button" tabindex="0" data-id="<?php echo absint($id); ?>"><span class="dashicons dashicons-trash"></span> <?php echo esc_html__('Trash', 'strive') . ' ' . esc_html($post_type_label); ?></a>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php if (self::did_post_miss_deadline($id)) { ?>
                        <div id="missed-deadline-explanation" class="missed-deadline-explanation"><?php
                        if (get_post_meta($id, '_strive_editorial_status', true) == 'complete') { ?>
                            <p><?php printf(__('WordPress failed to publish this scheduled post. Press the "Publish Post" button below to publish it immediately using the scheduled publication date & time. 
                            If this continues to occur, try installing <a target="_blank" href="%s">Missed Scheduled Posts Publisher<span class="dashicons dashicons-external"></span></a>.', 'strive'), 'https://wordpress.org/plugins/missed-scheduled-posts-publisher/'); ?></p>
                        <?php } else { ?>
                            <p><?php esc_html_e('This post was not published because its Editorial Status was not "Complete" at the time of publication. You can reschedule it or click the "Publish Post" button below 
                            to publish it immediately using the scheduled publication date & time.', 'strive'); ?></p>
                        <?php } ?>
                        </div>
                    <?php } ?>
                    <div id="edit-conflict" class="edit-conflict">
                        <span class="dashicons dashicons-warning"></span>
                        <p><?php esc_html_e('Someone has edited this post since you opened the Quick Edit menu. You can submit your changes, or close the Quick Edit menu to use their edits. Only the fields shown here will be affected.', 'strive'); ?></p>
                    </div>
                    <form id="post-data" class="post-data" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" 
                        data-post-type-label="<?php echo esc_attr($post_type_label); ?>">
                        <?php
                        $title = get_the_title($id) == '' ? esc_html__('Untitled', 'strive') : get_the_title($id); ?>
                        <input type="hidden" name="post-id" value="<?php echo absint($id); ?>" id="post-id" />
                        <input type="hidden" name="post-type" value="<?php echo esc_attr($post_type); ?>" id="post-type" />
                        <div class="title">
                            <label><?php echo esc_html_e('Title', 'strive'); ?></label>
                            <input id="post-title" class="large-text post-title" name="post-title" type="text" value="<?php echo esc_attr($title); ?>" />
                        </div>
                        <?php if ($id != 0) : ?>
                            <div class="status"><?php
                                $statuses = SCC()->post_statuses();
                            $editorial_status = get_post_meta($id, '_strive_editorial_status', true); ?>
                                <label><?php echo esc_html_e('Editorial Status', 'strive'); ?></label>
                                <div class="select-container" data-status="<?php echo esc_attr($editorial_status); ?>">
                                    <span class="circle">&#9679;</span>
                                    <select id="post-status" name="post-status"><?php
                                                                                                        foreach ($statuses as $key => $value) {
                                                                                                            echo '<option value="' . esc_attr($key) . '" ' . selected($key, $editorial_status, false) . '>' . esc_html($value) . '</option>';
                                                                                                        } ?>
                                    </select>
                                </div>
                            </div>
                        <?php endif; ?>
                        <div class="author">
                            <label><?php esc_html_e('Author', 'strive'); ?></label>
                            <select id="post-author" name="post-author"><?php
                                $post_author = get_post_field('post_author', $id);
            if ($post_author == '') {
                $post_author = get_current_user_id();
            }
            $authors = SCC()->get_users_can_write();
            foreach ($authors as $author) {
                echo '<option value="' . esc_attr($author->id) . '" ' . selected($author->id, $post_author, false) . '>' . esc_html($author->display_name) . '</option>';
            } ?>
                            </select>
                        </div>
                        <div class="date">
                            <label><?php echo esc_html_e('Date & Time', 'strive'); ?></label>
                            <?php
                            $date_format = self::user_date_format_cleaned();
            $time_format = self::user_time_format_cleaned();
            if ($id == 0) {
                // Saved as H:i:s
                $default_time = SCC()->strive_get_option('strive_default_post_time', '');
                $time = empty($default_time) ? current_time('H:i:s') : $default_time;
                $time = new DateTime($time);
                // $day is a string formatted as Y-m-d
                $date = DateTime::createFromFormat('Y-m-d H:i:s', $day . ' ' . $time->format('H:i:s'), wp_timezone());
                // Pass the user's preferred format and wp_date() will localize it (translate & handle timezone)
                $date = wp_date($date_format . ' ' . $time_format, $date->getTimestamp());
            } else {
                $date = get_the_date($date_format, $id) . ' ' . get_the_time($time_format, $id);
            } ?>
                            <input type="text" id="post-date" class="post-date" name="post-date" value="<?php echo esc_attr($date); ?>" onkeydown="return false" />
                            <input type="hidden" id="post-date-format" name="post-date-format" value="<?php echo esc_attr($date_format . ' ' . $time_format); ?>" />
                            <input type="hidden" id="post-date-save" name="post-date-save" value="" />
                        </div>
                        <span id="publish-warning-publish" class="publish-warning publish"><?php printf(__('<b>Warning:</b> This date has already passed. Updating this %s will publish it.', 'strive'), $post_type_label); ?></span>
                        <span id="publish-warning-schedule" class="publish-warning unpublish"><?php printf(__('<b>Warning:</b> This is a future date. Updating this %s will unpublish and schedule it.', 'strive'), $post_type_label); ?></span>
                        <?php if ($post_type == 'post') : ?>
                            <div class="categories">
                                <label><?php esc_html_e('Category', 'strive'); ?></label><?php
                                $category_colors = SCC()->strive_get_option('strive_category_colors', []);
                            $the_posts_categories = get_the_category($id);
                            $category_IDs = [];
                            $default_cat = get_option('default_category');
                            foreach ($the_posts_categories as $category) {
                                $category_IDs[] = $category->term_id;
                            }
                            if ($id == 0) {
                                $category_IDs[] = $default_cat;
                            } ?>
                                <select id="post-categories" name="post-categories[]" multiple="multiple" style="width:100%;" data-default-cat="<?php echo absint($default_cat); ?>"><?php
                                                                                                    foreach (get_categories(['hide_empty' => false]) as $category) {
                                                                                                        $selected = false;
                                                                                                        if (in_array($category->term_id, $category_IDs)) {
                                                                                                            $selected = $category->term_id;
                                                                                                        }
                                                                                                        $color = '#555555';
                                                                                                        // Check category for color, as well as its parent and grandparent
                                                                                                        if (array_key_exists($category->term_id, $category_colors)) {
                                                                                                            $color = $category_colors[$category->term_id];
                                                                                                        } elseif (array_key_exists($category->parent, $category_colors)) {
                                                                                                            $color = $category_colors[$category->parent];
                                                                                                        } elseif ($category->parent != 0) {
                                                                                                            $ancestor_id = get_category($category->parent)->parent;
                                                                                                            if (array_key_exists($ancestor_id, $category_colors)) {
                                                                                                                $color = $category_colors[$ancestor_id];
                                                                                                            }
                                                                                                        }
                                                                                                        echo '<option value="' . esc_attr($category->term_id) . '" ' . selected($category->term_id, $selected, false) . ' data-color="' . esc_attr($color) . '">' . esc_html($category->name) . '</option>';
                                                                                                    } ?>
                                </select>
                            </div>
                            <div class="tags">
                                <label><?php esc_html_e('Tags', 'strive'); ?></label><?php
                                $post_tags = get_the_tags($id);
            $tag_IDs = [];
            // Will return false if no tags
            if ($post_tags) {
                foreach ($post_tags as $tag) {
                    $tag_IDs[] = $tag->term_id;
                }
            } ?>
                                <select id="post-tags" name="post-tags[]" multiple="multiple" style="width:100%;"><?php
                                    foreach (get_tags(['hide_empty' => false]) as $tag) {
                                        $selected = false;
                                        if (in_array($tag->term_id, $tag_IDs)) {
                                            $selected = $tag->term_id;
                                        }
                                        // Have to use name as value b/c WP will only accept an array of strings for tags...
                                        echo '<option value="' . esc_attr($tag->slug) . '" ' . selected($tag->term_id, $selected, false) . '>' . esc_html($tag->name) . '</option>';
                                    } ?>
                                </select>
                            </div>
                        <?php endif;
            if (self::show_permalink_input($id)) :
                $permalink = self::get_permalink_base_slug($id, $post_type); ?>
                            <div class="permalink">
                                <label><?php esc_html_e('Permalink', 'strive'); ?></label>
                                <span><?php echo esc_html($permalink['base']); ?> </span>
                                <input id="post-permalink" class="post-permalink regular-text" name="post-permalink" value="<?php echo esc_attr($permalink['slug']); ?>" type="text" />
                            </div>
                        <?php endif; ?>
                        <?php if ($post_type == 'post') : ?>
                        <div class="notes">
                            <label><?php esc_html_e('Notes', 'strive'); ?></label>
                            <textarea id="post-notes" name="post-notes" class="post-notes" rows="3"><?php echo esc_html(get_post_meta($id, '_strive_post_notes', true)); ?></textarea>
                        </div>
                        <?php endif;
            if (self::did_post_miss_deadline($id)) {
                $submit_text = esc_html__('Publish', 'strive') . ' ' . $post_type_label;
            } elseif ($action == 'add-future') {
                $submit_text = esc_html__('Schedule', 'strive') . ' ' . $post_type_label;
            } elseif ($action == 'add-draft') {
                $submit_text = esc_html__('Add New Draft', 'strive');
            } elseif ($action == 'update-future' || $action == 'update-publish' || $action == 'update-draft') {
                $submit_text = esc_html__('Update', 'strive') . ' ' . $post_type_label;
            } ?>
                        <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_attr_e($submit_text); ?>">
                        <button id="cancel-update" class="button button-secondary"><?php esc_html_e('Cancel', 'strive'); ?></button>
                        <?php
                        if (self::did_post_miss_deadline($id)) : ?>
                            <button id="unschedule-post" class="unschedule-post"><?php esc_html_e('Or, click here to unschedule this post', 'strive'); ?></button>
                        <?php endif; ?>
                    </form>
                    <button id="close-post-details" class="close-button"><span class="dashicons dashicons-no"></span></button>
                    <div class="border"></div>
                </div>
            </div>
        <?php
        }

        // Stop WordPress from publishing scheduled posts if they do not have the "Complete" editorial status
        public function prevent_incomplete_post_publishing($post_id)
        {
            $block_posts = get_option('strive_block_incomplete_posts', true);
            $meta = get_post_meta($post_id, '_strive_editorial_status', true);
            if ($meta !== 'complete' && $block_posts) {
                wp_clear_scheduled_hook('publish_future_post', [$post_id]);
                remove_action('publish_future_post', 'check_and_publish_future_post', 10);
            }
        }

        /**
         * Helper Functions
         */

        // Format like "m" and "d"
        public function leading_zero_date_formatter($integer)
        {
            if (strlen($integer) == 1) {
                $integer = '0' . $integer;
            }

            return $integer;
        }

        public function first_three_letters_of_month($month)
        {
            $month_name = date('F', mktime(0, 0, 0, $month, 10));
            $three_letters = substr($month_name, 0, 3);

            return $three_letters;
        }

        public function calendar_post_classes($id)
        {
            $status = get_post_meta($id, '_strive_editorial_status', true);
            if (empty($status)) {
                $status = 'not-set';
            }
            $classes = 'post status-' . esc_attr($status);

            // If post is scheduled for later today, it needs a class so it's not gray
            if (get_the_date('Y-m-d', $id) == wp_date('Y-m-d') && get_the_time('H:i:s', $id) > current_time('H:i:s')) {
                $classes .= ' scheduled';
            }

            // Add class for posts that missed their deadline
            if (self::did_post_miss_deadline($id)) {
                $classes .= ' missed-deadline';
            }

            return $classes;
        }

        // Check if a post missed its deadline
        public function did_post_miss_deadline($id)
        {
            if (get_the_date('Y-m-d H:i:s', $id) < wp_date('Y-m-d H:i:s') && get_post_status($id) == 'future') {
                return true;
            } else {
                return false;
            }
        }

        public function checklist_progress($id, $post_type)
        {
            // Only output for supported post types
            if (!in_array($post_type, SCC()->strive_get_option('strive_checklist_post_types', ['post']))) {
                return;
            }

            $html = '';
            $all_checklists = SCC()->strive_get_option('strive_post_checklists', false);
            // See if checklist has ever been saved
            if ($all_checklists) {
                // Get the post's saved checklist
                $checklist_id = get_post_meta($id, '_strive_active_checklist', true);
                // Check if the checklist has been deleted
                $deleted = true;
                foreach ($all_checklists as $checklist) {
                    if ($checklist_id == $checklist['id']) {
                        $deleted = false;
                        break;
                    }
                }
                if ($deleted) {
                    $checklist_id = '';
                }
                // Set to first checklist if no meta and there are any checked tasks
                if ($checklist_id == '' && !empty(json_decode(get_post_meta($id, '_strive_checklists', true)))) {
                    $checklist_id = $all_checklists[0]['id'];
                }
                // Otherwise, fall back to global default
                if ($checklist_id == '') {
                    $checklist_id = SCC()->strive_get_option('strive_default_checklist', '');
                }
                // Or use first checklist if that's not set either
                if ($checklist_id == '') {
                    $checklist_id = $all_checklists[0]['id'];
                }
                // Store the checklist array for this post
                $the_post_checklist = [];
                // Save correct checklist
                foreach ($all_checklists as $checklist) {
                    if ($checklist['id'] == $checklist_id) {
                        $the_post_checklist = $checklist['checklist'];
                    }
                }
                // Count is run recursively for $checklists, so subtract 4 for the parent status arrays
                $total_items = count($the_post_checklist, 1) - 4;
                // Don't output for 0 because that means it's saved with no tasks
                if ($total_items != 0) {
                    $progress = 0;
                    $all_completed_tasks = json_decode(get_post_meta($id, '_strive_checklists', true));
                    if ($all_completed_tasks != '' && $all_completed_tasks != null) {
                        // Loop through the post checklist and increment for every match
                        foreach ($the_post_checklist as $status) {
                            foreach ($status as $task) {
                                $task_data = explode('~', $task);
                                if (in_array($task_data[1], $all_completed_tasks)) {
                                    $progress++;
                                }
                            }
                        }
                    }
                    if ($progress == $total_items) {
                        $html .= '<span class="progress finished">&#10003;</span>';
                    } else {
                        $html .= '<span class="progress">' . absint($progress) . '/' . absint($total_items) . '</span>';
                    }
                }
            }

            return $html;
        }

        // Output the post categories if enabled
        public function post_categories_markup($id)
        {
            $html = '';
            if (get_option('strive_show_categories', true)) {

                // Only output for posts
                if (get_post_type($id) != 'post') {
                    return;
                }

                $category_colors = SCC()->strive_get_option('strive_category_colors', []);
                $categories = get_the_category($id);
                $color = '#555555';
                $html .= '<div class="post-categories">';
                // Output the category label
                foreach ($categories as $category) {
                    $skip = false;
                    // Loop through the posts categories and check if any categories are children of this category
                    foreach ($categories as $check_subs) {
                        if ($check_subs->parent == $category->term_id) {
                            $skip = true;
                        } // edge case: 1st & 3rd tier selected but not 2nd
                        elseif ($check_subs->parent != 0) {
                            if (get_category($check_subs->parent)->parent == $category->term_id) {
                                $skip = true;
                            }
                        }
                    }
                    // Exit if it's a parent with a child category in use
                    if ($skip) {
                        continue;
                    }
                    // Check if category has color (could be brand new category)
                    if (array_key_exists($category->term_id, $category_colors)) {
                        $color = $category_colors[$category->term_id];
                    } // Check if parent has a color
                    elseif ($category->parent != 0) {
                        if (array_key_exists($category->parent, $category_colors)) {
                            $color = $category_colors[$category->parent];
                        } // And check for 3rd tier sub-categories too
                        elseif (get_category($category->parent)->parent != 0) {
                            $ancestor_id = get_category($category->parent)->parent;
                            if (array_key_exists($ancestor_id, $category_colors)) {
                                $color = $category_colors[$ancestor_id];
                            }
                        }
                    }
                    // Category label
                    $html .= '<span style="background-color: ' . esc_attr($color) . '">' . esc_html($category->name) . '</span>';
                }
                $html .= '</div>';
            }

            return $html;
        }

        // Output the post tags if enabled
        public function post_tags_markup($id)
        {
            $html = '';
            if (get_option('strive_show_tags', false)) {

                // Only output for posts
                if (get_post_type($id) != 'post') {
                    return;
                }

                $tags = get_the_tags($id);

                if (!empty($tags)) {
                    $html .= '<ul class="post-tags">';
                    foreach ($tags as $tag) {
                        $html .= '<li class="tag">' . esc_html($tag->name) . '</li>';
                    }
                    $html .= '</ul>';
                }
            }

            return $html;
        }

        // Remove all time characters in case they have a custom format (and remove ":" too since often used)
        public function user_date_format_cleaned()
        {
            $format = trim(str_replace(['a', 'A', 'g', 'G', 'h', 'H', 'i', 's', 'T', 'c', 'r', 'U', ':'], '', get_option('date_format')));
            if ($format == '') {
                $format = 'F j, Y';
            }

            return $format;
        }

        // Remove all day characters in case they have a custom format
        public function user_time_format_cleaned()
        {
            $format = get_option('time_format');
            if ($format == '') {
                $format = 'g:i a';
            }

            return $format;
        }

        public function show_permalink_input($id)
        {
            // Show if the permalink structure is set, it's not a new post, and it's not a revision
            if (!empty(get_option('permalink_structure')) && $id != 0 && (SCC()->revisions->get_revision_parent($id) === false)) {
                return true;
            } else {
                return false;
            }
        }

        public function get_permalink_base_slug($id, $post_type)
        {
            // 0-> http://strive/04/%postname%/
            // 1-> example-post-title
            $permalink_data = get_sample_permalink($id);
            $permalink_base = $permalink_data[0];
            if ($post_type == 'post') {
                $permalink_base = trailingslashit(str_replace('%postname%', '', $permalink_base));
            } else {
                $permalink_base = trailingslashit(str_replace('%pagename%', '', $permalink_base));
            }

            $slug = $permalink_data[1];

            $permalink = [
                'base' => $permalink_base,
                'slug' => $slug,
            ];

            return $permalink;
        }

        public function get_unscheduled_drafts()
        {
            $post_types = SCC()->get_supported_post_types('strive_calendar_post_types');

            // Get drafts and pending posts
            $args = [
                'posts_per_page' => 30,
                'post_status'    => ['draft', 'pending'],
                'perm'           => 'readable',
                'post_type'      => $post_types,
            ];

            // Limit to own posts for Contributors and Authors
            if (!SCC()->permission_manager('editor')) {
                $args['author'] = get_current_user_id();
            }

            $posts = new WP_Query($args);

            // Return the posts
            return $posts->posts;
        }

        /**
         * Ajax Callback Functions
         */

        // Reload the calendar based on the selected date
        public function reload_calendar()
        {
            if (!SCC()->permission_manager('author')) {
                return;
            }

            // Make sure the date and the nonce are set
            if (!isset($_POST['calendar_date']) || !isset($_POST['reload_calendar_nonce'])) {
                return;
            }

            // Verify the nonce
            check_ajax_referer('reload_calendar', 'reload_calendar_nonce');

            // Y-m-d formatted date
            $date = wp_unslash($_POST['calendar_date']);
            $date = new DateTimeImmutable($date);

            $data = [
                'calendar' => self::get_the_calendar($date),
                'day' => $date->format(get_option('date_format')),
            ];

            wp_send_json(json_encode($data));

            wp_die();
        }

        // Open the Quick Edit menu when clicking a post
        public function open_post_details_modal()
        {
            // Restrict to Authors and up
            if (!SCC()->permission_manager('author')) {
                return;
            }

            // Make sure the post data and the nonce are set
            if (!isset($_GET['post_data']) || !isset($_GET['open_post_details_modal_nonce'])) {
                return;
            }

            // Verify the nonce
            check_ajax_referer('open_post_details_modal', 'open_post_details_modal_nonce');

            global $wpdb;

            $post_data = wp_unslash($_GET['post_data']);

            // If only author, limit to their posts
            if (!SCC()->can_edit_this_post($post_data['id']) && $post_data['id'] != 0) {
                return;
            }

            self::post_details_modal($post_data['id'], $post_data['date'], $post_data['post_type'], $post_data['form_action']);

            wp_die();
        }

        // Save a post when from the Quick Edit modal (includes adding brand new posts)
        public function update_post_data()
        {
            // Restrict to Authors and up
            if (!SCC()->permission_manager('author')) {
                return;
            }

            // Make sure the post data and the nonce are set
            if (!isset($_POST['form_data']) || !isset($_POST['update_post_nonce'])) {
                return;
            }

            // Verify the nonce
            check_ajax_referer('update_post', 'update_post_nonce');

            $data = wp_unslash($_POST['form_data']);

            $form_values = [];

            // Unserialize the string into an array
            parse_str($data, $form_values);

            // Get the form action
            $action = wp_unslash($_POST['form_action']);

            // Sanitize the form action
            $valid = ['add-future', 'add-draft', 'update-future', 'update-draft', 'update-publish', 'unschedule'];
            if (!in_array($action, $valid)) {
                wp_send_json_error(esc_html__('Form action not set.', 'strive'));
                wp_die();
            }

            // If only author, limit to their posts
            if (!SCC()->can_edit_this_post($form_values['post-id']) && $post_data['id'] != 0) {
                return;
            }

            // Set default status to "not-started" if not saved yet
            $editorial_status = $form_values['post-status'] == '' ? 'not-started' : $form_values['post-status'];

            // Unserializing the empty value converts it to null, which doesn't save properly
            // Needs to be an empty array if there are no tags
            if ($form_values['post-tags'] == null) {
                $form_values['post-tags'] = [];
            }

            // Format saved fields into array matching post fields
            $post_data = [
                'ID'            => $form_values['post-id'], // 0 tells wp_insert_post() to make a new post
                'post_title'    => $form_values['post-title'],
                'post_author'   => $form_values['post-author'],
                'post_name'     => $form_values['post-permalink'],
                'post_date'     => $form_values['post-date-save'],
                'post_date_gmt' => get_gmt_from_date($form_values['post-date-save']),
                'edit_date'     => true,
                'meta_input'    => [
                    '_strive_editorial_status' => $editorial_status,
                ],
            ];

            // Add category, tag, and notes fields for posts only
            if ($form_values['post-type'] == 'post') {
                $post_data['post_category'] = $form_values['post-categories'];
                $post_data['tags_input'] = $form_values['post-tags'];
                $post_data['meta_input']['_strive_post_notes'] = $form_values['post-notes'];
            }

            // Add the post type for brand new posts using wp_insert_post()
            if ($form_values['post-id'] == 0) {
                $post_data['post_type'] = $form_values['post-type'];
            }

            // Sanitize all post fields
            $post_data = sanitize_post($post_data);

            if ($action == 'unschedule') {
                $post_data['post_status'] = 'draft';
                $post_id = wp_update_post($post_data);
            } elseif ($action == 'update-future' || $action == 'update-draft' || $action == 'update-publish') {
                $post_id = wp_update_post($post_data);
            } else {
                if ($action == 'add-future') {
                    $post_data['post_status'] = 'future';
                } elseif ($action == 'add-draft') {
                    $post_data['post_status'] = 'draft';
                }
                $post_id = wp_insert_post($post_data);
            }

            $data = [
                'markup' => self::post_markup($post_id),
                'id' => $post_id,
            ];

            wp_send_json(json_encode($data));

            wp_die();
        }

        // Save a post when dragged and dropped to a new day
        public function drag_drop_update()
        {
            // Restrict to Authors and up
            if (!SCC()->permission_manager('author')) {
                return;
            }

            // Make sure the post data and the nonce are set
            if (!isset($_POST['post_data']) || !isset($_POST['drag_drop_update_nonce'])) {
                return;
            }

            // Verify the nonce
            check_ajax_referer('drag_drop_update', 'drag_drop_update_nonce');

            global $wpdb;

            $post_data = wp_unslash($_POST['post_data']);

            // Convert JSON string into array
            $post_data = json_decode(html_entity_decode(stripslashes($post_data)));

            // If only author, limit to their posts
            if (!SCC()->can_edit_this_post($post_data->id)) {
                return;
            }

            // Structure based on WP post fields
            $post_array = [
                'ID'            => $post_data->id,
                'post_date'     => $post_data->post_date,
                'post_date_gmt' => get_gmt_from_date($post_data->post_date),
                'edit_date'     => true,
                'post_status'   => $post_data->post_status,
            ];

            // Sanitize all post fields
            $post_array = sanitize_post($post_array);

            wp_update_post($post_array);

            wp_die();
        }

        // Delete post (via link in Quick Edit menu)
        public function delete_post()
        {
            // Restrict to Authors and up
            if (!SCC()->permission_manager('author')) {
                return;
            }

            // Make sure the post data and the nonce are set
            if (!isset($_POST['post_id']) || !isset($_POST['delete_post_nonce'])) {
                return;
            }

            // Verify the nonce
            check_ajax_referer('delete_post', 'delete_post_nonce');

            global $wpdb;

            $post_id = wp_unslash($_POST['post_id']);

            // Sanitize via proving an integer
            $post_id = absint($post_id);

            // If only author, limit to their posts
            if (!SCC()->can_edit_this_post($post_id)) {
                return;
            }

            wp_trash_post($post_id);

            wp_die();
        }

        // Select for inserting a post draft
        public function post_draft_select()
        {
            // Restrict to Authors and up
            if (!SCC()->permission_manager('author')) {
                return;
            }

            // Make sure the post data and the nonce are set
            if (!isset($_POST['date']) || !isset($_POST['post_type']) || !isset($_POST['post_draft_select_nonce'])) {
                return;
            }

            // Verify the nonce
            check_ajax_referer('post_draft_select', 'post_draft_select_nonce');

            $date = wp_unslash($_POST['date']);
            $post_type = wp_unslash($_POST['post_type']);

            // General sanitization
            $date = sanitize_text_field($date);
            $post_type = sanitize_text_field($post_type);

            $post_type_label = get_post_type_object($post_type)->labels->singular_name;

            $args = [
                'numberposts' => -1,
                'post_status' => 'draft',
                'post_type'   => $post_type,
            ];

            // If only author, limit to their posts
            if (!SCC()->permission_manager('editor')) {
                $args['author'] = get_current_user_id();
            }

            $posts = get_posts($args);

            $html = '<div id="insert-draft-modal" class="insert-post" data-date="' . $date . '">';
            $html .= '<div class="inner">';
            $html .= '<div class="title">' . sprintf(esc_html__('Choose a %s Draft', 'strive'), $post_type_label) . '</div>';
            if (count($posts) == 0) {
                $html .= '<div class="no-drafts"><span>' . esc_html('You don\'t have any unscheduled drafts.', 'strive') . '</span></div>';
            } else {
                $html .= '<select id="post-draft-select" class="post-draft-select">';
                foreach ($posts as $post) {
                    $html .= '<option value="' . absint($post->ID) . '">' . esc_html(get_the_title($post->ID)) . '</option>';
                }
                $html .= '</select>';
                $html .= '<button id="insert-draft-action" class="insert-post-draft button button-primary">' . sprintf(esc_html__('Insert %s Draft', 'strive'), $post_type_label) . ' </button>';
            }
            $html .= '<button id="close-insert-draft" class="close-button"><span class="dashicons dashicons-no"></span></button>';
            $html .= '</div></div>';

            echo $html;

            wp_die();
        }

        // Insert in calendar. Schedule an existing draft.
        public function insert_post_draft()
        {
            // Restrict to Authors and up
            if (!SCC()->permission_manager('author')) {
                return;
            }

            // Make sure the post data and the nonce are set
            if (!isset($_POST['post_data']) || !isset($_POST['insert_post_draft_nonce'])) {
                return;
            }

            // Verify the nonce
            check_ajax_referer('insert_post_draft', 'insert_post_draft_nonce');

            global $wpdb;

            $post_data = wp_unslash($_POST['post_data']);

            // If only author, limit to their posts
            if (!SCC()->can_edit_this_post($post_data['id'])) {
                return;
            }

            // Add time since date doesn't include it
            $default_time = SCC()->strive_get_option('strive_default_post_time', '');
            $time = empty($default_time) ? current_time('H:i:s') : $default_time;
            $date = $post_data['post_date'] . ' ' . $time;

            // Structure based on WP post fields
            $post_array = [
                'ID'            => $post_data['id'],
                'post_status'   => 'future',
                'post_date'     => $date,
                'post_date_gmt' => get_gmt_from_date($post_data['post_date']),
                'edit_date'     => true,
            ];

            // Sanitize post fields
            $post_array = sanitize_post($post_array);

            // Save new date
            wp_update_post($post_array);

            // Return the new HTML for that day
            echo self::post_markup($post_array['ID']);

            wp_die();
        }

        public function search_unscheduled_posts()
        {
            // Restrict to Authors and up
            if (!SCC()->permission_manager('author')) {
                return;
            }

            // Make sure the post data and the nonce are set
            if (!isset($_POST['search_query']) || !isset($_POST['search_unscheduled_posts_nonce'])) {
                return;
            }

            // Verify the nonce
            check_ajax_referer('search_unscheduled_posts', 'search_unscheduled_posts_nonce');

            // Unslash the query
            $search_query = wp_unslash($_POST['search_query']);

            $post_types = SCC()->get_supported_post_types('strive_calendar_post_types');

            // Build arguments
            $args = [
                'posts_per_page' => 30,
                'post_status'    => ['draft', 'pending'],
                'post_type'      => $post_types,
                'perm'           => 'readable',
                's'              => $search_query,
            ];

            // If only author, limit to their posts
            if (!SCC()->permission_manager('editor')) {
                $args['author'] = get_current_user_id();
            }

            // Fetch the query
            $query = new WP_Query($args);

            // Prepare variable to store the HTML
            $html = '';

            // Loop through the posts and get the HTML to return
            if ($query->have_posts()) {
                while ($query->have_posts()) {
                    $query->the_post();
                    $html .= self::post_markup(get_the_ID());
                }
                wp_reset_postdata();
            }

            // Return the posts
            echo $html;

            wp_die();
        }

        // Save the sidebar as open or closed
        public function save_sidebar_display()
        {
            // Restrict to Admins only
            if (!SCC()->permission_manager('admin')) {
                return;
            }

            // Make sure the post data and the nonce are set
            if (!isset($_POST['sidebar_open']) || !isset($_POST['save_sidebar_display_nonce'])) {
                return;
            }

            // Verify the nonce
            check_ajax_referer('save_sidebar_display', 'save_sidebar_display_nonce');

            // Sanitize
            $sidebar_open = wp_unslash($_POST['sidebar_open']) === 'open' ? true : false;

            // Save the new open/closed state for the sidebar
            update_option('strive_unscheduled_drafts_open', $sidebar_open);

            wp_die();
        }
    }
}
