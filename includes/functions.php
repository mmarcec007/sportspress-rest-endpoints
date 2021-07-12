<?php
/*
* get post by slug
*/
function wp_get_post_by_slug( $slug, $post_type = 'post', $unique = true ){
    $args=array(
        'name' => $slug,
        'post_type' => $post_type,
        'post_status' => 'publish',
        'posts_per_page' => 1
    );
    $my_posts = get_posts( $args );
    if( $my_posts ) {
        //echo 'ID on the first post found ' . $my_posts[0]->ID;
        if( $unique ){
            return $my_posts[ 0 ];
        }else{
            return $my_posts;
        }
    }
    return false;
}
