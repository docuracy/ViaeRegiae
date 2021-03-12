<?php 

//ini_set('display_errors', 1);
//error_reporting(-1);

$number = isset($_GET['count']) ? (int)$_GET['count'] : 1000; // Maximum number of gazettes to return
$miles = isset($_GET['miles']) ? (float)$_GET['miles'] : 0.5; // Search radius (in miles)
$point = isset($_GET['point']) ? $_GET['point'] : '53.73663780007601, -1.5654937610831177'; // Comma-separated WGS84 lat,lng coordinates of centre point

// Template for geosearch on all gazetteers
$json_point = '{
    "from" : 0, 
    "size" : '.$number.',
    "query": {
        "bool": {
            "must": {
                "match": { "_type":   "entity" }
            },
            "filter": {
                "geo_distance": {
                    "distance": "'.$miles.'mi",
                    "representative_point": "%%%%"
                }
            }
        }
    }
}';

$geoJSON = '{"type":"Feature","geometry":{"type":"Point","coordinates":%point%},"properties":{"gazette": %gazette%}}';
$output = array();

// Initialise cURL
$ch = curl_init( 'localhost:9200/recogito/_search?pretty' );
curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true);

// Fetch nearby points from all gazetteers
curl_setopt( $ch, CURLOPT_POSTFIELDS, preg_replace( "/%%%%/",$point,$json_point ) );
$es = curl_exec( $ch );
// print_r($es);
$es = json_decode($es);

curl_close( $ch );

// Convert to geoJSON


foreach($es->hits->hits as $feature){
    $output[] = preg_replace( ["/%point%/","/%gazette%/"],[json_encode( $feature->_source->representative_point ),json_encode( [$feature->_source->union_id,$feature->_source->title] )],$geoJSON );
}

header('Content-Type: application/json;charset=utf-8');
echo '[{"type":"FeatureCollection","features":['.implode(",",$output).']}]';

?>


