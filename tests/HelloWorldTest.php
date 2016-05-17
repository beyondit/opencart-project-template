<?php

class HelloWorldTest extends OpenCartTest
{
    public function testHelloWorldGreeting()
    {
        $response = $this->dispatchAction('hello/world');
        $output = json_decode($response->getOutput(), true);

        $this->assertEquals('Hello World', $output['greeting']);
    }

    public function testHelloOpenCartGreeting()
    {
        $response = $this->dispatchAction('hello/world', 'GET', ['name' => 'OpenCart']);
        $output = json_decode($response->getOutput(), true);

        $this->assertEquals('Hello OpenCart', $output['greeting']);
    }

    public function testHelloCustomerGreeting()
    {
        // add examplary customer
        $this->db->query("INSERT INTO " . DB_PREFIX . "customer SET customer_group_id = '1', store_id = '" . (int)$this->config->get('config_store_id') . "', firstname = 'Test', lastname = 'Customer', email = 'somebody@test.com', telephone = '123456789', fax = '123456789', custom_field = '', salt = '" . $this->db->escape($salt = token(9)) . "', password = '" . $this->db->escape(sha1($salt . sha1($salt . sha1('password')))) . "', newsletter = '0', ip = '127.0.0.1', status = '1', approved = '1', date_added = NOW()");

        $this->login('somebody@test.com','password');

        $response = $this->dispatchAction('hello/world');
        $output = json_decode($response->getOutput(), true);

        $this->assertEquals('Hello Test Customer', $output['greeting']);

        $this->logout();

        // delete customers
        $this->db->query("DELETE FROM " . DB_PREFIX . "customer");
    }

}

