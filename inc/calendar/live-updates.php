<?php

if (!class_exists('SCC_Live_Updates')) {
    class SCC_Live_Updates
    {
        public function __construct()
        {
            add_action('post_updated', [$this, 'add_to_updated_list']);
            add_action('wp_ajax_live_update_calendar', [$this, 'live_update_calendar']);
            add_action('wp_ajax_live_updates', [$this, 'save_live_update_preference']);
        }

        public function add_to_updated_list($post_ID)
        {
            $post_types = SCC()->strive_get_option('strive_calendar_post_types', ['post']);
            if (!in_array(get_post_type($post_ID), $post_types)) {
                return;
            }

            $list = SCC()->strive_get_option('strive_updated_list', []);
            if (!in_array($post_ID, $list)) {
                $list[] = $post_ID;
                update_option('strive_updated_list', $list);
            }
        }

        public function empty_list()
        {
            delete_option('strive_updated_list');
        }

        public function live_update_calendar()
        {
            if (!SCC()->permission_manager('author')) {
                return;
            }
            if (!isset($_GET['live_update_calendar_nonce'])) {
                return;
            }

            check_ajax_referer('live_update_calendar', 'live_update_calendar_nonce');

            $updated_posts = SCC()->strive_get_option('strive_updated_list', []);

            if (count($updated_posts) == 0) {
                wp_die();
            }

            $response = [];
            foreach ($updated_posts as $ID) {
                $data = [
                    'ID' => absint($ID),
                    'date' => get_the_date('Y-m-d', $ID),
                    'status' => get_post_status($ID),
                    'html' => SCC()->calendar->post_markup($ID),
                ];
                $response[] = $data;
            }

            $this->empty_list();

            wp_send_json(json_encode($response));

            wp_die();
        }

        public function live_update_toggle()
        {
            $setting = SCC()->strive_get_option('strive_live_calendar', false);
            $class = $setting ? 'live' : ''; ?>
            <div id="live-updates" class="live-updates-toggle <?php echo esc_attr($class); ?>">
                <label id="toggle-live-updates" class="strive-live-updates" for="strive_live_calendar">
                    <span class="circle">&#9679;</span>
                    <span class="label"><?php esc_html_e('Live', 'strive'); ?></span>
                    <input type="checkbox" name="strive_live_calendar" id="strive_live_calendar" <?php checked(true, $setting, true); ?>>
                </label>
            </div>
        <?php
        }

        public function save_live_update_preference()
        {
            if (!SCC()->permission_manager('admin')) {
                return;
            }

            if (!isset($_POST['live_updates_nonce'])) {
                return;
            }

            check_ajax_referer('live_updates', 'live_updates_nonce');

            $live_updates = wp_unslash($_POST['live']);

            $live_updates = rest_sanitize_boolean($live_updates);

            update_option('strive_live_calendar', $live_updates);

            wp_die();
        }
    }
}
