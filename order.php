<?php

    include('dbConnect.php');
	$data = (json_decode(file_get_contents("php://input")));

	$name =         $data->formName;
	$city =         $data->formCity;
	$address =      $data->formAddress;
	$postal =       $data->formPostal;
	$imageurl =     $data->image;
	$imageBase64 =  $data->imageBase64;
	$phone =        $data->formPhone;
	$email =        $data->formEmail;
	$productType =  $data->formProduct;
	$frameSize =    $data->formFrameSize;
    $frameType =    $data->formFrameType;
    $borderType =   $data->formBorderType;
    $price =        $data->formPrice;

    if ( $imageBase64 != null ) { // upload original file if base64
        $levelUp =                  "..";
        $photo_path = 				"/data";
        $fileExtension =            '.jpg';
        $fileFolder = 				'/uploaded/';
        $fileName = 			    generateRandomString(12);

        $fullFileName =             $fileName . $fileExtension;
        $file_server_folder = 		$levelUp . $photo_path . $fileFolder;
        $file_url_folder =          $photo_path . $fileFolder;

        if ( ! file_exists( $file_server_folder ) ) {
            mkdir($file_server_folder, 0777, true);
        }

        list($type, $imageBase64) = explode(';', $imageBase64);
        list(, $imageBase64)      = explode(',', $imageBase64);
        $file_image =               base64_decode($imageBase64);

        file_put_contents($file_server_folder . $fullFileName, $file_image);

        $imageurl = $file_url_folder . $fullFileName;
    }

    $q =   "INSERT INTO orders (id, name, phone, email, city, address, postalcode, producttype, framesize, frametype, bordertype, price, image)
            VALUES (NULL, '" . $name . "', '". $phone ."',
                          '" . $email . "', '" . $city . "',
                          '" . $address . "', '" . $postal . "',
                          '" . $productType . "', '" . $frameSize . "',
                          '" . $frameType . "', '" . $borderType . "',
                          '" . $price . "', '" . $imageurl . "')";
    insertDataToDB($q);

	$subject = 'Заказ картины';
	$message = "Имя: " . $name . "<br>";
	$message .= "Телефон: " . $phone . "<br>";
	$message .= "E-mail: " . $email . "<br>";
	$message .= "Город: " . $city . "<br>";
	$message .= "Адрес: " . $address . "<br>";
	$message .= "Индекс: " . $postal . "<br>";
	$message .= "Тип картины: " . $productType . "<br>";
	$message .= "Размеры: " . $frameSize . "<br>";
	$message .= "Тип рамы: " . $frameType . "<br>";
	$message .= "Края: " . $borderType . "<br>";
	$message .= "Цена: " . $price . "<br>";
	$message .= "Фото: <a href='https://lovecanvas.ru" . $imageurl . "'>Photo</a><br>";
	sendEmail("info@lovecanvas.ru", $subject, $message);

	function sendEmail( $email, $subject, $message ) {

		$subject = 	"=?UTF-8?B?".base64_encode($subject)."?=";
		//$message = 	iconv( 'CP1251', 'UTF-8', $message );

		$headers  = "Content-Type: text/html; charset=utf-8\r\n";
		$headers .= "From: Love Canvas <order@lovecanvas.ru>\r\n";
		$headers .= "Reply-To: Love Canvas <order@lovecanvas.ru>\r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Message-ID: <". time() .rand(1,1000). "@".$_SERVER['SERVER_NAME'].">". "\r\n";
		$headers .= "X-Mailer: Exim 4.6\r\n";
		$headers .= "Organization: Love Canvas\r\n";
		/*$headers .= "X-Priority: 1\r\n";*/
		/*$headers .= "X-MSMail-Priority: High\r\n";*/

		mail( $email, $subject, $message, $headers );
	}
    function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
    header ("Content-Type: application/json");
	print(json_encode(array("status"=>"ok")));
	exit;

?>