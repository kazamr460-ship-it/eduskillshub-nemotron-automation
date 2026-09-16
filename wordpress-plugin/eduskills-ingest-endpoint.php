<?php
/**
 * Plugin Name: EduSkillsHub Ingest Endpoint
 * Description: Registers the /wp-json/eduskills/v1/ingest REST route used by the
 *              Nebius Token Factory + NVIDIA Nemotron content automation to push
 *              AI-generated course/listing copy into eduskillshub.site.
 * Version: 0.1.0
 * Author: Kazam Raza
 * License: MIT
 *
 * NOTE: This is a starting point, not a drop-in for an existing endpoint. If
 * /wp-json/eduskills/v1/ingest is already implemented elsewhere on the site,
 * use this file as documentation of the expected request shape and adapt the
 * real handler instead of activating this plugin alongside it.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access.
}

add_action( 'rest_api_init', function () {
	register_rest_route(
		'eduskills/v1',
		'/ingest',
		array(
			'methods'             => 'POST',
			'callback'            => 'eduskills_handle_ingest',
			// TODO: replace with real auth (shared secret header check, or a
			// logged-in capability check) before going live. Left open here
			// only so the automation can be tested end-to-end first.
			'permission_callback' => '__return_true',
			'args'                => array(
				'source'  => array( 'required' => true, 'type' => 'string' ),
				'content' => array( 'required' => true, 'type' => 'string' ),
			),
		)
	);
} );

/**
 * Handle an incoming { "source": "...", "content": "..." } payload from the
 * Make.com automation and store it against the relevant course/listing.
 *
 * TODO: replace the placeholder storage below with whatever eduskillshub.site
 * actually uses (e.g. update a specific post's meta/content, insert a new
 * draft post, or write to a custom table) once the target course/listing ID
 * is included in the automation's payload.
 */
function eduskills_handle_ingest( WP_REST_Request $request ) {
	$source  = sanitize_text_field( $request->get_param( 'source' ) );
	$content = wp_kses_post( $request->get_param( 'content' ) );

	if ( empty( $content ) ) {
		return new WP_Error( 'eduskills_empty_content', 'No content received.', array( 'status' => 400 ) );
	}

	// Placeholder: store the most recent AI-generated draft as an option so the
	// pipeline is verifiable end-to-end before wiring it to real course data.
	update_option( 'eduskills_last_ai_content', array(
		'source'      => $source,
		'content'     => $content,
		'received_at' => current_time( 'mysql' ),
	) );

	return new WP_REST_Response( array(
		'status'  => 'ok',
		'message' => 'Content received.',
	), 200 );
}
