# SEO / LLMO 対応キット（WordPress向け）

Search Console の「**ページにリダイレクトがあります**」通知への対応と、
検索エンジン（SEO）＋生成AI（LLMO）の両方で拾われるための実装一式です。

> このリポジトリ本体（`index.html`）は社内KPIダッシュボードであり、
> 公開ホームページとは別物です。ダッシュボードには `noindex` を入れて
> 検索結果に出ないようにしてあります。以下は **WordPress で構築された
> 自社ホームページ側** に適用する内容です。

---

## 使う順番

| 手順 | ファイル | 内容 | 所要 |
|---|---|---|---|
| 0 | [`00-collect-data.md`](00-collect-data.md) | 現状データの集め方（これを貼れば実物ベースで診断できます） | 10分 |
| 1 | [`01-redirect-diagnosis.md`](01-redirect-diagnosis.md) | 今回の警告が「直すべきもの」か「放置してよいもの」かを切り分ける | 30分 |
| 2 | [`wordpress/htaccess-canonical.txt`](wordpress/htaccess-canonical.txt) | URLの正規化（http/https・www有無・重複解消） | 15分 |
| 3 | [`wordpress/robots.txt`](wordpress/robots.txt) | 検索クローラ＋AIクローラの通行許可 | 5分 |
| 4 | [`wordpress/functions-seo.php`](wordpress/functions-seo.php) | 構造化データ・メタ情報の出力 | 30分 |
| 5 | [`wordpress/llms.txt`](wordpress/llms.txt) | 生成AIに読ませる自社サマリー | 30分 |
| 6 | [`02-seo-llmo-playbook.md`](02-seo-llmo-playbook.md) | コンテンツ側で順位を取るための実務手順 | 継続 |

---

## 最初にやること（共通の前提）

ドメインは **`https://o-snb.com`** で全ファイル設定済みです。
残る置換は次の1点だけです。

```
株式会社サンプル  →  正式な会社名（登記名）
```

**未確定：www の有無**
`www` あり／なしはどちらでも構いませんが、「**どちらか一方に決めて全体で統一する**」
ことが必須です。現状 `https://o-snb.com`（www なし）を正としてありますが、
ブラウザで `https://o-snb.com` を開いてアドレスバーに `www.` が付くようなら、
`www あり` が正です。その場合は次の2ファイルをパターンB側に切り替えてください。

- `wordpress/htaccess-canonical.txt` … 【1】のパターンBを有効化
- `wordpress/functions-seo.php` … `'url'` を `https://www.o-snb.com` に

---

## 免責

`functions-seo.php` は汎用テンプレートです。お使いのテーマ・SEOプラグインとの
組み合わせは実環境でしか検証できないため、**必ずステージング環境か、
子テーマ＋バックアップを取った上で**適用してください。
既存SEOプラグインとの二重出力を避けるガード（`SITE_SEO_ENABLE`）を入れてあります。
