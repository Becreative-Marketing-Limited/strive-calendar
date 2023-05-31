<?php

if (!class_exists('SCC_Pipeline')) {
    class SCC_Pipeline
    {
        public function __construct()
        {
            add_action('wp_ajax_reload_pipeline', [$this, 'reload_pipeline']);
        }

        public function build_pipeline()
        {
            $layout_class = SCC()->strive_get_option('strive_pipeline_layout', 'columns') == 'rows' ? 'rows' : 'columns';

            $pipeline = '<div id="strive-pipeline" class="strive-pipeline ' . esc_attr($layout_class) . '">';

            if (SCC()->permission_manager('admin')) {
                $pipeline .= '<div class="settings">';
                $pipeline .= '<a id="settings-button" class="settings-button" href="#"><img class="settings-icon" src="' . STRIVE_CC_URL . 'img/settings.svg" /> ' . esc_html__('Settings', 'strive') . '</a>';
                $pipeline .= '</div>';
            }

            $pipeline .= '<div class="layout-buttons">';
            $pipeline .= '<button id="pipeline-columns" class="button-columns"><span class="dashicons dashicons-columns"></span> ' . esc_html__('Columns', 'strive') . '</button>';
            $pipeline .= '<button id="pipeline-rows" class="button-rows"><span class="dashicons dashicons-menu"></span> ' . esc_html__('Rows', 'strive') . '</button>';
            $pipeline .= '</div>';
            $pipeline .= '<div id="pipeline" class="pipeline">';
            $pipeline .= self::status_sections();
            $pipeline .= '</div>';
            $pipeline .= '</div>';

            return $pipeline;
        }

        public function status_sections()
        {
            $html = '';
            foreach (SCC()->post_statuses() as $status) {
                $posts = self::get_post_drafts($status);

                $html .= '<div class="status-container ' . sanitize_title($status) . '">';
                $html .= '<div class="heading">' . esc_html($status) . ' <span class="count">(' . count($posts) . ')</span></div>';
                $html .= '<div class="posts">';

                if (count($posts) == 0) {
                    $html .= '<p>' . esc_html__('No posts with this status.', 'strive') . '</p>';
                } else {
                    foreach ($posts as $post) {
                        $html .= self::post_markup($post);
                    }
                }
                $html .= '</div>';
                $html .= '</div>';
            }

            return $html;
        }

        public function post_markup($post)
        {
            $post_type = get_post_type($post->ID);

            $html = '<div class="post post-type-' . esc_attr($post_type) . '" data-id="' . esc_attr($post->ID) . '">';
            $html .= '<a class="edit-post" href="' . esc_url(get_edit_post_link($post->ID)) . '" target="_blank">Edit Post</a>';
            $html .= '<div class="title">' . esc_html(get_the_title($post->ID)) . '</div>';
            $html .= '<div class="meta"><span class="author">' . esc_html(get_the_author_meta('display_name', $post->post_author)) . '</span>';
            if (get_post_status($post->ID) == 'future') {
                $html .= ' <span class="circle">&#8226;</span> <span class="date">' . get_the_date('', $post->ID) . '</span>';
            }
            if (!empty(get_post_meta($post->ID, '_strive_copy_of', true))) {
                $html .= ' <span class="circle">&#8226;</span> <span class="revision-label">' . esc_html__('Revision', 'strive') . '</span>';
            }
            $html .= '</div>';
            if (count(SCC()->get_supported_post_types('strive_pipeline_post_types')) > 1) {
                $html .= SCC()->get_post_type_icon($post_type);
            }
            $html .= '</div>';

            return $html;
        }

        public function get_post_drafts($status)
        {

            // Get posts that have the current status. "Not started" posts also include empty statuses
            if ($status == 'Not Started') {
                $args = [
                    'posts_per_page' => -1,
                    'post_status'    => ['draft', 'future', 'pending'],
                    'perm'           => 'readable',
                    'meta_query'     => [
                    'relation' => 'OR',
                        [
                            'key'     => '_strive_editorial_status',
                            'value'   => sanitize_title($status),
                            'compare' => 'NOT EXISTS',
                        ],
                        [
                            'key'     => '_strive_editorial_status',
                            'value'   => sanitize_title($status),
                            'compare' => 'LIKE',
                        ],
                    ],
                ];
            } else {
                $args = [
                    'posts_per_page' => -1,
                    'post_status'    => ['draft', 'future', 'pending'],
                    'perm'           => 'readable',
                    'meta_key'       => '_strive_editorial_status',
                    'meta_value'     => sanitize_title($status),
                ];
            }

            // Query the post types selected by the user
            $args['post_type'] = SCC()->get_supported_post_types('strive_pipeline_post_types');

            // Limit to posts they can edit when contributor or author
            if (!SCC()->permission_manager('editor')) {
                $args['author'] = get_current_user_id();
            }

            // Get the posts with the current status
            $posts = new WP_Query($args);

            // Get the WP_Post objects in the returned query
            $posts = $posts->posts;

            return $posts;
        }

        public function reload_pipeline()
        {
            if (!SCC()->permission_manager('admin')) {
                return;
            }

            if (!isset($_POST['reload_pipeline_nonce'])) {
                return;
            }

            check_ajax_referer('reload_pipeline', 'reload_pipeline_nonce');

            echo self::status_sections();

            wp_die();
        }
    }
}
