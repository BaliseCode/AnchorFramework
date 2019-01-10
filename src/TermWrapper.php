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

            $this->taxonomy = AsyncTaxonomyWrapper($term->name); 

            /*
            $this->order = $term->menu_order;
            $this->menu_order = $term->menu_order;
            $this->post_type = $term->post_type;
            */
           
            $this->url = get_permalink($term);
            $this->content = apply_filters('the_content', $term->post_content);
            $this->excerpt = ($term->post_excerpt) ? $term->post_excerpt :  wp_trim_words($term->post_content,  apply_filters( 'excerpt_length', 55 ), ' ' . '[&hellip;]');
            $this->author_id = $term->author;
            $this->post_parent = $term->post_parent;
            if ($term->post_parent)  {
                $this->parent = new AsycPostWrapper($term->post_parent);
            }
            $this->meta = new PostMetaWrapper($term);
            $this->taxonomy = new PostTaxonomyWrapper($term);
            $this->thumbnail = new PostThumbnail($term); 
            $this->date = get_the_date();
            $this->permalink = get_permalink($term);
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
Array ( [0] => WP_Term Object ( [term_id] => 3 [name] => Qualipro [slug] => qualipro [term_group] => 0 [term_taxonomy_id] => 3 [taxonomy] => semences_categories [description] => [parent] => 0 [count] => 1 [filter] => raw ) )
*/