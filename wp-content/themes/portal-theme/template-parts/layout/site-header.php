<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<header class="portal-header">
    <div class="portal-header__brand">
        <div class="portal-header__logo">
            <img
                src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/logoBelenergo.png' ); ?>"
                alt="Логотип Белэнерго"
            >
        </div>

        <div class="portal-header__title">
            ГПО «Белэнерго»
        </div>
    </div>

    <div class="portal-header__right">
        <form
            class="portal-search"
            method="get"
            action="<?php echo esc_url( home_url( '/' ) ); ?>"
        >
            <input
                type="search"
                name="s"
                placeholder="Поиск по сайту..."
            >

            <button type="submit">
                Поиск
            </button>
        </form>

        <a href="#" class="portal-btn portal-btn--blue">
            Перейти к материалам
        </a>
    </div>
</header>