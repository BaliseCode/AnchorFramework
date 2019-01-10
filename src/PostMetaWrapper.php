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
class PostMetaWrapper {
    function __construct($post=null) {
        $this->post = $post;
        if (gettype($post)==="object") {
            $this->post = $post->ID;
        }
    }
    public function __call($name, $arguments) {
        return get_post_meta($this->post,$name,false);
    }
    public function __get($name) { 
        return get_post_meta($this->post,$name,true);
    }
}