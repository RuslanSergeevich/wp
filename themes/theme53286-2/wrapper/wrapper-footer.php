<?php /* Wrapper Name: Footer */ ?>
<div class="row footer-widgets">
	<div class="span3" data-motopress-type="dynamic-sidebar" data-motopress-sidebar-id="footer-sidebar-1">
		<?php get_template_part("static/static-footer-nav"); ?>
	</div>
	<div class="span3" data-motopress-type="dynamic-sidebar" data-motopress-sidebar-id="footer-sidebar-2">
		<?php dynamic_sidebar("footer-sidebar-1"); ?>
	</div>
	<div class="span3 box" data-motopress-type="dynamic-sidebar" data-motopress-sidebar-id="footer-sidebar-3">
		
	</div>
	<div class="span3" data-motopress-type="dynamic-sidebar" data-motopress-sidebar-id="footer-sidebar-4">
		<?php dynamic_sidebar("footer-sidebar-4"); ?>
	</div>
</div>
<div class="row copyright">
	<div class="span7">
		<?php get_template_part("static/static-footer-text"); ?>
	</div>
	<div class="span5 footer-logo">
		<a href="http://www.templatemonster.com/" rel="nofollow" target="_blank">
			<span class="footer-logo-text">Designed by</span>
			<img src="<?php echo get_stylesheet_directory_uri(); ?>/images/footer_logo.png" alt="logo">
		</a>
	</div>
</div>