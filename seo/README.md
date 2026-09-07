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
| 0 | [`tools/audit-console.js`](tools/audit-console.js) | **★まずこれ★** ブラウザに貼るだけで自動診断。結果をチャットに戻せば実物ベースの指摘ができます | 2分 |
| 0b | [`tools/claude-in-chrome-prompt.md`](tools/claude-in-chrome-prompt.md) | Claude in Chrome（Chrome拡張）に同じ調査をさせるためのプロンプト | 5分 |
| 0c | [`00-collect-data.md`](00-collect-data.md) | 自動診断が使えない場合の、手作業でのデータ収集手順 | 10分 |
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

---

## 検証状況

`wordpress/functions-seo.php` は **実際に実行して出力を検証済み** です。

```bash
cd seo/wordpress/tests && python3 verify.py
```

`tests/harness.php` が WordPress の関数を最小限スタブして `functions-seo.php` を
実行し、`verify.py` がその出力を構造レベルで検証します（WordPress本体は不要）。

検証している内容（20項目・全通過）：

| シナリオ | 確認内容 |
|---|---|
| トップページ | Organization + WebSite のみ出力／SearchAction あり／canonicalが1つだけ |
| 投稿記事 | BlogPosting + BreadcrumbList／著者がPerson型／公開日と更新日が別／パンくず3階層の連番／canonicalが記事の最終URL |
| FAQ固定ページ | FAQPage が Question/Answer 型で出力 |
| Yoast稼働中 | **一切出力しない**（canonical・構造化データの二重出力を防止） |

`tools/audit-console.js` も Chromium 上で実動作を確認済みです
（301リンク・404リンク・短縮URL・実体のないサイトマップ・プラグインslugの検出）。

> ⚠️ 検証したのは「コードが仕様どおり動くこと」までです。
> **o-snb.com の実サイトに適用した状態は未確認**であり、テーマとの相性は
> ステージング環境での確認が必要です。
