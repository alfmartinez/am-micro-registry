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

    /**
     * @test
     */
    public function getServiceReturnsServiceNameAndOtherData() {
        $this->createServices([
            ['name' => 'ServiceA', 'providers' => ['http://test.example.com']],
            ['name' => 'ServiceB']
        ]);
        $this->client->request('GET', '/api/services/ServiceA');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $actual = $this->client->getResponse()->getContent();
        $this->assertJsonContent(['name' => 'ServiceA'], $actual);
    }

    /**
     * @test
     */
    public function getProviderReturnsProviderForServiceIfServiceHasProvider() {
        $this->createServices([
            ['name' => 'ServiceA', 'providers' => ['http://test.example.com']],
            ['name' => 'ServiceB']
        ]);
        $this->client->request('GET', '/api/services/ServiceA/provider');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $actual = $this->client->getResponse()->getContent();
        $this->assertJsonContent(['url' => 'http://test.example.com'], $actual);
    }

    /**
     * @test
     */
    public function getProviderReturnsFirstProviderForServiceIfServicesHasProvider() {
        $this->createServices([
            ['name' => 'ServiceA', 'providers' => ['http://test.example.com', 'http://test2.example.com']],
            ['name' => 'ServiceB']
        ]);
        $this->client->request('GET', '/api/services/ServiceA/provider');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $actual = $this->client->getResponse()->getContent();
        $this->assertJsonContent(['url' => 'http://test.example.com'], $actual);
    }

    /**
     * @test
     */
    public function getProvidersReturnsAllProviderForServiceIfServicesHasProvider() {
        $this->createServices([
            ['name' => 'ServiceA', 'providers' => ['http://test.example.com', 'http://test2.example.com']],
            ['name' => 'ServiceB']
        ]);
        $this->client->request('GET', '/api/services/ServiceA/providers');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $actual = $this->client->getResponse()->getContent();
        $this->assertJsonContent([['url' => 'http://test.example.com'], ['url' => 'http://test2.example.com']], $actual);
    }

    /**
     * @test
     */
    public function getProviderReturnsNotFoundIfServiceHasNoProvider() {
        $this->createServices([
            ['name' => 'ServiceA', 'providers' => ['http://test.example.com']],
            ['name' => 'ServiceB']
        ]);
        $this->client->request('GET', '/api/services/ServiceB/provider');
        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @test
     */
    public function getUnknownServiceReturnsNotFound() {
        $this->createServices([
            ['name' => 'ServiceA', 'providers' => ['http://test.example.com']],
            ['name' => 'ServiceB']
        ]);
        $this->client->request('GET', '/api/services/ServiceC');
        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
    }
    
    /**
     * @test
     */
    public function getUnknownServiceProvidersReturnsNotFound() {
        $this->createServices([
            ['name' => 'ServiceA', 'providers' => ['http://test.example.com']],
            ['name' => 'ServiceB']
        ]);
        $this->client->request('GET', '/api/services/ServiceC/providers');
        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @test
     */
    public function getUnknownServiceProviderReturnsNotFound() {
        $this->createServices([
            ['name' => 'ServiceA', 'providers' => ['http://test.example.com']],
            ['name' => 'ServiceB']
        ]);
        $this->client->request('GET', '/api/services/ServiceC/provider');
        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @test
     */
    public function postRegisterCreatesServiceWithProviderIfServiceDoesNotExist() {
        $requestBody = json_encode(['name'=>'ServiceA','host'=>'http://test.example.com']);
        $requestHeaders = ['CONTENT_TYPE' => 'application/json'];
        $this->client->request(
                'POST', '/api/services/registers', [], [], $requestHeaders, $requestBody
        );
        
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $actual = $this->client->getResponse()->getContent();
        $this->assertServiceHasProvider('http://test.example.com','ServiceA');
        $this->assertJsonContent(['msg'=>'Registration OK'], $actual);   
    }
    
    /**
     * @test
     */
    public function postRegisterAddsProviderIfServiceExists() {
        $this->createServices([
            ['name' => 'ServiceA', 'providers' => ['http://test.example.com']],
            ['name' => 'ServiceB']
        ]);
        $requestBody = json_encode(['name'=>'ServiceA','host'=>'http://test2.example.com']);
        $requestHeaders = ['CONTENT_TYPE' => 'application/json'];
        $this->client->request(
                'POST', '/api/services/registers', [], [], $requestHeaders, $requestBody
        );
        
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $actual = $this->client->getResponse()->getContent();
        $this->assertServiceHasProvider('http://test.example.com','ServiceA');
        $this->assertServiceHasProvider('http://test2.example.com','ServiceA');
        $this->assertJsonContent(['msg'=>'Registration OK'], $actual);
        
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

    public function assertServiceHasProvider($providerUrl, $service) {
         /* @var $odm DocumentManager */
        $odm = $this->client->getContainer()->get('doctrine_mongodb')->getManager();
        $service = $odm->getRepository('AppBundle:Service')->findOneByName($service);
        $this->assertInstanceOf('AppBundle\Document\Service', $service);
        $urls = array_map(function($provider){return $provider->getUrl();}, $service->getProviders()->toArray());
        $this->assertContains($providerUrl, $urls);
    }

}
