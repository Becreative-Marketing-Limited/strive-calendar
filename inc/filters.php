<?php
if (!class_exists('SCC_Filters')) {
    class SCC_Filters
    {
        public function __construct()
        {
            add_action('admin_init', [$this, 'register_filter_settings']);
            add_action('wp_ajax_save_filter', [$this, 'save_filter']);
            add_action('wp_ajax_delete_filter', [$this, 'delete_filter']);
        }

        public function build_filters()
        { ?>
            <div class="filters-container">
                <button id="toggle-filters" class="toggle-filters"><span class="dashicons dashicons-filter"></span> <?php esc_html_e('Filters', 'strive'); ?></button>
                <div id="filters" class="filters">
                    <div class="filter-heading">
                        <div class="top">
                            <label class="title"><?php esc_html_e('Filters', 'strive'); ?></label>
                            <?php if (SCC()->permission_manager('editor')) : ?>
                                <a id="show-save-filter" class="show-save-filter" href="#"><?php esc_html_e('Save new filter', 'strive'); ?></a>
                            <?php endif; ?>
                        </div>
                        <select id="select-filter" class="select-filter">
                            <option id="select-placeholder" value="placeholder"><?php esc_html_e('Create new filter', 'strive'); ?></option>
                            <?php
                                $saved_filters = SCC()->strive_get_option('strive_saved_filters', []);
                                foreach ($saved_filters as $filter) {
                                    $field_data = json_encode($filter['fields']);
                                    echo '<option data-id="' . esc_attr($filter['id']) . '" data-fields="' . esc_attr($field_data) . '">' . esc_html($filter['name']) . '</option>';
                                }
                            ?>
                        </select>
                    </div>
                    <div id="filter-options" class="filter-options">
                        <form id="filter-form" class="filter-form">
                            <input id="filter-id" type="hidden" name="id" value="">                        
                            <div class="category filter-option-container">
                                <label class="title"><?php esc_html_e('Category', 'strive'); ?></label>
                                <select id="filter-category" class="post-filter category filter-category" name="category[]" multiple="multiple" style="width:100%;">
                                    <?php
                                        $categories = get_categories(['hide_empty' => false]);
                                        foreach ($categories as $category) {
                                            echo '<option value="' . esc_attr($category->term_id) . '">' . esc_html($category->name) . '</option>';
                                        }
                                    ?>
                                </select>
                            </div>
                            <div class="tag filter-option-container">
                                <label class="title"><?php esc_html_e('Tag', 'strive'); ?></label>
                                <select id="filter-tag" class="post-filter tag filter-tag" name="tag[]" multiple="multiple" style="width:100%;">
                                    <?php
                                        $tags = get_tags(['hide_empty' => false]);
                                        foreach ($tags as $tag) {
                                            echo '<option value="' . esc_attr($tag->term_id) . '">' . esc_html($tag->name) . '</option>';
                                        }
                                    ?>
                                </select>
                            </div>
                            <div class="post-type filter-option-container">
                                <label class="title"><?php esc_html_e('Post Type', 'strive'); ?></label>
                                <select id="filter-post-type" class="post-filter post-type filter-post-type" name="post-type[]" multiple="multiple" style="width:100%;">
                                    <?php
                                        $post_types = SCC()->get_supported_post_types('strive_calendar_post_types');
                                        foreach ($post_types as $post_type) {
                                            $label = get_post_type_object($post_type)->labels->singular_name;
                                            echo '<option value="' . esc_attr($post_type) . '">' . esc_html($label) . '</option>';
                                        }
                                    ?>
                                </select>
                            </div>
                            <div class="filter-option-container">
                                <div class="title"><?php esc_html_e('Search Term', 'strive'); ?></div>
                                <input id="filter-search" class="post-filter search filter-search" type="search" name="search" placeholder="<?php echo esc_attr__('Search for...', 'strive'); ?>" value="" />
                            </div>
                            <div class="logic filter-option-container">
                                <div class="title">
                                    <span><?php esc_html_e('Logic', 'strive'); ?></span>
                                    <div class="tooltip-container">
                                        <span id="logic-icon" class="dashicons dashicons-info-outline logic-icon"></span>
                                        <div id="logic-exp" class="logic-exp tooltip">
                                            <p><?php _e('<b>Any:</b> show posts with ANY of the selected qualities.', 'strive'); ?></p>
                                            <p><?php _e('<b>All:</b> show posts with ALL of the selected qualities.', 'strive'); ?></p>
                                        </div>
                                    </div>
                                </div>
                                <label>
                                    <input id="logic-any" type="radio" name="logic" value="any" checked>
                                    <span><?php esc_html_e('Any', 'strive'); ?></span>
                                </label>
                                <label>
                                    <input id="logic-all" type="radio" name="logic" value="all">
                                    <span><?php esc_html_e('All', 'strive'); ?></span>
                                </label>
                            </div>
                            <div class="context filter-option-container">
                                <div class="title">
                                    <span><?php esc_html_e('Context', 'strive'); ?></span>
                                </div>
                                <label>
                                    <input id="context-calendar" type="radio" name="context" value="calendar" checked>
                                    <span><?php esc_html_e('Calendar', 'strive'); ?></span>
                                </label>
                                <label>
                                    <input id="context-sidebar" type="radio" name="context" value="sidebar">
                                    <span><?php esc_html_e('Sidebar', 'strive'); ?></span>
                                </label>
                                <label>
                                    <input id="context-both" type="radio" name="context" value="both">
                                    <span><?php esc_html_e('Calendar & Sidebar', 'strive'); ?></span>
                                </label>
                            </div>
                            <div class="visibility filter-option-container">
                                <div class="title">
                                    <span><?php esc_html_e('Visibility of filtered posts', 'strive'); ?></span>
                                </div>
                                <label>
                                    <input id="visibility-fade" type="radio" name="visibility" value="fade" checked>
                                    <span><?php esc_html_e('Fade Out', 'strive'); ?></span>
                                </label>
                                <label>
                                    <input id="visibility-remove" type="radio" name="visibility" value="remove">
                                    <span><?php esc_html_e('Remove', 'strive'); ?></span>
                                </label>
                            </div>
                        </form>
                        <div class="buttons">
                            <button id="reset-filter" class="reset-filter"><?php esc_html_e('Reset', 'strive'); ?></button>
                            <?php if (SCC()->permission_manager('editor')) : ?>
                                <div class="delete-button-container">
                                    <button id="delete-filter" class="delete-filter"><?php esc_html_e('Delete', 'strive'); ?></button>
                                    <span class="dashicons dashicons-update loading-icon"></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if (SCC()->permission_manager('editor')) : ?>
                        <div id="save-form" class="save-form">
                            <div class="title"><?php esc_html_e('Save filter', 'strive'); ?></div>
                            <input id="filter-name" class="filter-name" type="text" name="name" placeholder="<?php esc_html_e('Filter name', 'strive'); ?>">
                            <div class="save-button-container">
                                <button id="save-filter" class="save-filter button button-primary"><?php esc_html_e('Save Filter', 'strive'); ?></button>
                                <span class="dashicons dashicons-update loading-icon"></span>
                            </div>
                            <button id="cancel-save-filter" class="cancel-save-filter button button-secondary"><?php esc_html_e('Cancel', 'strive'); ?></button>
                        </div>
                    <?php endif; ?>
                </div>
            </div><?php
        }

        // Add setting to save filters
        public function register_filter_settings()
        {
            // Create a section to output the settings in
            add_settings_section('strive-filters-settings-section', esc_html__('Filter Settings', 'strive'), [$this, 'page_description'], 'strive-filter-settings');

            //=======================================
            // Saved filters
            //=======================================

            $args = [
                'type'              => 'array',
                'default'           => [],
                'sanitize_callback' => [$this, 'sanitize_filters'],
            ];

            register_setting('strive_filter_settings', 'strive_saved_filters', $args);
            add_settings_field('strive_saved_filters', esc_html__('Saved filters', 'strive'), [$this, 'saved_filters_callback'], 'strive-filter-settings', 'strive-filters-settings-section', ['class' => 'saved-filters']);
        }

        public function saved_filters_callback()
        {
            /*
             * Silent callback because I'm only saving via Ajax and not outputting this field in a traditional way
             */
        }

        public function sanitize_filters($filters)
        {
            if (!is_array($filters)) {
                return;
            }

            $category_ids = [];
            foreach (get_terms(['taxonomy'=> 'category', 'hide_empty'=>false]) as $category) {
                $category_ids[] = $category->term_id;
            }
            $tag_ids = [];
            foreach (get_terms(['taxonomy'=> 'post_tag', 'hide_empty'=>false]) as $tag) {
                $tag_ids[] = $tag->term_id;
            }
            $post_types = get_post_types();
            $logic_values = ['any', 'all'];
            $context_values = ['calendar', 'sidebar', 'both'];
            $visibility_values = ['fade', 'remove'];

            $filter['id'] = sanitize_text_field($filter['id']);
            $filter['name'] = sanitize_text_field($filter['name']);

            // Make sure each category exists and set it to an empty string if not
            if ($filter['fields']['category'] == null) {
                $filter['fields']['category'] = '';
            } else {
                foreach ($filter['fields']['category'] as &$category) {
                    if (!in_array($category, $category_ids)) {
                        $category = '';
                    }
                }
            }
            // Same for tags
            if ($filter['fields']['tag'] == null) {
                $filter['fields']['tag'] = '';
            } else {
                foreach ($filter['fields']['tag'] as &$tag) {
                    if (!in_array($tag, $tag_ids)) {
                        $tag = '';
                    }
                }
            }
            // And for post types
            if ($filter['fields']['post_type'] == null) {
                $filter['fields']['post_type'] = '';
            } else {
                foreach ($filter['fields']['post_type'] as &$post_type) {
                    if (!in_array($post_type, $post_types)) {
                        $post_type = '';
                    }
                }
            }

            $filter['fields']['search'] = sanitize_text_field($filter['fields']['search']);

            if (!in_array($filter['fields']['logic'], $logic_values)) {
                $filter['fields']['logic'] = 'any';
            }
            if (!in_array($filter['fields']['context'], $context_values)) {
                $filter['fields']['context'] = 'calendar';
            }
            if (!in_array($filter['fields']['visibility'], $visibility_values)) {
                $filter['fields']['visibility'] = 'fade';
            }

            return $filters;
        }

        public function save_filter()
        {
            if (!SCC()->permission_manager('editor')) {
                return;
            }

            // Make sure the date and the nonce are set
            if (!isset($_POST['filter']) || !isset($_POST['save_filter_nonce'])) {
                return;
            }

            // Verify the nonce
            check_ajax_referer('save_filter', 'save_filter_nonce');

            $data = wp_unslash($_POST['filter']);

            $filter_data = [];

            // Unserialize the string into an array
            parse_str($data, $filter_data);

            // Get their current filters
            $user_filters = SCC()->strive_get_option('strive_saved_filters', []);

            $new = false;

            // Create an ID if it's a new form
            if ($filter_data['id'] == '') {
                $filter_data['id'] = uniqid();
                $new = true;
            }

            $to_save = [
                'id' => $filter_data['id'],
                'name' => $filter_data['name'],
                'fields' => [
                    'category' => $filter_data['category'],
                    'tag' => $filter_data['tag'],
                    'post_type' => $filter_data['post-type'],
                    'search' => $filter_data['search'],
                    'logic' => $filter_data['logic'],
                    'context' => $filter_data['context'],
                    'visibility' => $filter_data['visibility'],
                ],
            ];

            if ($new) {
                // Add the new filter to their list
                $user_filters[] = $to_save;
            } else {
                // Update the existing filter
                foreach ($user_filters as &$filter) {
                    if ($filter['id'] == $filter_data['id']) {
                        $filter = $to_save;
                        break;
                    }
                }
            }

            update_option('strive_saved_filters', $user_filters);

            if ($new) {
                $response = [
                    'new' => true,
                    'name' => null, // don't need it
                    'data' => '<option data-id="' . esc_attr($to_save['id']) . '" data-fields="' . esc_attr(json_encode($to_save['fields'])) . '">' . esc_html($to_save['name']) . '</option>',
                ];
            } else {
                $response = [
                    'new' => false,
                    'name' => $to_save['name'],
                    'data' => $to_save['fields'],
                ];
            }

            wp_send_json(json_encode($response));

            wp_die();
        }

        public function delete_filter()
        {
            if (!SCC()->permission_manager('editor')) {
                return;
            }
            if (!isset($_POST['id']) || !isset($_POST['delete_filter_nonce'])) {
                return;
            }
            check_ajax_referer('delete_filter', 'delete_filter_nonce');

            $to_delete = wp_unslash($_POST['id']);

            $user_filters = SCC()->strive_get_option('strive_saved_filters', []);

            $key = array_search($to_delete, array_column($user_filters, 'id'));

            unset($user_filters[$key]);

            $user_filters = array_values($user_filters);

            update_option('strive_saved_filters', $user_filters);

            wp_die();
        }
    }
}
