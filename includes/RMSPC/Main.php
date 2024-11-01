<?php





$RMSPC__Main = new RMSPC__Main();

class RMSPC__Main {

    const POSTTYPE            = 'simple_popup_content';

    protected static $instance;

    public $plugin_path;

    private static $cache = array();

    private static $pluginBasename;

    private static $pluginUrl;

    public function __construct()
    {
        self::$pluginBasename = dirname( plugin_basename( __FILE__ ) );
        self::$pluginUrl = plugin_dir_url(__FILE__);

        $this->pluginPath = $this->plugin_path = trailingslashit( dirname( dirname( dirname( __FILE__ ) ) ) );
        $this->pluginDir  = $this->plugin_dir = trailingslashit( basename( $this->plugin_path ) );
        //$this->pluginUrl  = $this->plugin_url = plugins_url( $this->plugin_dir );

        //$this->maybe_set_common_lib_info();

        // let's initialize tec silly-early to avoid fatals with upgrades from 3.x to 4.x
        add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ), 0 );
    }


    /**
     * Static Singleton Factory Method
     *
     * @return RMSPC__Events__Main
     */
    public static function instance() {
        if ( ! isset( self::$instance ) ) {
            $className      = __CLASS__;
            self::$instance = new $className;
        }

        return self::$instance;
    }



    public function plugins_loaded() {
        // include the autoloader class
        $this->init_autoloading();

       //add_action( 'init', array( $this, 'loadTextDomain' ), 1 );

        $this->addHooks();
        // $this->maybe_load_tickets_framework();
        $this->loadLibraries();

    }

    /**
     * Setup the autoloader for common files
     */
    protected function init_autoloading() {
        if ( ! class_exists( 'RMSPC__Autoloader' ) ) {
            require_once dirname( __FILE__ ) . '/Autoloader.php';
        }

        $prefixes = array( 'RMSPC__' => dirname( __FILE__ ) );
        $autoloader = RMSPC__Autoloader::instance();
        $autoloader->register_prefixes( $prefixes );
        $autoloader->register_autoloader();
    }

    /**
     * Add filters and actions
     */
    protected function addHooks() {
        add_action( 'init', array( $this, 'init' ), 10 );
        add_action( 'plugins_loaded', array( 'RMSPC__Templates', 'init' ) );
        add_action( 'admin_menu', array( $this, 'addEventBox' ) );
        add_action( 'save_post', array( $this, 'addEventMeta' ), 15, 2 );
        
    }


    /**
     * Load all the required library files.
     */
    protected function loadLibraries() {


        // Load Template Tags
        require_once $this->plugin_path . 'src/functions/template-tags/query.php';
        require_once $this->plugin_path . 'src/functions/template-tags/general.php';
    }


    /**
     * Run on applied action init
     */
    public function init() {
        $this->registerPostType();
        // check if is query for premium post
        RMSPC__Query::init();
    }

    /**
     * Register the post types.
     */
    public function registerPostType() {
        $options =  get_option('rmspc_options');
        $Item_Name = $options['Item_Name'];

        if (!$Item_Name) {
            $Item_Name = __('Simple Popup', 'simple-popup-content');
            $slug = 'simple-popup-content';
        } else {
            $slug = str_replace(' ', '-', strtolower($Item_Name));
        }

        $SPC_args  =  array(
            'public'  => true,
            'show_in_nav_menus'  => false,
            'query_var'  => 'simple_popup_content',
            'rewrite'  => array(
                'slug'  => $slug,
                'with_front'  => false
            ),
            'has_archive' => true,
            'supports'  => array(
                'title',
                'editor',
                'thumbnail'
            ),
            //'register_meta_box_cb'  => array($this->addEventBox),
            'labels'  => array(
                'name'  => esc_html__('Simple Popups', 'simple-popup-content'),
                'all_items' => sprintf(esc_html__('All %ss', 'simple-popup-content'), $Item_Name),
                'singular_name'  => $Item_Name,
                'add_new'  => sprintf(esc_html__('Add New %s', 'simple-popup-content'), $Item_Name),
                'add_new_item'  => sprintf(esc_html__('Add New %s', 'simple-popup-content'), $Item_Name),
                'edit_item'  => sprintf(esc_html__('Edit %s', 'simple-popup-content'), $Item_Name),
                'new_item'  => sprintf(esc_html__('New %s', 'simple-popup-content'), $Item_Name),
                'view_item'  => sprintf(esc_html__('View %s', 'simple-popup-content'), $Item_Name),
                'search_items'  => sprintf(esc_html__('Search %ss', 'simple-popup-content'), $Item_Name),
                'not_found'  => sprintf(esc_html__('No %s Found', 'simple-popup-content'), $Item_Name),
                'not_found_in_trash'  => sprintf(esc_html__('No %ss Found In Trash', 'simple-popup-content'), $Item_Name),
            ),
        );
        /* Register the FYN post type. */
        register_post_type( 'simple_popup_content', $SPC_args );
    }

    /**
     * Callback for adding the Meta box to the admin page
     *
     */
    public function addEventBox() {
        add_meta_box(
            'rmspc_shortcode_display', __('Instructions', 'wp-simple-popup-content'), array(
                $this,
                'EventsChooserBox',
            ), 'simple_popup_content', 'side', 'high'
        );

        add_meta_box(
            'rmspc_teaser_box',
            'Popup Location',
            array($this, 'TeaserMetaBox'),
            'simple_popup_content',
            'side', // change to something other then normal, advanced or side
            'high'
        );
    }


    /**
     * Generates the main events settings meta box used within the event editor to configure
     * event dates, times and more.
     *
     * @param WP_Post $event
     */
    public function EventsChooserBox( $event = null ) {
        new RMSPC__Admin__Event_Meta_Box( $event );
    }

    /**
     * Create a content editor for Popup Location
     *
     * @param WP_Post $event
     */
    public function TeaserMetaBox( $event = null ) {
        global $post;

        $rmspc_teaser = get_post_meta ($post->ID, '_rmspc_teaser', TRUE );

        $args = array(
            'selected' => $rmspc_teaser,
            'echo' => 1,
            'name' => "rmspc_teaser",
            'id' => 'rmspc_teaser', // string
            'show_option_none'      => 'No Page Selected', // string
        );
        wp_dropdown_pages( $args );
    }

    /**
     * Adds / removes the event details as meta tags to the post.
     *
     * @param int     $postId
     * @param WP_Post $post
     *
     */
    public function addEventMeta( $postId, $post ) {

        // only continue if it's an event post
        if ( $post->post_type !== self::POSTTYPE || defined( 'DOING_AJAX' ) ) {

            return;
        }
        // don't do anything on autosave or auto-draft either or massupdates
        if ( wp_is_post_autosave( $postId ) || $post->post_status == 'auto-draft' || isset( $_GET['bulk_edit'] ) || ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'inline-save' ) ) {

            return;

        }

        // don't do anything on other wp_insert_post calls
        if ( isset( $_POST['post_ID'] ) && $postId != $_POST['post_ID'] ) {

            return;
        }

        if ( ! isset( $_POST['ecp_nonce'] ) ) {
            return;
        }

        if ( ! wp_verify_nonce( $_POST['ecp_nonce'], self::POSTTYPE ) ) {
            return;
        }

        if( isset($_POST['rmspc_price']) ){
            update_post_meta( $postId, '_rmspc_price', $_POST['rmspc_price']);
        }
        if( isset($_POST['rmspc_teaser']) ){
            update_post_meta( $postId, '_rmspc_teaser', $_POST['rmspc_teaser']);
        }
    }

}