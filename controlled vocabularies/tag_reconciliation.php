<?php 

// This utility is used to reconcile annotation tags against controlled vocabularies and a corrections list.
// STEP 1: Run this script to generate a specially-formatted list of Recogito documents bearing uncontrolled tags.
// STEP 2: Paste the output into a readable file (e.g. the raw version of a file saved to Github).
// STEP 3: Edit the readable file, adding replacement tags (from your loaded controlled vocabulary) after uncontrolled ones, for example:
//          vllage, village
//          small town, village
//          unknown,
//          butterfingers, DELETE
// - adding nothing after the comma will leave the tag unchanged.
// - adding the word 'DELETE' will delete the offending tag from the annotation.
// STEP 4: Run the script again: replacements and deletions will be made, and a new list generated.
//
// Stephen Gadd, Docuracy Ltd

ini_set('display_errors', 1);
error_reporting(-1);

// Template to return all annotations for a specific document
$json_all_annotations = '{
    "from" : 0,
    "size" : 5000,
    "query": {
        "bool": {
            "must": {
                "match": { "annotates.document_id":   "%%%%" }
            }
        }
    }
}';

// Fetch corrections list
if (($handle = fopen("https://raw.githubusercontent.com/docuracy/ViaeRegiae/main/controlled%20vocabularies/tag%20reconciliation", "r")) !== FALSE) { // *Replace with the url of the file generated in STEP 1
    $skip = true;
    $document_id = "";
    $corrections = array();
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        if($skip && $data[0]=="Uncontrolled:"){ // start of corrections
            $skip = false;
            continue;
        }
        elseif($skip){ // not in corrections section
            continue;            
        }
        elseif(count($data)>1 && $data[1]==""){ // no correction suggested
            continue;
        }
        elseif(count($data)<2){ // end of corrections (including uncorrected final line)
            $skip = true;
            continue;
        }
        elseif($data[0]=="document_id"){
            $document_id = trim($data[1]);
            continue;
        }
        $corrections[$document_id]["original"][] = '"value":"'.trim($data[0]).'"';
        $corrections[$document_id]["replacement"][] = '"value":"'.trim($data[1]).'"';
    }
    fclose($handle);
}

// Initialise cURL
$ch = curl_init();
curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true);

// Initialise cURL2
$ch2 = curl_init();
curl_setopt( $ch2, CURLOPT_CUSTOMREQUEST, 'PUT');
curl_setopt( $ch2, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt( $ch2, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt( $ch2, CURLOPT_RETURNTRANSFER, true);

// Get list of document ids with controlled vocabularies
$dbconn = pg_connect("host=localhost port=5432 dbname=******** user=******** password=********"); // *Replace with values set during Recogito setup
$results = pg_query($dbconn, "select document_id, preference_value from document_preferences WHERE preference_name='tag.vocabulary'");
while ($document = pg_fetch_array($results)) {
    $vocabulary = array();
    $uncontrolled = array();
    $vocabulary_objs = json_decode( $document['preference_value'] );
    foreach($vocabulary_objs as $vocabulary_obj){
        $vocabulary[] = $vocabulary_obj->value;        
    }
    // Load all matching annotations
    curl_setopt( $ch, CURLOPT_URL, 'localhost:9200/recogito/annotation/_search?pretty' );
    curl_setopt( $ch, CURLOPT_POSTFIELDS, preg_replace( "/%%%%/",$document['document_id'],$json_all_annotations ) );
    $es_annotations = curl_exec( $ch );
    
    // Check tags
    $es_annotations = json_decode( $es_annotations );
    foreach($es_annotations->hits->hits as $annotation){
        $annotation_json  = json_encode ($annotation->_source);
        $do_update = false;
        foreach($annotation->_source->bodies as $body){
            if( $body->type !== "TAG" ) continue;
            if( in_array($body->value,$vocabulary) ) continue;
            if ( isset($corrections[$document['document_id']]) && in_array( '"value":"'.$body->value.'"',$corrections[$document['document_id']]['original'] ) ) {
                $do_update = true;
                $annotation_json = str_replace($corrections[$document['document_id']]['original'],$corrections[$document['document_id']]['replacement'],$annotation_json);
                $annotation_json = preg_replace("$\{[^\{]*DELETE[^\}]*\}$", "", $annotation_json); // Tags marked for deletion
                $annotation_json = preg_replace(["$([\[\{]),$","$(,),$","$,([\}\]])$"], ["\\1","\\1","\\1"], $annotation_json); // Tidy up commas after deletion
            }
            else{
                $uncontrolled[] = $body->value;               
            }
        } 
        if ($do_update){
        curl_setopt( $ch2, CURLOPT_URL, 'localhost:9200/recogito/annotation/'.$annotation->_id );
        curl_setopt( $ch2, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Content-Length: ' . strlen($annotation_json)));
        curl_setopt( $ch2, CURLOPT_POSTFIELDS, $annotation_json);
        curl_exec($ch2);            
        }
    }
    $uncontrolled = array_unique( $uncontrolled );
    sort( $uncontrolled, SORT_NATURAL | SORT_FLAG_CASE );
    if ( count($uncontrolled)>0 ){
        $title = pg_query($dbconn, "select title from document WHERE id='".$document['document_id']."'");
        echo "<b>".pg_fetch_result($title, 0, 0)."</b>";
        echo "<br/><br/><b>"."Controlled:"."</b><br/>";
        echo implode (",<br/>", $vocabulary);
        echo "<br/><br/><b>"."Uncontrolled:"."</b><br/>";
        echo "document_id, ".$document['document_id']."<br/>";
        echo implode (",<br/>", $uncontrolled);
        echo "<br/><br/>==================================================<br/><br/>";
    }    
}

curl_close( $ch2 );
curl_close( $ch );


?>
