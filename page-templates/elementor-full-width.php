<?php
/*
Template Name: Terttus – Elementor fuld bredde
Template Post Type: page
*/
get_header();?>
<div class="tt-elementor-content"><?php while(have_posts()):the_post();the_content();endwhile;?></div>
<?php get_footer();?>