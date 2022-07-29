<?php
namespace GF_Post_Content_Fields;

class Core extends \GFAddOn {

	protected $_version                  = GFPCF_VERSION;
	protected $_min_gravityforms_version = '1.9';
	protected $_slug                     = 'gfpcf';
	protected $_path                     = GFPCF_PATH;
	protected $_full_path                = __FILE__;
	protected $_title                    = 'Gravity Forms Post Content Editor';
	protected $_short_title              = 'Post Content Editor';

	public function _construct() {
		parent::_construct();
	}

	public function init_field() {
		\GF_Fields::register( new Fields\GF_Field_Post_Editor );
	}

	public function js_path( $filename ) {
		return plugins_url( 'js/dist/' . $filename, __FILE__ );
	}

	public function css_path( $filename ) {
		return plugins_url( 'css/' . $filename, __FILE__ );
	}

	public function css_includes_path( $filename ) {
		return plugins_url( 'css/includes/' . $filename, __FILE__ );
	}

	public function field_condition() {
		return array( 'field_types' => array( 'post-editor' ) );
	}

	public function scripts() {
		$scripts = parent::scripts();

		$scripts[] = array(
			'handle'    => 'gfpcf',
			'src'       => $this->js_path( 'build.js' ),
			'version'   => $this->_version,
			'deps'      => array( 'jquery' ),
			'enqueue'   => array(
				$this->field_condition()
			),
			'in_footer' => true,
			'callback'  => array( $this, 'localize_posts' ),
		);

		wp_enqueue_media();

		return $scripts;
	}

	public function styles() {
		$styles = parent::styles();

		$styles[] = array(
			'handle' => 'gfpcf-tinymce-skin',
			'src' => $this->css_includes_path( 'lightgray/skin.min.css' ),
			'version' => $this->_version,
			'enqueue' => array(
				$this->field_condition()
			)
		);

		$styles[] = array(
			'handle' => 'gfpcf-tinymce-content',
			'src' => $this->css_includes_path( 'lightgray/content.min.css' ),
			'version' => $this->_version,
			'enqueue' => array(
				$this->field_condition()
			)
		);

		return $styles;
	}

	public static function build_existing_posts( $post_type = 'post' ) {

		$posts = get_posts( array(
			'post_type'      => $post_type,
			'posts_per_page' => -1,
		));

		$post_items_filtered = array();

		foreach ( $posts as $item ) {
			$post_items_filtered[] = new Utility\Processed_Post( $item );
		}

		return $post_items_filtered;
	}

	public function localize_posts() {
		wp_localize_script( 'gfpcf', 'gfpcf_posts', self::build_existing_posts() );
	}
}
