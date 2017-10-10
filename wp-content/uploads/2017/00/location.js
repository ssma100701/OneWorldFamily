var map;
var markers = [];
var infoWindow;
var locationSelect;

function initMap() {

var melbourne = {lat: -37.8685266, lng: 144.9304371};
map = new google.maps.Map(document.getElementById('map'), {
	center: melbourne,
	zoom: 11,
	mapTypeId: 'roadmap',
	mapTypeControlOptions: {style: google.maps.MapTypeControlStyle.DROPDOWN_MENU}
});
infoWindow = new google.maps.InfoWindow();

searchButton = document.getElementById("searchButton").onclick = searchLocations;
locationSelect = document.getElementById("locationSelect");
locationSelect.onchange = function() {
var markerNum = locationSelect.options[locationSelect.selectedIndex].value;
if (markerNum != "none"){
	google.maps.event.trigger(markers[markerNum], 'click');
	}
};
}

  function searchLocations() {
   var address = document.getElementById("addressInput").value;
   var geocoder = new google.maps.Geocoder();
   geocoder.geocode({address: address}, function(results, status) {
     if (status == google.maps.GeocoderStatus.OK) {
      searchLocationsNear(results[0].geometry.location);
     } else {
       alert(address + ' not found');
     }
   });
 }



function downloadUrl(url, callback) {
  var request = window.ActiveXObject ?
      new ActiveXObject('Microsoft.XMLHTTP') :
      new XMLHttpRequest;

  request.onreadystatechange = function() {
    if (request.readyState == 4) {
      request.onreadystatechange = doNothing;
      callback(request, request.status);
    }
  };

  request.open('GET', url, true);
  request.send(null);
}

function clearLocations() {
   infoWindow.close();
   for (var i = 0; i < markers.length; i++) {
     markers[i].setMap(null);
   }
   markers.length = 0;
   locationSelect.innerHTML = "";
   var option = document.createElement("option");
   option.value = "none";
   option.innerHTML = "See all results:";
   locationSelect.appendChild(option);
 }

 function searchLocationsNear(center) {
   clearLocations();

   var radius = document.getElementById('radiusSelect').value;
   var category = document.getElementById('radioSelect').value;
   var searchUrl = 'http://oneworldfamily.ga/wp-content/uploads/2017/00/'+ category + '.php?lat=' + center.lat() + '&lng=' + center.lng() + '&radius=' + radius;
   downloadUrl(searchUrl, function(data) {
   	var xml = data.responseXML;
      var markers = xml.documentElement.getElementsByTagName('marker');
      if(markers.length>0){
      var i = 0;
      var bounds = new google.maps.LatLngBounds();
      Array.prototype.forEach.call(markers, function(markerElem) {
        var name = markerElem.getAttribute('name');
        var address = markerElem.getAttribute('address');
        var distance = parseFloat(markerElem.getAttribute("distance"));
        var latlng = new google.maps.LatLng(
            parseFloat(markerElem.getAttribute('lat')),
            parseFloat(markerElem.getAttribute('lng')));
       createOption(name, distance, i++);
       createMarker(latlng, name, address);
       bounds.extend(latlng);
      });
      	map.fitBounds(bounds);
     		locationSelect.style.visibility = "visible";
     		locationSelect.onchange = function() {
       	var markerNum = locationSelect.options[locationSelect.selectedIndex].value;
       	google.maps.event.trigger(markers[markerNum], 'click');
     		};
      } else {
      	alert('No result');
      };         
   });
 }

 function createMarker(latlng, name, address) {
    var html = "<b>" + name + "</b> <br/>" + address;
    var marker = new google.maps.Marker({
      map: map,
      position: latlng
    });
    google.maps.event.addListener(marker, 'click', function() {
      infoWindow.setContent(html);
      infoWindow.open(map, marker);
    });
    markers.push(marker);
  }

 function createOption(name, distance, num) {
    var option = document.createElement("option");
    option.value = num;
    option.innerHTML = name;
    locationSelect.appendChild(option);
 }

 function parseXml(str) {
    if (window.ActiveXObject) {
      var doc = new ActiveXObject('Microsoft.XMLDOM');
      doc.loadXML(str);
      return doc;
    } else if (window.DOMParser) {
      return (new DOMParser).parseFromString(str, 'text/xml');
    }
 }


function doNothing() {}