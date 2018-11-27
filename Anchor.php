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
    public static function Init() {
        add_filter( 'theme_templates', array('Balise\AnchorFramework\Anchor','loadThemeTemplates'),10,4);
    }
    private static function getData() {
        global $post;
        $return = new PostWrapper($post, true);
        if (!is_singular()) {

        } else {

            $return->posts = array();
            while (have_posts()) {
                the_post();
                $subreturn = new PostWrapper($post, true);
                $return->posts[] = $subreturn;
            }
        }
        return $return;
    }
    protected static function getAllBlades($pattern=null, $traversePostOrder=0 ) {
        if (!$pattern) $pattern = get_template_directory().'/app/views/**/*.blade.php';

        $patternParts = explode('/**/', $pattern);

        $dirs = glob(array_shift($patternParts) . '/*', GLOB_ONLYDIR | GLOB_NOSORT);

        $files = glob(str_replace('/**/','/',$pattern));
        foreach ($dirs as $dir) {
            $subDirContent = self::getAllBlades($dir . '/**/' . implode('/**/', $patternParts), $traversePostOrder);
            if (!$traversePostOrder) {
                $files = array_merge($files, $subDirContent);
            } else {
                $files = array_merge($subDirContent, $files);
            }
        }

        return $files;
    }
    public static function loadThemeTemplates ( $post_templates, $object=null, $post=null, $post_type=null ) {
        $blades = self::getAllBlades();
        foreach($blades as $blade) {
            if (preg_match( '/{{--\s*Template Name:(.*)--}}/mi', file_get_contents($blade), $header ) ) {
                ;

                $post_templates[basename($blade)] = $header[1];
            }
        }

        return $post_templates;
    }
    private static function getTemplate() {

        global $post;

        $templates = array('index');
        if (is_404()) { array_unshift($templates, '404'); }
        if (is_search()) { array_unshift($templates, 'search'); }

        if (is_singular()) { array_unshift($templates, 'singular'); }

        if (is_attachment()) {
            $mime = $post->post_mime_type;
            array_unshift($templates, $template, $mime, 'attachment');
            // TOTO: COMPLETE WITH MEDIA

        } elseif (is_single()) {
            $type = $post->post_type;
            $slug = $post->post_name;
            $id = $post->id;
            $template = get_page_template();
            array_unshift($templates, $template, 'single-'.$slug.'-'.$type, 'single-'.$type,'single');
        }
        if (is_page()) {
            $slug = $post->post_name;
            $id = get_the_ID();
            $template = get_page_template();
            array_unshift($templates, $template, 'page-'.$slug, 'page-'.$id,'page');
        }


        if (is_archive()) { array_unshift($templates, 'archive'); }
        if (is_tag()) {
            $queried = get_queried_object();
            array_unshift($templates, 'tag-'.$queried->rewrite['slug'],'tag-'.$queried->term_id, 'tag');
        }
        // TODO; COMPLETE ARCHIVE

        if (is_home()) { array_unshift($templates, 'home'); }
        if (is_front_page()) { array_unshift($templates, 'front-page'); }

        if ($tmpl = get_post_meta(get_the_ID(), '_wp_page_template',true)) {

            array_unshift($templates, basename($tmpl,'.blade.php'));
        }

        return $templates;
    }

    private static function loadTemplate($array) {

        global $wp_styles,$wp_scripts;
        if (!$array || !is_array($array) || count($array)===0) return;
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
            @$wp_styles->done = array();
            @$wp_scripts->done = array();
            $template = self::$renderer->render($array[0], self::$data);
            echo $template;
            if (!is_admin()) {
                $wp_styles->done = array();
                $wp_scripts->done = array();
            }
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
        self::$data = self::getData();
        self::loadTemplate(self::getTemplate());

    }
}
