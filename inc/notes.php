<?php
if (!class_exists('SCC_Notes')) {
    class SCC_Notes
    {
        public function __construct()
        {
            add_action('add_meta_boxes', [$this, 'add_post_notes_meta_box']);
            add_action('save_post', [$this, 'save_post_notes_meta_box'], 10, 2);
        }

        // Add the post meta box. This one is for both the Classic Editor & Gutenberg
        public function add_post_notes_meta_box()
        {
            add_meta_box(
                'scc_post_notes',
                __('Notes', 'strive'),
                [$this, 'post_notes_meta_box_html'],
                'post',
                'side',
                'default',
                ['__back_compat_meta_box' => true]
            );
        }

        // Output the HTML for the meta box
        public function post_notes_meta_box_html($post)
        {
            $notes = get_post_meta($post->ID, '_strive_post_notes', true);
            wp_nonce_field('strive_post_notes_meta_box', 'strive_post_notes_meta_box_nonce'); ?>
            <p>
                <textarea id="strive_post_notes_meta_box" name="strive_post_notes_meta_box" rows="4" class="large-text"><?php echo esc_html($notes); ?></textarea>
            </p>
            <?php
        }

        // Save the post notes meta box
        public function save_post_notes_meta_box($post_id, $post)
        {

            // Restrict to Contributors and above
            if (!SCC()->permission_manager('contributor')) {
                return;
            }

            // Make sure the post status and nonce are set
            if (!isset($_POST['strive_post_notes_meta_box']) || !isset($_POST['strive_post_notes_meta_box_nonce'])) {
                return;
            }

            // Verify nonce
            if (!wp_verify_nonce($_POST['strive_post_notes_meta_box_nonce'], 'strive_post_notes_meta_box')) {
                return;
            }

            // Make sure they can edit this post
            if (!SCC()->can_edit_this_post($post_id)) {
                return;
            }

            $notes = wp_unslash($_POST['strive_post_notes_meta_box']);

            update_post_meta($post_id, '_strive_post_notes', sanitize_textarea_field($notes));
        }
    }
}
