<?php
/**
 * Display the meta boxes (like the input for price) on the premium posts
 *Called by EventsChooserBox in RMSPC__Main
 */
class RMSPC__Admin__Event_Meta_Box {

	/**
	 * @var WP_Post
	 *The $event (the post) is passed into this class when it is called
	 */
	protected $event;

	/**
	 * @var Tribe__Events__Main
	 */
	protected $tribe;

	/**
	 * Sets up and renders the event meta box for the specified existing event
	 * or for a new event (if $event === null).
	 *
	 * @param null $event
	 */
	public function __construct( $event = null ) {
		$this->tribe = RMSPC__Main::instance();
		$this->get_event( $event );
		$this->do_meta_box();
	}




	/**
	 * Work with the specifed event object or else use a placeholder if we are in
	 * the middle of creating a new event.
	 *
	 * @param null $event
	 */
	protected function get_event( $event = null ) {
		global $post;

		if ( $event === null ) {
			$this->event = $post;
		} elseif ( $event instanceof WP_Post ) {
			$this->event = $event;
		} else {
			$this->event = new WP_Post( (object) array( 'ID' => 0 ) );
		}
	}

	/**
	 * Pull the expected variables into scope and load the meta box template.
	 */
	protected function do_meta_box() {
		$events_meta_box_template = $this->tribe->pluginPath . 'src/admin-views/events-meta-box.php';
		$events_meta_box_template = apply_filters( 'tribe_events_meta_box_template', $events_meta_box_template );

		// get_event() assigns the $post object to $event
		$event = $this->event;
		$tribe = $this->tribe;

		include( $events_meta_box_template );
	}
}