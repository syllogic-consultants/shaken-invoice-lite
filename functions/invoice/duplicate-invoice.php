<?php
/**
 * Duplicates an invoice.
 * Based on WooCommerce, which is based on 'Duplicate Post' 
 * (http://www.lopo.it/duplicate-post-plugin/) by Enrico Battocchi
 *
 * @since 1.1
 */


/**
 * Duplicate a invoice action
 */
add_action('admin_action_duplicate_invoice', 'shaken_duplicate_invoice_action');

function shaken_duplicate_invoice_action() {

	if (! ( isset( $_GET['post']) || isset( $_POST['post'])  || ( isset($_REQUEST['action']) && 'duplicate_post_save_as_new_page' == $_REQUEST['action'] ) ) ) {
		wp_die(__('No invoice to duplicate has been supplied!', 'shaken'));
	}

	// Get the original page
	$id = (isset($_GET['post']) ? $_GET['post'] : $_POST['post']);
	check_admin_referer( 'shaken-duplicate-invoice_' . $id );
	$post = shaken_get_invoice_to_duplicate($id);

	// Copy the page and insert it
	if (isset($post) && $post!=null) {
		$new_id = shaken_create_duplicate_from_invoice($post);

		// If you have written a plugin which uses non-WP database tables to save
		// information about a page you can hook this action to dupe that data.
		do_action( 'shaken_duplicate_invoice', $new_id, $post );

		// Redirect to the edit screen for the new draft page
		wp_redirect( admin_url( 'post.php?action=edit&post=' . $new_id ) );
		exit;
	} else {
		wp_die(__('invoice creation failed, could not find original invoice:', 'shaken') . ' ' . $id);
	}
}

/**
 * Duplicate a invoice link on invoices list
 */
add_filter('post_row_actions', 'shaken_duplicate_invoice_link_row',10,2);
add_filter('page_row_actions', 'shaken_duplicate_invoice_link_row',10,2);
	
function shaken_duplicate_invoice_link_row($actions, $post) {
	
	if (function_exists('duplicate_post_plugin_activation')) return $actions;
	
	if (!current_user_can('manage_shaken')) return $actions;
	
	if ($post->post_type!='invoice') return $actions;
	
	$actions['duplicate'] = '<a href="' . wp_nonce_url( admin_url( 'admin.php?action=duplicate_invoice&amp;post=' . $post->ID ), 'shaken-duplicate-invoice_' . $post->ID ) . '" title="' . __("Make a duplicate from this invoice", 'shaken')
		. '" rel="permalink">' .  __("Duplicate", 'shaken') . '</a>';

	return $actions;
}

/**
 *  Duplicate a invoice link on edit screen
 */
add_action( 'post_submitbox_start', 'shaken_duplicate_invoice_post_button' );

function shaken_duplicate_invoice_post_button() {
	global $post;
	
	if (function_exists('duplicate_post_plugin_activation')) return;
	
	if (!current_user_can('manage_shaken')) return;
	
	if ($post->post_type!='invoice') return;
	
	if ( isset( $_GET['post'] ) ) :
		$notifyUrl = wp_nonce_url( admin_url( "admin.php?action=duplicate_invoice&post=" . $_GET['post'] ), 'shaken-duplicate-invoice_' . $_GET['post'] );
		?>
		<div id="duplicate-action"><a class="submitduplicate duplication"
			href="<?php echo esc_url( $notifyUrl ); ?>"><?php _e('Copy to a new draft', 'shaken'); ?></a>
		</div>
		<?php
	endif;
}

/**
 * Get a invoice from the database
 */
function shaken_get_invoice_to_duplicate($id) {
	global $wpdb;
	$post = $wpdb->get_results("SELECT * FROM $wpdb->posts WHERE ID=$id");
	if ($post->post_type == "revision"){
		$id = $post->post_parent;
		$post = $wpdb->get_results("SELECT * FROM $wpdb->posts WHERE ID=$id");
	}
	return $post[0];
}

/**
 * Function to create the duplicate
 */
function shaken_create_duplicate_from_invoice($post, $parent = 0) {
	global $wpdb;

	$new_post_author 	= wp_get_current_user();
	$new_post_date 		= current_time('mysql');
	$new_post_date_gmt 	= get_gmt_from_date($new_post_date);
	
	if ($parent>0) :
		$post_parent		= $parent;
		$suffix 			= '';
		$post_status     	= 'publish';
	else :
		$post_parent		= $post->post_parent;
		$post_status     	= 'draft';
		$suffix 			= __(" (Copy)", 'shaken');
	endif;
	
	$new_post_type 		= $post->post_type;
	$post_content    	= str_replace("'", "''", $post->post_content);
	$post_content_filtered = str_replace("'", "''", $post->post_content_filtered);
	$post_excerpt    	= str_replace("'", "''", $post->post_excerpt);
	$post_title      	= str_replace("'", "''", $post->post_title).$suffix;
	$post_name       	= str_replace("'", "''", $post->post_name);
	$comment_status  	= str_replace("'", "''", $post->comment_status);
	$ping_status     	= str_replace("'", "''", $post->ping_status);

	// Insert the new template in the post table
	$wpdb->query(
			"INSERT INTO $wpdb->posts
			(post_author, post_date, post_date_gmt, post_content, post_content_filtered, post_title, post_excerpt,  post_status, post_type, comment_status, ping_status, post_password, to_ping, pinged, post_modified, post_modified_gmt, post_parent, menu_order, post_mime_type)
			VALUES
			('$new_post_author->ID', '$new_post_date', '$new_post_date_gmt', '$post_content', '$post_content_filtered', '$post_title', '$post_excerpt', '$post_status', '$new_post_type', '$comment_status', '$ping_status', '$post->post_password', '$post->to_ping', '$post->pinged', '$new_post_date', '$new_post_date_gmt', '$post_parent', '$post->menu_order', '$post->post_mime_type')");

	$new_post_id = $wpdb->insert_id;

	// Copy the taxonomies
	shaken_duplicate_post_taxonomies($post->ID, $new_post_id, $post->post_type);

	// Copy the meta information
	shaken_duplicate_post_meta($post->ID, $new_post_id);

	return $new_post_id;
}

/**
 * Copy the taxonomies of a post to another post
 */
function shaken_duplicate_post_taxonomies($id, $new_id, $post_type) {
	global $wpdb;
	$taxonomies = get_object_taxonomies($post_type);
	foreach ($taxonomies as $taxonomy) {
		$post_terms = wp_get_object_terms($id, $taxonomy);
		for ($i=0; $i<count($post_terms); $i++) {
			wp_set_object_terms($new_id, $post_terms[$i]->slug, $taxonomy, true);
		}
	}
}

/**
 * Copy the meta information of a post to another post
 */
function shaken_duplicate_post_meta($id, $new_id) {
	global $wpdb;
	$post_meta_infos = $wpdb->get_results("SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$id");

	if (count($post_meta_infos)!=0) {
		$sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";
		foreach ($post_meta_infos as $meta_info) {
			$meta_key = $meta_info->meta_key;
			$meta_value = addslashes($meta_info->meta_value);
			$sql_query_sel[]= "SELECT $new_id, '$meta_key', '$meta_value'";
		}
		$sql_query.= implode(" UNION ALL ", $sql_query_sel);
		$wpdb->query($sql_query);
	}
}
?>