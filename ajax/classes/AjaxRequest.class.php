<?php

/*
 * Обертка для работы с Ajax-запросами
*/

class AjaxRequest
{
    public $actions = array();

    public $data;
    public $user;
    public $code;
    public $message;
    public $status;

    public function __construct($request)
    {
        $this->request = $request;
        $this->action = $this->getRequestParam("act");

        if (!empty($this->actions[$this->action])) {
            $this->callback = $this->actions[$this->action];
            call_user_func(array($this, $this->callback));
        } else {
            header("HTTP/1.1 400 Bad Request");
            $this->setFieldError("main", "Некорректный запрос");
        }

        $this->response = $this->renderToString();
    }



    public function getRequestParam($name)
    {
        if (array_key_exists($name, $this->request)) {
            return trim($this->request[$name]);
        }
        return null;
    }


    public function setResponse($key, $value)
    {
        $this->data[$key] = $value;
    }

    public function setUserData($arr)
    {
        $this->user = $arr;
    }


    public function setFieldError($name, $message = "")
    {
        $this->status = "err";
        $this->code = $name;
        $this->message = $message;
    }


    public function renderToString()
    {
        $this->json = array(
            "id" => session_id(),
            "status" => $this->status,
            "code" => $this->code,
            "message" => $this->message,
            "data" => $this->data,
            "user" => $this->user,
        );
        return json_encode($this->json, ENT_NOQUOTES);
    }


    public function showResponse()
    {
        header("Content-Type: application/json; charset=UTF-8");
        header("access-control-allow-origin: *");
        echo $this->response;
    }
}
