<?php
/*
Plugin Name: WPUF Contact Form
Plugin URI: https://wordpress.org/plugins/wpuf-contact-form/
Description: Create, edit, delete, manages contact forms for users
Author: Mithu A Quayium
Version: 0.1
Author URI: http://cybercraftit.com
License: GPL2
TextDomain: wcf
*/

define( 'WCF_ASSETS', plugins_url( '', __FILE__ ) . '/assets' );

class wpuf_contact_form{

    public $wpuf_path;

    function __construct(){
        $this->include_files();
    }



    function include_files(){
        if( is_admin() ){
            require_once dirname( __FILE__ ) . '/contact-form-elements.php';
        }
        require_once dirname( __FILE__ ) . '/frontend-form.php';
        require_once dirname( __FILE__ ) . '/contact-form.php';
    }

    function admin_contact_form_menu(){
        $capability = wpuf_admin_role();

        add_submenu_page( 'wpuf-admin-opt', __( 'Contact Form', 'wcf' ), __( 'Contact Form', 'wcf' ), $capability, 'wpuf_contact_form', array($this, 'admin_coupon_page' ) );
    }



}

new wpuf_contact_form();