<?php

GFForms::include_addon_framework();

class GFCombinedFormEntries extends GFAddOn {

	protected $_version = GF_COMBINEDFORMENTRIES_VERSION;
	protected $_min_gravityforms_version = '1.9';
	protected $_slug = 'gf-combined-form-entries';
	protected $_path = 'gf-combined-form-entries/gfcombinedformentries.php';
	protected $_full_path = __FILE__;
	protected $_title = 'Gravity Forms Combined Form Entries';
	protected $_short_title = 'Entries - All Forms';

	private static $_instance = null;

	/**
	 * Get an instance of this class.
	 *
	 * @return GFCombinedFormEntries
	 */
	public static function get_instance() {
		if ( self::$_instance == null ) {
			self::$_instance = new GFCombinedFormEntries();
		}

		return self::$_instance;
	}

	/**
	 * Handles hooks and loading of language files.
	 */
	public function init() {
		parent::init();
	}


	// # SCRIPTS & STYLES --------------------------------------------------------------------------------------

	/**
	 * Return the scripts which should be enqueued.
	 *
	 * @return array
	 */
	public function scripts() {
		$scripts = array(
			array(
				'handle'  => 'bootstrap',
				'src'     => $this->get_base_url() . '/js/bootstrap.min.js',
				'version' => '5.3.1',
				'deps'    => array( 'jquery' ),
				'enqueue' => array(
					array(
						'admin_page' => array( 'plugin_page' ),
					)
				)
			),

		);

		return array_merge( parent::scripts(), $scripts );
	}

	/**
	 * Return the stylesheets which should be enqueued.
	 *
	 * @return array
	 */
	public function styles() {
		$styles = array(
			array(
				'handle'  => 'gfcombinedformentries-plugin-page',
				'src'     => $this->get_base_url() . '/css/gfcombinedformentries-plugin-page.css',
				'version' => $this->_version,
				'enqueue' => array(
					array(
						'admin_page' => array( 'plugin_page' ),
					)
				)
			),
			array(
				'handle'  => 'bootstrap',
				'src'     => $this->get_base_url() . '/css/bootstrap.min.css',
				'version' => '5.3.1',
				'enqueue' => array(
					array(
						'admin_page' => array( 'plugin_page' ),
						'tab'        => 'gfCombinedFormEntries'
					)
				)
			)
		);

		return array_merge( parent::styles(), $styles );
	}


	// # FRONTEND FUNCTIONS --------------------------------------------------------------------------------------------

	public function get_all_form_entries() {
		$search_criteria = array();
		$search_criteria['status'] = "active";
		$form_id = 0;
		$page_size = 20;
		$current_page = max( 1, $_REQUEST['pagenum'] );
		$offset   = ($current_page - 1) * $page_size;
		$sorting = array();
		$paging = array( 'offset' => $offset, 'page_size' => $page_size ); 
		$total_count = 0;
		$results = GFAPI::get_entries( $form_id, $search_criteria, $sorting, $paging, $total_count );
		$total_pages = ceil( $total_count / $page_size ); 

		// pass-through data
		$results['current_page'] = $current_page;
		$results['total_count'] = $total_count;
		$results['total_pages'] = $total_pages;
		
		return $results;
	}


	// # ADMIN FUNCTIONS --------------------------------------------------------------------------------------

	/**
	 * Creates a custom page for this add-on.
	 */
	public function plugin_page() {
		$results = $this->get_all_form_entries();

		// Build pagination
		$pagination_links = '';
		if ( $results['total_pages'] > 1 ) {
			$pagination_links   = '<nav aria-label="Pagination">';
			$pagination_links  .= paginate_links([
						'base'      => @add_query_arg('pagenum','%#%'),
						'format'    => '&pagenum=%#%',
						'current'   => $results['current_page'],
						'total'     => $results['total_pages'],
						'prev_next' => true,
						'prev_text' => '&laquo;',
						'next_text' => '&raquo;',
						'type' 		=> 'list',
					]);
			$pagination_links .= '</nav>';
		}
		// convert to bootstrap pagination
		$pagination_links = str_replace("<ul class='page-numbers'>", '<ul class="pagination pagination-sm mb-0 justify-content-end">', $pagination_links);
		$pagination_links = str_replace('page-numbers', 'page-link', $pagination_links);
		$pagination_links = str_replace('<li>', '<li class="page-item mb-0">', $pagination_links);
		$pagination_links = str_replace(
			'<li class="page-item mb-0"><span aria-current="page" class="page-link current">',
			'<li class="page-item mb-0 active" aria-current="page"><span class="page-link">',
			$pagination_links
		);
		?>

		<div class="wrap px-0">
			<div id="top-pagination">
				<div class="container px-0">
					<div class="row mx-auto w-100">
						<div class="col col-12 col-md-6 px-0">
							<p><strong><?php echo $results['total_count']; ?></strong> total active entries from all forms.</p>
						</div>
						<div class="col col-12 col-md-6 px-0 justify-content-end">
							<!-- pagination -->
							<?php echo $pagination_links; ?>
							<!-- pagination end -->
						</div>
					</div>
				</div>
			</div>
			<div id="universal-message-container">
				<div class="container">
					<div class="row">
						<div class="col col-1">Entry ID<br>Date/Time</div>
						<div class="col col-2"><br>Form Title</div>
						<div class="col col-2"><br>Source URL</div>
						<div class="col"><br>Other Submission Fields</div>
					</div>
				</div>
				<div class="container pt-2 mt-0">
		<?php
		// remove pass-through data before foreach
		unset($results['current_page']);
		unset($results['total_count']);
		unset($results['total_pages']);

		foreach ($results as $entry) {
			$id = $entry['id'];
			$form_id = $entry['form_id'];
			$date = $entry['date_created'];
			$source = $entry['source_url'];
			$url = admin_url('admin.php?page=gf_entries&view=entry&id='.$form_id.'&lid='.$id);
			
			$form = GFAPI::get_form( $form_id );
			$form_title = $form['title'];
			
			$build = '<div class="row py-2 border border-0 border-top border-light-subtle">';
			$build .= '<div class="col col-1">';
			$build .= '<a href="'.$url.'" target="_blank" class="pe-2">'.$id.'</a><br>';
			$build .= $date;
			$build .= '</div>';
			$build .= '<div class="col col-2"><strong>';
			$build .= $form_title;
			$build .= '</strong></div>';
			$build .= '<div class="col col-2" style="overflow: hidden;">';
			$build .= '<a href="'.$source.'" target="_blank" class="pe-2">'.$source.'</a>';
			$build .= '</div>';
			
			$i = 0;
			$extra = '';
			foreach( $entry as $key=>$value ) {
				if ( is_int( (int)$key ) && (int)$key !== 0 && !empty($value) ) {
					if ( $i > 3 ) {
						$extra = 'mt-2';
					}
					$build .= '<div class="col '.$extra.'">';
					$build .= $value;
					$build .= '</div>';
					$i++;
				}
			}
			
			$build .= '</div>';
			
			
			print_r( $build );
		}
		  
							?>
				</div>
				
			</div><!-- #universal-message-container -->

			<div id="bottom-pagination">
				<div class="container px-0 pt-1">
					<div class="row mx-auto w-100">
						<div class="col px-0 justify-content-end">
							<!-- pagination -->
							<?php echo $pagination_links; ?>
							<!-- pagination end -->
						</div>
					</div>
				</div>
			</div>
		</div><!-- .wrap -->

		<?php
	}
	

	/**
	 * Define dashicons
	 */
	public function get_app_menu_icon() {
		return $this->get_base_url() . '/img/gf-combinedformentries.svg';
	}
	public function get_menu_icon() {
		return $this->get_base_url() . '/img/gf-combinedformentries.svg';
	}



}
