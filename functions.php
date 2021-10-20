<?php


// WP REST Headless
add_filter( 'wp_headless_rest__enable_rest_cleanup', '__return_true' );
add_filter( 'wp_headless_rest__disable_front_end', '__return_true' );



add_filter( 'wp_headless_rest__rest_endpoints_to_remove', 'wp_rest_headless_disable_endpoints' );
function wp_rest_headless_disable_endpoints( $endpoints_to_remove ) {

	$endpoints_to_remove = array(
        '/wp/v2/post',
		'/wp/v2/media',
		'/wp/v2/types',
		'/wp/v2/statuses',
		'/wp/v2/taxonomies',
		'/wp/v2/tags',
		'/wp/v2/users',
		'/wp/v2/comments',
		'/wp/v2/themes',
		'/wp/v2/blocks',
		'/wp/v2/block-renderer',
        '/oembed/',
        '/wp/v2/pages',

        // CUSTOM
        '/wp/v2/pa_video_gallery',
        '/wp/v2/categories',
        '/wp/v2/search',
        '/wp/v2/block-types',
        '/wp/v2/plugins',
        '/wp/v2/block-directory',
        '/wp/v2/settings',
	);


	return $endpoints_to_remove;
}

add_filter( 'wp_headless_rest__rest_object_remove_nodes', 'wp_rest_headless_clean_response_nodes' );
function wp_rest_headless_clean_response_nodes( $items_to_remove ) {

	$items_to_remove = array(
		'guid',
		'_links',
        'ping_status'
	);

	return $items_to_remove;
}


add_filter( 'wp_headless_rest__cors_rules', 'wp_rest_headless_header_rules' );
function wp_rest_headless_header_rules( $rules ) {

	$rules = array(
		'Access-Control-Allow-Origin'      => $origin,
		'Access-Control-Allow-Methods'     => 'GET',
		'Access-Control-Allow-Credentials' => 'true',
		'Access-Control-Allow-Headers'     => 'Access-Control-Allow-Headers, Content-Type, origin',
		'Access-Control-Expose-Headers'    => array( 'Link', false ), //Use array if replace param is required
	);

	return $rules;
}

function menu_positions(){
    return [
        'global-header' => "Global - Header",
        'global-footer-1' => "Global - Footer 01",
        'global-footer-2' => "Global - Footer 02",
        'global-footer-3' => "Global - Footer 03",
    ];
}

$menus = menu_positions();

add_action( 'init', function() use ($menus){
    foreach ($menus as $key => $menu) {
        register_nav_menu($key , $menu);
    }

});

add_action( 'rest_api_init', function () use ($menus) {

    foreach ($menus as $key => $menu) {
        register_rest_route( 'wp/v2/menus', '/'. $key, array(
            'methods' => 'GET',
            'callback' => function() use ($key){
                $locations = get_nav_menu_locations();
                $object = wp_get_nav_menu_object( $locations[$key] );
                $menu_items = wp_get_nav_menu_items( $object->name, $args );
                return  (object)['name'=> $object->name,  'itens'=> $menu_items];
            },
        ));
    }
});

function pa_get_banner_global(){
    
    $ativo = get_field('ativo', 'option');
    $title = get_field('titulo', 'option');
    $link = get_field('link', 'option');
    $banner_background = get_field('cor_banner', 'option');
    $imagem_large = get_field('imagem_large', 'option');
    $imagem_medium = get_field('imagem_medium', 'option');
    $imagem_small = get_field('imagem_small', 'option');

    $json = array('enable' => $ativo, 'link' => $link, 'title' => $title, 'color' => $banner_background, 'image_large' => $imagem_large, 'image_medium' => $imagem_medium, 'image_small'=> $imagem_small);

    return $json;
    
}


add_action( 'rest_api_init', function(){
    register_rest_route( 'wp/v2', '/banner', array(
        'methods' => 'GET',
        'callback' => 'pa_get_banner_global',
    ));
});

add_filter('init', 'processTaxonomies');

function processTaxonomies(){
    $tax = ['xtt-pa-colecoes', 'xtt-pa-editorias', 'xtt-pa-departamentos', 'xtt-pa-projetos', 'xtt-pa-sedes', 'xtt-pa-owner'];

    foreach ($tax as $t){
        add_filter( 'rest_'. $t .'_collection_params', 'big_json_change_post_per_page', 10, 1 );
    }
}

function big_json_change_post_per_page( $params ) {
    if ( isset( $params['per_page'] ) ) {
        $params['per_page']['maximum'] = 300;
    }
    return $params;
}