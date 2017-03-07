<?php
include('dbConnect.php');

$imageFolder = '../data/preview/';
$images = array();
if ($handle = opendir($imageFolder)) {
    //Читаем файлы в каталоге
    while (false !== ($file = readdir($handle))) {
        if ($file != "." && $file != "..") {
            $sizeInfo = getimagesize($imageFolder . $file);
            $fileName = explode('.', $file );
            $q =    "UPDATE pictures " .
                    "SET width=" . $sizeInfo[0] . ", height=" . $sizeInfo[1] . " " .
                    "WHERE filename = '" . $fileName[0] . "'";
            insertDataToDB($q);
        }
    }
    closedir($handle);
}
?>