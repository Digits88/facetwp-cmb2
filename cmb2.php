<?php
/**
 * Plugin Name: CMB2 Integration for FacetWP
 * Plugin URI: https://github.com/WebDevStudios/facetwp-cmb2
 * Description: Allow FacetWP to properly utilize data created and stored by CMB2.
 * Version: 1.0
 * Author: Matt Gibbs, Jeremy Pry (WebDevStudios)
 * License: GPL2
 */

// Prevent direct access to this file
if ( ! defined( 'ABSPATH' ) ) {
	die( "You can't do anything by accessing this file directly." );
}

// Check to see if CMB2 is loaded.
if ( ! defined( 'CMB2_LOADED' ) ) {
	return;
}

// Check to see if FacetWP is loaded.
if ( ! function_exists( 'FWP' ) ) {
	return;
}

class FacetWP_Integration_CMB2 {

	public function __construct() {

		// Add CMB2 fields to the Data Sources dropdown
		add_filter( 'facetwp_facet_sources', array( $this, 'facet_sources' ) );

		// CMB2 field handler
		add_filter( 'facetwp_indexer_post_facet', array( $this, 'indexer_post_facet' ), 10, 2 );
	}


	/**
	 * Add CMB2 fields to the Data Sources dropdown
	 *
	 * @param array $sources The current set of data sources.
	 * @return array The updated set of data sources.
	 */
	public function facet_sources( $sources ) {
		$sources['cmb2'] = array(
			'label'   => 'CMB2',
			'choices' => array(),
		);

		// Get every CMB2 registered field as an array
		$fields = $this->get_cmb_fields();
		foreach ( $fields as $field ) {
			// The Field ID string is used later to determine the metabox and field ID
			$field_id_string = "cmb2/{$field['metabox_id']}/{$field['id']}";

			$sources['cmb2']['choices'][ $field_id_string ] = $field['label'];
		}

		return $sources;
	}

	/**
	 * Index CMB2 field data
	 *
	 * @param bool $return
	 * @param array $params
	 *
	 * @return bool
	 */
	public function indexer_post_facet( $return, $params ) {
		$defaults = $params['defaults'];
		$facet    = $params['facet'];

		// Debugging!
		if ( WP_DEBUG ) {
			error_log( "Param info: " . print_r( $params, true ) . PHP_EOL, 3, WP_CONTENT_DIR . '/facet.log' );
		}

		if ( 'cmb2/' == substr( $facet['source'], 0, 4 ) ) {
			// TODO index the value
			$index = array();

			// return TRUE to prevent the default indexer from running
			return true;
		}

		return $return;
	}

	/**
	 * Get registered CMB2 fields.
	 *
	 * @return array Multidimensional array of field data. Each array item contains 'id', 'label',
	 *               and 'metabox_id' keys.
	 */
	protected function get_cmb_fields() {
		$return = array();
		$boxes  = CMB2_Boxes::get_all();
		foreach ( $boxes as $cmb ) {
			$fields = $cmb->prop( 'fields', array() );

			foreach ( $fields as $field ) {
				$return[] = array(
					'id'         => $field['id'],
					'label'      => isset( $field['name'] ) ? $field['name'] : $field['id'],
					'metabox_id' => $cmb->cmb_id,
				);
			}
		}

		return $return;
	}
}


new FacetWP_Integration_CMB2();
