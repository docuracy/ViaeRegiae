<!DOCTYPE html>
<html>
<head>
	
<title>Recogito Helper</title>

<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="author" content="Stephen Gadd, Docuracy Ltd.">

<link rel="shortcut icon" type="image/x-icon" href="../favicon.ico" />

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" integrity="sha512-xodZBNTC5n17Xt2atTPuE1HxjVMSvLVW9ocqUKLsCC5CXdbqCmblAshOMAS6/keqq/sMZMZ19scR4PsZChSR7A==" crossorigin=""/>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js" integrity="sha512-XQoYMqMTK8LvdxXYG3nZ448hOEQiglfqkJs1NOQV44cWnUrBc8PkAOcXy20w0vlaXaVUearIOBhiXZ5V3ynxwA==" crossorigin=""></script>
<script src="https://cdn.jsdelivr.net/npm/clipboard@2.0.8/dist/clipboard.min.js"></script>

<style>
html, body, #mapid {
    height:100%;
    width:100%;
    padding:0px;
    margin:0px;
} 
</style>
	
</head>
<body>

<div id="mapid"></div>
<script>

function updateURL(p,v){
	if ('URLSearchParams' in window) {
	    var searchParams = new URLSearchParams(window.location.search)
	    searchParams.set(p, v);
	    var newRelativePathQuery = window.location.pathname + '?' + searchParams.toString();
	    history.pushState(null, '', newRelativePathQuery);
	}
}

function selectText(node) {
    node = document.getElementById(node);

    if (document.body.createTextRange) {
        const range = document.body.createTextRange();
        range.moveToElementText(node);
        range.select();
    } else if (window.getSelection) {
        const selection = window.getSelection();
        const range = document.createRange();
        range.selectNodeContents(node);
        selection.removeAllRanges();
        selection.addRange(range);
    } else {
        console.warn("Could not select text in node: Unsupported browser.");
    }
}

var topo = L.tileLayer.wms('http://ows.mundialis.de/services/service?', {
    layers: 'SRTM30-Colored-Hillshade'
});
var OSM = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png');
var OS = L.tileLayer('https://geo.nls.uk/maps/os/1inch_2nd_ed/{z}/{x}/{y}.png', {
    maxZoom: 18,
    id: 'OS 1885-1900 1-inch',
    tileSize: 512,
    zoomOffset: -1,
});

var points = new L.GeoJSON();

function updatePoints(event){
	mymap.closePopup();	
	points.clearLayers();
	if(jQuery(event.target).prop("checked")){
		jQuery.getJSON("./near_points.json.php?point="+mymap.getCenter().lat+","+mymap.getCenter().lng, function (data) {
			points = new L.GeoJSON(data,{
                onEachFeature: function (f, l) {
                	l.bindPopup('<h2>'+f.properties.gazette[1]+'</h2><pre>['+f.properties.gazette[0]+']</pre><button class="btn" data-clipboard-text="['+f.properties.gazette[0]+']">Copy to clipboard</button>');
                }
			}).addTo(mymap);
		});
	}
}

var mymap = L.map('mapid', {
    center: [53.73663780007601, -1.5654937610831177],
    zoom: 14,
    layers: [OSM]
}).on('moveend fullscreenchange', function(e) {
	bounds = mymap.getBounds();
	updateURL("bounds",bounds.getNorthWest().lat.toFixed(4)+"|"+bounds.getNorthWest().lng.toFixed(4)+"|"+bounds.getSouthEast().lat.toFixed(4)+"|"+bounds.getSouthEast().lng.toFixed(4));
});

var baseMaps = {
	"<span>Ordnance Survey 1885-1900</span>": OS,
	"<span>Topography</span>": topo,
	"<span>Modern Roads</span>": OSM
};

var overlayMaps = {
	    "Gazetteer Points": points 
	};

var layerControl = L.control.layers(baseMaps, overlayMaps, {collapsed: false}).addTo(mymap);

jQuery(document).ready(function() {
	jQuery('.leaflet-control-layers-overlays input.leaflet-control-layers-selector:first-of-type').change(function(event){updatePoints(event)});
	var clipboard = new ClipboardJS('.btn');
	clipboard.on('success', function(e) {
		mymap.closePopup();
	});
});

var popup = L.popup();
mymap.on('contextmenu', function(e){popup.setLatLng(e.latlng).setContent('<pre>'+e.latlng.toString().split('Lng')[1].replace(/ /g,"")+'</pre><button class="btn" data-clipboard-text="'+e.latlng.toString().split('Lng')[1].replace(/ /g,"")+'">Copy to clipboard</button>').openOn(mymap);});

</script>

</body>
</html>
