<?php

/*
* (c) Balise.ca
*
* This source file is subject to the MIT license that is bundled
* with this source code in the file LICENSE.
*/

namespace Balise\AnchorFramework;


/*
* LOAD THE META ON DEMAND
*/
class TermMetaWrapper {
    function __construct($term=null) {
        $this->post = $term;
        if (gettype($term)==="object") {
            $this->post = $term->term_ID;
        }
    }
    public function __call($name, $arguments) {
        return get_term_meta($this->post,$name,false);
    }
    public function __get($name) { 
        return get_term_meta($this->post,$name,true);
    }
}