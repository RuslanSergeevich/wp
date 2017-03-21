<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Abstract Order Data Store: Stored in CPT.
 *
 * @version  3.0.0
 * @category Class
 * @author   WooThemes
 */
abstract class Abstract_WC_Order_Data_Store_CPT extends WC_Data_Store_WP implements WC_Object_Data_Store_Interface, WC_Abstract_Order_Data_Store_Interface {

	/**
	 * Internal meta type used to store order data.
	 *
	 * @var string
	 */
	protected $meta_type = 'post';

	/**
	 * Data stored in meta keys, but not considered "meta" for an order.
	 *
	 * @since 3.0.0
	 * @var array
	 */
	protected $internal_meta_keys = array(
		'_order_currency',
		'_cart_discount',
		'_cart_discount_tax',
		'_order_shipping',
		'_order_shipping_tax',
		'_order_tax',
		'_order_total',
		'_order_version',
		'_prices_include_tax',
		'_payment_tokens',
	);

	/*
	|--------------------------------------------------------------------------
	| CRUD Methods
	|--------------------------------------------------------------------------
	*/

	/**
	 * Method to create a new order in the database.
	 * @param WC_Order $order
	 */
	public function create( &$order ) {
		$order->set_version( WC_VERSION );
		$order->set_date_created( current_time( 'timestamp', true ) );
		$order->set_currency( $order->get_currency() ? $order->get_currency() : get_woocommerce_currency() );

		$id = wp_insert_post( apply_filters( 'woocommerce_new_order_data', array(
			'post_date'     => date( 'Y-m-d H:i:s', $order->get_date_created( 'edit' )->getOffsetTimestamp() ),
			'post_date_gmt' => date( 'Y-m-d H:i:s', $order->get_date_created( 'edit' )->getTimestamp() ),
			'post_type'     => $order->get_type( 'edit' ),
			'post_status'   => 'wc-' . ( $order->get_status( 'edit' ) ? $order->get_status( 'edit' ) : apply_filters( 'woocommerce_default_order_status', 'pending' ) ),
			'ping_status'   => 'closed',
			'post_author'   => 1,
			'post_title'    => $this->get_post_title(),
			'post_password' => uniqid( 'order_' ),
			'post_parent'   => $order->get_parent_id( 'edit' ),
			'post_excerpt'  => $this->get_post_excerpt( $order ),
		) ), true );

		if ( $id && ! is_wp_error( $id ) ) {
			$order->set_id( $id );
			$this->update_post_meta( $order );
			$order->save_meta_data();
			$order->apply_changes();
			$this->clear_caches( $order );
		}
	}

	/**
	 * Method to read an order from the database.
	 * @param WC_Order
	 */
	public function read( &$order ) {
		$order->set_defaults();

		if ( ! $order->get_id() || ! ( $post_object = get_post( $order->get_id() ) ) || ! in_array( $post_object->post_type, wc_get_order_types() ) ) {
			throw new Exception( __( 'Invalid order.', 'woocommerce' ) );
		}

		$id = $order->get_id();
		$order->set_props( array(
			'parent_id'          => $post_object->post_parent,
			'date_created'       => strtotime( $post_object->post_date_gmt ),
			'date_modified'      => strtotime( $post_object->post_modified_gmt ),
			'status'             => $post_object->post_status,
		) );

		$this->read_order_data( $order, $post_object );
		$order->read_meta_data();
		$order->set_object_read( true );

		/**
		 * In older versions, discounts may have been stored differently.
		 * Update them now so if the object is saved, the correct values are
		 * stored. @todo When meta is flattened, handle this during migration.
		 */
		if ( version_compare( $order->get_version( 'edit' ), '2.3.7', '<' ) && $order->get_prices_include_tax( 'edit' ) ) {
			$order->set_discount_total( (double) get_post_meta( $order->get_id(), '_cart_discount', true ) - (double) get_post_meta( $order->get_id(), '_cart_discount_tax', true ) );
		}
	}

	/**
	 * Method to update an order in the database.
	 * @param WC_Order $order
	 */
	public function update( &$order ) {
		$order->set_version( WC_VERSION );

		$changes = $order->get_changes();

		// Only update the post when the post data changes.
		if ( array_intersect( array( 'date_created', 'date_modified', 'status', 'parent_id', 'post_excerpt' ), array_keys( $changes ) ) ) {
			wp_update_post( array(
				'ID'                => $order->get_id(),
				'post_date'         => date( 'Y-m-d H:i:s', $order->get_date_created( 'edit' )->getOffsetTimestamp() ),
				'post_date_gmt'     => date( 'Y-m-d H:i:s', $order->get_date_created( 'edit' )->getTimestamp() ),
				'post_status'       => 'wc-' . ( $order->get_status( 'edit' ) ? $order->get_status( 'edit' ) : apply_filters( 'woocommerce_default_order_status', 'pending' ) ),
				'post_parent'       => $order->get_parent_id(),
				'post_excerpt'      => $this->get_post_excerpt( $order ),
				'post_modified'     => isset( $changes['date_modified'] ) ? date( 'Y-m-d H:i:s', $order->get_date_modified( 'edit' )->getOffsetTimestamp() ) : current_time( 'mysql' ),
				'post_modified_gmt' => isset( $changes['date_modified'] ) ? date( 'Y-m-d H:i:s', $order->get_date_modified( 'edit' )->getTimestamp() ) : current_time( 'mysql', 1 ),
			) );
		}
		$this->update_post_meta( $order );
		$order->save_meta_data();
		$order->apply_changes();
		$this->clear_caches( $order );
	}

	/**
	 * Method to delete an order from the database.
	 * @param WC_Order
	 * @param array $args Array of args to pass to the delete method.
	 */
	public function delete( &$order, $args = array() ) {
		$id   = $order->get_id();
		$args = wp_parse_args( $args, array(
			'force_delete' => false,
		) );

		if ( $args['force_delete'] ) {
			wp_delete_post( $id );
			$order->set_id( 0 );
			do_action( 'woocommerce_delete_order', $id );
		} else {
			wp_trash_post( $id );
			$order->set_status( 'trash' );
			do_action( 'woocommerce_trash_order', $id );
		}
	}

	/*
	|--------------------------------------------------------------------------
	| Additional Methods
	|--------------------------------------------------------------------------
	*/

	/**
	 * Excerpt for post.
	 *
	 * @param  WC_order $order
	 * @return string
	 */
	protected function get_post_excerpt( $order ) {
		return '';
	}

	/**
	 * Get a title for the new post type.
	 *
	 * @return string
	 */
	protected function get_post_title() {
		// @codingStandardsIgnoreStart
		/* translators: %s: Order date */
		return sprintf( __( 'Order &ndash; %s', 'woocommerce' ), strftime( _x( '%b %d, %Y @ %I:%M %p', 'Order date parsed by strftime', 'woocommerce' ) ) );
		// @codingStandardsIgnoreEnd
	}

	/**
	 * Read order data. Can be overridden by child classes to load other props.
	 *
	 * @param WC_Order
	 * @param object $post_object
	 * @since 3.0.0
	 */
	protected function read_order_data( &$order, $post_object ) {
		$id = $order->get_id();

		$order->set_props( array(
			'currency'           => get_post_meta( $id, '_order_currency', true ),
			'discount_total'     => get_post_meta( $id, '_cart_discount', true ),
			'discount_tax'       => get_post_meta( $id, '_cart_discount_tax', true ),
			'shipping_total'     => get_post_meta( $id, '_order_shipping', true ),
			'shipping_tax'       => get_post_meta( $id, '_order_shipping_tax', true ),
			'cart_tax'           => get_post_meta( $id, '_order_tax', true ),
			'total'              => get_post_meta( $id, '_order_total', true ),
			'version'            => get_post_meta( $id, '_order_version', true ),
			'prices_include_tax' => metadata_exists( 'post', $id, '_prices_include_tax' ) ? 'yes' === get_post_meta( $id, '_prices_include_tax', true ) : 'yes' === get_option( 'woocommerce_prices_include_tax' ),
		) );

		// Gets extra data associated with the order if needed.
		foreach ( $order->get_extra_data_keys() as $key ) {
			$function = 'set_' . $key;
			if ( is_callable( array( $order, $function ) ) ) {
				$order->{$function}( get_post_meta( $order->get_id(), '_' . $key, true ) );
			}
		}
	}

	/**
	 * Helper method that updates all the post meta for an order based on it's settings in the WC_Order class.
	 *
	 * @param WC_Order
	 * @since 3.0.0
	 */
	protected function update_post_meta( &$order ) {
		$updated_props     = array();
		$meta_key_to_props = array(
			'_order_currency'     => 'currency',
			'_cart_discount'      => 'discount_total',
			'_cart_discount_tax'  => 'discount_tax',
			'_order_shipping'     => 'shipping_total',
			'_order_shipping_tax' => 'shipping_tax',
			'_order_tax'          => 'cart_tax',
			'_order_total'        => 'total',
			'_order_version'      => 'version',
			'_prices_include_tax' => 'prices_include_tax',
		);

		$props_to_update = $this->get_props_to_update( $order, $meta_key_to_props );

		foreach ( $props_to_update as $meta_key => $prop ) {
			$value = $order->{"get_$prop"}( 'edit' );

			if ( 'prices_include_tax' === $prop ) {
				$value = $value ? 'yes' : 'no';
			}

			if ( update_post_meta( $order->get_id(), $meta_key, $value ) ) {
				$updated_props[] = $prop;
			}
		}

		do_action( 'woocommerce_order_object_updated_props', $order, $updated_props );
	}

	/**
	 * Clear any caches.
	 *
	 * @param WC_Order
	 * @since 3.0.0
	 */
	protected function clear_caches( &$order ) {
		clean_post_cache( $order->get_id() );
		wc_delete_shop_order_transients( $order );
		wp_cache_delete( 'object-' . $order->get_id(), 'orders' );
	}

	/**
	 * Read order items of a specific type from the database for this order.
	 *
	 * @param  WC_Order $order
	 * @param  string $type
	 * @return array
	 */
	public function read_items( $order, $type ) {
		global $wpdb;

		$get_items_sql = $wpdb->prepare( "SELECT order_item_type, order_item_id, order_id, order_item_name FROM {$wpdb->prefix}woocommerce_order_items WHERE order_id = %d AND order_item_type = %s ORDER BY order_item_id;", $order->get_id(), $type );
		$items         = $wpdb->get_results( $get_items_sql );

		if ( ! empty( $items ) ) {
			$items = array_map( array( 'WC_Order_Factory', 'get_order_item' ), array_combine( wp_list_pluck( $items, 'order_item_id' ), $items ) );
		} else {
			$items = array();
		}

		return $items;
	}

	/**
	 * Remove all line items (products, coupons, shipping, taxes) from the order.
	 *
	 * @param WC_Order
	 * @param string $type Order item type. Default null.
	 */
	public function delete_items( $order, $type = null ) {
		global $wpdb;
		if ( ! empty( $type ) ) {
			$wpdb->query( $wpdb->prepare( "DELETE FROM itemmeta USING {$wpdb->prefix}woocommerce_order_itemmeta itemmeta INNER JOIN {$wpdb->prefix}woocommerce_order_items items WHERE itemmeta.order_item_id = items.order_item_id AND items.order_id = %d AND items.order_item_type = %s", $order->get_id(), $type ) );
			$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}woocommerce_order_items WHERE order_id = %d AND order_item_type = %s", $order->get_id(), $type ) );
		} else {
			$wpdb->query( $wpdb->prepare( "DELETE FROM itemmeta USING {$wpdb->prefix}woocommerce_order_itemmeta itemmeta INNER JOIN {$wpdb->prefix}woocommerce_order_items items WHERE itemmeta.order_item_id = items.order_item_id and items.order_id = %d", $order->get_id() ) );
			$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}woocommerce_order_items WHERE order_id = %d", $order->get_id() ) );
		}
	}

	/**
	 * Get token ids for an order.
	 *
	 * @param WC_Order
	 * @return array
	 */
	public function get_payment_token_ids( $order ) {
		$token_ids = array_filter( (array) get_post_meta( $order->get_id(), '_payment_tokens', true ) );
		return $token_ids;
	}

	/**
	 * Update token ids for an order.
	 *
	 * @param WC_Order
	 * @param array $token_ids
	 */
	public function update_payment_token_ids( $order, $token_ids ) {
		update_post_meta( $order->get_id(), '_payment_tokens', $token_ids );
	}
}
