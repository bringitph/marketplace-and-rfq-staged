<?php
/**
 * Dynamically loads classes
 *
 * @package Multi Vendor Marketplace
 */

namespace WkMarketplace;

defined( 'ABSPATH' ) || exit;

spl_autoload_register( 'WkMarketplace\wkmp_namespace_class_autoload' );

/**
 * Autoload callback
 *
 * @param string $class_name The name of the class to load.
 */
function wkmp_namespace_class_autoload( $class_name ) {
	if ( false === strpos( $class_name, 'WkMarketplace' ) ) {
		return;
	}

	$file_parts = explode( '\\', $class_name );
	$namespace  = '';
	$filepath   = '';

	for ( $i = count( $file_parts ) - 1; $i > 0; $i -- ) {
		$current = strtolower( $file_parts[ $i ] );
		$current = str_ireplace( '_', '-', $current );

		if ( count( $file_parts ) - 1 === $i ) {
			if ( strpos( strtolower( $file_parts[ count( $file_parts ) - 1 ] ), 'interface' ) ) {
				$interface_name = explode( '_', $file_parts[ count( $file_parts ) - 1 ] );
				array_pop( $interface_name );
				$interface_name = strtolower( implode( '-', $interface_name ) );
				$file_name      = "interface-{$interface_name}.php";
			} else {
				$file_name = "class-{$current}.php";
			}
		} else {
			$namespace = '/' . esc_attr( $current ) . esc_attr( $namespace );
		}

		$filepath  = trailingslashit( dirname( dirname( __FILE__ ) ) . esc_attr( $namespace ) );
		$filepath .= $file_name;
	}

	// If the file exists in the specified path, then include it.
	if ( file_exists( $filepath ) ) {
		require_once $filepath;
	}
}
