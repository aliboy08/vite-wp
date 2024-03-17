<?php
namespace FF\Vite;

define('VITE_DIR', get_stylesheet_directory() .'/vite-wp/' );
define('VITE_URL', get_stylesheet_directory_uri().'/vite-wp/');
define('VITE_MANIFEST', get_manifest());
define('VITE_MODE', get_mode());
define('VITE_SERVER_ORIGIN', get_dev_server_origin());

function get_manifest(){
    $manifest_file = VITE_DIR . '/dist/wp-manifest.json';
    $manifest = wp_json_file_decode( $manifest_file );
    return $manifest;
}

function get_dev_server_origin(){
    $file = VITE_DIR.'/dist/vite-dev-server.json';
    if(!file_exists($file)) return 'http://localhost:5173';
    $server_config = wp_json_file_decode( $file );
    return $server_config->origin;
}

function get_mode(){
    if( !VITE_MANIFEST ) {
        return 'build';
    }
    if( VITE_MANIFEST->mode == 'dev' ) {
        return 'dev';
    }
    return 'build';
}

function load_asset($handle, $options = []){
    if( VITE_MODE == 'dev' ) {
        load_asset_dev( $handle );
    } else {
        load_asset_production( $handle, $options );
    }
}

function load_asset_production( $handle, $options ){
    if( !isset( VITE_MANIFEST->entry_points->$handle ) ) return;
    $asset = VITE_MANIFEST->entry_points->$handle;

    load_css( $asset );
    if( isset( $options['css_only'] ) && $options['css_only'] ) return;

    $js_src = VITE_URL.'/dist/'.$asset->file;
    wp_enqueue_script($handle, $js_src, [], null, true);
}

function load_css( $asset ){
    if( !isset($asset->css) ) return;
    foreach( $asset->css as $base_src ) {
        $css_src = VITE_URL.'/dist/'.$base_src;
        wp_enqueue_style( $base_src, $css_src );
    }
}

function load_asset_dev( $handle ){
    $src = VITE_SERVER_ORIGIN.'/'.$handle;
    wp_enqueue_script($handle, $src);

    add_filter('script_loader_tag', function( $tag, $js_handle ) use ($handle){
        if( $js_handle !== $handle ) return $tag;
        if( strpos( $tag, ' type="module"' ) === false ) {
            $tag = str_replace('<script', '<script type="module"', $tag);
        }
        return $tag;
    }, 100, 2);
}

function pre_debug($s){
    echo '<pre>'. print_r( $s, true ) .'</pre>';
}

function load_critical_css(){
    add_action('wp_head', function(){
        $handle = 'src/critical.js';
        if( VITE_MODE == 'dev' ) {
            // dev, load file
            load_asset_dev($handle);
        }
        else {
            // production, inline css
            if( !VITE_MANIFEST || !VITE_MANIFEST->entry_points->$handle ) return;
            // $css_file = VITE_DIR .'/dist/'.VITE_MANIFEST->$handle->css[0];
            // if( !file_exists($css_file) ) return;
            // echo '<style type="text/css">'. file_get_contents($css_file) .'</style>';
            $src = VITE_URL . '/dist/'. VITE_MANIFEST->entry_points->$handle->css[0];
            echo '<link rel="stylesheet" href="'. $src .'">';
        }
    }, 0);
}

function defer_css(){
    add_filter( 'style_loader_tag', function( $tag, $handle, $href, $media ){
        $exclude = [
            'admin-bar',
        ];
        if( in_array( $handle, $exclude )  ) return $tag;
        if( strpos( $tag, 'rel="preload"' ) === false ) {
            $defer = 'rel="preload" as="style" onload="this.onload=null;this.rel=\'stylesheet\';"';
            $tag = str_replace('>', $defer . '>', $tag);
        }
        return $tag;
    }, 10, 4 );
}
