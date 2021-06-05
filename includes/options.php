<?php

class MAPS_FOR_CF7_Options {
	const option_group = 'maps-for-cf7';
	const option_name = 'maps-for-cf7';
	const section_id = 'maps-for-cf7-section';
	const api_key_field_id = 'maps-for-cf7-api-key-field';
	const api_key = 'API_KEY';
	const language_field_id = 'maps-for-cf7-language-field';
	const language = 'language';
	const region_field_id = 'maps-for-cf7-region-field';
	const region = 'region';
	const form_ids_field_id = 'maps-for-cf7-form-ids-field';
	const form_ids = 'form_ids';
	const num_ranks_field_id = 'maps-for-cf7-num_ranks-field';
	const num_ranks = 'num_ranks';

	private static $languages = array( 'ja' );
	private	static $regions = array( 'jp' );

	private static $instance;

	private function __construct() {
	}
	public static function get_instance() {
                if ( empty( self::$instance ) ) {
                        self::$instance = new self;
                }

                return self::$instance;
        }
	public function delete_option() {
		delete_option( self::option_name );
	}
	public function get_option() {
		return get_option(
			self::option_name,
			array( 
				self::form_ids => array(),
				self::num_ranks => 3,
				self::language => 'ja',
				self::region => 'jp',
			) );
	}
	public function register_setting() {
		register_setting(
			self::option_group,
			self::option_name );
		add_settings_section(
                	self::section_id,
                	'',
                	'',
                	self::option_group );

		/* API_KEY */
		add_settings_field(
			self::api_key_field_id,
			'API KEY',
			array( $this, 'output_api_key_field' ),
			self::option_group,
			self::section_id );
		add_settings_field(
			self::language_field_id,
			'language',
			array( $this, 'output_language_field' ),
			self::option_group,
			self::section_id );
		add_settings_field(
			self::region_field_id,
			'region',
			array( $this, 'output_region_field' ),
			self::option_group,
			self::section_id );

		/* POSTS */
		add_settings_field(
			self::form_ids_field_id,
			'Posts',
			array( $this, 'output_form_ids_field' ),
			self::option_group,
			self::section_id );
		/* NUM RANKS */
		add_settings_field(
			self::num_ranks_field_id,
			'NumRanks',
			array( $this, 'output_num_ranks_field' ),
			self::option_group,
			self::section_id );
	}
	public function output_api_key_field( $args ) {
		$settings = $this->get_option();
		?>
		<input type="text" id="<?php echo self::api_key_field_id; ?>" name="<?php echo self::option_name; ?>[<?php echo self::api_key; ?>]" value="<?php esc_attr_e( $settings[ self::api_key ] ) ?>" />
		<?php
	}
	public function output_language_field( $args ) {
		$settings = $this->get_option();
		?>
		<select type="text" id="<?php echo self::language_field_id; ?>" name="<?php echo self::option_name; ?>[<?php echo self::language; ?>]" value="<?php esc_attr_e( $settings[ self::language ] ) ?>" />
		<?php
		foreach ( self::$languages as $language ) {
			$selected = ( $language == $settings[ self::language ] ) ?
			'selected' : '';
			?>
			<option value="<?php echo $language; ?>" <?php echo $selected; ?>><?php esc_attr_e( $language ); ?></option>
			<?php
		}
		$language = $settings[ self::language ];
		if ( !in_array( $language, self::$languages ) ) {
			?>
			<option value="<?php esc_attr_e( $language ); ?>" selected><?php esc_attr_e( $language ); ?></option>
			<?php
		}
		?>
		</select>
		<input type="text" class="maps-for-cf7-option-add" >
		<button>
        		<?php esc_html_e( __( 'add', 'maps-for-contact-form-7' ) ); ?>
        	</button>
		<?php
		require_once MAPS_FOR_CF7_PLUGIN_DIR . '/includes/add-option.php';
	}
	public function output_region_field( $args ) {
		$settings = $this->get_option();
		?>
		<select type="text" id="<?php echo self::region_field_id; ?>" name="<?php echo self::option_name; ?>[<?php echo self::region; ?>]" value="<?php esc_attr_e( $settings[ self::region ] ) ?>" />
		<?php
		foreach ( self::$regions as $region ) {
			$selected = ( $region == $settings[ self::region ] ) ?
			 'selected' : '';
			?>
			<option value="<?php echo $region; ?>" <?php echo $selected; ?>><?php esc_attr_e( $region ); ?></option>
			<?php
		}
		$region = $settings[ self::region ];
		if ( !in_array( $region, self::$regions ) ) {
			?>
			<option value="<?php esc_attr_e( $region ); ?>" selected><?php esc_attr_e( $region ); ?></option>
			<?php
		}
		?>
		</select>
		<input type="text" class="maps-for-cf7-option-add" >
		<button>
        		<?php esc_html_e( __( 'add', 'maps-for-contact-form-7' ) ); ?>
        	</button>
		<?php
		require_once MAPS_FOR_CF7_PLUGIN_DIR . '/includes/add-option.php';
	}
	public function output_form_ids_field( $args ) {
		$contact_forms = WPCF7_ContactForm::find();
		$settings = $this->get_option();

		$name = self::option_name . '[' . self::form_ids . '][]';
		$targets = $settings[ self::form_ids ];
		if ( empty( $targets ) ) $targets = array();

		$candidates = array();
		foreach ( $contact_forms as $contact_form ) {
			$tags = $contact_form->scan_form_tags();
			if ( !MAPS_FOR_CF7_ContactForm::has_place( $tags ) ) {
				continue;
			}
			$candidates[] = array( 
				'value' => $contact_form->id(),
				'label' => $contact_form->title()
			);
		}
		$selectValues = new MAPS_FOR_CF7_SelectValues(
			$name,
			$candidates,
			$targets,
			__( 'Candidate Forms', 'maps-for-contact-form-7' ),
			__( 'Target Forms', 'maps-for-contact-form-7' ) );
		$id = 'maps-for-cf7-option-form-ids';
		echo '<div id="' . $id . '">';
		$selectValues->html( "#{$id}" );
		echo '</div>';
	}
	public function output_num_ranks_field( $args ) {
		$settings = $this->get_option();
		?>
		<input type="number" id="<?php echo self::num_ranks_field_id; ?>" name="<?php echo self::option_name; ?>[<?php echo self::num_ranks; ?>]" value="<?php esc_attr_e( $settings[ self::num_ranks ] ) ?>" />
		<?php
	}
	public function add_options_page() {
		add_options_page(
		 	//ページタイトル
                	__( 'Settings of Maps for Contact Form 7', 'maps-for-contact-form-7' ),
                	//設定メニューに表示されるメニュータイトル
                	__( 'Maps for Contact Form 7', 'maps-for-contact-form-7' ),
                	//権限
                	'administrator',
                	//設定ページのURL。options-general.php?page=sample_setup_page
                	'maps-for-cf7-settings',
                	//設定ページのHTMLをはき出す関数の定義
                	array( $this, 'output' )
        	);
	}
	public function output() {
		?>
		<div class="wrap">
			<?php
			require_once(ABSPATH . 'wp-admin/options-head.php');
			?>
			<h1><?php esc_html_e( $GLOBALS['title'] ); ?></h1>
			<form method="post" action="options.php">
				<?php
				wp_title();
				settings_fields( self::option_group );
				// 入力項目を出力します(設定ページのslugを指定)>。
				do_settings_sections( self::option_group );
				// 送信ボタンを出力します。
				submit_button();
				?>
			</form>
		</div>
		<?php
	}
}

