<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$search_query = get_search_query();
$results      = function_exists( 'portal_theme_collect_search_results' )
	? portal_theme_collect_search_results( $search_query )
	: array();
?>

<main class="portal-page">
	<?php get_template_part( 'template-parts/layout/sidebar' ); ?>

	<div class="portal-page__main">
		<?php get_template_part( 'template-parts/layout/site-header' ); ?>

		<section class="portal-section">
			<h1><?php esc_html_e( 'Результаты поиска', 'portal-theme' ); ?></h1>

			<?php if ( $search_query !== '' ) : ?>
				<p>
					<?php
					printf(
						
						esc_html__( 'Запрос: %s', 'portal-theme' ),
						'<strong>' . esc_html( $search_query ) . '</strong>'
					);
					?>
				</p>
			<?php endif; ?>

			<?php if ( ! empty( $results ) ) : ?>
				<div class="portal-search-results">
					<?php foreach ( $results as $item ) : ?>
						<article class="portal-search-results__item">
							<h2>
								<a href="<?php echo esc_url( (string) $item['url'] ); ?>">
									<?php echo esc_html( (string) $item['title'] ); ?>
								</a>
							</h2>
							<p>
								<strong><?php esc_html_e( 'Раздел:', 'portal-theme' ); ?></strong>
								<?php echo esc_html( (string) $item['section'] ); ?>
							</p>
							<?php if ( ! empty( $item['excerpt'] ) ) : ?>
								<p><?php echo esc_html( wp_trim_words( (string) $item['excerpt'], 28 ) ); ?></p>
							<?php endif; ?>
						</article>
					<?php endforeach; ?>
				</div>
			<?php else : ?>
				<p><?php esc_html_e( 'Ничего не найдено. Попробуйте изменить запрос.', 'portal-theme' ); ?></p>
			<?php endif; ?>
		</section>
	</div>
</main>

<?php get_footer(); ?>
