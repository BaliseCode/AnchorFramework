<?php

/*
* (c) Balise.ca
*
* This source file is subject to the MIT license that is bundled
* with this source code in the file LICENSE.
*/

function attachement($id, $size="large", $full=null) {
    $img = wp_get_attachment_image_src( $id, $size);
    if ($full) return $img;
    return $img[0];
}