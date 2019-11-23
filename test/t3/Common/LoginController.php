<?php

namespace Common;

use CController;
use Models\User;

class LoginController extends CController {
    public function login($request) {
        $this->renderWidget('login');
    }
}