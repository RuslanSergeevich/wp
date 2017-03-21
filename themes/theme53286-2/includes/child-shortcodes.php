<?php
//Shortcodes

add_shortcode( 'advanced_categories', 'tm_advanced_categories_shortcode' );

function tm_advanced_categories_shortcode( $atts ) {

	extract( shortcode_atts( array(
		'from_cat' => '',
		'select_only_with_images' => true,
		'show_image' => true,
		'show_name' => false,
		'show_description' => false,
		'columns' => '4'
	), $atts ) );

	//global $tm_theme_texdomain;

	if ( '' != $from_cat) {
		$parent_cat = get_term_by( 'slug', $from_cat, 'product_cat' );
		$args = array(
		    'hide_empty'    => false, 
		    'parent'         => $parent_cat->term_id
		); 
	} else {
		$args = array(
		    'hide_empty'    => false
		);
	}
	$prod_cats = get_terms( 'product_cat', $args );
	if ( $prod_cats ) {
		$container_class = '';
		switch ($columns) {
			case '1':
				$container_class = 'cols_1';
				$col_num = 1;
				break;
			case '2':
				$container_class = 'cols_2';
				$col_num = 2;
				break;
			case '3':
				$container_class = 'cols_3';
				$col_num = 3;
				break;
			case '4':
				$container_class = 'cols_4';
				$col_num = 4;
				break;
			case '5':
				$container_class = 'cols_5';
				$col_num = 5;
				break;
			case '6':
				$container_class = 'cols_6';
				$col_num = 6;
				break;
			default:
				$container_class = 'cols_6';
				$col_num = 6;
				break;
		}
		
		$output = "<ul class='advanced_categories " . $container_class . "'>\n";
		$cat_iterator = 0;
		foreach ( $prod_cats as $cat ) {

			$cat_link = get_term_link( $cat, 'product_cat' );

			$visible_trigger = true;
			if ( true == $select_only_with_images ) {
				$thumbnail_id = get_woocommerce_term_meta( $cat->term_id, 'thumbnail_id', true );
				$image = wp_get_attachment_url( $thumbnail_id );
				if (!$image) {
					$visible_trigger = false;
				}
			}
			if ( true == $visible_trigger ) {
				$cat_iterator++;
				$item_class = '';
				if ( 1 == $cat_iterator % $col_num ) {
					$item_class = ' first';
				} elseif ( 0 == $cat_iterator % $col_num ) {
					$item_class = ' last';
				}
				$output .= "<li class='advanced_categories_item" . $item_class . "'>\n";
					$output .= "<div class='advanced_categories_item_inner'>\n";
						if ( true == $show_image ) {
							$thumbnail_id = get_woocommerce_term_meta( $cat->term_id, 'thumbnail_id', true );
							$image = wp_get_attachment_image_src( $thumbnail_id, 'thumbnail' );
							$output .= "<figure>\n";
								$output .= "<a href='" . $cat_link . "'><img src='" . $image[0] . "' alt='" . $cat->name . "'></a>\n";
							$output .= "</figure>\n";
						}
						if ( true == $show_name ) {
							$output .= "<h4><a href='" . $cat_link . "'>" . $cat->name . "</a></h4>\n";
						}
						if ( true == $show_description && $cat->description != '' ) {
							$output .= "<div class='cat_desc'>" . $cat->description . "</div>\n";
						}
					$output .= "</div>\n";
				$output .= "</li>\n";
			}

		}
		$output .= "</ul>\n";
	} else {
		$output = __( 'There is no categories has been found', CURRENT_THEME );
	}

	return $output;
	
}
/**
 * Banner
 *
 */
if (!function_exists('banner_shortcode')) {

 function banner_shortcode($atts, $content = null) {
  extract(shortcode_atts(
   array(
    'img'          => '',
    'banner_link'  => '',
    'title'        => '',
    'text'         => '',
    'btn_text'     => '',
    'target'       => '',
    'custom_class' => ''
  ), $atts));

  // get attribute
  $content_url = content_url();
  $content_str = 'wp-content';

  $pos = strpos($img, $content_str);
  if ($pos !== false) {
   $img_new = substr( $img, $pos+strlen($content_str), strlen($img)-$pos );
   $img     = $content_url.$img_new;
  }

  $output =  '<div "'. $banner_link .'" title="'. $title .'" class="banner-wrap '.$custom_class.'">'; 

  if ($img !="") {
   $output .= '<figure class="featured-thumbnail">';
   if ($banner_link != "") {
    $output .= '<img src="' . $img .'" title="'. $title .'" alt="" />';
   } else {
    $output .= '<img src="' . $img .'" title="'. $title .'" alt="" />';
   }
   $output .= '</figure>';
  }  
  $output .= '<div class="extra-wrap">';

   if ($title!="") {
   $output .= '<h5>';
   $output .= $title;
   $output .= '</h5>';
  }

  if ($text!="") {
   $output .= '<p>';
   $output .= $text;
   $output .= '</p>';
  }

  if ($btn_text!="") {
   $output .=  '<div class="link-align banner-btn">';
   $output .= $btn_text;
   $output .= '</div>';
  }
  $output .= '</div>';
  $output .= '</div><!-- .banner-wrap (end) -->';
  return $output;

 }
 add_shortcode('banner', 'banner_shortcode');
}
//Custom element
function shortcode_custom_element($atts, $content = null) {
	extract(shortcode_atts(array(
			'element' => 'div',
			'css_class' => 'my_class',
			'inner_wrapper' => false 
	), $atts));

	$output = '<'.$element.' class="'.esc_attr( $css_class ).'">';
	if (true == $inner_wrapper) {
		$output .= '<div class="'.esc_attr( $css_class ).'_wrap_inner">';
	}
		$output .= do_shortcode($content);
	if (true == $inner_wrapper) {
		$output .= '</div>';
	}
	$output .= '</'.$element.'>';

	return $output;
}
add_shortcode('custom_element', 'shortcode_custom_element');

/**
 * Banner
 *
 */
if ( !function_exists( 'banner_shortcode' ) ) {

	function banner_shortcode( $atts, $content = null, $shortcodename = '' ) {
		extract( shortcode_atts(
			array(
				'img'          => '',
				'banner_link'  => '',
				'title'        => '',
				'text'         => '',
				'btn_text'     => '',
				'target'       => '',
				'custom_class' => ''
		), $atts));

		// Get the URL to the content area.
		$content_url = untrailingslashit( content_url() );

		// Find latest '/' in content URL.
		$last_slash_pos = strrpos( $content_url, '/' );

		// 'wp-content' or something else.
		$content_dir_name = substr( $content_url, $last_slash_pos - strlen( $content_url ) + 1 );

		$pos = strpos( $img, $content_dir_name );

		if ( false !== $pos ) {

			$img_new = substr( $img, $pos + strlen( $content_dir_name ), strlen( $img ) - $pos );
			$img     = $content_url . $img_new;

		}

		$output =  '<div class="banner-wrap '.$custom_class.'">';
		if ($img !="") {
			$output .= '<figure class="featured-thumbnail">';
			if ($banner_link != "") {
				$output .= '<a href="'. $banner_link .'" title="'. $title .'"><img src="' . $img .'" title="'. $title .'" alt="" /></a>';
			} else {
				$output .= '<img src="' . $img .'" title="'. $title .'" alt="" />';
			}
			$output .= '</figure>';
		}
        $output .= '<div class="extra-wrap">';
    		if ($title!="") {
    			$output .= '<h5>';
    			$output .= $title;
    			$output .= '</h5>';
    		}
    		if ($text!="") {
    			$output .= '<p>';
    			$output .= $text;
    			$output .= '</p>';
    		}
    		if ($btn_text!="") {
    			$output .=  '<div class="link-align banner-btn"><a href="'.$banner_link.'" title="'.$btn_text.'" class="btn btn-link" target="'.$target.'">';
    			$output .= $btn_text;
    			$output .= '</a></div>';
    		}
        $output .= '</div>';
		$output .= '</div><!-- .banner-wrap (end) -->';

		$output = apply_filters( 'cherry_plugin_shortcode_output', $output, $atts, $shortcodename );

		return $output;
	}
	add_shortcode('banner', 'banner_shortcode');

}
/**
 * Service Box
 *
 */
if (!function_exists('service_box_shortcode')) {

	function service_box_shortcode( $atts, $content = null, $shortcodename = '' ) {
		extract(shortcode_atts(
			array(
				'title'        => '',
				'subtitle'     => '',
				'icon'         => '',
				'text'         => '',
				'btn_text'     => __('Read more', CHERRY_PLUGIN_DOMAIN),
				'btn_link'     => '',
				'btn_size'     => '',
				'target'       => '',
				'custom_class' => ''
		), $atts));

		$output =  '<div class="service-box '.$custom_class.'">';

		if($icon != 'no'){
			$icon_url = CHERRY_PLUGIN_URL . 'includes/images/' . strtolower($icon) . '.png' ;
			if( defined ('CHILD_DIR') ) {
				if(file_exists(CHILD_DIR.'/images/'.strtolower($icon).'.png')){
					$icon_url = CHILD_URL.'/images/'.strtolower($icon).'.png';
				}
			}
			$output .= '<figure class="icon"><img src="'.$icon_url.'" alt="" /></figure>';
		}

		$output .= '<div class="service-box_body">';

		if ($title!="") {
			$output .= '<a href="'.$btn_link.'" class="title">';
			$output .= $title;
			$output .= '</a>';
		}
		if ($subtitle!="") {
			$output .= '<h5 class="sub-title">';
			$output .= $subtitle;
			$output .= '</h5>';
		}
		if ($text!="") {
			$output .= '<div class="service-box_txt">';
			$output .= $text;
			$output .= '</div>';
		}
		/*if ($btn_link!="") {
			$output .=  '<div class="btn-align"><a href="'.$btn_link.'" title="'.$btn_text.'" class="btn btn-inverse btn-'.$btn_size.' btn-primary " target="'.$target.'">';
			$output .= $btn_text;
			$output .= '</a></div>';
		}*/
		$output .= '</div>';
		$output .= '</div><!-- /Service Box -->';

		$output = apply_filters( 'cherry_plugin_shortcode_output', $output, $atts, $shortcodename );

		return $output;
	}
	add_shortcode('service_box', 'service_box_shortcode');

}?>