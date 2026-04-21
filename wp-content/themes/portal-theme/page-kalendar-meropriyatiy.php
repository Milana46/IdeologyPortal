<?php
/**
 * Template Name: Календарь мероприятий
 *
 * Создайте страницу в админке и выберите этот шаблон (желательный ярлык: kalendar-meropriyatiy).
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();

$theme_img = get_template_directory_uri() . '/assets/img';
?>

<main class="portal-page">
    <?php get_template_part( 'template-parts/layout/sidebar' ); ?>

    <div class="portal-page__main">
        <?php get_template_part( 'template-parts/layout/site-header' ); ?>

        <div class="calendar-page">
            <nav class="calendar-breadcrumbs" aria-label="<?php esc_attr_e( 'Навигация', 'portal-theme' ); ?>">
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Главная', 'portal-theme' ); ?></a>
                <span class="calendar-breadcrumbs__sep">/</span>
                <span><?php esc_html_e( 'Календарь мероприятий', 'portal-theme' ); ?></span>
            </nav>

            <header class="calendar-hero">
                <div class="calendar-hero__left">
                    <div class="calendar-hero__icon" aria-hidden="true">
                        <img src="<?php echo esc_url( $theme_img . '/calendar.png' ); ?>" alt="">
                    </div>
                    <div class="calendar-hero__text">
                        <h1 class="calendar-hero__title"><?php esc_html_e( 'Календарь мероприятий', 'portal-theme' ); ?></h1>
                        <p class="calendar-hero__subtitle">
                            <?php esc_html_e( 'Календарь государственных праздников и памятных дат, отраслевых и корпоративных событий', 'portal-theme' ); ?>
                        </p>
                    </div>
                </div>
                <div class="calendar-hero__illustration">
                    <img
                        src="<?php echo esc_url( $theme_img . '/calendar_main.png' ); ?>"
                        alt="<?php esc_attr_e( 'Иллюстрация календаря', 'portal-theme' ); ?>"
                    >
                </div>
            </header>

            <div class="calendar-toolbar">
                <label class="calendar-toolbar__search-wrap">
                    <span class="screen-reader-text"><?php esc_html_e( 'Поиск по событиям', 'portal-theme' ); ?></span>
                    <input
                        type="search"
                        class="calendar-toolbar__search"
                        id="calendar-search-input"
                        placeholder="<?php esc_attr_e( 'Поиск по событиям', 'portal-theme' ); ?>"
                        autocomplete="off"
                    >
                </label>
                <label class="calendar-toolbar__sort-wrap">
                    <span class="screen-reader-text"><?php esc_html_e( 'Сортировка', 'portal-theme' ); ?></span>
                    <select class="calendar-toolbar__sort" id="calendar-sort-select" aria-label="<?php esc_attr_e( 'По дате', 'portal-theme' ); ?>">
                        <option value="date-asc"><?php esc_html_e( 'По дате (ближайшие)', 'portal-theme' ); ?></option>
                        <option value="date-desc"><?php esc_html_e( 'По дате (поздние)', 'portal-theme' ); ?></option>
                        <option value="title-asc"><?php esc_html_e( 'По названию А—Я', 'portal-theme' ); ?></option>
                    </select>
                </label>
            </div>

            <div class="calendar-layout">
                <div class="calendar-layout__main">
                    <section class="calendar-card" aria-labelledby="calendar-month-heading">
                        <div class="calendar-card__head">
                            <button type="button" class="calendar-card__nav calendar-card__nav--prev" id="calendar-prev-month" aria-label="<?php esc_attr_e( 'Предыдущий месяц', 'portal-theme' ); ?>">‹</button>
                            <h2 class="calendar-card__month" id="calendar-month-heading"></h2>
                            <button type="button" class="calendar-card__nav calendar-card__nav--next" id="calendar-next-month" aria-label="<?php esc_attr_e( 'Следующий месяц', 'portal-theme' ); ?>">›</button>
                        </div>
                        <p class="calendar-card__legend" role="note">
                            <span class="calendar-legend__item">
                                <span class="calendar-legend__dot calendar-legend__dot--holiday" aria-hidden="true"></span>
                                <?php esc_html_e( 'Государственные праздники', 'portal-theme' ); ?>
                            </span>
                            <span class="calendar-legend__sep" aria-hidden="true">·</span>
                            <span class="calendar-legend__item">
                                <span class="calendar-legend__dot calendar-legend__dot--merop" aria-hidden="true"></span>
                                <?php esc_html_e( 'Мероприятия', 'portal-theme' ); ?>
                            </span>
                        </p>
                        <div class="calendar-weekdays" aria-hidden="true">
                            <span><?php esc_html_e( 'Пн', 'portal-theme' ); ?></span>
                            <span><?php esc_html_e( 'Вт', 'portal-theme' ); ?></span>
                            <span><?php esc_html_e( 'Ср', 'portal-theme' ); ?></span>
                            <span><?php esc_html_e( 'Чт', 'portal-theme' ); ?></span>
                            <span><?php esc_html_e( 'Пт', 'portal-theme' ); ?></span>
                            <span><?php esc_html_e( 'Сб', 'portal-theme' ); ?></span>
                            <span><?php esc_html_e( 'Вс', 'portal-theme' ); ?></span>
                        </div>
                        <div class="calendar-grid" id="calendar-grid" role="grid" aria-labelledby="calendar-month-heading"></div>
                        <p class="calendar-card__hint">
                            <?php esc_html_e( 'Нажмите на число, чтобы открыть подробности: государственные праздники и мероприятия. Мероприятия добавляются в консоли: раздел «Календарь мероприятий».', 'portal-theme' ); ?>
                        </p>
                    </section>

                    <section class="calendar-holidays calendar-holidays--state" aria-labelledby="calendar-holidays-title">
                        <h2 class="calendar-holidays__title" id="calendar-holidays-title"><?php esc_html_e( 'Государственные праздники', 'portal-theme' ); ?></h2>
                        <ul class="calendar-holidays__list" id="calendar-holidays-list"></ul>
                    </section>

                    <section class="calendar-holidays calendar-holidays--merop" aria-labelledby="calendar-merop-title">
                        <h2 class="calendar-holidays__title" id="calendar-merop-title"><?php esc_html_e( 'Мероприятия', 'portal-theme' ); ?></h2>
                        <ul class="calendar-holidays__list" id="calendar-merop-list"></ul>
                    </section>
                </div>

                <aside class="calendar-layout__aside" aria-label="<?php esc_attr_e( 'Информационные материалы', 'portal-theme' ); ?>">
                    <div class="calendar-banner calendar-banner--work5">
                        <img src="<?php echo esc_url( $theme_img . '/work5.png' ); ?>" alt="<?php esc_attr_e( '2025—2029 Пятилетка качества', 'portal-theme' ); ?>">
                    </div>
                    <div class="calendar-banner calendar-banner--woman">
                        <img src="<?php echo esc_url( $theme_img . '/woman-belarus.png' ); ?>" alt="<?php esc_attr_e( 'Год белорусской женщины', 'portal-theme' ); ?>">
                    </div>
                </aside>
            </div>
        </div>
    </div>
</main>

<div class="calendar-modal" id="calendar-note-modal" hidden>
    <div class="calendar-modal__backdrop" id="calendar-note-backdrop"></div>
    <div class="calendar-modal__dialog calendar-modal__dialog--view" role="dialog" aria-modal="true" aria-labelledby="calendar-note-modal-title">
        <h3 id="calendar-note-modal-title" class="calendar-modal__title"><?php esc_html_e( 'События на дату', 'portal-theme' ); ?></h3>
        <p class="calendar-modal__date" id="calendar-note-modal-date"></p>
        <div class="calendar-modal__view" id="calendar-note-modal-body"></div>
        <div class="calendar-modal__actions">
            <button type="button" class="portal-btn portal-btn--ghost" id="calendar-note-close"><?php esc_html_e( 'Закрыть', 'portal-theme' ); ?></button>
            <button type="button" class="calendar-modal__close" id="calendar-note-close-x" aria-label="<?php esc_attr_e( 'Закрыть', 'portal-theme' ); ?>">×</button>
        </div>
    </div>
</div>

<?php get_footer(); ?>
