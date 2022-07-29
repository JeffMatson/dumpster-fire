<?php

namespace NotifyBot\Views;

use NotifyBot\Core;
use NotifyBot\Models\Notifications;

class List_All extends View_Controller {

	public function run_list_table() {
		$this->enqueue_scripts();
		$list_table = new List_Table();
		$list_table->prepare_items();
		$list_table->display();
	}

	public function add_actions() {

		if ( ! isset( $_GET['page'] ) || $_GET['page'] !== 'notifybot' ) {
			return;
		}

		add_action( 'admin_init', array( $this, 'delete' ) );
	}

	public function enqueue_scripts(  ) {

		if ( ! isset( $_GET['page'] ) || $_GET['page'] !== 'notifybot' ) {
			return;
		}

		$core = Core::get_instance();
		wp_enqueue_style( 'nb-list-all', $core->url() . 'css/list-all.css' );
	}

	public function display() {
		if ( ! current_user_can( 'manage_options' ) || ! defined( 'ABSPATH' ) ) { exit; }

		if ( ! isset( $_GET['page'] ) || $_GET['page'] !== 'notifybot' ) {
			return;
		} ?>
		
		<div class="wrap">
			<h2>NotifyBot <?php echo $this->add_new_button() ?></h2>
			<?php $this->run_list_table(); ?>
		</div>
		
		<?php
	}

	public function delete() {
		if ( ! current_user_can( 'manage_options' ) || ! defined( 'ABSPATH' ) ) { exit; }

		if ( isset( $_GET['action'] ) && isset( $_GET['group_id'] ) && $_GET['action'] == 'delete' ) {
			$group_id = intval( $_GET['group_id'] );
			Notifications::get_instance()->delete_by_group( $group_id );
		}
	}
}
