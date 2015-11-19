<?php

namespace AppBundle\Tests\Controller;

use AppBundle\Document\Provider;
use AppBundle\Document\Service;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ServiceControllerTest extends WebTestCase {

    public function setup() {
        $this->client = static::createClient();
        $this->removeServices();
    }

    /**
     * @test
     */
    public function getServicesReturnsEmptyListOfServiceNamesIfNoServices() {
        $this->client->request('GET', '/api/services');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $actual = $this->client->getResponse()->getContent();
        $this->assertJsonContent([], $actual);
    }

    /**
     * @test
     */
    public function getServicesReturnsListOfServicesIfServices() {
        $this->createServices([
            ['name' => 'ServiceA'],
            ['name' => 'ServiceB']
        ]);
        $this->client->request('GET', '/api/services');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $actual = $this->client->getResponse()->getContent();
        $this->assertJsonContent([['name' => 'ServiceA'], ['name' => 'ServiceB']], $actual);
    }

    /**
     * @test
     */
    public function getServicesReturnsListOfServiceNamesIfServicesWithProviders() {
        $this->createServices([
            ['name' => 'ServiceA', 'providers' => ['http://test.example.com']],
            ['name' => 'ServiceB']
        ]);
        $this->client->request('GET', '/api/services');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $actual = $this->client->getResponse()->getContent();
        $this->assertJsonContent([['name' => 'ServiceA'], ['name' => 'ServiceB']], $actual);
    }

    private function assertJsonContent($expected, $actual) {
        $this->assertJson($actual);
        $actualJson = json_decode($actual, true);
        $this->assertEquals($expected, $actualJson);
    }

    private function removeServices() {
        /* @var $odm DocumentManager */
        $odm = $this->client->getContainer()->get('doctrine_mongodb')->getManager();
        $odm->getDocumentCollection('AppBundle\Document\Service')->drop();
    }

    private function createServices($param0) {
        /* @var $odm DocumentManager */
        $odm = $this->client->getContainer()->get('doctrine_mongodb')->getManager();
        foreach ($param0 as $serviceData) {
            $service = $this->createService($serviceData);
            $odm->persist($service);
        }
        $odm->flush();
    }

    private function createService($serviceData) {
        $service = new Service($serviceData['name']);
        if (isset($serviceData['providers'])) {
            foreach ($serviceData['providers'] as $url) {
                $provider = new Provider($url);
                $service->addProvider($provider);
            }
        }
        return $service;
    }

}
