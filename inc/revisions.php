<?php

if (!class_exists('SCC_Revisions')) {
    class SCC_Revisions
    {
        public function __construct()
        {
            add_filter('post_row_actions', [$this, 'add_revision_link'], 10, 2);
            add_filter('page_row_actions', [$this, 'add_revision_link'], 10, 2);
            add_filter('display_post_states', [$this, 'add_revision_label'], 10, 2);
            add_action('admin_bar_menu', [$this, 'add_revision_toolbar_item'], 500);
            add_action('admin_action_create_revision', [$this, 'create_revision']);
            add_action('publish_post', [$this, 'override_original'], 10, 2);
            add_action('publish_page', [$this, 'override_original'], 10, 2);
            add_action('init', [$this, 'add_hooks_for_user_cpt']);
            // add_action('pre_get_posts', array($this, 'exclude_revisions_from_rss'));
            add_action('views_edit-post', [$this, 'pass_post_type_to_filter']);
            add_action('views_edit-page', [$this, 'pass_post_type_to_filter']);
            add_action('pre_get_posts', [$this, 'view_revisions_in_posts_menu']);
            add_filter('query_vars', [$this, 'add_revision_query_var']);
            add_action('admin_enqueue_scripts', [$this, 'hide_permalink_classic_editor']);
        }

        // Add support for publishing revisions and revision filter for CPTs. Registers hooks ASAP, which is on 'init'.
        public function add_hooks_for_user_cpt()
        {
            // Get public CPT created by the user
            $user_cpt = get_post_types([
                'public'   => true,
                '_builtin' => false,
            ]);
            // Limit to CPTs with custom-fields enabled
            foreach ($user_cpt as $cpt) {
                if (post_type_supports($cpt, 'custom-fields')) {
                    // Register the publish override hook
                    add_action('publish_' . esc_attr($cpt), [$this, 'override_original'], 10, 2);
                    // Register the Revisions menu filter
                    add_action('views_edit-' . esc_attr($cpt), [$this, 'pass_post_type_to_filter']);
                }
            }
        }

        // Add "Create Revision" to Posts menu (excluding revisions)
        public function add_revision_link($actions, $post)
        {
            // Allow Contributors and up
            if (!SCC()->permission_manager('contributor')) {
                return $actions;
            }

            if (self::allow_revision($post)) {

                // If contributor, limit to their posts
                if (!SCC()->can_edit_this_post($post->ID)) {
                    return $actions;
                }

                // Build the request URL
                $url = self::build_revision_request_url($post->ID);

                // Add the new link to the list
                $actions = array_merge($actions, [
                    'create_revision' => sprintf(
                        '<a href="%1$s">%2$s</a>',
                        esc_url($url),
                        esc_html__('Create Revision', 'strive')
                    ),
                ]);
            }

            return $actions;
        }

        // Create URL used for "Create Revision" link in Posts menu
        public function build_revision_request_url($id)
        {
            // Allow Contributors and up
            if (!SCC()->permission_manager('contributor')) {
                return;
            }

            // If contributor, limit to their posts
            if (!SCC()->can_edit_this_post($id)) {
                return;
            }

            // Create URL
            $url = add_query_arg([
                'action'  => 'create_revision',
                'post_id' => $id,
            ], admin_url());

            // Include nonce
            $url = wp_nonce_url($url, 'create_revision', 'create_revision_nonce');

            return $url;
        }

        // Create a revision of an existing post
        public function create_revision()
        {
            // Allow Contributors and up
            if (!SCC()->permission_manager('contributor')) {
                return;
            }

            // Make sure request is set
            if (!isset($_REQUEST['post_id'])) {
                return;
            }

            // Verify nonce
            if (!wp_verify_nonce(wp_unslash($_REQUEST['create_revision_nonce']), 'create_revision')) {
                return;
            }

            // Get the ID of the post the user clicked "Create Revision" for
            $parent_id = wp_unslash($_REQUEST['post_id']);

            // If author, limit to their posts
            if (!SCC()->can_edit_this_post($parent_id)) {
                return;
            }

            // Get the post data as an associative array
            $post = get_post($parent_id, 'ARRAY_A');

            // Create the revision
            $revision_id = self::insert_revision($post, $parent_id);

            // Craft the editor URL manually since get_edit_post_link() isn't ready yet
            $editor_url = add_query_arg([
                'action' => 'edit',
                'post'   => $revision_id,
            ], admin_url('post.php'));

            // Redirect safely to the editor
            wp_safe_redirect($editor_url);
            exit;
        }

        public function insert_revision($post, $parent_id)
        {

            // Reset the ID and status
            $post['ID'] = 0;
            $post['post_status'] = 'draft';

            // Add a unique permalink
            $post['post_name'] = $post['post_name'] . uniqid('_revision_');

            // Add the revision to DB and get the new ID back
            $revision_id = wp_insert_post($post);

            // Get all of the original post meta data
            $meta = get_post_meta($parent_id);

            // Have to convert values from single-item arrays back to their values for some INSANE reason
            $meta = array_map(function ($n) {
                return $n[0];
            }, $meta);

            // Reset Strive's status
            $meta['_strive_editorial_status'] = 'not-started';

            // Reset Strive's checklist completion
            $meta['_strive_checklists'] = '';

            // Add ID of parent post to meta
            $meta['_strive_copy_of'] = $parent_id;

            // Remove default WP fields a brand new post shouldn't copy
            $to_remove = ['_edit_lock', '_edit_last', '_pingme', '_encloseme'];

            // Remove the fields
            foreach ($to_remove as $remove) {
                unset($meta[$remove]);
            }

            // Add the parent post's meta to the new post (have to loop :/)
            foreach ($meta as $key => $value) {
                update_post_meta($revision_id, $key, $value);
            }

            return $revision_id;
        }

        // Copyies the revision's content over the original on publish
        // Only runs when a post is switched to the "publish" status from a different status
        public function override_original($ID, $post)
        {
            // Get ID of parent post (if it exists)
            $parent = self::get_revision_parent($ID);

            // Only continue for revisions
            if (!empty($parent)) {

                // Drop slug to avoid editing the parent's permalink
                unset($post->post_name);

                // Replace the ID
                $post->ID = $parent;

                // Update the parent post using the revision's content
                wp_update_post($post);

                // Get the revision's meta data
                $meta = get_post_meta($ID);

                // Have to convert values from single-item arrays back to their values for some INSANE reason
                $meta = array_map(function ($n) {
                    return $n[0];
                }, $meta);

                // Remove parent ID
                unset($meta['_strive_copy_of']);

                // Update parent post's meta
                foreach ($meta as $key => $value) {
                    update_post_meta($parent, $key, $value);
                }

                // Move the revision to Trash. This also redirects to the Posts page itself.
                wp_trash_post($ID);

                // Redirect needed for Classic Editor
                if (is_plugin_active('classic-editor/classic-editor.php')) {
                    wp_redirect(get_permalink($parent));
                    exit;
                }
            }
        }

        // Add "Revision" label to Posts menu where "Scheduled" shows
        public function add_revision_label($states, $post)
        {
            if (self::get_revision_parent($post->ID)) {
                $states['revision'] = esc_html__('Revision', 'strive');
            }

            return $states;
        }

        // Add the "Create Revision" or "Revision" label to the admin toolbar
        public function add_revision_toolbar_item(WP_Admin_Bar $admin_bar)
        {
            // Allow Contributors and up
            if (!SCC()->permission_manager('contributor')) {
                return;
            }

            global $pagenow;
            global $post;

            // Limit to pages with $post defined
            if ($post === null) {
                return;
            }

            // If author, limit to their posts
            if (!SCC()->can_edit_this_post($post->ID)) {
                return;
            }

            // Add the "Revision" label for revisions (only in back-end)
            if ($pagenow == 'post.php' && self::get_revision_parent($post->ID)) {

                // Add the new menu item
                $admin_bar->add_menu([
                    'id'     => 'strive-revision-label',
                    'parent' => null,
                    'group'  => null,
                    'title'  => esc_html__('This is a Revision', 'strive'),
                    'meta'   => [
                        'title' => esc_html__('This is a revision', 'strive'),
                    ],
                ]);
            }
            // Add "Create Revision" link for published non-revisions (front-end & back-end)
            elseif (($pagenow == 'post.php' || is_singular()) && self::allow_revision($post)) {
                // Add the new menu item
                $admin_bar->add_menu([
                    'id'     => 'strive-create-revision',
                    'parent' => null,
                    'group'  => null,
                    'title'  => esc_html__('Create Revision', 'strive'),
                    'href'   => self::build_revision_request_url($post->ID),
                    'meta'   => [
                        'title' => esc_html__('Create a revision', 'strive'),
                    ],
                ]);
            }
        }

        // Helper function to return a revision's parent post ID (or false)
        public function get_revision_parent($id)
        {
            $parent_id = get_post_meta($id, '_strive_copy_of', true);

            return $parent_id == '' ? false : $parent_id;
        }

        // Keep revisions from ever showing up in the RSS feed
        // This is needed because the "publish_post" hook happens after the status is saved to the DB
        // meaning that the post hits the RSS feed, even though it's for a few seconds at most, which could trigger
        // actions from services watching the feed
        public function exclude_revisions_from_rss($query)
        {

            // Only target public RSS feed
            if (is_feed() && !is_admin()) {
                $meta_query = $query->get('meta_query') ? $query->get('meta_query') : [];

                // Don't allow revisions
                $meta_query[] = [
                    'key'     => '_strive_copy_of',
                    'compare' => 'NOT EXISTS',
                ];

                $query->set('meta_query', $meta_query);
            }
        }

        // Makes query var available for use on edit.php
        public function add_revision_query_var($qvars)
        {
            $qvars[] = 'strive_revisions';

            return $qvars;
        }

        // Have to call function in this way since the parameter can't be passed via the action hook
        public function pass_post_type_to_filter($views)
        {
            // Better for getting the post type on the edit.php page than get_post_type()
            global $typenow;

            return self::add_revisions_filter_to_menu($views, $typenow);
        }

        // Adds a "Revisions" link to the status filters on edit.php
        public function add_revisions_filter_to_menu($views, $post_type)
        {
            // Prepare query
            $args = [
                'post_status' => 'draft',
                'meta_key'    => '_strive_copy_of',
                'post_type'   => $post_type,
            ];
            $revisions = get_posts($args);

            // Get # of revisions
            $count = count($revisions);
            $current = '';

            // If the Revisions filter is active, add current class
            if (get_query_var('strive_revisions') == 1) {
                $current = 'class="current" aria-current="page"';
            }

            // Add the Revisions link to the filters
            $views['strive_revisions'] = sprintf(
                '<a href="%1$s" %2$s>%3$s <span class="count">(%4$u)</span></a>',
                esc_url(admin_url('edit.php?post_type=' . esc_attr($post_type) . '&strive_revisions=1')),
                $current,
                esc_html('Revisions', 'strive'),
                absint($count)
            );

            return $views;
        }

        // Filter query on edit.php to only include post revisions when Revision filter is active
        public function view_revisions_in_posts_menu($query)
        {
            global $pagenow;

            // Carefully restrict to edit.php in admin for correct users
            if (is_admin() && $pagenow == 'edit.php' && $query->is_main_query() && SCC()->permission_manager('contributor')) {

                // Check if the filter is active
                if (get_query_var('strive_revisions') == 1) {

                    // Only get drafts
                    $query->set('post_status', 'draft');

                    // Limit to posts with a "parent"
                    $meta_query[] = [
                        'key'     => '_strive_copy_of',
                        'compare' => 'EXISTS',
                    ];

                    $query->set('meta_query', $meta_query);
                }
            }
        }

        // Hide permalink section for revisions with Classic Editor installed
        public function hide_permalink_classic_editor($hook)
        {
            global $post;

            // Make sure Classic Editor is installed, editing a post, and it's a revision
            if (is_plugin_active('classic-editor/classic-editor.php') && $hook == 'post.php' && self::get_revision_parent($post->ID)) {

                // Hide with inline CSS
                $css = '#edit-slug-box { display: none; }';
                wp_add_inline_style('strive-editor-style', $css);
            }
        }

        public function allow_revision($post)
        {
            if (post_type_supports($post->post_type, 'custom-fields') && !self::get_revision_parent($post->ID) && $post->post_status == 'publish') {
                return true;
            } else {
                return false;
            }
        }
    }
}
