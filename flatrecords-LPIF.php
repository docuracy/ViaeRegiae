<?php

// Conversion of flat records (e.g. csv, tsv) for minimal import to Recogito
// Stephen Gadd

ini_set('display_errors', 1);
error_reporting(-1);

$input_file = "example.csv";
$header_row = true;
$index_id = 2;
$index_id_url = ""; // e.g. "https://www.pastplace.org/gb1900/'
$index_title = 2;
$index_county = 3;
$index_country = false;
$index_geometry = 0; // Point or Latitude
$index_geometry_latlng = 1; // false or Longitude
$disallowed_countries = array("Scotland","Northern Ireland");
$testmode = false;

function clean($string){ // Remove ASC(0) characters, double-quotation marks, and leading/trailing spaces
    return trim(preg_replace('/[\x00-\x1F\x7F\x22]/u', '', $string));
}

function isJson($string) {
    json_decode($string);
    return (json_last_error() == JSON_ERROR_NONE);
}

switch( pathinfo($input_file, PATHINFO_EXTENSION) ){
    case "tsv":
        $delimiter = "\t";
        break;
    default:
        $delimiter = ",";
}

$chunk_size = 70000; // Indexing causes crashes with overlarge imports (dependent on individual server resources)
$row = $header_row ? -2 : -1;
$records = 0;
$invalid_coordinates = 0;
$invalid_json = 0;
$empty_titles = 0;
$lpif = "./data/".pathinfo($input_file, PATHINFO_FILENAME)."_%%%.lpf.json"; // Must have 'lpf' somewhere in the filename and .json file extension for successful import to Recogito
if (($handle = fopen("./data/".$input_file, "r")) !== FALSE) {
    while (($data = fgetcsv($handle,0,$delimiter)) !== FALSE) {
        if ($testmode){
            if ($row == 5) break;
            echo "<br/>".count($data)."<br/>";
            var_dump($data);
            echo "<br/>";            
        }
        $row++;
        if($row==-1) {
            continue; // Drop header row
        }
        if($row % $chunk_size == 0) {
            $chunkfile = str_replace("%%%",str_pad(intdiv($row, $chunk_size),3,"0",STR_PAD_LEFT),$lpif);
            file_put_contents($chunkfile, '{"type":"FeatureCollection","features":['); // Opening of GeoJSON-LD wrapper
        }
        if (count($data)>1) {
            if($index_country){
                $country = clean($data[$index_country]);
                if(in_array($country, $disallowed_countries)){
                    continue;
                }
            }
            $title = clean($data[$index_title]);
            if($title==""){
                echo "Empty Title extracted at row ".$row.": ".$data."<br/>";
                $empty_titles++;
                continue;
            }
            if($index_geometry_latlng){
                $lng = clean($data[$index_geometry_latlng]);
                $lat = clean($data[$index_geometry]);
            }
            else{
                $point = clean($data[$index_geometry]);
                preg_match("^Point\(([-+]?([1-8]?\d(\.\d+)?|90(\.0+)?))\s*([-+]?(180(\.0+)?|((1[0-7]\d)|([1-9]?\d))(\.\d+)?))\)^",$point,$matches);
                $lng = $matches[1];
                $lat = $matches[5];
            }
            if(abs($lat)>90 || abs($lng)>180 || !$lat || !$lng){
                echo "Invalid Latitude/Longitude at row ".$row.": ".$lat." | ".$lng.": ".$data."<br/>";
                $invalid_coordinates++;
                continue;
            }
            $json = array();
            $json[] = '"@id":"'.$index_id_url.urlencode(clean($data[$index_id])).'"';
            $json[] = '"properties":{ "title":"'.$title.'"}';
            $json[] = '"namings":[{"toponym":"'.$title.', '.clean($data[$index_county]).'"}]';
            $json[] = '"geometry":{ "type":"Point","coordinates":['.$lng.','.$lat.']}';
            $json = "{".implode(",",$json)."}";
            if(isJson($json)){
                file_put_contents($chunkfile, ($row % $chunk_size == 0 ? '' : ',').$json, FILE_APPEND);
                $records++;
                if ($testmode){
                    echo $json."<br/>";
                }
            }
            else{
                echo "Invalid JSON at row ".$row.": ".$data." => ".$json."<br/>";
                $invalid_json++;
            }
        }
    }
    echo $records." records extracted from ".$row." rows.<br/>";
    echo "Invalid coordinates extracted from ".$invalid_coordinates." rejected rows.<br/>";
    echo "Invalid JSON generated from ".$invalid_json." rejected rows.<br/>";
    echo "Empty Titles extracted from ".$empty_titles." rejected rows.<br/>";
    fclose($handle);
    for($chunk=0; $chunk<=intdiv($row, $chunk_size); $chunk++){
        $chunkfile = str_replace("%%%",str_pad($chunk,3,"0",STR_PAD_LEFT),$lpif);
        file_put_contents($chunkfile, ']}', FILE_APPEND); // Closing of GeoJSON-LD wrapper
    }
}
?>
