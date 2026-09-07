# Claude in Chrome に渡す調査プロンプト

## これは何か

Claude in Chrome（Chrome拡張）は**別プロダクト**で、
Claude Code on the web（このセッション）からは呼び出せません。

ただし Claude in Chrome を使えば、**御社のブラウザから直接 o-snb.com を読める**ため、
このセッションが取得できない情報を代わりに集めてもらえます。

## 使い方

1. Chrome で **`https://o-snb.com` を開く**
2. Claude in Chrome のサイドパネルを開く
3. 下のプロンプトを **そのままコピーして貼り付け**
4. 出てきた結果を、このセッションのチャットに貼り戻す

---

## コピーして貼るプロンプト

```
このページ（o-snb.com）のSEO/LLMO診断をしてください。
Search Console で「ページにリダイレクトがあります」の警告が出ているサイトです。
推測は書かず、実際にページから読み取れた事実だけを報告してください。

以下をMarkdownで出力してください：

## 1. head
- title（文字数も）
- meta description（文字数も）
- link rel="canonical" の href。★複数ある場合は全部★
- meta robots の内容
- html の lang 属性

## 2. OGP
- og:title / og:description / og:image / og:url / og:type / twitter:card の有無と値

## 3. 見出し
- h1 の個数と全文
- h2 の全文（最大25個）
- h3 の個数

## 4. 構造化データ
- script[type="application/ld+json"] の個数
- 各スクリプトの @type 一覧
- 無い場合は「なし」と明記

## 5. WordPress構成
- ページ内の全 script/link の URL から /wp-content/themes/◯◯/ と
  /wp-content/plugins/◯◯/ を抽出し、テーマ名と全プラグインslugを列挙
- meta name="generator" の値

## 6. リンク（★最重要★）
ページ内の全 <a href> を調べ、次に分類して**実際のURLを列挙**してください：
- http:// で始まるリンク
- www.o-snb.com を指すリンク（正規は www なしの想定）
- 短縮URL（x.gd / bit.ly / lin.ee / t.co など）を使っているリンク
- o-snb.com 内部の通常リンク（全部）

## 7. 別ページの確認
次のURLを順に開いて、それぞれ「表示されたか」「中身は何か」を報告：
- https://o-snb.com/robots.txt
- https://o-snb.com/sitemap.xml
- https://o-snb.com/sitemap_index.xml
- https://o-snb.com/wp-sitemap.xml
- https://o-snb.com/llms.txt
※ WordPressは存在しないパスでも通常のHTMLページを返すことがあります。
  XMLやテキストではなく普通のページが表示された場合は「実体なし」と報告してください。

## 8. リダイレクトの実測
「6」で見つかった内部リンクのうち最大10件を実際に開き、
**アドレスバーの最終URLがクリック前のURLと違うもの**を、
「クリックしたURL → 最終URL」の形で列挙してください。
これが今回の警告の直接原因です。
```

---

## 補足

Claude in Chrome を使わない場合は、次のどちらかで同じ情報が取れます。

- **`tools/audit-console.js`** をブラウザのコンソールに貼る（最も正確・2分）
- **`Ctrl + U`（Mac: `⌘ + Option + U`）でソース表示 → 全選択コピー**（最も簡単。
  ただしリダイレクトの実測だけは取れません）
