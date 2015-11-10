<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use AppBundle\Document\Service;
use FOS\RestBundle\Controller\Annotations\RouteResource;

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
    
    public function getProviderUrlAction($name) {
       $service = $this->findService($name);
       $provider = array_rand($service->getProviders());
       return $provider;
       if (!$provider) {
            throw $this->createNotFoundException("No known provider for $name");
        }
       return $provider->getUrl();
    }
    
    private function findService($name) {
        $dm = $this->get('doctrine_mongodb')->getManager();

        $service = $dm->getRepository('AppBundle:Service')->findOneByName($name);
        
        if (!$service) {
            throw $this->createNotFoundException("No known $name service");
        }
        return $service;
    }
}
