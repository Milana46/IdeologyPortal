<?php
/**
 * Template Name: Библиотека практик
 *
 * Материалы создаются в админке: тип записей «Библиотека практик».
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$theme_img = get_template_directory_uri() . '/assets/img';

$home_host = wp_parse_url( home_url(), PHP_URL_HOST );
$home_host = is_string( $home_host ) ? strtolower( $home_host ) : '';
$is_local  = $home_host && in_array( $home_host, array( 'localhost', '127.0.0.1', '::1' ), true );
if ( ! $is_local && $home_host ) {
	$hl = strlen( $home_host );
	if ( $hl > 6 && substr( $home_host, -6 ) === '.local' ) {
		$is_local = true;
	} elseif ( $hl > 5 && substr( $home_host, -5 ) === '.test' ) {
		$is_local = true;
	}
}

$bp_q = new WP_Query(
	array(
		'post_type'      => 'portal_bp_material',
		'post_status'    => array( 'publish', 'future' ),
		'posts_per_page' => -1,
		'orderby'        => array(
			'menu_order' => 'ASC',
			'date'       => 'DESC',
		),
	)
);

$bp_popular_q = new WP_Query(
	array(
		'post_type'      => 'portal_bp_material',
		'post_status'    => array( 'publish', 'future' ),
		'posts_per_page' => -1,
		'meta_key'       => '_portal_bp_in_popular',
		'meta_value'     => '1',
		'orderby'        => array(
			'menu_order' => 'ASC',
			'date'       => 'DESC',
		),
	)
);

$bp_new_q = new WP_Query(
	array(
		'post_type'      => 'portal_bp_material',
		'post_status'    => array( 'publish', 'future' ),
		'posts_per_page' => 24,
		'orderby'        => 'date',
		'order'          => 'DESC',
	)
);

$panel_id = 'bp-panel-main';
?>

<main class="portal-page">
	<?php get_template_part( 'template-parts/layout/sidebar' ); ?>

	<div class="portal-page__main">
		<?php get_template_part( 'template-parts/layout/site-header' ); ?>

		<div class="bp-page">
			<nav class="bp-breadcrumbs" aria-label="<?php esc_attr_e( 'Навигация', 'portal-theme' ); ?>">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Главная', 'portal-theme' ); ?></a>
				<span class="bp-breadcrumbs__sep">/</span>
				<span><?php esc_html_e( 'Библиотека практик', 'portal-theme' ); ?></span>
			</nav>

			<header class="bp-hero">
				<div class="bp-hero__left">
					<div class="bp-hero__icon-wrap" aria-hidden="true">
						<img
							class="bp-hero__icon"
							src="<?php echo esc_url( $theme_img . '/icon_book.png' ); ?>"
							alt=""
							loading="eager"
							width="120"
							height="120"
						>
					</div>
					<div class="bp-hero__text">
						<h1 class="bp-hero__title"><?php esc_html_e( 'Библиотека практик', 'portal-theme' ); ?></h1>
						<p class="bp-hero__subtitle">
							<?php esc_html_e( 'Структурированная база лучших практик, шаблонов, гайдлайнов и кейсов', 'portal-theme' ); ?>
						</p>
					</div>
				</div>
			</header>

			<div class="bp-toolbar">
				<label class="bp-toolbar__search-wrap">
					<span class="bp-sr-only"><?php esc_html_e( 'Поиск по материалам', 'portal-theme' ); ?></span>
					<span class="bp-toolbar__search-inner">
						<input type="search" class="bp-toolbar__search" id="bp-search" placeholder="<?php esc_attr_e( 'Поиск по материалам', 'portal-theme' ); ?>" autocomplete="off">
						<span class="bp-toolbar__search-icon" aria-hidden="true"></span>
					</span>
				</label>
				<label class="bp-toolbar__sort-wrap">
					<span class="bp-sr-only"><?php esc_html_e( 'Сортировка', 'portal-theme' ); ?></span>
					<select class="bp-toolbar__sort" id="bp-sort" aria-label="<?php esc_attr_e( 'По дате', 'portal-theme' ); ?>">
						<option value="date-desc"><?php esc_html_e( 'По дате', 'portal-theme' ); ?></option>
						<option value="date-asc"><?php esc_html_e( 'Сначала старые', 'portal-theme' ); ?></option>
						<option value="title-asc"><?php esc_html_e( 'По названию', 'portal-theme' ); ?></option>
					</select>
				</label>
			</div>

			<p class="bp-legend" aria-hidden="true">
				<span class="bp-legend__item"><span class="bp-type-dot bp-type-dot--social"></span><?php esc_html_e( 'Работа в соцсетях', 'portal-theme' ); ?></span>
				<span class="bp-legend__sep">·</span>
				<span class="bp-legend__item"><span class="bp-type-dot bp-type-dot--smi"></span><?php esc_html_e( 'СМИ', 'portal-theme' ); ?></span>
				<span class="bp-legend__sep">·</span>
				<span class="bp-legend__item"><span class="bp-type-dot bp-type-dot--events"></span><?php esc_html_e( 'Мероприятия', 'portal-theme' ); ?></span>
				<span class="bp-legend__sep">·</span>
				<span class="bp-legend__item"><span class="bp-type-dot bp-type-dot--templates"></span><?php esc_html_e( 'Шаблоны', 'portal-theme' ); ?></span>
			</p>

			<p class="bp-type-filter-heading" id="bp-type-filter-label"><?php esc_html_e( 'Тип материала', 'portal-theme' ); ?></p>

			<div class="kse-tabs" id="bp-category-tabs" role="tablist" aria-labelledby="bp-type-filter-label">
				<button type="button" class="kse-tabs__btn is-active" data-tab="all" role="tab" aria-selected="true" aria-controls="<?php echo esc_attr( $panel_id ); ?>" id="bp-tab-all"><?php esc_html_e( 'Все', 'portal-theme' ); ?></button>
				<button type="button" class="kse-tabs__btn" data-tab="social" role="tab" aria-selected="false" aria-controls="<?php echo esc_attr( $panel_id ); ?>" id="bp-tab-social"><?php esc_html_e( 'Работа в соцсетях', 'portal-theme' ); ?></button>
				<button type="button" class="kse-tabs__btn" data-tab="smi" role="tab" aria-selected="false" aria-controls="<?php echo esc_attr( $panel_id ); ?>" id="bp-tab-smi"><?php esc_html_e( 'СМИ', 'portal-theme' ); ?></button>
				<button type="button" class="kse-tabs__btn" data-tab="events" role="tab" aria-selected="false" aria-controls="<?php echo esc_attr( $panel_id ); ?>" id="bp-tab-events"><?php esc_html_e( 'Мероприятия', 'portal-theme' ); ?></button>
				<button type="button" class="kse-tabs__btn" data-tab="templates" role="tab" aria-selected="false" aria-controls="<?php echo esc_attr( $panel_id ); ?>" id="bp-tab-templates"><?php esc_html_e( 'Шаблоны', 'portal-theme' ); ?></button>
			</div>

			<div class="bp-layout">
				<div class="bp-layout__main">
					<div class="bp-panels">
						<div class="bp-tab-panel is-active" id="<?php echo esc_attr( $panel_id ); ?>" role="tabpanel" aria-labelledby="bp-tab-all" data-tab-panel="main">
							<div class="bp-list" id="bp-list">
								<?php
								$bp_has_cards = false;
								if ( $bp_q->have_posts() ) {
									while ( $bp_q->have_posts() ) {
										$bp_q->the_post();
										$html = portal_theme_bp_render_material_card_from_post( get_post(), $theme_img, $is_local );
										if ( $html !== '' ) {
											echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
											$bp_has_cards = true;
										}
									}
									wp_reset_postdata();
								}
								if ( ! $bp_has_cards ) {
									echo '<p class="bp-list-empty">' . esc_html__( 'Материалов пока нет. Добавьте записи в разделе «Библиотека практик» в панели управления сайта.', 'portal-theme' ) . '</p>';
								}
								?>
							</div>
						</div>
					</div>
				</div>

				<aside class="bp-aside">
					<section class="bp-widget">
						<h3 class="bp-widget__title"><?php esc_html_e( 'Популярные материалы', 'portal-theme' ); ?></h3>
						<ul class="bp-widget__list">
							<?php
							if ( $bp_popular_q->have_posts() ) :
								while ( $bp_popular_q->have_posts() ) :
									$bp_popular_q->the_post();
									$fid = (int) get_post_meta( get_the_ID(), '_portal_bp_file_id', true );
									if ( $fid <= 0 ) {
										continue;
									}
									$file_url = wp_get_attachment_url( $fid );
									if ( ! $file_url ) {
										continue;
									}
									$sub = get_the_excerpt();
									if ( ! is_string( $sub ) ) {
										$sub = '';
									}
									?>
									<li>
										<a href="<?php echo esc_url( $file_url ); ?>" class="bp-popular" download>
											<span class="bp-popular__icon" aria-hidden="true"></span>
											<span class="bp-popular__text">
												<strong><?php the_title(); ?></strong>
												<?php if ( $sub !== '' ) : ?>
													<span class="bp-popular__sub"><?php echo esc_html( wp_strip_all_tags( $sub ) ); ?></span>
												<?php endif; ?>
											</span>
										</a>
									</li>
									<?php
								endwhile;
								wp_reset_postdata();
							else :
								?>
								<li><span class="bp-widget__muted"><?php esc_html_e( 'Пока нет записей с пометкой «В популярные».', 'portal-theme' ); ?></span></li>
							<?php endif; ?>
						</ul>
					</section>

					<section class="bp-widget bp-widget--new">
						<h3 class="bp-widget__title"><?php esc_html_e( 'Новые поступления', 'portal-theme' ); ?></h3>
						<ul class="bp-widget__list bp-widget__list--new">
							<?php
							$bp_new_shown = 0;
							if ( $bp_new_q->have_posts() ) :
								while ( $bp_new_q->have_posts() ) :
									$bp_new_q->the_post();
									$fid = (int) get_post_meta( get_the_ID(), '_portal_bp_file_id', true );
									if ( $fid <= 0 ) {
										continue;
									}
									$file_url = wp_get_attachment_url( $fid );
									if ( ! $file_url ) {
										continue;
									}
									$bp_new_shown++;
									if ( $bp_new_shown > 3 ) {
										break;
									}
									$sub = get_the_excerpt();
									if ( ! is_string( $sub ) ) {
										$sub = '';
									}
									?>
									<li>
										<a href="<?php echo esc_url( $file_url ); ?>" class="bp-new-item" download>
											<span class="bp-new-item__doc" aria-hidden="true"></span>
											<span class="bp-new-item__text">
												<strong><?php the_title(); ?></strong>
												<?php if ( $sub !== '' ) : ?>
													<span class="bp-new-item__sub"><?php echo esc_html( wp_strip_all_tags( $sub ) ); ?></span>
												<?php endif; ?>
											</span>
											<span class="bp-new-item__dot" aria-hidden="true"></span>
										</a>
									</li>
									<?php
								endwhile;
								wp_reset_postdata();
							endif;
							if ( $bp_new_shown === 0 ) :
								?>
								<li><span class="bp-widget__muted"><?php esc_html_e( 'Новых материалов пока нет.', 'portal-theme' ); ?></span></li>
							<?php endif; ?>
						</ul>
					</section>
				</aside>
			</div>
		</div>
	</div>

	<dialog class="bp-doc-modal" id="bp-doc-modal" aria-labelledby="bp-doc-modal-title" data-bp-is-local="<?php echo $is_local ? '1' : '0'; ?>">
		<div class="bp-doc-modal__inner">
			<button type="button" class="bp-doc-modal__close" aria-label="<?php esc_attr_e( 'Закрыть', 'portal-theme' ); ?>">×</button>
			<h2 id="bp-doc-modal-title" class="bp-doc-modal__heading"></h2>

			<div class="bp-doc-modal__viewer">
				<iframe id="bp-doc-frame" class="bp-doc-modal__frame" title="" src="about:blank" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
				<img id="bp-media-img" class="bp-doc-modal__media bp-doc-modal__media--img" alt="" width="0" height="0" hidden decoding="async">
				<video id="bp-media-vid" class="bp-doc-modal__media bp-doc-modal__media--vid" controls playsinline preload="metadata" hidden></video>
				<div id="bp-doc-fallback" class="bp-doc-modal__fallback" hidden>
					<p id="bp-doc-fallback-local" class="<?php echo $is_local ? '' : 'bp-hidden'; ?>">
						<?php esc_html_e( 'На локальном адресе (localhost) встроенный просмотр Word-файла недоступен. Положите в папку темы PDF с тем же именем и расширением .pdf — здесь отобразится содержимое. Либо откройте сайт по публичному адресу для просмотра через Microsoft Office Online.', 'portal-theme' ); ?>
					</p>
					<p id="bp-doc-fallback-generic" class="<?php echo $is_local ? 'bp-hidden' : ''; ?>">
						<?php esc_html_e( 'Предпросмотр этого файла в браузере сейчас недоступен. Добавьте PDF-копию документа в каталог темы (то же имя, расширение .pdf) или скачайте исходный файл ниже.', 'portal-theme' ); ?>
					</p>
				</div>
			</div>

			<p class="bp-doc-modal__filename"></p>

			<div class="bp-doc-modal__footer bp-doc-modal__footer--split">
				<a href="#" class="bp-btn bp-btn--outline bp-doc-modal__download" id="bp-doc-download" download><?php esc_html_e( 'Скачать', 'portal-theme' ); ?></a>
			</div>
		</div>
	</dialog>
</main>

<?php get_footer(); ?>
