
(function(ReverseGeocoderCache){
  var Client = function(options){
    var self = this;
    
    options = options || {};
    this.options = {};
    this.options.tileBasedCache = options.tileBasedCache || {};
    this.options.dataProvider = options.dataProvider || {};
    
    this.sayHello = function(){
      return "hello";
    };
    
    this.get = function(lat,lng,callback){
      if (self.options.tileBasedCache.exists(lat,lng)){
        var data = JSON.parse(self.options.tileBasedCache.get(lat,lng));
        if (data.status == 'OK'){
          callback(data.content);
        }
        else {
          callback(null);
        }
      }
      else {
        self.options.dataProvider.retrieveData(lat, lng, function(data){
          if (data.status === 'OK'){
            self.options.tileBasedCache.set(lat,lng,JSON.stringify(data));
            callback(data.content);      
          }
          else if (data.status === 'DONT_REPEAT'){
            self.options.tileBasedCache.set(lat,lng,JSON.stringify(data));
            callback(null);
          }
          else {
            callback(null);
          }
        });
      }
    };
  
  };

  ReverseGeocoderCache.Client = Client;
  
})(window.ReverseGeocoderCache = window.ReverseGeocoderCache || {});
;
(function(ReverseGeocoderCache){
  var TileBasedCache = function(options){
    this.options = {};
    this.options.cache = options.cache;
    this.options.prefix = options.prefix;
    this.options.tileSize = options.tileSize;
    
    this.EARTH_RADIUS = 6371000;

  };
  
  TileBasedCache.prototype.sayHello = function(){
    return "hello";
  };


  TileBasedCache.prototype.set = function(lat,lng,data){
    var key = this.getCacheKey(lat,lng);
    this.options.cache.set(key,data);
  };

  TileBasedCache.prototype.get = function(lat,lng){
    var key = this.getCacheKey(lat,lng);
    return this.options.cache.get(key);
  };
  
  TileBasedCache.prototype.exists = TileBasedCache.prototype.get;

  
  TileBasedCache.prototype.getCacheKey = function(lat,lng){
    
   var keySizeRadians = this.getRadiansByDistance( this.options.tileSize ); 
   var latitudeRadians = this.getRadiansByDegree(lat);
   var longitudeRadians = this.getRadiansByDegree(lng);
   var keyLatitude = Math.round(latitudeRadians / this.getLatitudeKeyLength() );
   var keyLongitude = Math.round(longitudeRadians / this.getLongitudeKeyLength(lat) );
   
   return this.options.prefix + 'key-slot-size-' + this.options.tileSize + '-lat-' + keyLatitude + '-lng-' + keyLongitude;
  };

  TileBasedCache.prototype.getRadiansKeySize = function(){
    return this.getRadiansByDistance(this.options.tileSize);
  };
  
  TileBasedCache.prototype.getRadiansByDegree = function(degree){
    return (Math.PI/180)*degree;
  };

  TileBasedCache.prototype.getRadiansByDistance = function(distance){
    return (distance / this.EARTH_RADIUS);
  };

  TileBasedCache.prototype.getLatitudeKeyLength = function(){
    return this.getRadiansKeySize();
  };

  TileBasedCache.prototype.getLongitudeKeyLength = function(lat){
    var operand = (2*Math.sin(this.getRadiansKeySize()/2)) / Math.cos(this.getRadiansByDegree(lat));
    if (operand > Math.PI/2){
      return 1;
    }
    else {
      return Math.asin(operand);
    }
  };

  ReverseGeocoderCache.TileBasedCache = TileBasedCache;
})(window.ReverseGeocoderCache = window.ReverseGeocoderCache || {});


(function(){
  ReverseGeocoderCache.Cache = ReverseGeocoderCache.Cache || {};
  
  var LocalStorage = function(){
    var self = this;

    this.get = function(key){
      return localStorage.getItem(key);
    };

    this.set = function(key, value){
      localStorage.setItem(key,value);
    };
    
  };
  
  
  ReverseGeocoderCache.Cache.LocalStorage = LocalStorage;

})(window.ReverseGeocoderCache = window.ReverseGeocoderCache || {});

;
(function(){
  ReverseGeocoderCache.Cache = ReverseGeocoderCache.Cache || {};
  
  var SimpleCache = function(){
    this.cache = {};
  };
  

  SimpleCache.prototype.get = function(key){
    return this.cache[key];
  };
  
  SimpleCache.prototype.set = function(key, value){
    this.cache[key] = value;
  };
  
  ReverseGeocoderCache.Cache.SimpleCache = SimpleCache;

})(window.ReverseGeocoderCache = window.ReverseGeocoderCache || {});

;
(function(){
  ReverseGeocoderCache.DataProvider = ReverseGeocoderCache.DataProvider || {};
  
  var GoogleGeocoder = function(){
    var self = this;

    this.retrieveData = function(lat,lng,callback){
      var geocoder = new google.maps.Geocoder();

      geocoder.geocode({
        latLng: new google.maps.LatLng(lat, lng)
      }, function(results, status){
        if (status == 'OK'){
          callback({
            status: 'OK',
            content: results[0].formatted_address  
          });
        }
        else if (status == 'ZERO_RESULTS'){
          callback({
            status: 'DONT_REPEAT'
          });
        }
        else {
          console.debug('google geocoder error status: ' + status);
          callback({
            status: 'ERROR'
          });
        }
      });

    };

  };
  
  ReverseGeocoderCache.DataProvider.GoogleGeocoder = GoogleGeocoder;

})(window.ReverseGeocoderCache = window.ReverseGeocoderCache || {});


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////





var produceGeocoder = function(){
  
  //var backendCache = new ReverseGeocoderCache.Cache.LocalStorage();
  var backendCache = new ReverseGeocoderCache.Cache.LocalStorage();
  
  var tileBasedCache = new ReverseGeocoderCache.TileBasedCache({
    prefix: 'ReverseGeocoderCache_',
    tileSize: 50,
    cache: backendCache
  });
  
  var reverseGeocoderCache = new ReverseGeocoderCache.Client({
    tileBasedCache: tileBasedCache,
    dataProvider: new ReverseGeocoderCache.DataProvider.GoogleGeocoder()
    /*
    dataProvider: {
      'retrieveData': function(lat,lng,callback){
        var response = ZeitfadenService.reverseGeoCode(lat, lng, function(){
          
          if (response.status == 'OK'){
            callback({
              status: 'OK',
              content: response.description 
            });
          }
          else if (response.status == 'ZERO_RESULTS'){
            callback({
              status: 'DONT_REPEAT'
            });
          }
          else {
            console.debug('google geocoder error status: ' + response.status);
            callback({
              status: 'ERROR'
            });
          }
        });
      }
    }
    */
  });


  return {
    get: function(lat,lng,callback){
      reverseGeocoderCache.get(lat,lng,callback);
    }
  };
};


var ReverseGeoCacheService = produceGeocoder();

var attachGeoDataToStation = function(myStation){
  /*
  var response = ZeitfadenService.reverseGeoCode(myStation.startLatitude, myStation.startLongitude, function(){
    myStation.startLocationDescription = response.description;
  });
  */
 myStation.dateObject = new Date(myStation.timestamp*1000);
 ReverseGeoCacheService.get(myStation.latitude, myStation.longitude, function(data){
   myStation.locationDescription = data;
 });
};


