<?php
/**
 * Template Name: Аналитика и эффективность
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * Подключение CSS/JS здесь гарантирует загрузку: не зависит от is_page_template в момент глобального enqueue.
 */
if ( function_exists( 'portal_theme_enqueue_analytics_page_assets' ) && ! has_action( 'wp_enqueue_scripts', 'portal_theme_enqueue_analytics_page_assets' ) ) {
	add_action( 'wp_enqueue_scripts', 'portal_theme_enqueue_analytics_page_assets', 20 );
}

get_header();

$theme_img = get_template_directory_uri() . '/assets/img';

$tasks_query_args = array(
	'post_type'      => 'portal_at_task',
	'post_status'    => array( 'publish', 'future' ),
	'posts_per_page' => -1,
	'orderby'        => 'date',
	'order'          => 'DESC',
);
$analytics_section_id = function_exists( 'portal_theme_analytics_get_page_id' ) ? portal_theme_analytics_get_page_id() : 0;
if ( $analytics_section_id && function_exists( 'portal_theme_analytics_tasks_meta_query_for_section' ) ) {
	$mq = portal_theme_analytics_tasks_meta_query_for_section( $analytics_section_id );
	if ( $mq !== array() ) {
		$tasks_query_args['meta_query'] = $mq;
	}
}
$tasks_q = new WP_Query( $tasks_query_args );

$analytics_task_ids = array();

$analytics_section = $analytics_section_id ? get_post( $analytics_section_id ) : null;
$hero_title           = ( $analytics_section instanceof WP_Post && $analytics_section->post_title !== '' )
	? $analytics_section->post_title
	: __( 'Аналитика и эффективность', 'portal-theme' );
$hero_sub = '';
if ( $analytics_section instanceof WP_Post ) {
	$hero_sub = trim( (string) $analytics_section->post_excerpt );
}
if ( $hero_sub === '' ) {
	$hero_sub = __( 'Центр управления текущими задачами, решениями и актуальной информацией', 'portal-theme' );
}

$popular_rows = function_exists( 'portal_theme_analytics_get_popular_rows' )
	? portal_theme_analytics_get_popular_rows()
	: array();
$popular_sub_fallback = __( 'Краткая информация', 'portal-theme' );
?>

<main class="portal-page">
	<?php get_template_part( 'template-parts/layout/sidebar' ); ?>

	<div class="portal-page__main">
		<?php get_template_part( 'template-parts/layout/site-header' ); ?>

		<div class="analytics-page">
			<nav class="analytics-breadcrumbs" aria-label="<?php esc_attr_e( 'Навигация', 'portal-theme' ); ?>">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="analytics-breadcrumbs__home">
					<span class="analytics-breadcrumbs__arrow" aria-hidden="true">‹</span>
					<?php esc_html_e( 'Главная', 'portal-theme' ); ?>
				</a>
				<span class="analytics-breadcrumbs__sep">/</span>
				<span class="analytics-breadcrumbs__current"><?php echo esc_html( $hero_title ); ?></span>
			</nav>

			<header class="analytics-hero">
				<div class="analytics-hero__icon-wrap" aria-hidden="true">
					<div class="analytics-hero__icon">
						<img
							src="<?php echo esc_url( $theme_img . '/Bag.png' ); ?>"
							alt=""
							width="120"
							height="120"
							loading="eager"
						>
					</div>
				</div>
				<div class="analytics-hero__body">
					<h1 class="analytics-hero__title"><?php echo esc_html( $hero_title ); ?></h1>
					<p class="analytics-hero__subtitle"><?php echo esc_html( $hero_sub ); ?></p>
				</div>
				<div class="analytics-hero__illustration">
					<img
						src="<?php echo esc_url( $theme_img . '/Analys.png' ); ?>"
						alt=""
						loading="eager"
					>
				</div>
			</header>

			<div class="analytics-layout">
				<div class="analytics-main">
					<section class="analytics-section analytics-section--tasks" aria-labelledby="analytics-tasks-heading">
						<h2 id="analytics-tasks-heading" class="analytics-section__title"><?php esc_html_e( 'Текущие задачи', 'portal-theme' ); ?></h2>
						<div class="analytics-tasks" id="analytics-tasks-list">
							<?php
							if ( $tasks_q->have_posts() ) :
								while ( $tasks_q->have_posts() ) :
									$tasks_q->the_post();
									$analytics_task_ids[] = get_the_ID();
									echo function_exists( 'portal_theme_analytics_task_card_html' )
										? portal_theme_analytics_task_card_html( get_the_ID() ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
										: '';
								endwhile;
								wp_reset_postdata();
							else :
								?>
								<p class="analytics-empty"><?php esc_html_e( 'Пока задач нет. В консоли откройте меню «Аналитика» → «Новая задача» (как в «Медиабанк») или отредактируйте эту страницу и воспользуйтесь блоком «Материалы страницы: текущие задачи».', 'portal-theme' ); ?></p>
							<?php endif; ?>
						</div>
					</section>
				</div>

				<aside class="analytics-sidebar" aria-label="<?php esc_attr_e( 'Дополнительные блоки', 'portal-theme' ); ?>">
					<section class="analytics-widget analytics-widget--popular">
						<h3 class="analytics-widget__title"><?php esc_html_e( 'Популярные материалы', 'portal-theme' ); ?></h3>
						<ul class="analytics-widget__list">
							<?php foreach ( $popular_rows as $row ) : ?>
								<?php
								$pop_url = isset( $row['url'] ) ? trim( (string) $row['url'] ) : '';
								$pop_sub = isset( $row['sub'] ) && $row['sub'] !== '' ? (string) $row['sub'] : $popular_sub_fallback;
								$icon    = ( isset( $row['icon'] ) && 'green' === $row['icon'] ) ? 'green' : 'yellow';
								?>
								<li>
									<?php if ( $pop_url !== '' ) : ?>
										<a href="<?php echo esc_url( $pop_url ); ?>" class="analytics-popular-item">
											<span class="analytics-popular-item__icon analytics-popular-item__icon--<?php echo esc_attr( $icon ); ?>" aria-hidden="true"></span>
											<span class="analytics-popular-item__text">
												<strong class="analytics-popular-item__title"><?php echo esc_html( isset( $row['title'] ) ? $row['title'] : '' ); ?></strong>
												<span class="analytics-popular-item__sub"><?php echo esc_html( $pop_sub ); ?></span>
											</span>
											<span class="analytics-popular-item__dot" aria-hidden="true"></span>
										</a>
									<?php else : ?>
										<span class="analytics-popular-item analytics-popular-item--static">
											<span class="analytics-popular-item__icon analytics-popular-item__icon--<?php echo esc_attr( $icon ); ?>" aria-hidden="true"></span>
											<span class="analytics-popular-item__text">
												<strong class="analytics-popular-item__title"><?php echo esc_html( isset( $row['title'] ) ? $row['title'] : '' ); ?></strong>
												<span class="analytics-popular-item__sub"><?php echo esc_html( $pop_sub ); ?></span>
											</span>
											<span class="analytics-popular-item__dot" aria-hidden="true"></span>
										</span>
									<?php endif; ?>
								</li>
							<?php endforeach; ?>
						</ul>
						<button type="button" class="analytics-widget__more" aria-label="<?php esc_attr_e( 'Ещё материалы', 'portal-theme' ); ?>">
							<span class="analytics-widget__more-icon" aria-hidden="true">›</span>
						</button>
					</section>

					<section class="analytics-widget analytics-widget--ask">
						<h3 class="analytics-widget__title"><?php esc_html_e( 'Задайте дополнительные вопросы', 'portal-theme' ); ?></h3>
						<?php
						$analytics_ask_flag = isset( $_GET['analytics_ask'] ) ? sanitize_key( wp_unslash( $_GET['analytics_ask'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
						if ( '1' === $analytics_ask_flag ) :
							?>
							<p class="analytics-ask-notice analytics-ask-notice--success" role="status"><?php esc_html_e( 'Сообщение отправлено. Спасибо!', 'portal-theme' ); ?></p>
						<?php elseif ( 'empty' === $analytics_ask_flag ) : ?>
							<p class="analytics-ask-notice analytics-ask-notice--warn" role="alert"><?php esc_html_e( 'Введите текст сообщения.', 'portal-theme' ); ?></p>
						<?php elseif ( 'fail' === $analytics_ask_flag ) : ?>
							<p class="analytics-ask-notice analytics-ask-notice--error" role="alert"><?php esc_html_e( 'Не удалось сохранить сообщение. Попробуйте позже или свяжитесь с администратором сайта.', 'portal-theme' ); ?></p>
						<?php endif; ?>
						<form class="analytics-ask-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
							<input type="hidden" name="action" value="portal_analytics_ask">
							<?php wp_nonce_field( 'portal_analytics_ask' ); ?>
							<label class="analytics-sr-only" for="analytics-question-field"><?php esc_html_e( 'Ваш вопрос', 'portal-theme' ); ?></label>
							<textarea id="analytics-question-field" name="analytics_question" class="analytics-ask-form__textarea" rows="4" placeholder="<?php esc_attr_e( 'Описание', 'portal-theme' ); ?>"></textarea>
							<button type="submit" class="portal-btn portal-btn--orange analytics-ask-form__submit"><?php esc_html_e( 'Отправить', 'portal-theme' ); ?></button>
						</form>
					</section>
				</aside>
			</div>
		</div>
	</div>
</main>

<?php
foreach ( $analytics_task_ids as $analytics_tid ) {
	echo function_exists( 'portal_theme_analytics_task_modal_template_html' )
		? portal_theme_analytics_task_modal_template_html( $analytics_tid ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		: '';
}
?>

<div class="analytics-modal" id="analytics-task-modal" hidden>
	<div class="analytics-modal__backdrop" tabindex="-1"></div>
	<div class="analytics-modal__shell" role="dialog" aria-modal="true" aria-labelledby="analytics-modal-title-live"></div>
</div>

<?php
get_footer();
