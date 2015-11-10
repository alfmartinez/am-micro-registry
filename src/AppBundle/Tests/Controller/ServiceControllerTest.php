<?php

namespace AppBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ServiceControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/index');
    }

    public function testRegister()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/register');
    }

    public function testUnregister()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/unregister');
    }

    public function testRetrieve()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/retrieve');
    }

}
