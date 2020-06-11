<?php


namespace ViewsCounterNoRaceConditionHelper\Classes;


use DateTime;

class Cache {

	public $timeout;
	public $field;
	public $cachePrefix = 'views_post_id_';
	public static $interval = 15;

	public function __construct( $key ) {
		$this->timeout = MINUTE_IN_SECONDS * self::$interval;
		$this->field   = $key;

		add_filter( 'BroPostViewsCounter__cache', [ $this, 'cacheFilter' ], 10, 2 );

		add_action( 'save_post', function ( $post_ID ) {
			$field = $this->cachePrefix . $post_ID;
			delete_transient( $field );
		}, 10, 1 );

	}

	public function cacheFilter( $flag, $post_id ) {
		$datetime2 = new DateTime( 'yesterday' );
		$datetime1 = new DateTime( get_the_date( 'Y-n-j', $post_id ) );
		$interval  = $datetime1->diff( $datetime2 );

		if ( "1" == $interval->format( '%a' ) ) {
			return $flag;
		}

		return $this->cache( $post_id );
	}

	public function cache( $post_id ) {
		$field = $this->cachePrefix . $post_id;

		$cached = get_transient( $field );

		if ( ! empty( $cached ) ) {
			return $cached;
		}

		$count = get_post_meta( $post_id, $this->field, true );

		$count = apply_filters( 'BroPostViewsCounter__cache_filter', $count, $post_id );

		set_transient( $field, $count, $this->timeout );

		return $count;
	}

}