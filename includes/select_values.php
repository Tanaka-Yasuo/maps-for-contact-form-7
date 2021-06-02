<?php

class MAPS_FOR_CF7_SelectValues {
	private $name;
	private $candidates = array();
	private $targets = array();
	private $candidate_label;
	private $target_label;
	private $classes = array();

	public function __construct( $name, $candidates = array(), $targets = array(), $candidate_label = '', $target_label = '', $classes = array() ) {
		$this->name = $name;
		$this->targets = $targets;
		$this->candidate_label = $candidate_label;
		$this->target_label = $target_label;
		$this->classes = $classes;

		foreach ( $candidates as $candidate ) {
			$this->candidates[] = array(
				'value' => esc_attr( $candidate[ 'value' ] ),
				'label' => esc_html( $candidate[ 'label' ] ),
			);
		}
		foreach ( $targets as $targets ) {
			$this->targets[] = esc_attr( $target );
		}
	}

	public function html( $selector ) {
		?>
		<p>
			<?php
			$classes = array( 'maps-for-cf7-candidates-label' );
			if ( in_array( 'candidates-label', $this->classes ) ) {
				$classes[] = $this->classes[ 'candidates-label' ];
			}
			?>
			<label class="<?php esc_attr_e( implode( ' ', $classes ) ); ?>">
 				<?php esc_html_e( $this->candidate_label ); ?>
			</label>
			<br/>
			<?php
			$classes = array( 'maps-for-cf7-candidate-list' );
			if ( in_array( 'candidate-list', $this->classes ) ) {
				$classes[] = $this->classes[ 'candidate-list' ];
			}
			?>
			<select class="<?php esc_attr_e( implode( ' ', $classes ) ); ?>">
			</select>
			<br/>
			<?php
			$classes = array( 'maps-for-cf7-add-candidate-button' );
			if ( in_array( 'add-candidate-button', $this->classes ) ) {
				$classes[] = $this->classes[ 'add-candidate-button' ];
			}
			?>
			<button class="<?php esc_attr_e( implode( ' ', $classes ) ); ?>">
 			<?php esc_html_e( __( 'add', 'maps-for-contact-form-7' ) ); ?>
			</button>
		</p>
		<p>
			<?php
			$classes = array( 'maps-for-cf7-targets-label' );
			if ( in_array( 'targets-label', $this->classes ) ) {
				$classes[] = $this->classes[ 'targets-label' ];
			}
			?>
			<label class="<?php esc_attr_e( implode( ' ', $classes ) ); ?>">
 				<?php esc_html_e( $this->target_label ); ?>
			</label>
			<?php
			$classes = array( 'maps-for-cf7-target-list' );
			if ( in_array( 'target-list', $this->classes ) ) {
				$classes[] = $this->classes[ 'target-list' ];
			}
			?>
			<div class="<?php esc_attr_e( implode( ' ', $classes ) ); ?>">
			</div>
		</p>
<script type="text/javascript">
	jQuery( function( $ ) {
		function getSelectableCandidates( candidates, targets ) {
			var results = [];

			candidates.forEach( function( candidate ) {
				for ( var target of targets ) {
					if ( target == candidate.value ) return;
				}
				results.push( candidate );
			} );
			return results;
		}
		function getTargetCandidates( candidates, targets ) {
			var results = [];

			targets.forEach( function( target ) {
				for ( var candidate of candidates ) {
					if ( target == candidate.value ) {
						results.push( candidate );
						return;
					}
				}
			} );
			return results;
		}
		function resetPosts() {
			var selectableCandidates = getSelectableCandidates( candidates, targets );
			var selected = 'selected';
			var html = '';

			selectableCandidates.forEach( function( candidate ) {
				html += '<option value="' + candidate.value + '" ' + selected + '>' + candidate.label + '</option>';
				selected = '';

			} );

			$( selector ).find( '.maps-for-cf7-candidate-list' ).html( html );

			var targetCandidates = getTargetCandidates( candidates, targets );

			html = '';
			targetCandidates.forEach( function( candidate ) {
				<?php 
				$classes = array( 'maps-for-cf7-target-label' );
				if ( in_array( 'target-label', $this->classes ) ) {
					$classes[] = $this->classes[ 'target-label' ];
				}
				?>
				html += '<label class="<?php esc_attr_e( implode( ' ', $classes ) ); ?>">' + candidate.label + '</label>';
				<?php 
				$classes = array( 'maps-for-cf7-remove-target-button' );
				if ( in_array( 'remove-target-button', $this->classes ) ) {
					$classes[] = $this->classes[ 'remove-target-button' ];
				}
				?>
		 		html += '<button class="<?php esc_attr_e( implode( ' ', $classes ) ); ?>">';
		 		html += "<?php echo esc_html( __( 'remove', 'maps-for-contact-form-7' ) ); ?>";
		 		html += '</button>';
		 		html += '<input type="hidden" value="' + candidate.value + '" name="<?php esc_attr_e( $this->name ); ?>" >';
		 		html += '</input>';
		 		html += '<br/>';
			} );
			$( selector ).find( '.maps-for-cf7-target-list' ).html( html );
			$( selector ).find( 'button.maps-for-cf7-remove-target-button' ).on( 'click', function(e) {
			var val = $( e.target ).nextAll( 'input' ).val();

			targets.splice(targets.indexOf( val ), 1);
			resetPosts();
			e.preventDefault();
		} );
		}
		var candidates = <?php echo json_encode( $this->candidates, JSON_UNESCAPED_UNICODE ) ?>;
		var targets = <?php echo json_encode( $this->targets, JSON_UNESCAPED_UNICODE ) ?>;
		var selector = '<?php esc_attr_e( $selector ); ?>';

		candidates.forEach( function( candidate ) {
			candidate.label = decodeURIComponent( candidate.label );
		} );
		resetPosts();
		$( selector ).find( '.maps-for-cf7-add-candidate-button' ).on( 'click', function(e) {
			var val = $( selector ).find( '.maps-for-cf7-candidate-list' ).val();

			targets.push( val );
			resetPosts();
			e.preventDefault();
		} );
	} );
</script>
	<?php
	}
}
