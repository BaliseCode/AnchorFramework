<?php
/*
* (c) Balise.ca
*
* This source file is subject to the MIT license that is bundled
* with this source code in the file LICENSE.
*/
function theme_asset($url) {
   return get_stylesheet_directory_uri()."/public/images".$url;
}
