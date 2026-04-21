<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<aside class="portal-sidebar">
    <div class="portal-sidebar__top">
        <div class="portal-sidebar__pattern">
            <img
                src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/Rectangle 66 (1).png' ); ?>"
                alt="Орнамент"
            >
        </div>
        
        <nav class="portal-sidebar__nav" aria-label="Основное меню">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="portal-sidebar__link<?php echo is_front_page() ? ' is-active' : ''; ?>">
                Главная
            </a>

            <a href="<?php echo esc_url( home_url( '/osnovy-ideologa/' ) ); ?>" class="portal-sidebar__link<?php echo is_page( 'osnovy-ideologa' ) ? ' is-active' : ''; ?>">
                Основы идеолога
            </a>

            <?php
            $portal_calendar_pages = get_pages(
                array(
                    'meta_key'   => '_wp_page_template',
                    'meta_value' => 'page-kalendar-meropriyatiy.php',
                    'number'     => 1,
                )
            );
            $portal_calendar_url   = ! empty( $portal_calendar_pages )
                ? get_permalink( $portal_calendar_pages[0]->ID )
                : home_url( '/kalendar-meropriyatiy/' );
            ?>
            <a href="<?php echo esc_url( $portal_calendar_url ); ?>" class="portal-sidebar__link<?php echo is_page_template( 'page-kalendar-meropriyatiy.php' ) ? ' is-active' : ''; ?>">
                Календарь мероприятий
            </a>

            <?php
            $portal_analytics_pages = get_pages(
                array(
                    'meta_key'   => '_wp_page_template',
                    'meta_value' => 'page-analytics.php',
                    'number'     => 1,
                )
            );
            $portal_analytics_url   = ! empty( $portal_analytics_pages )
                ? get_permalink( $portal_analytics_pages[0]->ID )
                : home_url( '/analytics/' );
            ?>
            <a href="<?php echo esc_url( $portal_analytics_url ); ?>" class="portal-sidebar__link<?php echo function_exists( 'portal_theme_is_analytics_page' ) && portal_theme_is_analytics_page() ? ' is-active' : ''; ?>">
                Аналитика
            </a>

            <?php
            $portal_bib_pages = get_pages(
                array(
                    'meta_key'   => '_wp_page_template',
                    'meta_value' => 'page-biblioteka-praktik.php',
                    'number'     => 1,
                )
            );
            $portal_bib_url   = ! empty( $portal_bib_pages )
                ? get_permalink( $portal_bib_pages[0]->ID )
                : home_url( '/biblioteka-praktik/' );
            ?>
            <a href="<?php echo esc_url( $portal_bib_url ); ?>" class="portal-sidebar__link<?php echo is_page_template( 'page-biblioteka-praktik.php' ) ? ' is-active' : ''; ?>">
                Библиотека практик
            </a>

            <?php
            $portal_sov_pages = get_pages(
                array(
                    'meta_key'   => '_wp_page_template',
                    'meta_value' => 'page-sovetnik-plus.php',
                    'number'     => 1,
                )
            );
            $portal_sov_url   = ! empty( $portal_sov_pages )
                ? get_permalink( $portal_sov_pages[0]->ID )
                : home_url( '/sovetnik-plus/' );
            ?>
            <a href="<?php echo esc_url( $portal_sov_url ); ?>" class="portal-sidebar__link<?php echo is_page_template( 'page-sovetnik-plus.php' ) ? ' is-active' : ''; ?>">
                Советник +
            </a>

            <?php
            $portal_mediabank_pages = get_pages(
                array(
                    'meta_key'   => '_wp_page_template',
                    'meta_value' => 'page-mediabank.php',
                    'number'     => 1,
                )
            );
            $portal_mediabank_url   = ! empty( $portal_mediabank_pages )
                ? get_permalink( $portal_mediabank_pages[0]->ID )
                : home_url( '/mediabank/' );
            ?>
            <a href="<?php echo esc_url( $portal_mediabank_url ); ?>" class="portal-sidebar__link<?php echo is_page_template( 'page-mediabank.php' ) ? ' is-active' : ''; ?>">
                Медиабанк
            </a>

            <?php
            $portal_kse_pages = get_pages(
                array(
                    'meta_key'   => '_wp_page_template',
                    'meta_value' => 'page-kalendar-klyuchevyy-sobytiy.php',
                    'number'     => 1,
                )
            );
            $portal_kse_url   = ! empty( $portal_kse_pages )
                ? get_permalink( $portal_kse_pages[0]->ID )
                : home_url( '/kalendar-klyuchevyy-sobytiy/' );
            ?>
            <a href="<?php echo esc_url( $portal_kse_url ); ?>" class="portal-sidebar__link<?php echo is_page_template( 'page-kalendar-klyuchevyy-sobytiy.php' ) ? ' is-active' : ''; ?>">
                Календарь событий
            </a>
        </nav>
    </div>

    <div class="portal-sidebar__socials">
        <a href="https://www.instagram.com/belenergo?igsh=MXZrMjVvMHpqZGd5NQ==" aria-label="Instagram">
            <img src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/icon_instagram.png' ); ?>">
        </a>

        <a href="https://t.me/belenergo" aria-label="Telegram">
            <img  src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/icon_telegram.png' ); ?>">
        </a>

        <a href="#" aria-label="Viber">
            <img src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/icon_viber.png' ); ?>" alt="">
        </a>
    </div>

    <!-- <div class="portal-sidebar__pattern">
            <img
                src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/Rectangle 66 (1).png' ); ?>"
                alt="Орнамент"
            >
        </div> -->
</aside>
