<?php 
/* 
 * Dashboard Template
 *
 * @since 1.0.0
 * 
 * Retrieves the client ID and displays the archive for that client.
 * If no ID is sent, a search box is displayed.
 *
 **/

/* Check for the Client ID */
$key = false;
$verify = false;
$query_id = false;

if(isset($_GET['client_id']) && isset($_GET['key'])){
	
	$client_id = $_GET['client_id'];
	$key = $_GET['key'];
	
	// Verify client and key match
	if($key == sh_get_key($client_id)){	
		// Check if cookie isset and if it doesn't match the GET-client
		if(!isset($_COOKIE['sh_client_id']) || $_COOKIE['sh_client_id'] != $client_id){
			setcookie('sh_client_id', $client_id, 25920000 + time(),'/');
		}
		// Set $query_id
		$query_id = $client_id;
	}
	
	$verify = true;
	
} elseif(isset($_COOKIE['sh_client_id'])){
	$query_id = $_COOKIE['sh_client_id'];
	$verify = false;
	
} else{
	$query_id = false;
	$verify = true;
}


// Get the menu
get_header(); sh_get_menu($query_id, $key, $verify);

if($query_id):

	$args = array(
		'posts_per_page' => -1,
		'tax_query' => array(
			array(
				'taxonomy' => 'client',
				'field' => 'id',
				'terms' => array($query_id)
			)
		)
	);
	$the_query = new WP_Query( $args );
	
	if  ($the_query->have_posts()) : ?>
	
		<header>
			<section class="mini last">
				<?php $term = get_term( $query_id, 'client' ); ?>
				<p><?php echo $term->name; ?></p>
				<h1>Dashboard</h1>
			</section>
		</header><!-- #invoice-header -->
		
		<table class="invoice padded dashboard">
			<thead>
				<tr>
					<th class="id"><?php _e('ID #', 'sh_invoice'); ?></th>
					<th class="item"><?php _e('Title', 'sh_invoice'); ?></th>
					<th class="date"><?php _e('Date', 'sh_invoice'); ?></th>
					<th class="total"><?php _e('Total', 'sh_invoice'); ?></th>
					<th class="status"><?php _e('Status', 'sh_invoice'); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php while ($the_query->have_posts()) : $the_query->the_post(); ?>
				<?php 
					$payment_status = sh_get_invoice_status();
					$invoice_type = sh_get_invoice_type();
					
					if($payment_status == 'Paid'){
						$status = __('Paid', 'sh_invoice');
						$status_class = 'paid';
					} else if($payment_status != 'Paid' && $invoice_type != 'Quote'){
						$status = __('Pending', 'sh_invoice');
						$status_class = 'pending';
					} else if($invoice_type == 'Quote' && sh_get_quote_approved() != 'Not yet'){
						$status = __('Approved', 'sh_invoice');
						$status_class = 'approved';
					} else {
						$status = __('Pending', 'sh_invoice');
						$status_class = 'pending';
					}
				?>
					<tr>
						<td class="id"><a href="<?php the_permalink(); ?>"><?php sh_invoice_number(); ?></a></td>
						<td class="item"><a href="<?php the_permalink(); ?>"><?php the_title(); ?> <?php if($invoice_type == 'Quote'){ echo '<em>'.__('[Quote]', 'sh_invoice').'</em>'; } ?></a></td>
						<td><?php echo get_the_date(); ?></td>
						<td><?php the_invoice_total(); ?></td>
						<td class="status <?php echo $status_class; ?>"><a href="<?php the_permalink(); ?>"><?php echo $status; ?></a></td>
					</tr>
				<?php endwhile; ?>
			</tbody>
		</table>
		
		
	<?php else: // No posts available ?>
		<header>
			<section class="mini last">
				<h1>Dashboard</h1>
			</section>
		</header><!-- #invoice-header -->
		
		<section class="page-entry">
			Sorry we couldn't find anything. 
		</section>
	<?php endif; // has_posts() ?>

<?php else : // Didn't supply a query term ?>
	<header>
		<section class="mini last">
			<h1>Dashboard</h1>
		</section>
	</header><!-- #invoice-header -->
	
	<section class="alert">
		<?php 
		// A client ID and key weren't provided
		if($query_term == $key): ?>
			<p><strong><?php _e( 'Error:', 'sh_invoice' ); ?></strong> <?php _e( 'The Dashboard URL must consist of your client ID and key. Please contact us if you think this is a mistake.', 'sh_invoice' ); ?></p>
		<?php 
		// A key wasn't provided
		elseif(!$query_id): ?>
			<p><strong><?php _e( 'Error:', 'sh_invoice' ); ?></strong> <?php _e( "Your key is invalid. Please contact us if you think this is a mistake.", "sh_invoice" ); ?></p>
		<?php else: ?>
			<p><strong><?php _e( 'Error:', 'sh_invoice' ); ?></strong> <?php _e( "We couldn't find your information. Please contact us if you think this is a mistake.", "sh_invoice" ); ?></p>
		<?php endif; ?>
	</section>
	
	<section class="page-entry error-form">
		<h2><?php _e('Try Doing a Search', 'sh_invoice'); ?></h2>
		<form method="get" action="">
			<label for="client_id"><?php _e('Client ID:', 'sh_invoice'); ?></label>
			<input type="text" name="client_id" />
			
			<label for="key"><?php _e('Access Key:', 'sh_invoice'); ?></label>
			<input type="text" name="key" />
			
			<input class="btn" type="submit" value="<?php _e('Search', 'sh_invoice'); ?>" />
		</form>
	</section>
<?php endif; // $query_term?>

<?php get_footer(); ?>