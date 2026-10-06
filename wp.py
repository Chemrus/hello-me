"""Публикация и обновление записей на WordPress-сайте через REST API.

Доступ берётся из переменных окружения:
  WP_URL           адрес сайта (по умолчанию https://letfind.me)
  WP_USER          логин пользователя WordPress
  WP_APP_PASSWORD  пароль приложения (Пользователи → Профиль → Пароли приложений)

Примеры:
  python wp.py check
  python wp.py types
  python wp.py list --type articles -n 5
  python wp.py create --type articles --title "Заголовок" --content-file post.html
  python wp.py update 123 --type articles --status publish
"""

import argparse
import base64
import json
import os
import sys
import time
import urllib.error
import urllib.parse
import urllib.request

DEFAULT_URL = "https://letfind.me"
USER_AGENT = "hello-me-wp-client/1.0"
RETRY_DELAYS = [2, 5, 10]


class WPError(Exception):
    pass


class CaptchaError(WPError):
    pass


def site_url():
    return os.environ.get("WP_URL", DEFAULT_URL).rstrip("/")


def auth_header():
    user = os.environ.get("WP_USER")
    password = os.environ.get("WP_APP_PASSWORD")
    if not user or not password:
        raise WPError(
            "Не заданы переменные окружения WP_USER и WP_APP_PASSWORD. "
            "Добавьте их в настройках окружения и откройте новую сессию."
        )
    token = base64.b64encode(f"{user}:{password}".encode()).decode()
    return f"Basic {token}"


def request(method, path, params=None, data=None, auth=True):
    # Антибот-защита SiteGround иногда отвечает капчей вместо JSON —
    # такие запросы повторяем с паузой.
    for delay in RETRY_DELAYS:
        try:
            return send(method, path, params, data, auth)
        except CaptchaError:
            time.sleep(delay)
    return send(method, path, params, data, auth)


def send(method, path, params, data, auth):
    url = f"{site_url()}/wp-json/{path.lstrip('/')}"
    if params:
        url += "?" + urllib.parse.urlencode(params)
    headers = {"User-Agent": USER_AGENT, "Accept": "application/json"}
    if auth:
        headers["Authorization"] = auth_header()
    body = None
    if data is not None:
        body = json.dumps(data).encode()
        headers["Content-Type"] = "application/json"

    req = urllib.request.Request(url, data=body, headers=headers, method=method)
    try:
        with urllib.request.urlopen(req, timeout=30) as resp:
            text = resp.read().decode()
    except urllib.error.HTTPError as e:
        text = e.read().decode(errors="replace")
        try:
            err = json.loads(text)
            message = f"{err.get('message', text)} [{err.get('code')}]"
        except ValueError:
            message = text[:300]
        raise WPError(f"{method} {url} → HTTP {e.code}: {message}") from None

    if "sgcaptcha" in text:
        raise CaptchaError(
            "Запрос перехватила антибот-защита хостинга (SiteGround captcha). "
            "Повторите позже или разрешите доступ к /wp-json/ в SiteGround Site Tools."
        )
    try:
        return json.loads(text)
    except ValueError:
        raise WPError(f"{method} {url}: ответ не JSON: {text[:300]}") from None


def read_content(args):
    if args.content_file:
        with open(args.content_file, encoding="utf-8") as f:
            return f.read()
    return args.content


def post_fields(args):
    fields = {}
    if args.title is not None:
        fields["title"] = args.title
    content = read_content(args)
    if content is not None:
        fields["content"] = content
    if args.excerpt is not None:
        fields["excerpt"] = args.excerpt
    if args.status is not None:
        fields["status"] = args.status
    if args.slug is not None:
        fields["slug"] = args.slug
    return fields


def print_post(post):
    title = post.get("title", {}).get("rendered", "")
    print(f"{post['id']}\t{post.get('status', '')}\t{post.get('link', '')}\t{title}")


def cmd_check(args):
    me = request("GET", "wp/v2/users/me", {"context": "edit"})
    print(f"Подключено к {site_url()} как {me.get('name')} (логин {me.get('username')})")
    print("Роли:", ", ".join(me.get("roles", [])))


def cmd_types(args):
    types = request("GET", "wp/v2/types", auth=False)
    for slug, t in types.items():
        print(f"{t['rest_base']}\t{t['name']}")


def cmd_list(args):
    params = {"per_page": args.n, "context": "edit", "status": args.status or "any"}
    for post in request("GET", f"wp/v2/{args.type}", params):
        print_post(post)


def cmd_get(args):
    post = request("GET", f"wp/v2/{args.type}/{args.id}", {"context": "edit"})
    print(json.dumps(post, ensure_ascii=False, indent=2))


def cmd_create(args):
    fields = post_fields(args)
    if "title" not in fields:
        raise WPError("Для новой записи нужен --title")
    fields.setdefault("status", "draft")
    print_post(request("POST", f"wp/v2/{args.type}", data=fields))


def cmd_update(args):
    fields = post_fields(args)
    if not fields:
        raise WPError("Нечего обновлять: укажите --title, --content, --status и т.д.")
    print_post(request("POST", f"wp/v2/{args.type}/{args.id}", data=fields))


def main():
    parser = argparse.ArgumentParser(description="Работа с записями WordPress через REST API")
    sub = parser.add_subparsers(dest="command", required=True)

    sub.add_parser("check", help="проверить логин и пароль приложения").set_defaults(func=cmd_check)
    sub.add_parser("types", help="показать типы записей сайта").set_defaults(func=cmd_types)

    def add_type(p):
        p.add_argument("--type", default="posts", help="тип записи, например articles или news (по умолчанию posts)")

    def add_fields(p):
        p.add_argument("--title")
        content = p.add_mutually_exclusive_group()
        content.add_argument("--content", help="текст записи (HTML)")
        content.add_argument("--content-file", help="файл с текстом записи (HTML)")
        p.add_argument("--excerpt")
        p.add_argument("--slug")
        p.add_argument("--status", choices=["draft", "pending", "private", "publish", "future"])

    p = sub.add_parser("list", help="последние записи")
    add_type(p)
    p.add_argument("-n", type=int, default=10, help="сколько записей показать")
    p.add_argument("--status", help="фильтр по статусу (по умолчанию все)")
    p.set_defaults(func=cmd_list)

    p = sub.add_parser("get", help="запись целиком в JSON")
    p.add_argument("id", type=int)
    add_type(p)
    p.set_defaults(func=cmd_get)

    p = sub.add_parser("create", help="создать запись (по умолчанию черновик)")
    add_type(p)
    add_fields(p)
    p.set_defaults(func=cmd_create)

    p = sub.add_parser("update", help="обновить запись")
    p.add_argument("id", type=int)
    add_type(p)
    add_fields(p)
    p.set_defaults(func=cmd_update)

    args = parser.parse_args()
    try:
        args.func(args)
    except WPError as e:
        print(f"Ошибка: {e}", file=sys.stderr)
        sys.exit(1)


if __name__ == "__main__":
    main()
