<?php
namespace FF\Vite;

function get_manifest(){
    $manifest_file = __DIR__ . '/dist/wp-manifest.json';
    $manifest = wp_json_file_decode( $manifest_file );
    return $manifest;
}

function get_mode($manifest){
    if( !$manifest ) {
        return 'build';
    }
    if( $manifest->mode == 'dev' ) {
        return 'dev';
    }
    return 'build';
}

function load_asset($handle, $options = []){
    $manifest = get_manifest();
    if( !$manifest || $manifest->mode == 'dev' ) {
        load_asset_dev( $handle );
    } else {
        load_asset_production( $handle, $options, $manifest );
    }
}

function load_asset_production( $handle, $options ){
    $manifest = get_manifest();
    if( !isset( $manifest->entry_points->$handle ) ) return;
    
    load_css( $handle, $manifest );

    if( isset( $options['css_only'] ) && $options['css_only'] ) return;

    $js_src = get_stylesheet_directory_uri() . '/vite-wp/dist/'. $manifest->entry_points->$handle->file;
    wp_enqueue_script($handle, $js_src, [], null, true);
}

function load_css( $handle, $manifest  ){
    if( !isset($manifest->entry_points->$handle->css) ) return; 
    foreach( $manifest->entry_points->$handle->css as $base_src ) {
        $src = str_replace( $_SERVER['DOCUMENT_ROOT'], get_bloginfo('url'), str_replace('\\', '/', __DIR__ ) ) . '/dist/'. $base_src;
        wp_enqueue_style( $handle, $src );
    }
}

function load_asset_dev( $handle ){
    $src = 'http://localhost:5173/'. $handle;
    wp_enqueue_script($handle, $src);
    add_filter('script_loader_tag', function( $tag, $js_handle ) use ($handle){
        if( $js_handle !== $handle ) return $tag;
        if( strpos( $tag, ' type="module"' ) === false ) {
            $tag = str_replace('<script', '<script type="module"', $tag);
        }
        return $tag;
    }, 100, 2);
}

add_action('wp_enqueue_scripts', function(){
    load_asset('src/main.js');
});

function pre_debug($s){
    echo '<pre>'. print_r( $s, true ) .'</pre>';
}

function load_critical_css(){
    add_action('wp_head', function(){
        
        $handle = 'src/critical.js';
        $manifest = get_manifest();
        
        if( get_mode($manifest) == 'dev' ) {
            // dev, load file
            load_asset_dev($handle);
        } 
        else {
            // production, inline css
            $manifest = get_manifest();
            if( !$manifest ) return;
            $css_file = __DIR__ .'/dist/'.$manifest->entry_points->$handle->css[0];
            if( !file_exists($css_file) ) return;
            echo '<style type="text/css">'. file_get_contents($css_file) .'</style>';
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
