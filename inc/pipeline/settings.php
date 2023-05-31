<?php
if (!class_exists('SCC_Pipeline_Settings')) {
    class SCC_Pipeline_Settings
    {
        public function __construct()
        {
            add_action('admin_init', [$this, 'register_pipeline_settings']);
            add_action('wp_ajax_save_pipeline_settings', [$this, 'save_settings']);
        }

        // Add the Pipeline settings
        public function register_pipeline_settings()
        {
            // Create a section to output the settings in
            add_settings_section('strive-pipeline-settings-section', esc_html__('Pipeline Settings', 'strive'), [$this, 'page_description'], 'strive-pipeline-settings');

            //=======================================
            // Post Types to Include
            //=======================================

            $args = [
                'type'              => 'array',
                'default'           => ['post'],
                'sanitize_callback' => [$this, 'sanitize_post_type'],
            ];

            register_setting('strive_pipeline_settings', 'strive_pipeline_post_types', $args);
            add_settings_field('strive_pipeline_post_types', esc_html__('Post types to display in the Pipeline', 'strive'), [$this, 'post_types_callback'], 'strive-pipeline-settings', 'strive-pipeline-settings-section', ['class' => 'post-type']);

            //=======================================
            // Preferred layout
            //=======================================

            // Args for # of calendar weeks setting
            $args = [
                'type'    => 'string',
                'default' => 'columns',
                'sanitize_callback' => 'sanitize_title',
            ];

            // Register setting for the post types enabled in the Pipeline
            register_setting('strive_pipeline_settings', 'strive_pipeline_layout', $args);

            // Add a field for the checklist setting
            add_settings_field('strive_pipeline_layout', esc_html__('Default layout', 'strive'), [$this, 'layout_callback'], 'strive-pipeline-settings', 'strive-pipeline-settings-section', ['class' => 'layout']);
        }

        // Callback for post types
        public function post_types_callback()
        {
            $saved = SCC()->strive_get_option('strive_pipeline_post_types', ['post']);
            $post_types = SCC()->get_all_post_types();
            $any_disabled = false;

            foreach ($post_types as $post_type) {
                $selected = in_array($post_type, $saved) ? true : false;
                $label = get_post_type_object($post_type)->labels->name; ?>
                <label class="post-types-label" for="pipeline-post-type-<?php echo esc_attr($post_type); ?>">
                    <?php if (post_type_supports($post_type, 'custom-fields')) : ?>
                        <input type="checkbox" name="strive_pipeline_post_types[]" id="pipeline-post-type-<?php echo esc_attr($post_type); ?>" <?php checked(true, $selected, true); ?> value="<?php esc_attr_e($post_type); ?>">
                    <?php else :
                        $any_disabled = true; ?>
                        <input disabled type="checkbox" name="strive_pipeline_post_types[]" <?php checked(true, $selected, true); ?> value="<?php esc_attr_e($post_type); ?>">
                    <?php endif; ?>
                    <span><?php esc_html_e($label); ?></span>
                </label>
                <?php
            }
            if ($any_disabled) {
                echo '<p class="disabled-note">' . esc_html__('Only custom post types with custom field support can be enabled.', 'strive') . ' <a href="https://strivecalendar.com/knowledgebase/pipeline/adding-custom-field-support/" target="_blank">' . esc_html__('Learn More', 'strive') . '</a></p>';
            }
        }

        // Callback for layout
        public function layout_callback()
        { ?>
            <label class="pipeline-layout-label">
                <input type="radio" name="strive_pipeline_layout" value="columns" <?php checked('columns', get_option('strive_pipeline_layout'), true); ?>>
                <span><?php esc_html_e('Columns', 'strive'); ?></span>
            </label>
            <label class="pipeline-layout-label">
                <input type="radio" name="strive_pipeline_layout" value="rows" <?php checked('rows', get_option('strive_pipeline_layout'), true); ?>>
                <span><?php esc_html_e('Rows', 'strive'); ?></span>
            </label>
            <?php
        }

        // Called in strive.php to output everything
        public function output_settings_fields()
        {

            // Open the form. This is a standard WP practice and WP closes out the form itself
            echo '<div id="strive-settings-container" class="strive-settings-container">';
            echo '<div id="strive-settings" class="strive-settings">';
            echo '<div class="inner">';

            echo '<form method="post" action="options.php" id="strive-pipeline-settings">';

            // Output the fields required for the Settings API to save the setting
            settings_fields('strive_pipeline_settings');

            // Output the settings section with all the visual content from the callback
            do_settings_sections('strive-pipeline-settings');

            // add save button
            echo '<p class="submit">';
            submit_button(esc_html__('Save Settings', 'strive'), 'primary', 'save-settings', false, ['id' => 'save-pipeline-settings']);
            echo '<span class="dashicons dashicons-update loading-icon"></span>';
            echo '</p>';

            // Output admin notices
            settings_errors();

            echo '</div>';
            echo '<button id="close-settings" class="close-button"><span class="dashicons dashicons-no"></span></button>';
            echo '</div>';
            echo '</div>';
        }

        // Paragraph output at the top of the settings section
        public function page_description()
        {
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
            if (!SCC()->permission_manager('admin')) {
                return;
            }

            if (!isset($_POST['form_data']) || !isset($_POST['save_pipeline_settings_nonce'])) {
                return;
            }

            check_ajax_referer('save_pipeline_settings', 'save_pipeline_settings_nonce');

            $data = wp_unslash($_POST['form_data']);

            $form_values = [];

            parse_str($data, $form_values);

            $changed_post_type = get_option('strive_pipeline_post_types') == $form_values['strive_pipeline_post_types'] ? false : true;

            foreach ($form_values as $name => $value) {
                update_option($name, $value);
            }

            $response = [
                'layout' => $form_values['strive_pipeline_layout'],
                'post_types' => $changed_post_type,
            ];

            wp_send_json(json_encode($response));

            wp_die();
        }
    }
}
