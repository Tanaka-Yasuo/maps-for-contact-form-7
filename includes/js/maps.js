function maps_for_contact_form_7_initialize() {
    jQuery( function($) { 
        // code of maps_for_contact_form_7_initialize
        if (navigator.geolocation) {
            // 現在地を取得
            navigator.geolocation.getCurrentPosition(
                function( position ) {
                    initPlace( 8, position.coords.latitude, position.coords.longitude );
                    initMap( 8, position.coords.latitude, position.coords.longitude );
                },
                function( error ) {
                    initPlace( 6, 35.709984, 139.810703 );
                    initMap( 6, 35.709984, 139.810703 );
                }
             );
        } else {
            initPlace( 6, 35.709984, 139.810703 );
            initMap( 6, 35.709984, 139.810703 );
        }

        // functions of place field
        function initPlace( zoom, lat, lng ) {
            $( 'input.maps-for-cf7-place' ).each( function( index, element ) {
                var map = new google.maps.Map(
                    $( element ).closest( 'p' ).next( '.maps-for-cf7-place-map' ).get(0),
                    {
                        zoom: zoom,
                        center: new google.maps.LatLng( lat, lng ),
                        gestureHandling: 'greedy',
                    }
                );
                var timeoutId;

                $( element ).on( 'keyup', resetPlaceList );
		$( element ).nextAll( 'select.place-type' ).on( 'change', resetPlaceList );

                function resetPlaceList() {
                    if ( timeoutId ) {
                        clearTimeout( timeoutId );
                    }
                    timeoutId = undefined;

                    var submit = $( element ).closest( 'form' ).find( 'input[type="submit"]' );

                    submit.prop( 'disabled', true );

                    var select = $( element ).nextAll( 'select.place' );

                    select.prop( 'disabled', true );
                    select.html( '' );
                    timeoutId = setTimeout( function() {
                        var service = new google.maps.places.PlacesService(map);
                        var query = [];
                        var val = $( element ).val();
			var data_reserved_query = $( element ).attr( 'data-reserved-query' );

			if ( val ) {
			    query.push( val );
			}
			if ( data_reserved_query ) {
			    query.push( data_reserved_query );
			}
			if ( query.length == 0 ) {
                            select.prop( 'disabled', false );
                            submit.prop( 'disabled', false );
			    return;
			}
			var request = {
                            query: query.join( ' ' ),
			};

			val = $( element ).nextAll( 'select.place-type' ).val();
			if ( val && val != 'other' ) {
			     request.type = val;
			}
                        service.textSearch(
			    request,
                            function ( results, status ) {
                                if ( status == google.maps.places.PlacesServiceStatus.OK ) {
                                    var html = '';
                                    var selected = 'selected';

                                    results.forEach( function( result ) {
                                            html += '<option value="' + result.place_id + ',' + encodeURIComponent( result.name )+ ',' + result.geometry.location.lat() + ',' + result.geometry.location.lng() + '" ' + selected + '>' + result.name + '</option>';
                                                selected = '';
                                    } );
                                    select.html( html );
                                }
                                select.prop( 'disabled', false );
                                submit.prop( 'disabled', false );
                            }
                        );
                    },
                    2 * 1000 );
                }
            } );
        }

        // functions of maps_for_contact_form_7 shortcode
        function initMap( zoom, lat, lng ) {
            $( '.maps-for-cf7-shortcode' ).each( function( index, shortcodeElement ) {
                var markers = [];

                var JAPAN_BOUNDS = {
                        north: 50.0,
                        south: 20.0,
                        west: 120.0,
                        east: 150.0,
                  };
                var map = new google.maps.Map(
                    $( shortcodeElement ).find( '.maps-for-cf7-shortcode-map' ).get( 0 ),
                    {
                        zoom: zoom,
                        center: new google.maps.LatLng( lat, lng ),
                        gestureHandling: 'greedy',
/*
                        restriction: {
                            latLngBounds: JAPAN_BOUNDS,
                            strictBounds: false,
                        },
*/
                    } );
                var timerId;
		var openingInfoWindow = false;

                map.addListener( 'idle', function() {
		    if ( openingInfoWindow ) return;

                    if ( !timerId ) {
                        timerId = setTimeout( function() {
                            timerId = undefined;
                            setMarkers( shortcodeElement, map );
                        },
                        800 );
                    } else {
                        console.log( 'timerId exists' );
                    }
                } );
                $( shortcodeElement ).find( 'input[type="checkbox"]' ).each( function( index, input ) {
                    $( input ).on( 'change', function() {
                        setMarkers( shortcodeElement, map );
                    } );
                } );

                function resetMarkers() {
                    for ( var i = 0; i < markers.length; ++i ) {
                        var marker = markers[ i ];

                        marker.setMap( null );
                    }
                           markers = [];
                }
                function setMarkers( shortcodeElement, map ) {
                    console.log( 'setMarkers' );
                    var query = {
                        bounds: map.getBounds().toJSON(),
                        form_id: $( shortcodeElement ).find( 'form' ).attr( 'data-form-id' ),
                        form: $( shortcodeElement ).find( 'form' ).serializeArray(),
                    };
                    var jqXHR = $.ajax({
                        type: 'GET',
                        url: mapsForContactForm7Shortcode.ajax_url,
                        dataType: 'json',
                        data: {
                            action: 'getmarkerinfos',
                            query: JSON.stringify( query ),
                        },
                    } )
                    .done( function( data, textStatus, jqXHR ) {
                        markerInfos = data;

                        resetMarkers();

                        for ( var i = 0; i < markerInfos.length; ++i ) {
                            markerInfo = markerInfos[ i ];

                            function register() {
                                var marker = new google.maps.Marker( {
                                    map: map,
                                    position: new google.maps.LatLng( markerInfo.lat, markerInfo.lng ),
                                    label: markerInfo.name + '(' + markerInfo.count + ')',
                                } ); 

                                markers.push( marker );

                                // 吹き出しの追加
                                var infoWindow = new google.maps.InfoWindow({
                                    content: '<div class="sample">' + JSON.stringify( markerInfo.taxonomies ) + '</div>' // 吹き出しに表示する内容
                                } );
                                google.maps.event.addListener(marker, 'click', function() {
                                    infoWindow.open(map, marker);
				    openingInfoWindow = true;
				    setTimeout( function() {
					openingInfoWindow = false;
					},
					800 );
                                });
                            }
			    register();
                        }
                        setRank( shortcodeElement, map );
                    } )
                    .fail( function( jqXHR, textStatus, errorThrown ) {
                    } );
                }
                function setRank( shortcodeElement, map ) {
                    var query = {
                        bounds: map.getBounds().toJSON(),
                        form_id: $( shortcodeElement ).find( 'form' ).attr( 'data-form-id' ),
                        form: $( shortcodeElement ).find( 'form' ).serializeArray(),
                    };
                    var jqXHR = $.ajax({
                        type: 'GET',
                        url: mapsForContactForm7Shortcode.ajax_url,
                        dataType: 'json',
                        data: {
                            action: 'getrank',
                            query: JSON.stringify( query ),
                        },
                    } )
                    .done( function( data, textStatus, jqXHR ) {
                        markerInfos = data;

                        setRankMarkerInfos( shortcodeElement, map, markerInfos );
                    } )
                    .fail( function( jqXHR, textStatus, errorThrown ) {
                    } );
                }
            } );
            function resetRankMarkerInfos( shortcodeElement ) {
                for ( var i = 0; i < 10; ++i ) {
                          var id = '#rank-' + ( i + 1 );
                          var element = $( shortcodeElement ).find( id );

                          if ( !element ) break;
                          element.html( '' );
                    }
            }
            function setRankMarkerInfos( shortcodeElement, map, markerInfos ) {
                markerInfos.sort( function( a, b ) {
                    if ( a.count < b.count ) {
                        return 1;
                    } else if ( a.count > b.count ) {
                        return -1;
                    }
                    return 0;
                });
                resetRankMarkerInfos( shortcodeElement );
                for ( var i = 0; i < markerInfos.length; ++i ) {
                    var markerInfo = markerInfos[ i ];
                    var id = '#rank-' + ( i + 1 );
                    var element = $( shortcodeElement).find( id );

                    if ( !element ) break;

		    function register() {
                    	var html = '<div>'
				+ markerInfo.name + '(' + markerInfo.count + ')'
			+ '</div>';

                    	html += '<input type="hidden" name="lat" value="' + markerInfo.lat + '">';
                    	html += '<input type="hidden" name="lng" value="' + markerInfo.lng + '">';
                    	element.html( html );
			$( element ).find( 'div' ).on( 'click', function() {
			     map.panTo( new google.maps.LatLng( markerInfo.lat, markerInfo.lng ) );
			     map.setZoom( 12 );
			} );
                        var service = new google.maps.places.PlacesService(map);

                        service.getDetails(
			    {
				placeId: markerInfo.placeId,
				fields: [ 'website' ],
			    },
                            function ( place, status ) {
                                if ( status == google.maps.places.PlacesServiceStatus.OK ) {
				    if ( place.website ) {
					$( element ).find( 'div' ).append( '<a href="' + place.website + '" target="_blank" rel="noopener">' + mapsForContactForm7Shortcode.homepage_label + '</a>' );
				    }
				}
			    }
			);
		    }
		    register();
                }
            }
        }
    } );
}

