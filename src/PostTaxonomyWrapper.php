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
class PostTaxonomyWrapper {
    private $virtual = null;
    private $post;
    function __construct($post=null)  {
        $this->post = $post;
    }

    public function __get($name) { 
        if (!$this->virtual) {
            $this->virtual = (object) array();
            $posttype = $this->post->post_type;
            $tax = array_filter(get_taxonomies(null,'object'),function ($item) use ($posttype) {
            if (in_array ($posttype, $item->object_type)) return true;
                return false;
            });
            foreach($tax as $key => $val) {
                $this->virtual->{$key} = $val;
            }
        }
        return $this->virtual->{$name};
    }
   
    
}