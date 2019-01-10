<?php

/*
* (c) Balise.ca
*
* This source file is subject to the MIT license that is bundled
* with this source code in the file LICENSE.
*/

namespace Balise\AnchorFramework;

class AuthorWrapper {
    /*
    * CONSTRUCTOR
    *
    */
    function __construct($post=null) {
        if (gettype ($post)==="integer") {
            $post = get_post($post);
        }
        if ($post && gettype($post)==="object" ) {
        }
    }

}
/*
* LOAD THE POST ON DEMAND
*/
class AsyncAuthorWrapper {
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
            $this->virtual = new AuthorWrapper($this->id);
        }
        if ($this->virtual) {
            return $this->virtual->$name;
        }
        return '';
    }
}