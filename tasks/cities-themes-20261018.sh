#!/bin/bash
# Темы сайта (lf_theme) для 38 материалов: 21 в «Городах», 9 в «Культуре», 7 новостей, 1 заметка, 18.10.2026. wp post term set не пересохраняет запись — дата «Обновлено» не меняется.
set -u
U=--url=https://letfind.me
ids='16691,4798,4796,4779,4483,4149,4145,4115,4106,3925,3907,1779,1276,1274,1269,1257,1252,1055,1037,972,930,18487,17615,17613,17611,17609,17607,17603,17601,17605,18397,17921,17849,17563,17491,17486,17474,18366'
before=$(wp db query "SELECT CONCAT(COUNT(*),'-',SUM(UNIX_TIMESTAMP(post_modified))) FROM $(wp db prefix $U)posts WHERE ID IN ($ids)" $U --skip-column-names)
wp post term set 16691 lf_theme relocation-city-choice --by=slug $U
wp post term set 4798 lf_theme relocation-city-choice --by=slug $U
wp post term set 4796 lf_theme relocation-city-choice --by=slug $U
wp post term set 4779 lf_theme relocation-city-choice --by=slug $U
wp post term set 4483 lf_theme relocation-city-choice --by=slug $U
wp post term set 4149 lf_theme relocation-city-choice --by=slug $U
wp post term set 4145 lf_theme relocation-city-choice --by=slug $U
wp post term set 4115 lf_theme relocation-city-choice --by=slug $U
wp post term set 4106 lf_theme relocation-city-choice --by=slug $U
wp post term set 3925 lf_theme relocation-city-choice --by=slug $U
wp post term set 3907 lf_theme relocation-city-choice --by=slug $U
wp post term set 1779 lf_theme travel-sights --by=slug $U
wp post term set 1276 lf_theme travel-sights --by=slug $U
wp post term set 1274 lf_theme travel-sights --by=slug $U
wp post term set 1269 lf_theme travel-sights --by=slug $U
wp post term set 1257 lf_theme travel-sights --by=slug $U
wp post term set 1252 lf_theme travel-sights --by=slug $U
wp post term set 1055 lf_theme travel-sights --by=slug $U
wp post term set 1037 lf_theme travel-sights --by=slug $U
wp post term set 972 lf_theme travel-sights --by=slug $U
wp post term set 930 lf_theme travel-sights --by=slug $U
wp post term set 18487 lf_theme culture-family --by=slug $U
wp post term set 17615 lf_theme culture-holidays --by=slug $U
wp post term set 17613 lf_theme culture-holidays --by=slug $U
wp post term set 17611 lf_theme culture-holidays --by=slug $U
wp post term set 17609 lf_theme culture-holidays --by=slug $U
wp post term set 17607 lf_theme culture-holidays --by=slug $U
wp post term set 17603 lf_theme culture-holidays --by=slug $U
wp post term set 17601 lf_theme culture-holidays --by=slug $U
wp post term set 17605 lf_theme culture-religion --by=slug $U
wp post term set 18397 lf_theme country-today-economy --by=slug $U
wp post term set 17921 lf_theme country-today --by=slug $U
wp post term set 17849 lf_theme country-today --by=slug $U
wp post term set 17563 lf_theme country-today --by=slug $U
wp post term set 17491 lf_theme country-today --by=slug $U
wp post term set 17486 lf_theme culture-holidays --by=slug $U
wp post term set 17474 lf_theme housing-buying --by=slug $U
wp post term set 18366 lf_theme money-banks --by=slug $U
after=$(wp db query "SELECT CONCAT(COUNT(*),'-',SUM(UNIX_TIMESTAMP(post_modified))) FROM $(wp db prefix $U)posts WHERE ID IN ($ids)" $U --skip-column-names)
[ "$before" = "$after" ] && echo "Даты 38 материалов не сдвинулись — OK" || echo "ВНИМАНИЕ: даты сдвинулись ($before → $after)"
wp cache flush $U
echo "Сбросьте кэш SiteGround для /cities/."
