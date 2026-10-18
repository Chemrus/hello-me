<?php
/**
 * Координаты мест из ссылок на карту (задание 26). Запуск:
 *   wp eval-file geo-backfill-20261018.php --url=https://letfind.me            — пробный прогон: ничего не пишет, CSV в /home/customer/geo-backfill-20261018.csv
 *   wp eval-file geo-backfill-20261018.php apply --url=https://letfind.me      — записать то, что прошло проверку
 * 1) Короткие ссылки maps.app.goo.gl — раскрываются тем же кодом темы, что при сохранении карточки (lf_schema_update_geo).
 * 2) Ссылки-поиски google.com/maps/search/?query=… — геокодер OpenStreetMap (Nominatim, не чаще 1 запроса в секунду):
 *    сначала «название, город» (как ищет человек), потом адрес из ссылки.
 *    Принимаем только точный результат: заведение/дом (amenity, shop, tourism, leisure, building, office, place=house)
 *    или совпал номер дома из запроса, и точка не дальше 25 км от центра города карточки. Остальное — в CSV для ручной проверки.
 */
$apply = in_array( 'apply', (array) ( $args ?? array() ), true );
$centers = array( 'danang' => array( 16.054, 108.202 ), 'hoian' => array( 15.880, 108.338 ), 'phuquoc' => array( 10.227, 103.963 ), 'nhatrang' => array( 12.238, 109.196 ),
	'hochiminh' => array( 10.776, 106.700 ), 'hanoi' => array( 21.028, 105.854 ), 'dalat' => array( 11.940, 108.458 ), 'muine' => array( 10.933, 108.287 ), 'vungtau' => array( 10.346, 107.084 ),
	'hue' => array( 16.463, 107.590 ), 'quynhon' => array( 13.782, 109.219 ), 'halong' => array( 20.951, 107.080 ), 'sapa' => array( 22.336, 103.844 ), 'cantho' => array( 10.045, 105.747 ),
	'haiphong' => array( 20.845, 106.688 ), 'vinh' => array( 18.679, 105.682 ), 'buonmathuot' => array( 12.667, 108.038 ), 'tuyhoa' => array( 13.088, 109.316 ), 'donghoi' => array( 17.468, 106.622 ),
	'phongnha' => array( 17.590, 106.283 ), 'ninhbinh' => array( 20.251, 105.975 ), 'condao' => array( 8.686, 106.609 ) );
$city_names = array( 'danang' => 'Da Nang', 'hoian' => 'Hoi An', 'phuquoc' => 'Phu Quoc', 'nhatrang' => 'Nha Trang', 'hochiminh' => 'Ho Chi Minh City', 'hanoi' => 'Hanoi', 'dalat' => 'Da Lat',
	'muine' => 'Mui Ne', 'vungtau' => 'Vung Tau', 'hue' => 'Hue', 'quynhon' => 'Quy Nhon', 'halong' => 'Ha Long', 'sapa' => 'Sa Pa', 'cantho' => 'Can Tho', 'haiphong' => 'Hai Phong', 'vinh' => 'Vinh',
	'buonmathuot' => 'Buon Ma Thuot', 'tuyhoa' => 'Tuy Hoa', 'donghoi' => 'Dong Hoi', 'phongnha' => 'Phong Nha', 'ninhbinh' => 'Ninh Binh', 'condao' => 'Con Dao' );
$km = function ( $a, $b ) { $r = M_PI / 180; $x = sin( ( $b[0] - $a[0] ) * $r / 2 ) ** 2 + cos( $a[0] * $r ) * cos( $b[0] * $r ) * sin( ( $b[1] - $a[1] ) * $r / 2 ) ** 2; return 6371 * 2 * atan2( sqrt( $x ), sqrt( 1 - $x ) ); };
$csv = fopen( '/home/customer/geo-backfill-20261018.csv', 'w' );
fputcsv( $csv, array( 'id', 'title', 'kind', 'query', 'found_by', 'lat', 'lng', 'osm_class', 'osm_type', 'osm_name', 'km_from_city', 'found', 'accepted' ) );
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
		fputcsv( $csv, array( $id, $title, 'short', $karta, 'link', $geo[0] ?? '', $geo[1] ?? '', '', '', '', '', $ok ? 1 : 0, $ok ? 1 : 0 ) );
		if ( $ok && $apply ) { lf_schema_update_geo( $id, true ); }
		continue;
	}
	parse_str( (string) wp_parse_url( $karta, PHP_URL_QUERY ), $q );
	$query = trim( (string) ( $q['query'] ?? $q['q'] ?? '' ) );
	if ( '' === $query ) { continue; }
	$city = function_exists( 'lf_schema_city_key' ) ? (string) lf_schema_city_key( $id ) : '';
	// 1) название + город (как ищет человек), 2) адрес из ссылки. Название: латинская часть в скобках, если есть («Пляж Сао (Bãi Sao)» → Bãi Sao).
	$name = preg_match( '/\(([^()]*[a-z][^()]*)\)/iu', $title, $pm ) ? $pm[1] : $title;
	$tries = array();
	if ( isset( $city_names[ $city ] ) ) { $tries[] = array( 'name', trim( $name ) . ', ' . $city_names[ $city ] ); }
	$tries[] = array( 'address', $query );
	$r = null; $how = ''; $lat = 0; $lng = 0; $dist = ''; $exact = false; $ok = false;
	foreach ( $tries as $t ) {
		sleep( 1 ); // правила Nominatim: не чаще раза в секунду
		$res = wp_remote_get( 'https://nominatim.openstreetmap.org/search?' . http_build_query( array( 'q' => $t[1], 'format' => 'jsonv2', 'countrycodes' => 'vn', 'limit' => 1, 'addressdetails' => 1 ) ),
			array( 'timeout' => 15, 'user-agent' => 'LetFind geo backfill (https://letfind.me)' ) );
		$cand = is_wp_error( $res ) ? null : ( json_decode( wp_remote_retrieve_body( $res ), true )[0] ?? null );
		if ( ! $cand ) { continue; }
		$clat = (float) $cand['lat']; $clng = (float) $cand['lon'];
		$cdist = isset( $centers[ $city ] ) ? round( $km( $centers[ $city ], array( $clat, $clng ) ), 1 ) : '';
		$cexact = in_array( $cand['category'] ?? '', array( 'amenity', 'shop', 'tourism', 'leisure', 'building', 'office', 'natural' ), true ) || 'house' === ( $cand['type'] ?? '' )
			|| ( 'address' === $t[0] && preg_match( '/^(\d+[a-z]?)\s/i', $query, $hn ) && strtolower( $hn[1] ) === strtolower( (string) ( $cand['address']['house_number'] ?? '' ) ) );
		// по названию — только если найденное место называется так же (сети вроде Highlands Coffee есть на каждой улице: тогда в CSV на проверку)
		if ( 'name' === $t[0] ) {
			similar_text( mb_strtolower( remove_accents( (string) ( $cand['name'] ?? '' ) ) ), mb_strtolower( remove_accents( $name ) ), $pct );
			$cexact = $cexact && $pct >= 70;
		}
		$cok = $cexact && '' !== $cdist && $cdist <= 25;
		if ( ! $r || $cok ) { $r = $cand; $how = $t[0]; $lat = $clat; $lng = $clng; $dist = $cdist; $exact = $cexact; $ok = $cok; }
		if ( $cok ) { break; }
	}
	$n[ $ok ? 'osm_ok' : 'osm_check' ]++;
	fputcsv( $csv, array( $id, $title, 'search', $query, $how, $r ? $lat : '', $r ? $lng : '', $r['category'] ?? '', $r['type'] ?? '', $r['display_name'] ?? '', $dist, $r ? 1 : 0, $ok ? 1 : 0 ) );
	if ( $ok && $apply ) {
		update_post_meta( $id, '_lf_geo', $lat . ',' . $lng );
		update_post_meta( $id, '_lf_geo_src', $karta ); // чтобы сохранение карточки не стёрло точку: ссылка та же
		update_post_meta( $id, '_lf_geo_kind', 'osm' );
	}
}
fclose( $csv );
WP_CLI::log( ( $apply ? 'ЗАПИСАНО' : 'ПРОБНЫЙ ПРОГОН' ) . ': ' . wp_json_encode( $n ) . ' — CSV: /home/customer/geo-backfill-20261018.csv' );
