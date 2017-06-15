<?php
/*
Plugin Name: Permalinks to WP REST API
Plugin URI: http://www.worona.org/
Description: Retrieve WP-API results sending a permalink
Version: 1.0.0
Author: Worona Labs SL
Author URI: http://www.worona.org/
License: GPL v3
Copyright: Worona Labs SL
*/



if( !class_exists('permalinks_to_wp_rest_api') ):

class permalinks_to_wp_rest_api
{
	// vars
	public $plugin_version = '1.0.0';

	/*
	*  Constructor
	*
	*  This function will construct all the neccessary actions, filters and functions for the plugin to work
	*
	*  @type	function
	*  @date	@date	15/07/17
	*  @since 1.0.0
	*
	*  @param	N/A
	*  @return	N/A
	*/

	function __construct()
	{
		// actions
		add_action( 'rest_api_init', function () {
			register_rest_route( 'permalinks/v1', '/discover/', array(
				'methods' => 'GET',
				'callback' => array( $this,'discover_url'))
			);
		});
		// filters
	}

	/*
	*  init
	*
	*  This function is called during the 'init' action and will do things such as:
	*  create custom_post_types, register scripts, add actions / filters
	*
	*  @type	action (init)
	*  @date	15/07/17
	*  @since 1.0.0
	*
	*  @param	N/A
	*  @return	N/A
	*/

	function init()
	{
		// requires
	}

	/*
	*	@param \WP_REST_Request $request Full details about the request
	*/
	function discover_url( $request ) {

		if (!isset($request['url'])) {
			return array('Error' => 'url param is missing');
		}

		preg_match(  "/^\/?([^\/]+)(?:.*?)?([^\/]+)?\/?$/" , $request['url'], $matches );

		$first_folder = isset($matches[2]) ? $matches[1] : null;
		$last_folder = isset($matches[2]) ? $matches[2] : $matches[1];

		if (is_null($last_folder)) {
			return array('Error' => 'last_folder is missing');
		}

		// ----------------
		// Post
		// ----------------
		$args = array(
  		'name'        => $last_folder,
  		'numberposts' => 1,
		);
		$post = get_posts($args);
		if ( sizeof($post) > 0 ) {
      $request = new WP_REST_Request('GET', '/wp/v2/posts/' . $post[0]->ID);
			return rest_do_request($request);
		}

		// ----------------
		// Page
		// ----------------
		$args = array(
  		'name'        => $last_folder,
  		'numberposts' => 1,
			'post_type'		=> 'page',
		);
		$page = get_posts($args);
		if ( sizeof($page) > 0 ) {
			return $page[0];
		}

		// ----------------
		// Author
		// ----------------
		if($first_folder === 'author') {
			$args = array(
				'author_name'		=> $last_folder,
			);
			$author = get_posts($args);
			if ( sizeof($author) > 0 ) {
				return $author[0];
			} else {
				return( new stdClass() ); //empty object instead of null
			}
		}

		// ----------------
		// Category
		// ----------------
		$category = get_term_by('slug',$last_folder,'category');
		if( $category ) {
			return $category;
		}

		// ----------------
		// Tag
		// ----------------
		$tag = get_term_by('slug',$last_folder,'tag');
		if( $tag ) {
			return $tag;
		}

		// ----------------
		// Custom Post type
		// ----------------

		$post_types = get_post_types('','object');
		$post_type = '';

		foreach ($post_types as $p) {
			if( $p->rewrite['slug'] == $first_folder ) {
				$post_type = $p->name;
			}
		}

		if ( $post_type !== '' ) {
			$args = array(
				'name'        => $last_folder,
				'numberposts' => 1,
				'post_type'		=> $post_type,
			);
			$custom_post = get_posts($args);

			if ( sizeof($custom_post) > 0 ) {
				return $custom_post[0];
			}
		}

		// ----------------
		// Custom Taxonomy
		// ----------------
		$taxonomies = get_taxonomies('','object');
		$taxonomy = '';

		foreach ($taxonomies as $t) {
			if( $t->rewrite['slug'] === $first_folder ) {
				$taxonomy = $t->name;
			}
		}

		if ( $taxonomy === '' ) {
			return array('Error' => $first_folder . ' not supported');
		}

		$custom_taxonomy = get_term_by('slug',$last_folder,$taxonomy);

		if( $custom_taxonomy ) {
			return $custom_taxonomy;
		} else {
				return array('Error' => $first_folder . 'not supported');
		}

		// ----------------
		// first_folder not found
		// ----------------
		return array('Error' => 'We couldn\'t any result with '. $url);
	}

}

/*
*  permalinks_to_wp_rest_api
*
*  The main function responsible for returning the one true permalinks_to_wp_rest_api Instance
*  to functions everywhere. Use this function like you would a global variable,
*  except without needing to declare the global.
*
*  Example: <?php $permalinks_to_wp_rest_api = permalinks_to_wp_rest_api(); ?>
*
*  @type	function
*  @date	15/07/17
*  @since 1.0.0
*
*  @param	N/A
*  @return	(object)
*/

function permalinks_to_wp_rest_api()
{
	global $permalinks_to_wp_rest_api;

	if( !isset($permalinks_to_wp_rest_api) )
	{
		$permalinks_to_wp_rest_api = new permalinks_to_wp_rest_api();
	}

	return $permalinks_to_wp_rest_api;
}

// initialize
permalinks_to_wp_rest_api();

endif; // class_exists check
