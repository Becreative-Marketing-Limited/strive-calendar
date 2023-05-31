<?php
if (!class_exists('SCC_Statuses')) {
    class SCC_Statuses
    {
        public function __construct()
        {
            add_action('add_meta_boxes', [$this, 'add_post_status_meta_box']);
            add_action('save_post', [$this, 'save_post_status_meta_box'], 10, 2);
        }

        // Add meta box for Classic Editor
        public function add_post_status_meta_box()
        {
            foreach (SCC()->post_types_with_custom_fields() as $post_type) {
                add_meta_box(
                    'scc_post_status',
                    __('Editorial Status', 'strive'),
                    [$this, 'post_status_meta_box_html'],
                    $post_type,
                    'side',
                    'core',
                    ['__back_compat_meta_box' => true]
                );
            }
        }

        // Output the HTML for the meta box
        public function post_status_meta_box_html($post)
        {
            $status = get_post_meta($post->ID, '_strive_editorial_status', true);
            wp_nonce_field('strive_post_status_meta_box', 'strive_post_status_meta_box_nonce'); ?>
            <p>
                <select name="strive_post_status_meta_box" id="strive_post_status_meta_box">
                    <?php
                    $statuses = SCC()->post_statuses();
            foreach ($statuses as $slug => $name) {
                echo '<option value="' . esc_attr($slug) . '" ' . selected($status, $slug) . '>' . esc_html($name) . '</option>';
            } ?>
                </select>
            </p>
            <?php
        }

        // Save the post status meta box
        public function save_post_status_meta_box($post_id, $post)
        {

            // Restrict to Contributors and above
            if (!SCC()->permission_manager('contributor')) {
                return;
            }

            // Make sure the post status and nonce are set
            if (!isset($_POST['strive_post_status_meta_box']) || !isset($_POST['strive_post_status_meta_box_nonce'])) {
                return;
            }

            // Verify nonce
            if (!wp_verify_nonce($_POST['strive_post_status_meta_box_nonce'], 'strive_post_status_meta_box')) {
                return;
            }

            // Make sure they can edit this post
            if (!SCC()->can_edit_this_post($post_id)) {
                return;
            }

            $status = wp_unslash($_POST['strive_post_status_meta_box']);

            update_post_meta($post_id, '_strive_editorial_status', sanitize_text_field($status));
        }
    }
}
