<?php

require_once __DIR__.'/../models/UserModel.php';

class UserController{

    public function __construct()
    {
        
    }

    public function getUser(){
        $userModel = new User();
        return $userModel->getUser();
    }

    public function getUsername(){
        $userModel = new User();
        return $userModel->getUsername();


    }


}