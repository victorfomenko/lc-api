<?php
	include('dbConnect.php');
	$url = file_get_contents('php://input');

    $q1 =   "SELECT p.id, p.name, p.rate, p.fee, p.seourl, p.filename, u.username, u.urlname
             FROM pictures AS p, users AS u
             WHERE p.seourl = '" . $url . "' AND p.deleted = 0 AND p.authorid = u.id";

    $dataReader = getDataFromDB($q1);
    $data =         $dataReader[0];

    $pic = array();
    $pic["name"] =      $data['name'];
    $pic["urlname"] =   $data['urlname'];
    $pic["author"] =    $data['username'];
    $pic["rate"] =      $data['rate'];
    $pic["fee"] =       $data['fee'];
    $pic["filename"] =  $data['filename'];
    $pic["seourl"] =    $data['seourl'];
    $pic["full"] =      "/data/full/" . $pic["filename"] . '.jpg';
    $pic["preview"] =   "/data/preview/" . $pic["filename"] . '.jpg';

    $q2 =  "SELECT sizes.value
            FROM sizes
            LEFT JOIN sizeslist
            ON sizes.id = sizeslist.size_id
            WHERE sizeslist.pictures_id = "  . $data['id'] ;

    $dataReader = getDataFromDB($q2);
    $count = count($dataReader);
    $sizes = array();

    for ($i = 0; $i < $count; $i++){
        $data =         $dataReader[$i];
        $size = array();
        $size["value"] =   $data['value'];
        $sizeName =    explode('|', $data['value']);
        $size["name"] = $sizeName[0] . 'см x ' . $sizeName[1] . 'см';
        array_push($sizes, $size);
    }
    $pic["sizes"] =     $sizes;

    header ("Content-Type: application/json");
    echo(json_encode($pic));
?>