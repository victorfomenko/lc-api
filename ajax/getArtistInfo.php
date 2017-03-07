<?php
include('dbConnect.php');
	$urlname = file_get_contents('php://input');

    $q =   "SELECT id, username, avatar, website, about
           FROM users
           WHERE urlname = '" . $urlname . "'";

    $dataReader =   getDataFromDB($q);
    $data =         $dataReader[0];
    if($data['id']) {
        $artist = array();
        $artist["id"] =         $data['id'];
        $artist["name"] =       $data['username'];
        $artist["website"] =     $data['website'];
        $artist["avatar"] =     $data['avatar'];
        $artist["about"] =      $data['about'];

        //if( !$limit ) $limit = 20;

        $q2 =   "SELECT SQL_CALC_FOUND_ROWS
                name, rate, fee, filename, seourl, width, height
                FROM pictures
                WHERE deleted=0 AND authorid = '" . $artist["id"] . "'
                ORDER BY rate DESC";

        $listOfPic = array();

        if ($dataReader = getDataFromDB($q2)){
            $count = count($dataReader);
            for ($i = 0; $i < $count; $i++){
                $data =         $dataReader[$i];

                $pic = array();
                $pic["name"] =      $data['name'];
                $pic["author"] =    $artist["name"];
                $pic["rate"] =      $data['rate'];
                $pic["fee"] =       $data['fee'];
                $pic["filename"] =  $data['filename'];
                $pic["seourl"] =    $data['seourl'];
                $pic["full"] =    "/data/full/" . $pic["filename"] . '.jpg';
                $pic["preview"] = "/data/preview/" . $pic["filename"] . '.jpg';
                $pic["width"] =     $data['width'];
                $pic["height"] =    $data['height'];
                array_push($listOfPic, $pic);
            }
            $artist["pictures"] = $listOfPic;
        }
    }
    else {
        $artist = false;
    }

    header ("Content-Type: application/json");
    echo(json_encode($artist));

?>