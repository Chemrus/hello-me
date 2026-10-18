<?php
/** Обложки материалам без обложки (задание 26): тот же генератор, что срабатывает через 5 минут после публикации (lf-auto-cover.php).
 *  wp eval-file covers-backfill-20261018.php --url=https://letfind.me */
$n = 0; $err = 0;
foreach ( array_keys( lf_auto_cover_types() ) as $type ) {
	foreach ( get_posts( array( 'post_type' => $type, 'post_status' => 'publish', 'numberposts' => -1, 'fields' => 'ids', 'meta_query' => array( array( 'key' => '_thumbnail_id', 'compare' => 'NOT EXISTS' ) ) ) ) as $id ) {
		$r = lf_auto_cover_make( $id );
		if ( is_wp_error( $r ) ) { $err++; WP_CLI::warning( "$id: " . $r->get_error_message() ); } elseif ( $r ) { $n++; WP_CLI::log( "обложка $id ($type) → $r" ); }
	}
}
WP_CLI::log( "готово: обложек $n, ошибок $err" );
