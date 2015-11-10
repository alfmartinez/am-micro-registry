<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use AppBundle\Document\Service;

class ServiceRestController extends Controller {

    public function getServicesAction() {
        $service1 = new Service('Andy');
        $service2 = new Service('Selma');
        return array($service1, $service2);
    }

    public function getServiceAction($name) {
        return new Service($name);
    }
}
