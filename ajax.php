<?php

include './classes/Auth.class.php';
include './classes/AjaxRequest.class.php';
include './classes/AjaxFileSave.class.php';

if (!empty($_COOKIE['sid'])) {
    // check session id in cookies
    session_id($_COOKIE['sid']);
}
session_start();

class AuthorizationAjaxRequest extends AjaxRequest
{
    public $actions = array(
        "login" => "login",
        "logout" => "logout",
        "register" => "register",
        "profile" => "profile",
        "artist" => "artist",
        "profileSave" => "profileSave",
        "avatarSave" => "avatarSave",
        "changePassword" => "changePassword",
    );

    public function login()
    {
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            // Method Not Allowed
            http_response_code(405);
            header("Allow: POST");
            $this->setFieldError("main", "Method Not Allowed");
            return;
        }
        setcookie("sid", "");

        $email = $this->getRequestParam("email");
        $password = $this->getRequestParam("password");
        $remember = !!$this->getRequestParam("remember-me");

        if (empty($email)) {
            $this->setFieldError("email", "Введите Email");
            return;
        }

        if (empty($password)) {
            $this->setFieldError("password", "Введите пароль");
            return;
        }

        $user = new User();
        $auth_result = $user->authorize($email, $password, $remember);

        if (!$auth_result) {
            $this->setFieldError("password", "Неправильное имя пользователя или пароль");
            return;
        }

        $this->status = "ok";
        $this->setUserData($user->getAuthorizedUserInfo());
        $this->setResponse('redirect', '.');
        $this->message = sprintf("Hello, %s! Access granted.", $email);
    }
    public function logout()
    {
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            // Method Not Allowed
            http_response_code(405);
            header("Allow: POST");
            $this->setFieldError("main", "Method Not Allowed");
            return;
        }

        setcookie("sid", "");

        $user = new User();
        $user->logout();

        $this->setResponse("redirect", ".");
        $this->status = "ok";
    }
    public function register()
    {
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            // Method Not Allowed
            http_response_code(405);
            header("Allow: POST");
            $this->setFieldError("main", "Method Not Allowed");
            return;
        }

        setcookie("sid", "");

        $username = $this->getRequestParam("username");
        $URLName = $this->getRequestParam("urlname");
        $email = $this->getRequestParam("email");
        $password1 = $this->getRequestParam("password");
        $password2 = $password1; //$this->getRequestParam("password2");

        if (empty($URLName)) {
            $this->setFieldError("urlname", "Введите ссылку на профиль");
            return;
        }

        if (empty($username)) {
            $this->setFieldError("username", "Введите имя");
            return;
        }

        if (empty($email)) {
            $this->setFieldError("email", "Введите Email");
            return;
        }

        if (empty($password1)) {
            $this->setFieldError("password", "Введите пароль");
            return;
        }

        if (empty($password2)) {
            $this->setFieldError("password2", "Подтвердите пароль");
            return;
        }

        if ($password1 !== $password2) {
            $this->setFieldError("password2", "Пароли не совпадают");
            return;
        }

        $user = new User();

        try {
            $new_user_id = $user->create($username, $URLName, $email, $password1);
        } catch (\Exception $e) {
            $this->setFieldError("username", $e->getMessage());
            return;
        }
        $user->authorize($email, $password1);

        $this->message = sprintf("Hello, %s! Thank you for registration.", $username);
        $this->setResponse("redirect", $URLName);
        $this->status = "ok";
    }

    public function changePassword (){
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            // Method Not Allowed
            http_response_code(405);
            header("Allow: POST");
            $this->setFieldError("main", "Method Not Allowed");
            return;
        }
        if(empty($_SESSION["user_id"])) {
            $this->setFieldError("session", "No session on the server");
            return;
        }

        $password1 = $this->getRequestParam("password");
        $password2 = $this->getRequestParam("password2");

        if (empty($password1)) {
            $this->setFieldError("password", "Введите пароль");
            return;
        }

        if (empty($password2)) {
            $this->setFieldError("password2", "Подтвердите пароль");
            return;
        }

        if ($password1 !== $password2) {
            $this->setFieldError("password2", "Пароли не совпадают");
            return;
        }
        $user = new User();
        $role = 'user';
        if($_SESSION['is_admin']) $role = 'admin';

        try {
            $response = $user->changePassword($password1);
        } catch (\Exception $e) {
            $this->setFieldError("username", $e->getMessage());
            return;
        }
        $this->message = sprintf("Пароль был успешно изменён.");
        $this->status = "ok";


    }

    public function profile() {
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            // Method Not Allowed
            http_response_code(405);
            header("Allow: POST");
            $this->setFieldError("main", "Method Not Allowed");
            return;
        }
        if(empty($_SESSION["user_id"])) {
            $this->setFieldError("session", "No session on the server");
            return;
        }
        $user = new User();

        $this->status = "ok";
        $role = 'user';
        if($_SESSION['is_admin']) $role = 'admin';
        $this->setUserData(array(
            'id' => $_SESSION['user_id'],
            'name' => $_SESSION['user_name'],
            'url' => $_SESSION['url_name'],
            'role' => $role
        ));
        //$this->setResponse('redirect', '.');
        //$this->message = sprintf("Hello, %s! Access granted.");
    }
    public function artist(){
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            // Method Not Allowed
            http_response_code(405);
            header("Allow: POST");
            $this->setFieldError("main", "Method Not Allowed");
            return;
        }
        if(empty($_SESSION["user_id"])) {
            $this->setFieldError("session", "No session on the server");
            return;
        }
        $url = $this->getRequestParam("userurl");
        $user = new User();
        $userInfo = $user->getUserInfo($url);

        /*if (!$userInfo) {
            $this->setFieldError("userinfo", "not authorized");
            return;
        }*/
        $this->status = "ok";
        $this->setUserData($userInfo);
    }
    public function profileSave(){
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            // Method Not Allowed
            http_response_code(405);
            header("Allow: POST");
            $this->setFieldError("main", "Method Not Allowed");
            return;
        }
        if(empty($_SESSION["user_id"])) {
            $this->setFieldError("session", "No session on the server");
            return;
        }
        $id = $this->getRequestParam("userid");
        $urlname = $this->getRequestParam("urlname");
        $username = $this->getRequestParam("username");
        $email = $this->getRequestParam("email");
        $phone = $this->getRequestParam("phone");
        $website = $this->getRequestParam("website");
        $about = $this->getRequestParam("about");

        if (empty($username)) {
            $this->setFieldError("username", "Введите имя");
            return;
        }

        if (empty($email)) {
            $this->setFieldError("email", "Введите Email");
            return;
        }
        if($urlname !== $_SESSION['url_name'] && !$_SESSION['is_admin']) {
            $this->message = sprintf("Sorry, %s! You have no access.");
            return;
        }
        $arr = array(
            'id'=> $id,
            'username'=> $username,
            'email'=> $email,
            'phone'=> $phone,
            'website'=> $website,
            'about'=> $about,
        );
        $user = new User();
        try {
            $response = $user->setUserInfo($arr);
        } catch (\Exception $e) {
            $this->setFieldError("username", $e->getMessage());
            return;
        }
        $this->message = sprintf("Данные были успешно обновлены.");
        $this->status = "ok";
    }
    public function avatarSave(){
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            // Method Not Allowed
            http_response_code(405);
            header("Allow: POST");
            $this->setFieldError("main", "Method Not Allowed");
            return;
        }
        if(empty($_SESSION["user_id"])) {
            $this->setFieldError("session", "No session on the server");
            return;
        }
        $urlname = $this->getRequestParam("urlname");

        if($urlname !== $_SESSION['url_name'] && !$_SESSION['is_admin']) {
            $this->message = sprintf("Sorry, %s! You have no access.");
            return;
        }
        $target_dir = "../data/avatars/";
        $upload = new FileSave();
        $response = $upload->uploadAvatar($target_dir, 'file', $urlname);
        $this->user = $response;
        $this->message = sprintf("Данные были успешно обновлены.");
        $this->status = "ok";
    }
}

$ajaxRequest = new AuthorizationAjaxRequest($_REQUEST);
$ajaxRequest->showResponse();
