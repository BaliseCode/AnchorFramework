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
    function __construct($user=null) {

        $this->title = ""; 
        $this->content = "";
        if (gettype($user)!=="object") {
            $user = get_user_by('id',$user);
            
        }
        if ($user && gettype($user)==="object" ) {
            $this->title = ($user->display_name) ? $user->display_name : $user->user_nicename;
            $this->content = $user->description;
        }
    }

}
