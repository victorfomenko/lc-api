<?php
    include('dbConnect.php');
    //$url = $_REQUEST["url"];

    $q =    "SELECT SQL_CALC_FOUND_ROWS
                         o.image, o.name, o.phone, o.email, o.city, o.address, o.producttype, o.framesize, o.frametype, o.price, o.date
                        FROM orders AS o
                        ORDER BY id DESC";

    $orders = array();

    if ($dataReader = getDataFromDB($q)){
        $count = count($dataReader);
        for ($i = 0; $i < $count; $i++){
            $data =  $dataReader[$i];

            $order = array();
            $order["name"] =            $data['name'];
            $order["phone"] =           $data['phone'];
            $order["email"] =           $data['email'];
            $order["city"] =            $data['city'];
            $order["address"] =         $data['address'];
            $order["producttype"] =     $data['producttype'];
            $order["framesize"] =       $data['framesize'];
            $order["frametype"] =       $data['frametype'];
            $order["price"] =           $data['price'];
            $order["date"] =            $data['date'];
            $order["image"] =           $data['image'];
            array_push($orders, $order);
        }
    }
    header ("Content-Type: application/json");
    echo(json_encode($orders));

?>