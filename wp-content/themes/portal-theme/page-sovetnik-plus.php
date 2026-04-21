<?php
/**
 * Template Name: Советник+
 *
 * Материалы: меню «Советник+» в админке WordPress.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$sov_main = new WP_Query(
	array(
		'post_type'      => 'portal_sovetnik',
		'post_status'    => array( 'publish', 'future' ),
		'posts_per_page' => -1,
		'orderby'        => 'date',
		'order'          => 'DESC',
	)
);

$sov_popular_q = new WP_Query(
	array(
		'post_type'      => 'portal_sovetnik',
		'post_status'    => array( 'publish', 'future' ),
		'posts_per_page' => 5,
		'orderby'        => 'date',
		'order'          => 'DESC',
		'meta_query'     => array(
			array(
				'key'   => '_portal_sov_highlight',
				'value' => '1',
			),
		),
	)
);

$sov_new_q = new WP_Query(
	array(
		'post_type'      => 'portal_sovetnik',
		'post_status'    => array( 'publish', 'future' ),
		'posts_per_page' => 5,
		'orderby'        => 'date',
		'order'          => 'DESC',
	)
);

$theme_img = get_template_directory_uri() . '/assets/img';

get_header();
?>

<main class="portal-page">
	<?php get_template_part( 'template-parts/layout/sidebar' ); ?>

	<div class="portal-page__main">
		<?php get_template_part( 'template-parts/layout/site-header' ); ?>

		<div class="sov-page">
			<nav class="sov-breadcrumbs" aria-label="<?php esc_attr_e( 'Навигация', 'portal-theme' ); ?>">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Главная', 'portal-theme' ); ?></a>
				<span class="sov-breadcrumbs__sep">/</span>
				<span><?php esc_html_e( 'Советник+', 'portal-theme' ); ?></span>
			</nav>

			<header class="sov-hero">
				<div class="sov-hero__left">
					<div class="sov-hero__icon-wrap" aria-hidden="true">
						<img
							class="sov-hero__icon"
							src="<?php echo esc_url( $theme_img . '/Advisor.png' ); ?>"
							alt=""
							loading="eager"
							width="120"
							height="120"
						>
					</div>
					<div class="sov-hero__text">
						<h1 class="sov-hero__title"><?php esc_html_e( 'Советник+', 'portal-theme' ); ?></h1>
						<p class="sov-hero__subtitle">
							<?php esc_html_e( 'Методические материалы, шаблоны и подсказки для ежедневной работы идеолога и специалиста по связям с общественностью', 'portal-theme' ); ?>
						</p>
					</div>
				</div>
			</header>

			<div class="sov-toolbar">
				<label class="sov-toolbar__search-wrap">
					<span class="sov-sr-only"><?php esc_html_e( 'Поиск по материалам', 'portal-theme' ); ?></span>
					<span class="sov-toolbar__search-inner">
						<input type="search" class="sov-toolbar__search" id="sov-search" placeholder="<?php esc_attr_e( 'Поиск по материалам', 'portal-theme' ); ?>" autocomplete="off">
						<span class="sov-toolbar__search-icon" aria-hidden="true"></span>
					</span>
				</label>
				<label class="sov-toolbar__sort-wrap">
					<span class="sov-sr-only"><?php esc_html_e( 'Сортировка', 'portal-theme' ); ?></span>
					<select class="sov-toolbar__sort" id="sov-sort" aria-label="<?php esc_attr_e( 'Сортировка', 'portal-theme' ); ?>">
						<option value="date-desc"><?php esc_html_e( 'По дате', 'portal-theme' ); ?></option>
						<option value="date-asc"><?php esc_html_e( 'Сначала старые', 'portal-theme' ); ?></option>
						<option value="title-asc"><?php esc_html_e( 'По названию', 'portal-theme' ); ?></option>
					</select>
				</label>
			</div>

			<div class="sov-tabs" role="tablist" aria-label="<?php esc_attr_e( 'Категории материалов', 'portal-theme' ); ?>">
				<button type="button" class="sov-tabs__btn is-active" data-filter="all" role="tab" aria-selected="true"><?php esc_html_e( 'Все', 'portal-theme' ); ?></button>
				<button type="button" class="sov-tabs__btn" data-filter="social" role="tab" aria-selected="false"><?php esc_html_e( 'Работа в соцсетях', 'portal-theme' ); ?></button>
				<button type="button" class="sov-tabs__btn" data-filter="media" role="tab" aria-selected="false"><?php esc_html_e( 'СМИ', 'portal-theme' ); ?></button>
				<button type="button" class="sov-tabs__btn" data-filter="events" role="tab" aria-selected="false"><?php esc_html_e( 'Мероприятия', 'portal-theme' ); ?></button>
				<button type="button" class="sov-tabs__btn" data-filter="templates" role="tab" aria-selected="false"><?php esc_html_e( 'Шаблоны', 'portal-theme' ); ?></button>
			</div>

			<div class="sov-layout">
				<div class="sov-layout__main">
					<p class="sov-empty" id="sov-empty" hidden><?php esc_html_e( 'Материалов в этой категории пока нет.', 'portal-theme' ); ?></p>
					<div class="sov-list" id="sov-list">
						<?php
						if ( $sov_main->have_posts() ) :
							while ( $sov_main->have_posts() ) :
								$sov_main->the_post();
								$item = function_exists( 'portal_theme_sovetnik_post_to_item_array' )
									? portal_theme_sovetnik_post_to_item_array( get_the_ID() )
									: null;
								if ( ! $item || $item['title'] === '' ) {
									continue;
								}
								echo portal_theme_sovetnik_render_card( $item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							endwhile;
							wp_reset_postdata();
						endif;
						?>
					</div>
				</div>

				<aside class="sov-aside">
					<?php if ( $sov_popular_q->have_posts() ) : ?>
						<section class="sov-widget">
							<h3 class="sov-widget__title"><?php esc_html_e( 'Популярные материалы', 'portal-theme' ); ?></h3>
							<ul class="sov-widget__list">
								<?php
								while ( $sov_popular_q->have_posts() ) :
									$sov_popular_q->the_post();
									$pitem = function_exists( 'portal_theme_sovetnik_post_to_item_array' )
										? portal_theme_sovetnik_post_to_item_array( get_the_ID() )
										: null;
									if ( ! $pitem || $pitem['title'] === '' ) {
										continue;
									}
									?>
									<li>
										<?php echo portal_theme_sovetnik_render_popular_button( $pitem ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
									</li>
								<?php endwhile; ?>
							</ul>
						</section>
						<?php
						wp_reset_postdata();
					endif;
					?>

					<?php if ( $sov_new_q->have_posts() ) : ?>
						<section class="sov-widget sov-widget--new">
							<h3 class="sov-widget__title"><?php esc_html_e( 'Новые поступления', 'portal-theme' ); ?></h3>
							<ul class="sov-widget__list sov-widget__list--new">
								<?php
								while ( $sov_new_q->have_posts() ) :
									$sov_new_q->the_post();
									$nitem = function_exists( 'portal_theme_sovetnik_post_to_item_array' )
										? portal_theme_sovetnik_post_to_item_array( get_the_ID() )
										: null;
									if ( ! $nitem || $nitem['title'] === '' ) {
										continue;
									}
									?>
									<li>
										<?php echo portal_theme_sovetnik_render_new_button( $nitem ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
									</li>
								<?php endwhile; ?>
							</ul>
						</section>
						<?php
						wp_reset_postdata();
					endif;
					?>
				</aside>
			</div>
		</div>
	</div>
</main>

	<dialog class="sov-view-modal" id="sov-view-modal" aria-labelledby="sov-view-title">
		<div class="sov-view-modal__inner">
			<button type="button" class="sov-view-modal__close" id="sov-view-close" aria-label="<?php esc_attr_e( 'Закрыть', 'portal-theme' ); ?>">×</button>
			<h2 id="sov-view-title" class="sov-view-modal__title"></h2>
			<div id="sov-view-reading-wrap" class="sov-view-modal__reading-wrap" hidden>
				<p id="sov-view-reading-label" class="sov-view-modal__reading-label"><?php esc_html_e( 'Описание', 'portal-theme' ); ?></p>
				<div id="sov-view-reading" class="sov-view-modal__reading"></div>
			</div>
			<div id="sov-view-preview-wrap" class="sov-view-modal__preview-wrap" hidden>
				<iframe id="sov-view-frame" class="sov-view-modal__frame" title="" src="about:blank"></iframe>
				<img id="sov-view-img" class="sov-view-modal__media" alt="" width="0" height="0" hidden decoding="async">
				<video id="sov-view-vid" class="sov-view-modal__media" controls playsinline preload="metadata" hidden></video>
				<p id="sov-view-fallback" class="sov-view-modal__fallback" hidden></p>
			</div>
			<div class="sov-view-modal__actions">
				<a href="#" class="sov-btn sov-btn--green" id="sov-view-download" download hidden><?php esc_html_e( 'Скачать файл', 'portal-theme' ); ?></a>
			</div>
		</div>
	</dialog>

<?php get_footer(); ?>
