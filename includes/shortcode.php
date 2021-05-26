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

                $setting = $options->get_option();
                $form_ids = $setting[ MAPS_FOR_CF7_Options::form_ids ];

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
		<div class="maps-for-contact-form-7-shortcode">
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
		<form class="" data-form-id="<?php echo $form_id; ?>">
		<?php
		foreach( $taxonomies as $taxonomy ) {
			$tag = $taxonomy[ 'tag' ];
			?>
			<p>
				<label>
				<?php echo $tag->name; ?>
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
			<input type="checkbox" name="<?php echo $name; ?>" value="<?php echo $raw_value; ?>"><?php echo $label; ?>
			<br/>
			<?php
		}
		?>
		</div>
		<?php
	}
	private function html_rank() {
		$options = MAPS_FOR_CF7_Options::get_instance();

                $setting = $options->get_option();
                $num_ranks = $setting[ MAPS_FOR_CF7_Options::num_ranks ];
		if ( $num_ranks < 0 ) $num_ranks = 0;
		?>
		<label><?php _e( 'Rank', 'maps-for-contact-form-7' ); ?></label>
		<div class="block">
		<?php
		for ( $i = 0; $i < $num_ranks; ++$i ) {
			$id = 'rank-' . $i;
			?>
			<div id="<?php echo $id; ?>"></div>
			<?php
		}
		?>
		</div>
		<?php
	}
	private function html_map() {
		?>
		<div class="block">
			<div id="map" class="map" style="height: 600px;"></div>
      		</div>
		<?php
	}
	private function enqueue_scripts() {
		$assets = array();
                $assets = wp_parse_args(
                        $assets,
                        array(
                                'src' => map_wpcf7_plugin_url( 'includes/js/shortcode.js' ),
                                'dependencies' => array( 'jquery' ),
                                'version' => MAPS_FOR_CF7_VERSION,
                                'in_footer' => ( 'header' !== wpcf7_load_js() ),
			) );
		wp_register_script(
			'maps-for-contact-form-7-shortcode',
                        $assets['src'],
                        $assets['dependencies'],
                        $assets['version'],
                        $assets['in_footer']
                );
		wp_localize_script(
                        'maps-for-contact-form-7-shortcode',
                        'mapContactForm7ShortcodeAjax',
                        array(
				'url' => admin_url( 'admin-ajax.php' )
                        ) );
		wp_enqueue_script( 'maps-for-contact-form-7-shortcode' );

		$options = MAPS_FOR_CF7_Options::get_instance();
                $setting = $options->get_option();
		$api_key = $setting[ MAPS_FOR_CF7_Options::api_key ];

		$assets = array();
                $assets = wp_parse_args(
                        $assets,
                        array(
                                'src' => "https://maps.googleapis.com/maps/api/js?key={$api_key}&libraries=places&callback=maps_for_contact_form_7_initialize",
                                'dependencies' => array(),
                                'version' => MAPS_FOR_CF7_VERSION,
                                'in_footer' => ( 'header' !== wpcf7_load_js() ),
			) );
		wp_register_script(
			'maps-for-contact-form-7-shortcode-places',
                        $assets['src'],
                        $assets['dependencies'],
                        $assets['version'],
                        $assets['in_footer']
                );
		add_action( 'script_loader_tag', function($tag, $handle){
			if ($handle === 'maps-for-contact-form-7-shortcode-places') {
				return str_replace(' src=', " async defer src=", $tag);
			}
			return $tag;
		}, 10, 2 );
		wp_enqueue_script( 'maps-for-contact-form-7-shortcode-places' );
	}
}

