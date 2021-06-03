<?php

class MAPS_FOR_CF7_Shortcode {
	public $atts;
	public $content;
	public $code;

	public function __construct( $atts, $content, $code ) {
		$this->atts = $atts;
		$this->content = $content;
		$this->code = $code;

        }
	public static function add_action() {
		add_action( 'wp_ajax_getmarkerinfos', array( 'MAPS_FOR_CF7_Rest', 'getmarkerinfos' ) );
		add_action( 'wp_ajax_nopriv_getmarkerinfos', array( 'MAPS_FOR_CF7_Rest', 'getmarkerinfos' ) );
		add_action( 'wp_ajax_getrank', array( 'MAPS_FOR_CF7_Rest', 'getrank' ) );
		add_action( 'wp_ajax_nopriv_getrank', array( 'MAPS_FOR_CF7_Rest', 'getrank' ) );
	}

	public function html() {
		$this->enqueue_scripts();

		$options = MAPS_FOR_CF7_Options::get_instance();

                $settings = $options->get_option();
                $form_ids = $settings[ MAPS_FOR_CF7_Options::form_ids ];

		$atts = shortcode_atts(
			array(
				'form-id' => 0
			),
			$this->atts,
			'maps-for-contact-form-7'
                );
		$form_id = $atts[ 'form-id' ];
		if ( empty( $form_id ) ) {
			return;
		}
		if ( !in_array( $form_id, $form_ids ) ) {
			return;
		}
		$contact_forms = WPCF7_ContactForm::find( array(
			'p' => $form_id,
		) );
                $contact_form = $contact_forms[ 0 ];
		$tags = $contact_form->scan_form_tags();
		$taxonomies = array();
                foreach( $tags as $tag ) {
                        switch ( $tag[ 'basetype' ] ) {
                        case 'radio':
                                $taxonomy_name = MAPS_FOR_CF7_Taxonomy::get_name( $contact_form->id(), $tag );
                                $taxonomies[] = array(
                                        'name' => $taxonomy_name,
                                        'tag' => $tag );
                                break;
			default:
				break;
			}
		}
		?>
		<div class="maps-for-cf7-shortcode">
		<?php
		$this->html_taxonomies( $form_id, $taxonomies );
		$this->html_rank();
		$this->html_map();
		?>
		</div>
		<?php
        }
	public function html_taxonomies( $form_id, $taxonomies ) {
		?>
		<form class="maps-for-cf7-shortcode-form" data-form-id="<?php echo esc_attr( $form_id ); ?>">
		<?php
		foreach( $taxonomies as $taxonomy ) {
			$tag = $taxonomy[ 'tag' ];
			?>
			<p>
				<label class="maps-for-cf7-shortcode-radio-label">
				<?php echo esc_html( $tag->name ); ?>
				</label>
				<?php
                        	switch ( $tag[ 'basetype' ] ) {
				case 'radio':
					$this->html_tag_radio( $form_id, $taxonomy );
					break;
				default:
					break;
				}
				?>
			</p>
			<?php
		}
		?>
		</form>
		<?php
	}
	private function html_tag_radio( $form_id, $taxonomy ) {
		$tag = $taxonomy[ 'tag' ];
		$name = MAPS_FOR_CF7_Taxonomy::get_name( $form_id, $tag );
		$raw_values = $tag[ 'raw_values' ];
		$labels = $tag[ 'labels' ];
		?>
		<div>
		<?php
		for ( $i = 0; $i < count( $raw_values ); ++$i ) {
			$raw_value = $raw_values[ $i ];
			$label = $labels[ $i ];
			?>
			<input type="checkbox" class="maps-for-cf7-shortcode-map-radio" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $raw_value ); ?>"><?php echo esc_html( $label ); ?>
			<br/>
			<?php
		}
		?>
		</div>
		<?php
	}
	private function html_rank() {
		$options = MAPS_FOR_CF7_Options::get_instance();

                $settings = $options->get_option();
                $num_ranks = $settings[ MAPS_FOR_CF7_Options::num_ranks ];
		if ( $num_ranks < 0 ) $num_ranks = 0;
		?>
		<label class="maps-for-cf7-shortcode-rank-label"><?php echo esc_html( __( 'Rank', 'maps-for-contact-form-7' ) ); ?></label>
		<div class="block">
		<?php
		for ( $i = 0; $i < $num_ranks; ++$i ) {
			$class = 'rank-' . ( $i + 1 );
			?>
			<div class="maps-for-cf7-shortcode-map-rank <?php echo $class; ?>"></div>
			<?php
		}
		?>
		<div class="maps-for-cf7-shortcode-map-rank rank-more"></div>
		</div>
		<?php
	}
	private function html_map() {
		?>
		<div class="maps-for-cf7-shortcode-map-block">
			<div class="maps-for-cf7-shortcode-map" ></div>
      		</div>
		<?php
	}
	private function enqueue_scripts() {
	}
}

