<?php 

// Conversion of GB1900_abridged .csv for minimal import to Recogito
// Source data from https://www.visionofbritain.org.uk/data/#tabgb1900
// Source contains 1,174,449 records in 1,174,451 rows.
// Stephen Gadd

ini_set('display_errors', 1);
error_reporting(-1);

function clean($string){ // GB1900 csv is littered with ASC(0) characters and a few double-quotation marks
    return preg_replace('/[\x00-\x1F\x7F\x22]/u', '', $string);
}

function isJson($string) {
    json_decode($string);
    return (json_last_error() == JSON_ERROR_NONE);
}

$chunk_size = 70000; // Recogito crashes with overlarge imports
$row = -2;
$records = 0;
$lpif = "./data/gb1900_abridged_%%%.lpf.json"; // Must have 'lpf' somewhere in the filename and .json file extension for successful import to Recogito
if (($handle = fopen("./data/gb1900_abridged.csv", "r")) !== FALSE) {
    while (($data = fgetcsv($handle)) !== FALSE) {
        $row++;
        if($row==-1) {
            continue; // Drop header: pin_id,final_text,nation,local_authority,parish,osgb_east,osgb_north,latitude,longitude,notes
        }
        if($row % $chunk_size == 0) {
            $chunkfile = str_replace("%%%",str_pad(intdiv($row, $chunk_size),3,"0",STR_PAD_LEFT),$lpif);
            file_put_contents($chunkfile, '{"type":"FeatureCollection","features":['); // Opening of GeoJSON-LD wrapper
        }
        if (count($data)>1) {
            $json = array();
            $json[] = '"@id":"https://www.pastplace.org/gb1900/'.clean($data[0]).'"';
            $json[] = '"properties":{ "title":"'.clean($data[1]).'"}';
            $json[] = '"namings":[{"toponym":"'.clean($data[1]).','.clean($data[4]).','.clean($data[2]).'"}]';
            $json[] = '"geometry":{ "type":"Point","coordinates":['.clean($data[8]).','.clean($data[7]).']}';
            $json = "{".implode(",",$json)."}";
            if(isJson($json)){
                file_put_contents($chunkfile, ($row % $chunk_size == 0 ? '' : ',').$json, FILE_APPEND);
                $records++;
            }
            else{
                echo "Invalid JSON at row ".$row.": ".$json."<br/>";
            }
        }
    }
    echo $records." records extracted from ".$row." rows.<br/>";
    fclose($handle);
    for($chunk=0; $chunk<=intdiv($row, $chunk_size); $chunk++){
        $chunkfile = str_replace("%%%",str_pad($chunk,3,"0",STR_PAD_LEFT),$lpif);
        file_put_contents($chunkfile, ']}', FILE_APPEND); // Closing of GeoJSON-LD wrapper
    }
}
?>
