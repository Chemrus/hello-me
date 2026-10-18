<?php
/**
 * Координаты мест из ссылок на карту (задание 26). Запуск:
 *   wp eval-file geo-backfill-20261018.php --url=https://letfind.me            — пробный прогон: ничего не пишет, CSV в /home/customer/geo-backfill-20261018.csv
 *   wp eval-file geo-backfill-20261018.php apply --url=https://letfind.me      — записать то, что прошло проверку
 * 1) Короткие ссылки maps.app.goo.gl — раскрываются тем же кодом темы, что при сохранении карточки (lf_schema_update_geo).
 * 2) Ссылки-поиски google.com/maps/search/?query=… — геокодер OpenStreetMap (Nominatim, не чаще 1 запроса в секунду).
 *    Принимаем только точный результат: заведение/дом (amenity, shop, tourism, leisure, building, office, place=house)
 *    или совпал номер дома из запроса, и точка не дальше 25 км от центра города карточки. Остальное — в CSV для ручной проверки.
 */
$apply = in_array( 'apply', (array) ( $args ?? array() ), true );
$centers = array( 'danang' => array( 16.054, 108.202 ), 'hoian' => array( 15.880, 108.338 ), 'phuquoc' => array( 10.227, 103.963 ), 'nhatrang' => array( 12.238, 109.196 ),
	'hochiminh' => array( 10.776, 106.700 ), 'hanoi' => array( 21.028, 105.854 ), 'dalat' => array( 11.940, 108.458 ), 'muine' => array( 10.933, 108.287 ), 'vungtau' => array( 10.346, 107.084 ),
	'hue' => array( 16.463, 107.590 ), 'quynhon' => array( 13.782, 109.219 ), 'halong' => array( 20.951, 107.080 ), 'sapa' => array( 22.336, 103.844 ), 'cantho' => array( 10.045, 105.747 ),
	'haiphong' => array( 20.845, 106.688 ), 'vinh' => array( 18.679, 105.682 ), 'buonmathuot' => array( 12.667, 108.038 ), 'tuyhoa' => array( 13.088, 109.316 ), 'donghoi' => array( 17.468, 106.622 ),
	'phongnha' => array( 17.590, 106.283 ), 'ninhbinh' => array( 20.251, 105.975 ), 'condao' => array( 8.686, 106.609 ) );
$km = function ( $a, $b ) { $r = M_PI / 180; $x = sin( ( $b[0] - $a[0] ) * $r / 2 ) ** 2 + cos( $a[0] * $r ) * cos( $b[0] * $r ) * sin( ( $b[1] - $a[1] ) * $r / 2 ) ** 2; return 6371 * 2 * atan2( sqrt( $x ), sqrt( 1 - $x ) ); };
$csv = fopen( '/home/customer/geo-backfill-20261018.csv', 'w' );
fputcsv( $csv, array( 'id', 'title', 'kind', 'query', 'lat', 'lng', 'osm_class', 'osm_type', 'km_from_city', 'found', 'accepted' ) );
$ids = get_posts( array( 'post_type' => array( 'objects', 'attractions', 'activities' ), 'post_status' => 'publish', 'numberposts' => -1, 'fields' => 'ids' ) );
$n = array( 'short_ok' => 0, 'short_fail' => 0, 'osm_ok' => 0, 'osm_check' => 0 );
foreach ( $ids as $id ) {
	$karta = trim( (string) get_post_meta( $id, 'karta', true ) );
	if ( '' === $karta || preg_match( '/^-?\d+\.\d+,-?\d+\.\d+$/', (string) get_post_meta( $id, '_lf_geo', true ) ) ) { continue; }
	$title = html_entity_decode( get_the_title( $id ), ENT_QUOTES, 'UTF-8' );
	if ( preg_match( '~goo\.gl|g\.page|maps\.app~', $karta ) ) {
		$geo = lf_schema_resolve_geo( $karta );
		$ok = (bool) $geo;
		$n[ $ok ? 'short_ok' : 'short_fail' ]++;
		fputcsv( $csv, array( $id, $title, 'short', $karta, $geo[0] ?? '', $geo[1] ?? '', '', '', '', $ok ? 1 : 0, $ok ? 1 : 0 ) );
		if ( $ok && $apply ) { lf_schema_update_geo( $id, true ); }
		continue;
	}
	parse_str( (string) wp_parse_url( $karta, PHP_URL_QUERY ), $q );
	$query = trim( (string) ( $q['query'] ?? $q['q'] ?? '' ) );
	if ( '' === $query ) { continue; }
	sleep( 1 ); // правила Nominatim: не чаще раза в секунду
	$res = wp_remote_get( 'https://nominatim.openstreetmap.org/search?' . http_build_query( array( 'q' => $query, 'format' => 'jsonv2', 'countrycodes' => 'vn', 'limit' => 1, 'addressdetails' => 1 ) ),
		array( 'timeout' => 15, 'user-agent' => 'LetFind geo backfill (https://letfind.me)' ) );
	$r = is_wp_error( $res ) ? null : ( json_decode( wp_remote_retrieve_body( $res ), true )[0] ?? null );
	$city = function_exists( 'lf_schema_city_key' ) ? (string) lf_schema_city_key( $id ) : '';
	$lat = $r ? (float) $r['lat'] : 0; $lng = $r ? (float) $r['lon'] : 0;
	$dist = ( $r && isset( $centers[ $city ] ) ) ? round( $km( $centers[ $city ], array( $lat, $lng ) ), 1 ) : '';
	$exact = $r && ( in_array( $r['category'] ?? '', array( 'amenity', 'shop', 'tourism', 'leisure', 'building', 'office' ), true ) || 'house' === ( $r['type'] ?? '' )
		|| ( preg_match( '/^(\d+[a-z]?)\s/i', $query, $hn ) && strtolower( $hn[1] ) === strtolower( (string) ( $r['address']['house_number'] ?? '' ) ) ) );
	$ok = $r && $exact && '' !== $dist && $dist <= 25;
	$n[ $ok ? 'osm_ok' : 'osm_check' ]++;
	fputcsv( $csv, array( $id, $title, 'search', $query, $r ? $lat : '', $r ? $lng : '', $r['category'] ?? '', $r['type'] ?? '', $dist, $r ? 1 : 0, $ok ? 1 : 0 ) );
	if ( $ok && $apply ) {
		update_post_meta( $id, '_lf_geo', $lat . ',' . $lng );
		update_post_meta( $id, '_lf_geo_src', $karta ); // чтобы сохранение карточки не стёрло точку: ссылка та же
		update_post_meta( $id, '_lf_geo_kind', 'osm' );
	}
}
fclose( $csv );
WP_CLI::log( ( $apply ? 'ЗАПИСАНО' : 'ПРОБНЫЙ ПРОГОН' ) . ': ' . wp_json_encode( $n ) . ' — CSV: /home/customer/geo-backfill-20261018.csv' );
