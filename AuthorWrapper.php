<?php

/*
* (c) Balise.ca
*
* This source file is subject to the MIT license that is bundled
* with this source code in the file LICENSE.
*/

namespace Balise\AnchorFramework;

class PostWrapper {
    /*
    * CONSTRUCTOR
    *
    */
    function __construct($post=null) {
        if (gettype ($post)==="integer") {
            $post = get_post($post);
        }
        if ($post && gettype($post)==="object" ) {

            $this->id = $post->ID;
            $this->slug = $post->post_name;
            $this->title = $post->post_title;
            $this->content = apply_filters('the_content', $post->post_content);
            $this->excerpt = $post->post_excerpt;
            $this->author_id = $post->author;
            $this->post_parent = $post->post_parent;

            if ($post->post_parent)  {
                $this->parent = new AsycPostWrapper($post->post_parent);
            }
        }

        /*

        // TODO: ADD THIS LIST
        [post_date] =>
        [post_date_gmt] =>
        [post_status] =>
        [comment_status] =>
        [ping_status] =>
        [post_password] =>
        [to_ping] =>
        [pinged] =>
        [post_modified] =>
        [post_modified_gmt] =>
        [post_content_filtered] =>
        [guid] =>
        [menu_order] =>
        [post_type] =>
        [post_mime_type] =>
        [comment_count] =>
        [filter] =>
        */
    }
    public function __get($name) {
        return $name;
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
