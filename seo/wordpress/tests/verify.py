#!/usr/bin/env python3
"""
functions-seo.php の出力を検証する。

  cd seo/wordpress/tests && python3 verify.py

harness.php が WordPress の関数を最小限スタブし、functions-seo.php を
実際に実行して wp_head の出力を取り出す。このスクリプトはその出力の
JSON-LD と meta タグを構造レベルで検証する。WordPress本体は不要。
"""
import subprocess, json, re, sys, os

os.chdir(os.path.dirname(os.path.abspath(__file__)))

run   = lambda s: subprocess.run(['php', 'harness.php', s], capture_output=True, text=True).stdout
types = lambda g: [x['@type'] for x in g['@graph']]
node  = lambda g, t: next((x for x in g['@graph'] if x['@type'] == t), None)
canon = lambda o: re.findall(r'<link rel="canonical" href="([^"]*)"', o)


def ld(out):
    m = re.search(r'<script type="application/ld\+json">(.*?)</script>', out, re.S)
    return json.loads(m.group(1)) if m else None


def meta(out, k):
    m = re.search(r'<meta (?:property|name)="%s" content="([^"]*)"' % re.escape(k), out)
    return m.group(1) if m else None


fails = []


def ck(cond, msg):
    print(('  OK   ' if cond else '  FAIL ') + msg)
    if not cond:
        fails.append(msg)


print('[front] トップページ')
o = run('front'); g = ld(o)
ck(types(g) == ['Organization', 'WebSite'], 'Organization + WebSite のみ（トップにパンくずは出さない）')
ck(node(g, 'WebSite').get('potentialAction', {}).get('@type') == 'SearchAction', 'WebSite に SearchAction')
ck(len(node(g, 'Organization')['sameAs']) == 3, 'sameAs が3件')
ck(canon(o) == ['https://o-snb.com/'], 'canonical が1つだけ・トップURL')
ck(meta(o, 'og:type') == 'website', 'og:type = website')
ck(meta(o, 'og:locale') == 'ja_JP', 'og:locale = ja_JP')
ck(meta(o, 'description') is not None, 'meta description あり')

print('[post] 投稿記事')
o = run('post'); g = ld(o)
ck('BlogPosting' in types(g) and 'BreadcrumbList' in types(g), 'BlogPosting と BreadcrumbList')
a = node(g, 'BlogPosting')
ck(a['author']['@type'] == 'Person' and a['author']['name'] == '及川 志伸', '著者が Person（E-E-A-T）')
ck(a['datePublished'] != a['dateModified'], '公開日と更新日が別々')
ck(a['image']['width'] == 1200, 'アイキャッチが寸法つき')
ck(a['publisher'] == {'@id': 'https://o-snb.com/#organization'}, 'publisher が Organization を参照')
b = node(g, 'BreadcrumbList')['itemListElement']
ck([x['name'] for x in b] == ['ホーム', 'マーケティング', 'サンプル記事のタイトル'], 'パンくず3階層')
ck([x['position'] for x in b] == [1, 2, 3], 'position が連番')
ck(canon(o) == ['https://o-snb.com/blog/sample-post/'], 'canonical = 記事の最終URL')
ck(meta(o, 'og:type') == 'article', 'og:type = article')

print('[faq] FAQ固定ページ')
o = run('faq'); g = ld(o)
f = node(g, 'FAQPage')
ck(f is not None, 'FAQPage が出る')
ck(f and len(f['mainEntity']) == 2, '質問が2件')
ck(f and f['mainEntity'][0]['acceptedAnswer']['@type'] == 'Answer', '回答が Answer 型')

print('[plugin] Yoast 稼働中')
ck(run('plugin').strip() == '', 'SEOプラグインがある時は一切出力しない')

print()
if fails:
    print('FAILED: %d件' % len(fails))
    sys.exit(1)
print('PASSED: 全チェック通過')
