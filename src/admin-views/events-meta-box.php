<?php
/*
 *The $event variable is made available by RMSPC__Admin__Event_Meta_Box::do_meta_box()
 *Call stack
 *RMSPC__Main 
    -> plugins_loaded() -- hooked to plugins_loaded
      -> addHooks 
        -> addEventBox() -- hooked to admin_menu
          -> add_meta_box
            -> EventsChooserBox()
              -> RMSPC__Admin__Event_Meta_Box
                -> do_meta_box
                  -> events-meta-box.php
*/
$rmspc_price = get_post_meta ($event->ID, '_rmspc_price', TRUE );
wp_nonce_field( RMSPC__Main::POSTTYPE, 'ecp_nonce' );
?>

<table>
    <tr>
        <td>Choose which page you would like to display your popup on using the dropdown below.</td>
    </tr>
</table>