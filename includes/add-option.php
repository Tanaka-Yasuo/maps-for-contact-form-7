<script type="text/javascript">
jQuery( function( $ ) {
    $( '.maps-for-cf7-option-add' ).each( function( index, element ) {
	var button = $( element ).next( 'button' );
	
	$( button ).on( 'click', function( e ) {
	    var select = $( element ).prev( 'select' );
	
	    $( select ).append( '<option value="' + $( element ).val() + '" selected>' + $( element ).val() + '</option>' );
	    e.preventDefault();
	} );
    } );
} );
</script>
