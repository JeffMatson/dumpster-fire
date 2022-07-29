<?php

GFForms::include_feed_addon_framework();

class GF_Simple_PDF extends GFFeedAddOn {

	protected $_version = '1.0';
	protected $_min_gravityforms_version = '1.9';
	protected $_slug = 'gf_simple_pdf';
	protected $_path = 'gf-simple-pdf-generator/gf-simple-pdf-generator.php';
	protected $_full_path = __FILE__;
	protected $_title = 'Gravity Forms Simple PDF Generator';
	protected $_short_title = 'Simple PDF';

	protected $_multiple_feeds = false;

	private static $_instance = null;

	public static function get_instance() {
		if ( self::$_instance == null ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function feed_settings_fields() {
		return array(
			array(
				'title'  => 'PDF Settings',
				'fields' => array(
					array(
						'label'   => 'Feed Name',
						'type'    => 'text',
						'name'    => 'feedName',
					),
					array(
						'label' => 'Content',
						'type'  => 'textarea',
						'name'  => 'pdf_content',
						'callback' => array( $this, 'settings_pdf_content' ),
					),
				),
			),
		);
	}

	public function feed_list_columns() {
		return array(
			'feedName' => 'Name',
		);
	}

	public function get_column_value_name( $feed ) {
		return '<b>' . rgars( $feed, 'meta/feedName' ) . '</b>';
	}

	public function settings_pdf_content() {
		wp_editor( $this->get_setting( 'pdf_content' ), '_gaddon_setting_pdf_content' );
	}

	public function process_feed( $feed, $entry, $form ) {
		$content = rgars( $feed, 'meta/pdf_content' );
		$this->content_template = $content;
		add_filter( 'gform_confirmation', array( $this, 'generate_confirmation' ), 10, 4 );
	}

	public function generate_confirmation( $confirmation, $form, $entry, $is_ajax ) {
		$pdf_content = GFCommon::replace_variables( $this->content_template, $form, $entry, false, false, false );
		$this->generate_pdf( $pdf_content );
		return $pdf_content;
	}

	public function generate_pdf( $content ) {
		$pdf = new TCPDF( PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false );

		$pdf->SetAuthor( 'Jeff Matson' );

		// remove default header/footer
		$pdf->setPrintHeader( false );
		$pdf->setPrintFooter( false );

		// set default monospaced font
		$pdf->SetDefaultMonospacedFont( PDF_FONT_MONOSPACED );

		// set margins
		$pdf->SetMargins( PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT );

		// set auto page breaks
		$pdf->SetAutoPageBreak( TRUE, PDF_MARGIN_BOTTOM );

		// set image scale factor
		$pdf->setImageScale( PDF_IMAGE_SCALE_RATIO );

		// set font
		$pdf->SetFont( 'times', '', 12 );

		// add a page
		$pdf->AddPage();

		// set some text to print
		$txt = $content;

		// print a block of text using Write()
		$pdf->writeHTML( $content, true, false, true, false, '' );

		// ---------------------------------------------------------

		//Close and output PDF document
		$pdf->Output( 'contract.pdf', 'I' );
	}
}
