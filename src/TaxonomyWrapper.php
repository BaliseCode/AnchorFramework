<?php
/*
* (c) Balise.ca
*
* This source file is subject to the MIT license that is bundled
* with this source code in the file LICENSE.
*/
namespace Balise\AnchorFramework;
class TaxonomyWrapper {
    private $isSync;
    function __construct($taxonomy, $post=null,$isSync=false) {
        $this->isSync = $isSync;
        if (gettype($taxonomy)==="string") {
            $taxonomy = get_taxonomy($taxonomy);
        }
        if ($taxonomy && gettype($taxonomy)==="object") {
            $this->slug = $taxonomy->name;
            $this->title = $taxonomy->labels->name; 
            $this->content = apply_filters('the_content', $taxonomy->description);
            $this->excerpt = wp_trim_words($taxonomy->description,  apply_filters( 'excerpt_length', 55 ), ' ' . '[&hellip;]');

            if ($post) {
                $this->terms = get_the_terms($post, $taxonomy->name);
            } else {
                $this->terms = get_terms( array(
                    'taxonomy' => $taxonomy->name,
                    'hide_empty' => false,
                ) );
            }
            $this->terms = array_map(function($item) {
                return new TermWrapper($item, true);
            }, $this->terms);
        }
        $this->isSync = false;
    }
}


/*
* LOAD THE POST ON DEMAND
*/
class AsyncTaxonomyWrapper {
    private $virtual;
    function __construct($id) {
        $this->id = $id;
        $this->virtual = null;
    }
    public function __tostring() {
        return $this->id;
    }
    public function __get($name) {
        if (!$this->virtual) {
             $this->virtual = new TaxonomyWrapper($this->id);
        }
        if ($this->virtual) {
            return $this->virtual->$name;
        }
        return '';
    }
}
