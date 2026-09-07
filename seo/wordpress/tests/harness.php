<?php
/* WordPress の最小スタブ。functions-seo.php を実際に実行して出力を検証する */
$SCENARIO = $argv[1] ?? 'front';
define('ABSPATH', '/wp/');
if ($SCENARIO === 'plugin') { define('WPSEO_VERSION', '22.0'); }

$GLOBALS['__actions'] = [];
function add_action($hook, $fn, $prio = 10, $n = 1) { $GLOBALS['__actions'][] = [$hook, $fn, $prio]; }
function do_wp_head() {
    $a = $GLOBALS['__actions'];
    usort($a, fn($x, $y) => $x[2] <=> $y[2]);
    ob_start();
    foreach ($a as [$hook, $fn, $p]) { if ($hook === 'wp_head') { $fn(); } }
    return ob_get_clean();
}

$S = $GLOBALS['__s'] = $SCENARIO;
function is_404()   { return false; }
function is_search() { return false; }
function is_front_page() { return $GLOBALS['__s'] === 'front'; }
function is_singular($t = null) {
    if ($GLOBALS['__s'] === 'post')  { return $t === null || $t === 'post'; }
    if ($GLOBALS['__s'] === 'faq')   { return $t === null || $t === 'page'; }
    return false;
}
function is_page()      { return $GLOBALS['__s'] === 'faq'; }
function is_category()  { return false; }
function is_tag()       { return false; }
function is_tax()       { return false; }
function is_post_type_archive() { return false; }
function is_attachment(){ return false; }
function is_author()    { return false; }
function is_date()      { return false; }
function is_wp_error($x){ return false; }

function home_url($p = '/') { return 'https://o-snb.com' . $p; }
function get_permalink()    { return $GLOBALS['__s'] === 'post'
    ? 'https://o-snb.com/blog/sample-post/' : 'https://o-snb.com/faq/'; }
function get_the_title()    { return $GLOBALS['__s'] === 'post' ? 'サンプル記事のタイトル' : 'よくあるご質問'; }
function get_the_excerpt()  { return 'これは抜粋文です。テストのための説明文が入ります。'; }
function get_the_date($f)   { return '2026-01-15T10:00:00+09:00'; }
function get_the_modified_date($f) { return '2026-03-02T12:30:00+09:00'; }
function has_post_thumbnail() { return true; }
function get_post_thumbnail_id() { return 42; }
function wp_get_attachment_image_src($id, $sz) { return ['https://o-snb.com/wp-content/uploads/thumb.jpg', 1200, 630]; }
function get_the_author_meta($k, $id) { return '及川 志伸'; }
function get_author_posts_url($id) { return 'https://o-snb.com/author/admin/'; }
function get_the_category() { $o = new stdClass; $o->name = 'マーケティング'; $o->term_id = 7; return [$o]; }
function get_category_link($id) { return 'https://o-snb.com/category/marketing/'; }
function get_queried_object() { return null; }
function get_queried_object_id() { return 99; }
function get_post_field($f, $id) { return 'faq'; }
function get_term_link($t) { return ''; }
function get_post_type_archive_link($t) { return ''; }
function get_post_type() { return 'post'; }
function wp_get_document_title() { return 'アーカイブ | サンプル'; }
function wp_strip_all_tags($s) { return strip_tags((string) $s); }
function esc_url($s)  { return htmlspecialchars($s, ENT_QUOTES); }
function esc_attr($s) { return htmlspecialchars($s, ENT_QUOTES); }
function wp_json_encode($d, $o = 0) { return json_encode($d, $o); }

$post = new stdClass; $post->post_author = 1;
$GLOBALS['post'] = $post;
$GLOBALS['wp'] = new stdClass; $GLOBALS['wp']->request = 'blog/sample-post';

require '/home/user/Dashboard/seo/wordpress/functions-seo.php';
echo do_wp_head();
