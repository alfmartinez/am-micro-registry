<?php

namespace AppBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;

/**
 * @MongoDB\Document(collection="services")
 * @ExclusionPolicy("all") 
 */
class Service {

    /**
     * @MongoDB\Id
     */
    protected $id;

    /**
     * @MongoDB\String
     * @Expose
     */
    protected $name;

    /**
     * @MongoDB\EmbedMany(targetDocument="Provider")
     */
    protected $providers = array();

    function __construct($name = '') {
        $this->name = $name;
    }

    public function getProviders() {
        return $this->providers;
    }

    public function addProvider($provider) {
        $this->providers[] = $provider;
    }

}
