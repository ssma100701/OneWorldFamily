<?php
/**
 * Common functions class for WP Event aggregator.
 *
 * @link       http://xylusthemes.com/
 * @since      1.0.0
 *
 * @package    Import_Eventbrite_Events
 * @subpackage Import_Eventbrite_Events/includes
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class Import_Eventbrite_Events_Common {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		add_action( 'wp_ajax_iee_render_terms_by_plugin', array( $this,'iee_render_terms_by_plugin' ) );	
		add_action( 'tribe_events_single_event_after_the_meta', array( $this, 'iee_add_tec_ticket_section' ) ) ;
		add_filter( 'the_content', array( $this, 'iee_add_em_add_ticket_section') );
		add_filter( 'mc_event_content', array( $this, 'iee_add_my_calendar_ticket_section') , 10, 4);
		add_action( 'iee_render_pro_notice', array( $this, 'render_pro_notice') );
	}	

	/**
	 * Format events arguments as per TEC
	 *
	 * @since    1.0.0
	 * @param array $eventbrite_event Eventbrite event.
	 * @return array
	 */
	public function render_import_into_and_taxonomy() {

		$active_plugins = $this->get_active_supported_event_plugins();
		?>	
		<tr class="event_plugis_wrapper">
			<th scope="row">
				<?php esc_attr_e( 'Import into','import-eventbrite-events' ); ?> :
			</th>
			<td>
				<select name="event_plugin" class="eventbrite_event_plugin">
					<?php
					if( !empty( $active_plugins ) ){
						foreach ($active_plugins as $slug => $name ) {
							?>
							<option value="<?php echo $slug;?>"><?php echo $name; ?></option>
							<?php
						}
					}
					?>
	            </select>
			</td>
		</tr>

		<tr class="event_cats_wrapper">
			<th scope="row">
				<?php esc_attr_e( 'Event Categories for Event Import','import-eventbrite-events' ); ?> : 
			</th>
			<td>
				<div class="event_taxo_terms_wraper">

				</div>
				<span class="iee_small">
		            <?php esc_attr_e( 'These categories are assign to imported event.', 'import-eventbrite-events' ); ?>
		        </span>
			</td>
		</tr>
		<?php		

	}

	/**
	 * Render Taxonomy Terms based on event import into Selection.
	 *
	 * @since 1.0
	 * @return void
	 */
	function iee_render_terms_by_plugin() {
		global $iee_events;
		$event_plugin  = esc_attr( $_REQUEST['event_plugin'] );
		$event_taxonomy = '';
		switch ( $event_plugin ) {
			case 'iee':
				$event_taxonomy = $iee_events->iee->get_taxonomy();
				break;

			case 'tec':
				$event_taxonomy = $iee_events->tec->get_taxonomy();
				break;

			case 'em':
				$event_taxonomy = $iee_events->em->get_taxonomy();
				break;

			case 'eventon':
				$event_taxonomy = $iee_events->eventon->get_taxonomy();
				break;

			case 'event_organizer':
				$event_taxonomy = $iee_events->event_organizer->get_taxonomy();
				break;

			case 'aioec':
				$event_taxonomy = $iee_events->aioec->get_taxonomy();
				break;

			case 'my_calendar':
				$event_taxonomy = $iee_events->my_calendar->get_taxonomy();
				break;
			
			default:
				break;
		}
		
		$terms = array();
		if ( $event_taxonomy != '' ) {
			if( taxonomy_exists( $event_taxonomy ) ){
				$terms = get_terms( $event_taxonomy, array( 'hide_empty' => false ) );
			}
		}
		if( ! empty( $terms ) ){ ?>
			<select name="event_cats[]" multiple="multiple">
		        <?php foreach ($terms as $term ) { ?>
					<option value="<?php echo $term->term_id; ?>">
	                	<?php echo $term->name; ?>                                	
	                </option>
				<?php } ?> 
			</select>
			<?php
		}
		wp_die();
	}

	/**
	 * Get Active supported active plugins.
	 *
	 * @since  1.0.0
	 * @return array
	 */
	public function get_active_supported_event_plugins() {

		$supported_plugins = array();
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}
		// check The Events Calendar active or not if active add it into array.
		if( class_exists( 'Tribe__Events__Main' ) ){
			$supported_plugins['tec'] = __( 'The Events Calendar', 'import-eventbrite-events' );
		}

		// check Events Manager.
		if( defined( 'EM_VERSION' ) ){
			$supported_plugins['em'] = __( 'Events Manager', 'import-eventbrite-events' );
		}
		
		// Check event_organizer.
		if( defined( 'EVENT_ORGANISER_VER' ) &&  defined( 'EVENT_ORGANISER_DIR' ) ){
			$supported_plugins['event_organizer'] = __( 'Event Organiser', 'import-eventbrite-events' );
		}

		// check EventON.
		if( class_exists( 'EventON' ) ){
			$supported_plugins['eventon'] = __( 'EventON', 'import-eventbrite-events' );
		}

		// check All in one Event Calendar
		if( class_exists( 'Ai1ec_Event' ) ){
			$supported_plugins['aioec'] = __( 'All in one Event Calendar', 'import-eventbrite-events' );
		}

		// check My Calendar
		if ( is_plugin_active( 'my-calendar/my-calendar.php' ) ) {
			$supported_plugins['my_calendar'] = __( 'My Calendar', 'import-eventbrite-events' );
		}		
		$supported_plugins['iee'] = __( 'Eventbrite Events', 'import-eventbrite-events' );
		return $supported_plugins;
	}

	/**
	 * Setup Featured image to events
	 *
	 * @since    1.0.0
	 * @param int $event_id event id.
	 * @param int $image_url Image URL
	 * @return void
	 */
	public function setup_featured_image_to_event( $event_id, $image_url = '' ) {
		if ( $image_url == '' ) {
			return;
		}
		$event = get_post( $event_id );
		if( Empty ( $event ) ){
			return;
		}

		require_once(ABSPATH . 'wp-admin/includes/media.php');
		require_once(ABSPATH . 'wp-admin/includes/file.php');
		require_once(ABSPATH . 'wp-admin/includes/image.php');

		$event_title = $event->post_title;
		//$image = media_sideload_image( $image_url, $event_id, $event_title );
		if ( ! empty( $image_url ) ) {

			// Set variables for storage, fix file filename for query strings.
			preg_match( '/[^\?]+\.(jpe?g|jpe|gif|png)\b/i', $image_url, $matches );
			if ( ! $matches ) {
				return new WP_Error( 'image_sideload_failed', __( 'Invalid image URL' ) );
			}

			$file_array = array();
			$file_array['name'] = $event->ID . '_image_'.basename( $matches[0] );
			
			if( has_post_thumbnail( $event_id ) ){
				$attachment_id = get_post_thumbnail_id( $event_id );
				$attach_filename = basename( get_attached_file( $attachment_id ) );
				if( $attach_filename == $file_array['name'] ){
					return false;
				}
			}

			// Download file to temp location.
			$file_array['tmp_name'] = download_url( $image_url );

			// If error storing temporarily, return the error.
			if ( is_wp_error( $file_array['tmp_name'] ) ) {
				return $file_array['tmp_name'];
			}

			// Do the validation and storage stuff.
			$att_id = media_handle_sideload( $file_array, $event_id, $event_title );

			// If error storing permanently, unlink.
			if ( is_wp_error( $att_id ) ) {
				@unlink( $file_array['tmp_name'] );
				return $att_id;
			}

			if ($att_id) {
				set_post_thumbnail($event_id, $att_id);
			}

			return $att_id;
		}
	}

	/**
	 * Display Ticket Section after eventbrite events.
	 *
	 * @since 1.0.0
	 */
	public function iee_add_tec_ticket_section() {
		global $iee_events;
		$event_id = get_the_ID();
		$xt_post_type = get_post_type();
		$event_origin = get_post_meta( $event_id, 'iee_event_origin', true );
		$eventbrite_event_id = get_post_meta( $event_id, 'iee_eventbrite_event_id', true );
		if ( $event_id > 0 ) {
			if ( $event_origin == 'eventbrite' ) {
				if( $iee_events->tec->get_event_posttype() == $xt_post_type ){
					$eventbrite_id = get_post_meta( $event_id, 'iee_event_id', true );
					if ( $eventbrite_id && $eventbrite_id > 0 && is_numeric( $eventbrite_id ) ) {
						$ticket_section = $this->iee_get_ticket_section( $eventbrite_id );
						echo $ticket_section;
					}
				}
			}elseif( $eventbrite_event_id && $eventbrite_event_id > 0 && is_numeric( $eventbrite_event_id ) ){
				$ticket_section = $this->iee_get_ticket_section( $eventbrite_event_id );
				echo $ticket_section;
			}
		}
	}

	/**
	 * Display Ticket Section after eventbrite events.
	 *
	 * @since 1.0.0
	 */
	public function iee_add_my_calendar_ticket_section( $details = '', $event = array(), $type = '', $time = '' ) {
		global $iee_events;
		$ticket_section = '';
		if( $type == 'single' ){
			$event_id = $event->event_post;
			$xt_post_type = get_post_type( $event_id );
			$event_origin = get_post_meta( $event_id, 'iee_event_origin', true );
			if ( $event_id > 0 && $event_origin == 'eventbrite' ) {
				if( $iee_events->my_calendar->get_event_posttype() == $xt_post_type ){
					$eventbrite_id = get_post_meta( $event_id, 'iee_event_id', true );
					if ( $eventbrite_id && $eventbrite_id > 0 && is_numeric( $eventbrite_id ) ) {
						$ticket_section = $this->iee_get_ticket_section( $eventbrite_id );
					}
				}
			}	
		}
		return $details . $ticket_section;
	}

	

	/**
	 * Add ticket section to Eventbrite event.
	 *
	 * @since    1.0.0
	 */
	public function iee_add_em_add_ticket_section( $content = '' ) {
		global $iee_events;
		$xt_post_type =  get_post_type();
		$event_id = get_the_ID();
		$event_origin = get_post_meta( $event_id, 'iee_event_origin', true );
		if ( $event_id > 0 && $event_origin == 'eventbrite' ) {
			if( ( $iee_events->em->get_event_posttype()  == $xt_post_type ) || ( $iee_events->aioec->get_event_posttype()  == $xt_post_type ) || ( $iee_events->iee->get_event_posttype()  == $xt_post_type ) || ( $iee_events->eventon->get_event_posttype()  == $xt_post_type ) ){
				$eventbrite_id = get_post_meta( $event_id, 'iee_event_id', true );
				if ( $eventbrite_id && $eventbrite_id > 0 && is_numeric( $eventbrite_id ) ) {
					$ticket_section = $this->iee_get_ticket_section( $eventbrite_id );
					return $content.$ticket_section;
				}
			}
		}
		return $content;
	}

	/**
	 * Get Ticket Sectoin for eventbrite events.
	 *
	 * @since  1.1.0
	 * @return html
	 */
	public function iee_get_ticket_section( $eventbrite_id = 0 ) {
		$options = iee_get_import_options( 'eventbrite' );
		
		$enable_ticket_sec = isset( $options['enable_ticket_sec'] ) ? $options['enable_ticket_sec'] : 'no';
		if ( 'yes' != $enable_ticket_sec ) {
			return '';
		}

		if( $eventbrite_id > 0 ){
			ob_start();
			?>
			<div class="eventbrite-ticket-section" style="width:100%; text-align:left;">
				<iframe id="eventbrite-tickets-<?php echo $eventbrite_id; ?>" src="//www.eventbrite.com/tickets-external?eid=<?php echo $eventbrite_id; ?>" style="width:100%;height:300px; border: 0px;"></iframe>
			</div>
			<?php
			$ticket = ob_get_clean();
			return $ticket;
		}else{
			return '';
		}

	}

	/**
	 * Format events arguments as per TEC
	 *
	 * @since    1.0.0
	 * @param array $eventbrite_event Eventbrite event.
	 * @return array
	 */
	public function display_import_success_message( $import_data = array(),$import_args = array(), $schedule_post = '' ) {
		global $iee_success_msg, $iee_errors;
		if( empty( $import_data ) || !empty( $iee_errors ) ){
			return;
		}

		$import_status = $import_ids = array();
		foreach ($import_data as $key => $value) {
			if( $value['status'] == 'created'){
				$import_status['created'][] = $value;
			}elseif( $value['status'] == 'updated'){
				$import_status['updated'][] = $value;
			}elseif( $value['status'] == 'skipped'){
				$import_status['skipped'][] = $value;
			}else{

			}
			if( isset( $value['id'] ) ){
				$import_ids[] = $value['id'];
			}
		}
		$created = $updated = $skipped = 0;
		$created = isset( $import_status['created'] ) ? count( $import_status['created'] ) : 0;
		$updated = isset( $import_status['updated'] ) ? count( $import_status['updated'] ) : 0;
		$skipped = isset( $import_status['skipped'] ) ? count( $import_status['skipped'] ) : 0;
		
		$success_message = esc_html__( 'Event(s) are imported successfully.', 'import-eventbrite-events' )."<br>";
		if( $created > 0 ){
			$success_message .= "<strong>".sprintf( __( '%d Created', 'import-eventbrite-events' ), $created )."</strong><br>";
		}
		if( $updated > 0 ){
			$success_message .= "<strong>".sprintf( __( '%d Updated', 'import-eventbrite-events' ), $updated )."</strong><br>";
		}
		if( $skipped > 0 ){
			$success_message .= "<strong>".sprintf( __( '%d Skipped (Already exists)', 'import-eventbrite-events' ), $skipped ) ."</strong><br>";
		}
		$iee_success_msg[] = $success_message;

		if( $schedule_post != '' && $schedule_post > 0 ){
			$temp_title = get_the_title( $schedule_post );
		}else{
			$temp_title = 'Manual Import';
		}
		
		if( $created > 0 || $updated > 0 || $skipped >0 ){
			$insert_args = array(
				'post_type'   => 'iee_import_history',
				'post_status' => 'publish',
				'post_title'  => $temp_title . " - ".ucfirst( $import_args["import_origin"]),
			);
			
			$insert = wp_insert_post( $insert_args, true );
			if ( !is_wp_error( $insert ) ) {
				update_post_meta( $insert, 'import_origin', $import_args["import_origin"] );
				update_post_meta( $insert, 'created', $created );
				update_post_meta( $insert, 'updated', $updated );
				update_post_meta( $insert, 'skipped', $skipped );
				update_post_meta( $insert, 'import_data', $import_args );
				if( $schedule_post != '' && $schedule_post > 0 ){
					update_post_meta( $insert, 'schedule_import_id', $schedule_post );
				}
			}	
		}				
	}

	/**
	 * Get Import events into selected destination.
	 *
	 * @since  1.0.0
	 * @return array
	 */
	public function import_events_into( $centralize_array, $event_args ){
		global $iee_events;
		$import_result = array();
		$event_import_into = isset( $event_args['import_into'] ) ?  $event_args['import_into'] : 'tec';
		switch ( $event_import_into ) {
			case 'iee':
				$import_result = $iee_events->iee->import_event( $centralize_array, $event_args );
				break;

			case 'tec':
				$import_result = $iee_events->tec->import_event( $centralize_array, $event_args );
				break;

			case 'em':
				$import_result = $iee_events->em->import_event( $centralize_array, $event_args );
				break;

			case 'eventon':
				$import_result = $iee_events->eventon->import_event( $centralize_array, $event_args );
				break;
				
			case 'event_organizer':
				$import_result = $iee_events->event_organizer->import_event( $centralize_array, $event_args );
				break;

			case 'aioec':
				$import_result = $iee_events->aioec->import_event( $centralize_array, $event_args );
				break;

			case 'my_calendar':
				$import_result = $iee_events->my_calendar->import_event( $centralize_array, $event_args );
				break;
				
			default:
				break;
		}
		return $import_result;
	}

	/**
	 * Render import Frequency
	 *
	 * @since   1.0.0
	 * @return  void
	 */
	function render_import_frequency(){
		?>
		<select name="import_frequency" class="import_frequency" disabled="disabled" >
	        <option value='hourly'>
	            <?php esc_html_e( 'Once Hourly','import-eventbrite-events' ); ?>
	        </option>
	        <option value='twicedaily'>
	            <?php esc_html_e( 'Twice Daily','import-eventbrite-events' ); ?>
	        </option>
	        <option value="daily" selected="selected">
	            <?php esc_html_e( 'Once Daily','import-eventbrite-events' ); ?>
	        </option>
	        <option value="weekly" >
	            <?php esc_html_e( 'Once Weekly','import-eventbrite-events' ); ?>
	        </option>
	        <option value="monthly">
	            <?php esc_html_e( 'Once a Month','import-eventbrite-events' ); ?>
	        </option>
	    </select>
		<?php
	}

	/**
	 * Render import type, one time or scheduled
	 *
	 * @since   1.0.0
	 * @return  void
	 */
	function render_import_type(){
		?>
		<select name="import_type" id="import_type" disabled="disabled">
	    	<option value="onetime" ><?php esc_attr_e( 'One-time Import','import-eventbrite-events' ); ?></option>
	    	<option value="scheduled" selected="selected" ><?php esc_attr_e( 'Scheduled Import','import-eventbrite-events' ); ?></option>
	    </select>
	    <span class="hide_frequency">
	    	<?php $this->render_import_frequency(); ?>
	    </span>
	    <?php do_action( 'iee_render_pro_notice' ); ?>
	    <?php
	}

	/**
	 * Clean URL.
	 *
	 * @since 1.0.0
	 */
	function clean_url( $url ) {
		
		$url = str_replace( '&amp;#038;', '&', $url );
		$url = str_replace( '&#038;', '&', $url );
		return $url;
		
	}

	/**
	 * Get UTC offset
	 *
	 * @since    1.0.0
	 */
	function get_utc_offset( $datetime ) {
		try {
			$datetime = new DateTime( $datetime );
		} catch ( Exception $e ) {
			return '';
		}

		$timezone = $datetime->getTimezone();
		$offset   = $timezone->getOffset( $datetime ) / 60 / 60;

		if ( $offset >= 0 ) {
			$offset = '+' . $offset;
		}

		return 'UTC' . $offset;
	}

	/**
	 * Render dropdown for Imported event status.
	 *
	 * @since 1.0
	 * @return void
	 */
	function render_eventstatus_input(){
		?>
		<tr class="event_status_wrapper">
			<th scope="row">
				<?php esc_attr_e( 'Status','import-eventbrite-events' ); ?> :
			</th>
			<td>
				<select name="event_status" >
	                <option value="publish">
	                    <?php esc_html_e( 'Published','import-eventbrite-events' ); ?>
	                </option>
	                <option value="pending">
	                    <?php esc_html_e( 'Pending','import-eventbrite-events' ); ?>
	                </option>
	                <option value="draft">
	                    <?php esc_html_e( 'Draft','import-eventbrite-events' ); ?>
	                </option>
	            </select>
			</td>
		</tr>
		<?php
	}

	/**
	 * remove query string from URL.
	 *
	 * @since 1.0.0
	 */
	function convert_datetime_to_db_datetime( $datetime ) {
		try {
			$datetime = new DateTime( $datetime );
			return $datetime->format( 'Y-m-d H:i:s' );
		}
		catch ( Exception $e ) {
			return $datetime;
		}
	}

	/**
	 * Check for Existing Event
	 *
	 * @since    1.0.0
	 * @param int $event_id event id.
	 * @return /boolean
	 */
	public function get_event_by_event_id( $post_type, $event_id ) {
		$event_args = array(
			'post_type' => $post_type,
			'post_status' => array( 'pending', 'draft', 'publish' ),
			'posts_per_page' => -1,
			'suppress_filters' => true,
			/*'meta_key'   => 'iee_event_id',
			'meta_value' => $event_id,*/
			'meta_query' => array(
				'relation' => 'OR',
				array(
					'key'     => 'iee_event_id',
					'value'   => $event_id,
					'compare' => '=',
				),
				array(
					'key'     => '_xt_eventbrite_event_id',
					'value'   => $event_id,
					'compare' => '=',
				),
				array(
					'key'     => 'xtei_eventbrite_event_id',
					'value'   => $event_id,
					'compare' => '=',
				),
			),
		);
		if( $post_type == 'tribe_events' && class_exists( 'Tribe__Events__Query' ) ){
			remove_action( 'pre_get_posts', array( 'Tribe__Events__Query', 'pre_get_posts' ), 50 );	
		}		
		$events = new WP_Query( $event_args );
		if( $post_type == 'tribe_events' && class_exists( 'Tribe__Events__Query' ) ){
			add_action( 'pre_get_posts', array( 'Tribe__Events__Query', 'pre_get_posts' ), 50 );
		}		
		if ( $events->have_posts() ) {
			while ( $events->have_posts() ) {
				$events->the_post();
				return get_the_ID();
			}
		}
		wp_reset_postdata();
		return false;
	}

	/**
	 * Display upgrade to pro notice in form.
	 *
	 * @since 1.0.0
	 */
	public function render_pro_notice(){
		?>
		<span class="iee_small">
	        <?php printf( '<span style="color: red">%s</span> <a href="' . IEE_PLUGIN_BUY_NOW_URL. '" target="_blank" >%s</a>', __( 'Available in Pro version.', 'import-eventbrite-events' ), __( 'Upgrade to PRO', 'import-eventbrite-events' ) ); ?>
	    </span>
		<?php
	}

	/**
	 * Get Active supported active plugins.
	 *
	 * @since  1.0.0
	 * @return array
	 */
	public function iee_get_country_code( $country ) {
		if( $country == '' ){
			return '';
		}
		
		$countries = array(
		    'AF'=>'AFGHANISTAN',
		    'AL'=>'ALBANIA',
		    'DZ'=>'ALGERIA',
		    'AS'=>'AMERICAN SAMOA',
		    'AD'=>'ANDORRA',
		    'AO'=>'ANGOLA',
		    'AI'=>'ANGUILLA',
		    'AQ'=>'ANTARCTICA',
		    'AG'=>'ANTIGUA AND BARBUDA',
		    'AR'=>'ARGENTINA',
		    'AM'=>'ARMENIA',
		    'AW'=>'ARUBA',
		    'AU'=>'AUSTRALIA',
		    'AT'=>'AUSTRIA',
		    'AZ'=>'AZERBAIJAN',
		    'BS'=>'BAHAMAS',
		    'BH'=>'BAHRAIN',
		    'BD'=>'BANGLADESH',
		    'BB'=>'BARBADOS',
		    'BY'=>'BELARUS',
		    'BE'=>'BELGIUM',
		    'BZ'=>'BELIZE',
		    'BJ'=>'BENIN',
		    'BM'=>'BERMUDA',
		    'BT'=>'BHUTAN',
		    'BO'=>'BOLIVIA',
		    'BA'=>'BOSNIA AND HERZEGOVINA',
		    'BW'=>'BOTSWANA',
		    'BV'=>'BOUVET ISLAND',
		    'BR'=>'BRAZIL',
		    'IO'=>'BRITISH INDIAN OCEAN TERRITORY',
		    'BN'=>'BRUNEI DARUSSALAM',
		    'BG'=>'BULGARIA',
		    'BF'=>'BURKINA FASO',
		    'BI'=>'BURUNDI',
		    'KH'=>'CAMBODIA',
		    'CM'=>'CAMEROON',
		    'CA'=>'CANADA',
		    'CV'=>'CAPE VERDE',
		    'KY'=>'CAYMAN ISLANDS',
		    'CF'=>'CENTRAL AFRICAN REPUBLIC',
		    'TD'=>'CHAD',
		    'CL'=>'CHILE',
		    'CN'=>'CHINA',
		    'CX'=>'CHRISTMAS ISLAND',
		    'CC'=>'COCOS (KEELING) ISLANDS',
		    'CO'=>'COLOMBIA',
		    'KM'=>'COMOROS',
		    'CG'=>'CONGO',
		    'CD'=>'CONGO, THE DEMOCRATIC REPUBLIC OF THE',
		    'CK'=>'COOK ISLANDS',
		    'CR'=>'COSTA RICA',
		    'CI'=>'COTE D IVOIRE',
		    'HR'=>'CROATIA',
		    'CU'=>'CUBA',
		    'CY'=>'CYPRUS',
		    'CZ'=>'CZECH REPUBLIC',
		    'DK'=>'DENMARK',
		    'DJ'=>'DJIBOUTI',
		    'DM'=>'DOMINICA',
		    'DO'=>'DOMINICAN REPUBLIC',
		    'TP'=>'EAST TIMOR',
		    'EC'=>'ECUADOR',
		    'EG'=>'EGYPT',
		    'SV'=>'EL SALVADOR',
		    'GQ'=>'EQUATORIAL GUINEA',
		    'ER'=>'ERITREA',
		    'EE'=>'ESTONIA',
		    'ET'=>'ETHIOPIA',
		    'FK'=>'FALKLAND ISLANDS (MALVINAS)',
		    'FO'=>'FAROE ISLANDS',
		    'FJ'=>'FIJI',
		    'FI'=>'FINLAND',
		    'FR'=>'FRANCE',
		    'GF'=>'FRENCH GUIANA',
		    'PF'=>'FRENCH POLYNESIA',
		    'TF'=>'FRENCH SOUTHERN TERRITORIES',
		    'GA'=>'GABON',
		    'GM'=>'GAMBIA',
		    'GE'=>'GEORGIA',
		    'DE'=>'GERMANY',
		    'GH'=>'GHANA',
		    'GI'=>'GIBRALTAR',
		    'GR'=>'GREECE',
		    'GL'=>'GREENLAND',
		    'GD'=>'GRENADA',
		    'GP'=>'GUADELOUPE',
		    'GU'=>'GUAM',
		    'GT'=>'GUATEMALA',
		    'GN'=>'GUINEA',
		    'GW'=>'GUINEA-BISSAU',
		    'GY'=>'GUYANA',
		    'HT'=>'HAITI',
		    'HM'=>'HEARD ISLAND AND MCDONALD ISLANDS',
		    'VA'=>'HOLY SEE (VATICAN CITY STATE)',
		    'HN'=>'HONDURAS',
		    'HK'=>'HONG KONG',
		    'HU'=>'HUNGARY',
		    'IS'=>'ICELAND',
		    'IN'=>'INDIA',
		    'ID'=>'INDONESIA',
		    'IR'=>'IRAN, ISLAMIC REPUBLIC OF',
		    'IQ'=>'IRAQ',
		    'IE'=>'IRELAND',
		    'IL'=>'ISRAEL',
		    'IT'=>'ITALY',
		    'JM'=>'JAMAICA',
		    'JP'=>'JAPAN',
		    'JO'=>'JORDAN',
		    'KZ'=>'KAZAKSTAN',
		    'KE'=>'KENYA',
		    'KI'=>'KIRIBATI',
		    'KP'=>'KOREA DEMOCRATIC PEOPLES REPUBLIC OF',
		    'KR'=>'KOREA REPUBLIC OF',
		    'KW'=>'KUWAIT',
		    'KG'=>'KYRGYZSTAN',
		    'LA'=>'LAO PEOPLES DEMOCRATIC REPUBLIC',
		    'LV'=>'LATVIA',
		    'LB'=>'LEBANON',
		    'LS'=>'LESOTHO',
		    'LR'=>'LIBERIA',
		    'LY'=>'LIBYAN ARAB JAMAHIRIYA',
		    'LI'=>'LIECHTENSTEIN',
		    'LT'=>'LITHUANIA',
		    'LU'=>'LUXEMBOURG',
		    'MO'=>'MACAU',
		    'MK'=>'MACEDONIA, THE FORMER YUGOSLAV REPUBLIC OF',
		    'MG'=>'MADAGASCAR',
		    'MW'=>'MALAWI',
		    'MY'=>'MALAYSIA',
		    'MV'=>'MALDIVES',
		    'ML'=>'MALI',
		    'MT'=>'MALTA',
		    'MH'=>'MARSHALL ISLANDS',
		    'MQ'=>'MARTINIQUE',
		    'MR'=>'MAURITANIA',
		    'MU'=>'MAURITIUS',
		    'YT'=>'MAYOTTE',
		    'MX'=>'MEXICO',
		    'FM'=>'MICRONESIA, FEDERATED STATES OF',
		    'MD'=>'MOLDOVA, REPUBLIC OF',
		    'MC'=>'MONACO',
		    'MN'=>'MONGOLIA',
		    'MS'=>'MONTSERRAT',
		    'MA'=>'MOROCCO',
		    'MZ'=>'MOZAMBIQUE',
		    'MM'=>'MYANMAR',
		    'NA'=>'NAMIBIA',
		    'NR'=>'NAURU',
		    'NP'=>'NEPAL',
		    'NL'=>'NETHERLANDS',
		    'AN'=>'NETHERLANDS ANTILLES',
		    'NC'=>'NEW CALEDONIA',
		    'NZ'=>'NEW ZEALAND',
		    'NI'=>'NICARAGUA',
		    'NE'=>'NIGER',
		    'NG'=>'NIGERIA',
		    'NU'=>'NIUE',
		    'NF'=>'NORFOLK ISLAND',
		    'MP'=>'NORTHERN MARIANA ISLANDS',
		    'NO'=>'NORWAY',
		    'OM'=>'OMAN',
		    'PK'=>'PAKISTAN',
		    'PW'=>'PALAU',
		    'PS'=>'PALESTINIAN TERRITORY, OCCUPIED',
		    'PA'=>'PANAMA',
		    'PG'=>'PAPUA NEW GUINEA',
		    'PY'=>'PARAGUAY',
		    'PE'=>'PERU',
		    'PH'=>'PHILIPPINES',
		    'PN'=>'PITCAIRN',
		    'PL'=>'POLAND',
		    'PT'=>'PORTUGAL',
		    'PR'=>'PUERTO RICO',
		    'QA'=>'QATAR',
		    'RE'=>'REUNION',
		    'RO'=>'ROMANIA',
		    'RU'=>'RUSSIAN FEDERATION',
		    'RW'=>'RWANDA',
		    'SH'=>'SAINT HELENA',
		    'KN'=>'SAINT KITTS AND NEVIS',
		    'LC'=>'SAINT LUCIA',
		    'PM'=>'SAINT PIERRE AND MIQUELON',
		    'VC'=>'SAINT VINCENT AND THE GRENADINES',
		    'WS'=>'SAMOA',
		    'SM'=>'SAN MARINO',
		    'ST'=>'SAO TOME AND PRINCIPE',
		    'SA'=>'SAUDI ARABIA',
		    'SN'=>'SENEGAL',
		    'SC'=>'SEYCHELLES',
		    'SL'=>'SIERRA LEONE',
		    'SG'=>'SINGAPORE',
		    'SK'=>'SLOVAKIA',
		    'SI'=>'SLOVENIA',
		    'SB'=>'SOLOMON ISLANDS',
		    'SO'=>'SOMALIA',
		    'ZA'=>'SOUTH AFRICA',
		    'GS'=>'SOUTH GEORGIA AND THE SOUTH SANDWICH ISLANDS',
		    'ES'=>'SPAIN',
		    'LK'=>'SRI LANKA',
		    'SD'=>'SUDAN',
		    'SR'=>'SURINAME',
		    'SJ'=>'SVALBARD AND JAN MAYEN',
		    'SZ'=>'SWAZILAND',
		    'SE'=>'SWEDEN',
		    'CH'=>'SWITZERLAND',
		    'SY'=>'SYRIAN ARAB REPUBLIC',
		    'TW'=>'TAIWAN, PROVINCE OF CHINA',
		    'TJ'=>'TAJIKISTAN',
		    'TZ'=>'TANZANIA, UNITED REPUBLIC OF',
		    'TH'=>'THAILAND',
		    'TG'=>'TOGO',
		    'TK'=>'TOKELAU',
		    'TO'=>'TONGA',
		    'TT'=>'TRINIDAD AND TOBAGO',
		    'TN'=>'TUNISIA',
		    'TR'=>'TURKEY',
		    'TM'=>'TURKMENISTAN',
		    'TC'=>'TURKS AND CAICOS ISLANDS',
		    'TV'=>'TUVALU',
		    'UG'=>'UGANDA',
		    'UA'=>'UKRAINE',
		    'AE'=>'UNITED ARAB EMIRATES',
		    'GB'=>'UNITED KINGDOM',
		    'US'=>'UNITED STATES',
		    'UM'=>'UNITED STATES MINOR OUTLYING ISLANDS',
		    'UY'=>'URUGUAY',
		    'UZ'=>'UZBEKISTAN',
		    'VU'=>'VANUATU',
		    'VE'=>'VENEZUELA',
		    'VN'=>'VIET NAM',
		    'VG'=>'VIRGIN ISLANDS, BRITISH',
		    'VI'=>'VIRGIN ISLANDS, U.S.',
		    'WF'=>'WALLIS AND FUTUNA',
		    'EH'=>'WESTERN SAHARA',
		    'YE'=>'YEMEN',
		    'YU'=>'YUGOSLAVIA',
		    'ZM'=>'ZAMBIA',
		    'ZW'=>'ZIMBABWE',
		  );

		foreach ($countries as $code => $name ) {
			if( strtoupper( $country) == $name ){
				return $code;
			}
		}
		return $country;
	}

}
