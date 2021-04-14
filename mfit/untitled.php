<?php

$order_item_ids = $wpdb->get_results( "SELECT DISTINCT(order_item_id) FROM `{$wpdb->prefix}wcrp_rental_products_rentals`;" );

foreach ( $order_item_ids as $order_item_id ) {

	$order_item_id = $order_item_id->order_item_id;

	$event_data = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT MIN(`reserved_date`) as min_date, MAX(`reserved_date`) as max_date, order_id, product_id, quantity FROM `{$wpdb->prefix}wcrp_rental_products_rentals` WHERE order_item_id = %d;",
			$order_item_id
		)
	);

	if ( !empty( $event_data ) ) {

		$order_item_return_days = wc_get_order_item_meta( $order_item_id, 'wcrp_rental_products_return_days_threshold', true );
		$order_item_returned = wc_get_order_item_meta( $order_item_id, 'wcrp_rental_products_returned', true );

		$pa_meta = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT meta_key, meta_value FROM `wp_woocommerce_order_itemmeta` WHERE `order_item_id` = %d AND `meta_key` LIKE %s;',
				$order_item_id,
				$wpdb->esc_like( 'pa_' ) . '%'
			)
		);

		if ( !empty( $pa_meta ) ) {

			$pa_meta_string = '';

			foreach ( $pa_meta as $pm ) {

				$pa_meta_string .= ucwords( str_replace( '_', ' ', str_replace( 'pa_', '', $pm->meta_key ) ) ) . ': ' . ucwords( $pm->meta_value ) . ', ';

			}

			$pa_meta_string = rtrim( $pa_meta_string, ', ' );

		} else {

			$pa_meta_string = '';

		}

		$rental_start_date = esc_html( $event_data[0]->min_date );
		$rental_end_date = gmdate( 'Y-m-d', strtotime( esc_html( $event_data[0]->max_date ) . ' -' . $order_item_return_days . ' days' ) );
		$rental_end_date = gmdate( 'Y-m-d', strtotime( $rental_end_date . ' +1 day' ) ); // Adds a day to max date as fullcalendar thinks the max date is at 00:00:00 so a 31st May 2020 max 

		$return_start_date = $rental_end_date;
		$return_end_date = esc_html( $event_data[0]->max_date );
		$return_end_date = gmdate( 'Y-m-d', strtotime( $return_end_date . ' +1 day' ) ); // Adds a day to max date as fullcalendar thinks the max date is at 00:00:00 so a 31st May 2020 max 

		$product_title = ( empty( wp_get_post_parent_id( esc_html( $event_data[0]->product_id ) ) ) ? get_the_title( esc_html( $event_data[0]->product_id ) ) : get_the_title( wp_get_post_parent_id( esc_html( $event_data[0]->product_id ) ) ) ); // If a variation use the parent product title (as get_the_title for variations includes for example Product Name - Blue), also see getting the meta later in the foreach

		$order_id = esc_html( $event_data[0]->order_id );
		$product_id = esc_html( $event_data[0]->product_id );
		$order_status = get_post_status( $order_id );
		$quantity = esc_html( $event_data[0]->quantity );

		// Set event color green if complete, red if date passed and not complete, blue if anything else (current/future rentals)

		if ( 'wc-completed' == $order_status ) {

			$event_color = esc_html( $event_color_green );

		} else {

			if ( 'yes' == $order_item_returned ) {

				$event_color = esc_html( $event_color_green );

			} else {

				$event_color = ( time() > strtotime( $return_end_date ) ? esc_html( $event_color_red ) : esc_html( $event_color_blue ) );

			}

		}

		// Event - Rental

		$events[] = array(
			'type' => 'rental',
			'color' => $event_color,
			'start' => $rental_start_date,
			'end' => $rental_end_date,
			'product_title' => $product_title,
			'product_id' => $product_id,
			'order_id' => $order_id,
			'quantity' => $quantity,
			'pa_meta' => $pa_meta_string
		);

		if ( $order_item_return_days > 0 ) {

			// Event - Return Days

			$events[] = array(
				'type' => 'return',
				'color' => $event_color,
				'start' => $return_start_date,
				'end' => $return_end_date,
				'product_title' => $product_title,
				'product_id' => $product_id,
				'order_id' => $order_id,
				'quantity' => $quantity,
				'pa_meta' => $pa_meta_string
			);

		}

	}

}

?>

<script>
	document.addEventListener('DOMContentLoaded', function() {
		var calendarEl = document.getElementById( 'wcrp-rental-products-rentals-calendar' );
		var calendar = new FullCalendar.Calendar( calendarEl, {
			headerToolbar: {
				left: 'title',
				center: 'dayGridMonth,dayGridWeek,dayGridDay,listMonth',
				right: 'prev,next today'
			},
			footerToolbar: {
				right: 'prev,next today'
			},
			height: 'auto',
			themeSystem: 'standard',
			navLinks: true, // Can click day/week names to navigate views
			weekNumbers: true,
			weekText: '<?php esc_html_e( 'Week', 'wcrp-rental-products' ); ?>',
			events: [
			<?php
			if ( !empty( $events ) ) {
				foreach ( $events as $event ) {
					$title_suffix = ( 'return' == $event['type'] ? esc_html__( ' - Return expected', 'wcrp-rental-products' ) : '' );
					?>
					{
						color: "<?php echo esc_html( $event['color'] ); ?>",
						title: "<?php echo esc_html( $event['quantity'] ); ?> <?php esc_html_e( 'x', 'wcrp-rental-products' ); ?> <?php echo esc_html( $event['product_title'] ) . ( !empty( $event['pa_meta'] ) ? ' (' . esc_html( $event['pa_meta'] ) . ')' : '' ); ?> <?php echo esc_html__( '#', 'wcrp-rental-products' ) . esc_html( $event['product_id'] ); ?> <?php esc_html_e( '/', 'wcrp-rental-products' ); ?> <?php esc_html_e( 'Order #', 'wcrp-rental-products' ); ?><?php echo esc_html( $event['order_id'] ) . esc_html( $title_suffix ); ?>",
						url: "<?php echo esc_url( get_admin_url() ) . 'post.php?post=' . esc_html( $event['order_id'] ) . '&action=edit'; // We specifically do not use get_edit_post_link() as would require html_entity_decode to get into correct URL in JS but then no way to escape it for WPCS ?>",
						start: "<?php echo esc_html( $event['start'] ); ?>",
						end: "<?php echo esc_html( $event['end'] ); ?>",
					},
					<?php
				}
			}
			?>
			]
		});
		document.getElementById( 'wcrp-rental-products-rentals-calendar' ).innerHTML = '';
		calendar.render();
	});
</script>
<style>
	#wcrp-rental-products-rentals-calendar .fc-button {
		background: <?php echo esc_html( $current_color_scheme->colors[2] ); ?> !important;
	}
	#wcrp-rental-products-rentals-calendar .fc-button.fc-button-active {
		background: <?php echo esc_html( $current_color_scheme->colors[3] ); ?> !important;
	}
</style>
<div id="wcrp-rental-products-rentals-calendar"><?php esc_html_e( 'Checking rentals...', 'wcrp-rental-products' ); ?></div>
