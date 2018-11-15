<?php

/*
* (c) Balise.ca
*
* This source file is subject to the MIT license that is bundled
* with this source code in the file LICENSE.
*/

namespace Balise\AnchorFramework;

use Windwalker\Renderer\BladeRenderer;

class Anchor
{
    public static $renderer;
    public static $data;

    private static function getData() {
        global $post;
        $return = new PostWrapper($post);
        if (!is_singular()) {

        } else {

            $return->posts = array();
            while (have_posts()) {
                the_post();
                $subreturn = new PostWrapper($post);
                $return->posts[] = $subreturn;
            }
        }
        return $return;
    }
    private static function getTemplate() {
        if (is_404()) { return array('404','index'); }
        if (is_search()) { return array('search','index'); }
        if (is_singular()) {
            if (is_page()) {
                return array('page','index');
            } elseif(is_single()) {
                return array('single-post','single','index');
            } elseif (is_attachment()) {
                return array('attachment','index');
            }

        }
        return array('index');
    }

    private static function loadTemplate($array) {

        global $wp_styles,$wp_scripts;
        if (count($array)===0) return;
        if (!self::$renderer) {
            $paths = new \SplPriorityQueue;
            $paths->insert(get_template_directory().'/app/views', 200);
            $paths->insert(__DIR__.'/views', 100);
            self::$renderer = new BladeRenderer($paths, array('cache_path' => get_template_directory(). '/public/views/'));
            self::$renderer->addCustomCompiler('wp_head', function($expression) {
                return '<?php wp_head(); ?>';
            });
            self::$renderer->addCustomCompiler('wp_footer', function($expression) {
                return '<?php wp_footer(); ?>';
            });
        }
        if (self::checkTemplatePresence($array[0])) {
            self::$renderer->render($array[0], []);
            $wp_styles->done = array();
            $wp_scripts->done = array();
            $template = self::$renderer->render($array[0], self::$data);
            if (!is_admin()) {
                $wp_styles->done = array();
                $wp_scripts->done = array();
            }
            echo $template;
        } else {
            self::loadTemplate(array_slice($array,1));
        }
    }
    protected static function checkTemplatePresence($name) {
        if (file_exists(get_template_directory()."/app/views/${name}.blade.php")) return true;
        if (file_exists(__DIR__."/views/${name}.blade.php")) return true;
        return false;
    }
    public static function render() {
        print_r(self::getTemplate());
        self::$data = self::getData();
        self::loadTemplate(self::getTemplate());

    }
}
