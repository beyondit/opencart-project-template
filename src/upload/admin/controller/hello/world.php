<?php

class ControllerHelloWorld extends Controller {

    public function index() {
        $greeting = "Hello " . $this->user->getUserName();

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode([
            'greeting' => $greeting
        ]));
    }

}

