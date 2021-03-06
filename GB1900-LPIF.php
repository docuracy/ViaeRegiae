<?php

// Conversion of GB1900_abridged .csv for minimal import to Recogito
// Source contains 1,174,449 records in 1,174,451 rows.
// Stephen Gadd

ini_set('display_errors', 1);
error_reporting(-1);

function clean($string){ // GB1900 csv is littered with ASC(0) characters and a few double-quotation marks
    return trim(preg_replace('/[\x00-\x1F\x7F\x22]/u', '', $string));
}

function isJson($string) {
    json_decode($string);
    return (json_last_error() == JSON_ERROR_NONE);
}

$chunk_size = 70000; // Indexing causes crashes with overlarge imports (dependent on individual server resources)
$row = -2;
$records = 0;
$invalid_coordinates = 0;
$invalid_json = 0;
$empty_titles = 0;
$lpif = "./data/gb1900_abridged_%%%.lpf.json"; // Must have 'lpf' somewhere in the filename and .json file extension for successful import to Recogito
if (($handle = fopen("./data/gb1900_abridged.csv", "r")) !== FALSE) {
    while (($data = fgetcsv($handle)) !== FALSE) { // Some of the failed record imports are probably due to incorrect parsing of the csv by this function (or malformed csv)
        $row++;
        if($row==-1) {
            continue; // Drop header: pin_id,final_text,nation,local_authority,parish,osgb_east,osgb_north,latitude,longitude,notes
        }
        if($row % $chunk_size == 0) {
            $chunkfile = str_replace("%%%",str_pad(intdiv($row, $chunk_size),3,"0",STR_PAD_LEFT),$lpif);
            file_put_contents($chunkfile, '{"type":"FeatureCollection","features":['); // Opening of GeoJSON-LD wrapper
        }
        if (count($data)>1) {
            $title = clean($data[1]);
            if($title==""){
                echo "Empty Title extracted at row ".$row.": ".$data."<br/>";
                $empty_titles++;
                continue;
            }
            $lng = clean($data[8]);
            $lat = clean($data[7]);
            if(abs($lat)>90 || abs($lng)>180){
                echo "Invalid Latitude/Longitude at row ".$row.": ".$lat." | ".$lng.": ".$data."<br/>";
                $invalid_coordinates++;
                continue;
            }
            $json = array();
            $json[] = '"@id":"https://www.pastplace.org/gb1900/'.clean($data[0]).'"';
            $json[] = '"properties":{ "title":"'.$title.'"}';
            $json[] = '"namings":[{"toponym":"'.$title.','.clean($data[4]).','.clean($data[2]).'"}]';
            $json[] = '"geometry":{ "type":"Point","coordinates":['.$lng.','.$lat.']}';
            $json = "{".implode(",",$json)."}";
            if(isJson($json)){
                file_put_contents($chunkfile, ($row % $chunk_size == 0 ? '' : ',').$json, FILE_APPEND);
                $records++;
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
