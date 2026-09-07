/* ============================================================
 *  o-snb.com  SEO / LLMO 自動診断スクリプト
 * ------------------------------------------------------------
 *  【使い方】
 *   1. Chrome で https://o-snb.com を開く
 *   2. F12（Mac: ⌘ + Option + I）→ 上部タブ「Console / コンソール」
 *   3. 初回だけ、入力欄に  allow pasting  と打って Enter
 *      （Chromeの貼り付け防止を解除。これをしないと貼れません）
 *   4. このファイルの中身を全部コピーして貼り付け → Enter
 *   5. 5〜20秒待つと診断結果が表示され、自動でクリップボードにコピーされます
 *   6. そのままチャットに貼り付けてください
 *
 *  ※ 読み取りのみ。サイトを一切変更しません。
 * ========================================================== */
(async () => {
  const L = [];
  const p = (s) => L.push(s == null ? '' : String(s));
  const esc = (s) => String(s == null ? '' : s).replace(/\s+/g, ' ').trim();
  const cut = (s, n) => (esc(s).length > n ? esc(s).slice(0, n) + '…' : esc(s));
  const $$ = (sel) => Array.from(document.querySelectorAll(sel));

  p('# o-snb.com 診断レポート');
  p('');
  p('- 診断URL: ' + location.href);
  p('- 実行日時: ' + new Date().toISOString());
  p('');

  /* ---------- 1. head の基本タグ ---------- */
  p('## 1. head');
  p('');
  const title = document.title || '';
  p('- title (' + title.length + '文字): ' + JSON.stringify(cut(title, 200)));

  const md = document.querySelector('meta[name="description"]');
  p('- meta description: ' + (md
    ? '(' + esc(md.content).length + '文字) ' + JSON.stringify(cut(md.content, 300))
    : '★ なし'));

  const cans = $$('link[rel="canonical"]');
  if (cans.length === 0) {
    p('- canonical: ★ なし');
  } else {
    cans.forEach((c) => {
      const same = c.href.replace(/\/$/, '') === location.href.replace(/\/$/, '').split('#')[0];
      p('- canonical: ' + c.href + (same ? '  ✅一致' : '  ★現在のURLと不一致'));
    });
    if (cans.length > 1) p('  ★★ canonical が ' + cans.length + '個あります（重複出力＝プラグイン競合）');
  }

  const rb = document.querySelector('meta[name="robots"]');
  p('- meta robots: ' + (rb ? esc(rb.content) : '（指定なし＝index,follow）'));
  p('- html lang: ' + (document.documentElement.getAttribute('lang') || '★ なし'));
  p('- viewport: ' + (document.querySelector('meta[name="viewport"]') ? 'あり' : '★ なし'));

  const gen = $$('meta[name="generator"]').map((g) => esc(g.content));
  p('- generator: ' + (gen.length ? gen.join(' / ') : '（なし）'));
  p('');

  /* ---------- 2. OGP ---------- */
  p('## 2. OGP / Twitter Card');
  p('');
  ['og:title', 'og:description', 'og:image', 'og:url', 'og:type', 'og:site_name', 'twitter:card']
    .forEach((k) => {
      const el = document.querySelector('meta[property="' + k + '"], meta[name="' + k + '"]');
      p('- ' + k + ': ' + (el ? cut(el.content, 160) : '★ なし'));
    });
  p('');

  /* ---------- 3. 見出し構造 ---------- */
  p('## 3. 見出し構造');
  p('');
  const h1 = $$('h1');
  p('- h1: ' + h1.length + '個' + (h1.length === 1 ? ' ✅' : ' ★（1個が理想）'));
  h1.forEach((h) => p('  - ' + cut(h.textContent, 120)));
  const h2 = $$('h2');
  p('- h2: ' + h2.length + '個');
  h2.slice(0, 25).forEach((h) => p('  - ' + cut(h.textContent, 120)));
  if (h2.length > 25) p('  - …他 ' + (h2.length - 25) + '個');
  p('- h3: ' + $$('h3').length + '個');
  p('');

  /* ---------- 4. 構造化データ ---------- */
  p('## 4. 構造化データ (JSON-LD)');
  p('');
  const lds = $$('script[type="application/ld+json"]');
  if (!lds.length) {
    p('★ JSON-LD が1つもありません（LLMO・リッチリザルトの土台が無い状態）');
  } else {
    p('- スクリプト数: ' + lds.length);
    lds.forEach((s, i) => {
      try {
        const o = JSON.parse(s.textContent);
        const arr = Array.isArray(o) ? o : (o['@graph'] || [o]);
        const types = arr.map((x) => (x && x['@type']) || '?').flat();
        p('  - [' + (i + 1) + '] @type: ' + JSON.stringify(types));
      } catch (e) {
        p('  - [' + (i + 1) + '] ★ JSONパースエラー: ' + e.message);
      }
    });
  }
  p('');

  /* ---------- 5. WordPress テーマ / プラグイン検出 ---------- */
  p('## 5. WordPress 構成（HTMLから検出）');
  p('');
  const assets = $$('script[src], link[href]')
    .map((e) => e.src || e.href).filter(Boolean);
  const plugins = new Set();
  const themes = new Set();
  assets.forEach((u) => {
    let m = u.match(/\/wp-content\/plugins\/([^/?#]+)/);
    if (m) plugins.add(m[1]);
    m = u.match(/\/wp-content\/themes\/([^/?#]+)/);
    if (m) themes.add(m[1]);
  });
  p('- テーマ: ' + (themes.size ? Array.from(themes).join(', ') : '（検出できず）'));
  p('- プラグイン (' + plugins.size + '件): ' +
    (plugins.size ? Array.from(plugins).sort().join(', ') : '（検出できず）'));

  const html = document.documentElement.innerHTML;
  const has = (needle) => plugins.has(needle) || html.includes(needle);
  const seoSigns = [];
  if (has('wordpress-seo') || has('yoast')) seoSigns.push('Yoast SEO');
  if (has('seo-by-rank-math') || has('rank-math') || has('rankmath')) seoSigns.push('Rank Math');
  if (has('all-in-one-seo-pack') || has('aioseo')) seoSigns.push('All in One SEO');
  if (has('seo-simple-pack')) seoSigns.push('SEO SIMPLE PACK');
  const sslSigns = [];
  if (has('really-simple-ssl')) sslSigns.push('Really Simple SSL');
  if (has('ssl-insecure-content-fixer')) sslSigns.push('SSL Insecure Content Fixer');
  const cacheSigns = ['wp-rocket', 'w3-total-cache', 'wp-super-cache', 'litespeed-cache',
    'wp-fastest-cache', 'autoptimize'].filter(has);
  p('- SEOプラグインの痕跡: ' + (seoSigns.length ? seoSigns.join(', ') : '（検出できず＝未導入の可能性）'));
  if (seoSigns.length > 1) p('  ★★ SEOプラグインが複数動いています（canonical重複の原因）');
  p('- SSL化プラグイン: ' + (sslSigns.length ? sslSigns.join(', ') + '  ※.htaccessと二重転送になっていないか要確認' : '（検出できず）'));
  p('- キャッシュ系: ' + (cacheSigns.length ? cacheSigns.join(', ') +
    (cacheSigns.length > 1 ? '  ★★ 複数稼働は競合します' : '') : '（検出できず）'));
  p('');

  /* ---------- 6. 同一オリジンのファイル存在確認 ---------- */
  p('## 6. robots.txt / sitemap / llms.txt');
  p('');
  const files = ['/robots.txt', '/sitemap.xml', '/sitemap_index.xml', '/wp-sitemap.xml', '/llms.txt'];
  for (const f of files) {
    try {
      const r = await fetch(f, { redirect: 'follow' });
      const ct = r.headers.get('content-type') || '';
      const t = r.ok ? await r.text() : '';
      // WordPress は存在しないパスでも 200 で HTML を返すことがあるため、
      // 中身がHTMLなら「そのファイルは存在しない」と判定する
      const isHtml = /text\/html/i.test(ct) || /^\s*<!DOCTYPE html|^\s*<html/i.test(t);
      p('### ' + f + '  → ' + r.status +
        (r.redirected ? ' (転送あり → ' + r.url + ')' : '') +
        (r.ok && isHtml ? '  ★実体なし（HTMLが返っています）' : ''));
      if (r.ok && t && !isHtml) {
        p('```');
        p(t.split('\n').slice(0, 20).join('\n').slice(0, 1500));
        p('```');
      }
    } catch (e) {
      p('### ' + f + '  → 取得失敗: ' + e.message);
    }
    p('');
  }

  /* ---------- 7. ★リダイレクト実測★ ---------- */
  p('## 7. 内部リンクのリダイレクト実測');
  p('');
  const anchors = $$('a[href]').map((a) => a.href).filter(Boolean);

  // 危険なリンク形式（Search Console の「リダイレクトがあります」の主因）
  const risky = { http: [], www: [], shortener: [] };
  const SHORT = /(^|\.)(x\.gd|bit\.ly|t\.co|lin\.ee|is\.gd|tinyurl\.com|ur0\.cc|onl\.bz)$/i;
  anchors.forEach((u) => {
    try {
      const x = new URL(u);
      if (x.protocol === 'http:') risky.http.push(u);
      if (/^www\./i.test(x.hostname) && x.hostname.replace(/^www\./i, '') === location.hostname) risky.www.push(u);
      if (SHORT.test(x.hostname)) risky.shortener.push(u);
    } catch (e) { /* ignore */ }
  });
  const uniq = (a) => Array.from(new Set(a));
  if (location.protocol !== 'https:') {
    p('★★ このページ自体が https ではありません（' + location.protocol + '）。');
    p('   まずSSL化が必要です。http内部リンクの一覧はここでは省略します。');
  } else {
    p('- http:// のリンク: ' + uniq(risky.http).length + '件' +
      (uniq(risky.http).length ? '  ★要修正' : ' ✅'));
    uniq(risky.http).slice(0, 10).forEach((u) => p('  - ' + u));
  }
  p('- www 付きの自サイトリンク: ' + uniq(risky.www).length + '件' +
    (uniq(risky.www).length ? '  ★要修正' : ' ✅'));
  uniq(risky.www).slice(0, 10).forEach((u) => p('  - ' + u));
  p('- 短縮URLのリンク: ' + uniq(risky.shortener).length + '件' +
    (uniq(risky.shortener).length ? '  ★サイト内リンクには使わない' : ' ✅'));
  uniq(risky.shortener).slice(0, 10).forEach((u) => p('  - ' + u));
  p('');

  // 同一オリジンリンクを実際に叩いて転送されるか測る
  const same = uniq(anchors.filter((u) => {
    try {
      const x = new URL(u);
      return x.origin === location.origin && !/\.(jpg|jpeg|png|gif|webp|svg|pdf|zip)$/i.test(x.pathname);
    } catch (e) { return false; }
  })).slice(0, 30);

  p('- 実測対象: 同一オリジンリンク ' + same.length + '件（最大30件）');
  p('');
  const redirected = [];
  const broken = [];
  for (const u of same) {
    try {
      const r = await fetch(u, { redirect: 'follow' });
      if (r.status >= 400) broken.push(u + '  → ' + r.status);
      else if (r.redirected && r.url.split('#')[0] !== u.split('#')[0]) {
        redirected.push(u + '\n      → ' + r.url);
      }
    } catch (e) {
      broken.push(u + '  → 取得失敗 (' + e.message + ')');
    }
  }
  p('### 転送されたリンク: ' + redirected.length + '件' + (redirected.length ? '  ★これが原因' : ' ✅'));
  redirected.forEach((x) => p('  - ' + x));
  p('');
  p('### エラーになったリンク: ' + broken.length + '件');
  broken.forEach((x) => p('  - ' + x));
  p('');

  /* ---------- 8. 画像・本文 ---------- */
  p('## 8. 画像とコンテンツ量');
  p('');
  const imgs = $$('img');
  const noAlt = imgs.filter((i) => !i.getAttribute('alt'));
  const noDim = imgs.filter((i) => !i.getAttribute('width') || !i.getAttribute('height'));
  p('- 画像: ' + imgs.length + '枚');
  p('- alt 未設定: ' + noAlt.length + '枚' + (noAlt.length ? '  ★' : ' ✅'));
  p('- width/height 未指定: ' + noDim.length + '枚' + (noDim.length ? '  ★CLS悪化の原因' : ' ✅'));
  const bodyText = esc(document.body.innerText || '');
  p('- 本文の文字数（概算）: ' + bodyText.length + '文字');
  p('');

  /* ---------- 出力 ---------- */
  const out = L.join('\n');
  console.log(out);
  try {
    if (typeof copy === 'function') {
      copy(out);
      console.log('%c✅ 診断結果をクリップボードにコピーしました。そのままチャットに貼り付けてください。',
        'color:#0E9D78;font-size:14px;font-weight:bold');
    } else {
      await navigator.clipboard.writeText(out);
      console.log('%c✅ クリップボードにコピーしました。', 'color:#0E9D78;font-size:14px;font-weight:bold');
    }
  } catch (e) {
    console.log('%c↑ 上の出力を手動で選択してコピーしてください。', 'color:#D9871C;font-size:14px;font-weight:bold');
  }
})();
