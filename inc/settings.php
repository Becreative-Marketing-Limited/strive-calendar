<?php
if ( ! class_exists( 'SCC_Settings' ) ) {
	class SCC_Settings {
		public function build_settings() {
			if ( ! SCC()->permission_manager( 'admin' ) ) {
				return;
			} ?>
            <div id="settings-tabs" class="settings-tabs">
                <a href="#" id="tab-calendar" class="tab calendar current" data-id="calendar"><?php esc_html_e( 'Calendar', 'strive' ); ?></a>
                <a href="#" id="tab-checklists" class="tab checklists" data-id="checklists"><?php esc_html_e( 'Checklists', 'strive' ); ?></a>
                <a href="#" id="tab-pipeline" class="tab pipeline" data-id="pipeline"><?php esc_html_e( 'Pipeline', 'strive' ); ?></a>
            </div>
            <div class="settings-area">
                <div id="settings-calendar" class="settings-group calendar current" data-id="calendar">
					<?php echo SCC()->cal_settings->output_settings_fields(); ?>
                </div>
                <div id="settings-checklists" class="settings-group checklists" data-id="checklists">
					<?php echo SCC()->display_pro_notice(); // echo SCC()->check_settings->output_checklist_settings(); ?>
                </div>
                <div id="settings-pipeline" class="settings-group pipeline" data-id="pipeline">
					<?php echo SCC()->display_pro_notice(); // echo SCC()->pipe_settings->output_settings_fields(); ?>
                </div>
            </div>
			<?php
		}
	}
}
