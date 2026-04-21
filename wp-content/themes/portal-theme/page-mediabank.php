<?php
/**
 * Template Name: Медиабанк
 *
 * Материалы создаются в админке: «Медиабанк» в меню или «Добавить материалы» на странице.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$theme_img = get_template_directory_uri() . '/assets/img';

$mediabank_q = new WP_Query(
	array(
		'post_type'      => 'portal_mediabank',
		// publish — опубликовано; future — запланировано (иначе до даты публикации материал не попадёт в выборку).
		'post_status'    => array( 'publish', 'future' ),
		'posts_per_page' => -1,
		'orderby'        => 'date',
		'order'          => 'DESC',
	)
);

$mb_types = function_exists( 'portal_theme_mediabank_type_slugs' )
	? portal_theme_mediabank_type_slugs()
	: array( 'photo', 'video', 'infographic', 'logo' );

?>

<main class="portal-page">
	<?php get_template_part( 'template-parts/layout/sidebar' ); ?>

	<div class="portal-page__main">
		<?php get_template_part( 'template-parts/layout/site-header' ); ?>

		<div class="mediabank-page">
			<nav class="mediabank-breadcrumbs" aria-label="<?php esc_attr_e( 'Навигация', 'portal-theme' ); ?>">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Главная', 'portal-theme' ); ?></a>
				<span class="mediabank-breadcrumbs__sep">/</span>
				<span><?php esc_html_e( 'Медиабанк', 'portal-theme' ); ?></span>
			</nav>

			<header class="mediabank-hero">
				<div class="mediabank-hero__left">
					<div class="mediabank-hero__icon" aria-hidden="true">
						<svg width="36" height="36" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M4 16l4.5-6 3.5 4.5 2.5-3 4.5 6H4z" fill="currentColor" opacity="0.95"/>
							<circle cx="9" cy="8" r="1.5" fill="currentColor"/>
							<rect x="3" y="5" width="18" height="14" rx="2" stroke="currentColor" stroke-width="1.6" fill="none"/>
						</svg>
					</div>
					<div class="mediabank-hero__text">
						<h1 class="mediabank-hero__title"><?php esc_html_e( 'Медиабанк', 'portal-theme' ); ?></h1>
						<p class="mediabank-hero__subtitle">
							<?php esc_html_e( 'Единое хранилище фото, видео и графических материалов', 'portal-theme' ); ?>
						</p>
					</div>
				</div>
				<div class="mediabank-hero__actions">
					<?php if ( current_user_can( 'edit_posts' ) ) : ?>
						<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=portal_mediabank' ) ); ?>" class="portal-btn portal-btn--green">
							<?php esc_html_e( 'Добавить материалы', 'portal-theme' ); ?>
						</a>
					<?php else : ?>
						<a href="<?php echo esc_url( wp_login_url( get_permalink() ) ); ?>" class="portal-btn portal-btn--green">
							<?php esc_html_e( 'Войти, чтобы добавить материалы', 'portal-theme' ); ?>
						</a>
					<?php endif; ?>
				</div>
				<div class="mediabank-hero__illustration">
					<img
						src="<?php echo esc_url( $theme_img . '/calendar_main.png' ); ?>"
						alt=""
						loading="lazy"
					>
				</div>
			</header>

			<div class="mediabank-toolbar">
				<label class="mediabank-toolbar__search-wrap">
					<span class="mediabank-sr-only"><?php esc_html_e( 'Поиск по материалам', 'portal-theme' ); ?></span>
					<input type="search" class="mediabank-toolbar__search" id="mediabank-search" placeholder="<?php esc_attr_e( 'Поиск по материалам', 'portal-theme' ); ?>" autocomplete="off">
				</label>
				<label class="mediabank-toolbar__sort-wrap">
					<span class="mediabank-sr-only"><?php esc_html_e( 'Сортировка', 'portal-theme' ); ?></span>
					<select class="mediabank-toolbar__sort" id="mediabank-sort" aria-label="<?php esc_attr_e( 'По дате', 'portal-theme' ); ?>">
						<option value="date-desc"><?php esc_html_e( 'По дате', 'portal-theme' ); ?></option>
						<option value="date-asc"><?php esc_html_e( 'Сначала старые', 'portal-theme' ); ?></option>
						<option value="title-asc"><?php esc_html_e( 'По названию', 'portal-theme' ); ?></option>
					</select>
				</label>
			</div>

			<div class="mediabank-tabs" role="tablist" aria-label="<?php esc_attr_e( 'Тип материалов', 'portal-theme' ); ?>">
				<button type="button" class="mediabank-tabs__btn is-active" data-filter="all" role="tab" aria-selected="true"><?php esc_html_e( 'Все', 'portal-theme' ); ?></button>
				<button type="button" class="mediabank-tabs__btn" data-filter="photo" role="tab" aria-selected="false"><?php esc_html_e( 'Фото', 'portal-theme' ); ?></button>
				<button type="button" class="mediabank-tabs__btn" data-filter="video" role="tab" aria-selected="false"><?php esc_html_e( 'Видео', 'portal-theme' ); ?></button>
				<button type="button" class="mediabank-tabs__btn" data-filter="infographic" role="tab" aria-selected="false"><?php esc_html_e( 'Инфографика', 'portal-theme' ); ?></button>
				<button type="button" class="mediabank-tabs__btn" data-filter="logo" role="tab" aria-selected="false"><?php esc_html_e( 'Логотипы', 'portal-theme' ); ?></button>
			</div>

			<div class="mediabank-layout">
				<div class="mediabank-layout__main">
					<p class="mediabank-empty" id="mediabank-empty" hidden><?php esc_html_e( 'Материалов в этой категории пока нет.', 'portal-theme' ); ?></p>
					<div class="mediabank-grid" id="mediabank-grid">
						<?php
						if ( $mediabank_q->have_posts() ) :
							while ( $mediabank_q->have_posts() ) :
								$mediabank_q->the_post();
								$pid = get_the_ID();
								$type = get_post_meta( $pid, '_portal_mb_type', true );
								$type = is_string( $type ) && $type !== '' ? $type : 'photo';
								if ( ! in_array( $type, $mb_types, true ) ) {
									$type = 'photo';
								}
								$slides = function_exists( 'portal_theme_mediabank_get_slide_items' )
									? portal_theme_mediabank_get_slide_items( $pid )
									: array();
								$slide_count = count( $slides );
								$thumb_id    = function_exists( 'portal_theme_mediabank_resolve_thumbnail_id' )
									? portal_theme_mediabank_resolve_thumbnail_id( $pid )
									: (int) get_post_thumbnail_id( $pid );
								$img_url     = '';
								$link_url    = '';
								$show_play   = ( 'video' === $type );
								$skip_card   = false;

								if ( $slide_count >= 2 ) {
									$link_url = $slides[0]['full'];
									foreach ( $slides as $s ) {
										if ( ! $s['is_video'] ) {
											$img_url = $s['src'];
											break;
										}
									}
								} elseif ( 1 === $slide_count ) {
									$one      = $slides[0];
									$link_url = $one['full'];
									$show_play = $one['is_video'];
									if ( $one['is_video'] ) {
										$img_url = '';
									} else {
										$img_url = $one['src'];
									}
								} else {
									if ( ! $thumb_id ) {
										$skip_card = true;
									} else {
										$img_url = wp_get_attachment_image_url( $thumb_id, 'large' );
										if ( ! $img_url ) {
											$skip_card = true;
										} else {
											$link_url = function_exists( 'portal_theme_mediabank_card_link_url' )
												? portal_theme_mediabank_card_link_url( $pid )
												: '';
											if ( $link_url === '' ) {
												$skip_card = true;
											}
										}
									}
								}

								if ( $skip_card ) {
									continue;
								}

								$title     = get_the_title();
								$date_disp = get_the_date( 'd.m.Y' );
								$sort_ts   = (int) get_post_time( 'U', true );
								$raw_title = wp_strip_all_tags( $title );
								?>
							<article
								class="mediabank-card"
								data-type="<?php echo esc_attr( $type ); ?>"
								data-title="<?php echo esc_attr( $raw_title ); ?>"
								data-date="<?php echo esc_attr( $date_disp ); ?>"
								data-sort="<?php echo esc_attr( (string) $sort_ts ); ?>"
							>
								<?php if ( $slide_count >= 2 ) : ?>
									<div class="mediabank-card__media mediabank-card__media--carousel">
										<div class="mediabank-carousel" data-mb-carousel tabindex="0" aria-roledescription="carousel" aria-label="<?php echo esc_attr( $raw_title ); ?>">
											<div class="mediabank-carousel__counter" aria-live="polite">
												<span class="mediabank-carousel__counter-current">1</span>/<span class="mediabank-carousel__counter-total"><?php echo esc_html( (string) $slide_count ); ?></span>
											</div>
											<button type="button" class="mediabank-carousel__arrow mediabank-carousel__prev" aria-label="<?php esc_attr_e( 'Предыдущий слайд', 'portal-theme' ); ?>">‹</button>
											<button type="button" class="mediabank-carousel__arrow mediabank-carousel__next" aria-label="<?php esc_attr_e( 'Следующий слайд', 'portal-theme' ); ?>">›</button>
											<div class="mediabank-carousel__viewport">
												<div class="mediabank-carousel__track">
													<?php
													foreach ( $slides as $si => $s ) :
														$lazy = $si > 0 ? 'lazy' : 'eager';
														?>
														<div class="mediabank-carousel__slide" data-slide-index="<?php echo esc_attr( (string) $si ); ?>">
															<?php if ( $s['is_video'] ) : ?>
																<video class="mediabank-carousel__video" src="<?php echo esc_url( $s['full'] ); ?>" controls playsinline preload="metadata"<?php echo $si > 0 ? ' tabindex="-1"' : ''; ?>></video>
															<?php else : ?>
																<a href="<?php echo esc_url( $s['full'] ); ?>" class="mediabank-carousel__img-link" target="_blank" rel="noopener noreferrer">
																	<img src="<?php echo esc_url( $s['src'] ); ?>" alt="<?php echo esc_attr( $raw_title ); ?>" loading="<?php echo esc_attr( $lazy ); ?>">
																</a>
															<?php endif; ?>
														</div>
														<?php
													endforeach;
													?>
												</div>
											</div>
											<div class="mediabank-carousel__dots" role="tablist" aria-label="<?php esc_attr_e( 'Слайды', 'portal-theme' ); ?>">
												<?php
												for ( $di = 0; $di < $slide_count; $di++ ) :
													?>
													<button type="button" class="mediabank-carousel__dot<?php echo 0 === $di ? ' is-active' : ''; ?>" role="tab" aria-selected="<?php echo 0 === $di ? 'true' : 'false'; ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: %d: slide number */ __( 'Слайд %d', 'portal-theme' ), $di + 1 ) ); ?>" data-slide-to="<?php echo esc_attr( (string) $di ); ?>"></button>
													<?php
												endfor;
												?>
											</div>
										</div>
									</div>
								<?php else : ?>
									<a href="<?php echo esc_url( $link_url ); ?>" class="mediabank-card__media" target="_blank" rel="noopener noreferrer">
										<?php if ( $img_url !== '' ) : ?>
											<img src="<?php echo esc_url( $img_url ); ?>" alt="<?php echo esc_attr( $raw_title ); ?>" loading="lazy">
										<?php else : ?>
											<span class="mediabank-card__video-placeholder" aria-hidden="true"></span>
										<?php endif; ?>
										<?php if ( $show_play ) : ?>
											<span class="mediabank-card__play" aria-hidden="true"></span>
										<?php endif; ?>
									</a>
								<?php endif; ?>
								<div class="mediabank-card__meta">
									<span class="mediabank-card__title"><?php echo esc_html( $title ); ?></span>
									<time class="mediabank-card__date" datetime="<?php echo esc_attr( get_the_date( 'Y-m-d' ) ); ?>"><?php echo esc_html( $date_disp ); ?></time>
								</div>
							</article>
								<?php
							endwhile;
							wp_reset_postdata();
						endif;
						?>
					</div>
				</div>

				<aside class="mediabank-aside">
					<?php for ( $b = 0; $b < 2; $b++ ) : ?>
						<section class="mediabank-collections">
							<h3 class="mediabank-collections__title"><?php esc_html_e( 'Полезные подборки', 'portal-theme' ); ?></h3>
							<ul class="mediabank-collections__list">
								<?php for ( $i = 0; $i < 2; $i++ ) : ?>
									<li>
										<a href="#" class="mediabank-collections__item">
											<span class="mediabank-collections__icon" aria-hidden="true"></span>
											<span class="mediabank-collections__text">
												<strong><?php esc_html_e( 'Название', 'portal-theme' ); ?></strong>
												<span class="mediabank-collections__sub"><?php esc_html_e( 'Информация', 'portal-theme' ); ?></span>
											</span>
											<span class="mediabank-collections__arrow" aria-hidden="true">›</span>
										</a>
									</li>
								<?php endfor; ?>
							</ul>
						</section>
					<?php endfor; ?>
				</aside>
			</div>
		</div>
	</div>
</main>

<?php get_footer(); ?>
