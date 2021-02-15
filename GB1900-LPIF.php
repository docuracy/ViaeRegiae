<?php 

// Conversion of GB1900_abridged .csv for minimal import to Recogito
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

$row = 0;
$records = 0;
$lpif = "./data/gb1900_abridged.lpf.json"; // Must have 'lpf' somewhere in the filename and .json file extension for successful import to Recogito
if (($handle = fopen("./data/gb1900_abridged.csv", "r")) !== FALSE) {
    while (($data = fgetcsv($handle)) !== FALSE) {
        $row++;
        if($row==1) {
            file_put_contents($lpif, '{"type":"FeatureCollection","features":['); // Opening of GeoJSON-LD wrapper            
            continue; // Drop header: pin_id,final_text,nation,local_authority,parish,osgb_east,osgb_north,latitude,longitude,notes
        }
        elseif (($row>1) && ($row<=10000000) && count($data)>1) {
            $json = array();
            $json[] = '"@id":"https://www.pastplace.org/gb1900/'.clean($data[0]).'"';
            $json[] = '"properties":{ "title":"'.clean($data[1]).'"}';
            $json[] = '"namings":[{"toponym":"'.clean($data[1]).','.clean($data[4]).','.clean($data[2]).'"}]';
            $json[] = '"geometry":{ "type":"Point","coordinates":['.clean($data[8]).','.clean($data[7]).']}';
            $json = "{".implode(",",$json)."}";
            if(isJson($json)){
                file_put_contents($lpif, ($row>2 ? ',' : '').$json, FILE_APPEND);
            }
            else{
                echo "Invalid JSON at row ".$row.": ".$json."<br/>";
            }
            $records++;
        }
        else break;
    }
    echo $records." records extracted from ".$row." rows.<br/>";
    fclose($handle);
    file_put_contents($lpif, ']}', FILE_APPEND); // Closing of GeoJSON-LD wrapper
}
// 51 Error reports:
// Invalid JSON at row 49716: {"@id":"https://www.pastplace.org/gb1900/580ce2d22c66dc1ce502f898","properties":{ "title":"Note. The boundaries of the Colleges and Halls which constitute the Parish of Oxford University"},"namings":[{"toponym":"Note. The boundaries of the Colleges and Halls which constitute the Parish of Oxford University, or in which they are locally situated., Colleges and Halls are not shewn"}],"geometry":{ "type":"Point","coordinates":[452927.652785526,Oxford (Un-parished)]}}
// Invalid JSON at row 65665: {"@id":"https://www.pastplace.org/gb1900/580ce3b02c66dc1ce502f991","properties":{ "title":"Note.-The boundaries of the Colleges and Halls which constitute the Parish of Oxford University"},"namings":[{"toponym":"Note.-The boundaries of the Colleges and Halls which constitute the Parish of Oxford University, or in which they are locally situated., Colleges and Halls are not shewn"}],"geometry":{ "type":"Point","coordinates":[453968.57803678,Oxford (Un-parished)]}}
// Invalid JSON at row 142228: {"@id":"https://www.pastplace.org/gb1900/584567392c66dc0ad4001272","properties":{ "title":"Human Remains"},"namings":[{"toponym":"Human Remains, &c. found A. D. 1825, Swords"}],"geometry":{ "type":"Point","coordinates":[433425.542765691,Wakefield (Un-parished)]}}
// Invalid JSON at row 315353: {"@id":"https://www.pastplace.org/gb1900/52a89ca14dde3e00080041e7","properties":{ "title":"Coins of Elizabeth"},"namings":[{"toponym":"Coins of Elizabeth, found (A. D. 1814), James 1"}],"geometry":{ "type":"Point","coordinates":[322275.784001023,Llangunllo]}}
// Invalid JSON at row 353708: {"@id":"https://www.pastplace.org/gb1900/595045992c66dc085a0db8ff","properties":{ "title":"Urns"},"namings":[{"toponym":"Urns, and pieces of armour found A. D. 1834, Campkettles"}],"geometry":{ "type":"Point","coordinates":[291707.511136996,Fowlis Wester]}}
// Invalid JSON at row 364755: {"@id":"https://www.pastplace.org/gb1900/57eeab552c66dca3220085f3","properties":{ "title":"Sword"},"namings":[{"toponym":"Sword, found A. D. 1823, Blades"}],"geometry":{ "type":"Point","coordinates":[304389.834381841,Rafford]}}
// Invalid JSON at row 441557: {"@id":"https://www.pastplace.org/gb1900/591612782c66dc085a0049ca","properties":{ "title":"Two Urns"},"namings":[{"toponym":"Two Urns, found A. D. 1850., containing flint arrowheads"}],"geometry":{ "type":"Point","coordinates":[237782.422313984,Stewarton]}}
// Invalid JSON at row 445253: {"@id":"https://www.pastplace.org/gb1900/581d22892c66dc2d420175f1","properties":{ "title":"Stone Coffin"},"namings":[{"toponym":"Stone Coffin, and Arrow Heads found A. D. 1897., Human Remains"}],"geometry":{ "type":"Point","coordinates":[383494.646891023,Newhills]}}
// Invalid JSON at row 502471: {"@id":"https://www.pastplace.org/gb1900/5878f03f2c66dc67e202e597","properties":{ "title":"Flint Implements in large numbers"},"namings":[{"toponym":"Flint Implements in large numbers, Shell Mounds, Rude Pottery"}],"geometry":{ "type":"Point","coordinates":[Highland,Scotland]}}
// Invalid JSON at row 538372: {"@id":"https://www.pastplace.org/gb1900/596270762c66dc085a11da09","properties":{ "title":"Stone Cists"},"namings":[{"toponym":"Stone Cists, &c. found A. D. 1818 & 1841., Urns"}],"geometry":{ "type":"Point","coordinates":[405462.357046306,Cruden]}}
// Invalid JSON at row 538373: {"@id":"https://www.pastplace.org/gb1900/596270a92c66dc085a11da1f","properties":{ "title":"Stone Cist"},"namings":[{"toponym":"Stone Cist, Flint Battle Axe, Human Remains"}],"geometry":{ "type":"Point","coordinates":[Scotland, & Neck Chain found]}}
// Invalid JSON at row 554664: {"@id":"https://www.pastplace.org/gb1900/59c3d5782c66dc085a1df259","properties":{ "title":"Stone Celt"},"namings":[{"toponym":"Stone Celt, &c. found, Flint Arrowheads"}],"geometry":{ "type":"Point","coordinates":[358667.417530941,Leslie]}}
// Invalid JSON at row 554677: {"@id":"https://www.pastplace.org/gb1900/57f2ee912c66dcab3d0061db","properties":{ "title":"Pley Fauld Site of the Battle of Harlaw Between the Armies of Alexander"},"namings":[{"toponym":"Pley Fauld Site of the Battle of Harlaw Between the Armies of Alexander, Lord of the Isles A. D. 1411, Earl of Mar"}],"geometry":{ "type":"Point","coordinates":[375084.321200251,Chapel of Garioch]}}
// Invalid JSON at row 555340: {"@id":"https://www.pastplace.org/gb1900/58b95df72c66dc21b10107b8","properties":{ "title":"Supposed Site of Ancient Town Stone Cists"},"namings":[{"toponym":"Supposed Site of Ancient Town Stone Cists, Bronze Pins, Urns"}],"geometry":{ "type":"Point","coordinates":[Reay,Highland]}}
// Invalid JSON at row 575426: {"@id":"https://www.pastplace.org/gb1900/52712449e416f0000200543e","properties":{ "title":"FLAT HOLM (Cardiff Co. & Parly. Boro."},"namings":[{"toponym":"FLAT HOLM (Cardiff Co. & Parly. Boro., St. Mary's Ph.), Southern Division"}],"geometry":{ "type":"Point","coordinates":[322482.294319604,]}}
// Invalid JSON at row 580607: {"@id":"https://www.pastplace.org/gb1900/52d2fdf4a6b66f000500a44a","properties":{ "title":"G. W. R. COLEFORD"},"namings":[{"toponym":"G. W. R. COLEFORD, & PONTYPOOL, MONMOUTH"}],"geometry":{ "type":"Point","coordinates":[340104.49624813,Gwehelog Fawr]}}
// Invalid JSON at row 582956: {"@id":"https://www.pastplace.org/gb1900/592162602c66dc085a02fb64","properties":{ "title":"Housle Cairn Stone Cists containing Human Remains"},"namings":[{"toponym":"Housle Cairn Stone Cists containing Human Remains, & Pottery found, Bronze Rings"}],"geometry":{ "type":"Point","coordinates":[312012.859671024,Halkirk]}}
// Invalid JSON at row 583147: {"@id":"https://www.pastplace.org/gb1900/52ded6434caf96000f006136","properties":{ "title":"Undivided Land"},"namings":[{"toponym":"Undivided Land, Penmaen, Common to the Parishes of Llanrhidian Higher"}],"geometry":{ "type":"Point","coordinates":[Wales, and Reynoldston.]}}
// Invalid JSON at row 688756: {"@id":"https://www.pastplace.org/gb1900/58162e322c66dc3849017d1c","properties":{ "title":"Remains of Hyena"},"namings":[{"toponym":"Remains of Hyena, &c. found A. D. 1879., Bear"}],"geometry":{ "type":"Point","coordinates":[539317.047875882,Barrington]}}
// Invalid JSON at row 718187: {"@id":"https://www.pastplace.org/gb1900/58c50ec72c66dcf8fa0107b3","properties":{ "title":"Wheal Brothers (Silver"},"namings":[{"toponym":"Wheal Brothers (Silver, Disused), Lead"}],"geometry":{ "type":"Point","coordinates":[239239.135064759,Calstock]}}
// Invalid JSON at row 749873: {"@id":"https://www.pastplace.org/gb1900/588127712c66dc67e206ed8d","properties":{ "title":"Roman Coins"},"namings":[{"toponym":"Roman Coins, Axes, Urns"}],"geometry":{ "type":"Point","coordinates":[Sandwich,Dover]}}
// Invalid JSON at row 787981: {"@id":"https://www.pastplace.org/gb1900/580239972c66dcab3d06e43a","properties":{ "title":"A large quantity of British & Roman Coins"},"namings":[{"toponym":"A large quantity of British & Roman Coins, &c. found here., Pottery"}],"geometry":{ "type":"Point","coordinates":[505133.180336672,Albury]}}
// Invalid JSON at row 791417: {"@id":"https://www.pastplace.org/gb1900/58dfbecc2c66dc0c3600019b","properties":{ "title":"Castle Field (Roman Pottery"},"namings":[{"toponym":"Castle Field (Roman Pottery, Coins, Tesseroe"}],"geometry":{ "type":"Point","coordinates":[Burgh,Suffolk Coastal]}}
// Invalid JSON at row 869943: {"@id":"https://www.pastplace.org/gb1900/58e26e762c66dcf8fa09f417","properties":{ "title":"Human Remains"},"namings":[{"toponym":"Human Remains, &C. found previous to A. D. 1844., Sword Hilts"}],"geometry":{ "type":"Point","coordinates":[606017.861523883,Old Newton With Dagworth]}}
// Invalid JSON at row 877840: {"@id":"https://www.pastplace.org/gb1900/5921d4be2c66dc085a032fcf","properties":{ "title":"NUTWITH & ROOMER COMMON Common to the Parishes of Burton upon Ure"},"namings":[{"toponym":"NUTWITH & ROOMER COMMON Common to the Parishes of Burton upon Ure, Fearby, Ellingstring"}],"geometry":{ "type":"Point","coordinates":[England, Masham & Swinton with Warthermarske (Bedale & Leyburn Unions)]}}
// Invalid JSON at row 878498: {"@id":"https://www.pastplace.org/gb1900/5a2ec3a52c66dc7912026346","properties":{ "title":"SPAUNTON MOOR (Undivided Moor common to Appleton le Moors"},"namings":[{"toponym":"SPAUNTON MOOR (Undivided Moor common to Appleton le Moors, Lastingham, Hutton le Hole"}],"geometry":{ "type":"Point","coordinates":[Hutton-Le-Hole,Ryedale]}}
// Invalid JSON at row 878580: {"@id":"https://www.pastplace.org/gb1900/582796452c66dcdc110133b9","properties":{ "title":"(Human Remains"},"namings":[{"toponym":"(Human Remains, &c. found), Sword"}],"geometry":{ "type":"Point","coordinates":[456856.340276373,Glen Parva]}}
// Invalid JSON at row 879075: {"@id":"https://www.pastplace.org/gb1900/593fc7d12c66dc5011000277","properties":{ "title":"SPAUNTON MOOR (Undivided Moor common to Appleton le Moors"},"namings":[{"toponym":"SPAUNTON MOOR (Undivided Moor common to Appleton le Moors, Lastingham, Hutton le Hole"}],"geometry":{ "type":"Point","coordinates":[Appleton-Le-Moors,Ryedale]}}
// Invalid JSON at row 879667: {"@id":"https://www.pastplace.org/gb1900/586a8a502c66dc10b807919b","properties":{ "title":"Human Remains"},"namings":[{"toponym":"Human Remains, Fibulae &c. found, Urns"}],"geometry":{ "type":"Point","coordinates":[647524.876078509,Burgh Castle]}}
// Invalid JSON at row 879907: {"@id":"https://www.pastplace.org/gb1900/588f83022c66dc8119022e5a","properties":{ "title":"Site of Tumulus Human Remains"},"namings":[{"toponym":"Site of Tumulus Human Remains, Amber Beads, Bronze Dagger"}],"geometry":{ "type":"Point","coordinates":[Breckland,England]}}
// Invalid JSON at row 880133: {"@id":"https://www.pastplace.org/gb1900/596886212c66dc085a12f933","properties":{ "title":"Tumulus (Human Remains"},"namings":[{"toponym":"Tumulus (Human Remains, British Pottery, Roman Coin"}],"geometry":{ "type":"Point","coordinates":[Hartington Upper Quarter,High Peak]}}
// Invalid JSON at row 880147: {"@id":"https://www.pastplace.org/gb1900/58c9365f2c66dcf8fa026cc7","properties":{ "title":"Tumuli (Human Remains"},"namings":[{"toponym":"Tumuli (Human Remains, &c. found), Cist"}],"geometry":{ "type":"Point","coordinates":[407947.781268283,Hartington Upper Quarter]}}
// Invalid JSON at row 880388: {"@id":"https://www.pastplace.org/gb1900/59688e202c66dc085a12fa47","properties":{ "title":"Gib Hill (Tumulus) (Human Remains"},"namings":[{"toponym":"Gib Hill (Tumulus) (Human Remains, &c. found), Cist"}],"geometry":{ "type":"Point","coordinates":[415695.799113331,Middleton and Smerrill]}}
// Invalid JSON at row 880389: {"@id":"https://www.pastplace.org/gb1900/59688f112c66dc085a12facc","properties":{ "title":"Tumulus Human Remains"},"namings":[{"toponym":"Tumulus Human Remains, &c found), (Helmet"}],"geometry":{ "type":"Point","coordinates":[414650.230949717,Monyash]}}
// Invalid JSON at row 880747: {"@id":"https://www.pastplace.org/gb1900/58de65ab2c66dcf8fa08a522","properties":{ "title":"Great Down Quarry (Human Remains"},"namings":[{"toponym":"Great Down Quarry (Human Remains, &c. found A. D. 1870-71.), Roman Coins"}],"geometry":{ "type":"Point","coordinates":[379108.609934313,Marnhull]}}
// Invalid JSON at row 881286: {"@id":"https://www.pastplace.org/gb1900/58b7e6d92c66dc21b1009e90","properties":{ "title":"Entrenchment (Site of) (Earthenware"},"namings":[{"toponym":"Entrenchment (Site of) (Earthenware, Coins, Spear Head"}],"geometry":{ "type":"Point","coordinates":[Suffolk Coastal,England]}}
// Invalid JSON at row 881962: {"@id":"https://www.pastplace.org/gb1900/59d676752c66dc87d8005fa8","properties":{ "title":"Human Remains"},"namings":[{"toponym":"Human Remains, Relics, Roman Coins"}],"geometry":{ "type":"Point","coordinates":[Girton,South Cambridgeshire]}}
// Invalid JSON at row 882014: {"@id":"https://www.pastplace.org/gb1900/57f1726e2c66dca32201a9a8","properties":{ "title":"Remains of a Roman burial vault"},"namings":[{"toponym":"Remains of a Roman burial vault, &c. found (A. D. 1810 & 1827), bath"}],"geometry":{ "type":"Point","coordinates":[440350.699237835,Great Tew]}}
// Invalid JSON at row 882116: {"@id":"https://www.pastplace.org/gb1900/5835c66e2c66dc680e04c652","properties":{ "title":"Supposed Site of a Battle between Offa"},"namings":[{"toponym":"Supposed Site of a Battle between Offa, King of Kent, King of Mercia"}],"geometry":{ "type":"Point","coordinates":[Otford,Sevenoaks]}}
// Invalid JSON at row 882461: {"@id":"https://www.pastplace.org/gb1900/588e4f2a2c66dc811901df8b","properties":{ "title":"Roman Remains of Buildings"},"namings":[{"toponym":"Roman Remains of Buildings, Coins, Graves"}],"geometry":{ "type":"Point","coordinates":[Lancing,Adur]}}
// Invalid JSON at row 895975: {"@id":"https://www.pastplace.org/gb1900/58ca8cfc2c66dcf8fa02f182","properties":{ "title":"Bronze & Flint"},"namings":[{"toponym":"Bronze & Flint, Beads &c, Spearheads"}],"geometry":{ "type":"Point","coordinates":[Garton,East Riding of Yorkshire]}}
// Invalid JSON at row 896527: {"@id":"https://www.pastplace.org/gb1900/5957df542c66dc085a0f9b27","properties":{ "title":"Human Remains"},"namings":[{"toponym":"Human Remains, Dagger & Two Hammers found A. D. 1841 & 1868., Silver Coins"}],"geometry":{ "type":"Point","coordinates":[420434.39109053,Kniveton]}}
// Invalid JSON at row 929658: {"@id":"https://www.pastplace.org/gb1900/591c2e722c66dc085a01c83f","properties":{ "title":"Beads"},"namings":[{"toponym":"Beads, &c. found, Fibula"}],"geometry":{ "type":"Point","coordinates":[486421.041723051,Nunburnholme]}}
// Invalid JSON at row 990613: {"@id":"https://www.pastplace.org/gb1900/596886a22c66dc085a12f94b","properties":{ "title":"Tumulus (Human Remains"},"namings":[{"toponym":"Tumulus (Human Remains, & Flint Flakes found), Roman Pottery"}],"geometry":{ "type":"Point","coordinates":[405539.782037879,Hartington Upper Quarter]}}
// Invalid JSON at row 990882: {"@id":"https://www.pastplace.org/gb1900/588cbed12c66dc81190110a8","properties":{ "title":"Tumulus (Human Remains"},"namings":[{"toponym":"Tumulus (Human Remains, Iron Spear &c. found), Cists"}],"geometry":{ "type":"Point","coordinates":[417739.630065397,Little Longstone]}}
// Invalid JSON at row 1004261: {"@id":"https://www.pastplace.org/gb1900/590206c52c66dcf8fa1119cb","properties":{ "title":"LEA"},"namings":[{"toponym":"LEA, AND COTTAM, ASHTON"}],"geometry":{ "type":"Point","coordinates":[348441.075535338,Lea]}}
// Invalid JSON at row 1027210: {"@id":"https://www.pastplace.org/gb1900/595b48f82c66dc085a104788","properties":{ "title":"Bead"},"namings":[{"toponym":"Bead, Celts, Arrows"}],"geometry":{ "type":"Point","coordinates":[Cold Kirby,Ryedale]}}
// Invalid JSON at row 1061812: {"@id":"https://www.pastplace.org/gb1900/596881982c66dc085a12f7fa","properties":{ "title":"Tumulus (Human Remains"},"namings":[{"toponym":"Tumulus (Human Remains, &c found), Urn"}],"geometry":{ "type":"Point","coordinates":[409340.499493895,Hartington Upper Quarter]}}
// Invalid JSON at row 1130227: {"@id":"https://www.pastplace.org/gb1900/59df8c4a2c66dc87d801b062","properties":{ "title":"Tumulus (Site of) (Human Remains"},"namings":[{"toponym":"Tumulus (Site of) (Human Remains, &c found), Knives"}],"geometry":{ "type":"Point","coordinates":[411609.67563271,Hartington Middle Quarter]}}
// Invalid JSON at row 1151321: {"@id":"https://www.pastplace.org/gb1900/59688e5d2c66dc085a12fa6c","properties":{ "title":"Tumulus (Human Remains"},"namings":[{"toponym":"Tumulus (Human Remains, &c. found), Cist"}],"geometry":{ "type":"Point","coordinates":[416094.685415338,Middleton and Smerrill]}}
// Invalid JSON at row 1171780: {"@id":"https://www.pastplace.org/gb1900/582defd52c66dc680e01b0d7","properties":{ "title":"Burrough Hill Encampment Human Remains"},"namings":[{"toponym":"Burrough Hill Encampment Human Remains, Dagger, Flint Arrowhead"}],"geometry":{ "type":"Point","coordinates":[Somerby,Melton]}}
// 1174449 records extracted from 1174451 rows.
?>