<?php

namespace AppBundle\Services\Provider\Selector;

use AppBundle\Services\Provider\ProviderSelectorInterface;

class FirstSelector implements ProviderSelectorInterface {

    public function select($providers) {
        return $providers[0];
    }

}
