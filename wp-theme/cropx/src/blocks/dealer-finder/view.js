/* global mapboxgl */
/**
 * Dealer Finder — front-end view script.
 *
 * Depends on:
 *   - mapboxgl (global, loaded from Mapbox CDN before this script)
 *   - window.cropxDealerFinder (injected by render.php inline <script>)
 *
 * Search modes:
 *   1. Zip code — geocoded via Mapbox, 100-mile radius, results sorted nearest→farthest
 *   2. Current location — browser Geolocation API, 100-mile radius, sorted nearest→farthest
 */

( function () {
	'use strict';

	// ── Guard ─────────────────────────────────────────────────────────────────
	if ( typeof mapboxgl === 'undefined' ) {
		console.warn( 'CropX Dealer Finder: mapboxgl not loaded.' );
		return;
	}

	const cfg = window.cropxDealerFinder;
	if ( ! cfg || ! cfg.token ) {
		console.warn( 'CropX Dealer Finder: no Mapbox token configured.' );
		return;
	}

	// ── Haversine distance (miles) ────────────────────────────────────────────
	function distanceMi( lat1, lon1, lat2, lon2 ) {
		const R    = 3958.8;
		const dLat = ( lat2 - lat1 ) * Math.PI / 180;
		const dLon = ( lon2 - lon1 ) * Math.PI / 180;
		const a    = Math.sin( dLat / 2 ) ** 2 +
		             Math.cos( lat1 * Math.PI / 180 ) *
		             Math.cos( lat2 * Math.PI / 180 ) *
		             Math.sin( dLon / 2 ) ** 2;
		return R * 2 * Math.atan2( Math.sqrt( a ), Math.sqrt( 1 - a ) );
	}

	// ── Minimal HTML escaping ─────────────────────────────────────────────────
	function esc( str ) {
		if ( ! str ) return '';
		return String( str )
			.replace( /&/g, '&amp;' )
			.replace( /</g, '&lt;' )
			.replace( />/g, '&gt;' )
			.replace( /"/g, '&quot;' );
	}

	// ── Convert dealer array → GeoJSON FeatureCollection ─────────────────────
	function toGeoJSON( dealers ) {
		return {
			type: 'FeatureCollection',
			features: dealers.map( function ( d ) {
				return {
					type: 'Feature',
					geometry: { type: 'Point', coordinates: [ d.lng, d.lat ] },
					properties: {
						id:      d.id,
						title:   d.title,
						address: d.address,
						phone:   d.phone,
						email:   d.email,
						website: d.website,
						region:  d.region,
					},
				};
			} ),
		};
	}

	// ── One instance per .df-block on the page ────────────────────────────────
	document.querySelectorAll( '.df-block' ).forEach( function ( container ) {

		var mapEl     = container.querySelector( '.df-map' );
		var listEl    = container.querySelector( '.df-list' );
		var metaEl    = container.querySelector( '.df-results-meta' );
		var zipInput  = container.querySelector( '.df-zip-input' );
		var searchBtn = container.querySelector( '.df-search-btn' );
		var clearBtn  = container.querySelector( '.df-search-clear' );
		var locBtn    = container.querySelector( '.df-location-btn' );
		var locLabel  = locBtn ? locBtn.querySelector( '.df-location-label' ) : null;

		if ( ! mapEl ) return;

		// ── State ─────────────────────────────────────────────────────────────
		var allDealers   = [];
		var popup        = null;
		var activeId     = null;
		var searchMarker = null; // single Mapbox Marker for the search location

		// ── Map ───────────────────────────────────────────────────────────────
		mapboxgl.accessToken = cfg.token;

		var map = new mapboxgl.Map( {
			container:           mapEl,
			style:               'mapbox://styles/lhcropx/cmqllpb3d005z01rgc4qt2mvk',
			center:              [ cfg.defaultLng || -96, cfg.defaultLat || 38.5 ],
			zoom:                cfg.defaultZoom  || 4,
			cooperativeGestures: true,
			attributionControl:  false,
		} );

		map.addControl( new mapboxgl.AttributionControl( { compact: true } ) );
		map.addControl( new mapboxgl.NavigationControl( { showCompass: false } ), 'top-right' );
		map.once( 'load', function () { map.resize(); } );

		map.on( 'error', function ( e ) {
			console.error( 'CropX Dealer Finder map error:', e.error );
		} );

		// ── Fetch dealers ─────────────────────────────────────────────────────
		fetch( cfg.apiUrl )
			.then( function ( r ) {
				if ( ! r.ok ) throw new Error( 'HTTP ' + r.status );
				return r.json();
			} )
			.then( function ( data ) {
				allDealers = data.filter( function ( d ) { return d.lat && d.lng; } );

				if ( ! allDealers.length ) {
					listEl.innerHTML   = '<li class="df-item df-item--empty">No dealer locations with coordinates found.</li>';
					metaEl.textContent = '';
					return;
				}

				metaEl.textContent = allDealers.length + ' dealer' + ( allDealers.length !== 1 ? 's' : '' ) + ' nationwide';
				renderList( allDealers );

				if ( map.isStyleLoaded() ) {
					addMapLayers( allDealers );
				} else {
					map.on( 'load', function onLoad() {
						map.off( 'load', onLoad );
						addMapLayers( allDealers );
						map.resize();
					} );
				}
			} )
			.catch( function ( err ) {
				listEl.innerHTML = '<li class="df-item df-item--empty">Unable to load dealer data.</li>';
				console.error( 'CropX Dealer Finder fetch error:', err );
			} );

		// ── Map layers ────────────────────────────────────────────────────────
		function addMapLayers( dealers ) {
			if ( map.getSource( 'dealers' ) ) return;

			map.addSource( 'dealers', {
				type: 'geojson', data: toGeoJSON( dealers ),
				cluster: true, clusterMaxZoom: 10, clusterRadius: 50,
			} );

			map.addLayer( {
				id: 'df-cluster-halo', type: 'circle', source: 'dealers',
				filter: [ 'has', 'point_count' ],
				paint: {
					'circle-color': 'rgba(12, 168, 192, 0.18)',
					'circle-radius': [ 'step', [ 'get', 'point_count' ], 28, 10, 38, 50, 48 ],
				},
			} );

			map.addLayer( {
				id: 'df-clusters', type: 'circle', source: 'dealers',
				filter: [ 'has', 'point_count' ],
				paint: {
					'circle-color': '#0CA8C0',
					'circle-radius': [ 'step', [ 'get', 'point_count' ], 18, 10, 24, 50, 32 ],
					'circle-stroke-width': 2, 'circle-stroke-color': '#fff',
				},
			} );

			map.addLayer( {
				id: 'df-cluster-count', type: 'symbol', source: 'dealers',
				filter: [ 'has', 'point_count' ],
				layout: {
					'text-field': '{point_count_abbreviated}',
					'text-font': [ 'DIN Offc Pro Medium', 'Arial Unicode MS Bold' ],
					'text-size': 12,
				},
				paint: { 'text-color': '#fff' },
			} );

			map.addLayer( {
				id: 'df-points', type: 'circle', source: 'dealers',
				filter: [ '!', [ 'has', 'point_count' ] ],
				paint: {
					'circle-radius': 8, 'circle-color': '#243565',
					'circle-stroke-width': 2.5, 'circle-stroke-color': '#fff',
					'circle-stroke-opacity': 1,
				},
			} );

			// Cluster → zoom in
			map.on( 'click', 'df-clusters', function ( e ) {
				var features  = map.queryRenderedFeatures( e.point, { layers: [ 'df-clusters' ] } );
				if ( ! features.length ) return;
				var clusterId = features[ 0 ].properties.cluster_id;
				map.getSource( 'dealers' ).getClusterExpansionZoom( clusterId, function ( err, zoom ) {
					if ( err ) return;
					map.easeTo( { center: features[ 0 ].geometry.coordinates, zoom: zoom } );
				} );
			} );

			// Individual pin → activate
			map.on( 'click', 'df-points', function ( e ) {
				var feature = e.features[ 0 ];
				var coords  = feature.geometry.coordinates.slice();
				var id      = Number( feature.properties.id );
				while ( Math.abs( e.lngLat.lng - coords[ 0 ] ) > 180 ) {
					coords[ 0 ] += e.lngLat.lng > coords[ 0 ] ? 360 : -360;
				}
				activateDealer( id, coords, false );
			} );

			[ 'df-clusters', 'df-cluster-halo', 'df-points' ].forEach( function ( layer ) {
				map.on( 'mouseenter', layer, function () { map.getCanvas().style.cursor = 'pointer'; } );
				map.on( 'mouseleave', layer, function () { map.getCanvas().style.cursor = ''; } );
			} );
		}

		// ── Activate a dealer ─────────────────────────────────────────────────
		//
		// shouldFly=true  (sidebar click): flyTo with zoom + offset
		// shouldFly=false (pin click):     easeTo in-place with offset
		//
		// offset [0, 120] places the pin 120 px below map center so the popup
		// card that appears above it is vertically centered in the visible area.
		function activateDealer( id, coords, shouldFly ) {
			activeId   = id;
			var dealer = allDealers.find( function ( d ) { return d.id === id; } );
			if ( ! dealer ) return;

			if ( map.getLayer( 'df-points' ) ) {
				map.setPaintProperty( 'df-points', 'circle-color', [
					'case', [ '==', [ 'get', 'id' ], dealer.id ],
					'#E48C4D', '#243565',
				] );
			}

			listEl.querySelectorAll( '.df-item' ).forEach( function ( el ) {
				el.classList.toggle( 'is-active', Number( el.dataset.id ) === dealer.id );
			} );
			var activeEl = listEl.querySelector( '.df-item.is-active' );
			if ( activeEl ) activeEl.scrollIntoView( { block: 'nearest', behavior: 'smooth' } );

			var dirUrl = 'https://www.google.com/maps/dir/?api=1&destination='
				+ encodeURIComponent( dealer.address || dealer.title );
			var html = '<div class="df-popup">'
				+ '<strong class="df-popup-name">' + esc( dealer.title ) + '</strong>';
			if ( dealer.address ) html += '<p class="df-popup-addr">'   + esc( dealer.address ) + '</p>';
			if ( dealer.phone )   html += '<a href="tel:'     + esc( dealer.phone )   + '" class="df-popup-link">☎ ' + esc( dealer.phone ) + '</a>';
			if ( dealer.email )   html += '<a href="mailto:'  + esc( dealer.email )   + '" class="df-popup-link">✉ ' + esc( dealer.email ) + '</a>';
			if ( dealer.website ) html += '<a href="'         + esc( dealer.website ) + '" class="df-popup-link" target="_blank" rel="noopener noreferrer">🌐 Visit website</a>';
			if ( dealer.address ) html += '<a href="'         + esc( dirUrl )         + '" class="df-popup-link df-popup-dir" target="_blank" rel="noopener noreferrer">Get directions →</a>';
			html += '</div>';

			var lngLat = coords || [ dealer.lng, dealer.lat ];

			if ( popup ) popup.remove();
			// focusAfterOpen defaults to true in mapbox-gl — it auto-focuses the
			// first focusable element inside the popup (our close button, or a
			// tel:/mailto:/website link) as soon as it opens. Because the popup
			// lives inside the map canvas rather than the scrollable .df-list,
			// the browser's native "scroll focused element into view" has no
			// local container to target and scrolls the whole page instead —
			// the exact cause of the jump-to-top/bottom bug. Disabling it here
			// leaves keyboard Tab navigation into the popup unaffected; it only
			// stops the automatic focus-on-open.
			popup = new mapboxgl.Popup( { offset: 14, closeButton: true, maxWidth: '280px', focusAfterOpen: false } )
				.setLngLat( lngLat )
				.setHTML( html )
				.addTo( map );

			popup.on( 'close', function () {
				if ( activeId !== dealer.id ) return;
				clearActive();
			} );

			if ( shouldFly ) {
				map.flyTo( { center: lngLat, zoom: Math.max( map.getZoom(), 11 ), offset: [ 0, 120 ], speed: 1.4 } );
			} else {
				map.easeTo( { center: lngLat, offset: [ 0, 120 ], duration: 350 } );
			}
		}

		function clearActive() {
			activeId = null;
			if ( map.getLayer( 'df-points' ) ) {
				map.setPaintProperty( 'df-points', 'circle-color', '#243565' );
			}
			listEl.querySelectorAll( '.df-item' ).forEach( function ( el ) {
				el.classList.remove( 'is-active' );
			} );
		}

		// ── Render sidebar list ───────────────────────────────────────────────
		function renderList( items, hasDistances ) {
			if ( ! items.length ) {
				listEl.innerHTML = '';
				return;
			}

			var fragment = document.createDocumentFragment();

			items.forEach( function ( dealer ) {
				var li        = document.createElement( 'li' );
				li.className  = 'df-item';
				li.dataset.id = dealer.id;

				var distHTML   = ( hasDistances && dealer._dist !== undefined )
					? '<span class="df-item-dist">' + dealer._dist.toFixed( 1 ) + ' mi</span>'
					: '';
				var regionHTML = dealer.region
					? '<span class="df-item-region">' + esc( dealer.region ) + '</span>'
					: '';

				li.innerHTML = '<div class="df-item-body">'
					+ '<span class="df-item-name">'  + esc( dealer.title )   + '</span>'
					+ ( dealer.address ? '<span class="df-item-addr">' + esc( dealer.address ) + '</span>' : '' )
					+ regionHTML
					+ '</div>'
					+ distHTML;

				li.addEventListener( 'click', function () {
					activateDealer( dealer.id, [ dealer.lng, dealer.lat ], true );
				} );

				fragment.appendChild( li );
			} );

			listEl.innerHTML = '';
			listEl.appendChild( fragment );
		}

		// ── Apply search results ──────────────────────────────────────────────
		// Shared by zip search and geolocation — filters to 50 miles, sorts
		// nearest→farthest, updates the list + meta, and flies the map.
		function applySearchResults( lat, lng, locationLabel, isGeolocate ) {
			const RADIUS_MI = 100;

			var nearby = allDealers.map( function ( d ) {
				var dist = distanceMi( lat, lng, d.lat, d.lng );
				return dist <= RADIUS_MI ? Object.assign( {}, d, { _dist: dist } ) : null;
			} ).filter( Boolean ).sort( function ( a, b ) { return a._dist - b._dist; } );

			var count  = nearby.length;
			var plural = count !== 1 ? 's' : '';
			metaEl.textContent = count
				? count + ' dealer' + plural + ' within ' + RADIUS_MI + ' miles of ' + locationLabel
				: 'No dealers found within ' + RADIUS_MI + ' miles of ' + locationLabel + '.';

			renderList( nearby, true );

			// Search marker (skip for geolocation — position is implicit)
			if ( searchMarker ) { searchMarker.remove(); searchMarker = null; }
			if ( ! isGeolocate ) {
				var pinEl       = document.createElement( 'div' );
				pinEl.className = 'df-search-pin';
				searchMarker    = new mapboxgl.Marker( { element: pinEl } )
					.setLngLat( [ lng, lat ] )
					.addTo( map );
			}

			clearBtn.hidden = false;
			map.flyTo( { center: [ lng, lat ], zoom: 7, speed: 1.2 } );

			if ( popup ) { popup.remove(); popup = null; }
			clearActive();
		}

		// ── Reset to nationwide view ──────────────────────────────────────────
		function resetToAll() {
			if ( searchMarker ) { searchMarker.remove(); searchMarker = null; }
			zipInput.value  = '';
			clearBtn.hidden = true;
			metaEl.textContent = allDealers.length + ' dealer' + ( allDealers.length !== 1 ? 's' : '' ) + ' nationwide';
			renderList( allDealers, false );
			map.flyTo( { center: [ cfg.defaultLng || -96, cfg.defaultLat || 38.5 ], zoom: cfg.defaultZoom || 4, speed: 1.2 } );
			if ( popup ) { popup.remove(); popup = null; }
			clearActive();
		}

		// ── Zip code search ───────────────────────────────────────────────────
		function doZipSearch() {
			var zip = zipInput.value.trim();
			if ( ! zip ) return;

			metaEl.textContent = 'Searching…';
			searchBtn.disabled = true;

			var url = 'https://api.mapbox.com/geocoding/v5/mapbox.places/'
				+ encodeURIComponent( zip )
				+ '.json?country=us&types=postcode&limit=1&access_token=' + cfg.token;

			fetch( url )
				.then( function ( r ) { return r.json(); } )
				.then( function ( data ) {
					if ( ! data.features || ! data.features.length ) {
						metaEl.textContent = '“' + zip + '” not found. Check the zip code and try again.';
						return;
					}
					var f    = data.features[ 0 ];
					var lat  = f.center[ 1 ];
					var lng  = f.center[ 0 ];
					var name = f.place_name.split( ',' )[ 0 ]; // e.g. "58474"
					applySearchResults( lat, lng, name, false );
				} )
				.catch( function () {
					metaEl.textContent = 'Search failed. Please try again.';
				} )
				.finally( function () {
					searchBtn.disabled = false;
				} );
		}

		// ── Geolocation ───────────────────────────────────────────────────────
		if ( locBtn && ! navigator.geolocation ) {
			locBtn.hidden = true;
		}

		function doCurrentLocation() {
			if ( ! navigator.geolocation ) return;

			locBtn.disabled = true;
			if ( locLabel ) locLabel.textContent = 'Getting location…';
			metaEl.textContent = 'Getting your location…';

			navigator.geolocation.getCurrentPosition(
				function ( position ) {
					locBtn.disabled = false;
					if ( locLabel ) locLabel.textContent = 'Use current location';
					zipInput.value  = ''; // clear any typed zip when using geo
					applySearchResults( position.coords.latitude, position.coords.longitude, 'your location', true );
				},
				function ( err ) {
					locBtn.disabled = false;
					if ( locLabel ) locLabel.textContent = 'Use current location';
					metaEl.textContent = err.code === 1
						? 'Location access denied. Please enter a zip code.'
						: 'Unable to get your location. Please enter a zip code.';
				},
				{ timeout: 10000, maximumAge: 60000 }
			);
		}

		// ── Event wiring ──────────────────────────────────────────────────────
		searchBtn.addEventListener( 'click', doZipSearch );

		zipInput.addEventListener( 'keydown', function ( e ) {
			if ( e.key === 'Enter' ) {
				e.preventDefault();
				doZipSearch();
			}
		} );

		clearBtn.addEventListener( 'click', resetToAll );

		if ( locBtn ) locBtn.addEventListener( 'click', doCurrentLocation );

	} ); // end forEach .df-block

} )();
