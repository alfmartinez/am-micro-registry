<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use Symfony\Component\HttpFoundation\Request;

/**
 * @RouteResource("Service")
 */
class ServiceRestController extends Controller {

    public function cgetAction() {
        $dm = $this->get('doctrine_mongodb')->getManager();

        $services = $dm->getRepository('AppBundle:Service')->findAll();
        
        return $services;
    }

    public function getAction($name) {
        return $this->findService($name);
    }
    
    public function getProvidersAction($name) {
       $service = $this->findService($name);
       return $service->getProviders();
    }
    
    public function getProviderAction($name) {
       $service = $this->findService($name);
       $provider = $this->selectProvider($service);
       if (!$provider) {
            throw $this->createNotFoundException("No known provider for $name");
        }
       return $provider;
    }
    
    public function postRegisterAction(Request $request) {
        return $request->getContent();
    }
    
    private function findService($name) {
        $dm = $this->get('doctrine_mongodb')->getManager();

        $service = $dm->getRepository('AppBundle:Service')->findOneByName($name);
        
        if (!$service) {
            throw $this->createNotFoundException("No known $name service");
        }
        return $service;
    }
    
    private function selectProvider($service){
        $selector = $this->get("provider_selector");
        return $selector->select($service->getProviders());
    }
}
