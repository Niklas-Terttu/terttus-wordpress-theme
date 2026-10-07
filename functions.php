<?php
if(!defined('ABSPATH'))exit;define('TERTTUS_VERSION','1.3.9');define('TERTTUS_UPDATE_METADATA','https://raw.githubusercontent.com/Niklas-Terttu/terttus-wordpress-theme/main/update.json');
function terttus_setup(){add_theme_support('title-tag');add_theme_support('post-thumbnails');add_theme_support('custom-logo',['height'=>80,'width'=>260,'flex-height'=>true,'flex-width'=>true]);add_theme_support('woocommerce');add_theme_support('wc-product-gallery-zoom');add_theme_support('wc-product-gallery-lightbox');add_theme_support('wc-product-gallery-slider');register_nav_menus(['primary'=>'Primær menu','footer'=>'Footer menu']);}add_action('after_setup_theme','terttus_setup');
function terttus_assets(){wp_enqueue_style('terttus-style',get_stylesheet_uri(),[],TERTTUS_VERSION);wp_enqueue_script('terttus-theme',get_template_directory_uri().'/assets/theme.js',[],TERTTUS_VERSION,true);}add_action('wp_enqueue_scripts','terttus_assets');
function terttus_customize($w){$w->add_section('terttus_shop',['title'=>'Terttus webshop','priority'=>30]);$x=['shipping_text'=>['Topbar tekst','🚚 Fri fragt ved køb over 499 kr. · Levering 1–3 hverdage'],'hero_title'=>['Hero overskrift','Gør dit hjem smartere med Terttus'],'hero_text'=>['Hero tekst','IT, elektronik og smart home – udvalgt af en entusiast, til fornuftige priser og med ordentlig support.']];foreach($x as$id=>$v){$w->add_setting($id,['default'=>$v[1],'sanitize_callback'=>'sanitize_text_field']);$w->add_control($id,['section'=>'terttus_shop','label'=>$v[0],'type'=>'text']);}}add_action('customize_register','terttus_customize');
function terttus_meta(){$r=wp_remote_get(TERTTUS_UPDATE_METADATA,['timeout'=>10]);if(is_wp_error($r)||200!==wp_remote_retrieve_response_code($r))return null;$d=json_decode(wp_remote_retrieve_body($r),true);return is_array($d)?$d:null;}
function terttus_update_check($t){$d=terttus_meta();if(empty($d['version'])||version_compare(TERTTUS_VERSION,$d['version'],'>='))return $t;$theme=wp_get_theme();$t->response[$theme->get_stylesheet()]=['theme'=>$theme->get_stylesheet(),'new_version'=>$d['version'],'url'=>$d['details_url']??'','package'=>$d['download_url']??''];return $t;}add_filter('pre_set_site_transient_update_themes','terttus_update_check');
function terttus_force_theme_update_check(){
 if(!current_user_can('update_themes'))wp_die('Ingen adgang.');
 check_admin_referer('terttus_force_theme_update');
 delete_site_transient('update_themes');
 wp_clean_themes_cache(true);
 wp_update_themes();
 wp_safe_redirect(add_query_arg('terttus_checked','1',admin_url('themes.php')));
 exit;
}
add_action('admin_post_terttus_force_theme_update','terttus_force_theme_update_check');
function terttus_theme_update_action(){
 if(!current_user_can('update_themes'))return;
 $screen=get_current_screen();if(!$screen||$screen->id!=='themes')return;
 $url=wp_nonce_url(admin_url('admin-post.php?action=terttus_force_theme_update'),'terttus_force_theme_update');
 echo '<div class="notice notice-info terttus-update-check"><p><strong>Terttus Theme:</strong> <a class="button button-secondary" href="'.esc_url($url).'">Søg efter opdatering</a> <span>Kontrollerer GitHub med det samme.</span></p></div>';
}
add_action('admin_notices','terttus_theme_update_action');


function terttus_elementor_support(){add_theme_support('elementor');}
add_action('after_setup_theme','terttus_elementor_support',20);
function terttus_is_elementor_page($post_id=0){if(!did_action('elementor/loaded'))return false;$post_id=$post_id?:get_the_ID();return $post_id&&\Elementor\Plugin::$instance->db->is_built_with_elementor($post_id);}
function terttus_body_classes($classes){if(is_singular()&&terttus_is_elementor_page()){$classes[]='terttus-elementor-page';$classes[]='terttus-full-width';}return $classes;}
add_filter('body_class','terttus_body_classes');

function terttus_category_mega_menu(){
 if(!class_exists('WooCommerce')){wp_nav_menu(['theme_location'=>'primary','container'=>false,'fallback_cb'=>false]);return;}
 $uncategorized_id=(int)get_option('default_product_cat',0);
 $parents=get_terms(['taxonomy'=>'product_cat','hide_empty'=>false,'parent'=>0,'exclude'=>$uncategorized_id?[$uncategorized_id]:[],'orderby'=>'menu_order','order'=>'ASC']);
 if(is_wp_error($parents)||!$parents){wp_nav_menu(['theme_location'=>'primary','container'=>false,'fallback_cb'=>false]);return;}
 echo '<ul class="tt-mega-root">';
 foreach($parents as$parent){
  if(get_term_meta($parent->term_id,'_tpm_show_mega',true)==='no')continue;
  $children=get_terms(['taxonomy'=>'product_cat','hide_empty'=>false,'parent'=>$parent->term_id,'orderby'=>'menu_order','order'=>'ASC']);
  if(is_wp_error($children))$children=[];
  $has=!empty($children);
  echo '<li class="tt-mega-item'.($has?' has-children':'').'"><a href="'.esc_url(get_term_link($parent)).'">'.esc_html($parent->name).($has?'<span class="tt-mega-chevron">⌄</span>':'').'</a>';
  if($has){
   echo '<div class="tt-mega-panel"><div class="tt-mega-inner"><div class="tt-mega-heading"><span>Shop kategori</span><strong>'.esc_html($parent->name).'</strong><a href="'.esc_url(get_term_link($parent)).'">Se alle →</a></div><div class="tt-mega-columns">';
   foreach($children as$child){
    if(get_term_meta($child->term_id,'_tpm_show_mega',true)==='no')continue;
    $grand=get_terms(['taxonomy'=>'product_cat','hide_empty'=>false,'parent'=>$child->term_id,'orderby'=>'menu_order','order'=>'ASC']);if(is_wp_error($grand))$grand=[];
    echo '<div class="tt-mega-column"><a class="tt-mega-title" href="'.esc_url(get_term_link($child)).'">'.esc_html($child->name).'</a>';
    if($grand){echo '<ul>';foreach($grand as$g){if(get_term_meta($g->term_id,'_tpm_show_mega',true)==='no')continue;echo '<li><a href="'.esc_url(get_term_link($g)).'">'.esc_html($g->name).'</a></li>';}echo '</ul>';}
    echo '</div>';
   }
   echo '</div></div></div>';
  }
  echo '</li>';
 }
 $sale=get_term_by('slug','tilbud','product_cat');$sale_url=($sale&&!is_wp_error($sale))?get_term_link($sale):wc_get_page_permalink('shop');echo '<li class="tt-mega-item tt-mega-sale"><a href="'.esc_url($sale_url).'">Tilbud</a></li></ul>';
}
function terttus_wc_fragments($fragments){ob_start();?><span class="tt-cart-count"><?php echo WC()->cart?intval(WC()->cart->get_cart_contents_count()):0;?></span><?php $fragments['.tt-cart-count']=ob_get_clean();return $fragments;}add_filter('woocommerce_add_to_cart_fragments','terttus_wc_fragments');
function terttus_product_benefits(){echo '<div class="tt-product-benefits"><div>✓ <strong>Dansk support</strong></div><div>✓ Sikker betaling</div><div>✓ 14 dages fortrydelse</div></div>';}add_action('woocommerce_single_product_summary','terttus_product_benefits',35);
function terttus_loop_stock(){global $product;if(!$product)return;echo '<div class="tt-loop-stock">'.($product->is_in_stock()?'● På lager':'● Ikke på lager').'</div>';}add_action('woocommerce_after_shop_loop_item_title','terttus_loop_stock',11);

require_once get_template_directory().'/inc/customizer-storefront.php';

function terttus_shop_archive_cleanup(){
 remove_action('woocommerce_shop_loop_header','woocommerce_product_taxonomy_archive_header',10);
 remove_action('woocommerce_archive_description','woocommerce_taxonomy_archive_description',10);
 remove_action('woocommerce_archive_description','woocommerce_product_archive_description',10);
}
add_action('wp', 'terttus_shop_archive_cleanup');

function terttus_shop_toolbar_open(){
 if(!is_shop()&&!is_product_taxonomy())return;
 echo '<div class="tt-shop-toolbar">';
}
function terttus_shop_toolbar_close(){
 if(!is_shop()&&!is_product_taxonomy())return;
 echo '</div><div class="tt-shop-layout">';
 terttus_render_shop_filters();
 echo '<div class="tt-shop-products">';
}
function terttus_shop_layout_close(){
 if(!is_shop()&&!is_product_taxonomy())return;
 echo '</div></div>';
}
add_action('woocommerce_before_shop_loop','terttus_shop_toolbar_open',19);
add_action('woocommerce_before_shop_loop','terttus_shop_toolbar_close',31);
add_action('woocommerce_after_shop_loop','terttus_shop_layout_close',20);

function terttus_render_shop_filters(){
 if(!class_exists('WooCommerce'))return;
 $current=is_product_category()?get_queried_object():null;
 echo '<aside class="tt-shop-filters" aria-label="Produktfiltre"><h2>Filtrer produkter</h2>';
 $cats=get_terms(['taxonomy'=>'product_cat','hide_empty'=>true,'parent'=>$current&&isset($current->term_id)?(int)$current->term_id:0]);
 if(!is_wp_error($cats)&&$cats){
  echo '<div class="tt-filter-group"><h3>Kategori</h3><ul class="tt-filter-list">';
  foreach($cats as$cat){$active=$current&&$current->term_id===$cat->term_id?' is-active':'';echo '<li><a class="'.$active.'" href="'.esc_url(get_term_link($cat)).'">'.esc_html($cat->name).' <span>('.intval($cat->count).')</span></a></li>';}
  echo '</ul></div>';
 }
 echo '<form class="tt-filter-group" method="get"><h3>Pris</h3><div class="tt-filter-price"><input type="number" min="0" name="min_price" placeholder="Min." value="'.esc_attr(isset($_GET['min_price'])?wc_clean(wp_unslash($_GET['min_price'])):'').'"><input type="number" min="0" name="max_price" placeholder="Maks." value="'.esc_attr(isset($_GET['max_price'])?wc_clean(wp_unslash($_GET['max_price'])):'').'"></div>';
 foreach($_GET as$key=>$value){if(in_array($key,['min_price','max_price'],true)||is_array($value))continue;echo '<input type="hidden" name="'.esc_attr($key).'" value="'.esc_attr(wc_clean(wp_unslash($value))).'">';}
 echo '<button class="tt-btn tt-filter-submit" type="submit">Anvend filter</button></form>';
 if(!empty($_GET['min_price'])||!empty($_GET['max_price']))echo '<a class="tt-filter-clear" href="'.esc_url(remove_query_arg(['min_price','max_price'])).'">Nulstil filter</a>';
 echo '</aside>';
}
