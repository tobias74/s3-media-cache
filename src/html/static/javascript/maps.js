    
console.log('loading maps.js');

var addYourLocationButton = function(googleMap, marker,callback) 
{
	var controlDiv = document.createElement('div');
	
	var firstChild = document.createElement('button');
	firstChild.style.backgroundColor = '#fff';
	firstChild.style.border = 'none';
	firstChild.style.outline = 'none';
	firstChild.style.width = '28px';
	firstChild.style.height = '28px';
	firstChild.style.borderRadius = '2px';
	firstChild.style.boxShadow = '0 1px 4px rgba(0,0,0,0.3)';
	firstChild.style.cursor = 'pointer';
	firstChild.style.marginRight = '10px';
	firstChild.style.padding = '0px';
	firstChild.title = 'Your Location';
	controlDiv.appendChild(firstChild);
	
	var secondChild = document.createElement('div');
	secondChild.style.margin = '5px';
	secondChild.style.width = '18px';
	secondChild.style.height = '18px';
	secondChild.style.backgroundImage = 'url(//maps.gstatic.com/tactile/mylocation/mylocation-sprite-1x.png)';
	secondChild.style.backgroundSize = '180px 18px';
	secondChild.style.backgroundPosition = '0px 0px';
	secondChild.style.backgroundRepeat = 'no-repeat';
	firstChild.appendChild(secondChild);
	
	google.maps.event.addListener(googleMap, 'dragend', function() {
		$(secondChild).css('background-position', '0px 0px');
	});

	firstChild.addEventListener('click', function() {
		var imgX = '0';
		var animationInterval = setInterval(function(){
			if(imgX == '-18') imgX = '0';
			else imgX = '-18';
			$(secondChild).css('background-position', imgX+'px 0px');
		}, 500);
		if(navigator.geolocation) {
			navigator.geolocation.getCurrentPosition(function(position) {
        		var latlng = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
        		googleMap.setCenter(latlng);
				marker.setPosition(latlng);
				clearInterval(animationInterval);
				$(secondChild).css('background-position', '-144px 0px');
				callback && callback(latlng);
			});
		}
		else{
			clearInterval(animationInterval);
			$(secondChild).css('background-position', '0px 0px');
		}
	});
	
	controlDiv.index = 1;
	googleMap.controls[google.maps.ControlPosition.RIGHT_BOTTOM].push(controlDiv);
}


var setToMyLocation = function(googleMap, marker,callback){
	if(navigator.geolocation) {
		navigator.geolocation.getCurrentPosition(function(position) {
    		var latlng = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
    		googleMap.setCenter(latlng);
			marker.setPosition(latlng);
			callback && callback(latlng);
		});
	}
};


var confirmDelete = function(target){
	if (confirm('Do you relly want to delete this product? This cannot be undone!')) {
		$(target).closest('form').submit();	
	}
};


var initializeStationMap = function(card){
  setTimeout(function(){
  	var mapDiv = card.find('.map-for-station');
  	console.log(mapDiv[0]);
    
    var latitude = parseFloat(card.data('latitude'));
    var longitude = parseFloat(card.data('longitude'));
    
    var stationGoogleMap;

    stationGoogleMap = new google.maps.Map(mapDiv[0], {
      center: {lat: latitude, lng: longitude},
      zoom: 13        
    });
    
    var stationMarker = new google.maps.Marker({
      position: {lat: latitude, lng: longitude},
      map: stationGoogleMap,
      animation:google.maps.Animation.DROP
    });
    
    
  },0);

/*
  centerMarker = new google.maps.Marker({
    map: googleMap,
    animation:google.maps.Animation.DROP
  });
*/      
};



