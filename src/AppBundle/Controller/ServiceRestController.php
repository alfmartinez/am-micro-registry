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
        return new Service($name);
    }
}
