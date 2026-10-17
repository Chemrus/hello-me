#!/bin/bash
# Темы сайта (lf_theme) для 21 материала раздела «Города», 18.10.2026. wp post term set не пересохраняет запись — дата «Обновлено» не меняется.
set -u
U=--url=https://letfind.me
ids='16691,4798,4796,4779,4483,4149,4145,4115,4106,3925,3907,1779,1276,1274,1269,1257,1252,1055,1037,972,930'
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
after=$(wp db query "SELECT CONCAT(COUNT(*),'-',SUM(UNIX_TIMESTAMP(post_modified))) FROM $(wp db prefix $U)posts WHERE ID IN ($ids)" $U --skip-column-names)
[ "$before" = "$after" ] && echo "Даты 21 материала не сдвинулись — OK" || echo "ВНИМАНИЕ: даты сдвинулись ($before → $after)"
wp cache flush $U
echo "Сбросьте кэш SiteGround для /cities/."
