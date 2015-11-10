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

    /**
     * Get id
     *
     * @return id $id
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return self
     */
    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    /**
     * Get name
     *
     * @return string $name
     */
    public function getName() {
        return $this->name;
    }
    
    public function getProviders() {
        return $this->providers;
    }

    public function setProviders($providers) {
        $this->providers = $providers;
    }

    public function addProvider($provider) {
        $this->providers[]=$provider;
    }

}
