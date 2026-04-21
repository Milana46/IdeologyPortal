<?php
/**
 * Template Name: Основы идеолога
 *
 * Материалы ведутся в админке: меню «Основы идеолога».
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$ideology_q = new WP_Query(
	array(
		'post_type'      => 'portal_ideology',
		'post_status'    => array( 'publish', 'future' ),
		'posts_per_page' => -1,
		'orderby'        => 'date',
		'order'          => 'DESC',
	)
);

get_header();
?>

<main class="portal-page">
	<?php get_template_part( 'template-parts/layout/sidebar' ); ?>

	<div class="portal-page__main">
		<?php get_template_part( 'template-parts/layout/site-header' ); ?>

		<div class="ideology-page">
			<div class="ideology-breadcrumbs">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>">
					<?php esc_html_e( 'Главная', 'portal-theme' ); ?>
				</a>

				<span>
					/
				</span>

				<span>
					<?php esc_html_e( 'Основы идеолога', 'portal-theme' ); ?>
				</span>
			</div>

			<section class="ideology-hero">
				<div class="ideology-hero__left">
					<div class="ideology-hero__icon">
						<img
							src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/icon_book.png' ); ?>"
							alt="<?php esc_attr_e( 'Основы идеолога', 'portal-theme' ); ?>"
						>
					</div>

					<div class="ideology-hero__title-wrap">
						<h1>
							<?php esc_html_e( 'Основы идеолога', 'portal-theme' ); ?>
						</h1>
					</div>
				</div>
			</section>

			<div class="ideology-search">
				<label class="screen-reader-text" for="ideology-search"><?php esc_html_e( 'Поиск по материалам', 'portal-theme' ); ?></label>
				<input
					type="search"
					id="ideology-search"
					placeholder="<?php esc_attr_e( 'Поиск по материалам', 'portal-theme' ); ?>"
					autocomplete="off"
				>
			</div>

			<section class="ideology-tabs" aria-label="<?php esc_attr_e( 'Тип материала', 'portal-theme' ); ?>">
				<button type="button" class="ideology-tabs__item is-active" data-ideology-tab="all"><?php esc_html_e( 'Все', 'portal-theme' ); ?></button>
				<button type="button" class="ideology-tabs__item" data-ideology-tab="symbolika"><?php esc_html_e( 'Государственная символика', 'portal-theme' ); ?></button>
				<button type="button" class="ideology-tabs__item" data-ideology-tab="akty"><?php esc_html_e( 'Акты', 'portal-theme' ); ?></button>
				<button type="button" class="ideology-tabs__item" data-ideology-tab="pasport"><?php esc_html_e( 'Социальный паспорт предприятия', 'portal-theme' ); ?></button>
			</section>

			<div class="ideology-layout">
				<div class="ideology-content">
					<div class="ideology-cards" id="ideology-cards">
						<?php
						if ( $ideology_q->have_posts() ) :
							while ( $ideology_q->have_posts() ) :
								$ideology_q->the_post();
								$item = function_exists( 'portal_theme_ideology_post_to_item_array' )
									? portal_theme_ideology_post_to_item_array( get_the_ID() )
									: null;
								if ( ! $item || $item['title'] === '' ) {
									continue;
								}
								echo portal_theme_ideology_render_card( $item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							endwhile;
							wp_reset_postdata();
						endif;
						?>
					</div>
				</div>

				<aside class="ideology-sidebar">
					<section class="ideology-widget">
						<h3>
							<?php esc_html_e( 'Полезные ссылки', 'portal-theme' ); ?>
						</h3>

						<a href="https://pravo.by/" class="ideology-link-item">
							<span class="ideology-link-item__left">
								<img
									src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/pravo_by.png' ); ?>"
									alt="Право.by"
								>

								<span>
									Право.by
								</span>
							</span>

							<span class="ideology-link-item__arrow">
								>
							</span>
						</a>

						<a href="https://t.me/pul_1" class="ideology-link-item">
							<span class="ideology-link-item__left">
								<img
									src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/pul.png' ); ?>"
									alt="Пул Первого"
								>
								<span>
									Пул Первого
								</span>
							</span>

							<span class="ideology-link-item__arrow">
								>
							</span>
						</a>

						<a href="https://belta.by/" class="ideology-link-item">
							<span class="ideology-link-item__left">
								<img
									src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/belta.png' ); ?>"
									alt="Belta"
								>
								<span>
									Belta.by
								</span>
							</span>

							<span class="ideology-link-item__arrow">
								>
							</span>
						</a>
					</section>

					<section class="ideology-poster">
						<img
							src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/woman-belarus.png' ); ?>"
							alt="<?php esc_attr_e( 'Постер', 'portal-theme' ); ?>"
						>

						<img
							src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/work5.png' ); ?>"
							alt="<?php esc_attr_e( 'Постер', 'portal-theme' ); ?>"
						>
					</section>
				</aside>
			</div>
		</div>
	</div>
</main>

	<dialog class="ideology-view-modal" id="ideology-view-modal" aria-labelledby="ideology-view-title">
		<div class="ideology-view-modal__inner">
			<button type="button" class="ideology-view-modal__close" id="ideology-view-close" aria-label="<?php esc_attr_e( 'Закрыть', 'portal-theme' ); ?>">×</button>
			<h2 id="ideology-view-title" class="ideology-view-modal__title"></h2>
			<div id="ideology-view-reading-wrap" class="ideology-view-modal__reading-wrap" hidden>
				<p id="ideology-view-reading-label" class="ideology-view-modal__reading-label"><?php esc_html_e( 'Описание', 'portal-theme' ); ?></p>
				<div id="ideology-view-reading" class="ideology-view-modal__reading"></div>
			</div>
			<div id="ideology-view-preview-wrap" class="ideology-view-modal__preview-wrap" hidden>
				<iframe id="ideology-view-frame" class="ideology-view-modal__frame" title="" src="about:blank"></iframe>
				<img id="ideology-view-img" class="ideology-view-modal__media" alt="" width="0" height="0" hidden decoding="async">
				<video id="ideology-view-vid" class="ideology-view-modal__media" controls playsinline preload="metadata" hidden></video>
				<p id="ideology-view-fallback" class="ideology-view-modal__fallback" hidden></p>
			</div>
			<div class="ideology-view-modal__actions">
				<a href="#" class="portal-btn portal-btn--green" id="ideology-view-download" download hidden><?php esc_html_e( 'Скачать файл', 'portal-theme' ); ?></a>
			</div>
		</div>
	</dialog>

<?php get_footer(); ?>
