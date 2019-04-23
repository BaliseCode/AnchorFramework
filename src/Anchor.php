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
    public static $templates = array();

    /**
     * Is WooCommerce loaded?
     *
     * @var boolean
     */
    private static $woocommerce_loaded = false;

    /**
     * Initial function that hook into the functions.php file
     * - Make sure that custom templates are loaded
     * - Make sure the template_loader hierarchy gets saved
     *
     */
    
    public static function Init()
    {
        add_filter('template_include',function($t){
        return get_template_directory().'/index.php';
        });
        add_filter('theme_templates', array('Balise\AnchorFramework\Anchor', 'loadThemeTemplates'), 10, 4);
        $types = array('index', '404', 'archive', 'author', 'category', 'tag', 'taxonomy', 'date', 'embed', 'home', 'frontpage', 'page', 'paged', 'search', 'single', 'singular', 'attachment');
        foreach ($types as $type) {
            add_filter("{$type}_template", array('Balise\AnchorFramework\Anchor', 'getThemeTemplate'), 10, 3);
        }
        
        if (!self::$renderer) {
            $paths = new \SplPriorityQueue;
            $paths->insert(get_template_directory() . '/app/views', 200);
            $paths->insert(dirname(__DIR__) . '/views', 100);
            self::$renderer = new BladeRenderer($paths, array('cache_path' => get_template_directory() . '/public/views/'));
            self::$renderer->addCustomCompiler('wp_head', function ($expression) {
                return '<?php wp_head(); ?>';
            });
            self::$renderer->addCustomCompiler('doquery', function ($expression) {
                return '<?php
                if (!isset($__posts)) { $posts = array(); }
                $__posts[] = $posts;
                
                $args = '.$expression.';

                if (@!$args["paged"]) {
                   $args["paged"] = get_query_var( "paged", 1 );
                }
                $query = new WP_Query($args);
                $posts = array_map(function($post){
                    return new \Balise\AnchorFramework\PostWrapper($post, true);
                }, $query->posts);

                ?>
                ';
            });
            self::$renderer->addCustomCompiler('endquery', function () {
                return '<?php
                $posts = array_pop($__posts);

                ?>
                ';
            });

            self::$renderer->addCustomCompiler('wp_footer', function ($expression) {
                return '<?php wp_footer(); ?>';
            });
        }
    }
    /**
     * render function hook
     */
    public static function Render($template=null, $data=null)
    {
        if ($template && $data) {
            return self::$renderer->render($template, $data);
        } else {
            self::$data = self::getData();
            self::$templates[] = "index";
            self::loadTemplate(self::$templates);
        }
    }
    /**
     * Load data object to include in the blade template
     */
    private static function getData()
    {
        global $post;
        $return = apply_filters('balise-anchor-getData', null);
        if (!$return) {
            if (!is_singular() || (function_exists('is_shop') && is_shop())) {
                /**
                 * If the post is an archive or the product page(woocommerce) load template page and
                 */
                $return = new PostWrapper($post, true);
                if ((function_exists('is_shop') && is_shop())) {
                    $return = new PostWrapper(get_post(wc_get_page_id('shop')), true);
                }
                if (get_option('page_for_posts')) {
                    $return = new PostWrapper(get_post(get_option('page_for_posts')), true);
                }
                $return->posts = array();
                while (have_posts()) {
                    the_post();
                    $subreturn = new PostWrapper($post, true);
                    $return->posts[] = $subreturn;
                }
            } else {
                $return = new PostWrapper($post, true);
            }
        }
        return $return;
    }

    /**
     * Recursive function to load all blade templates
     */
    protected static function getAllBlades($pattern = null, $traversePostOrder = 0)
    {
        if (!$pattern) {
            $pattern = get_template_directory() . '/app/views/**/*.blade.php';
        }

        $patternParts = explode('/**/', $pattern);
        $dirs = glob(array_shift($patternParts) . '/*', GLOB_ONLYDIR | GLOB_NOSORT);
        $files = glob(str_replace('/**/', '/', $pattern));
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
    /**
     * Load custom template files
     */
    public static function loadThemeTemplates($post_templates, $object = null, $post = null, $post_type = null)
    {
        $blades = self::getAllBlades();
        foreach ($blades as $blade) {
            if (preg_match('/{{--\s*Template Name:(.*)--}}/mi', file_get_contents($blade), $header)) {

                $post_templates[basename($blade, ".blade.php")] = $header[1];
            }
        }
        return $post_templates;
    }

    /**
     * Load template Hierarchy
     * Custom support for WooCommerce
     */
    public static function getThemeTemplate($template, $type, $templates)
    {

        if (class_exists('WC_Template_Loader') && !self::$woocommerce_loaded) {
            self::$woocommerce_loaded = true;
            if (is_page_template()) {
                self::$templates[] = get_page_template_slug();
            }
            if (is_singular('product')) {
                $object = get_queried_object();
                $name_decoded = urldecode($object->post_name);
                if ($name_decoded !== $object->post_name) {
                    self::$templates[] = "single-product-{$name_decoded}";
                }
                self::$templates[] = "single-product-{$object->post_name}";
                self::$templates[] = "single-product";
            }
            if (is_product_taxonomy()) {
                $object = get_queried_object();
                self::$templates[] = 'taxonomy-' . $object->taxonomy . '-' . $object->slug;
                self::$templates[] = 'taxonomy-' . $object->taxonomy;
                self::$templates[] = 'archive-product';
            }
            if (is_post_type_archive('product') || is_page(wc_get_page_id('shop'))) {
                self::$templates[] = 'archive-product';
                $default_file = current_theme_supports('woocommerce') ? 'archive-product.php' : '';
            }
            $fixed_woocommerce_folder = array();
            foreach (self::$templates as $item) {
                $fixed_woocommerce_folder[] = 'woocommerce.' . $item;
                $fixed_woocommerce_folder[] = $item;
            }
            self::$templates = $fixed_woocommerce_folder;
        }

        foreach ($templates as $t) {
            self::$templates[] = basename($t, '.php');
            if (substr(basename($t, '.php'), 0, 6) === "single") {
                $type = substr(basename($t, '.php'), 7);
                self::$templates[] = $type . '/single';
            }
            if (substr(basename($t, '.php'), 0, 7) === "archive") {
                $type = substr(basename($t, '.php'), 8);
                self::$templates[] = $type . '/archive';
            }

        }
        return $template;
    }

    /**
     * Load the blade template
     */
    private static function loadTemplate($array)
    {
        global $wp_styles, $wp_scripts;
        if (!$array || !is_array($array) || count($array) === 0) {
            return;
        }

        if (self::checkTemplatePresence($array[0])) {
            $isadmin = is_admin_bar_showing();
            add_filter('show_admin_bar', '__return_false');
            self::$renderer->render($array[0], []);
            @$wp_styles->done = array();
            @$wp_scripts->done = array(); 
            if ($isadmin) {
                add_filter('show_admin_bar', '__return_true');
            }
            $template = self::$renderer->render($array[0], self::$data);
            
            
            
            $template = str_replace("<html>", "<html " . get_language_attributes() . ">", $template);
            $template = str_replace("<body>", '<body class="' . implode(" ", get_body_class()) . '">', $template);

            echo $template;

            if (!is_admin()) {
                $wp_styles->done = array();
                $wp_scripts->done = array();
            }
        } else {
            self::loadTemplate(array_slice($array, 1));
        }
    }
    /**
     * check for template existance
     */
    protected static function checkTemplatePresence($raw_name)
    {
        $name = str_replace('.', '/', $raw_name);
        if (file_exists(get_template_directory() . "/app/views/${name}.blade.php")) {
            return true;
        }

        if (file_exists(dirname(__DIR__) . "/views/${name}.blade.php")) {
            return true;
        }

        return false;
    }

}
