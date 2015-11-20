<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Document\Service;
use AppBundle\Document\Provider;

/**
 * @RouteResource("Service")
 */
class ServiceRestController extends Controller {

    public function cgetAction() {
        $dm = $this->get('doctrine_mongodb')->getManager();

        $services = $dm->getRepository('AppBundle:Service')->findAll();

        return $services;
    }

    public function cdeleteAction($name) {
        $dm = $this->get('doctrine_mongodb')->getManager();
        $service = $this->findService($name);
        if (!$service) {
            throw $this->createNotFoundException("No known $name service");
        }
        $dm->remove($service);
        $dm->flush();
        return ['msg'=>'Service Removed'];
    }

    public function getAction($name) {
        $service = $this->findService($name);
        if (!$service) {
            throw $this->createNotFoundException("No known $name service");
        }
        return $service;
    }

    public function getProvidersAction($name) {
        $service = $this->findService($name);
        if (!$service) {
            throw $this->createNotFoundException("No known $name service");
        }
        return $service->getProviders();
    }

    public function getProviderAction($name) {
        $service = $this->findService($name);
        if (!$service) {
            throw $this->createNotFoundException("No known $name service");
        }
        $provider = $this->selectProvider($service);
        if (!$provider) {
            throw $this->createNotFoundException("No known provider for $name");
        }
        return $provider;
    }

    public function putProviderAction($name, Request $request) {
        $data = json_decode($request->getContent());
        $this->registerService($name, $data);
        $msg = 'Registration OK';
        return ['msg' => $msg];
    }

    public function deleteProviderAction($name, Request $request) {
        $data = json_decode($request->getContent());
        $this->unregisterService($name, $data);
        $msg = 'Registration Removed';
        return ['msg' => $msg];
    }

    private function findService($name) {
        $dm = $this->get('doctrine_mongodb')->getManager();

        $service = $dm->getRepository('AppBundle:Service')->findOneByName($name);

        return $service;
    }

    private function selectProvider($service) {
        $selector = $this->get("provider_selector");
        return $selector->select($service->getProviders());
    }

    private function registerService($name, $data) {
        $service = $this->findService($name);
        if (!$service) {
            $service = $this->createService($name);
        }
        if ($data->host) {
            $provider = new Provider($data->host);
            $service->addProvider($provider);
        }
    }

    private function unregisterService($name, $data) {
        /* @var $service Service */
        $service = $this->findService($name);
        if (!$service) {
            throw $this->createNotFoundException("No known $name service");
        }
        if ($data->host) {
            $service->removeProvider($data->host);
        }
    }

    private function createService($name) {
        $dm = $this->get('doctrine_mongodb')->getManager();
        $service = new Service($name);
        $dm->persist($service);
        $dm->flush();
        return $service;
    }

}
