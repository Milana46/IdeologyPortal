<?php
/**
 * Plugin Name: Portal Core
 * Description: Настройки портала (созвоны, таблицы), файлы главной.
 * Version: 0.3.1
 * Requires PHP: 8.1
 * Author: Mila
 * Text Domain: portal-core
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'PORTAL_CORE_PATH', plugin_dir_path( __FILE__ ) );
define( 'PORTAL_CORE_URL', plugin_dir_url( __FILE__ ) );

register_activation_hook(
    __FILE__,
    function () {
        portal_core_register_home_file_cpt();
        flush_rewrite_rules();
    }
);

register_deactivation_hook(
    __FILE__,
    function () {
        flush_rewrite_rules();
    }
);

add_action( 'init', 'portal_core_register_home_file_cpt' );

/**
 * Файлы для блоков «Актуальное» и «Необходимые документы» на главной.
 */
function portal_core_register_home_file_cpt() {
    register_post_type(
        'portal_home_file',
        array(
            'labels'             => array(
                'name'          => __( 'Файлы главной', 'portal-core' ),
                'singular_name' => __( 'Файл главной', 'portal-core' ),
                'add_new_item'  => __( 'Добавить файл', 'portal-core' ),
                'edit_item'     => __( 'Редактировать файл', 'portal-core' ),
            ),
            'public'             => false,
            'publicly_queryable' => false,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'menu_icon'          => 'dashicons-media-document',
            'capability_type'    => 'post',
            'map_meta_cap'       => true,
            'supports'           => array( 'title', 'editor', 'page-attributes' ),
            'menu_position'      => 57,
        )
    );
}

add_action( 'add_meta_boxes', 'portal_core_home_file_meta_boxes' );

function portal_core_home_file_meta_boxes() {
    add_meta_box(
        'portal_home_file_download',
        __( 'Скачивание и размещение', 'portal-core' ),
        'portal_core_home_file_metabox_render',
        'portal_home_file',
        'normal',
        'high'
    );
}

function portal_core_home_file_metabox_render( WP_Post $post ) {
    wp_nonce_field( 'portal_home_file_save', 'portal_home_file_nonce' );

    $block = get_post_meta( $post->ID, '_portal_widget_block', true );
    if ( ! in_array( $block, array( 'documents', 'actual' ), true ) ) {
        $block = 'documents';
    }

    $file_id = (int) get_post_meta( $post->ID, '_portal_home_file_id', true );
    $file_url = (string) get_post_meta( $post->ID, '_portal_home_file_url', true );

    $file_name = '';

    if ( $file_id ) {
        $file_name = get_the_title( $file_id );
        if ( '' === $file_name ) {
            $file_name = basename( (string) get_attached_file( $file_id ) );
        }
    }
    ?>
    <p>
        <label for="portal_widget_block"><strong><?php esc_html_e( 'Блок на главной', 'portal-core' ); ?></strong></label><br>
        <select name="portal_widget_block" id="portal_widget_block">
            <option value="actual" <?php selected( $block, 'actual' ); ?>><?php esc_html_e( 'Актуальное (правый столбец)', 'portal-core' ); ?></option>
            <option value="documents" <?php selected( $block, 'documents' ); ?>><?php esc_html_e( 'Необходимые документы', 'portal-core' ); ?></option>
        </select>
    </p>
    <p>
        <label><strong><?php esc_html_e( 'Файл из медиатеки', 'portal-core' ); ?></strong></label><br>
        <input type="hidden" name="portal_home_file_id" id="portal_home_file_id" value="<?php echo esc_attr( (string) $file_id ); ?>">
        <button type="button" class="button" id="portal_home_file_pick"><?php esc_html_e( 'Выбрать файл', 'portal-core' ); ?></button>
        <button type="button" class="button" id="portal_home_file_clear" <?php echo $file_id ? '' : 'style="display:none"'; ?>><?php esc_html_e( 'Сбросить', 'portal-core' ); ?></button>
    </p>
    <p id="portal_home_file_label" style="<?php echo $file_id ? '' : 'display:none;'; ?>">
        <?php esc_html_e( 'Выбрано:', 'portal-core' ); ?>
        <span id="portal_home_file_name"><?php echo esc_html( $file_name ); ?></span>
    </p>
    <p>
        <label for="portal_home_file_url"><strong><?php esc_html_e( 'Или внешняя ссылка на файл', 'portal-core' ); ?></strong></label><br>
        <input type="url" class="large-text" name="portal_home_file_url" id="portal_home_file_url"
            value="<?php echo esc_attr( $file_url ); ?>" placeholder="https://">
    </p>
    <p class="description"><?php esc_html_e( 'Если указаны и медиафайл, и ссылка, для кнопки «Скачать» используется вложение.', 'portal-core' ); ?></p>
    <?php
}

add_action( 'admin_enqueue_scripts', 'portal_core_home_file_admin_assets' );

function portal_core_home_file_admin_assets( $hook ) {
    if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) {
        return;
    }

    $screen = get_current_screen();

    if ( ! $screen || 'portal_home_file' !== $screen->post_type ) {
        return;
    }

    wp_enqueue_media();

    wp_enqueue_script(
        'portal-home-file-admin',
        PORTAL_CORE_URL . 'assets/admin-home-file.js',
        array( 'jquery' ),
        '0.3.0',
        true
    );
}

add_action( 'save_post_portal_home_file', 'portal_core_save_home_file_meta', 10, 2 );

function portal_core_save_home_file_meta( $post_id, WP_Post $post ) {
    if ( ! isset( $_POST['portal_home_file_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['portal_home_file_nonce'] ) ), 'portal_home_file_save' ) ) {
        return;
    }

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    $block = isset( $_POST['portal_widget_block'] ) ? sanitize_text_field( wp_unslash( $_POST['portal_widget_block'] ) ) : 'documents';

    if ( ! in_array( $block, array( 'documents', 'actual' ), true ) ) {
        $block = 'documents';
    }

    update_post_meta( $post_id, '_portal_widget_block', $block );

    $fid = isset( $_POST['portal_home_file_id'] ) ? absint( $_POST['portal_home_file_id'] ) : 0;

    if ( $fid > 0 && 'attachment' === get_post_type( $fid ) ) {
        update_post_meta( $post_id, '_portal_home_file_id', $fid );
    } else {
        delete_post_meta( $post_id, '_portal_home_file_id' );
    }

    $url = isset( $_POST['portal_home_file_url'] ) ? esc_url_raw( wp_unslash( $_POST['portal_home_file_url'] ) ) : '';

    if ( $url ) {
        update_post_meta( $post_id, '_portal_home_file_url', $url );
    } else {
        delete_post_meta( $post_id, '_portal_home_file_url' );
    }
}

/**
 * URL для скачивания (вложение приоритетнее внешней ссылки).
 *
 * @param int $post_id ID записи portal_home_file.
 * @return string
 */
function portal_core_get_home_file_download_url( $post_id ) {
    $post_id = (int) $post_id;

    if ( $post_id < 1 || 'portal_home_file' !== get_post_type( $post_id ) ) {
        return '';
    }

    $fid = (int) get_post_meta( $post_id, '_portal_home_file_id', true );

    if ( $fid ) {
        $url = wp_get_attachment_url( $fid );

        if ( $url ) {
            return $url;
        }
    }

    $ext = get_post_meta( $post_id, '_portal_home_file_url', true );

    return is_string( $ext ) && $ext ? $ext : '';
}

/**
 * @param string $block documents|actual
 * @return WP_Query
 */
function portal_core_home_file_query( $block ) {
    if ( ! in_array( $block, array( 'documents', 'actual' ), true ) ) {
        $block = 'documents';
    }

    return new WP_Query(
        array(
            'post_type'      => 'portal_home_file',
            'post_status'    => 'publish',
            'posts_per_page' => 50,
            'orderby'        => array(
                'menu_order' => 'ASC',
                'title'      => 'ASC',
            ),
            'meta_query'     => array(
                array(
                    'key'   => '_portal_widget_block',
                    'value' => $block,
                ),
            ),
        )
    );
}

/**
 * @param string $block documents|actual
 */
function portal_core_render_home_file_list( $block ) {
    $q = portal_core_home_file_query( $block );

    if ( ! $q->have_posts() ) {
        echo '<div class="documents-list__empty">';

        if ( current_user_can( 'manage_options' ) ) {
            echo esc_html(
                'documents' === $block
                    ? __( 'Добавьте записи в «Файлы главной» с блоком «Необходимые документы».', 'portal-core' )
                    : __( 'Добавьте записи в «Файлы главной» с блоком «Актуальное».', 'portal-core' )
            );
        } else {
            echo esc_html(
                'documents' === $block
                    ? __( 'Документы пока не добавлены.', 'portal-core' )
                    : __( 'Материалы скоро появятся.', 'portal-core' )
            );
        }

        echo '</div>';
        return;
    }

    echo '<div class="documents-list">';

    while ( $q->have_posts() ) {
        $q->the_post();
        portal_core_render_home_file_row( (int) get_the_ID() );
    }

    echo '</div>';
    wp_reset_postdata();
}

/**
 * @param int $post_id ID portal_home_file.
 */
function portal_core_render_home_file_row( $post_id ) {
    $url = portal_core_get_home_file_download_url( $post_id );

    if ( ! $url ) {
        return;
    }

    $raw     = get_post_field( 'post_content', $post_id );
    $summary = $raw ? wp_trim_words( wp_strip_all_tags( $raw ), 12, '…' ) : '';
    ?>
    <div class="documents-list__item">
        <div class="documents-list__icon" aria-hidden="true"></div>
        <div class="documents-list__content">
            <div class="documents-list__title">
                <?php echo esc_html( get_the_title( $post_id ) ); ?>
            </div>
            <?php if ( $summary ) : ?>
                <div class="documents-list__text">
                    <?php echo esc_html( $summary ); ?>
                </div>
            <?php endif; ?>
            <div class="documents-list__actions">
                <a class="portal-download-link" href="<?php echo esc_url( $url ); ?>" download target="_blank" rel="noopener noreferrer">
                    <?php esc_html_e( 'Скачать', 'portal-core' ); ?>
                </a>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Расширенный whitelist для вкладок главной.
 *
 * @return array<string, array<string, array<int, string>>>
 */
function portal_core_allowed_tab_html_tags() {
    $a = array(
        'href'   => array(),
        'title'  => array(),
        'target' => array(),
        'rel'    => array(),
        'class'  => array(),
    );

    return array(
        'a'      => $a,
        'br'     => array(),
        'p'      => array( 'class' => array() ),
        'strong' => array(),
        'em'     => array(),
        'ul'     => array( 'class' => array() ),
        'ol'     => array( 'class' => array() ),
        'li'     => array(),
        'h2'     => array( 'class' => array() ),
        'h3'     => array( 'class' => array() ),
        'div'    => array( 'class' => array() ),
    );
}

/**
 * @param string $html Сырой HTML.
 * @return string
 */
function portal_core_sanitize_tab_html( $html ) {
    return wp_kses( $html, portal_core_allowed_tab_html_tags() );
}

/**
 * @param string $option_key Ключ опции.
 * @return string Очищенный HTML или пустая строка.
 */
function portal_core_get_tab_html( $option_key ) {
    $html = get_option( $option_key, '' );

    if ( ! is_string( $html ) || '' === $html ) {
        return '';
    }

    return wp_kses( $html, portal_core_allowed_tab_html_tags() );
}

add_action( 'admin_menu', 'portal_core_register_admin_page' );

function portal_core_register_admin_page() {
    add_menu_page(
        __( 'Портал', 'portal-core' ),
        __( 'Портал', 'portal-core' ),
        'manage_options',
        'portal-settings',
        'portal_core_render_settings_page',
        'dashicons-admin-site',
        58
    );

    add_submenu_page(
        'portal-settings',
        __( 'Инструкция: молодёжный форум', 'portal-core' ),
        __( 'Инструкция (форум)', 'portal-core' ),
        'manage_options',
        'portal-forum-instruction',
        'portal_core_render_forum_instruction_page'
    );
}

/**
 * Документ из папки темы: assets/documents/
 *
 * @return array<string, mixed>
 */
function portal_core_get_youth_forum_instruction_assets() {
    $doc_filename = 'Пошагавая инструкция к проведению молодежного-патриотического форума в Полоцком районе.doc';

    $empty = array(
        'doc_filename'      => $doc_filename,
        'doc_exists'        => false,
        'doc_path'          => '',
        'doc_url_plain'     => '',
        'pdf_url_plain'     => '',
        'office_embed_src'  => '',
        'is_local'          => true,
        'has_iframe'        => false,
    );

    $theme_dir = get_template_directory();
    if ( ! is_string( $theme_dir ) || '' === $theme_dir ) {
        return $empty;
    }

    $doc_rel  = 'assets/documents/' . $doc_filename;
    $doc_path = wp_normalize_path( $theme_dir . '/' . $doc_rel );

    if ( ! file_exists( $doc_path ) ) {
        $empty['doc_path'] = $doc_path;
        return $empty;
    }

    $doc_url_plain = get_template_directory_uri() . '/assets/documents/' . rawurlencode( $doc_filename );

    $pdf_filename = preg_replace( '/\.docx?$/iu', '.pdf', $doc_filename );
    $pdf_path     = wp_normalize_path( $theme_dir . '/assets/documents/' . $pdf_filename );
    $pdf_url_plain = '';
    if ( file_exists( $pdf_path ) ) {
        $pdf_url_plain = get_template_directory_uri() . '/assets/documents/' . rawurlencode( $pdf_filename );
    }

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

    $office_embed_src = '';
    if ( ! $pdf_url_plain && $doc_url_plain && ! $is_local ) {
        $office_embed_src = 'https://view.officeapps.live.com/op/embed.aspx?src=' . rawurlencode( $doc_url_plain );
    }

    $has_iframe = (bool) ( $pdf_url_plain || $office_embed_src );

    return array(
        'doc_filename'     => $doc_filename,
        'doc_exists'       => true,
        'doc_path'         => $doc_path,
        'doc_url_plain'    => $doc_url_plain,
        'pdf_url_plain'    => $pdf_url_plain,
        'office_embed_src' => $office_embed_src,
        'is_local'         => $is_local,
        'has_iframe'       => $has_iframe,
    );
}

/**
 * Страница просмотра инструкции в консоли (чтение + скачивание .doc).
 */
function portal_core_render_forum_instruction_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $a = portal_core_get_youth_forum_instruction_assets();

    ?>
    <div class="wrap portal-admin-instruction">
        <h1><?php esc_html_e( 'Пошаговая инструкция к проведению молодежного-патриотического форума', 'portal-core' ); ?></h1>
        <p class="description">
            <?php esc_html_e( 'Файл хранится в теме: wp-content/themes/…/assets/documents/', 'portal-core' ); ?>
        </p>

        <?php if ( ! $a['doc_exists'] ) : ?>
            <div class="notice notice-error">
                <p>
                    <?php
                    echo esc_html(
                        sprintf(
                            /* translators: %s: file name */
                            __( 'Файл не найден: %s', 'portal-core' ),
                            $a['doc_filename']
                        )
                    );
                    ?>
                </p>
            </div>
        <?php else : ?>

            <div class="portal-admin-instruction__viewer">
                <?php if ( ! empty( $a['pdf_url_plain'] ) ) : ?>
                    <iframe
                        class="portal-admin-instruction__frame"
                        title="<?php esc_attr_e( 'Просмотр PDF', 'portal-core' ); ?>"
                        src="<?php echo esc_url( $a['pdf_url_plain'] ); ?>"
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                    ></iframe>
                <?php elseif ( ! empty( $a['office_embed_src'] ) ) : ?>
                    <iframe
                        class="portal-admin-instruction__frame"
                        title="<?php esc_attr_e( 'Просмотр документа', 'portal-core' ); ?>"
                        src="<?php echo esc_url( $a['office_embed_src'] ); ?>"
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        allowfullscreen
                    ></iframe>
                <?php else : ?>
                    <div class="portal-admin-instruction__fallback notice notice-info inline">
                        <p>
                            <?php
                            if ( ! empty( $a['is_local'] ) ) {
                                esc_html_e( 'На локальном адресе (localhost) встроенный просмотр Word-файла недоступен. Положите в папку темы PDF с тем же именем и расширением .pdf — здесь отобразится содержимое. Либо откройте сайт по публичному адресу для просмотра через Microsoft Office Online.', 'portal-core' );
                            } else {
                                esc_html_e( 'Предпросмотр недоступен. Добавьте PDF-копию документа в каталог темы (то же имя, расширение .pdf) или скачайте исходный файл ниже.', 'portal-core' );
                            }
                            ?>
                        </p>
                    </div>
                <?php endif; ?>
            </div>

            <p class="portal-admin-instruction__filename">
                <strong><?php esc_html_e( 'Файл:', 'portal-core' ); ?></strong>
                <?php echo esc_html( $a['doc_filename'] ); ?>
            </p>

            <p class="portal-admin-instruction__actions">
                <a href="<?php echo esc_url( $a['doc_url_plain'] ); ?>" class="button button-primary button-hero" download="<?php echo esc_attr( $a['doc_filename'] ); ?>">
                    <?php esc_html_e( 'Скачать документ (.doc)', 'portal-core' ); ?>
                </a>
            </p>

        <?php endif; ?>
    </div>
    <?php
}

add_action( 'admin_enqueue_scripts', 'portal_core_instruction_admin_assets' );

/**
 * Стили экрана инструкции в админке.
 *
 * @param string $hook_suffix Текущий экран.
 */
function portal_core_instruction_admin_assets( $hook_suffix ) {
    if ( 'portal-settings_page_portal-forum-instruction' !== $hook_suffix ) {
        return;
    }

    $css_path = PORTAL_CORE_PATH . 'assets/admin-instruction.css';
    if ( ! file_exists( $css_path ) ) {
        return;
    }

    wp_enqueue_style(
        'portal-admin-instruction',
        PORTAL_CORE_URL . 'assets/admin-instruction.css',
        array(),
        '0.3.1'
    );
}

add_action( 'admin_init', 'portal_core_register_settings' );

function portal_core_register_settings() {
    register_setting(
        'portal_core_settings',
        'portal_calls_url',
        array(
            'type'              => 'string',
            'sanitize_callback' => 'esc_url_raw',
            'default'           => '',
        )
    );
    register_setting(
        'portal_core_settings',
        'portal_calls_label',
        array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => __( 'Перейти к созвону', 'portal-core' ),
        )
    );
    register_setting(
        'portal_core_settings',
        'portal_calls_secondary_url',
        array(
            'type'              => 'string',
            'sanitize_callback' => 'esc_url_raw',
            'default'           => '',
        )
    );
    register_setting(
        'portal_core_settings',
        'portal_calls_secondary_label',
        array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => '',
        )
    );
    register_setting(
        'portal_core_settings',
        'portal_sheets_embed_url',
        array(
            'type'              => 'string',
            'sanitize_callback' => 'esc_url_raw',
            'default'           => '',
        )
    );
    register_setting(
        'portal_core_settings',
        'portal_sheets_block_title',
        array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => __( 'Оперативные данные (Google Таблицы)', 'portal-core' ),
        )
    );
    register_setting(
        'portal_core_settings',
        'portal_home_tab_about_html',
        array(
            'type'              => 'string',
            'sanitize_callback' => 'portal_core_sanitize_tab_html',
            'default'           => '',
        )
    );
    register_setting(
        'portal_core_settings',
        'portal_home_tab_advantages_html',
        array(
            'type'              => 'string',
            'sanitize_callback' => 'portal_core_sanitize_tab_html',
            'default'           => '',
        )
    );
    register_setting(
        'portal_core_settings',
        'portal_home_tab_sections_html',
        array(
            'type'              => 'string',
            'sanitize_callback' => 'portal_core_sanitize_tab_html',
            'default'           => '',
        )
    );
}

function portal_core_render_settings_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    ?>
    <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        <p class="description">
            <?php esc_html_e( 'Ссылки на созвоны и встраивание таблиц — без правки кода. Для Google Таблиц: «Файл → Опубликовать в интернете» или вставьте готовую ссылку вида docs.google.com/…', 'portal-core' ); ?>
        </p>
        <p class="description">
            <?php esc_html_e( 'Файлы для блоков «Актуальное» и «Необходимые документы» добавляйте в меню «Файлы главной» (вложение или внешняя ссылка). Порядок: поле «Порядок» в записи.', 'portal-core' ); ?>
        </p>
        <form action="options.php" method="post">
            <?php
            settings_fields( 'portal_core_settings' );
            $calls_url          = get_option( 'portal_calls_url', '' );
            $calls_label        = get_option( 'portal_calls_label', __( 'Перейти к созвону', 'portal-core' ) );
            $calls_sec_url      = get_option( 'portal_calls_secondary_url', '' );
            $calls_sec_label    = get_option( 'portal_calls_secondary_label', '' );
            $sheets_url         = get_option( 'portal_sheets_embed_url', '' );
            $sheets_title       = get_option( 'portal_sheets_block_title', __( 'Оперативные данные (Google Таблицы)', 'portal-core' ) );
            $tab_about          = get_option( 'portal_home_tab_about_html', '' );
            $tab_adv            = get_option( 'portal_home_tab_advantages_html', '' );
            $tab_sec            = get_option( 'portal_home_tab_sections_html', '' );
            ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="portal_calls_url"><?php esc_html_e( 'Ссылка на созвон', 'portal-core' ); ?></label></th>
                    <td>
                        <input name="portal_calls_url" id="portal_calls_url" type="url" class="large-text code" value="<?php echo esc_attr( $calls_url ); ?>" placeholder="https://">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="portal_calls_label"><?php esc_html_e( 'Текст кнопки', 'portal-core' ); ?></label></th>
                    <td>
                        <input name="portal_calls_label" id="portal_calls_label" type="text" class="regular-text" value="<?php echo esc_attr( $calls_label ); ?>">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="portal_calls_secondary_url"><?php esc_html_e( 'Доп. ссылка (второй созвон / чат)', 'portal-core' ); ?></label></th>
                    <td>
                        <input name="portal_calls_secondary_url" id="portal_calls_secondary_url" type="url" class="large-text code" value="<?php echo esc_attr( $calls_sec_url ); ?>" placeholder="https://">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="portal_calls_secondary_label"><?php esc_html_e( 'Подпись для доп. ссылки', 'portal-core' ); ?></label></th>
                    <td>
                        <input name="portal_calls_secondary_label" id="portal_calls_secondary_label" type="text" class="regular-text" value="<?php echo esc_attr( $calls_sec_label ); ?>">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="portal_sheets_embed_url"><?php esc_html_e( 'URL для iframe Google Таблицы', 'portal-core' ); ?></label></th>
                    <td>
                        <input name="portal_sheets_embed_url" id="portal_sheets_embed_url" type="url" class="large-text code" value="<?php echo esc_attr( $sheets_url ); ?>">
                        <p class="description"><?php esc_html_e( 'Будет показано на главной в блоке «живой» таблицы (обновление — со стороны Google).', 'portal-core' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="portal_sheets_block_title"><?php esc_html_e( 'Заголовок блока с таблицей', 'portal-core' ); ?></label></th>
                    <td>
                        <input name="portal_sheets_block_title" id="portal_sheets_block_title" type="text" class="large-text" value="<?php echo esc_attr( $sheets_title ); ?>">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="portal_home_tab_about_html"><?php esc_html_e( 'Вкладка «О платформе»', 'portal-core' ); ?></label></th>
                    <td>
                        <textarea name="portal_home_tab_about_html" id="portal_home_tab_about_html" class="large-text" rows="5"><?php echo esc_textarea( $tab_about ); ?></textarea>
                        <p class="description"><?php esc_html_e( 'Текст в основной колонке при наведении на первую вкладку. HTML: абзацы, списки, ссылки, h2/h3.', 'portal-core' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="portal_home_tab_advantages_html"><?php esc_html_e( 'Вкладка «Преимущества платформы»', 'portal-core' ); ?></label></th>
                    <td>
                        <textarea name="portal_home_tab_advantages_html" id="portal_home_tab_advantages_html" class="large-text" rows="5"><?php echo esc_textarea( $tab_adv ); ?></textarea>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="portal_home_tab_sections_html"><?php esc_html_e( 'Вкладка «Основные разделы» (вводный текст)', 'portal-core' ); ?></label></th>
                    <td>
                        <textarea name="portal_home_tab_sections_html" id="portal_home_tab_sections_html" class="large-text" rows="4"><?php echo esc_textarea( $tab_sec ); ?></textarea>
                        <p class="description"><?php esc_html_e( 'Показывается над карточками разделов и блоком таблицы.', 'portal-core' ); ?></p>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

/**
 * Создаёт опубликованную страницу «Советник+» с шаблоном темы, если её ещё нет.
 */
add_action( 'init', 'portal_core_ensure_sovetnik_plus_page', 99 );

function portal_core_ensure_sovetnik_plus_page() {
    if ( wp_installing() ) {
        return;
    }

    $slug = 'sovetnik-plus';
    $page = get_page_by_path( $slug, OBJECT, 'page' );

    if ( $page instanceof WP_Post ) {
        if ( 'publish' !== $page->post_status ) {
            wp_update_post(
                array(
                    'ID'          => $page->ID,
                    'post_status' => 'publish',
                )
            );
        }
        update_post_meta( $page->ID, '_wp_page_template', 'page-sovetnik-plus.php' );
        return;
    }

    $post_id = wp_insert_post(
        array(
            'post_title'  => __( 'Советник+', 'portal-core' ),
            'post_name'   => $slug,
            'post_status' => 'publish',
            'post_type'   => 'page',
            'post_author' => 1,
        ),
        true
    );

    if ( is_wp_error( $post_id ) || ! $post_id ) {
        return;
    }

    update_post_meta( (int) $post_id, '_wp_page_template', 'page-sovetnik-plus.php' );
}

/**
 * Создаёт опубликованную страницу «Библиотека практик» с шаблоном темы, если её ещё нет.
 */
add_action( 'init', 'portal_core_ensure_biblioteka_praktik_page', 100 );

function portal_core_ensure_biblioteka_praktik_page() {
    if ( wp_installing() ) {
        return;
    }

    $slug = 'biblioteka-praktik';
    $page = get_page_by_path( $slug, OBJECT, 'page' );

    if ( $page instanceof WP_Post ) {
        if ( 'publish' !== $page->post_status ) {
            wp_update_post(
                array(
                    'ID'          => $page->ID,
                    'post_status' => 'publish',
                )
            );
        }
        update_post_meta( $page->ID, '_wp_page_template', 'page-biblioteka-praktik.php' );
        return;
    }

    $post_id = wp_insert_post(
        array(
            'post_title'  => __( 'Библиотека практик', 'portal-core' ),
            'post_name'   => $slug,
            'post_status' => 'publish',
            'post_type'   => 'page',
            'post_author' => 1,
        ),
        true
    );

    if ( is_wp_error( $post_id ) || ! $post_id ) {
        return;
    }

    update_post_meta( (int) $post_id, '_wp_page_template', 'page-biblioteka-praktik.php' );
}

/**
 * Создаёт опубликованную страницу «Календарь ключевых событий» с шаблоном темы, если её ещё нет.
 */
add_action( 'init', 'portal_core_ensure_kalendar_klyuchevyy_sobytiy_page', 101 );

function portal_core_ensure_kalendar_klyuchevyy_sobytiy_page() {
    if ( wp_installing() ) {
        return;
    }

    $slug = 'kalendar-klyuchevyy-sobytiy';
    $page = get_page_by_path( $slug, OBJECT, 'page' );

    if ( $page instanceof WP_Post ) {
        if ( 'publish' !== $page->post_status ) {
            wp_update_post(
                array(
                    'ID'          => $page->ID,
                    'post_status' => 'publish',
                )
            );
        }
        update_post_meta( $page->ID, '_wp_page_template', 'page-kalendar-klyuchevyy-sobytiy.php' );
        return;
    }

    $post_id = wp_insert_post(
        array(
            'post_title'  => __( 'Календарь ключевых событий', 'portal-core' ),
            'post_name'   => $slug,
            'post_status' => 'publish',
            'post_type'   => 'page',
            'post_author' => 1,
        ),
        true
    );

    if ( is_wp_error( $post_id ) || ! $post_id ) {
        return;
    }

    update_post_meta( (int) $post_id, '_wp_page_template', 'page-kalendar-klyuchevyy-sobytiy.php' );
}
