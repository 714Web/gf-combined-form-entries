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
		$form_id = 0;
		$start_date = date( 'Y-m-d', strtotime('-30 days') );
		$end_date = date( 'Y-m-d', time() );
		$search_criteria['start_date'] = $start_date;
		$search_criteria['end_date'] = $end_date;
		$search_criteria['status'] = "active";

		$results = GFAPI::get_entries($form_id, $search_criteria);
		
		return $results;
	}


	// # ADMIN FUNCTIONS --------------------------------------------------------------------------------------

	/**
	 * Creates a custom page for this add-on.
	 */
	public function plugin_page() {
		$results = $this->get_all_form_entries();
		?>
		<div class="wrap px-0">
			<p>Recent entries from all forms in one location:</p>

			<div id="universal-message-container">
				<div class="container">
					<div class="row">
						<div class="col">
							<p class="my-4 lead"><strong><?php echo count($results); ?> entries from all forms in the last 30 days</strong></p>
						</div>
					</div>
				</div>
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

		</div><!-- .wrap -->
		<?php
	}



}
