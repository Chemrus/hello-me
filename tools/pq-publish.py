"""Публикация карточек мест острова Фукуок из черновиков (memory/pq-content.md).
python3 tools/pq-publish.py <папка черновиков> <id> [<id> …]
Берёт <id>.html (текст) и <id>.json (title, seo_title, seo_description); копию — в data/pq-cards/<id>.html."""
import json, os, shutil, subprocess, sys
sys.path.insert(0, os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
import wp
src = sys.argv[1]
for i in sys.argv[2:]:
    html = open(os.path.join(src, f'{i}.html')).read()
    meta = json.load(open(os.path.join(src, f'{i}.json')))
    data = {'content': html}
    if meta.get('title'):
        data['title'] = meta['title']
    r = wp.request('POST', f'wp/v2/attractions/{i}', data=data)
    shutil.copy(os.path.join(src, f'{i}.html'), f'data/pq-cards/{i}.html')
    json.dump(meta, open(f'data/pq-cards/{i}.json', 'w'), ensure_ascii=False, indent=1)
    if meta.get('seo_title') or meta.get('seo_description'):
        subprocess.run([sys.executable, 'wp.py', 'seo', str(i), '--type', 'attractions', '--title', meta.get('seo_title', ''), '--description', meta.get('seo_description', '')], check=True, capture_output=True)
    print(i, r['title']['rendered'], r['link'])
