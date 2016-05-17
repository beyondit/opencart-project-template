<?php

class HelloWorldAdminTest extends OpenCartTest
{
    public function addPermission()
    {
        $query = $this->db->query("SELECT permission from ".DB_PREFIX."user_group WHERE name = 'Administrator'");
        $permissions = json_decode($query->row['permission'],true);

        if (!in_array("hello/world",$permissions['access'])) {
            $permissions['access'][] = "hello/world";
            $this->db->query("UPDATE ".DB_PREFIX."user_group SET permission='".$this->db->escape(json_encode($permissions))."' WHERE name = 'Administrator'");
        }
    }

    public function testHelloUserGreeting()
    {
        $this->addPermission();
        $this->login('admin','admin');

        $response = $this->dispatchAction('hello/world');
        $output = json_decode($response->getOutput(), true);

        $this->assertEquals('Hello admin', $output['greeting']);

        $this->logout();
    }

}

