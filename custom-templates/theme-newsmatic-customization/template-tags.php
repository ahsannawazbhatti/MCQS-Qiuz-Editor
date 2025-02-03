<?php
/**
 * Custom template tags for this theme
 *
 * Eventually, some of the functionality here could be replaced by core features.
 *
 * @package Newsmatic
 */
use Newsmatic\CustomizerDefault as ND;

if( ! function_exists( 'newsmatic_tags_list_mcqs' ) ) :
	/**
	 * print the html for tags list
	 */
	function newsmatic_tags_list_mcqs() {
		// Hide category and tag text for pages.
		if ( 'mcqs' === get_post_type() ) {
			/* translators: used between list items, there is a space after the comma */
			$tags_list = get_the_tag_list( '', ' ' );
			if ( $tags_list ) {
				/* translators: 1: list of tags. */
				printf( '<span class="tags-links">' . esc_html__( 'Tagged: %1$s', 'newsmatic' ) . '</span>', $tags_list ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
		}
	}
endif;
if( ! function_exists( 'newsmatic_categories_list_mcqs' ) ) :
	/**
	 * print the html for categories list
	 */
	function newsmatic_categories_list_mcqs() {
		// Hide category and tag text for pages.
		if ( 'mcqs' === get_post_type() ) {
			/* translators: used between list items, there is a space after the comma */
			$categories_list = get_the_category_list( esc_html__( ', ', 'newsmatic' ) );
			if ( $categories_list ) {
				/* translators: 1: list of categories. */
				printf( '<span class="cat-links">' . esc_html__( 'Posted in %1$s', 'newsmatic' ) . '</span>', $categories_list ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
		}
	}
endif;