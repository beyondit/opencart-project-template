<?php

class ControllerHelloWorld extends Controller {

    public function index() {
        $greeting = "Hello ";

        if ($this->customer->isLogged()) {
            $greeting .= $this->customer->getFirstName() . " " . $this->customer->getLastName();
        } elseif(isset($this->request->get['name'])) {
            $greeting .= $this->request->get['name'];
        } else {
            $greeting .= "World";
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode([
            'greeting' => $greeting
        ]));
    }

}

