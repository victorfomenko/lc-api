<?php
class FileSave {
    public $actions = array();
    public function __construct()
    {
        $database = new Database();
        $this->db =  $database->db;
    }

    public function isFileExistInRequest ($fieldName) {
        $uploadOk = true;
        // Check if image file is a actual image or fake image
        if(isset($_POST["submit"])) {
            $check = getimagesize($_FILES[$fieldName]["tmp_name"]);
            if($check !== false) {
                echo "File is an image - " . $check["mime"] . ".";
                $uploadOk = true;
            } else {
                echo "File is not an image.";
                $uploadOk = false;
            }
        }
        return $uploadOk;
    }

    public function isFileAlreadyExistInFolder ($target_file) {
        $isExist = false;
        // Check if file already exists
        if (file_exists($target_file)) {
            $isExist = true;
        }
        return $isExist;
    }

    public function incorrectFileFormat ($imageFileType) {
        $allow = false;
        // Allow certain file formats
        if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
            && $imageFileType != "gif" ) {
            //echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            $allow = true;
        }
        return $allow;
    }
    public function uploadAvatar ($target_dir, $requestFiledName, $userurl, $resize= true){
        $imageFileType =    pathinfo($_FILES[$requestFiledName]["name"], PATHINFO_EXTENSION);
        $file_name =        $this->generateRandomString(12); //generate random name
        $target_file =      $target_dir . basename($file_name) . '.' . $imageFileType;

        if(!$this->isFileExistInRequest($requestFiledName)){
            echo "Sorry, file is not an image";
            die();
        }
        if($this->isFileAlreadyExistInFolder($target_file)) {
            echo "File already exist in target folder";
            die();
        }
        if($this->incorrectFileFormat($imageFileType)) {
            echo "Sorry, file format is incorrect";
            die();
        }


        if (move_uploaded_file($_FILES[$requestFiledName]["tmp_name"], $target_file)) {
            $thumb = new thumbnail($target_file,150,150);
            $query = "update users set avatar = :avatar where urlname = :urlname";
            $sth = $this->db->prepare($query);

            try {
                $this->db->beginTransaction();
                $result = $sth->execute(
                    array(
                        ":urlname"  =>  $userurl,
                        ":avatar" =>    $thumb,
                    )
                );
                $this->db->commit();
            } catch (\PDOException $e) {
                $this->db->rollback();
                echo "Database error: " . $e->getMessage();
                die();
            }

            if (!$result) {
                $info = $sth->errorInfo();
                printf("Database error %d %s", $info[1], $info[2]);
                die();
            }
            $result = array(
                'avatar' => $thumb->filename
            );
            return $result;
        } else {
            echo "Sorry, there was an error uploading your file.";
            die();
        }
    }
    public function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}



// Imaging
class imaging
{
    // Variables
    private $img_input;
    private $img_output;
    private $img_src;
    private $format;
    private $quality = 80;
    private $x_input;
    private $y_input;
    private $x_output;
    private $y_output;
    private $x_src;
    private $y_src;
    private $resize;

    // Set image
    public function set_img($img)
    {
        // Find format
        $ext = strtoupper(pathinfo($img, PATHINFO_EXTENSION));
        // JPEG image
        if(is_file($img) && ($ext == "JPG" OR $ext == "JPEG"))
        {
            $this->format = $ext;
            $this->img_input = ImageCreateFromJPEG($img);
            $this->img_src = $img;
        }
        // PNG image
        elseif(is_file($img) && $ext == "PNG")
        {
            $this->format = $ext;
            $this->img_input = ImageCreateFromPNG($img);
            $this->img_src = $img;
        }
        // GIF image
        elseif(is_file($img) && $ext == "GIF")
        {
            $this->format = $ext;
            $this->img_input = ImageCreateFromGIF($img);
            $this->img_src = $img;
        }
        // Get dimensions
        $this->x_input = imagesx($this->img_input);
        $this->y_input = imagesy($this->img_input);
    }

    // Set maximum image size (pixels)
    public function set_size($max_x = 100,$max_y = 100, $square = true)
    {
        $this->max_x = $max_x;
        $this->max_y = $max_y;
        // Resize
        if($this->x_input > $max_x || $this->y_input > $max_y)
        {
            $this->x_src = 0;
            $this->y_src = 0;
            $a= $max_x / $max_y;
            $b= $this->x_input / $this->y_input;
            if ($a<$b)
            {
                $this->x_output = $max_x;
                $this->y_output = ($max_x / $this->x_input) * $this->y_input;
            }
            else
            {
                $this->y_output = $max_y;
                $this->x_output = ($max_y / $this->y_input) * $this->x_input;

            }
            if($square) {
                $max = max(array($max_x,$max_y));
                $min_input = min(array($this->x_input, $this->y_input));
                $this->x_output = $max;
                $this->y_output = $max;
                if($b<1){
                    $this->y_src = $this->y_input/2 - $this->x_input/2;
                    $this->x_src = 0;
                    $this->y_input = $min_input;

                }else{
                    $this->x_src = $this->x_input/2 - $this->y_input/2;
                    $this->y_src = 0;
                    $this->x_input = $min_input;
                }
            }
            // Ready
            $this->resize = TRUE;
        }
        // Don't resize
        else { $this->resize = FALSE; }
    }
    // Set image quality (JPEG only)
    public function set_quality($quality)
    {
        if(is_int($quality))
        {
            $this->quality = $quality;
        }
    }
    // Save image
    public function save_img($path)
    {
        // Resize
        if($this->resize)
        {
            $this->img_output = ImageCreateTrueColor($this->x_output, $this->y_output);
            ImageCopyResampled($this->img_output, $this->img_input, 0, 0, $this->x_src, $this->y_src, $this->x_output, $this->y_output, $this->x_input, $this->y_input);
        }
        // Save JPEG
        if($this->format == "JPG" OR $this->format == "JPEG")
        {
            if($this->resize) { imageJPEG($this->img_output, $path, $this->quality); }
            else { copy($this->img_src, $path); }
        }
        // Save PNG
        elseif($this->format == "PNG")
        {
            if($this->resize) { imagePNG($this->img_output, $path); }
            else { copy($this->img_src, $path); }
        }
        // Save GIF
        elseif($this->format == "GIF")
        {
            if($this->resize) { imageGIF($this->img_output, $path); }
            else { copy($this->img_src, $path); }
        }
    }

    // Get width
    public function get_width()
    {
        return $this->x_input;
    }
    // Get height
    public function get_height()
    {
        return $this->y_input;
    }
    // Clear image cache
    public function clear_cache()
    {
        @ImageDestroy($this->img_input);
        @ImageDestroy($this->img_output);
    }
}
class thumbnail extends imaging {
    private $image;
    private $width;
    private $height;

    function __construct($image,$width,$height) {
        parent::set_img($image);
        parent::set_quality(85);
        parent::set_size($width,$height);
        $this->thumbnail= pathinfo($image, PATHINFO_DIRNAME) . '/' .pathinfo($image, PATHINFO_FILENAME).'_tn.'.pathinfo($image, PATHINFO_EXTENSION);
        $this->filename= pathinfo($image, PATHINFO_FILENAME).'_tn.'.pathinfo($image, PATHINFO_EXTENSION);
        parent::save_img($this->thumbnail);
        parent::clear_cache();
    }
    function __toString() {
        return $this->filename;
    }
}

?>