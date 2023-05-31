<?php
if (!class_exists('SCC_Checklists')) {
    class SCC_Checklists
    {
        public function __construct()
        {
            add_action('admin_init', [$this, 'register_checklists']);
            add_action('add_meta_boxes', [$this, 'add_checklists_meta_box']);
            add_action('save_post', [$this, 'save_checklists_meta_box'], 10, 2);
            add_action('plugins_loaded', [$this, 'upgrade_checklist_data_structure'], 10);
            add_action('wp_ajax_get_the_checklist', [$this, 'get_the_checklist']);
            add_action('wp_ajax_export_checklist', [$this, 'export_checklist']);
            add_action('wp_ajax_import_checklist', [$this, 'import_checklist']);
        }

        public function build_checklists() { ?>
            <div class="heading">
                <div class="heading-title">
                    <span class="title"><?php esc_html_e('Checklists', 'strive'); ?></span>
                    <span class="sub-title">
                        <?php esc_html_e('Create and edit your checklists', 'strive'); ?>
                    </span>
                </div>
                <?php
                if (SCC()->permission_manager('admin')) : ?>
                    <div class="settings">
                        <a id="settings-button" class="settings-button" href="#"><img class="settings-icon" src="<?php echo STRIVE_CC_URL . 'img/settings.svg' ?>" /> <?php esc_html_e('Settings', 'strive'); ?></a>
                    </div>
                <?php endif; ?>
            </div><?php
            self::output_checklists();
        }

        public function register_checklists()
        {
            $args = [
                'type'    => 'array',
                'default' => self::default_checklists(esc_html__('New Checklist', 'strive')),
                'sanitize_callback' => [$this, 'sanitize_checklists'],
            ];
            register_setting('checklists', 'strive_post_checklists', $args);
            add_settings_section('checklist-section', esc_html__('Post Checklists', 'strive'), [$this, 'page_description'], 'strive-content-calendar-checklists');
            add_settings_field('strive_post_checklists', '', [$this, 'checklist_callback'], 'strive-content-calendar-checklists', 'checklist-section', false);
        }

        public function checklist_callback(bool $new)
        {
            $checklists = SCC()->strive_get_option('strive_post_checklists', self::default_checklists(__('Primary', 'strive'))); ?>
            <div>
                <div id="checklist-tabs" class="checklist-tabs"><?php
                    for ($i = 0; $i < count($checklists); $i++) {
                        $classes = 'tab';
                        if ($i == 0) {
                            $classes .= ' current';
                        }
                        echo '<a class="' . esc_attr($classes) . '" href="#" data-id="' . esc_attr($checklists[$i]['id']) . '">' . esc_attr($checklists[$i]['title']) . '</a>';
                    } ?>
                </div>
                <button id="create-checklist" class="create-checklist"><?php esc_html_e('Create Checklist', 'strive'); ?></button>
            </div>
            <div id="checklists" class="checklists">
                <?php echo self::output_checklist_html($checklists, false, 0); ?>
            </div>
            <div id="loading" class="loading">
                <img src="<?php echo trailingslashit(STRIVE_CC_URL) . 'img/loading.svg'; ?>" />
            </div><?php
        }

        public function output_checklist_html($checklists, $new, $starting_index)
        {
            $count = 0;
            $html = '';
            foreach ($checklists as $checklist_key => $checklist) {
                $index = $checklist_key;
                if ($new) {
                    $index = $starting_index;
                }
                $container_classes = 'checklist-container';
                if ($new || (!$new && $index == 0)) {
                    $container_classes .= ' current';
                }
                $html .= '<div class="' . esc_attr($container_classes) . '" data-id="' . esc_attr($checklist['id']) . '" data-index="' . esc_attr($index) . '">';
                $html .= self::checklist_toolbar($checklist, $index);
                $html .= '<div class="section-container">';
                foreach ($checklist['checklist'] as $status => $tasks) {
                    $html .= '<div class="section ' . esc_attr($status) . '">';
                    $html .= '<div class="quarter-circle"></div>';
                    $html .= '<div class="section-title"><span class="circle">&#9679;</span> <span class="title-text">' . str_replace('-', ' ', ucfirst(esc_attr($status))) . '</span></div>';
                    $html .= '<div class="task-container" data-status="' . esc_attr($status) . '" data-checklist="' . esc_attr($index) . '">';

                    if (count($tasks) == 0) {
                        $html .= self::task($index, $status, 0, '');
                    } else {
                        foreach ($tasks as $key => $taskString) {
                            $html .= self::task($index, $status, $key, $taskString);
                        }
                    }
                    $html .= '</div>';
                    $html .= "<button class='add-item button button-secondary' data-status='" . esc_attr($status) . "' data-checklist='" . esc_attr($index) . "'>Add Task</button>";
                    $html .= '</div>';
                }
                $html .= '</div>';
                $html .= '</div>';

                $count++;
            }

            return $html;
        }

        public function checklist_toolbar($checklist, $index)
        {
            ob_start(); ?>
            <div class="checklist-title-container">
                <input type="text" class="checklist-title" id="strive_post_checklists[<?php echo esc_attr($index); ?>][title]" name="strive_post_checklists[<?php echo esc_attr($index); ?>][title]" value="<?php echo esc_attr($checklist['title']); ?>" />
                <span class="dashicons dashicons-edit"></span>
            </div>
            <div class="delete-button-container">
                <button class="delete-button button-secondary"><span class="dashicons dashicons-trash"></span><?php esc_html_e('Delete', 'strive'); ?></button>
            </div>
            <div class="import-export">
                <div class="export">
                    <button class="export-button button-secondary"><span class="dashicons dashicons-download"></span><?php esc_html_e('Export', 'strive'); ?></button>
                </div>
                <div class="import">
                    <button class="import-button button-secondary"><span class="dashicons dashicons-upload"></span><?php esc_html_e('Import', 'strive'); ?></button>
                    <input class="import-file" type="file" style="display:none;" />
                </div>
            </div>
            <input type="hidden" class="checklist-id" id="strive_post_checklists[<?php echo esc_attr($index); ?>][id]" name="strive_post_checklists[<?php echo esc_attr($index); ?>][id]" value="<?php echo esc_attr($checklist['id']); ?>" /><?php
            $content = ob_get_contents();
            ob_end_clean();

            return $content;
        }

        public function task($index, $status, $key, $taskString)
        {
            $instructions = '';
            $id = '';
            // Tasks saved as strings like this: "Add a Featured Image~jf89j2fz"
            if ($taskString != '') {
                $task_array = explode('~', $taskString);
                $instructions = $task_array[0];
                $id = $task_array[1];
            } else {
                $id = uniqid();
            }
            ob_start(); ?>
            <div class="task" data-key="<?php echo esc_attr($key); ?>">
                <input type="text" class="instructions-input" name="task-instructions" value="<?php echo esc_attr($instructions); ?>" />
                <input type="hidden" class="id-input" name="task-id" value="<?php echo esc_attr($id); ?>" />
                <input type="hidden" class="save-input" 
                    id="strive_post_checklists[<?php echo esc_attr($index); ?>][checklist][<?php echo esc_attr($status); ?>][<?php echo esc_attr($key); ?>]" 
                    name="strive_post_checklists[<?php echo esc_attr($index); ?>][checklist][<?php echo esc_attr($status); ?>][<?php echo esc_attr($key); ?>]" 
                    value="<?php echo esc_attr($taskString); ?>" />
                <a class="remove-item"><span class="dashicons dashicons-dismiss"></span></a>
                <img class="drag-handle" src="<?php echo STRIVE_CC_URL . 'img/drag-handle.svg'; ?>" />
            </div><?php
            $content = ob_get_contents();
            ob_end_clean();

            return $content;
        }

        // Called in strive.php to output everything in Checklist tab
        public function output_checklists()
        {

            // Open the form. This is a standard WP practice and WP closes out the form itself
            echo '<div id="strive-post-checklists" class="strive-post-checklists">';
            echo '<form method="post" action="options.php">';

            // Output the fields required for the Settings API to save the setting
            settings_fields('checklists');

            // Output the settings section with all the visual content from the callback
            echo '<table class="form-table">';
            do_settings_fields('strive-content-calendar-checklists', 'checklist-section');
            echo '</table>';

            // add save button
            submit_button(esc_html__('Save All Checklists', 'strive'), 'primary', 'save-checklists');

            // Output admin notices
            settings_errors();

            echo '</form>';
            echo '</div>';
        }

        // Paragraph output at the top of the settings section
        public function page_description()
        {
            return;
            echo '<p class="description">' . esc_html__('Create a checklist for each editorial status. Tasks added here show up immediately in the editor sidebar for all posts.', 'strive') . '</p>';
        }

        // Sanitize the checklist setting
        public function sanitize_checklists($checklists)
        {
            foreach ($checklists as &$checklist) {
                // Sanitize checklist name
                $checklist['title'] = sanitize_text_field($checklist['title']);
                $checklist['id'] = sanitize_text_field($checklist['id']);
                // Loop through all status arrays
                foreach ($checklist['checklist'] as $status => &$tasks) {
                    // Reset indexes to respect DnD reordering
                    $tasks = array_values($tasks);
                    foreach ($tasks as $key => &$task) {
                        // Sanitize the title~ID
                        $task = sanitize_text_field($task);
                        if ($task == '') {
                            // Remove empty tasks
                            array_splice($checklist['checklist'][$status], $key, 1);
                        }
                    }
                }
                // If a status has no tasks, WordPress will completely delete the status array due to the lack of fields being saved
                // This is guarantees all 4 statuses are present and don't get removed
                $checklist['checklist'] = [
                    'not-started' => $checklist['checklist']['not-started'] == null ? [] : $checklist['checklist']['not-started'],
                    'writing'     => $checklist['checklist']['writing'] == null ? [] : $checklist['checklist']['writing'],
                    'editing'     => $checklist['checklist']['editing'] == null ? [] : $checklist['checklist']['editing'],
                    'complete'    => $checklist['checklist']['complete'] == null ? [] : $checklist['checklist']['complete'],
                ];
            }

            // Reset keys (because checklists can be deleted creating unpredictable keys)
            $checklists = array_values($checklists);

            return $checklists;
        }

        //=============================================================
        // Classic Editor support
        //=============================================================

        // Add meta box for Classic Editor
        public function add_checklists_meta_box()
        {
            $post_types = SCC()->get_supported_post_types('strive_checklist_post_types');
            foreach ($post_types as $post_type) {
                add_meta_box(
                    'scc_post_checklists',
                    __('Post Checklist', 'strive'),
                    [$this, 'post_checklists_meta_box_html'],
                    $post_type,
                    'side',
                    'core',
                    ['__back_compat_meta_box' => true]
                );
            }
        }

        public function post_checklists_meta_box_html($post)
        {
            $saved_checklists = SCC()->strive_get_option('strive_post_checklists', false);
            $url = add_query_arg([
                'page' => 'strive-content-calendar',
                'tab' => 'checklists',
            ], admin_url('edit.php'));

            if (!$saved_checklists) {
                echo '<p>' . esc_html__('You haven\'t created any checklists with Strive yet. Checklists can help you document and systemize your writing process.', 'strive') . '</p>';
                echo '<p><a href="' . esc_url($url) . '" class="button-primary create-checklist" target="_blank">' . esc_html__('Create a Checklist', 'strive') . '</a></p>';

                return;
            }

            // Get the checked items for the post (saved as a string using JSON.stringify())
            // Ex. "['cv6whdye9e80', 'gxt6nsqs34w0']"
            $checked_tasks = get_post_meta($post->ID, '_strive_checklists', true);

            wp_nonce_field('strive_post_checklist_meta_box', 'strive_post_checklist_meta_box_nonce');

            echo '<input type="hidden" id="strive_post_checklist_meta_box" name="strive_post_checklist_meta_box" value="' . esc_attr($checked_tasks) . '" />';

            // Convert into array (contains IDs of checked tasks)
            if ($checked_tasks == '' || $checked_tasks == '""' || $checked_tasks == null) {
                $checked_tasks = [];
            } else {
                $checked_tasks = json_decode($checked_tasks);
            }

            $checklist_id = get_post_meta($post->ID, '_strive_active_checklist', true);
            $deleted = true;
            foreach ($saved_checklists as $checklist) {
                if ($checklist_id == $checklist['id']) {
                    $deleted = false;
                    break;
                }
            }
            if ($deleted) {
                $checklist_id = '';
            }
            // Set to first checklist if no meta and there are any checked tasks
            if ($checklist_id == '' && !empty($checked_tasks)) {
                $checklist_id = $saved_checklists[0]['id'];
            }
            // Otherwise, fall back to global default
            if ($checklist_id == '') {
                $checklist_id = SCC()->strive_get_option('strive_default_checklist', '');
            }
            // Or use first checklist if that's not set either
            if ($checklist_id == '') {
                $checklist_id = $saved_checklists[0]['id'];
            }

            echo '<div id="checklist-select-classic" class="checklist-select-classic">';
            echo '<select id="strive_default_checklist_meta_box" name="strive_default_checklist_meta_box">';
            if ($saved_checklists) {
                foreach ($saved_checklists as $checklist) {
                    echo '<option value="' . esc_attr($checklist['id']) . '" ' . selected($checklist_id, $checklist['id'], false) . '>' . esc_html($checklist['title']) . '</option>';
                }
            }
            echo '</select>';
            echo '</div>';

            // Output checkboxes
            foreach ($saved_checklists as $entry) {
                $checklist = $entry['checklist'];
                foreach ($checklist as $status => $tasks) {
                    $classes = 'checklist-section';
                    if ($entry['id'] == $checklist_id) {
                        $classes .= ' show';
                    }
                    echo '<div class="' . esc_attr($classes) . '" data-id="' . $entry['id'] . '">';
                    echo '<p class="section-title">' . str_replace('-', ' ', ucfirst(esc_html($status))) . '</p>';
                    if (!empty($tasks)) {
                        foreach ($tasks as $task) {
                            $task_data = explode('~', $task);
                            $checked = in_array($task_data[1], $checked_tasks);
                            echo '<p>';
                            echo '<input class="checklist-checkbox" type="checkbox" id="' . esc_attr($task_data[1]) . '" name="' . esc_attr($task_data[1]) . '" ' . checked($checked, true, false) . ' />';
                            echo '<label for="' . esc_attr($task_data[1]) . '">' . $task_data[0] . '</label>';
                            echo '</p>';
                        }
                    }
                    echo '</div>';
                }
            }
        }

        public function default_checklists($title)
        {
            $default = [
                [
                    'title' => $title,
                    'id'    => uniqid(),
                    'checklist' =>  [
                        'not-started' => [],
                        'writing'     => [],
                        'editing'     => [],
                        'complete'    => [],
                    ],
                ],
            ];

            return $default;
        }

        // Save the post status meta box
        public function save_checklists_meta_box($post_id, $post)
        {
            // Restrict to Contributors and above
            if (!SCC()->permission_manager('contributor')) {
                return;
            }

            // Make sure the post checklist and nonce are set
            if (!isset($_POST['strive_post_checklist_meta_box']) || !isset($_POST['strive_post_checklist_meta_box_nonce'])) {
                return;
            }

            // Verify nonce
            if (!wp_verify_nonce($_POST['strive_post_checklist_meta_box_nonce'], 'strive_post_checklist_meta_box')) {
                return;
            }

            // Make sure they can edit this post
            if (!SCC()->can_edit_this_post($post_id)) {
                return;
            }

            $checklists = wp_unslash($_POST['strive_post_checklist_meta_box']);
            $default_checklist = wp_unslash($_POST['strive_default_checklist_meta_box']);

            update_post_meta($post_id, '_strive_checklists', sanitize_text_field($checklists));
            update_post_meta($post_id, '_strive_active_checklist', sanitize_text_field($default_checklist));
        }

        // Update the checklist data structure to allow for multiple checklists
        public function upgrade_checklist_data_structure()
        {
            // If no value saved in DB or less than the version this update was released
            if (get_option('strive_db_version', 0) < 1.17) {
                $checklist = get_option('strive_post_checklists');

                // Make sure they have saved a checklist or it doesn't need to run
                if (!empty($checklist)) {

                    // Make sure they're still using the old format (extra precaution)
                    if (array_key_exists('not-started', $checklist)) {
                        $checklist_id = uniqid();
                        $new_format = [
                            [
                                'title' => esc_html('Primary Checklist', 'strive'),
                                'id'    => $checklist_id,
                                'checklist' => $checklist,
                            ],
                        ];
                        // Save new format
                        update_option('strive_post_checklists', $new_format);
                    }
                }
            }
        }

        // AJAX - get new checklist
        public function get_the_checklist()
        {
            // Restrict to Admins
            if (!SCC()->permission_manager('admin')) {
                return;
            }

            // Make sure the nonce and index are set
            if (!isset($_POST['get_the_checklist_nonce']) || !isset($_POST['index'])) {
                return;
            }

            // Verify the nonce
            check_ajax_referer('get_the_checklist', 'get_the_checklist_nonce');

            // Using an array containing one checklist because that's the format output_checklist_html() uses
            $empty_checklist = self::default_checklists(esc_html__('New Checklist', 'strive'));

            $id = $empty_checklist[0]['id'];
            $index = absint($_POST['index']);

            $response = [
                'tab'       => '<a class="tab current" href="#" data-id="' . esc_attr($id) . '">' . esc_html__('New Checklist', 'strive') . '</a>',
                'checklist' => self::output_checklist_html($empty_checklist, true, $index),
            ];

            echo json_encode($response);

            wp_die();
        }

        // Export the checklist
        public function export_checklist()
        {
            // Restrict to Admins
            if (!SCC()->permission_manager('admin')) {
                return;
            }

            // Make sure the nonce and index are set
            if (!isset($_POST['export_checklist_nonce']) || !isset($_POST['index'])) {
                return;
            }

            // Verify the nonce
            check_ajax_referer('export_checklist', 'export_checklist_nonce');

            // Clean the index
            $index = absint($_POST['index']);

            // Load the checklists
            $checklists = SCC()->strive_get_option('strive_post_checklists', false);

            if ($checklists) {

                // Make sure the exported checklist exists
                if (array_key_exists($index, $checklists)) {

                    // Get the tasks from the checklist
                    $the_checklist = $checklists[$index]['checklist'];

                    // Return the checklist tasks in JSON format
                    echo json_encode($the_checklist);
                } else {
                    wp_send_json_error(esc_html__('Please save this checklist first before exporting.', 'strive'));
                }
            } else {
                wp_send_json_error(esc_html__('Please save this checklist first before exporting.', 'strive'));
            }

            wp_die();
        }

        // Import the checklist tasks
        public function import_checklist()
        {

            // Restrict to Admins
            if (!SCC()->permission_manager('admin')) {
                return;
            }

            // Make sure the nonce and all values are set
            if (!isset($_POST['import_checklist_nonce']) || !isset($_POST['index']) || !isset($_POST['checklist_id'])
            || !isset($_POST['checklist_title']) || !isset($_POST['checklist_tasks'])) {
                return;
            }

            // Verify the nonce
            check_ajax_referer('import_checklist', 'import_checklist_nonce');

            // Clean the index
            $index = absint($_POST['index']);
            // Clean the ID
            $id = sanitize_text_field($_POST['checklist_id']);
            // Clean the title
            $title = sanitize_text_field($_POST['checklist_title']);
            // Remove slashes added by WP escaping quotes
            $tasks = wp_unslash($_POST['checklist_tasks']);
            // Decode as an associative array
            $tasks = json_decode($tasks, true);

            if (is_array($tasks)) {
                // Make sure it's formatted correcty
                if (count($tasks) != 4 || !array_key_exists('not-started', $tasks) || !array_key_exists('writing', $tasks) || !array_key_exists('editing', $tasks) || !array_key_exists('complete', $tasks)) {
                    wp_send_json_error(esc_html__('Import failed due to incorrect formatting. Please try exporting your checklist again and importing the new file.', 'strive'));
                } else {
                    $checklists = [
                        [
                            'title' => $title,
                            'id' => $id,
                            'checklist' => $tasks,
                        ],
                    ];
                    // Return to JS the checklist container html with all tasks
                    echo self::output_checklist_html($checklists, true, $index);
                }
            } else {
                wp_send_json_error(esc_html__('Import failed due to incorrect formatting. Please try exporting your checklist again and importing the new file.', 'strive'));
            }
            wp_die();
        }
    }
}
