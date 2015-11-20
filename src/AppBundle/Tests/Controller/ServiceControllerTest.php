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
    public function putProviderCreatesServiceWithProviderIfServiceDoesNotExist() {
        $requestBody = json_encode(['host'=>'http://test.example.com']);
        $requestHeaders = ['CONTENT_TYPE' => 'application/json'];
        $this->client->request(
                'PUT', '/api/services/ServiceA/provider', [], [], $requestHeaders, $requestBody
        );
        
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $actual = $this->client->getResponse()->getContent();
        $this->assertServiceHasProvider('http://test.example.com','ServiceA');
        $this->assertJsonContent(['msg'=>'Registration OK'], $actual);   
    }
    
    /**
     * @test
     */
    public function putProviderAddsProviderIfServiceExists() {
        $this->createServices([
            ['name' => 'ServiceA', 'providers' => ['http://test.example.com']],
            ['name' => 'ServiceB']
        ]);
        $requestBody = json_encode(['host'=>'http://test2.example.com']);
        $requestHeaders = ['CONTENT_TYPE' => 'application/json'];
        $this->client->request(
                'PUT', '/api/services/ServiceA/provider', [], [], $requestHeaders, $requestBody
        );
        
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $actual = $this->client->getResponse()->getContent();
        $this->assertServiceHasProvider('http://test.example.com','ServiceA');
        $this->assertServiceHasProvider('http://test2.example.com','ServiceA');
        $this->assertJsonContent(['msg'=>'Registration OK'], $actual);
        
    }
    
    /**
     * @test
     */
    public function deleteUnregisterRemovesProvider() {
        $this->createServices([
            ['name' => 'ServiceA', 'providers' => ['http://test.example.com']],
            ['name' => 'ServiceB']
        ]);
        $requestBody = json_encode(['host'=>'http://test.example.com']);
        $requestHeaders = ['CONTENT_TYPE' => 'application/json'];
        $this->client->request(
                'DELETE', '/api/services/ServiceA/provider', [], [], $requestHeaders, $requestBody
        );
        
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $actual = $this->client->getResponse()->getContent();
        $service = $this->getService('ServiceA');
        $this->assertCount(0, $service->getProviders());
        $this->assertJsonContent(['msg'=>'Registration Removed'], $actual);
        
    }
    
    /**
     * @test
     */
    public function deleteUnregisterRemovesProviderIfMultipleProviders() {
        $this->createServices([
            ['name' => 'ServiceA', 'providers' => ['http://test.example.com','http://test2.example.com']],
            ['name' => 'ServiceB']
        ]);
        $requestBody = json_encode(['host'=>'http://test.example.com']);
        $requestHeaders = ['CONTENT_TYPE' => 'application/json'];
        $this->client->request(
                'DELETE', '/api/services/ServiceA/provider', [], [], $requestHeaders, $requestBody
        );
        
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $actual = $this->client->getResponse()->getContent();
        $service = $this->getService('ServiceA');
        $this->assertCount(1, $service->getProviders());
        $this->assertServiceHasNotProvider('http://test.example.com','ServiceA');
        $this->assertServiceHasProvider('http://test2.example.com','ServiceA');
        
        $this->assertJsonContent(['msg'=>'Registration Removed'], $actual);
        
    }
    
    /**
     * @test
     */
    public function deleteReturnsNotFoundIfServiceDoesNotExist() {
        $this->createServices([
            ['name' => 'ServiceA', 'providers' => ['http://test.example.com','http://test2.example.com']],
            ['name' => 'ServiceB']
        ]);
        $requestBody = json_encode(['host'=>'http://test.example.com']);
        $requestHeaders = ['CONTENT_TYPE' => 'application/json'];
        $this->client->request(
                'DELETE', '/api/services/ServiceC/provider', [], [], $requestHeaders, $requestBody
        );
        
        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @test
     */
    public function deleteServiceRemovesService() {
        $this->createServices([
            ['name' => 'ServiceA', 'providers' => ['http://test.example.com']]
        ]);
        $requestHeaders = ['CONTENT_TYPE' => 'application/json'];
        $this->client->request(
                'DELETE', '/api/services/ServiceA', [], [], $requestHeaders
        );
        
        $actual = $this->client->getResponse()->getContent();
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        
        $service = $this->getService('ServiceA');
        $this->assertNull($service);
        $this->assertJsonContent(['msg'=>'Service Removed'], $actual);
    }
    
    /**
     * @test
     */
    public function deleteServiceReturnsNotFoundIfNoService() {
        $this->createServices([
            ['name' => 'ServiceA', 'providers' => ['http://test.example.com']]
        ]);
        $requestHeaders = ['CONTENT_TYPE' => 'application/json'];
        $this->client->request(
                'DELETE', '/api/services/ServiceB', [], [], $requestHeaders
        );
        
        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
        
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

    private function createServices($services) {
        /* @var $odm DocumentManager */
        $odm = $this->client->getContainer()->get('doctrine_mongodb')->getManager();
        foreach ($services as $serviceData) {
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

    private function assertServiceHasProvider($providerUrl, $serviceName) {
        $service = $this->getService($serviceName);
        $this->assertInstanceOf('AppBundle\Document\Service', $service);
        $urls = array_map(function($provider){return $provider->getUrl();}, $service->getProviders()->toArray());
        $this->assertContains($providerUrl, $urls);
    }
    
    private function assertServiceHasNotProvider($providerUrl, $serviceName) {
        $service = $this->getService($serviceName);
        $this->assertInstanceOf('AppBundle\Document\Service', $service);
        $urls = array_map(function($provider){return $provider->getUrl();}, $service->getProviders()->toArray());
        $this->assertNotContains($providerUrl, $urls);
    }
    
    private function getService($serviceName) {
         /* @var $odm DocumentManager */
        $odm = $this->client->getContainer()->get('doctrine_mongodb')->getManager();
        $service = $odm->getRepository('AppBundle:Service')->findOneByName($serviceName);
        return $service;
    }

}
