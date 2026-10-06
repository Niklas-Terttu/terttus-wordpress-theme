<?php get_header();?>
<?php while(have_posts()):the_post();?>
<?php if(function_exists('terttus_is_elementor_page')&&terttus_is_elementor_page()):?>
<div class="tt-elementor-content"><?php the_content();?></div>
<?php else:?>
<div class="tt-container tt-content"><article><h1 class="tt-page-title"><?php the_title();?></h1><?php the_content();?></article></div>
<?php endif;?>
<?php endwhile;?>
<?php get_footer();?>