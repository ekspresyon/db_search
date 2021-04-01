<?php
/**
 * Plugin Name: BU DBsearch
 * Description: Search database for spacific string and return location of string
 */



	/**
	 * Report on text in post content
	 * Author: jdub233
	 * License: GPLv2 or later
	 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
	 * Version: 0.1
	 */
    class DBTextSearch {
		/**
		 * Scans sites for a particular string.
		 *
		 * Currently hard coded to scan for the string 'wechat' on line 51, but can be customized.
		 *
		 * @alias post-text
		 *
		 * @param array $args Positional args.
		 * @param array $args_assoc Assocative args.
		 */
		public function find( $args, $args_assoc ) {
            $srchSrting = $args[0];
			global $wpdb;
			$blogs = $wpdb->get_results( 'SELECT * FROM wp_blogs' );
			if ( ! $blogs ) {
				\WP_CLI::error( 'No blogs found' );
			}

			// Setup a table to return the data.
			$output = new \cli\Table();
			$output->setHeaders(
				array(
					'blog_id',
					'url',
					'post_id',
					'post_title',
					'post_url'
				)
			);

			foreach ( $blogs as $blog ) {
				$site_url = 'https://' . $blog->domain . $blog->path;

				// Get post IDs where the post_content contains the target string.
				$query  = sprintf( "SELECT ID, post_title, guid FROM wp_%s_posts WHERE post_status='publish' AND post_content LIKE '%%".$srchSrting."%%';", $blog->blog_id );
				$result = $wpdb->get_results( $query );

				foreach ($result as $hit) {
					$hit_id = $hit->ID;
					$hit_title = $hit->post_title;
					$hit_url = $hit->guid;
				
				

					$row = array(
						$blog->blog_id,
						$site_url,
						$hit_id,
						$hit_title,
						$hit_url,
					);

					$output->addRow( $row );

				}

			}

			$output->display();
		}
    }

	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		\WP_CLI::add_command( 'search-text', __CLASS__ . '\\DBTextSearch' );
	}
