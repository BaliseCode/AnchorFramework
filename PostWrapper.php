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
            $this->excerpt = $post->post_excerpt;
            $this->author_id = $post->author;
            $this->post_parent = $post->post_parent;
            if ($post->post_parent)  {
                $this->parent = new AsycPostWrapper($post->post_parent);
            }
            $this->meta = new PostMetaWrapper($post);
            /*
            // TODO: ADD THIS LIST
            [post_date] =>
            [post_date_gmt] =>
            [post_modified] =>
            [post_modified_gmt] =>
            [post_status] =>
            [comment_status] =>
            [ping_status] =>
            [post_password] =>
            [to_ping] =>
            [pinged] =>
            [post_mime_type] =>
            [comment_count] =>
            */
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
