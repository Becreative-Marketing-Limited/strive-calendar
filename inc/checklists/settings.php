<?php
if (!class_exists('SCC_Checklists_Settings')) {
    class SCC_Checklists_Settings
    {
        public function __construct()
        {
            add_action('admin_init', [$this, 'register_checklist_settings']);
            add_action('wp_ajax_save_checklist_settings', [$this, 'save_settings']);
        }

        public function register_checklist_settings()
        {
            // Create a section to output the settings in
            add_settings_section('checklist-settings-section', esc_html__('Checklists Settings', 'strive'), [$this, 'page_description'], 'strive-content-calendar-checklists');

            //=======================================
            // Default checklist
            //=======================================

            $args = [
                'type'              => 'string',
                'default'           => '',
                'sanitize_callback' => 'sanitize_text_field',
            ];

            // Register setting
            register_setting('checklist_settings', 'strive_default_checklist', $args);

            // Add a field for the checklist setting
            add_settings_field('strive_default_checklist', esc_html__('Default checklist', 'strive'), [$this, 'default_checklist_callback'], 'strive-content-calendar-checklists', 'checklist-settings-section', ['class' => 'default-checklist-row']);

            //=======================================
            // Post Types to Include
            //=======================================

            $args = [
                'type'              => 'array',
                'default'           => ['post'],
                'sanitize_callback' => [$this, 'sanitize_post_type'],
            ];

            // Register setting for the post types enabled in the Checklists
            register_setting('checklist_settings', 'strive_checklist_post_types', $args);

            // Add a field for the checklist setting
            add_settings_field('strive_checklist_post_types', esc_html__('Post types to enable checklists for', 'strive'), [$this, 'post_types_callback'], 'strive-content-calendar-checklists', 'checklist-settings-section', ['class' => 'post-type']);
        }

        public function default_checklist_callback()
        {
            $default = SCC()->strive_get_option('strive_default_checklist', '');
            // Default to false or a new checklist with a non-matching ID will be used. JS is used when there are no options.
            $checklists = SCC()->strive_get_option('strive_post_checklists', false); ?>
            <div class="default-checklist-container">
                <label class="strive-default-checklist">
                    <select id="strive_default_checklist" name="strive_default_checklist" value="<?php echo esc_attr($default); ?>">
                        <?php
                        if ($checklists) {
                            foreach ($checklists as $checklist) {
                                echo '<option value="' . esc_attr($checklist['id']) . '" ' . selected($default, $checklist['id'], false) . '>' . esc_html($checklist['title']) . '</option>';
                            }
                        } ?>
                    </select>
                </label>
            </div>
            <?php
        }

        // Callback for post types
        public function post_types_callback()
        {
            $saved = SCC()->strive_get_option('strive_checklist_post_types', ['post']);
            $post_types = SCC()->get_all_post_types();
            $any_disabled = false;

            foreach ($post_types as $post_type) {
                $selected = in_array($post_type, $saved) ? true : false;
                $label = get_post_type_object($post_type)->labels->name; ?>
                <label class="post-types-label" for="checklists-post-type-<?php echo esc_attr($post_type); ?>">
                    <?php if (post_type_supports($post_type, 'custom-fields')) : ?>
                        <input type="checkbox" name="strive_checklist_post_types[]" id="checklists-post-type-<?php echo esc_attr($post_type); ?>" <?php checked(true, $selected, true); ?> value="<?php esc_attr_e($post_type); ?>">
                    <?php else :
                        $any_disabled = true; ?>
                        <input disabled type="checkbox" name="strive_checklist_post_types[]" <?php checked(true, $selected, true); ?> value="<?php esc_attr_e($post_type); ?>">
                    <?php endif; ?>
                    <span><?php esc_html_e($label); ?></span>
                </label>
                <?php
            }
            if ($any_disabled) {
                echo '<p class="disabled-note">' . esc_html__('Only custom post types with custom field support can be enabled.', 'strive') . ' <a href="https://strivecalendar.com/knowledgebase/pipeline/adding-custom-field-support/" target="_blank">' . esc_html__('Learn More', 'strive') . '</a></p>';
            }
        }

        // Called in strive.php to output everything in Checklist tab
        public function output_checklist_settings()
        {
            echo '<div id="strive-settings-container" class="strive-settings-container">';
            echo '<div id="strive-settings" class="strive-settings">';
            echo '<div class="inner">';

            echo '<form method="post" action="options.php" id="strive-checklist-settings">';

            echo '<h2>' . esc_html__('Checklist Settings', 'strive') . '</h2>';

            // Output the fields required for the Settings API to save the setting
            settings_fields('checklist_settings');

            // Output the settings section with all the visual content from the callback
            echo '<table class="form-table">';
            do_settings_fields('strive-content-calendar-checklists', 'checklist-settings-section');
            echo '</table>';

            // add save button
            echo '<p class="submit">';
            submit_button(esc_html__('Save Settings', 'strive'), 'primary', 'save-settings', false, ['id' => 'save-checklists-settings']);
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

        public function save_settings()
        {
            // Restrict to Admins
            if (!SCC()->permission_manager('admin')) {
                return;
            }

            // Make sure the post data and the nonce are set
            if (!isset($_POST['form_data']) || !isset($_POST['save_checklist_settings_nonce'])) {
                return;
            }

            // Verify the nonce
            check_ajax_referer('save_checklist_settings', 'save_checklist_settings_nonce');

            // Unslash the query
            $data = wp_unslash($_POST['form_data']);

            $form_values = [];

            // Unserialize the string into an array
            parse_str($data, $form_values);

            foreach ($form_values as $name => $value) {
                update_option($name, $value);
            }

            // Empty settings get skipped instead of saving as empty
            if (!array_key_exists('strive_checklist_post_types', $form_values)) {
                update_option('strive_checklist_post_types', []);
            }

            wp_die();
        }
    }
}
