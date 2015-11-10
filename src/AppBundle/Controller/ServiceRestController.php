<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use AppBundle\Document\Service;

class ServiceRestController extends Controller {

    public function getServicesAction() {
        $dm = $this->get('doctrine_mongodb')->getManager();

        $services = $dm->getRepository('AppBundle:Service')->findAll();
        
        return $services;
    }

    public function getServiceAction($name) {
        return $this->findService($name);
    }
    
    public function getProvidersAction($name) {
       $service = $this->findService($name);
       return $service->getProviders();
    }
    
    private function findService($name) {
        $dm = $this->get('doctrine_mongodb')->getManager();

        $service = $dm->getRepository('AppBundle:Service')->findOneByName($name);
        
        if (!$service) {
            throw $this->createNotFoundException();
        }
        return $service;
    }
}
