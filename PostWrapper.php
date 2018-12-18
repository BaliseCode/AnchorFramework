<?php
/*
* (c) Balise.ca
*
* This source file is subject to the MIT license that is bundled
* with this source code in the file LICENSE.
*/
namespace Balise\AnchorFramework;
class PostWrapper {
    private $isSync;
    /*
    * CONSTRUCTOR
    *
    */
    function __construct($post=null,$isSync=false) {
        $this->isSync = $isSync;
        if (gettype($post)==="integer") {
            $post = get_post($post);
        }
        if ($post && gettype($post)==="object" && $isSync) {
            $this->id = $post->ID;
            $this->order = $post->menu_order;
            $this->menu_order = $post->menu_order;
            $this->post_type = $post->post_type;
            $this->guid = $post->guid;
            $this->url = get_permalink($post);
            $this->slug = $post->post_name;
            $this->title = $post->post_title;
            $this->content = apply_filters('the_content', $post->post_content);
            $this->excerpt = apply_filters( 'the_excerpt', get_the_excerpt($post) );
            $this->author_id = $post->author;
            $this->post_parent = $post->post_parent;
            if ($post->post_parent)  {
                $this->parent = new AsycPostWrapper($post->post_parent);
            }
            $this->meta = new PostMetaWrapper($post);
            $this->thumbnail = new PostThumbnail($post); 
            $this->date = get_the_date();
            $this->permalink = get_permalink($post);
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
* LOAD THE META ON DEMAND
*/
class PostMetaWrapper {
    function __construct($post=null) {
        $this->post = $post;
    }
    public function __call($name, $arguments) {
        return get_post_meta($this->post,$name,false);
    }
    public function __get($name) { 
        return get_post_meta($this->post,$name,true);
    }
}

/*
* LOAD THE THUMBNAIL  ON DEMAND  
*/
class PostThumbnail {
    function __construct($post=null) {
        $this->post = $post;
    }
    public function __invoke($arguments) {
        return get_the_post_thumbnail_url($this->post);
    }
    public function __get($name) {
       return get_the_post_thumbnail_url($this->post);
    }
}
/*
* LOAD THE POST ON DEMAND
*/
class AsycPostWrapper {
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
            $this->virtual = new PostWrapper($this->id);
        }
        if ($this->virtual) {
            return $this->virtual->$name;
        }
        return '';
    }
}
