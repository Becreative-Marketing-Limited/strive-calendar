<?php
if (!class_exists('SCC_Welcome')) {
    class SCC_Welcome
    {
        public function __construct()
        {
            add_action('wp_ajax_save_onboarding_settings', [$this, 'save_onboarding_settings']);
            add_action('wp_ajax_import_starter_checklist', [$this, 'import_starter_checklist']);
        }

        public function welcome_page_markup()
        {
            $steps = ['welcome', 'calendar', 'statuses', 'checklist', 'finish'];
            $current = 'welcome';
            $exit_url = add_query_arg('page', 'strive-content-calendar', admin_url('edit.php'));
            $next_url = $exit_url;
            if (isset($_GET['step'])) {
                // Sanitize - has to be one of these 5 values
                if (in_array($_GET['step'], $steps)) {
                    $current = sanitize_text_field($_GET['step']);
                }
            }
            // Set the next page URL
            if ($current != 'finish') {
                $next = array_search($current, $steps);
                $next = $steps[$next + 1];
                $next_url = add_query_arg([
                    'page' => 'strive-content-calendar-welcome',
                    'step' => $next,
                ], admin_url('edit.php'));
            } ?>
            <div class="strive-welcome-container">
                <div class="inner">
                    <div class="logo-container">
                        <img class="logo" src="<?php echo trailingslashit(STRIVE_CC_URL) . 'img/logo.svg'; ?>" />
                    </div>
                    <div id="progress" class="progress">
                        <a href="?page=strive-content-calendar-welcome" class="tab welcome <?php if ($current == 'welcome') {
                echo 'current';
            } ?>">
                            <?php esc_html_e('Welcome', 'strive'); ?><span></span>
                        </a>
                        <a href="?page=strive-content-calendar-welcome&step=calendar" class="tab calendar <?php if ($current == 'calendar') {
                echo 'current';
            } ?>">
                            <?php esc_html_e('Calendar', 'strive'); ?><span></span>
                        </a>
                        <a href="?page=strive-content-calendar-welcome&step=statuses" class="tab statuses <?php if ($current == 'statuses') {
                echo 'current';
            } ?>">
                            <?php esc_html_e('Statuses', 'strive'); ?><span></span>
                        </a>
                        <a href="?page=strive-content-calendar-welcome&step=checklist" class="tab checklist <?php if ($current == 'checklist') {
                echo 'current';
            } ?>">
                            <?php esc_html_e('Checklist', 'strive'); ?><span></span>
                        </a>
                        <a href="?page=strive-content-calendar-welcome&step=finish" class="tab finish <?php if ($current == 'finish') {
                echo 'current';
            } ?>">
                            <?php esc_html_e('Finish', 'strive'); ?><span></span>
                        </a>
                    </div>
                    <div id="welcome-box" class="welcome-box">
                        <?php if ($current == 'welcome') : ?>
                            <div class="welcome step current">
                                <h2><?php esc_html_e('License Successfully Activated!', 'strive'); ?></h2>
                                <p class="subheading"><?php esc_html_e('All of Strive\'s features are now available on your site.', 'strive'); ?></p>
                                <p class="intro"><?php esc_html_e('To help you get started, follow the setup wizard by clicking the "Start Setup Wizard" button below.', 'strive'); ?></p>
                            </div>
                        <?php elseif ($current == 'calendar') : ?>
                            <div class="calendar step">
                                <form method="post" action="options.php"><?php
                                    settings_fields('strive_settings');
            do_settings_sections('strive-content-calendar-welcome');
            submit_button(esc_html__('Save & continue', 'strive'), 'primary hidden-submit', 'save-settings');
            // Override the referrer field to proceed to next step
            echo '<input type="hidden" name="_wp_http_referer" value="' . esc_attr($next_url) . '">'; ?>
                                </form>
                                <p class="note"><span><?php esc_html_e('Note:', 'strive'); ?></span> <?php esc_html_e('These options can be changed later from the Settings menu.', 'strive'); ?></p>
                            </div>
                        <?php elseif ($current == 'statuses') : ?>
                            <div class="statuses step">
                                <h2><?php esc_html_e('Assign statuses to your upcoming posts', 'strive'); ?></h2>
                                <p class="subheading"><?php esc_html_e('Select statuses for a few of your posts to color-code them in the calendar', 'strive'); ?></p>
                                <div class="posts">
                                    <?php
                                        $upcoming_posts = self::get_posts_for_onboarding();
            if (count($upcoming_posts) == 0) {
                echo '<div class="no-posts">';
                echo '<p>' . esc_html__('You have no upcoming posts to organize. Continue to the next step.', 'strive') . '</p>';
                echo '</div>';
            } else {
                echo '<div class="posts-heading">';
                echo '<span>' . esc_html__('Post title', 'strive') . '</span>';
                echo '<span>' . esc_html__('Status', 'strive') . '</span>';
                echo '</div>';
                foreach ($upcoming_posts as $post) {
                    $status = get_post_meta($post->ID, '_strive_editorial_status', true);
                    echo '<div class="post-row" data-id="' . esc_attr($post->ID) . '">';
                    echo '<div class="post">';
                    echo '<p class="post-title">';
                    echo '<a href="' . esc_url(get_edit_post_link($post->ID)) . '" target="_blank">' . esc_html($post->post_title) . '<span class="dashicons dashicons-external"></span></a>';
                    echo '</p>';
                    echo '</div>';
                    echo '<div class="status-container" data-status="' . esc_attr($status) . '">';
                    echo '<span class="circle">&#9679;</span>';
                    echo '<select class="status-select">';
                    foreach (SCC()->post_statuses() as $value => $label) {
                        echo '<option value="' . esc_attr($value) . '" ' . selected($value, $status, false) . ' class="' . esc_attr($value) . '">' . esc_html($label) . '</option>';
                    }
                    echo '</select>';
                    echo '</div>';
                    echo '</div>';
                }
            } ?>
                                </div>
                                <?php if (count($upcoming_posts) > 0) : ?>
                                    <p class="note"><span><?php esc_html_e('Note:', 'strive'); ?></span> <?php esc_html_e('You\'ll be able to edit the statuses via the calendar and post editor too.', 'strive'); ?></p>
                                <?php endif; ?>
                            </div>
                        <?php elseif ($current == 'checklist') : ?>
                            <div class="checklist step">
                                <h2><?php esc_html_e('Import a starter checklist', 'strive'); ?></h2>
                                <p class="subheading"><?php esc_html_e('Use our pre-made checklist to organize your content workflow', 'strive'); ?></p>
                                <p class="center"><?php esc_html_e('Would you like to import an 18-step checklist to get started with?', 'strive'); ?></p>
                                <p class="radio-buttons center" id="import-checklist">
                                    <input type="radio" id="import-yes" name="import" value="yes" checked>
                                    <label for="yes"><?php esc_html_e('Yes', 'strive'); ?></label>
                                    <input type="radio" id="import-no" name="import" value="no">
                                    <label for="no"><?php esc_html_e('No', 'strive'); ?></label>
                                </p>
                                <p class="note"><span><?php esc_html_e('Note:', 'strive'); ?></span> <?php esc_html_e('You can fully customize the checklist later in the Checklists page.', 'strive'); ?></p>
                            </div>
                        <?php elseif ($current == 'finish') :
                            $kb_url = 'https://strivecalendar.com/knowledgebase/';
            $contact_url = add_query_arg([
                                'page' => 'strive-content-calendar',
                                'tab'  => 'contact',
                            ], admin_url('edit.php')); ?>
                            <div class="finish step">
                                <h2><?php esc_html_e('Setup Complete!', 'strive'); ?></h2>
                                <p class="subheading"><?php esc_html_e('You\'re ready to create amazing content with Strive', 'strive'); ?></p>
                                <p class="center"><?php
                                    printf(
                                        '%s <a href="' . esc_url($kb_url) . '" target="_blank">%s</a>, %s <a href="' . esc_url($contact_url) . '">%s</a>.',
                                        esc_html__('If you need more guidance, you can find video tutorials and articles in the', 'strive'),
                                        esc_html__('Knowledge Base', 'strive'),
                                        esc_html__('or email us via the', 'strive'),
                                        esc_html__('contact form', 'strive')
                                    ); ?>
                                </p>
                                <p class="center"><?php esc_html_e('Click the button below to go to the Content Calendar.', 'strive'); ?></p>
                            </div>
                        <?php endif; ?>
                        <div class="navigation">
                            <?php if ($current == 'welcome') : ?>
                                <a id="start-button" class="start button-primary button" href="<?php echo esc_url($next_url); ?>"><?php esc_html_e('Start Setup Wizard', 'strive'); ?></a>
                            <?php elseif ($current == 'finish') : ?>
                                <a id="start-button" class="start button-primary button" href="<?php echo esc_url($exit_url); ?>"><?php esc_html_e('Go to the Calendar', 'strive'); ?></a>
                            <?php else : ?>
                                <p><a class="skip button-secondary button" href="<?php echo esc_url($next_url); ?>"><?php esc_html_e('Skip step', 'strive'); ?></a></p>
                                <p><a id="submit" class="button-primary button <?php esc_attr_e($current); ?>" href="<?php echo esc_url($next_url); ?>"><?php esc_html_e('Save & Continue', 'strive'); ?></a></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="skip-container">
                        <a href="<?php echo esc_url($exit_url); ?>"><?php esc_html_e('Skip to the calendar', 'strive'); ?></a>
                    </div>
                </div>
            </div>
        <?php
        }

        public function get_posts_for_onboarding()
        {

            // Get up to 6 scheduled posts
            $args = [
                'posts_per_page' => 6,
                'post_status'    => 'future',
            ];
            $upcoming_posts = get_posts($args);

            // Add drafs if less than 6 scheduled posts
            $remaining = 6 - count($upcoming_posts);
            if ($remaining > 0) {

                // Get a few recently edited drafts
                $args = [
                    'posts_per_page' => $remaining,
                    'post_status'    => 'draft',
                ];
                $drafts = get_posts($args);

                // Add the drafts to the upcoming posts
                if ($drafts) {
                    foreach ($drafts as $draft) {
                        array_push($upcoming_posts, $draft);
                    }
                }
            }

            return $upcoming_posts;
        }

        public function save_onboarding_settings()
        {

            // Restrict to Admins
            if (!SCC()->permission_manager('admin')) {
                return;
            }

            // Make sure the nonce and index are set
            if (!isset($_POST['save_onboarding_settings_nonce']) || !isset($_POST['post_status_data'])) {
                return;
            }

            // Verify the nonce
            check_ajax_referer('save_onboarding_settings', 'save_onboarding_settings_nonce');

            $posts = wp_unslash($_POST['post_status_data']);

            if (is_array($posts)) {
                // Save the new status
                foreach ($posts as $post) {
                    // Sanitize ID
                    $id = absint($post['id']);
                    // Sanitize status. Has to be one of the four statuses
                    $status = array_key_exists($post['status'], SCC()->post_statuses()) ? $post['status'] : 'not-started';
                    update_post_meta($id, '_strive_editorial_status', $status);
                }
            } else {
                wp_send_json_error(esc_html__('Not array.', 'strive'));
            }

            wp_die();
        }

        public function import_starter_checklist()
        {

            // Restrict to Admins
            if (!SCC()->permission_manager('admin')) {
                return;
            }

            // Make sure the nonce and index are set
            if (!isset($_POST['import_starter_checklist_nonce']) || !isset($_POST['import_checklist'])) {
                return;
            }

            // Verify the nonce
            check_ajax_referer('import_starter_checklist', 'import_starter_checklist_nonce');

            // yes/no from radio buttons
            $value = wp_unslash($_POST['import_checklist']);

            // Don't do anything if it's "no"
            if ($value == 'yes') {
                // Get the checklist from the checklist.json file
                $checklist = file_get_contents(trailingslashit(STRIVE_CC_DIR) . 'resources/checklist.json');
                $checklist = json_decode($checklist, true);
                $checklist = [
                    'title' => __('Starter Checklist', 'strive'),
                    'id'    => uniqid(),
                    'checklist' => $checklist,
                ];

                // Default to empty string to skip array evaluation if no tasks are saved
                $user_checklists = SCC()->strive_get_option('strive_post_checklists', '');

                // Avoid replacing an existing checklist
                if (is_array($user_checklists)) {
                    // Replace if no tasks e.g. one checklist with 4 empty status arrays
                    if (count($user_checklists) == 1 && count($user_checklists[0]['checklist'], 1) == 4) {
                        $user_checklists = [$checklist];
                    } else {
                        $user_checklists[] = $checklist;
                    }
                } else {
                    $user_checklists = [$checklist];
                }

                update_option('strive_post_checklists', $user_checklists);
            }

            wp_die();
        }
    }
}
