<?php

/*
* (c) Balise.ca
*
* This source file is subject to the MIT license that is bundled
* with this source code in the file LICENSE.
*/

namespace Balise\AnchorFramework;

class PostWrapper {
    function __construct($post) {
        $this->title = $post->post_title;
        $this->content = preg_replace('/<!--(.|\s)*?-->/', '', $post->post_content);
    }
}
