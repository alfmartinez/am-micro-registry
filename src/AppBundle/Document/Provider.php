<?php

namespace AppBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;

/**
 * @MongoDB\EmbeddedDocument
 * @ExclusionPolicy("all")
 */
class Provider {
    /**
     * @MongoDB\String
     * @Expose
     */
    protected $url;
    function __construct($url) {
        $this->url = $url;
    }
    
    public function getUrl() {
        return $this->url;
    }

    public function setUrl($url) {
        $this->url = $url;
    }

}
