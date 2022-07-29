<?php
namespace GF_Post_Content_Fields_Post_Types;

class Core {

	public function __construct() {
		$this->add_actions();
	}

	public function add_actions() {
		add_action( 'gform_field_standard_settings', array( $this, 'add_post_type_setting' ), 10, 1 );
		add_filter( 'gfpcf_settings', array( $this, 'add_setting' ), 10, 1 );
		add_action( 'gform_editor_js', array( $this, 'load_editor_values' ) );
	}

	public function load_editor_values() {
		?>

		<script type='text/javascript'>
			jQuery( document ).bind( 'gform_load_field_settings', function( event, field, form ){
				jQuery( '#gfpcf_post_type' ).val( field.postType );
			});
		</script>

		<?php
	}

	public function add_setting( $existing ) {
		$existing[] = 'gfpcf_post_type';
		return $existing;
	}

	public function add_post_type_setting( $position ) {

		if ( $position == 25 ) {
			$this->build_post_type_select();
		}
	}

	public function get_post_types() {
		return get_post_types( array(), 'objects' );
	}

	public function build_post_type_select() {
		?>

		<li class="gfpcf_post_type field_setting">

			<label for="gfpcf_post_type">
				<?php esc_html_e( 'Post Type', 'gravityforms' ); ?>
			</label>

			<select id="gfpcf_post_type" onchange="SetFieldProperty('postType', this.value);">

				<?php foreach ( $this->get_post_types() as $post_type ) : ?>
					<option value="<?php echo $post_type->name; ?>"><?php echo $post_type->label; ?></option>
				<?php endforeach; ?>

			</select>

		</li>

	<?php
	}

}
