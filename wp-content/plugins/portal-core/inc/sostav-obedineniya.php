<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function portal_core_register_union_org_cpt() {
	register_post_type(
		'portal_union_org',
		array(
			'labels'             => array(
				'name'               => __( 'Состав объединения', 'portal-core' ),
				'singular_name'      => __( 'Организация', 'portal-core' ),
				'add_new'            => __( 'Добавить организацию', 'portal-core' ),
				'add_new_item'       => __( 'Новая организация', 'portal-core' ),
				'edit_item'          => __( 'Редактировать организацию', 'portal-core' ),
				'new_item'           => __( 'Новая организация', 'portal-core' ),
				'search_items'       => __( 'Поиск организаций', 'portal-core' ),
				'not_found'          => __( 'Организаций не найдено', 'portal-core' ),
				'not_found_in_trash' => __( 'В корзине пусто', 'portal-core' ),
				'all_items'          => __( 'Все организации', 'portal-core' ),
				'menu_name'          => __( 'Состав объединения', 'portal-core' ),
			),
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'menu_icon'          => 'dashicons-groups',
			'menu_position'      => 56,
			'capability_type'    => 'post',
			'map_meta_cap'       => true,
			'hierarchical'       => true,
			'supports'           => array( 'title', 'page-attributes' ),
			'has_archive'        => false,
			'rewrite'            => false,
			'show_in_rest'       => false,
		)
	);
}
add_action( 'init', 'portal_core_register_union_org_cpt' );

function portal_core_union_org_add_meta_boxes() {
	add_meta_box(
		'portal_union_org_details',
		__( 'Сведения об организации', 'portal-core' ),
		'portal_core_union_org_metabox_render',
		'portal_union_org',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'portal_core_union_org_add_meta_boxes' );

function portal_core_union_org_metabox_render( WP_Post $post ) {
	wp_nonce_field( 'portal_union_org_save', 'portal_union_org_nonce' );
	$figure   = (string) get_post_meta( $post->ID, '_portal_union_figure', true );
	$about    = (string) get_post_meta( $post->ID, '_portal_union_about', true );
	$contacts = (string) get_post_meta( $post->ID, '_portal_union_contacts', true );
	$website  = (string) get_post_meta( $post->ID, '_portal_union_website', true );
	$email    = (string) get_post_meta( $post->ID, '_portal_union_email', true );
	?>
	<p>
		<label for="portal_union_figure"><strong><?php esc_html_e( 'Число после названия (например, 14)', 'portal-core' ); ?></strong></label><br>
		<input type="text" class="small-text" name="portal_union_figure" id="portal_union_figure" value="<?php echo esc_attr( $figure ); ?>">
	</p>
	<p>
		<label for="portal_union_website"><strong><?php esc_html_e( 'Сайт организации', 'portal-core' ); ?></strong></label><br>
		<input type="url" class="large-text" name="portal_union_website" id="portal_union_website" value="<?php echo esc_attr( $website ); ?>" placeholder="https://">
		<span class="description"><?php esc_html_e( 'На сайте адрес не показывается: ссылкой станет название организации внутри раскрытого блока.', 'portal-core' ); ?></span>
	</p>
	<p>
		<label for="portal_union_email"><strong><?php esc_html_e( 'Электронная почта', 'portal-core' ); ?></strong></label><br>
		<input type="email" class="regular-text" name="portal_union_email" id="portal_union_email" value="<?php echo esc_attr( $email ); ?>">
	</p>
	<p>
		<label for="portal_union_about"><strong><?php esc_html_e( 'Краткая информация', 'portal-core' ); ?></strong></label><br>
		<textarea class="large-text" rows="4" name="portal_union_about" id="portal_union_about"><?php echo esc_textarea( $about ); ?></textarea>
	</p>
	<p>
		<label for="portal_union_contacts"><strong><?php esc_html_e( 'Контакты', 'portal-core' ); ?></strong></label><br>
		<textarea class="large-text" rows="3" name="portal_union_contacts" id="portal_union_contacts"><?php echo esc_textarea( $contacts ); ?></textarea>
	</p>
	<p class="description">
		<?php esc_html_e( 'Заголовок — название (РУП «Брестэнерго»). Филиалы добавляйте отдельными записями и в блоке «Атрибуты» справа выбирайте родителя — тогда они появятся внутри раскрытого блока. Порядок — поле «Порядок».', 'portal-core' ); ?>
	</p>
	<?php
}

function portal_core_union_org_save_meta( $post_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! isset( $_POST['portal_union_org_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['portal_union_org_nonce'] ) ), 'portal_union_org_save' ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( get_post_type( $post_id ) !== 'portal_union_org' ) {
		return;
	}

	$figure = isset( $_POST['portal_union_figure'] ) ? sanitize_text_field( wp_unslash( $_POST['portal_union_figure'] ) ) : '';
	update_post_meta( $post_id, '_portal_union_figure', $figure );

	$about = isset( $_POST['portal_union_about'] ) ? sanitize_textarea_field( wp_unslash( $_POST['portal_union_about'] ) ) : '';
	update_post_meta( $post_id, '_portal_union_about', $about );

	$contacts = isset( $_POST['portal_union_contacts'] ) ? sanitize_textarea_field( wp_unslash( $_POST['portal_union_contacts'] ) ) : '';
	update_post_meta( $post_id, '_portal_union_contacts', $contacts );

	$website = isset( $_POST['portal_union_website'] ) ? esc_url_raw( wp_unslash( $_POST['portal_union_website'] ) ) : '';
	if ( $website ) {
		update_post_meta( $post_id, '_portal_union_website', $website );
	} else {
		delete_post_meta( $post_id, '_portal_union_website' );
	}

	$email = isset( $_POST['portal_union_email'] ) ? sanitize_email( wp_unslash( $_POST['portal_union_email'] ) ) : '';
	if ( $email ) {
		update_post_meta( $post_id, '_portal_union_email', $email );
	} else {
		delete_post_meta( $post_id, '_portal_union_email' );
	}
}
add_action( 'save_post_portal_union_org', 'portal_core_union_org_save_meta' );

function portal_core_union_org_columns( $columns ) {
	$new = array();
	foreach ( $columns as $key => $label ) {
		$new[ $key ] = $label;
		if ( 'title' === $key ) {
			$new['portal_union_figure']  = __( 'Число', 'portal-core' );
			$new['portal_union_website'] = __( 'Сайт', 'portal-core' );
			$new['portal_union_email']   = __( 'Почта', 'portal-core' );
		}
	}
	return $new;
}
add_filter( 'manage_portal_union_org_posts_columns', 'portal_core_union_org_columns' );

function portal_core_union_org_custom_column( $column, $post_id ) {
	$post_id = (int) $post_id;
	if ( 'portal_union_figure' === $column ) {
		echo esc_html( (string) get_post_meta( $post_id, '_portal_union_figure', true ) );
		return;
	}
	if ( 'portal_union_website' === $column ) {
		$url = (string) get_post_meta( $post_id, '_portal_union_website', true );
		echo $url ? esc_html( $url ) : '—';
		return;
	}
	if ( 'portal_union_email' === $column ) {
		$email = (string) get_post_meta( $post_id, '_portal_union_email', true );
		echo $email ? esc_html( $email ) : '—';
	}
}
add_action( 'manage_portal_union_org_posts_custom_column', 'portal_core_union_org_custom_column', 10, 2 );

function portal_core_union_org_posts( $parent_id = 0 ) {
	$posts = get_posts(
		array(
			'post_type'      => 'portal_union_org',
			'post_status'    => 'publish',
			'post_parent'    => (int) $parent_id,
			'numberposts'    => 50,
			'orderby'        => 'menu_order title',
			'order'          => 'ASC',
		)
	);
	return is_array( $posts ) ? $posts : array();
}

function portal_core_render_union_accordion() {
	portal_core_render_union_accordion_list( 0, false );
}

function portal_core_render_union_accordion_list( $parent_id, $nested ) {
	$posts = portal_core_union_org_posts( $parent_id );

	if ( empty( $posts ) ) {
		if ( ! $nested && current_user_can( 'manage_options' ) ) {
			echo '<p class="portal-widget__placeholder">' . esc_html__( 'Добавьте организации в меню «Состав объединения».', 'portal-core' ) . '</p>';
		}
		return;
	}

	$class = $nested ? 'portal-accordion portal-accordion--nested' : 'portal-accordion';
	echo '<div class="' . esc_attr( $class ) . '" data-portal-accordion>';

	foreach ( $posts as $post ) {
		if ( $post instanceof WP_Post ) {
			portal_core_render_union_accordion_item( (int) $post->ID );
		}
	}

	echo '</div>';
}

function portal_core_render_union_accordion_item( $pid ) {
	$pid      = (int) $pid;
	$post     = get_post( $pid );
	if ( ! $post instanceof WP_Post ) {
		return;
	}
	$title    = get_the_title( $pid );
	$figure   = trim( (string) get_post_meta( $pid, '_portal_union_figure', true ) );
	$about    = trim( (string) get_post_meta( $pid, '_portal_union_about', true ) );
	$contacts = trim( (string) get_post_meta( $pid, '_portal_union_contacts', true ) );
	$website  = (string) get_post_meta( $pid, '_portal_union_website', true );
	$email    = (string) get_post_meta( $pid, '_portal_union_email', true );
	$head_id  = 'portal-acc-h-' . $pid;
	$panel_id = 'portal-acc-p-' . $pid;
	$label    = $title;
	if ( $figure !== '' ) {
		$label .= ' / ' . $figure;
	}
	$children = portal_core_union_org_posts( $pid );
	?>
	<div class="portal-accordion__item">
		<button
			type="button"
			class="portal-accordion__head"
			id="<?php echo esc_attr( $head_id ); ?>"
			aria-expanded="false"
			aria-controls="<?php echo esc_attr( $panel_id ); ?>"
		>
			<span class="portal-accordion__label"><?php echo esc_html( $label ); ?></span>
			<span class="portal-accordion__mark" aria-hidden="true">+</span>
		</button>
		<div
			class="portal-accordion__body"
			id="<?php echo esc_attr( $panel_id ); ?>"
			role="region"
			aria-labelledby="<?php echo esc_attr( $head_id ); ?>"
			hidden
		>
			<div class="portal-accordion__card">
				<?php if ( $website ) : ?>
					<p class="portal-accordion__org">
						<a href="<?php echo esc_url( $website ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $title ); ?></a>
					</p>
				<?php else : ?>
					<p class="portal-accordion__org"><?php echo esc_html( $title ); ?></p>
				<?php endif; ?>

				<?php if ( $about !== '' ) : ?>
					<div class="portal-accordion__block">
						<p class="portal-accordion__k"><?php esc_html_e( 'Краткая информация', 'portal-core' ); ?></p>
						<p class="portal-accordion__v"><?php echo nl2br( esc_html( $about ) ); ?></p>
					</div>
				<?php endif; ?>

				<?php if ( $contacts !== '' ) : ?>
					<div class="portal-accordion__block">
						<p class="portal-accordion__k"><?php esc_html_e( 'Контакты', 'portal-core' ); ?></p>
						<p class="portal-accordion__v"><?php echo nl2br( esc_html( $contacts ) ); ?></p>
					</div>
				<?php endif; ?>

				<?php if ( $email ) : ?>
					<div class="portal-accordion__block">
						<p class="portal-accordion__k"><?php esc_html_e( 'Электронная почта', 'portal-core' ); ?></p>
						<p class="portal-accordion__v">
							<a href="<?php echo esc_url( 'mailto:' . $email ); ?>"><?php echo esc_html( $email ); ?></a>
						</p>
					</div>
				<?php endif; ?>
			</div>
			<?php
			if ( ! empty( $children ) ) {
				echo '<div class="portal-accordion__children">';
				echo '<div class="portal-accordion portal-accordion--nested" data-portal-accordion>';
				foreach ( $children as $child ) {
					if ( $child instanceof WP_Post ) {
						portal_core_render_union_accordion_item( (int) $child->ID );
					}
				}
				echo '</div>';
				echo '</div>';
			}
			?>
		</div>
	</div>
	<?php
}
