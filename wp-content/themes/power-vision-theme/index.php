<?php

/* =========================================
   PVG - Main Website & Article Detail
========================================= */

$prototype = file_get_contents(
    get_template_directory() . '/prototype.html'
);

if ($prototype === false) {
    wp_die('Unable to load website prototype.');
}

/* Load website styles */

$prototype = preg_replace(
    '~<style\b[^>]*>.*?</style>~is',
    '',
    $prototype,
    1
);

/* Use WordPress page title */

$prototype = preg_replace(
    '~<title\b[^>]*>.*?</title>~is',
    '',
    $prototype,
    1
);

$is_article = is_singular('post');

$home = home_url('/');


/* =========================================
   Article Detail Page
========================================= */

if ($is_article) {

    $id = get_queried_object_id();

    $post = get_post($id);

    $categories = get_the_category($id);

    ob_start();

    ?>

    <section id="pvg-detail" class="section">

        <div class="wrap"
             style="max-width:850px;">

            <a href="<?php echo esc_url(
                $home . '#insights'
            ); ?>">

                &larr; กลับไปยังข่าวสารและความรู้

            </a>

            <p class="eyebrow"
               style="margin-top:24px;">

                <?php echo esc_html(
                    $categories
                    ? $categories[0]->name
                    : 'Insights & News'
                ); ?>

            </p>

            <h1>

                <?php echo esc_html(
                    get_the_title($id)
                ); ?>

            </h1>

            <p class="muted">

                <?php echo esc_html(
                    get_the_date('d/m/Y', $id)
                ); ?>

            </p>

            <?php if (!post_password_required($post)) : ?>

                <?php if (has_excerpt($id)) : ?>

                    <p class="muted">

                        <?php echo esc_html(
                            get_the_excerpt($post)
                        ); ?>

                    </p>

                <?php endif; ?>

                <?php echo get_the_post_thumbnail(
                    $id,
                    'large',
                    array(
                        'style' =>
                        'width:100%;height:auto;
                        border-radius:16px;
                        margin:16px 0 24px;'
                    )
                ); ?>

            <?php endif; ?>

            <div class="pvg-detail-content"
                 style="line-height:1.8;
                 overflow-wrap:anywhere;">

                <?php echo wp_kses_post(
                    apply_filters(
                        'the_content',
                        get_the_content(
                            null,
                            false,
                            $post
                        )
                    )
                ); ?>

            </div>

            <div style="margin-top:30px;">

                <a class="btn outline"
                   href="<?php echo esc_url(
                       $home . '#insights'
                   ); ?>">

                    &larr; ข่าวสารและความรู้

                </a>

            </div>

        </div>

    </section>

    <?php

    $detail = ob_get_clean();

    $marker =
        '<section class="page" id="insights" hidden>';

    $prototype = str_replace(
        $marker,
        $detail . $marker,
        $prototype
    );

}


/* =========================================
   WordPress Header
========================================= */

ob_start();

wp_head();

$head = ob_get_clean();


/* Article Detail Styles */

if ($is_article) {

    $head .= '<style>

        .page {
            display:none !important;
        }

        #pvg-detail img {
            max-width:100%;
            height:auto;
        }

        #pvg-detail iframe {
            max-width:100%;
        }

    </style>';

}


/* =========================================
   WordPress Footer
========================================= */

ob_start();

wp_footer();

$footer = ob_get_clean();


/* Article Navigation Configuration */

$config = wp_json_encode(
    array(
        'home' => $home,
        'article' => $is_article
    ),
    JSON_HEX_TAG |
    JSON_HEX_AMP |
    JSON_HEX_APOS |
    JSON_HEX_QUOT
);

$footer .=
    '<script>window.PVG_DETAIL_CONFIG = '
    . $config .
    ';</script>';


/* =========================================
   Open Full Article Instead of Popup
========================================= */

$footer .= <<<'JS'

<script>

document.addEventListener('click', function(e) {

    const config = window.PVG_DETAIL_CONFIG;

    if (!config) return;

    const button = e.target.closest(
        '[data-pvg-post]'
    );

    if (button) {

        const post = (
            window.PVG_ARTICLES || []
        ).find(p =>

            Number(p.id) ===
            Number(button.dataset.pvgPost)

        );

        if (post && post.url) {

            e.preventDefault();

            e.stopImmediatePropagation();

            location.href = post.url;

            return;

        }

    }

    if (config.article) {

        const link = e.target.closest(
            'a[href^="#"]'
        );

        if (link &&
            /^#(home|product|insights|about|contact)$/
            .test(link.getAttribute('href'))
        {

            e.preventDefault();

            e.stopImmediatePropagation();

            location.href =
                config.home +
                link.getAttribute('href');

        }

    }

}, true);

</script>

JS;


/* =========================================
   Display Website
========================================= */

$prototype = str_replace(
    '</head>',
    $head . '</head>',
    $prototype
);

$prototype = str_replace(
    '</body>',
    $footer . '</body>',
    $prototype
);

echo $prototype;