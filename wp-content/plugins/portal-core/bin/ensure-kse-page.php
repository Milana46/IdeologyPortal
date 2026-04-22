<?php
portal_core_ensure_kalendar_klyuchevyy_sobytiy_page();
$p = get_page_by_path( 'kalendar-klyuchevyy-sobytiy' );
echo $p ? (string) $p->ID . ' ' . $p->post_status : 'fail';
