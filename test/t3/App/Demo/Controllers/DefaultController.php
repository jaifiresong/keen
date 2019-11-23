<?php

namespace App\Demo\Controllers;

use App\Demo\Common\DemoController;


class DefaultController extends DemoController {
    public function index () {
        $this->render($this->action);
    }
}