<?php /* Wrapper Name: Header */ ?>
<div class="header_wrapper">
    <div class="shop_box">
        <?php get_template_part("static/static-shop-nav"); ?>
        <?php dynamic_sidebar( 'cart-holder' ); ?>
    </div>
    <?php get_template_part("static/static-logo"); ?>
    <div class="static_box">
        <?php get_template_part("static/static-search"); ?>
        <div class="logo_box" data-motopress-type="static" data-motopress-static-file="static/static-nav.php">
    		<?php get_template_part("static/static-nav"); ?>
    	</div>
    </div>
    
</div>