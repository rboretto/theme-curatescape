<?php
	
/*
** Relabel Search Record Types
*/
add_filter('search_record_types', 'mh_search_record_types');
function mh_search_record_types($recordTypes)
{
    if(plugin_is_active('SimplePages')) $recordTypes['SimplePagesPage'] = __('Page');
    $recordTypes['Item'] = mh_item_label('singular');
    if(plugin_is_active('TourBuilder','1.6','>=')) $recordTypes['Tour'] = mh_tour_label('singular');
    return $recordTypes;
}

/*
** Set Default Search Record Types
*/
add_filter('search_form_default_record_types', 'mh_search_form_default_record_types');
function mh_search_form_default_record_types()
{
	$recordTypes=array();
    $recordTypes[]='Item';
    if(plugin_is_active('TourBuilder','1.6','>=') && get_theme_option('default_tour_search')) $recordTypes[]='Tour';
	if(plugin_is_active('SimplePages') && get_theme_option('default_page_search')) $recordTypes[]='SimplePagesPage';
	if(get_theme_option('default_file_search')) $recordTypes[]='File';
    return $recordTypes;
}	
	
/*
** SEO Page Description
*/
function mh_seo_pagedesc($item=null,$tour=null,$file=null){
	if($item != null){
		$itemdesc=snippet(mh_the_text($item),0,500,"...");
		return htmlspecialchars(strip_tags($itemdesc));
	}elseif($tour != null){
		$tourdesc=snippet(tour('Description'),0,500,"...");
		return htmlspecialchars(strip_tags($tourdesc));
	}elseif($file != null){
		$filedesc=snippet(metadata('file',array('Dublin Core', 'Description')),0,500,"...");
		return htmlspecialchars(strip_tags($filedesc));
	}else{
		return mh_seo_sitedesc();
	}
}

/* 
** SEO Site Description
*/
function mh_seo_sitedesc(){
	return mh_about() ? strip_tags(mh_about()) : strip_tags(option('description'));
}

/* 
** SEO Page Title
*/
function mh_seo_pagetitle($title,$item){
	$subtitle=$item ? (mh_the_subtitle($item) ? ' - '.mh_the_subtitle($item) : null) : null;
	$pt = $title ? $title.$subtitle.' | '.option('site_title') : option('site_title');
	return strip_tags($pt);
}

/* 
** SEO Page Image
*/
function mh_seo_pageimg($item=null,$file=null){
	if($item){
		if(metadata($item, 'has thumbnail')){
			$itemimg=item_image('fullsize') ;
			preg_match('/<img(.*)src(.*)=(.*)"(.*)"/U', $itemimg, $result);
			$itemimg=array_pop($result);
		}
	}elseif($file){
		if($itemimg=file_image('fullsize') ){
			preg_match('/<img(.*)src(.*)=(.*)"(.*)"/U', $itemimg, $result);
			$itemimg=array_pop($result);
		}
	}
	return isset($itemimg) ? $itemimg : mh_seo_pageimg_custom();
}

/* 
** SEO Site Image
*/
function mh_seo_pageimg_custom(){
	$custom_img = get_theme_option('custom_meta_img');
	$custom_img_url = $custom_img ? WEB_ROOT.'/files/theme_uploads/'.$custom_img : mh_the_logo_url();	
	return $custom_img_url;
}

/* 
** Get theme CSS link with version number
*/
function mh_theme_css($media='all'){
	$themeName = Theme::getCurrentThemeName();
	$theme = Theme::getTheme($themeName);
	return '<link href="'.WEB_PUBLIC_THEME.'/'.$themeName.'/css/screen.css?v='.$theme->version.'" media="'.$media.'" rel="stylesheet" type="text/css" >';
}


/* 
** Custom Label for Items/Stories
*/
function mh_item_label($which=null){
	if($which=='singular'){
		return ($singular=get_theme_option('item_label_singular')) ? $singular : __('Story');
	}
	elseif($which=='plural'){
		return ($plural=get_theme_option('item_label_plural')) ? $plural : __('Stories');
	}else{
		return __('Story');
	}
}

/* 
** Custom Label for Tours
*/
function mh_tour_label($which=null){
	if($which=='singular'){
		return ($singular=get_theme_option('tour_label_singular')) ? $singular : __('Tour');
	}
	elseif($which=='plural'){
		return ($plural=get_theme_option('tour_label_plural')) ? $plural : __('Tours');
	}else{
		return __('Tour');
	}
}

/* 
** Tour Header on Homepage
*/
function mh_tour_header(){
	if($text=get_theme_option('tour_header')){
		return $text;
	}else{
		return __('Take a %s', mh_tour_label('singular'));
	}
}

/*
** Global navigation
*/
function mh_global_nav(){
	$curatenav=get_theme_option('default_nav');
	if( $curatenav==1 || !isset($curatenav) ){
		return nav(array(
				array('label'=>__('Home'),'uri' => url('/')),
				array('label'=>mh_item_label('plural'),'uri' => url('items/browse')),
				array('label'=>mh_tour_label('plural'),'uri' => url('tours/browse/')),
				array('label'=>__('About'),'uri' => url('about/')),
			));
	}else{
		return public_nav_main();
	}
}

/*
** Subnavigation for items/browse
*/
function mh_item_browse_subnav(){
	echo nav(array(
			array('label'=>__('All') ,'uri'=> url('items/browse')),
			array('label'=>__('Tags'), 'uri'=> url('items/tags')),
			array('label'=>__('%s Search', mh_item_label('singular')), 'uri'=> url('items/search')),
			array('label'=>__('Sitewide Search'), 'uri'=> url('search')),
		));
}


/*
** Subnavigation for collections/browse
*/

function mh_collection_browse_subnav(){
	echo nav(array(
			array('label'=>__('All') ,'uri'=> url('collections/browse')),
		));
}

function mh_tour_browse_subnav($label,$id){
	echo nav(array(
			array('label'=>__('Locations for %s', $label) ,'uri'=> url('tours/show/'.$id)),
		));	
}

/*
** Logo URL
*/
function mh_the_logo_url()
{
	$logo = get_theme_option('lg_logo');
	$logo_url = $logo ? WEB_ROOT.'/files/theme_uploads/'.$logo : img('hm-logo.png');
	return $logo_url;
}

/*
** Logo IMG Tag
*/
function mh_the_logo(){
	return '<img src="'.mh_the_logo_url().'" alt="'.option('site_title').'"/>';
}

/*
** Link to Random Item
*/
function random_item_link($text=null,$class='show',$hasImage=true){

	if(!$text){
		$text= __('View a Random %s', mh_item_label('singular'));
	}
	$randitems = get_records('Item', array( 'sort_field' => 'random', 'hasImage' => $hasImage), 1);

	if( count( $randitems ) > 0 ){
		$link = link_to( $randitems[0], 'show', $text, array( 'class' => 'random-story-link ' . $class ) );
	}else{
		$link = link_to( '/', 'show', __('Publish some items to activate this link'),
			array( 'class' => 'random-story-link ' . $class ) );
	}
	return $link;
}


/*
** Global header
*/
function mh_global_header($html=null){
?>  
<div id="navigation">
	<nav>
		<?php echo link_to_home_page(mh_the_logo(),array('id'=>'home-logo'));?>
		<span class="spacer"></span>
		<span class="flex flex-end flex-grow flex-nav-container">
			<span class="flex priority">
	  			<a href="<?php echo url('/items/browse/');?>" class="button button-primary"><?php echo mh_item_label('plural');?></a>
	  			<?php if(plugin_is_active('TourBuilder')): ?>
	  				<a href="<?php echo url('/tours/browse/');?>" class="button button-primary"><?php echo mh_tour_label('plural');?></a>
	  			<?php endif;?>
			</span>
			<span class="flex search-plus flex-grow">
  			<!--input class="nav-search u-full-width" type="search" placeholder="Search"-->
  			<?php echo mh_simple_search();?>
  			<a id="menu-button" href="#secondary-menu" class="button icon"><i class="fa fa-bars fa-lg" aria-hidden="true"></i></a>	
			</span>
		</span>
	</nav>
</div>
<?php
}


/*
** Single Tour JSON
*/
function mh_get_tour_json($tour=null){
			
	if($tour){
		$tourItems=array();
		foreach($tour->Items as $item){
			$location = get_db()->getTable( 'Location' )->findLocationByItem( $item, true );
			$address = ( element_exists('Item Type Metadata','Street Address') ) 
				? preg_replace( "/\r|\n/", "", strip_tags(metadata( $item, array( 'Item Type Metadata','Street Address' )) )) 
				: null;
			if($location && $item->public){
				$tourItems[] = array(
					'id'		=> $item->id,
					'title'		=> trim(addslashes(metadata($item,array('Dublin Core','Title')))),
					'address'	=> trim(html_entity_decode(strip_formatting(addslashes($address)))),
					'latitude'	=> $location[ 'latitude' ],
					'longitude'	=> $location[ 'longitude' ],
					);
				}
		}
		$tourMetadata = array(
		     'id'           => $tour->id,
		     'items'        => $tourItems,
		     );
		return json_encode($tourMetadata);
	
	}	
}


/*
** Single Item JSON	
*/
function mh_get_item_json($item=null){
			
	if($item){
		$location = get_db()->getTable( 'Location' )->findLocationByItem( $item, true );
		$address= ( element_exists('Item Type Metadata','Street Address') ) 
			? preg_replace( "/\r|\n/", "", strip_tags(metadata( 'item', array( 'Item Type Metadata','Street Address' )) ))  
			: null;
		$accessinfo= ( element_exists('Item Type Metadata','Access Information') && metadata($item, array('Item Type Metadata','Access Information')) ) ? true : false;
		$title=html_entity_decode( strip_formatting( metadata( 'item', array( 'Dublin Core', 'Title' ))));
		if(metadata($item, 'has thumbnail')){
			$thumbnail = (preg_match('/<img(.*)src(.*)=(.*)"(.*)"/U', item_image('square_thumbnail'), $result)) ? array_pop($result) : null;
		}else{ 
			$thumbnail=''; 
		}
		if($location){
			$itemMetadata = array(
				'id'          => $item->id,
				'featured'    => $item->featured,
				'latitude'    => $location[ 'latitude' ],
				'longitude'   => $location[ 'longitude' ],
				'title'       => trim(addslashes($title)),
				'address'	  => addslashes($address),
				'accessinfo'  => $accessinfo,
				'thumbnail'   => $thumbnail,
			);		
			return json_encode($itemMetadata);
		}	
	}	
}

/*
** Map Type
** Uses variable set in each page template via head() function
*/
function mh_map_type($maptype='none',$item=null,$tour=null){
	
	if ($maptype == 'focusarea') {
		return mh_display_map('focusarea',null,null);
	}
	elseif ($maptype == 'story') {
		return mh_display_map('story',$item,null,null);
	}
	elseif ($maptype == 'queryresults') {
		return mh_display_map('queryresults',null,null);
	}
	elseif ($maptype == 'tour') {
		return mh_display_map('tour',null,$tour);
	}
	elseif ($maptype == 'collection') {
		return mh_display_map('queryresults',null,null);
	}	
	elseif ($maptype == 'none') {
		return null;
	}
	else {
		return null;
	}
}


/*
** Render the map
** Source feeds generated from Mobile JSON plugin
** Location data (LatLon and Zoom) created and stored in Omeka using stock Geolocation plugin
*/
function mh_display_map($type=null,$item=null,$tour=null){
	$pluginlng=(get_option( 'geolocation_default_longitude' )) ? get_option( 'geolocation_default_longitude' ) : null;
	$pluginlat=(get_option( 'geolocation_default_latitude' )) ? get_option( 'geolocation_default_latitude' ) : null;
	$zoom=(get_option('geolocation_default_zoom_level')) ? get_option('geolocation_default_zoom_level') : 12;
	$color=get_theme_option('marker_color') ? get_theme_option('marker_color') : '#333333';
	$featured_color=get_theme_option('featured_marker_color') ? get_theme_option('featured_marker_color') : $color;
	switch($type){
		case 'focusarea':
			/* all stories, map is centered on focus area (plugin center) */
			$json_source=WEB_ROOT.'/items/browse?output=mobile-json';
			break;
	
		case 'global':
			/* all stories, map is bounded according to content */
			$json_source=WEB_ROOT.'/items/browse?output=mobile-json';
			break;
	
		case 'queryresults':
			/* browsing by tags, subjects, search results, etc, map is bounded according to content */
			$uri=WEB_ROOT.$_SERVER['REQUEST_URI'];
			$json_source=$uri.'&output=mobile-json';
			break;		
	
		case 'story':
			/* single story */
			$json_source = ($item) ? mh_get_item_json($item) : null;
			break;
	
		case 'tour':
			/* single tour, map is bounded according to content  */
			$json_source= ($tour) ? mh_get_tour_json($tour) : null;
			break;
	
		default:
			$json_source=WEB_ROOT.'/items/browse?output=mobile-json';
	}
	?>
	<script type="text/javascript">
		// PHP Variables
		var type =  '<?php echo $type ;?>';
		var color = '<?php echo $color ;?>';
		var featured_color = '<?php echo $featured_color ;?>';
		var root = '<?php echo WEB_ROOT ;?>';
		var source ='<?php echo $json_source ;?>';
		var center =[<?php echo $pluginlat.','.$pluginlng ;?>];
		var zoom = <?php echo $zoom ;?>;
		var defaultItemZoom=<?php echo get_theme_option('map_zoom_single') ? (int)get_theme_option('map_zoom_single') : 14;?>;
		var featuredStar = <?php echo get_theme_option('featured_marker_star');?>;
		var useClusters = <?php echo get_theme_option('clustering');?>; 
		var clusterTours = <?php echo get_theme_option('tour_clustering');?>; 
		var clusterIntensity = <?php echo get_theme_option('cluster_intensity') ? get_theme_option('cluster_intensity') : 15;?>; 
		var alwaysFit = <?php echo get_theme_option('fitbounds') ? get_theme_option('fitbounds') : 0;?>; 
		var markerSize = '<?php echo get_theme_option('marker_size') ? get_theme_option('marker_size') : "m";?>'; 
		var mapBounds; // keep track of changing bounds
		var root_url = '<?php echo WEB_ROOT;?>';
		var terrain = L.tileLayer('//stamen-tiles-{s}.a.ssl.fastly.net/terrain/{z}/{x}/{y}{retina}.jpg', {
				attribution: '<a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a> | Map Tiles by <a href="http://stamen.com/">Stamen Design</a>',
				retina: (L.Browser.retina) ? '@2x' : '',
			});		
		var carto = L.tileLayer('//cartodb-basemaps-{s}.global.ssl.fastly.net/light_all/{z}/{x}/{y}{retina}.png', {
		    	attribution: '<a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a> | <a href="https://cartodb.com/attributions">CartoDB</a>',
				retina: (L.Browser.retina) ? '@2x' : '',
			});
		var defaultMapLayer=<?php echo get_theme_option('map_style') ? strtolower(get_theme_option('map_style')) : 'carto';?>;		
		// End PHP Variables
		
		var isSecure = window.location.protocol == 'https:' ? true : false;
		function getChromeVersion () {  
			// Chrome v.50+ requires secure origins for geolocation   
		    var raw = navigator.userAgent.match(/Chrom(e|ium)\/([0-9]+)\./);
		    return raw ? parseInt(raw[2], 10) : 0; // return 0 for not-Chrome
		}
		function getSafariVersion () {  
			// Safari v.9.3+ requires secure origins for geolocation   
		    var raw = navigator.userAgent.match(/Safari\/([-+]?[0-9]*\.?[0-9]+)\./);
		    return raw ? parseFloat(raw[1]) : 0; // return 0 for not-Safari
		}		

		jQuery(document).ready(function() {

			if (
				(getChromeVersion()>=50 && !isSecure) || 
				(getSafariVersion()>=601.6 && !isSecure) || 
				!navigator.geolocation
				){
				/* Hide the geolocation button on insecure sites for... 
				** Safari 9.3+ users, Chrome 50+ users, and for browsers with no support
				** TODO: eventually, this will need to be applied to all insecure origins
				*/
				jQuery('.map-actions a.location').addClass('hidden');
			}	

			// Build the base map
			var map = L.map('curatescape-map-canvas',{
				layers: defaultMapLayer,
				minZoom: 3,
				scrollWheelZoom: false,
			}).setView(center, zoom);
			
			// Layer controls
			L.control.layers({
				"Terrain":terrain,
				"Street":carto,
			}).addTo(map);			
			
			// Center marker and popup on open
			map.on('popupopen', function(e) {
				// find the pixel location on the map where the popup anchor is
			    var px = map.project(e.popup._latlng); 
			    // find the height of the popup container, divide by 2, subtract from the Y axis of marker location
			    px.y -= e.popup._container.clientHeight/2;
			    // pan to new center
			    map.panTo(map.unproject(px),{animate: true}); 
			});				
			// Add Markers
			var addMarkers = function(data){				
		        function icon(color,markerInner){ 
			        return L.MakiMarkers.icon({
			        	icon: markerInner, 
						color: color, 
						size: markerSize,
						accessToken: "pk.eyJ1IjoiZWJlbGxlbXBpcmUiLCJhIjoiY2ludWdtOHprMTF3N3VnbHlzODYyNzh5cSJ9.w3AyewoHl8HpjEaOel52Eg"
			    		});	
			    }				
				if(typeof(data.items)!="undefined"){ // tours and other multi-item maps
					
					var group=[];
					if(useClusters==true){
						var markers = L.markerClusterGroup({
							zoomToBoundsOnClick:true,
							disableClusteringAtZoom: clusterIntensity,
							polygonOptions: {
								'stroke': false,
								'color': '#000',
								'fillOpacity': .1
							}
						});
					}
					
			        jQuery.each(data.items,function(i,item){
							
				        var address = item.address ? item.address : '';
						var c = (item.featured==1 && featured_color) ? featured_color : color;
						var inner = (item.featured==1 && featuredStar) ? "star" : "circle";
				        if(typeof(item.thumbnail)!="undefined"){
					        var image = '<a href="'+root_url+'/items/show/'+item.id+'" class="curatescape-infowindow-image '+(!item.thumbnail ? 'no-img' : '')+'" style="background-image:url('+item.thumbnail+');"></a>';
					    }else{
						    var image = '';
					    }
					    var number = (type=='tour') ? '<span class="number">'+(i+1)+'</span>' : '';
				        var html = image+number+'<span><a class="curatescape-infowindow-title" href="'+root_url+'/items/show/'+item.id+'">'+item.title+'</a><br>'+'<div class="curatescape-infowindow-address">'+address.replace(/(<([^>]+)>)/ig,"")+'</div></span>';
						
						
						var marker = L.marker([item.latitude,item.longitude],{icon: icon(c,inner)}).bindPopup(html);
						
						group.push(marker);  
						
						if(useClusters==true) markers.addLayer(marker);

			        });
			        
			        if(useClusters==true && type!=='tour' || type=='tour' && clusterTours==true){
				        map.addLayer(markers);
				        mapBounds = markers.getBounds();
				    }else{
			        	group=new L.featureGroup(group); 
						group.addTo(map);	
						mapBounds = group.getBounds();				    
				    }
			        
					// Fit map to markers as needed			        
			        if((type == 'queryresults'|| type == 'tour') || alwaysFit==true){
				        if(useClusters==true){
					        map.fitBounds(markers.getBounds());
					    }else{
						    map.fitBounds(group.getBounds());
					    }
			        }
			        
			        
				}else{ // single items
					map.setView([data.latitude,data.longitude],defaultItemZoom);	
			        var address = data.address ? data.address : data.latitude+','+data.longitude;
			        var accessInfo=(data.accessinfo === true) ? '<a class="access-anchor" href="#access-info"><span class="icon-exclamation-circle" aria-hidden="true"></span> Access Information</a>' : '';

			        var image = (typeof(data.thumbnail)!="undefined") ? '<a href="#item-media" class="curatescape-infowindow-image '+(!data.thumbnail ? 'no-img' : '')+'" style="background-image:url('+data.thumbnail+');" title="Skip to media files"></a>' : '';

			        var html = image+'<div class="curatescape-infowindow-address single-item"><span class="icon-map-marker" aria-hidden="true"></span> '+address.replace(/(<([^>]+)>)/ig,"")+accessInfo+'</div>';
					
					var marker = L.marker([data.latitude,data.longitude],{icon: icon(color,"circle")}).bindPopup(html);					
					
					marker.addTo(map).bindPopup(html);
					marker.openPopup();
					mapBounds = map.getBounds();
					
				}
				
			}		
			
			if(type=='story'){
				var data = jQuery.parseJSON(source);
				if(data){
					addMarkers(data);	
				}else{
					jQuery('#hero, .map-actions').hide();
				}
				
			}else if(type=='tour'){
				var data = jQuery.parseJSON(source);
				addMarkers(data);
				
			}else if(type=='focusarea'){
				jQuery.getJSON( source, function(data) {
					var data = data;
					addMarkers(data);
				});
				
			}else if(type=='queryresults'){
				jQuery.getJSON( source, function(data) {
					var data = data;
					addMarkers(data);
				});
				
			}else{
				jQuery.getJSON( source, function(data) {
					var data = data;
					addMarkers(data);
				});
			}

			/* Map Action Buttons */
			
			// Fullscreen
			jQuery('.map-actions .fullscreen').click(function(){
				jQuery('#slider').slideToggle('fast', 'linear');
				jQuery('#swipenav').slideToggle('fast', 'linear');	
				jQuery('.small #curatescape-map-canvas').toggle(); // in case it's hidden by checkwidth.js
				jQuery("body").toggleClass("fullscreen-map");
				jQuery(".map-actions a.fullscreen i").toggleClass('icon-expand').toggleClass('icon-compress');
				map.invalidateSize();
			});
			jQuery(document).keyup(function(e) {
				if ( e.keyCode == 27 ){ // exit fullscreen
					if(jQuery('body').hasClass('fullscreen-map')) jQuery('.map-actions .fullscreen').click();
				}
			});
			
			// Geolocation
			jQuery('.map-actions .location').click(
				function(){
				var options = {
					enableHighAccuracy: true,
					maximumAge: 30000,
					timeout: 15000
				};
				navigator.geolocation.getCurrentPosition(
					function(pos) {
						var userLocation = [pos.coords.latitude, pos.coords.longitude];					
						// adjust map view
						if(type=='story'|| type=='tour' || type == 'queryresults'){
							if(jQuery(".leaflet-popup-close-button").length) jQuery(".leaflet-popup-close-button")[0].click(); // close popup
							var newBounds = new L.LatLngBounds(mapBounds,new L.LatLng(pos.coords.latitude, pos.coords.longitude));
							map.fitBounds(newBounds);
						}else{
							map.panTo(userLocation);
						}
						// add/update user location indicator
						if(typeof(userMarker)==='undefined') {
							userMarker = new L.circleMarker(userLocation,{
							  radius: 8,
							  fillColor: "#4a87ee",
							  color: "#ffffff",
							  weight: 3,
							  opacity: 1,
							  fillOpacity: 0.8,
							}).addTo(map);
						}else{
							userMarker.setLatLng(userLocation);
						}
					}, 
					function(error) {
						console.log(error);
						var errorMessage = error.message ? ' Error message: "' + error.message + '"' : 'Oops! We were unable to determine your current location.';
						alert(errorMessage);
					}, 
					options);
			});

		});
    </script>
        
	<!-- Map Container -->
	<div class="curatescape-map">
		<div id="curatescape-map-canvas" class="hero"></div>
	</div>
		
<?php }

/*
** Add the map actions toolbar
*/
function mh_map_actions($item=null,$tour=null,$collection=null,$saddr='current',$coords=null){
	
		$show_directions=null;
		$street_address=null;
		
		if($item!==null){
			
			// get the destination coordinates for the item
			$location = get_db()->getTable('Location')->findLocationByItem($item, true);
			$coords=$location[ 'latitude' ].','.$location[ 'longitude' ];
			$street_address=mh_street_address($item,false);
			
			$show_directions = true;
		
		}elseif($tour!==null){
			
			// get the waypoint coordinates for the tour
			$coords = array();
			foreach( $tour->Items as $item ){
				
				set_current_record( 'item', $item );
				$location = get_db()->getTable('Location')->findLocationByItem($item, true);							$street_address=mh_street_address($item,false);
				$coords[] = $street_address ? urlencode($street_address) : $location['latitude'].','.$location['longitude'];
			}
			
			$daddr=end($coords);
			reset($coords);
			$waypoints=array_pop($coords);		
			$waypoints=implode('+to:', $coords);
			$coords=$daddr.'+to:'.$waypoints;	
			
			$show_directions=get_theme_option('show_tour_dir') ? get_theme_option('show_tour_dir') : 0;
			
		}else{
			$show_directions= 0;
		}
	
	?>
	
	<div class="map-actions clearfix">
		

		<!-- Fullscreen -->
		<a class="fullscreen"><span class="icon-expand" aria-hidden="true"></span> <span class="label"><?php echo __('Fullscreen Map');?></span><span class="alt"><?php echo __('Map');?></span></a>
		
				
		<!-- Geolocation -->
		<a class="location"><span class="icon-location-arrow" aria-hidden="true"></span> <span class="label"><?php echo __('Show Current Location');?></span><span class="alt"><?php echo __('My Location');?></span></a> 
		
		<!-- Directions link -->
		<?php
		$directions_link= ($show_directions==1) ? '<a onclick="jQuery(\'body\').removeClass(\'fullscreen-map\')" class="directions" title="'.__('Get Directions on Google Maps').'" target="_blank" href="https://maps.google.com/maps?saddr='.$saddr.'+location&daddr='.($street_address ? urlencode($street_address) : $coords).'"><span class="icon-external-link-square" aria-hidden="true"></span> <span class="label">'.__('Get Directions').'</span><span class="alt">'.__('Directions').'</span></a> ' : null;	
		echo ( $coords && ($item || $tour) ) ? $directions_link : null;	
		?>
		
	
	</div>

	
	<?php	
}


/*
** Modified search form
** Adds HTML "placeholder" attribute
** Adds HTML "type" attribute
** Includes settings for simple and advanced search via theme options
*/

function mh_simple_search($formProperties=array()){
	
	$sitewide = (get_theme_option('use_sitewide_search') == 1) ? 1 : 0;	
	$qname = ($sitewide==1) ? 'query' : 'search';
	$searchUri = ($sitewide==1) ? url('search') : url('items/browse?sort_field=relevance');
	$placeholder =  __('Search');	
	$default_record_types = mh_search_form_default_record_types();


	$searchQuery = array_key_exists($qname, $_GET) ? $_GET[$qname] : '';
	$formProperties['action'] = $searchUri;
	$formProperties['method'] = 'get';
	$html = '<form ' . tag_attributes($formProperties) . '>' . "\n";
	$html .= '<fieldset>' . "\n\n";
	$html .= '<label for "search" hidden class="hidden">Search</label>';
	$html .= get_view()->formText('search', $searchQuery, array('name'=>$qname,'class'=>'textinput search','placeholder'=>$placeholder));
	$html .= '</fieldset>' . "\n\n";

	// add hidden fields for the get parameters passed in uri
	$parsedUri = parse_url($searchUri);
	if (array_key_exists('query', $parsedUri)) {
		parse_str($parsedUri['query'], $getParams);
		foreach($getParams as $getParamName => $getParamValue) {
			$html .= get_view()->formHidden($getParamName, $getParamValue);
		}
	}
	if($sitewide==1 && count($default_record_types)){
		foreach($default_record_types as $drt){
			$html .= get_view()->formHidden('record_types[]', $drt);
		}
	}
	
	$html .= '</form>';
	return $html;	
	
}


/*
** App Store links on homepage
*/
function mh_appstore_downloads(){
	if (get_theme_option('enable_app_links')){

		echo '<div>';
		echo '<h2 class="hidden">Downloads</h2>';

		$ios_app_id = get_theme_option('ios_app_id');
		echo ($ios_app_id ?
			'<a id="apple" class="app-store" href="https://itunes.apple.com/us/app/'.$ios_app_id.'"><span class="icon-apple" aria-hidden="true"></span>
		'.__('App Store').'
		</a> ':'<a id="apple" class="app-store" href="#"><span class="icon-apple" aria-hidden="true"></span>
		'.__('iPhone App Coming Soon').'
		</a> ');

		$android_app_id = get_theme_option('android_app_id');
		echo ($android_app_id ?
			'<a id="android" class="app-store" href="http://play.google.com/store/apps/details?id='.$android_app_id.'"><span class="icon-android" aria-hidden="true"></span>
		'.__('Google Play').'
		</a> ':'<a id="android" class="app-store" href="#"><span class="icon-android" aria-hidden="true"></span>
		'.__('Android App Coming Soon').'
		</a> ');
		echo '</div>';

	}
}


/*
** App Store links in footer
*/
function mh_appstore_footer(){
	if (get_theme_option('enable_app_links')){
		echo '<div id="app-store-links">';

		$ios_app_id = get_theme_option('ios_app_id');
		$android_app_id = get_theme_option('android_app_id');
		if (($ios_app_id != false) && ($android_app_id == false)) {
			echo '<a id="apple-text-link" class="app-store-footer" href="https://itunes.apple.com/us/app/'.$ios_app_id.'">'.__('Get the app for iPhone').'</a>';
		}
		elseif (($ios_app_id == false) && ($android_app_id != false)) {
			echo '<a id="android-text-link" class="app-store-footer" href="http://play.google.com/store/apps/details?id='.$android_app_id.'">'.__('Get the app for Android').'</a>';

		}
		elseif (($ios_app_id != false)&&($android_app_id != false)) {
			$iphone='<a id="apple-text-link" class="app-store-footer" href="https://itunes.apple.com/us/app/'.$ios_app_id.'">'.__('iPhone').'</a>';
			$android='<a id="android-text-link" class="app-store-footer" href="http://play.google.com/store/apps/details?id='.$android_app_id.'">'.__('Android').'</a>';
			echo __('Get the app for %1$s and %2$s', $iphone, $android);
		}
		else{
			echo __('iPhone + Android Apps Coming Soon!');
		}
		echo '</div>';
	}
}


/*
** Replace BR tags, wrapping text in P tags instead
*/
function replace_br($data) {
    $data = preg_replace('#(?:<br\s*/?>\s*?){2,}#', '</p><p>', $data);
    return "<p>$data</p>";
}

/*
** primary item text  
*/

function mh_the_text($item='item',$options=array()){
	
	$dc_desc = metadata($item, array('Dublin Core', 'Description'),$options);
	$primary_text = element_exists('Item Type Metadata','Story') ? metadata($item,array('Item Type Metadata', 'Story'),$options) : null;
	
	return $primary_text ? replace_br($primary_text) : ($dc_desc ? replace_br($dc_desc) : null);
}

/*
** Title
*/
function mh_the_title($item='item'){
	return '<h1 class="title">'.metadata($item, array('Dublin Core', 'Title'), array('index'=>0)).'</h1>';
}


/*
** Subtitle 
*/

function mh_the_subtitle($item='item'){

	$dc_title2 = metadata($item, array('Dublin Core', 'Title'), array('index'=>1));
	$subtitle=element_exists('Item Type Metadata','Subtitle') ? metadata($item,array('Item Type Metadata', 'Subtitle')) : null;
	
	return  $subtitle ? '<h2 class="subtitle">'.$subtitle.'</h2>' : ($dc_title2!=='[Untitled]' ? '<h2 class="subtitle">'.$dc_title2.'</h2>' : null);
}


/*
** lede  
*/
function mh_the_lede($item='item'){
	if (element_exists('Item Type Metadata','Lede')){
		$lede=metadata($item,array('Item Type Metadata', 'Lede'));
		return  $lede ? '<div class="lede">'.$lede.'</div>' : null;
	}
		
}


/*
** sponsor for use in item byline 
*/
function mh_the_sponsor($item='item'){

	if (element_exists('Item Type Metadata','Sponsor')){
		$sponsor=metadata($item,array('Item Type Metadata','Sponsor'));
		return $sponsor ? '<span class="sponsor"> '.__('with research support from %s', $sponsor).'</span>' : null;	
	} 
	
}


/*
** Display subjects as tags
*/
function mh_subjects(){
	$subjects = metadata('item',array('Dublin Core', 'Subject'), 'all');
	if (count($subjects) > 0){
		$html = '<div class="subjects">';
			$html.= '<h3>'.__('Subjects').'</h3>';
			$html.= '<ul>';
			foreach ($subjects as $subject){
				$link = WEB_ROOT;
				$link .= htmlentities('/items/browse?term=');
				$link .= rawurlencode($subject);
				$link .= htmlentities('&search=&advanced[0][element_id]=49&advanced[0][type]=contains&advanced[0][terms]=');
				$link .= urlencode(str_replace('&amp;','&',$subject));
				$html.= '<li><a href="'.$link.'">'.$subject.'</a></li> ';
			}
			$html.= '</ul>';
		$html .= '</div>';
		return $html;

	}
}
/*
** Display subjects as single line of links
*/
function mh_subjects_string(){
	$subjects = metadata('item',array('Dublin Core', 'Subject'), 'all');
	if (count($subjects) > 0){
		$html=array();

		foreach ($subjects as $subject){
			$link = WEB_ROOT;
			$link .= htmlentities('/items/browse?term=');
			$link .= rawurlencode($subject);
			$link .= htmlentities('&search=&advanced[0][element_id]=49&advanced[0][type]=contains&advanced[0][terms]=');
			$link .= urlencode(str_replace('&amp;','&',$subject));
			$html[]= '<a href="'.$link.'">'.$subject.'</a>';
		}

		return '<div class="subjects"><span>'.__('Subjects: ').'</span>'.implode(", ", $html).'</div>';
	}
}


/*
** Display the item tags
*/
function mh_tags(){
	if (metadata('item','has tags')){
		$html  = '<div class="tags">';
		$html .= '<h3>'.__('Tags').'</h3>';
		$html .= tag_cloud('item','items/browse');
		$html .= '</div>';
		return $html;
	}
}

/*
** Display the official website
*/
function mh_official_website($item='item'){

	if (element_exists('Item Type Metadata','Official Website')){
		$website=metadata($item,array('Item Type Metadata','Official Website'));
		return $website ? '<h3>'.__('Official Website').'</h3><div>'.$website.'</div>' : null;	
	} 

}

/*
** Display the street address
*/
function mh_street_address($item='item',$formatted=true){

	if (element_exists('Item Type Metadata','Street Address')){
		$address=metadata($item,array('Item Type Metadata','Street Address'));
		$map_link='<a target="_blank" href="https://maps.google.com/maps?saddr=current+location&daddr='.urlencode($address).'">map</a>';
		return $address ? ( $formatted ? '<h3>'.__('Street Address').'</h3><div>'.$address.' ['.$map_link.']</div>' : $address ) : null;	
	}else{
		return null;
	} 

}

/*
** Display the access info  
*/
function mh_access_information($item='item',$formatted=true){
	if (element_exists('Item Type Metadata','Access Information')){
		$access_info=metadata($item,array('Item Type Metadata', 'Access Information'));
		return  $access_info ? ($formatted ? '<div class="access-information"><h3>'.__('Access Information').'</h3><div>'.$access_info.'</div></div>' : $access_info) : null;
	}else{
		return null;
	}
		
}

/*
** Display the map caption
*/

function mh_map_caption($item='item'){
	$caption=array();
	if($addr=mh_street_address($item,false)) $caption[]=$addr;
	if($accs=mh_access_information($item,false)) $caption[]=$accs;
	return implode( ' ~ ', $caption );
}

/*
** Display the factoid
*/
function mh_factoid($item='item'){

	if (element_exists('Item Type Metadata','Factoid')){
		$factoids=metadata($item,array('Item Type Metadata','Factoid'),array('all'=>true));
		if($factoids){
			$html=null;
			//$html.='<script type="text/javascript" async src="https://platform.twitter.com/widgets.js"></script>';
			$tweetable=get_theme_option('tweetable_factoids');
			$via=get_theme_option('twitter_username') ? 'data-via="'.get_theme_option('twitter_username').'"' : '';
			foreach($factoids as $factoid){
				$html.='<div class="factoid flex"><span>'.$factoid.'</span>'.($tweetable ? '<span><a class="button tweet-this"><i class="fa fa-lg fa-twitter" aria-hidden="true"></i></a></span>' : null).'</div>';
			}
			
			return $html;
			//"<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>";
		}
	} 

}

/*
** Display related links
*/
function mh_related_links(){
	$dc_relations_field = metadata('item',array('Dublin Core', 'Relation'), array('all' => true));
	
	$related_resources = element_exists('Item Type Metadata','Related Resources') ? metadata('item',array('Item Type Metadata', 'Related Resources'), array('all' => true)) : null;
	
	$relations = $related_resources ? $related_resources : $dc_relations_field;
	
	if ($relations){
		$html= '<h3>'.__('Related Sources').'</h3><div><ul>';
		foreach ($relations as $relation) {
			$html.= '<li>'.strip_tags($relation,'<a><i><em><b><strong>').'</li>';
		}
		$html.= '</ul></div>';
		return $html;
	}
}


/*
** Author Byline
*/
function mh_the_byline($itemObj='item',$include_sponsor=false){
	if ((get_theme_option('show_author') == true)){
		$html='<div class="byline">'.__('By').' ';

		if(metadata($itemObj,array('Dublin Core', 'Creator'))){
			$authors=metadata($itemObj,array('Dublin Core', 'Creator'), array('all'=>true));
			$total=count($authors);
			$index=1;
			$authlink=get_theme_option('link_author');

			foreach ($authors as $author){
				if($authlink==1){
					$href='/items/browse?search=&advanced[0][element_id]=39&advanced[0][type]=is+exactly&advanced[0][terms]='.$author;
					$author='<a href="'.$href.'">'.$author.'</a>';
				}

				switch ($index){
				case ($total):
					$delim ='';
					break;

				case ($total-1):
					$delim =' <span class="amp">&amp;</span> ';
					break;

				default:
					$delim =', ';
					break;
				}


				$html .= $author.$delim;
				$index++;
			}
		}else{
			$html .= __('The %s team', option('site_title'));
		}
		
		$html .= (($include_sponsor) && (mh_the_sponsor($itemObj)!==null ))? ''.mh_the_sponsor($itemObj) : null;
		
		$html .='</div>';

		return $html;
	}
}


/*
** Custom item citation
*/
function mh_item_citation(){
	return '<div class="item-citation"><h3>'.__('Cite this Page').'</h3><div>'.html_entity_decode(metadata('item', 'citation')).'</div></div>';
}

/*
** Post Added/Modified String
*/
function mh_post_date(){

	if(get_theme_option('show_datestamp')==1){
		$a=format_date(metadata('item', 'added'));
		$m=format_date(metadata('item', 'modified'));	
	
		return '<div class="item-post-date">'.__('Published on %s.', $a ).( ($a!==$m) ? '<div>'.__('Last updated on %s.', $m ).'</div>' : null ).'</div>';	
	}
}

/*
** Build caption from description, source, and creator
*/
function mh_file_caption($file,$inlineTitle=true){

	$caption=array();

	if( $inlineTitle !== false ){
		$title = metadata( $file, array( 'Dublin Core', 'Title' ) ) ? '<span class="title">'.metadata( $file, array( 'Dublin Core', 'Title' ) ).'</span>' : null;
	}

	$description = metadata( $file, array( 'Dublin Core', 'Description' ) );
	if( $description ) {
		$caption[]= $description;
	}

	$source = metadata( $file, array( 'Dublin Core', 'Source' ) );
	if( $source ) {
		$caption[]= __('Source: %s',$source);
	}


	$creator = metadata( $file, array( 'Dublin Core', 'Creator' ) );
	if( $creator ) {
		$caption[]= __('Creator: %s', $creator);
	}

	if( count($caption) ){
		return ($inlineTitle ? $title.': ' : null).implode(" | ", $caption);
	}else{
		return $inlineTitle ? $title : null;
	}
}


function mh_footer_scripts_init(){
	//===========================// ?>
	<script>
	jQuery(document).ready(function($) {

	});
	</script>
	<?php //========================//
}


/*
** Loop through and display image files
*/
function mh_item_images($item,$index=0){
	$html=null;
	foreach (loop('files', $item->Files) as $file){
		$img = array('image/jpeg','image/jpg','image/png','image/jpeg','image/gif');
		$mime = metadata($file,'MIME Type');
		if(in_array($mime,$img)) {
			$src=WEB_ROOT.'/files/fullsize/'.str_replace( array('.JPG','.jpeg','.JPEG','.png','.PNG','.gif','.GIF'), '.jpg', $file->filename );
			$html.= '<figure class="flex-image" itemprop="associatedMedia" itemscope itemtype="http://schema.org/ImageObject">';
				$html.= '<a class="file flex" href="'.$src.'" data-size="" style="background-image: url(\''.$src.'\');"></a>';
				$html.= '<figcaption hidden class="hidden;">'.metadata($file, array('Dublin Core', 'Title')).'</figcaption>';
			$html.= '</figure>';
		}		
	}
	if($html): ?>
		<h3><?php echo __('Images');?></h3>
		<figure id="item-photos" class="flex flex-wrap" itemscope itemtype="http://schema.org/ImageGallery">
			<?php echo $html;?>
		</figure>		
		<!-- PhotoSwipe -->
		<div class="pswp" tabindex="-1" role="dialog" aria-hidden="true">
		  <div class="pswp__bg"></div>
		  <div class="pswp__scroll-wrap">
		      <div class="pswp__container">
		          <div class="pswp__item"></div>
		          <div class="pswp__item"></div>
		          <div class="pswp__item"></div>
		      </div>
		      <div class="pswp__ui pswp__ui--hidden">
		          <div class="pswp__top-bar">
		              <div class="pswp__counter"></div>
		              <button class="pswp__button pswp__button--close" title="Close (Esc)"></button>
		              <button class="pswp__button pswp__button--share" title="Share"></button>
		              <button class="pswp__button pswp__button--fs" title="Toggle fullscreen"></button>
		              <button class="pswp__button pswp__button--zoom" title="Zoom in/out"></button>
		              <div class="pswp__preloader">
		                  <div class="pswp__preloader__icn">
		                    <div class="pswp__preloader__cut">
		                      <div class="pswp__preloader__donut"></div>
		                    </div>
		                  </div>
		              </div>
		          </div>
		          <div class="pswp__share-modal pswp__share-modal--hidden pswp__single-tap">
		              <div class="pswp__share-tooltip"></div> 
		          </div>
		          <button class="pswp__button pswp__button--arrow--left" title="Previous (arrow left)">
		          </button>
		          <button class="pswp__button pswp__button--arrow--right" title="Next (arrow right)">
		          </button>
		          <div class="pswp__caption">
		              <div class="pswp__caption__center"></div>
		          </div>
		      </div>
		  </div>
		</div>		
	<?php endif;
}


/*
** Loop through and display audio files
*/
function mh_audio_files($item,$index=0){
	if (!$item){
		$item=set_loop_records('files',$item);
	}
	$html=null;
	$audioTypes = array('audio/mpeg');
	foreach (loop('files', $item->Files) as $file){
		$mime = metadata($file,'MIME Type');
		if ( array_search($mime, $audioTypes) !== false ){
			$audioTitle = metadata($file,array('Dublin Core','Title')) ? metadata($file,array('Dublin Core','Title')) : 'Audio File '.($index+1);
			$audioDesc = strip_tags(mh_file_caption($file,false),'<span>');
			$html.='<div class="flex media-select" data-source="'.WEB_ROOT.'/files/original/'.$file->filename.'">';
				$html.='<div class="media-thumb"><i class="fa fa-lg fa-microphone media-icon" aria-hidden="true"></i></div>';
				$html.='<div class="media-caption">';
					$html.='<div class="media-title">'.$audioTitle.'</div>';
					$html.='<strong>Duration</strong>: <span class="duration">00:00:00</span><br>';
					$html.='<strong>Details</strong>: '.link_to($file,'show',__('View File Record'));
				$html.='</div>';
			$html.='</div>';
		}
	};
	if($html): ?>
		<h3><?php echo __('Audio');?></h3>
		<figure id="item-audio">	
			<div class="media-container audio">
				<audio id="curatescape-player-audio" class="video-js" controls preload="auto" type="audio/mp3">
					<p class="vjs-no-js">To listen to this audio please enable JavaScript, and consider upgrading to a web browser that <a href="http://videojs.com/html5-video-support/" target="_blank">supports HTML5 audio</a></p>
				</audio>
				<div class="flex media-list audio" style="">
					<?php echo $html;?>		
				</div>
			</div>
		</figure>	
		<script>
			jQuery(document).ready(function($) {
				
				var audioplayer = videojs('curatescape-player-audio',{
					height:'30',
					controlBar: {
						fullscreenToggle: false
					}
				}).src(
					$('.media-list.audio .media-select:first-child').attr('data-source')
				);
				if(typeof audioplayer == 'object'){
					$('.media-list.audio .media-select:first-child').addClass('now-playing');
					
					$('.media-list.audio .media-select').on('click',function(e){
						$('.media-list.audio .now-playing').removeClass('now-playing');
						$(this).addClass('now-playing');
						audioplayer.src($(this).attr('data-source')).play();
					});
					
					audioplayer.ready(function(){
						// load controls
						audioplayer.play().pause()
					});
				}				
				
			});
		</script>
	<?php endif;
}



/*
** Loop through and display video files
** Please use H.264 video format
** We accept multiple H.264-related MIME-types because Omeka MIME detection is sometimes spotty
** But in the end, we always tell the browser they're looking at "video/mp4"
*/
function mh_video_files($item='item',$html=null) {

	$videoTypes = array('video/mp4','video/mpeg','video/quicktime');
	foreach (loop('files', $item->Files) as $file){
		$videoMime = metadata($file,'MIME Type');
		if ( in_array($videoMime,$videoTypes) ){
			$videoTitle = metadata($file,array('Dublin Core','Title')) ? metadata($file,array('Dublin Core','Title')) : 'Video File '.($videoIndex+1);
			$html.='<div class="flex media-select" data-source="'.WEB_ROOT.'/files/original/'.$file->filename.'">';
				$html.='<div class="media-thumb"><i class="fa fa-lg fa-film media-icon" aria-hidden="true"></i></div>';
				$html.='<div class="media-caption">';
					$html.='<div class="media-title">'.$videoTitle.'</div>';
					$html.='<strong>Duration</strong>: <span class="duration">00:00:00</span><br>';
					$html.='<strong>Details</strong>: '.link_to($file,'show',__('View File Record'));
				$html.='</div>';
			$html.='</div>';

		}
	}
	if($html): ?>
		<h3><?php echo __('Video');?></h3>
		<figure id="item-video">
			<div class="media-container video">
			
			<video id="curatescape-player" class="video-js vjs-fluid" controls preload="auto">
				<p class="vjs-no-js">To view this video please enable JavaScript, and consider upgrading to a web browser that <a href="http://videojs.com/html5-media-support/" target="_blank">supports HTML5 video</a></p>
			</video>
			<div class="flex media-list video" style="">
				<?php echo $html;?>
			</div>
		</figure>
		<script>
			jQuery(document).ready(function($) {
				
				var videoplayer = videojs('curatescape-player').src(
					$('.media-list.video .media-select:first-child').attr('data-source')
				);
				if(typeof videoplayer == 'object'){
					$('.media-list.video .media-select:first-child').addClass('now-playing');
					
					$('.media-list.video .media-select').on('click',function(e){
						$('.media-list.video .now-playing').removeClass('now-playing');
						$(this).addClass('now-playing');
						videoplayer.src($(this).attr('data-source')).play();
					});
				}				
				
			});
		</script>			
	<?php endif;
}
/*
** display single file in FILE TEMPLATE
*/

function mh_single_file_show($file=null){
		
		$mime = metadata($file,'MIME Type');
		$img = array('image/jpeg','image/jpg','image/png','image/jpeg','image/gif');
		$audioTypes = array('audio/mpeg');
		$videoTypes = array('video/mp4','video/mpeg','video/quicktime');
		
		
		// SINGLE AUDIO FILE
		if ( array_search($mime, $audioTypes) !== false ){
			
			?>
			
			<script>
			jQuery.ajaxSetup({
				cache: true
			});
			var audioTagSupport = !!(document.createElement('audio').canPlayType);
			if (Modernizr.audio) {
			   var myAudio = document.createElement('audio');
			   // Currently canPlayType(type) returns: "", "maybe" or "probably" 
			   var canPlayMp3 = !!myAudio.canPlayType && "" != myAudio.canPlayType('audio/mpeg');
			}
			if(!canPlayMp3){
				loadJS("<?php echo WEB_ROOT.'/themes/'.basename(__DIR__) ;?>/javascripts/audiojs/audiojs/audio.min.js", function(){
					audiojs.events.ready(function() {
					var as = audiojs.createAll();				
					});
				});  
			}  
			</script>
			
			<?php
			
			$html = '<audio controls ><source src="'.file_display_url($file,'original').'" type="audio/mpeg" /><h5 class="no-audio"><strong>'.__('Download Audio').':</strong><a href="'.file_display_url($file,'original').'">MP3</a></h5></audio>';
			
			return $html;
		
		// SINGLE VIDEO FILE	
		}elseif(array_search($mime, $videoTypes) !== false){
			$html=null;
			$videoIndex = 0;
			$localVid=0;
			$videoTypes = array('video/mp4','video/mpeg','video/quicktime');
			$videoFile = file_display_url($file,'original');
			$videoTitle = metadata($file,array('Dublin Core', 'Title'));
			$videoClass = (($videoIndex==0) ? 'first' : 'not-first');
			$videoDesc = mh_file_caption($file,false);
			$videoTitle = metadata($file,array('Dublin Core','Title'));
			$embeddable=embeddableVersion($file,$videoTitle,$videoDesc,array('Dublin Core','Relation'),false);
			if($embeddable){
				// If a video has an embeddable streaming version, use it.
				$html.= $embeddable;
				$videoIndex++;
				//break;
			}else{
				?>
				<script>
					loadCSS('//vjs.zencdn.net/4.3/video-js.css');
					loadJS('//vjs.zencdn.net/4.3/video.js');
				</script>	
				<?php 	
				$html .= '<div class="item-file-container">';
				$html .= '<video width="725" height="410" id="video-'.$localVid.'" class="'.$videoClass.' video-js vjs-default-skin" controls preload="auto" data-setup="{}">';
				$html .= '<source src="'.$videoFile.'" type="video/mp4">';
				$html .= '</video>';

			}	
					
			return $html;
		
		// SINGLE IMAGE OR OTHER FILE	
		}else{
			return file_markup($file, array('imageSize'=>'fullsize'));
		}
}

/*
** Checks file metadata record for embeddable version of video file
** Because YouTube and Vimeo have better compression, etc.
** returns string $html | false
*/
function embeddableVersion($file,$title=null,$desc=null,$field=array('Dublin Core','Relation'),$caption=true){

	$youtube= (strpos(metadata($file,$field), 'youtube.com')) ? metadata($file,$field) : false;
	$youtube_shortlink= (strpos(metadata($file,$field), 'youtu.be')) ? metadata($file,$field) : false;
	$vimeo= (strpos(metadata($file,$field), 'vimeo.com')) ? metadata($file,$field) : false;

	if($youtube) {
		// assumes YouTube links look like https://www.youtube.com/watch?v=NW03FB274jg where the v query contains the video identifier
		$url=parse_url($youtube);
		$id=str_replace('v=','',$url['query']);
		$html= '<div class="embed-container youtube" id="v-streaming" style="position: relative;padding-bottom: 56.25%;height: 0; overflow: hidden;"><iframe style="position: absolute;top: 0;left: 0;width: 100%;height: 100%;" src="//www.youtube.com/embed/'.$id.'" frameborder="0" width="725" height="410" allowfullscreen></iframe></div>';
		if($caption==true){
			$html .= ($title) ? '<h4 class="title video-title sib">'.$title.' <span class="icon-info-sign" aria-hidden="true"></span></h4>' : '';
			$html .= ($desc) ? '<p class="description video-description sib">'.$desc.link_to($file,'show', '<span class="view-file-link"><span class="icon-file" aria-hidden="true"></span> '.__('View File Details Page').'</span>',array('class'=>'view-file-record','rel'=>'nofollow')).'</p>' : '';
		}
		return '<div class="item-file-container">'.$html.'</div>';
	}
	elseif($youtube_shortlink) {
		// assumes YouTube links look like https://www.youtu.be/NW03FB274jg where the path string contains the video identifier
		$url=parse_url($youtube_shortlink);
		$id=$url['path'];
		$html= '<div class="embed-container youtube" id="v-streaming" style="position: relative;padding-bottom: 56.25%;height: 0; overflow: hidden;"><iframe style="position: absolute;top: 0;left: 0;width: 100%;height: 100%;" src="//www.youtube.com/embed/'.$id.'" frameborder="0" width="725" height="410" allowfullscreen></iframe></div>';
		if($caption==true){
			$html .= ($title) ? '<h4 class="title video-title sib">'.$title.' <span class="icon-info-sign" aria-hidden="true"></span></h4>' : '';
			$html .= ($desc) ? '<p class="description video-description sib">'.$desc.link_to($file,'show', '<span class="view-file-link"><span class="icon-file" aria-hidden="true"></span> '.__('View File Details Page').'</span>',array('class'=>'view-file-record','rel'=>'nofollow')).'</p>' : '';
		}
		return '<div class="item-file-container">'.$html.'</div>';
	}
	elseif($vimeo) {
		// assumes the Vimeo links look like http://vimeo.com/78254514 where the path string contains the video identifier
		$url=parse_url($vimeo);
		$id=$url['path'];
		$html= '<div class="embed-container vimeo" id="v-streaming" style="padding-top:0; height: 0; padding-top: 25px; padding-bottom: 67.5%; margin-bottom: 10px; position: relative; overflow: hidden;"><iframe style=" top: 0; left: 0; width: 100%; height: 100%; position: absolute;" src="//player.vimeo.com/video'.$id.'?color=333" width="725" height="410" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe></div>';
		if($caption==true){
			$html .= ($title) ? '<h4 class="title video-title sib">'.$title.' <span class="icon-info-sign" aria-hidden="true"></span></h4>' : '';
			$html .= ($desc) ? '<p class="description video-description sib">'.$desc.link_to($file,'show', '<span class="view-file-link"><span class="icon-file" aria-hidden="true"></span> '.__('View File Details Page').'</span>',array('class'=>'view-file-record','rel'=>'nofollow')).'</p>' : '';
		}
		return '<div class="item-file-container">'.$html.'</div>';
	}
	else{
		return false;
	}
}


/*
** Display the AddThis social sharing widgets
** www.addthis.com
*/
function mh_share_this($type='Page'){
	$addthis = get_theme_option('Add This') ? '#pubid='.get_theme_option('Add This') : null;
	$tracking= ($addthis && get_theme_option('track_address_bar')) ? '"data_track_addressbar":true' : null;

	$html = '<h3>'.__('Share this %s',$type).'</h3>';
	$html .= '<!-- AddThis Button BEGIN -->
<div class="addthis_toolbox addthis_default_style addthis_32x32_style">
<a class="addthis_button_twitter"></a>
<a class="addthis_button_facebook"></a>
<a class="addthis_button_pinterest_share"></a>
<a class="addthis_button_email"></a>
<a class="addthis_button_compact"></a>
</div>
<script type="text/javascript">var addthis_config = {'.$tracking.'};</script>
<script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js'.$addthis.'"></script>
<script type="text/javascript">
// Alert a message when the AddThis API is ready
function addthisReady(evt) {
    jQuery(\'#share-this\').addClass(\'api-loaded\');
}

// Listen for the ready event
addthis.addEventListener(\'addthis.ready\', addthisReady);
</script>
<!-- AddThis Button END -->';


	return $html;
}

/*
** DISQUS COMMENTS
** disqus.com
*/
function mh_disquss_comments($shortname){
	$preface=get_theme_option('comments_text');
	if ($shortname){
	?>
    <?php echo $preface ? '<div id="comments_preface">'.$preface.'</div>' : ''?>
    <div id="disqus_thread"></div>
    <script type="text/javascript">
        var disqus_shortname = '<?php echo $shortname;?>'; 
        (function() {
            var dsq = document.createElement('script'); dsq.type = 'text/javascript'; dsq.async = true;
            dsq.src = '//' + disqus_shortname + '.disqus.com/embed.js';
            (document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(dsq);
        })();
    </script>
    <noscript><?php echo __('Please enable JavaScript to view the <a href="http://disqus.com/?ref_noscript">comments powered by Disqus.</a>');?></noscript>
    <a href="http://disqus.com" class="dsq-brlink"><?php echo __('comments powered by <span class="logo-disqus">Disqus</span>');?></a>
    
	<?php
	}
}

/*
** INTENSE DEBATE COMMENTS
** intensedebate.com
*/	
function mh_intensedebate_comments($intensedebate_id){
	$preface=get_theme_option('comments_text');
	if ($intensedebate_id){ ?>
	    <?php echo $preface ? '<div id="comments_preface">'.$preface.'</div>' : ''?>
	    <div id="disqus_thread"></div>
	
		<script>
		var idcomments_acct = '<?php echo $intensedebate_id;?>';
		var idcomments_post_id;
		var idcomments_post_url;
		</script>
		<span id="IDCommentsPostTitle" style="display:none"></span>
		<script type='text/javascript' src='https://www.intensedebate.com/js/genericCommentWrapperV2.js'></script>
		<?php
	}
}

/*
** DISPLAY COMMENTS
*/	
function mh_display_comments(){
	if(get_theme_option('comments_id')){
		return mh_disquss_comments(get_theme_option('comments_id'));
	}else if(get_theme_option('intensedebate_site_account')){
		return mh_intensedebate_comments(get_theme_option('intensedebate_site_account'));
	}else{
		return null;
	}
}


/*
** Display the Tours search results
*/
function mh_tour_preview($s){
	$html=null;
	$record=get_record_by_id($s['record_type'], $s['record_id']);
	set_current_record( 'tour', $record );
	$html.=  '<article>';
	$html.=  '<h3 class="tour-result-title"><a href="'.record_url($record, 'show').'">'.($s['title'] ? $s['title'] : '[Unknown]').'</a></h3>';
	$html.=  '<span class="tour-meta-browse">';
	if(tour('Credits') ){
		$html.=  __('%1s curated by: %2s', mh_tour_label('singular'),tour('Credits') ).' | ';
	}elseif(get_theme_option('show_author') == true){
		$html.=  __('%1s curated by: The %2s Team',mh_tour_label('singular'),option('site_title')).' | ';
	}		
	$html.=  count($record->Items).' '.__('Locations').'</span><br>';
	$html.=  ($text=tour('Description')) ? '<span class="tour-result-snippet">'.snippet($text,0,300).'</span>' : null;
	if(get_theme_option('show_tour_item_thumbs') == true){
		$html.=  '<span class="tour-thumbs-container">';
		foreach($record->Items as $mini_thumb){
			$html.=  metadata($mini_thumb, 'has thumbnail') ? 
			'<div class="mini-thumb">'.item_image('square_thumbnail',array('height'=>'40','width'=>'40'),null,$mini_thumb).'</div>' : 
			null;
		}
		$html.=  '</span>';
	}
	$html.= '</article>';	
	return $html;
}	


/*
** Display the Tours list
*/
function mh_display_homepage_tours($num=7, $scope='random'){
	
	$scope=get_theme_option('homepage_tours_scope') ? get_theme_option('homepage_tours_scope') : $scope;
	
	// Get the database.
	$db = get_db();

	// Get the Tour table.
	$table = $db->getTable('Tour');

	// Build the select query.
	$select = $table->getSelect();
	$select->where('public = 1');
	
	// Get total count
	$public = $table->fetchObjects($select);		
	
	// Continue, get scope
	switch($scope){
		case 'random':
			$select->from(array(), 'RAND() as rand');
			break;
		case 'featured':
			$select->where('featured = 1');
			break;
	}
	

	// Fetch some items with our select.
	$items = $table->fetchObjects($select);
	if($scope=='random') shuffle($items);
	$num = (count($items)<$num)? count($items) : $num;
	$html=null;
	
	if($items){
		$html .= '<h2><a href="'.WEB_ROOT.'/tours/browse/">'.mh_tour_header().'</a></h2>';
	
		for ($i = 0; $i < $num; $i++) {
			$html .= '<article class="item-result">';
			$html .= '<h3 class="home-tour-title"><a href="' . WEB_ROOT . '/tours/show/'. $items[$i]['id'].'">' . $items[$i]['title'] . '</a></h3>';
			$html .= '</article>';
		}
		if(count($public)>1){
			$html .= '<p class="view-more-link"><a href="'.WEB_ROOT.'/tours/browse/">'.__('Browse all <span>%1$s %2$s</span>', count($public), mh_tour_label('plural')).'</a></p>';
		}
	}else{
		$html .= '<article class="home-tour-result none">';
		$html .= '<p>'.__('No tours are available.').'</p>';
		$html .= '</article>';
	}
	
	return $html;

}

function mh_homepage_hero_item($item){
			$itemTitle = metadata($item, array('Dublin Core', 'Title'));
			$itemDescription = mh_the_text($item,array('snippet'=>200));
			$class=get_theme_option('featured_tint')==1 ? 'tint' : 'no-tint';
			$html=null;
	
			if (metadata($item, 'has thumbnail') ) {
			
				$img_markup=item_image('fullsize',array(),0, $item);
				preg_match('/<img(.*)src(.*)=(.*)"(.*)"/U', $img_markup, $result);
				$img_url = array_pop($result);				
				
				$html .= '<div class="'.$class.'">';
					$html .= '<article class="featured-story-result">';
					$html .= '<div class="featured-decora-outer">' ;
						$html .= '<div class="featured-decora-bg" style="background-image:url('.$img_url.')"></div>' ;
						$html .= '<div class="featured-decora-img">'.link_to_item(item_image('square_thumbnail',array('alt'=>''),0, $item), array(), 'show', $item).'</div>';
					
						$html .= '<div class="featured-decora-text"><div class="featured-decora-text-inner">';
							$html .= '<header><h3>' . link_to_item($itemTitle, array(), 'show', $item) . '<span class="featured-item-author">'.mh_the_byline($item,false).'</span></h3></header>';
						if ($itemDescription) {
							$html .= '<div class="item-description">' . strip_tags($itemDescription) . '</div>';
							}else{
							$html .= '<div class="item-description">'.__('Preview text not available.').'</div>';
						}
	
						$html .= '</div></div>' ;
					
					$html .= '</div>' ;
					$html .= '</article>';
				$html .= '</div>';
			}
			
			return $html;
				
}

/*
** Display random featured item(s)
*/
function mh_display_random_featured_item($withImage=false,$num=1)
{
	$featuredItem = get_random_featured_items($num,$withImage);
	$html = '<h2 class="hidden">'.__('Featured %s', mh_item_label()).'</h2>';
	
	if ($featuredItem) {
	
	foreach($featuredItem as $item):

		$html.=mh_homepage_hero_item($item);
			
	endforeach;		
			
	}else {
		$html .= '<article class="featured-story-result none">';
		$html .= '<div class="item-thumb clearfix"></div><div class="item-description empty"><p>'.__('No featured items are available.').'</p></div>';
		$html .= '</article>';
	}
	
	

	return $html;
}


/*
** Display the customizable "About" content on homepage
*/
function mh_home_about($length=530,$html=null){

	$html .= '<div class="about-text">';
		$html .= '<article>';
			
			$html .= '<header>';
				$html .= '<h2>'.option('site_title').'</h2>';
				$html .= '<span class="find-us">'.__('A project by %s', mh_owner_link()).'</span>';
			$html .= '</header>';
		
			$html .= '<div class="about-main">';
				$html .= substr(mh_about(),0,$length);
				$html .= ($length < strlen(mh_about())) ? '...' : null;
				$html .= '<p class="view-more-link"><a href="'.url('about').'">'.__('Read more <span>About Us</span>').'</a></p>';
			$html .= '</div>';
	
		$html .= '</article>';
	$html .= '</div>';
	
	$html .= '<div class="home-about-links">';
		$html .= '<aside>';
		$html .= mh_homepage_find_us();
		$html .= '</aside>';
	$html .= '</div>';

	return $html;
}

/*
** Tag cloud for homepage
*/
function mh_home_popular_tags($num=50){
	
	$tags=get_records('Tag',array('sort_field' => 'count', 'sort_dir' => 'd'),$num);
	
	return '<div id="home-tags" class="browse tags">'.tag_cloud($tags,url('items/browse')).'<p class="view-more-link"><a href="'.url('items/tags').'">'.__('View all <span>%s Tags</span>',total_records('Tags')).'</a></p></div>';
	
}

	

/*
** List of recent or random items for homepage
*/
function mh_home_item_list($html=null){
	$html.= '<div id="rr_home-items" class="">';
	$html.=  mh_random_or_recent( ($mode=get_theme_option('random_or_recent')) ? $mode : 'recent' );
	$html.=  '</div>';	
	
	return $html;
}

/*
** Build an array of social media links (including icons) from theme settings
*/
function mh_social_array($max=5){
	$services=array();
	($email=get_theme_option('contact_email') ? get_theme_option('contact_email') : get_option('administrator_email')) ? array_push($services,'<a href="mailto:'.$email.'" class="button social icon email"><i class="fa fa-lg fa-envelope" aria-hidden="true"><span> Email</span></i></a>') : null;		
	($facebook=get_theme_option('facebook_link')) ? array_push($services,'<a href="'.$facebook.'" class="button social icon facebook"><i class="fa fa-lg fa-facebook" aria-hidden="true"><span> Facebook</span></i></a>') : null;	
	($twitter=get_theme_option('twitter_username')) ? array_push($services,'<a href="'.$twitter.'" class="button social icon twitter"><i class="fa fa-lg fa-twitter" aria-hidden="true"><span> Twitter</span></i></a>') : null;	
	($youtube=get_theme_option('youtube_username')) ? array_push($services,'<a href="'.$youtube.'" class="button social icon youtube"><i class="fa fa-lg fa-youtube-play" aria-hidden="true"><span> Youtube</span></i></a>') : null;
	($instagram=get_theme_option('instagram_username')) ? array_push($services,'<a href="'.$instagram.'" class="button social icon instagram"><i class="fa fa-lg fa-instagram" aria-hidden="true"><span> Instagram</span></i></a>') : null;			
	($pinterest=get_theme_option('pinterest_username')) ? array_push($services,'<a href="'.$pinterest.'" class="button social icon pinterest"><i class="fa fa-lg fa-pinterest" aria-hidden="true"><span> Pinterest</span></i></a>') : null;
	($tumblr=get_theme_option('tumblr_link')) ? array_push($services,'<a href="'.$tumblr.'" class="button social icon tumblr"><i class="fa fa-lg fa-tumblr" aria-hidden="true"><span> Tumblr</span></i></a>') : null;
	($reddit=get_theme_option('reddit_link')) ? array_push($services,'<a href="'.$reddit.'" class="button social icon reddit"><i class="fa fa-lg fa-reddit" aria-hidden="true"><span> Reddit</span></i></a>') : null;	
	($wordpress=get_theme_option('wordpress_link')) ? array_push($services,'<a href="'.$wordpress.'" class="button social icon wordpress"><i class="fa fa-lg fa-wordpress" aria-hidden="true"><span> WordPress</span></i></a>') : null;				

	if( ($total=count($services)) > 0 ){
		if($total>$max){
			for($i=$total; $i>($max-1); $i-- ){
				unset($services[$i]);
			}			
		}
		return $services;
	}else{
		return false;
	}	
}

/*
** Build a series of social media link for the footer
** $class 'colored' uses a service-specific color as background
** $class 'no-label' visually hides the label and just uses the icon
*/
function mh_footer_find_us($class="colored no-label", $max=9){
	if( $services=mh_social_array($max) ){
		return '<div class="link-icons '.$class.'">'.implode(' ',$services).'</div>';
	}
}

/*
** Build a series of social media link for the homepage
** $class 'colored' uses a service-specific color as background
** $class 'no-label' visually hides the label and just uses the icon
*/
function mh_homepage_find_us($class="", $max=3){
	if( $services=mh_social_array($max) ){
		return '<div class="link-icons '.$class.'">'.implode(' ',$services).'</div>';
	}
}


/*
** Build a link for the footer copyright statement and credit line on homepage
*/
function mh_owner_link(){

	$fallback=(option('author')) ? option('author') : option('site_title');

	$authname=(get_theme_option('sponsor_name')) ? get_theme_option('sponsor_name') : $fallback;

	return $authname;
}


/*
** Build HTML content for homepage widget sections
** Each widget can be used ONLY ONCE
** The "Random or Recent" widget is always used since it's req. for the mobile slider
** If the admin user chooses not to use it, it's included in a hidden container
*/

function homepage_widget_1($content='featured'){
	
	get_theme_option('widget_section_1') ? $content=get_theme_option('widget_section_1') : null;
	
	return $content;
}

function homepage_widget_2($content='tours'){
	
	get_theme_option('widget_section_2') ? $content=get_theme_option('widget_section_2') : null;
	
	return $content;	
}

function homepage_widget_3($content='recent_or_random'){
	
	get_theme_option('widget_section_3') ? $content=get_theme_option('widget_section_3') : null;
	
	return $content;	
}

function homepage_widget_sections($html=null){
		
		$recent_or_random_isset=0; 
		$tours_isset=0;
		$featured_isset=0;
		$popular_tags=0;
		
		foreach(array(homepage_widget_1(),homepage_widget_2(),homepage_widget_3()) as $setting){
			
			switch ($setting) {
			    case 'featured':
			        $html.= ($featured_isset==0) ? '<section id="featured-story">'.mh_display_random_featured_item(true,3).'</section>' : null;
			        $featured_isset++;
			        break;
			    case 'tours':
			        $html.= ($tours_isset==0) ? '<section id="home-tours">'.mh_display_homepage_tours().'</section>' : null;
			        $tours_isset++;
			        break;
			    case 'recent_or_random':
			        $html.= ($recent_or_random_isset==0) ? '<section id="home-item-list">'.mh_home_item_list().'</section>' : null;
			        $recent_or_random_isset++;
			        break;
			    case 'popular_tags':
			        $html.= ($popular_tags==0) ? '<section id="home-popular-tags">'.mh_home_popular_tags().'</section>' : null;
			        $popular_tags++;
			        break;

			    default:
			    	$html.=null;
			}
			
		}
		
		// we need to use this one at least once for the mobile slider. if it's unused, we'll include it in a hidden div
		$html.= ($recent_or_random_isset==0) ? '<section class="hidden" id="home-item-list">'.mh_home_item_list().'</section>' : null;
		
		return $html;


}



/*
** Get recent/random items for use in mobile slideshow on homepage
*/
function mh_random_or_recent($mode='recent',$num=4){
	
	switch ($mode){
	
	case 'random':
		$items=get_random_featured_items($num,true);
		$param="Random";
		break;
	case 'recent':
		$items=get_records('Item', array('hasImage'=>true,'sort_field' => 'added', 'sort_dir' => 'd'), $num);
		$param="Recent";
		break;
		
	}

	
	set_loop_records('items',$items);

	$html=null;
	$labelcount='<span>'.total_records('Item').' '.mh_item_label('plural').'</span>';
		
	if (has_loop_records('items')){
			
		$html.=($num <=1) ? '<h2>'.__('%s1 %s2', $param, mh_item_label()).'</h2>' : '<h2>'.__('%1s %2s', $param, mh_item_label('plural')).'</h2>';
		
		$html.= '<div class="rr-results">';	
			
		foreach (loop('items') as $item){
			$html.= '<article class="item-result has-image">';

			$html.= '<h3>'.link_to_item(metadata($item,array('Dublin Core','Title')),array('class'=>'permalink')).'</h3>';

			$hasImage=metadata($item, 'has thumbnail');
			if ($hasImage){
				preg_match('/<img(.*)src(.*)=(.*)"(.*)"/U', item_image('fullsize'), $result);
				$item_image = array_pop($result);
			}

			$html.= isset($item_image) ? link_to_item('<span class="item-image" style="background-image:url('.$item_image.');"></span>') : null;


			if($desc = mh_the_text($item,array('snippet'=>200))){
				$html.= '<div class="item-description">'.strip_tags($desc).'</div>';
			}else{
				$html.= '<div class="item-description">'.__('Text preview unavailable.').'</div>';
			}

			$html.= '</article>';

		}
		$html.= '</div>';	
		$html.= '<p class="view-more-link">'.link_to_items_browse(__('Browse all %s',$labelcount)).'</p>';

		
	}else{
		$html .= '<article class="recent-random-result none">';
		$html .= '<p>'.__('No %s items are available.',$mode).'</p>';
		$html .= '</article><div class="clearfix"></div>';
	}

	
	return $html;	
	
}
/*
** Icon file for mobile devices
*/
function mh_apple_icon_logo_url()
{
	$apple_icon_logo = get_theme_option('apple_icon_144');

	$logo_img = $apple_icon_logo ? WEB_ROOT.'/files/theme_uploads/'.$apple_icon_logo : img('Icon.png');

	return $logo_img;
}


/*
** Background image
*/
function mh_bg_url()
{
	$bg_image = get_theme_option('bg_img');

	$img_url = $bg_image ? WEB_ROOT.'/files/theme_uploads/'.$bg_image : null;

	return $img_url;
}

/*
** Custom link color - Primary
*/
function mh_link_color()
{
	$color = get_theme_option('link_color');

	if ( ($color) && (preg_match('/^#[a-f0-9]{6}$/i', $color)) ){
		return $color;
	}
}

/*
** Custom link color - Secondary
*/
function mh_secondary_link_color()
{
	$color = get_theme_option('secondary_link_color');

	if ( ($color) && (preg_match('/^#[a-f0-9]{6}$/i', $color)) ){
		return $color;
	}
}
/*
** Custom CSS
*/
function mh_configured_css(){
	$bg_url=mh_bg_url();
	$bg = $bg_url ? 'background-image: url('.$bg_url.');background-attachment: fixed; ' : '';
	$color_primary=mh_link_color();
	$color_secondary=mh_secondary_link_color();
	$user_css= get_theme_option('custom_css') ? '/* Theme Option CSS */ '.get_theme_option('custom_css') : null;
	return '<style type="text/css">
	</style>
	';
}


/*
** Which fonts/service to use?
** Typekit, FontDeck, Monotype or fallback to defaults using Google Fonts
*/
function mh_font_config(){
	if($tk=get_theme_option('typekit')){
		$config="typekit: { id: '".$tk."' }";
	}elseif($fd=get_theme_option('fontdeck')){
		$config="fontdeck: { id: '".$fd."' }";
	}elseif($fdc=get_theme_option('fonts_dot_com')){
		$config="monotype: { projectId: '".$fdc."' }";
	}else{
		$config="google: { families: [ 'Raleway:latin', 'Playfair+Display:400,300,600:latin' ] }";
	}
	return $config;
}


/*
** Web Font Loader async script
** https://developers.google.com/fonts/docs/webfont_loader
** see also screen.css
*/
function mh_web_font_loader(){ ?>
<script type="text/javascript">
	WebFontConfig = {
		<?php echo mh_font_config(); ?>
	};
	(function() {
		var wf = document.createElement('script');
		wf.src = ('https:' == document.location.protocol ? 'https' : 'http') +
		'://ajax.googleapis.com/ajax/libs/webfont/1/webfont.js';
		wf.type = 'text/javascript';
		wf.async = 'true';
		var s = document.getElementsByTagName('script')[0];
		s.parentNode.insertBefore(wf, s);
	})(); 
</script>	
<?php }

/*
** Google Analytics
*/
function mh_google_analytics($webPropertyID=null){
	$webPropertyID= get_theme_option('google_analytics');
	if ($webPropertyID!=null){
		echo "
		<!-- Google Analytics -->
		<script type=\"text/javascript\">
			var _gaq = _gaq || [];
			_gaq.push(['_setAccount', '".$webPropertyID."']);
			_gaq.push(['_trackPageview']);
			
			(function() {
			var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
			ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
			var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
			})();
	  	</script>";
	}
}

/*
** About text
*/
function mh_about($text=null){
	if (!$text) {
		// If the 'About Text' option has a value, use it. Otherwise, use default text
		$text =
			get_theme_option('about') ?
			get_theme_option('about') :
			__('%s is powered by <a href="http://omeka.org/">Omeka</a> + <a href="http://curatescape.org/">Curatescape</a>, a humanities-centered web and mobile framework available for both Android and iOS devices.',option('site_title'));
	}
	return $text;
}

/*
**
*/
function mh_license(){
	$cc_license=get_theme_option('cc_license');
	$cc_version=get_theme_option('cc_version');
	$cc_jurisdiction=get_theme_option('cc_jurisdiction');
	$cc_readable=array(
		'1'=>'1.0',
		'2'=>'2.0',
		'2-5'=>'2.5',
		'3'=>'3.0',
		'4'=>'4.0',
		'by'=>'Attribution',
		'by-sa'=>'Attribution-ShareAlike',
		'by-nd'=>'Attribution-NoDerivs',
		'by-nc'=>'Attribution-NonCommercial',
		'by-nc-sa'=>'Attribution-NonCommercial-ShareAlike',
		'by-nc-nd'=>'Attribution-NonCommercial-NoDerivs'
	);
	$cc_jurisdiction_readable=array(
		'intl'=>'International',
		'ca'=>'Canada',
		'au'=>'Australia',
		'uk'=>'United Kingdom (England and Whales)',
		'us'=>'United States'
	);
	if($cc_license != 'none'){
		return __('This work is licensed by '.mh_owner_link().' under a <a rel="license" href="http://creativecommons.org/licenses/'.$cc_license.'/'.$cc_readable[$cc_version].'/'.($cc_jurisdiction !== 'intl' ? $cc_jurisdiction : null).'">Creative Commons '.$cc_readable[$cc_license].' '.$cc_readable[$cc_version].' '.$cc_jurisdiction_readable[$cc_jurisdiction].' License</a>.');
	}else{
		return __('&copy; %1$s %2$s', date('Y'), mh_owner_link() );
	}
}


/*
** Edit item link
*/
function link_to_item_edit($item=null,$pre=null,$post=null)
{
	if (is_allowed($item, 'edit')) {
		return $pre.'<a class="edit" href="'. html_escape(url('admin/items/edit/')).metadata('item','ID').'">'.__('Edit Item').'</a>'.$post;
	}
}

/*
** File item link
*/
function link_to_file_edit($file=null,$pre=null,$post=null)
{
	if (is_allowed($file, 'edit')) {
		return $pre.'<a class="edit" href="'. html_escape(url('admin/files/edit/')).metadata('file','ID').'">'.__('Edit File Details').'</a>'.$post;
	}
}

/*
** Display notice to admins if item is private
*/
function item_is_private($item=null){
	if(is_allowed($item, 'edit') && ($item->public)==0){
		return '<div class="item-is-private">This item is private.</div>';
	}else{
		return null;
	}
}


/*
** iOS Smart Banner
** Shown not more than once per day
*/
function mh_ios_smart_banner(){
	// show the iOS Smart Banner once per day if the app ID is set
	$appID = (get_theme_option('ios_app_id')) ? get_theme_option('ios_app_id') : false;
	if ($appID != false){
		$AppBanner = 'Curatescape_AppBanner_'.$appID;
		$numericID=str_replace('id', '', $appID);
		if (!isset($_COOKIE[$AppBanner])){
			echo '<meta name="apple-itunes-app" content="app-id='.$numericID.'">';
			setcookie($AppBanner, true,  time()+86400); // 1 day
		}
	}
}
?>