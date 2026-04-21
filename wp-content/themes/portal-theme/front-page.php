<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();

$sheet_url   = get_option( 'portal_sheets_embed_url', '' );
$sheet_title = get_option( 'portal_sheets_block_title', '' );

if ( $sheet_url && ! $sheet_title ) {
    $sheet_title = __( 'Оперативные данные', 'portal-theme' );
}

$tab_about_html =
    function_exists( 'portal_core_get_tab_html' )
    ? portal_core_get_tab_html( 'portal_home_tab_about_html' )
    : '';
$tab_adv_html =
    function_exists( 'portal_core_get_tab_html' )
    ? portal_core_get_tab_html( 'portal_home_tab_advantages_html' )
    : '';
$tab_sec_html =
    function_exists( 'portal_core_get_tab_html' )
    ? portal_core_get_tab_html( 'portal_home_tab_sections_html' )
    : '';
?>

<main class="portal-page">
    <?php get_template_part( 'template-parts/layout/sidebar' ); ?>

    <div class="portal-page__main">
        <?php get_template_part( 'template-parts/layout/site-header' ); ?>

        <section class="portal-hero">
            <div class="portal-hero__content">
                <h1>
                    Единая платформа
                </h1>

                <p>
                    Единая цифровая платформа для идеологов и специалистов
                    по связи с общественностью ГПО «Белэнерго»
                </p>

                <div class="portal-hero__buttons">
                    <?php
                    $call_url   = get_option( 'portal_calls_url', '' );
                    $call_label = get_option( 'portal_calls_label', __( 'Перейти к созвону', 'portal-theme' ) );
                    $call2_url  = get_option( 'portal_calls_secondary_url', '' );
                    $call2_lbl  = get_option( 'portal_calls_secondary_label', '' );
                    ?>
                    <?php if ( $call_url ) : ?>
                        <a href="<?php echo esc_url( $call_url ); ?>" class="portal-btn portal-btn--green" target="_blank" rel="noopener noreferrer">
                            <?php echo esc_html( $call_label ); ?>
                        </a>
                    <?php else : ?>
                        <a href="#" class="portal-btn portal-btn--green">
                            О платформе
                        </a>
                    <?php endif; ?>

                    <?php if ( $call2_url && $call2_lbl ) : ?>
                        <a href="<?php echo esc_url( $call2_url ); ?>" class="portal-btn portal-btn--blue" target="_blank" rel="noopener noreferrer">
                            <?php echo esc_html( $call2_lbl ); ?>
                        </a>
                    <?php else : ?>
                        <a href="#" class="portal-btn portal-btn--blue">
                            Открыть медиабанк
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="portal-hero__image">
                <img
                    src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/main_pic.png' ); ?>"
                    alt="Иллюстрация платформы"
                >
            </div>
        </section>

        <nav class="portal-tabs" role="tablist" aria-label="<?php esc_attr_e( 'Разделы главной страницы', 'portal-theme' ); ?>">
            <button
                type="button"
                role="tab"
                class="portal-tabs__tab is-active"
                id="portal-tab-about"
                data-tab="about"
                aria-selected="true"
                aria-controls="portal-panel-about"
            >
                <?php esc_html_e( 'О платформе', 'portal-theme' ); ?>
            </button>

            <button
                type="button"
                role="tab"
                class="portal-tabs__tab"
                id="portal-tab-advantages"
                data-tab="advantages"
                aria-selected="false"
                aria-controls="portal-panel-advantages"
            >
                <?php esc_html_e( 'Преимущества платформы', 'portal-theme' ); ?>
            </button>

            <button
                type="button"
                role="tab"
                class="portal-tabs__tab"
                id="portal-tab-sections"
                data-tab="sections"
                aria-selected="false"
                aria-controls="portal-panel-sections"
            >
                <?php esc_html_e( 'Основные разделы', 'portal-theme' ); ?>
            </button>
        </nav>

        <div class="portal-layout">
            <div class="portal-content">
                <div class="portal-main-panels">
                    <div
                        class="portal-tab-panel is-active"
                        id="portal-panel-about"
                        role="tabpanel"
                        aria-labelledby="portal-tab-about"
                        data-tab-panel="about"
                    >
                        <div class="portal-tab-panel__inner portal-prose">
                            <?php if ( $tab_about_html ) : ?>
                                <?php echo $tab_about_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- sanitized in portal_core_get_tab_html ?>
                            <?php elseif ( current_user_can( 'manage_options' ) ) : ?>
                                <p class="portal-widget__placeholder">
                                    <?php esc_html_e( 'Заполните текст вкладки «О платформе» в меню «Портал».', 'portal-theme' ); ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div
                        class="portal-tab-panel"
                        id="portal-panel-advantages"
                        role="tabpanel"
                        aria-labelledby="portal-tab-advantages"
                        data-tab-panel="advantages"
                        hidden
                    >
                        <div class="portal-tab-panel__inner portal-prose">
                            <?php if ( $tab_adv_html ) : ?>
                                <?php echo $tab_adv_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                            <?php elseif ( current_user_can( 'manage_options' ) ) : ?>
                                <p class="portal-widget__placeholder">
                                    <?php esc_html_e( 'Заполните текст вкладки «Преимущества платформы» в меню «Портал».', 'portal-theme' ); ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div
                        class="portal-tab-panel"
                        id="portal-panel-sections"
                        role="tabpanel"
                        aria-labelledby="portal-tab-sections"
                        data-tab-panel="sections"
                        hidden
                    >
                        <div class="portal-tab-panel__inner">
                            <?php if ( $tab_sec_html ) : ?>
                                <div class="portal-prose portal-prose--intro">
                                    <?php echo $tab_sec_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                </div>
                            <?php elseif ( current_user_can( 'manage_options' ) ) : ?>
                                <p class="portal-widget__placeholder">
                                    <?php esc_html_e( 'По желанию добавьте вводный текст для вкладки «Основные разделы» в меню «Портал».', 'portal-theme' ); ?>
                                </p>
                            <?php endif; ?>

                            <section class="portal-section">
                                <h2>
                                    <?php esc_html_e( 'Основные разделы', 'portal-theme' ); ?>
                                </h2>

                                <div class="portal-cards">
                                    <article class="portal-card">
                                        <?php esc_html_e( 'Библиотека практик', 'portal-theme' ); ?>
                                    </article>

                                    <article class="portal-card">
                                        <?php esc_html_e( 'Медиабанк', 'portal-theme' ); ?>
                                    </article>

                                    <article class="portal-card">
                                        <?php esc_html_e( 'Советник+', 'portal-theme' ); ?>
                                    </article>

                                    <article class="portal-card">
                                        <?php esc_html_e( 'Оперативный штаб', 'portal-theme' ); ?>
                                    </article>
                                </div>
                            </section>

                            <?php if ( $sheet_url ) : ?>
                                <section class="portal-section portal-section--live-sheet">
                                    <h2>
                                        <?php echo esc_html( $sheet_title ); ?>
                                    </h2>

                                    <div class="portal-live-sheet">
                                        <iframe
                                            title="<?php echo esc_attr( $sheet_title ); ?>"
                                            src="<?php echo esc_url( $sheet_url ); ?>"
                                            loading="lazy"
                                            referrerpolicy="no-referrer-when-downgrade"
                                        ></iframe>
                                    </div>
                                </section>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <aside class="portal-rightbar">
                <section class="portal-widget">
                    <h3>
                        <?php esc_html_e( 'Актуальное', 'portal-theme' ); ?>
                    </h3>

                    <div class="portal-widget__box portal-widget__box--actual">
                        <?php
                        if ( function_exists( 'portal_core_render_home_file_list' ) ) {
                            portal_core_render_home_file_list( 'actual' );
                        } else {
                            echo '<div class="documents-list__empty">' . esc_html__( 'Включите плагин Portal Core.', 'portal-theme' ) . '</div>';
                        }
                        ?>
                    </div>
                </section>

                <section class="portal-widget">
                    <h3>
                        <?php esc_html_e( 'Необходимые документы', 'portal-theme' ); ?>
                    </h3>

                    <div class="portal-widget__box">
                        <?php
                        if ( function_exists( 'portal_core_render_home_file_list' ) ) {
                            portal_core_render_home_file_list( 'documents' );
                        } else {
                            echo '<div class="documents-list__empty">' . esc_html__( 'Включите плагин Portal Core.', 'portal-theme' ) . '</div>';
                        }
                        ?>
                    </div>
                </section>
            </aside>
        </div>
    </div>
</main>

<?php get_footer(); ?>
