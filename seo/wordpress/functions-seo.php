<?php
/**
 * ============================================================
 *  SEO / LLMO 構造化データ出力
 * ------------------------------------------------------------
 *  設置方法（どちらか）
 *   A) 子テーマの functions.php の末尾に、このファイルの中身を貼る
 *   B) このファイルを子テーマ直下に置き、functions.php に次を追記
 *        require_once get_stylesheet_directory() . '/functions-seo.php';
 *
 *  ★必ず子テーマで★ 親テーマに書くと、テーマ更新時に消えます。
 *  ★必ずバックアップを★ PHPの記述ミスはサイト全体を停止させます。
 *     可能なら「WPCode」等のスニペット管理プラグイン経由が安全です。
 *
 *  ★置換箇所★ 下の site_seo_config() の中身をすべて自社情報に。
 * ============================================================
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ============================================================
 * 1. 設定 —— ここだけ書き換えれば動きます
 * ========================================================== */
function site_seo_config() {
	return array(

		// 正式な会社名（登記名）
		'name'          => '株式会社サンプル',

		// 検索・SNSで使われる通称（同じでよければ name と同じ値）
		'alternate'     => 'サンプル',

		// 正規URL（末尾スラッシュなし・www有無は .htaccess と揃える）
		'url'           => 'https://example.co.jp',

		// ロゴ画像の絶対URL（推奨：横長・112px以上・PNG/JPG）
		'logo'          => 'https://example.co.jp/wp-content/uploads/logo.png',

		// SNS等でシェアされた時の既定画像（推奨 1200x630px）
		'og_image'      => 'https://example.co.jp/wp-content/uploads/ogp.jpg',

		// 事業内容を1〜2文で。曖昧語を使わず、対象者と提供価値を断定形で
		'description'   => '中小企業の経営者向けに、実践型マーケティング講座と伴走支援を提供するコンサルティング会社です。',

		// 設立年月日（YYYY-MM-DD）／不明なら空文字 ''
		'founding_date' => '1990-04-01',

		// 電話番号（国際表記）／不要なら空文字 ''
		'telephone'     => '+81-3-0000-0000',

		// 所在地／不要な項目は空文字 ''
		'address'       => array(
			'postal'   => '100-0001',
			'region'   => '東京都',
			'locality' => '千代田区',
			'street'   => '千代田1-2-3 サンプルビル5F',
		),

		// 公式アカウント・掲載先URL（E-E-A-T の裏付けになるので必ず入れる）
		'same_as'       => array(
			'https://x.com/your_account',
			'https://www.youtube.com/@your_channel',
			'https://www.facebook.com/your_page',
			// 'https://ja.wikipedia.org/wiki/...',
		),

		// サイト内検索を Google に認識させる（検索ページがあるなら true）
		'search_action' => true,
	);
}

/* ============================================================
 * 2. 既存SEOプラグインとの衝突ガード
 * ------------------------------------------------------------
 *  Yoast / Rank Math / AIOSEO / SEO SIMPLE PACK を検出したら、
 *  canonical・description・OGP の出力は行いません（二重出力の防止）。
 *  構造化データ（JSON-LD）は、下の定数で挙動を選べます。
 * ========================================================== */

// true にすると、SEOプラグインがあっても JSON-LD を出力します。
// まずは false のまま様子を見て、プラグイン側の構造化データが
// 不十分な場合だけ true にしてください。
if ( ! defined( 'SITE_SEO_ENABLE' ) ) {
	define( 'SITE_SEO_ENABLE', false );
}

function site_seo_has_seo_plugin() {
	return (
		defined( 'WPSEO_VERSION' )        // Yoast SEO
		|| defined( 'RANK_MATH_VERSION' ) // Rank Math
		|| defined( 'AIOSEO_VERSION' )    // All in One SEO
		|| defined( 'SSP_VERSION' )       // SEO SIMPLE PACK
	);
}

/* ============================================================
 * 3. 構造化データ（JSON-LD）の出力
 * ========================================================== */
add_action( 'wp_head', 'site_seo_print_jsonld', 5 );
function site_seo_print_jsonld() {

	if ( site_seo_has_seo_plugin() && ! SITE_SEO_ENABLE ) {
		return;
	}
	if ( is_404() || is_search() ) {
		return;
	}

	$c     = site_seo_config();
	$graph = array();

	/* --- 会社情報（Organization）------------------------------
	 * 「この会社は実在し、こういう事業をしている」をGoogleとAIに伝える。
	 * ナレッジパネル・AI回答での引用の土台になります。
	 */
	$org = array(
		'@type'       => 'Organization',
		'@id'         => $c['url'] . '/#organization',
		'name'        => $c['name'],
		'url'         => $c['url'],
		'description' => $c['description'],
	);
	if ( ! empty( $c['alternate'] ) && $c['alternate'] !== $c['name'] ) {
		$org['alternateName'] = $c['alternate'];
	}
	if ( ! empty( $c['logo'] ) ) {
		$org['logo'] = array(
			'@type' => 'ImageObject',
			'@id'   => $c['url'] . '/#logo',
			'url'   => $c['logo'],
		);
		$org['image'] = array( '@id' => $c['url'] . '/#logo' );
	}
	if ( ! empty( $c['founding_date'] ) ) {
		$org['foundingDate'] = $c['founding_date'];
	}
	if ( ! empty( $c['telephone'] ) ) {
		$org['telephone'] = $c['telephone'];
	}
	$addr = array_filter( (array) $c['address'] );
	if ( $addr ) {
		$org['address'] = array_filter( array(
			'@type'           => 'PostalAddress',
			'addressCountry'  => 'JP',
			'postalCode'      => $c['address']['postal'],
			'addressRegion'   => $c['address']['region'],
			'addressLocality' => $c['address']['locality'],
			'streetAddress'   => $c['address']['street'],
		) );
	}
	$same = array_filter( (array) $c['same_as'] );
	if ( $same ) {
		$org['sameAs'] = array_values( $same );
	}
	$graph[] = $org;

	/* --- サイト情報（WebSite）--------------------------------- */
	$site = array(
		'@type'      => 'WebSite',
		'@id'        => $c['url'] . '/#website',
		'url'        => $c['url'],
		'name'       => $c['name'],
		'publisher'  => array( '@id' => $c['url'] . '/#organization' ),
		'inLanguage' => 'ja',
	);
	if ( ! empty( $c['search_action'] ) ) {
		$site['potentialAction'] = array(
			'@type'       => 'SearchAction',
			'target'      => array(
				'@type'       => 'EntryPoint',
				'urlTemplate' => $c['url'] . '/?s={search_term_string}',
			),
			'query-input' => 'required name=search_term_string',
		);
	}
	$graph[] = $site;

	/* --- 記事情報（Article）: 投稿ページのみ ------------------- */
	if ( is_singular( 'post' ) ) {
		global $post;

		$article = array(
			'@type'            => 'BlogPosting',
			'@id'              => get_permalink() . '#article',
			'mainEntityOfPage' => array( '@id' => get_permalink() ),
			'headline'         => mb_substr( wp_strip_all_tags( get_the_title() ), 0, 110 ),
			'datePublished'    => get_the_date( 'c' ),
			'dateModified'     => get_the_modified_date( 'c' ),
			'inLanguage'       => 'ja',
			'publisher'        => array( '@id' => $c['url'] . '/#organization' ),
			'isPartOf'         => array( '@id' => $c['url'] . '/#website' ),
		);

		// 著者（E-E-A-T：誰が書いたかは評価に直結します）
		$author_id = (int) $post->post_author;
		$article['author'] = array(
			'@type' => 'Person',
			'@id'   => get_author_posts_url( $author_id ) . '#person',
			'name'  => get_the_author_meta( 'display_name', $author_id ),
			'url'   => get_author_posts_url( $author_id ),
		);

		$excerpt = wp_strip_all_tags( get_the_excerpt() );
		if ( $excerpt ) {
			$article['description'] = mb_substr( $excerpt, 0, 160 );
		}
		if ( has_post_thumbnail() ) {
			$img = wp_get_attachment_image_src( get_post_thumbnail_id(), 'full' );
			if ( $img ) {
				$article['image'] = array(
					'@type'  => 'ImageObject',
					'url'    => $img[0],
					'width'  => $img[1],
					'height' => $img[2],
				);
			}
		}
		$graph[] = $article;
	}

	/* --- パンくず（BreadcrumbList）----------------------------
	 * 検索結果にサイト階層が表示され、クリック率が上がります。
	 */
	if ( ! is_front_page() ) {
		$items = array(
			array(
				'@type'    => 'ListItem',
				'position' => 1,
				'name'     => 'ホーム',
				'item'     => $c['url'] . '/',
			),
		);
		$pos = 2;

		if ( is_singular( 'post' ) ) {
			$cats = get_the_category();
			if ( ! empty( $cats ) ) {
				$items[] = array(
					'@type'    => 'ListItem',
					'position' => $pos++,
					'name'     => $cats[0]->name,
					'item'     => get_category_link( $cats[0]->term_id ),
				);
			}
		} elseif ( is_category() || is_tag() || is_tax() ) {
			$term = get_queried_object();
			if ( $term && ! empty( $term->name ) ) {
				$items[] = array(
					'@type'    => 'ListItem',
					'position' => $pos++,
					'name'     => $term->name,
				);
			}
		}

		if ( is_singular() ) {
			$items[] = array(
				'@type'    => 'ListItem',
				'position' => $pos,
				'name'     => wp_strip_all_tags( get_the_title() ),
			);
		}

		$graph[] = array(
			'@type'           => 'BreadcrumbList',
			'@id'             => site_seo_current_url() . '#breadcrumb',
			'itemListElement' => $items,
		);
	}

	/* --- FAQ（FAQPage）---------------------------------------
	 * 下の site_seo_faq_map() に登録したページでのみ出力されます。
	 * AIは Q&A 形式をそのまま引用するため、LLMO効果が最も高い項目です。
	 */
	$faq = site_seo_faq_for_current();
	if ( $faq ) {
		$qa = array();
		foreach ( $faq as $q => $a ) {
			$qa[] = array(
				'@type'          => 'Question',
				'name'           => $q,
				'acceptedAnswer' => array(
					'@type' => 'Answer',
					'text'  => $a,
				),
			);
		}
		$graph[] = array(
			'@type'      => 'FAQPage',
			'@id'        => site_seo_current_url() . '#faq',
			'mainEntity' => $qa,
		);
	}

	$json = wp_json_encode(
		array(
			'@context' => 'https://schema.org',
			'@graph'   => $graph,
		),
		JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
	);

	echo "\n<!-- SEO/LLMO structured data -->\n";
	echo '<script type="application/ld+json">' . $json . "</script>\n";
}

/* ============================================================
 * 4. FAQ の登録
 * ------------------------------------------------------------
 *  キー   = 固定ページのスラッグ（URLの末尾部分）
 *  値     = 質問 => 回答 の配列
 *
 *  ★重要★ ここに書いたQ&Aは、**そのページの本文にも同じ内容を
 *          表示していること** が条件です。本文に無いFAQを構造化
 *          データだけで出すと、Googleのガイドライン違反になります。
 * ========================================================== */
function site_seo_faq_map() {
	return array(

		'faq' => array(
			'サービスの料金はいくらですか？'
				=> '基本プランは月額◯◯円（税込）です。初期費用はかかりません。',
			'契約期間の縛りはありますか？'
				=> '最低契約期間は3ヶ月です。以降は1ヶ月単位でご解約いただけます。',
		),

		// 'service' => array(
		//     '初心者でも受講できますか？' => '受講者の約◯割が未経験からのスタートです。',
		// ),
	);
}

function site_seo_faq_for_current() {
	if ( ! is_page() ) {
		return array();
	}
	$map  = site_seo_faq_map();
	$slug = get_post_field( 'post_name', get_queried_object_id() );
	return isset( $map[ $slug ] ) ? $map[ $slug ] : array();
}

/* ============================================================
 * 5. canonical / description / OGP
 * ------------------------------------------------------------
 *  SEOプラグインがある場合は何もしません（二重出力の防止）。
 *  ※ canonical は必ず「最終URL」を指します。転送されるURLを
 *    canonical に書くことが、今回の警告のよくある原因のひとつです。
 * ========================================================== */
add_action( 'wp_head', 'site_seo_print_meta', 6 );
function site_seo_print_meta() {

	if ( site_seo_has_seo_plugin() ) {
		return;
	}

	$c    = site_seo_config();
	$url  = site_seo_current_url();
	$type = 'website';
	$img  = $c['og_image'];

	if ( is_front_page() ) {
		$title = $c['name'];
		$desc  = $c['description'];
	} elseif ( is_singular() ) {
		$title = wp_strip_all_tags( get_the_title() ) . ' | ' . $c['name'];
		$desc  = wp_strip_all_tags( get_the_excerpt() );
		$type  = is_singular( 'post' ) ? 'article' : 'website';
		if ( has_post_thumbnail() ) {
			$t = wp_get_attachment_image_src( get_post_thumbnail_id(), 'full' );
			if ( $t ) {
				$img = $t[0];
			}
		}
	} else {
		$title = wp_get_document_title();
		$desc  = $c['description'];
	}

	$desc = trim( mb_substr( preg_replace( '/\s+/u', ' ', (string) $desc ), 0, 160 ) );
	if ( '' === $desc ) {
		$desc = $c['description'];
	}

	printf( '<link rel="canonical" href="%s">' . "\n", esc_url( $url ) );
	printf( '<meta name="description" content="%s">' . "\n", esc_attr( $desc ) );

	printf( '<meta property="og:type" content="%s">' . "\n", esc_attr( $type ) );
	printf( '<meta property="og:title" content="%s">' . "\n", esc_attr( $title ) );
	printf( '<meta property="og:description" content="%s">' . "\n", esc_attr( $desc ) );
	printf( '<meta property="og:url" content="%s">' . "\n", esc_url( $url ) );
	printf( '<meta property="og:site_name" content="%s">' . "\n", esc_attr( $c['name'] ) );
	printf( '<meta property="og:locale" content="ja_JP">' . "\n" );
	if ( $img ) {
		printf( '<meta property="og:image" content="%s">' . "\n", esc_url( $img ) );
	}
	printf( '<meta name="twitter:card" content="summary_large_image">' . "\n" );
}

/* ============================================================
 * 6. 共通ヘルパー：現在URL（クエリを除いた正規形）
 * ========================================================== */
function site_seo_current_url() {
	if ( is_front_page() ) {
		return home_url( '/' );
	}
	if ( is_singular() ) {
		return get_permalink();
	}
	if ( is_category() || is_tag() || is_tax() ) {
		$link = get_term_link( get_queried_object() );
		if ( ! is_wp_error( $link ) ) {
			return $link;
		}
	}
	if ( is_post_type_archive() ) {
		return get_post_type_archive_link( get_post_type() );
	}
	$req = isset( $GLOBALS['wp']->request ) ? trim( (string) $GLOBALS['wp']->request, '/' ) : '';
	return $req ? home_url( '/' . $req . '/' ) : home_url( '/' );
}

/* ============================================================
 * 7. インデックス汚染の抑制
 * ------------------------------------------------------------
 *  中身の薄いページを検索結果から外し、評価を主要ページに集中させます。
 *  ※ これは noindex であってリダイレクトではないため、
 *    今回の「リダイレクトがあります」を増やしません。
 * ========================================================== */
add_action( 'wp_head', 'site_seo_thin_page_noindex', 1 );
function site_seo_thin_page_noindex() {
	if ( site_seo_has_seo_plugin() ) {
		return; // プラグイン側の設定に任せる
	}
	// ※ ページ送り（2ページ目以降）は noindex にしません。
	//    Google は「ページ送りを noindex にしない」ことを推奨しています。
	if ( is_search() || is_attachment() || is_author() || is_date() ) {
		echo '<meta name="robots" content="noindex,follow">' . "\n";
	}
}
