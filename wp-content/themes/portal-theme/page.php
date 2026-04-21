<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>

<main class="portal-page">
    <?php get_template_part( 'template-parts/layout/sidebar' ); ?>

    <div class="portal-page__main">
        <?php get_template_part( 'template-parts/layout/site-header' ); ?>

        <section class="portal-section">
            <?php if ( have_posts() ) : ?>
                <?php while ( have_posts() ) : the_post(); ?>
                    <h1><?php the_title(); ?></h1>

                    <div>
                        <?php the_content(); ?>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </section>
    </div>
</main>

<?php get_footer(); ?>