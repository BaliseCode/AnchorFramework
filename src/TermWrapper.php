<?php
/*
* (c) Balise.ca
*
* This source file is subject to the MIT license that is bundled
* with this source code in the file LICENSE.
*/
namespace Balise\AnchorFramework;
class TermWrapper {
    private $isSync;
    /*
    * CONSTRUCTOR
    *
    */
    function __construct($term=null,$isSync=false) {
        $this->isSync = $isSync;
        if (gettype($term)==="integer") {
            $term = get_term($term);
        }
        if ($term && gettype($term)==="object" && $isSync) {
            $this->id = $term->term_id;
            $this->slug = $term->slug;
            $this->title = $term->name;  

            $this->taxonomy = new AsyncTaxonomyWrapper($term->taxonomy); 

            /*
            $this->order = $term->menu_order;
            $this->menu_order = $term->menu_order;
            $this->post_type = $term->post_type;
            */
           

            $this->content = apply_filters('the_content', $term->description);
            $this->excerpt = wp_trim_words($term->description,  apply_filters( 'excerpt_length', 55 ), ' ' . '[&hellip;]');
            $this->url = get_term_link($term);
            $this->permalink = get_term_link($term);

            $this->post_parent = $term->post_parent;

            $this->meta = new TermMetaWrapper($term);
            
            $this->isSync = false;
        }
    }
    public function __set($name, $value) {
        if ($this->isSync) {
            $this->$name = $value;
        }
    }
}

/*
Array ( [description] => [parent] => 0 [count] => 1 [filter] => raw ) )
*/