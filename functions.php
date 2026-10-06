<?php
if(!defined('ABSPATH'))exit;define('TERTTUS_VERSION','1.2.0');define('TERTTUS_UPDATE_METADATA','https://raw.githubusercontent.com/Niklas-Terttu/terttus-wordpress-theme/main/update.json');
function terttus_setup(){add_theme_support('title-tag');add_theme_support('post-thumbnails');add_theme_support('custom-logo',['height'=>80,'width'=>260,'flex-height'=>true,'flex-width'=>true]);add_theme_support('woocommerce');add_theme_support('wc-product-gallery-zoom');add_theme_support('wc-product-gallery-lightbox');add_theme_support('wc-product-gallery-slider');register_nav_menus(['primary'=>'Primær menu','footer'=>'Footer menu']);}add_action('after_setup_theme','terttus_setup');
function terttus_assets(){wp_enqueue_style('terttus-style',get_stylesheet_uri(),[],TERTTUS_VERSION);wp_enqueue_script('terttus-theme',get_template_directory_uri().'/assets/theme.js',[],TERTTUS_VERSION,true);}add_action('wp_enqueue_scripts','terttus_assets');
function terttus_customize($w){$w->add_section('terttus_shop',['title'=>'Terttus webshop','priority'=>30]);$x=['shipping_text'=>['Topbar tekst','🚚 Fri fragt ved køb over 499 kr. · Levering 1–3 hverdage'],'hero_title'=>['Hero overskrift','Gør dit hjem smartere med Terttus'],'hero_text'=>['Hero tekst','IT, elektronik og smart home – udvalgt af en entusiast, til fornuftige priser og med ordentlig support.']];foreach($x as$id=>$v){$w->add_setting($id,['default'=>$v[1],'sanitize_callback'=>'sanitize_text_field']);$w->add_control($id,['section'=>'terttus_shop','label'=>$v[0],'type'=>'text']);}}add_action('customize_register','terttus_customize');
function terttus_meta(){$r=wp_remote_get(TERTTUS_UPDATE_METADATA,['timeout'=>10]);if(is_wp_error($r)||200!==wp_remote_retrieve_response_code($r))return null;$d=json_decode(wp_remote_retrieve_body($r),true);return is_array($d)?$d:null;}
function terttus_update_check($t){$d=terttus_meta();if(empty($d['version'])||version_compare(TERTTUS_VERSION,$d['version'],'>='))return $t;$theme=wp_get_theme();$t->response[$theme->get_stylesheet()]=['theme'=>$theme->get_stylesheet(),'new_version'=>$d['version'],'url'=>$d['details_url']??'','package'=>$d['download_url']??''];return $t;}add_filter('pre_set_site_transient_update_themes','terttus_update_check');

function terttus_elementor_support(){add_theme_support('elementor');}
add_action('after_setup_theme','terttus_elementor_support',20);
function terttus_is_elementor_page($post_id=0){if(!did_action('elementor/loaded'))return false;$post_id=$post_id?:get_the_ID();return $post_id&&\Elementor\Plugin::$instance->db->is_built_with_elementor($post_id);}
function terttus_body_classes($classes){if(is_singular()&&terttus_is_elementor_page()){$classes[]='terttus-elementor-page';$classes[]='terttus-full-width';}return $classes;}
add_filter('body_class','terttus_body_classes');

function terttus_wc_fragments($fragments){ob_start();?><span class="tt-cart-count"><?php echo WC()->cart?intval(WC()->cart->get_cart_contents_count()):0;?></span><?php $fragments['.tt-cart-count']=ob_get_clean();return $fragments;}add_filter('woocommerce_add_to_cart_fragments','terttus_wc_fragments');
function terttus_product_benefits(){echo '<div class="tt-product-benefits"><div>✓ <strong>Dansk support</strong></div><div>✓ Sikker betaling</div><div>✓ 14 dages fortrydelse</div></div>';}add_action('woocommerce_single_product_summary','terttus_product_benefits',35);
function terttus_loop_stock(){global $product;if(!$product)return;echo '<div class="tt-loop-stock">'.($product->is_in_stock()?'● På lager':'● Ikke på lager').'</div>';}add_action('woocommerce_after_shop_loop_item_title','terttus_loop_stock',11);

require_once get_template_directory().'/inc/customizer-storefront.php';
