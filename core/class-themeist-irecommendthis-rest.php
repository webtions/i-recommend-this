<?php
/**
 * REST API integration for I Recommend This.
 *
 * @package IRecommendThis
 * @subpackage Core
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Registers REST fields and routes for recommendations.
 *
 * @since 4.1.0
 */
class Themeist_IRecommendThis_Rest {

	/**
	 * REST API namespace (versioned).
	 *
	 * @var string
	 */
	const NAMESPACE_V1 = 'irecommendthis/v1';

	/**
	 * Hook into WordPress.
	 */
	public function initialize() {
		add_action( 'rest_api_init', array( $this, 'register_rest_routes_and_fields' ) );
	}

	/**
	 * Register REST routes and the post like-count field.
	 */
	public function register_rest_routes_and_fields() {
		$post_types = $this->get_rest_post_types();

		foreach ( $post_types as $post_type ) {
			register_rest_field(
				$post_type,
				'irt_likes',
				array(
					'get_callback'    => array( $this, 'get_post_like_count_for_rest' ),
					'schema'       => array(
						'description' => __( 'Number of recommendations (likes) for this post.', 'i-recommend-this' ),
						'type'        => 'integer',
						'readonly'    => true,
						'context'     => array( 'view', 'edit', 'embed' ),
					),
				)
			);
		}

		register_rest_route(
			self::NAMESPACE_V1,
			'/posts/(?P<id>\d+)/like',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'like_post' ),
				'permission_callback' => array( $this, 'like_permission' ),
				'args'                => array(
					'id'          => array(
						'description' => __( 'Unique identifier for the post.', 'i-recommend-this' ),
						'type'        => 'integer',
						'required'    => true,
					),
					'unrecommend' => array(
						'description' => __( 'If true, unlike the post (matches AJAX behaviour).', 'i-recommend-this' ),
						'type'        => array( 'string', 'boolean' ),
						'default'     => false,
					),
				),
			)
		);

		/**
		 * Fires after REST routes and fields for I Recommend This are registered.
		 *
		 * @since 4.1.0
		 */
		do_action( 'irecommendthis_rest_registered' );
	}

	/**
	 * Resolve post types that expose the `irt_likes` REST field.
	 *
	 * @return string[]
	 */
	private function get_rest_post_types() {
		/**
		 * Filter which post types include the `irt_likes` REST field.
		 *
		 * @since 4.1.0
		 * @param string[] $post_types Post type names. Default `post` only.
		 */
		$post_types = apply_filters( 'irecommendthis_rest_post_types', array( 'post' ) );
		$post_types = array_filter( array_map( 'sanitize_key', (array) $post_types ) );

		$valid = array();
		foreach ( $post_types as $post_type ) {
			if ( post_type_exists( $post_type ) ) {
				$valid[] = $post_type;
			}
		}

		if ( empty( $valid ) && post_type_exists( 'post' ) ) {
			$valid[] = 'post';
		}

		return $valid;
	}

	/**
	 * REST callback: integer like count from `_recommended` meta.
	 *
	 * @param array $object Post REST field object.
	 * @return int
	 */
	public function get_post_like_count_for_rest( $object ) {
		$post_id = isset( $object['id'] ) ? (int) $object['id'] : 0;
		if ( $post_id <= 0 ) {
			return 0;
		}

		$meta = get_post_meta( $post_id, '_recommended', true );
		if ( '' === $meta || false === $meta ) {
			return 0;
		}

		return (int) $meta;
	}

	/**
	 * Permission check for POST like route.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return bool|\WP_Error
	 */
	public function like_permission( $request ) {
		$post_id = (int) $request->get_param( 'id' );
		$post    = $post_id > 0 ? get_post( $post_id ) : null;

		/**
		 * Override permission for the REST like endpoint.
		 *
		 * Return null to use the default rules. Return true/false, or a WP_Error.
		 *
		 * @since 4.1.0
		 * @param null|bool|\WP_Error $permission Current decision. Default null (use plugin defaults).
		 * @param \WP_REST_Request    $request    Request object.
		 * @param \WP_Post|null       $post       Post object if the ID exists, else null.
		 */
		$filtered = apply_filters( 'irecommendthis_rest_like_permission', null, $request, $post );
		if ( null !== $filtered ) {
			if ( is_wp_error( $filtered ) ) {
				return $filtered;
			}
			return (bool) $filtered;
		}

		if ( ! is_user_logged_in() ) {
			return new WP_Error(
				'rest_not_logged_in',
				__( 'You must be logged in to recommend posts via the REST API.', 'i-recommend-this' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		if ( ! $post ) {
			return true;
		}

		$allowed_types = apply_filters( 'irecommendthis_rest_post_types', array( 'post' ) );
		$allowed_types = array_map( 'sanitize_key', (array) $allowed_types );
		if ( ! in_array( $post->post_type, $allowed_types, true ) ) {
			return new WP_Error(
				'rest_invalid_post_type',
				__( 'Recommendations are not enabled for this post type.', 'i-recommend-this' ),
				array( 'status' => 404 )
			);
		}

		if ( 'publish' !== $post->post_status ) {
			return new WP_Error(
				'rest_post_not_published',
				__( 'Only published posts can be recommended via the REST API.', 'i-recommend-this' ),
				array( 'status' => 404 )
			);
		}

		if ( ! current_user_can( 'read_post', $post_id ) ) {
			return new WP_Error(
				'rest_cannot_read_post',
				__( 'Sorry, you are not allowed to recommend this post.', 'i-recommend-this' ),
				array( 'status' => 403 )
			);
		}

		return true;
	}

	/**
	 * POST handler: process recommendation using the same processor as AJAX.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function like_post( $request ) {
		$post_id = (int) $request->get_param( 'id' );

		/**
		 * Filter the post ID before processing (parity with AJAX).
		 *
		 * @since 4.0.0
		 * @param int $post_id The post ID.
		 */
		$post_id = (int) apply_filters( 'irecommendthis_ajax_post_id', $post_id );

		$post = get_post( $post_id );
		if ( ! $post ) {
			return new WP_Error(
				'rest_post_invalid_id',
				__( 'Invalid post ID.', 'i-recommend-this' ),
				array( 'status' => 404 )
			);
		}

		$allowed_types = apply_filters( 'irecommendthis_rest_post_types', array( 'post' ) );
		$allowed_types = array_map( 'sanitize_key', (array) $allowed_types );
		if ( ! in_array( $post->post_type, $allowed_types, true ) ) {
			return new WP_Error(
				'rest_invalid_post_type',
				__( 'Recommendations are not enabled for this post type.', 'i-recommend-this' ),
				array( 'status' => 404 )
			);
		}

		if ( 'publish' !== $post->post_status ) {
			return new WP_Error(
				'rest_post_not_published',
				__( 'Only published posts can be recommended via the REST API.', 'i-recommend-this' ),
				array( 'status' => 404 )
			);
		}

		$unrecommend = $this->parse_unrecommend_param( $request );

		$options          = get_option( 'irecommendthis_settings' );
		$text_zero_suffix = isset( $options['text_zero_suffix'] ) ? sanitize_text_field( $options['text_zero_suffix'] ) : '';
		$text_one_suffix  = isset( $options['text_one_suffix'] ) ? sanitize_text_field( $options['text_one_suffix'] ) : '';
		$text_more_suffix = isset( $options['text_more_suffix'] ) ? sanitize_text_field( $options['text_more_suffix'] ) : '';

		Themeist_IRecommendThis_Public_Processor::process_recommendation(
			$post_id,
			$text_zero_suffix,
			$text_one_suffix,
			$text_more_suffix,
			'update',
			$unrecommend
		);

		$likes = (int) get_post_meta( $post_id, '_recommended', true );

		$data = array(
			'post_id' => $post_id,
			'likes'   => $likes,
			'message' => __( 'Recommendation updated.', 'i-recommend-this' ),
		);

		/**
		 * Filter the REST JSON response for a successful like/unlike request.
		 *
		 * @since 4.1.0
		 * @param array           $data    Response data.
		 * @param int             $post_id Post ID.
		 * @param \WP_REST_Request $request Request object.
		 */
		$data = apply_filters( 'irecommendthis_rest_like_response', $data, $post_id, $request );

		return new WP_REST_Response( $data, 200 );
	}

	/**
	 * Normalize unrecommend from JSON body, query string, or default.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return string `'true'` or `'false'`.
	 */
	private function parse_unrecommend_param( $request ) {
		$raw = $request->get_param( 'unrecommend' );

		if ( true === $raw || false === $raw ) {
			return true === $raw ? 'true' : 'false';
		}

		$str = strtolower( sanitize_text_field( (string) $raw ) );
		if ( '' === $str ) {
			return 'false';
		}

		return ( 'true' === $str || '1' === $str ) ? 'true' : 'false';
	}
}
