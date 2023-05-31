<?php
if (!class_exists('SCC_Calendar_Settings')) {
    class SCC_Calendar_Settings
    {
        public function __construct()
        {
            add_action('admin_init', [$this, 'register_strive_calendar_settings']);
            add_action('wp_ajax_save_settings', [$this, 'save_settings']);
        }

        // Add Strive's settings via Settings API
        public function register_strive_calendar_settings()
        {
            // Create a section to output the settings in
            add_settings_section('strive-settings-section', esc_html__('Calendar Settings', 'strive'), [$this, 'page_description'], 'strive-content-calendar-settings');

            // Onboarding - Calendar
            add_settings_section('strive-onboarding-calendar', esc_html__('Customize the calendar', 'strive'), [$this, 'onboarding_calendar_subheading'], 'strive-content-calendar-welcome');

            //=======================================
            // Number of extra weeks in calendar
            //=======================================

            // Args for # of calendar weeks setting
            $args = [
                'type'              => 'number',
                'default'           => 1,
                'sanitize_callback' => 'absint',
            ];

            // Register setting for # of weeks in calendar
            register_setting('strive_settings', 'calendar_weeks', $args);

            // Add a field for the checklist setting
            add_settings_field('calendar_weeks', esc_html__('Number of weeks to display', 'strive'), [$this, 'calendar_weeks_callback'], 'strive-content-calendar-settings', 'strive-settings-section', ['class' => 'calendar-weeks']);

            // Add to onboarding
            add_settings_field('calendar_weeks', esc_html__('Number of weeks to display', 'strive'), [$this, 'calendar_weeks_callback'], 'strive-content-calendar-welcome', 'strive-onboarding-calendar', ['class' => 'calendar-weeks']);

            //=======================================
            // Number of previews weeks in calendar
            //=======================================

            // Args for # of previous weeks
            $args = [
                'type'              => 'number',
                'default'           => 0,
                'sanitize_callback' => 'absint',
            ];

            // Register setting for # of weeks in calendar
            register_setting('strive_settings', 'calendar_prev_weeks', $args);

            // Add a field for the checklist setting
            add_settings_field('calendar_prev_weeks', esc_html__('Number of previous weeks to display', 'strive'), [$this, 'calendar_prev_weeks_callback'], 'strive-content-calendar-settings', 'strive-settings-section', ['class' => 'calendar-prev-weeks']);

            // Add to onboarding
            add_settings_field('calendar_prev_weeks', esc_html__('Number of previous weeks to display', 'strive'), [$this, 'calendar_prev_weeks_callback'], 'strive-content-calendar-welcome', 'strive-onboarding-calendar', ['class' => 'calendar-prev-weeks']);

            //=======================================
            // Starting day of week
            //=======================================

            $args = [
                'type'              => 'integer',
                'default'           => 0,
                'sanitize_callback' => 'absint',
            ];

            register_setting('strive_settings', 'strive_starting_dow', $args);

            add_settings_field('strive_starting_dow', esc_html__('Day to start the week on', 'strive'), [$this, 'strive_starting_dow_callback'], 'strive-content-calendar-settings', 'strive-settings-section', ['class' => 'starting-dow']);

            // Add to onboarding
            add_settings_field('strive_starting_dow', esc_html__('Which day of the week should the calendar start on?', 'strive'), [$this, 'strive_starting_dow_callback'], 'strive-content-calendar-welcome', 'strive-onboarding-calendar', ['class' => 'starting-dow']);

            //=======================================
            // Default publication time
            //=======================================

            $args = [
                'type'              => 'string',
                'default'           => '',
                'sanitize_callback' => 'sanitize_text_field',
            ];

            register_setting('strive_settings', 'strive_default_post_time', $args);

            add_settings_field('strive_default_post_time', esc_html__('Default publication time for new posts', 'strive'), [$this, 'strive_default_post_time_callback'], 'strive-content-calendar-settings', 'strive-settings-section', ['class' => 'default-post-time']);

            // Add to onboarding
            add_settings_field('strive_default_post_time', esc_html__('Choose a default time for new posts', 'strive'), [$this, 'strive_default_post_time_callback'], 'strive-content-calendar-welcome', 'strive-onboarding-calendar', ['class' => 'default-post-time']);

            //=======================================
            // Default post category
            //=======================================

            // Register the built-in default category option
            register_setting('strive_settings', 'default_category');

            add_settings_field('default_category', esc_html__('Default post category', 'strive'), [$this, 'strive_default_post_category_callback'], 'strive-content-calendar-settings', 'strive-settings-section', ['class' => 'default-category']);

            // Add to onboarding
            add_settings_field('default_category', esc_html__('Choose a default category for new posts', 'strive'), [$this, 'strive_default_post_category_callback'], 'strive-content-calendar-welcome', 'strive-onboarding-calendar', ['class' => 'default-category']);

            //=======================================
            // Keep Unscheduled Drafts sidebar open
            //=======================================

            $args = [
                'type'              => 'boolean',
                'default'           => false,
                'sanitize_callback' => 'rest_sanitize_boolean',
            ];

            // Register setting for unscheduled drafts sidebar
            register_setting('strive_settings', 'strive_unscheduled_drafts_open', $args);

            // Add to onboarding so that default value gets saved properly
            add_settings_field('strive_unscheduled_drafts_open', esc_html__('Keep the Unscheduled Drafts sidebar open by default', 'strive'), [$this, 'strive_unscheduled_drafts_open_callback'], 'strive-content-calendar-welcome', 'strive-onboarding-calendar', ['class' => 'unscheduled-drafts-open']);

            //=======================================
            // Disable option to block non-complete posts
            //=======================================

            $args = [
                'type'              => 'boolean',
                'default'           => true,
                'sanitize_callback' => 'rest_sanitize_boolean',
            ];

            register_setting('strive_settings', 'strive_block_incomplete_posts', $args);

            add_settings_field('strive_block_incomplete_posts', esc_html__('Prevent scheduled posts from publishing if their editorial status is not "Complete."', 'strive'), [$this, 'strive_block_incomplete_posts_callback'], 'strive-content-calendar-settings', 'strive-settings-section', ['class' => 'block-incomplete-posts']);

            // Add to onboarding
            add_settings_field('strive_block_incomplete_posts', esc_html__('Block scheduled posts from getting published if they don\'t have the "Complete" status.', 'strive'), [$this, 'strive_block_incomplete_posts_callback'], 'strive-content-calendar-welcome', 'strive-onboarding-calendar', ['class' => 'block-incomplete-posts']);

            //=======================================
            // Color Blind Mode
            //=======================================

            $args = [
                'type'              => 'boolean',
                'default'           => false,
                'sanitize_callback' => 'rest_sanitize_boolean',
            ];

            register_setting('strive_settings', 'strive_color_blind_mode', $args);

            add_settings_field('strive_color_blind_mode', esc_html__('Enable Color Blind Mode', 'strive'), [$this, 'strive_color_blind_mode_callback'], 'strive-content-calendar-settings', 'strive-settings-section', ['class' => 'color-blind-mode']);

            // Add to onboarding
            add_settings_field('strive_color_blind_mode', esc_html__('Enable Color Blind Mode', 'strive'), [$this, 'strive_color_blind_mode_callback'], 'strive-content-calendar-welcome', 'strive-onboarding-calendar', ['class' => 'color-blind-mode']);

            //=======================================
            // Show Post Categories
            //=======================================

            $args = [
                'type'              => 'boolean',
                'default'           => true,
                'sanitize_callback' => 'rest_sanitize_boolean',
            ];

            register_setting('strive_settings', 'strive_show_categories', $args);

            add_settings_field('strive_show_categories', esc_html__('Show post categories in the calendar', 'strive'), [$this, 'strive_show_categories_callback'], 'strive-content-calendar-settings', 'strive-settings-section', ['class' => 'show-categories']);

            // Add to onboarding
            add_settings_field('strive_show_categories', esc_html__('Show post categories in the calendar', 'strive'), [$this, 'strive_show_categories_callback'], 'strive-content-calendar-welcome', 'strive-onboarding-calendar', ['class' => 'show-categories']);

            //=======================================
            // Category colors
            //=======================================

            $cat_args = [
                'hide_empty' => 0,
                'parent'     => 0,
            ];
            $categories = get_categories($cat_args);
            $default = [];
            // Build an array of all post categories with default color values
            foreach ($categories as $category) {
                $default[$category->term_id] = '#555555';
            }

            $args = [
                'type'              => 'array',
                'default'           => $default,
                'sanitize_callback' => [$this, 'sanitize_category_colors'],
            ];

            register_setting('strive_settings', 'strive_category_colors', $args);

            add_settings_field('strive_category_colors', esc_html__('Category colors', 'strive'), [$this, 'strive_category_colors_callback'], 'strive-content-calendar-settings', 'strive-settings-section', ['class' => 'category-colors']);

            // Add to onboarding
            add_settings_field('strive_category_colors', esc_html__('Category colors', 'strive'), [$this, 'strive_category_colors_callback'], 'strive-content-calendar-welcome', 'strive-onboarding-calendar', ['class' => 'category-colors']);

            //=======================================
            // Show Post Tags
            //=======================================

            $args = [
                'type'              => 'boolean',
                'default'           => false,
                'sanitize_callback' => 'rest_sanitize_boolean',
            ];
            register_setting('strive_settings', 'strive_show_tags', $args);

            add_settings_field('strive_show_tags', esc_html__('Show post tags in the calendar', 'strive'), [$this, 'strive_show_tags_callback'], 'strive-content-calendar-settings', 'strive-settings-section', ['class' => 'show-tags']);

            // Add to onboarding
            add_settings_field('strive_show_tags', esc_html__('Show post tags in the calendar', 'strive'), [$this, 'strive_show_tags_callback'], 'strive-content-calendar-welcome', 'strive-onboarding-calendar', ['class' => 'show-tags']);

            //=======================================
            // Post Types to Include
            //=======================================

            // Args for # of calendar weeks setting
            $args = [
                'type'              => 'array',
                'default'           => ['post'],
                'sanitize_callback' => [$this, 'sanitize_post_type'],
            ];

            register_setting('strive_settings', 'strive_calendar_post_types', $args);
            add_settings_field('strive_calendar_post_types', esc_html__('Post types to display in the Calendar', 'strive'), [$this, 'post_types_callback'], 'strive-content-calendar-settings', 'strive-settings-section', ['class' => 'post-type']);
            add_settings_field('strive_calendar_post_types', esc_html__('Post types to display in the Calendar', 'strive'), [$this, 'post_types_callback'], 'strive-content-calendar-welcome', 'strive-onboarding-calendar', ['class' => 'post-type']);

            //=======================================
            // Live Calendar
            //=======================================

            $args = [
                'type'              => 'boolean',
                'default'           => false,
                'sanitize_callback' => 'rest_sanitize_boolean',
            ];

            // Register setting for unscheduled drafts sidebar
            register_setting('strive_settings', 'strive_live_calendar', $args);

            // Add to onboarding so that default value gets saved properly
            add_settings_field('strive_live_calendar', esc_html__('Enable live calendar updates', 'strive'), [$this, 'strive_live_calendar_callback'], 'strive-content-calendar-welcome', 'strive-onboarding-calendar', ['class' => 'live-updates']);
        }

        // Callback function to output the HTML for the checklist setting
        public function calendar_weeks_callback()
        {
            $cal_weeks = SCC()->strive_get_option('calendar_weeks', 1);
            $weeks = [
                0 => 4,
                1 => 5,
                2 => 6,
                3 => 7,
                4 => 8,
                5 => 9,
                6 => 10,
                7 => 11,
                8 => 12,
            ];
            echo '<select id="calendar_weeks" name="calendar_weeks" value="' . esc_attr($cal_weeks) . '" >';
            foreach ($weeks as $key => $value) {
                echo '<option value="' . esc_attr($key) . '" ' . selected($cal_weeks, $key, false) . '>' . esc_html($value) . ' ' . esc_html__('weeks', 'strive') . '</option>';
            }
        }

        // Callback function for previous weeks
        public function calendar_prev_weeks_callback()
        {
            $setting = SCC()->strive_get_option('calendar_prev_weeks', 0); ?>
            <label class="calendar-prev-week-option weeks-0">
                <input type="radio" name="calendar_prev_weeks" value="0" <?php checked(0, get_option('calendar_prev_weeks'), true); ?>>
                <span><?php esc_html_e('0 weeks', 'strive'); ?></span>
            </label>
            <label class="calendar-prev-week-option weeks-1">
                <input type="radio" name="calendar_prev_weeks" value="1" <?php checked(1, get_option('calendar_prev_weeks'), true); ?>>
                <span><?php esc_html_e('1 week', 'strive'); ?></span>
            </label>
            <label class="calendar-prev-week-option weeks-2">
                <input type="radio" name="calendar_prev_weeks" value="2" <?php checked(2, get_option('calendar_prev_weeks'), true); ?>>
                <span><?php esc_html_e('2 weeks', 'strive'); ?></span>
            </label>
            <?php
        }

        // Callback for default post category setting
        public function strive_starting_dow_callback()
        {
            $setting = SCC()->strive_get_option('strive_starting_dow', 0);
            $dows = [
                0 => 'Sunday',
                1 => 'Monday',
                2 => 'Tuesday',
                3 => 'Wednesday',
                4 => 'Thursday',
                5 => 'Friday',
                6 => 'Saturday',
            ];

            echo '<select id="strive_starting_dow" name="strive_starting_dow" value="' . esc_attr($setting) . '" >';
            foreach ($dows as $key => $value) {
                echo '<option value="' . esc_attr($key) . '" ' . selected($setting, $key, false) . '>' . esc_html($value) . '</option>';
            }
            echo '</select>';
        }

        // Callback for default time setting
        public function strive_default_post_time_callback()
        {
            $default_time = SCC()->strive_get_option('strive_default_post_time', '');
            $time_array = self::formatted_hours(); ?>
            <label class="strive-default-post-time">
                <select id="strive_default_post_time" name="strive_default_post_time" value="<?php echo esc_attr($default_time); ?>">
                    <option value="" <?php selected('', SCC()->strive_get_option('strive_default_post_time', ''), true); ?>><?php esc_html_e('Use current time', 'strive'); ?></option>
                    <?php
                    foreach ($time_array as $time) {

                        // Need a full date, so use today's date with the array's time
                        $readable_time = new DateTime(date('Y-m-d') . ' ' . $time);

                        // Convert to the user's preferred time format (13:00 VS 1:00pm)
                        $readable_time = $readable_time->format(get_option('time_format'));

                        // Output options
                        echo '<option value="' . esc_attr($time) . '" ' . selected($time, SCC()->strive_get_option('strive_default_post_time', ''), false) . '>' . esc_html($readable_time) . '</option>';
                    } ?>
                </select>
            </label>
            <?php
        }

        public function formatted_hours()
        {
            $time_array = [];
            $x = 0;
            while ($x < 24) {
                $time = $x . ':00:00';
                $time_array[] = str_pad($time, 8, 0, STR_PAD_LEFT);
                $x++;
            }

            return $time_array;
        }

        // Callback for default post category setting
        public function strive_default_post_category_callback()
        {
            $default_category = get_option('default_category');
            $categories = get_categories(['hide_empty' => false]);

            echo '<select id="default_category" name="default_category" value="' . esc_attr($default_category) . '" >';
            foreach ($categories as $category) {
                echo '<option value="' . esc_attr($category->term_id) . '" ' . selected($default_category, $category->term_id, false) . '>' . esc_html($category->name) . '</option>';
            }
            echo '</select>';
        }

        // Callback function for unscheduled drafts toggle
        public function strive_unscheduled_drafts_open_callback()
        {
            $setting = SCC()->strive_get_option('strive_unscheduled_drafts_open', false); ?>
            <label class="strive-unscheduled-drafts-open switch" for="strive_unscheduled_drafts_open">
                <input type="checkbox" name="strive_unscheduled_drafts_open" id="strive_unscheduled_drafts_open" <?php checked(true, $setting, true); ?>>
                <span class="slider"></span>
            </label>
            <?php
        }

        // Callback function to output the HTML for the checklist setting
        public function strive_block_incomplete_posts_callback()
        {
            $block_posts = get_option('strive_block_incomplete_posts', true); ?>
            <label class="strive-block-incomplete-posts switch" for="strive_block_incomplete_posts">
                <input type="checkbox" name="strive_block_incomplete_posts" id="strive_block_incomplete_posts" <?php checked(true, $block_posts, true); ?>>
                <span class="slider"></span>
            </label>
            <?php
        }

        // Callback function for color blind mode
        public function strive_color_blind_mode_callback()
        {
            $setting = get_option('strive_color_blind_mode', false); ?>
            <label class="strive-color-blind-mode switch" for="strive_color_blind_mode">
                <input type="checkbox" name="strive_color_blind_mode" id="strive_color_blind_mode" <?php checked(true, $setting, true); ?>>
                <span class="slider"></span>
            </label>
            <?php
        }

        // Callback function for showing post categories
        public function strive_show_categories_callback()
        {
            $setting = get_option('strive_show_categories', true); ?>
            <label class="strive-show-categories switch" for="strive_show_categories">
                <input type="checkbox" name="strive_show_categories" id="strive_show_categories" <?php checked(true, $setting, true); ?>>
                <span class="slider"></span>
            </label>
            <?php
        }

        // Callback function for showing post categories
        public function strive_category_colors_callback()
        {
            $setting = SCC()->strive_get_option('strive_category_colors', []);

            $args = [
                'hide_empty' => 0,
                'parent'     => 0,
            ];
            $categories = get_categories($args);

            echo '<button id="expand-categories" class="expand-categories"><span class="text">' . __('Display color choices', 'strive') . '</span> <span class="dashicons dashicons-arrow-down"></span></button>';
            echo '<div class="category-colors">';
            echo '<ul>';
            // Counter is needed so color pickers can have unique IDs to apply Pickr to. Could be anything but this is simple and guarantees no repeats.
            $counter = 1;
            foreach ($categories as $category) {
                $value = '#555555';
                if (array_key_exists($category->term_id, $setting)) {
                    $value = $setting[$category->term_id];
                }
                echo '<li>';
                echo '<input id="strive_category_colors[' . esc_attr($category->term_id) . ']" name="strive_category_colors[' . esc_attr($category->term_id) . ']" 
                        class="color-picker-value" type="hidden" value="' . esc_attr($value) . '" />';
                echo '<span id="color-picker-' . esc_attr($counter) . '" class="color-picker-button"></span>';
                echo '<span>' . esc_html($category->name) . '</span>';
                echo '</li>';
                $counter++;
            }
            echo '</ul>';
            echo '</div>'; ?>

            <?php
        }

        // Callback function for showing post tags
        public function strive_show_tags_callback()
        {
            $setting = get_option('strive_show_tags', false); ?>
            <label class="strive-show-tags switch" for="strive_show_tags">
                <input type="checkbox" name="strive_show_tags" id="strive_show_tags" <?php checked(true, $setting, true); ?>>
                <span class="slider"></span>
            </label>
            <?php
        }

        // Callback for post types
        public function post_types_callback()
        {
            $saved = SCC()->strive_get_option('strive_calendar_post_types', ['post']);
            $post_types = SCC()->get_all_post_types();
            $any_disabled = false;

            foreach ($post_types as $post_type) {
                $selected = in_array($post_type, $saved) ? true : false;
                $label = get_post_type_object($post_type)->labels->name; ?>
                <label class="post-types-label" for="calendar-post-type-<?php echo esc_attr($post_type); ?>">
                    <?php if (post_type_supports($post_type, 'custom-fields')) : ?>
                        <input type="checkbox" name="strive_calendar_post_types[]" id="calendar-post-type-<?php echo esc_attr($post_type); ?>" <?php checked(true, $selected, true); ?> value="<?php esc_attr_e($post_type); ?>">
                    <?php else :
                        $any_disabled = true; ?>
                        <input disabled type="checkbox" name="strive_calendar_post_types[]" <?php checked(true, $selected, true); ?> value="<?php esc_attr_e($post_type); ?>">
                    <?php endif; ?>
                    <span><?php esc_html_e($label); ?></span>
                </label><?php
            }
            if ($any_disabled) {
                echo '<p class="disabled-note">' . esc_html__('Only custom post types with custom field support can be enabled.', 'strive') . ' <a href="https://strivecalendar.com/knowledgebase/pipeline/adding-custom-field-support/" target="_blank">' . esc_html__('Learn More', 'strive') . '</a></p>';
            }
        }

        public function strive_live_calendar_callback()
        {
            $setting = SCC()->strive_get_option('strive_live_calendar', false); ?>
            <label class="strive-live-calendar switch" for="strive_live_calendar">
                <input type="checkbox" name="strive_live_calendar" id="strive_live_calendar" <?php checked(true, $setting, true); ?>>
                <span class="slider"></span>
            </label>
            <?php
        }

        public function onboarding_calendar_subheading()
        {
            echo '<p class="subheading">' . __('Customize how the calendar works on your site', 'strive') . '</p>';
        }

        // Sanitize the category colors setting
        public function sanitize_category_colors($color_data)
        {
            // Make sure only valid hex codes are saved
            foreach ($color_data as &$color) {
                $color = sanitize_hex_color($color);
                // Bad entries get returned as null
                if ($color == null) {
                    // Maintain valid hex code default
                    $color = '#555555';
                }
            }

            return $color_data;
        }

        public function sanitize_post_type($array)
        {
            $save = [];
            foreach ($array as $post_type) {
                if (get_post_type_object($post_type) !== null) {
                    $save[] = $post_type;
                }
            }

            return $save;
        }

        // Called in strive.php to output everything
        public function output_settings_fields()
        {

            // Open the form. This is a standard WP practice and WP closes out the form itself
            echo '<div id="strive-settings-container" class="strive-settings-container">';
            echo '<div id="strive-settings" class="strive-settings">';
            echo '<div class="inner">';

            echo '<form method="post" action="options.php" id="strive-calendar-settings">';

            // Output the fields required for the Settings API to save the setting
            settings_fields('strive_settings');

            // Output the settings section with all the visual content from the callback
            do_settings_sections('strive-content-calendar-settings');

            // add save button
            echo '<p class="submit">';
            submit_button(esc_html__('Save Settings', 'strive'), 'primary', 'save-settings', false, ['id' => 'save-calendar-settings']);
            echo '<span class="dashicons dashicons-update loading-icon"></span>';
            echo '</p>';

            // Output admin notices
            settings_errors();

            echo '</form>';
            echo '</div>';
            echo '<button id="close-settings" class="close-button"><span class="dashicons dashicons-no"></span></button>';
            echo '</div>';
            echo '</div>';
        }

        // Paragraph output at the top of the settings section
        public function page_description()
        {
            return;
            echo '<p class="description">' . esc_html__('Customize how Strive works on your site.', 'strive') . '</p>';
        }

        public function save_settings()
        {
            // Restrict to Admins
            if (!SCC()->permission_manager('admin')) {
                return;
            }

            // Make sure the post data and the nonce are set
            if (!isset($_POST['form_data']) || !isset($_POST['save_settings_nonce'])) {
                return;
            }

            // Verify the nonce
            check_ajax_referer('save_settings', 'save_settings_nonce');

            // Unslash the query
            $data = wp_unslash($_POST['form_data']);

            $form_values = [];

            // Unserialize the string into an array
            parse_str($data, $form_values);

            $changed_dow = $form_values['strive_starting_dow'] != get_option('strive_starting_dow') ? true : false;
            $changed_colorblind = $form_values['strive_color_blind_mode'] != get_option('strive_color_blind_mode') ? true : false;
            $changed_post_types = $form_values['strive_calendar_post_types'] != get_option('strive_calendar_post_types') ? true : false;

            foreach ($form_values as $name => $value) {
                update_option($name, $value);
            }

            // Have to manually check empty checkboxes...
            if (!array_key_exists('strive_block_incomplete_posts', $form_values)) {
                update_option('strive_block_incomplete_posts', '');
            }
            if (!array_key_exists('strive_color_blind_mode', $form_values)) {
                update_option('strive_color_blind_mode', '');
            }
            if (!array_key_exists('strive_show_categories', $form_values)) {
                update_option('strive_show_categories', '');
            }
            if (!array_key_exists('strive_show_tags', $form_values)) {
                update_option('strive_show_tags', '');
            }

            $response = [
                'changed_dow' => $changed_dow,
                'changed_colorblind' => $changed_colorblind,
                'changed_post_types' => $changed_post_types,
            ];

            if ($changed_dow) {
                $response['dow_labels'] = SCC()->calendar->dow_labels();
                $response['calendar_nav'] = SCC()->calendar->calendar_navigation();
            }
            if ($changed_colorblind) {
                $response['colorblind'] = $form_values['strive_color_blind_mode'] == 'on' ? true : false;
            }

            wp_send_json(json_encode($response));

            wp_die();
        }
    }
}
