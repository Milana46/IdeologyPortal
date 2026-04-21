<?php
/**
 * Template Name: Календарь ключевых событий
 *
 * Страница с ярлыком kalendar-klyuchevyy-sobytiy.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$theme_img = get_template_directory_uri() . '/assets/img';

$month_names = array(
	1  => __( 'Январь', 'portal-theme' ),
	2  => __( 'Февраль', 'portal-theme' ),
	3  => __( 'Март', 'portal-theme' ),
	4  => __( 'Апрель', 'portal-theme' ),
	5  => __( 'Май', 'portal-theme' ),
	6  => __( 'Июнь', 'portal-theme' ),
	7  => __( 'Июль', 'portal-theme' ),
	8  => __( 'Август', 'portal-theme' ),
	9  => __( 'Сентябрь', 'portal-theme' ),
	10 => __( 'Октябрь', 'portal-theme' ),
	11 => __( 'Ноябрь', 'portal-theme' ),
	12 => __( 'Декабрь', 'portal-theme' ),
);
?>

<main class="portal-page">
	<?php get_template_part( 'template-parts/layout/sidebar' ); ?>

	<div class="portal-page__main">
		<?php get_template_part( 'template-parts/layout/site-header' ); ?>

		<div class="kse-page">
			<nav class="kse-breadcrumbs" aria-label="<?php esc_attr_e( 'Навигация', 'portal-theme' ); ?>">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Главная', 'portal-theme' ); ?></a>
				<span class="kse-breadcrumbs__sep">/</span>
				<span><?php esc_html_e( 'Календарь событий', 'portal-theme' ); ?></span>
			</nav>

			<header class="kse-hero">
				<div class="kse-hero__left">
					<div class="kse-hero__icon" aria-hidden="true">
						<img src="<?php echo esc_url( $theme_img . '/calendar.png' ); ?>" alt="">
					</div>
					<div class="kse-hero__text">
						<h1 class="kse-hero__title"><?php esc_html_e( 'Календарь ключевых событий', 'portal-theme' ); ?></h1>
						<p class="kse-hero__subtitle">
							<?php esc_html_e( 'График мероприятий, совещаний и ключевых событий', 'portal-theme' ); ?>
						</p>
					</div>
				</div>
				<div class="kse-hero__illustration">
					<img
						src="<?php echo esc_url( $theme_img . '/calendar_main.png' ); ?>"
						alt=""
						loading="lazy"
					>
				</div>
			</header>

			<div class="kse-toolbar">
				<label class="kse-toolbar__search-wrap">
					<span class="kse-sr-only"><?php esc_html_e( 'Поиск по событиям', 'portal-theme' ); ?></span>
					<input type="search" class="kse-toolbar__search" id="kse-search" placeholder="<?php esc_attr_e( 'Поиск по событиям', 'portal-theme' ); ?>" autocomplete="off">
				</label>
				<label class="kse-toolbar__sort-wrap">
					<span class="kse-sr-only"><?php esc_html_e( 'Сортировка', 'portal-theme' ); ?></span>
					<select class="kse-toolbar__sort" id="kse-sort" aria-label="<?php esc_attr_e( 'Сортировка списка', 'portal-theme' ); ?>">
						<option value="date"><?php esc_html_e( 'По дате', 'portal-theme' ); ?></option>
						<option value="title"><?php esc_html_e( 'По названию', 'portal-theme' ); ?></option>
					</select>
				</label>
			</div>

			<div class="kse-tabs" role="tablist" aria-label="<?php esc_attr_e( 'Фильтры календаря', 'portal-theme' ); ?>">
				<button type="button" class="kse-tabs__btn" data-kse-tab="april" role="tab" aria-selected="false"><?php esc_html_e( 'Апрель', 'portal-theme' ); ?></button>
				<button type="button" class="kse-tabs__btn is-active" data-kse-tab="all" role="tab" aria-selected="true"><?php esc_html_e( 'Все форматы', 'portal-theme' ); ?></button>
				<button type="button" class="kse-tabs__btn" data-kse-tab="video" role="tab" aria-selected="false"><?php esc_html_e( 'Видеоконференция', 'portal-theme' ); ?></button>
				<button type="button" class="kse-tabs__btn" data-kse-tab="docs" role="tab" aria-selected="false"><?php esc_html_e( 'Подача документов', 'portal-theme' ); ?></button>
			</div>

			<div class="kse-layout">
				<div class="kse-layout__main">
					<section class="kse-calendar-card" aria-labelledby="kse-cal-heading">
						<div class="kse-calendar-nav">
							<button type="button" class="kse-cal-nav__btn" id="kse-prev-month" aria-label="<?php esc_attr_e( 'Предыдущий месяц', 'portal-theme' ); ?>">‹</button>
							<label class="kse-sr-only" for="kse-month-select"><?php esc_html_e( 'Месяц', 'portal-theme' ); ?></label>
							<select id="kse-month-select" class="kse-cal-nav__select">
								<?php foreach ( $month_names as $m_num => $m_label ) : ?>
									<option value="<?php echo esc_attr( (string) $m_num ); ?>"><?php echo esc_html( $m_label ); ?></option>
								<?php endforeach; ?>
							</select>
							<label class="kse-sr-only" for="kse-year-select"><?php esc_html_e( 'Год', 'portal-theme' ); ?></label>
							<select id="kse-year-select" class="kse-cal-nav__select">
								<?php
								$y_now = (int) current_time( 'Y' );
								for ( $yy = $y_now - 3; $yy <= $y_now + 7; $yy++ ) :
									?>
									<option value="<?php echo esc_attr( (string) $yy ); ?>"><?php echo esc_html( (string) $yy ); ?></option>
								<?php endfor; ?>
							</select>
							<button type="button" class="kse-cal-nav__btn" id="kse-next-month" aria-label="<?php esc_attr_e( 'Следующий месяц', 'portal-theme' ); ?>">›</button>
						</div>
						<h2 class="kse-calendar-card__title" id="kse-cal-heading"></h2>
						<p class="kse-cal-legend">
							<span class="kse-cal-legend__item">
								<span class="kse-cal-legend__dot kse-cal-legend__dot--green"></span>
								<?php esc_html_e( 'Видеоконференция', 'portal-theme' ); ?>
							</span>
							<span class="kse-cal-legend__sep">·</span>
							<span class="kse-cal-legend__item">
								<span class="kse-cal-legend__dot kse-cal-legend__dot--blue"></span>
								<?php esc_html_e( 'Подача документов', 'portal-theme' ); ?>
							</span>
						</p>
						<div class="kse-cal" role="grid" aria-label="<?php esc_attr_e( 'Календарь месяца', 'portal-theme' ); ?>">
							<div class="kse-cal__weekdays" aria-hidden="true">
								<span><?php esc_html_e( 'Пн', 'portal-theme' ); ?></span>
								<span><?php esc_html_e( 'Вт', 'portal-theme' ); ?></span>
								<span><?php esc_html_e( 'Ср', 'portal-theme' ); ?></span>
								<span><?php esc_html_e( 'Чт', 'portal-theme' ); ?></span>
								<span><?php esc_html_e( 'Пт', 'portal-theme' ); ?></span>
								<span><?php esc_html_e( 'Сб', 'portal-theme' ); ?></span>
								<span><?php esc_html_e( 'Вс', 'portal-theme' ); ?></span>
							</div>
							<div class="kse-cal__grid" id="kse-cal-grid"></div>
						</div>
					</section>

					<section class="kse-upcoming" aria-labelledby="kse-upcoming-title">
						<h2 class="kse-upcoming__title" id="kse-upcoming-title"><?php esc_html_e( 'События текущей недели', 'portal-theme' ); ?></h2>
						<ul class="kse-upcoming__list" id="kse-upcoming-list"></ul>
						<p class="kse-upcoming__empty" id="kse-upcoming-empty" hidden>
							<?php esc_html_e( 'На текущей неделе нет событий. Измените поиск или фильтр.', 'portal-theme' ); ?>
						</p>
					</section>
				</div>

				<aside class="kse-aside" aria-label="<?php esc_attr_e( 'Дополнительно', 'portal-theme' ); ?>">
					<section class="kse-widget">
						<h3 class="kse-widget__title"><?php esc_html_e( 'Задачи и напоминания', 'portal-theme' ); ?></h3>
						<ul class="kse-widget__list">
							<?php
							$tasks = array(
								array( 'dot' => 'yellow' ),
								array( 'dot' => 'green' ),
								array( 'dot' => 'yellow' ),
							);
							foreach ( $tasks as $t ) :
								?>
								<li>
									<div class="kse-task">
										<span class="kse-task__cal" aria-hidden="true">
											<img src="<?php echo esc_url( $theme_img . '/calendar.png' ); ?>" alt="">
										</span>
										<span class="kse-task__text"><?php esc_html_e( 'Название', 'portal-theme' ); ?></span>
										<span class="kse-task__dot kse-task__dot--<?php echo esc_attr( $t['dot'] ); ?>" aria-hidden="true"></span>
									</div>
								</li>
							<?php endforeach; ?>
						</ul>
					</section>

					<section class="kse-widget">
						<h3 class="kse-widget__title"><?php esc_html_e( 'Ссылки по темам', 'portal-theme' ); ?></h3>
						<ul class="kse-widget__list kse-widget__list--links">
							<?php for ( $l = 0; $l < 3; $l++ ) : ?>
								<li>
									<a href="#" class="kse-link-row">
										<span class="kse-link-row__icon" aria-hidden="true">
											<img src="<?php echo esc_url( $theme_img . '/link.png' ); ?>" alt="">
										</span>
										<span class="kse-link-row__text">
											<strong><?php esc_html_e( 'Название', 'portal-theme' ); ?></strong>
											<span class="kse-link-row__sub"><?php esc_html_e( 'Краткая информация', 'portal-theme' ); ?></span>
										</span>
										<span class="kse-link-row__chev" aria-hidden="true">›</span>
									</a>
								</li>
							<?php endfor; ?>
						</ul>
					</section>

					<section class="kse-widget">
						<h3 class="kse-widget__title"><?php esc_html_e( 'Необходимые каналы для связи', 'portal-theme' ); ?></h3>
						<ul class="kse-widget__list kse-widget__list--tg">
							<?php for ( $tg = 0; $tg < 3; $tg++ ) : ?>
								<li>
									<a href="https://t.me/belenergo" class="kse-tg-row" target="_blank" rel="noopener noreferrer">
										<span class="kse-tg-row__icon" aria-hidden="true"></span>
										<span class="kse-tg-row__label"><?php esc_html_e( 'Ссылка', 'portal-theme' ); ?></span>
									</a>
								</li>
							<?php endfor; ?>
						</ul>
					</section>
				</aside>
			</div>
		</div>

		<div class="kse-modal" id="kse-event-modal" hidden>
			<div class="kse-modal__backdrop" id="kse-event-backdrop"></div>
			<div class="kse-modal__dialog kse-modal__dialog--view" role="dialog" aria-modal="true" aria-labelledby="kse-event-modal-title">
				<h3 class="kse-modal__title" id="kse-event-modal-title"><?php esc_html_e( 'События', 'portal-theme' ); ?></h3>
				<p class="kse-modal__date" id="kse-event-modal-date"></p>
				<div class="kse-modal__view" id="kse-event-modal-body"></div>
				<div class="kse-modal__actions">
					<button type="button" class="kse-btn kse-btn--ghost" id="kse-event-close"><?php esc_html_e( 'Закрыть', 'portal-theme' ); ?></button>
				</div>
			</div>
		</div>
	</div>
</main>

<?php get_footer(); ?>
