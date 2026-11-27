<?php

/* =========================================
   PVG - Load Website Assets
========================================= */

function pvg_load_assets() {

    wp_enqueue_style(
        'pvg-style',
        get_stylesheet_uri(),
        array(),
        '1.0'
    );

}

add_action(
    'wp_enqueue_scripts',
    'pvg_load_assets'
);

add_theme_support('title-tag');
add_theme_support('post-thumbnails');


/* =========================================
   PVG - Insights & News
========================================= */

function pvg_get_website_articles() {

    $query = new WP_Query(array(
        'post_type' => 'post',
        'post_status' => 'publish',
        'posts_per_page' => 50,
        'orderby' => 'date',
        'order' => 'DESC',
        'category_name' => 'company-news,hr-knowledge',
        'has_password' => false,
        'no_found_rows' => true
    ));

    $articles = array();

    foreach ($query->posts as $post) {

        $id = $post->ID;

        $categories = get_the_category($id);

        $category = '';
        $category_slug = '';

        foreach ($categories as $cat) {

            if (in_array($cat->slug, array(
                'company-news',
                'hr-knowledge'
            ), true)) {

                $category = $cat->name;
                $category_slug = $cat->slug;

                break;
            }
        }

        $articles[] = array(

            'id' => $id,

            'title' => get_the_title($id),

            'excerpt' => wp_strip_all_tags(
                get_the_excerpt($post)
            ),

            'content' => wp_kses_post(
                apply_filters(
                    'the_content',
                    $post->post_content
                )
            ),

            'image' => get_the_post_thumbnail_url(
                $id,
                'large'
            ) ?: '',

            'category' => $category,

            'category_slug' => $category_slug,

            'date' => get_the_date(
                'd/m/Y',
                $id
            ),

            'url' => get_permalink($id)

        );
    }

    return $articles;
}


/* =========================================
   Send Article Data to Website
========================================= */

function pvg_export_articles() {

    $articles = pvg_get_website_articles();

    $json = wp_json_encode(
        $articles,
        JSON_HEX_TAG |
        JSON_HEX_AMP |
        JSON_HEX_APOS |
        JSON_HEX_QUOT
    );

    if ($json === false) {
        $json = '[]';
    }

    echo '<script id="pvg-article-data">';

    echo 'window.PVG_ARTICLES = ' . $json . ';';

    echo '</script>';
}

add_action(
    'wp_head',
    'pvg_export_articles'
);