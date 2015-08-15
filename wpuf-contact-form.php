<?php
/*
Plugin Name: WPUF Contact Form
Plugin URI: https://wordpress.org/plugins/wpuf-contact-form/
Description: Create, edit, delete, manages contact forms for users
Author: Mithu A Quayium
Version: 0.1
License: GPL2
TextDomain: wcf
*/

define( 'WCF_ASSETS', plugins_url( '', __FILE__ ) . '/assets' );

class wpuf_contact_form{

    public $wpuf_path;

    function __construct(){
        $this->include_files();
        add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array( $this, 'wcf_plugin_action_links' ) );
    }

    function wcf_plugin_action_links( $links ){
        $links[] = '<a href="'. get_admin_url(null, 'edit.php?post_type=wcf_contact_form') .'">Go to Contact Form Page</a>';
        return $links;
    }


    function include_files(){
        if( is_admin() ){
            require_once dirname( __FILE__ ) . '/contact-form-elements.php';
        }
        require_once dirname( __FILE__ ) . '/frontend-form.php';
        require_once dirname( __FILE__ ) . '/contact-form.php';
    }



}

new wpuf_contact_form();