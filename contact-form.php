<?php

class wcf_contact_form_stuff extends WPUF_Admin_Form{

    function __construct(){

        add_action( 'init', array( $this, 'register_contact_form' ) );

        //add to admin wpuf menu
        add_action( 'wpuf_admin_menu_top', array($this, 'wcf_contact_form_menu') );

        add_action( 'add_meta_boxes_wcf_contact_form', array($this, 'add_meta_box_wcf_contact_form') );
        add_action( 'admin_enqueue_scripts', array($this, 'enqueue_scripts') );
        add_action( 'wp_enqueue_scripts', array($this, 'frontend_enqueue_scripts') );

        //ajax functionality
        add_action( 'wp_ajax_wcf_form_add_el', array( $this, 'wcf_ajax_post_add_element' ) );

        //save post
        add_action( 'save_post', array( $this, 'save_contact_fields' ), 1 , 3  );

        //hook to render saved fields
        add_action( 'wcf_edit_contact_form_area_profile', array( $this, 'wcf_edit_form_area_profile_runner' ) );

    }

    function register_contact_form(){

        $capability = wpuf_admin_role();

        register_post_type( 'wcf_contact_form', array(
            'label'           => __( 'Contact Forms', 'wcf' ),
            'public'          => false,
            'show_ui'         => true,
            'show_in_menu'    => false,
            'capability_type' => 'post',
            'hierarchical'    => false,
            'query_var'       => false,
            'supports'        => array('title'),
            'capabilities' => array(
                'publish_posts'       => $capability,
                'edit_posts'          => $capability,
                'edit_others_posts'   => $capability,
                'delete_posts'        => $capability,
                'delete_others_posts' => $capability,
                'read_private_posts'  => $capability,
                'edit_post'           => $capability,
                'delete_post'         => $capability,
                'read_post'           => $capability,
            ),
            'labels' => array(
                'name'               => __( 'Contact Forms', 'wcf' ),
                'singular_name'      => __( 'Contact Form', 'wcf' ),
                'menu_name'          => __( 'Contact Forms', 'wcf' ),
                'add_new'            => __( 'Add Contact Form', 'wcf' ),
                'add_new_item'       => __( 'Add New Contact Form', 'wcf' ),
                'edit'               => __( 'Edit', 'wcf' ),
                'edit_item'          => __( 'Edit Contact Form', 'wcf' ),
                'new_item'           => __( 'New Contact Form', 'wcf' ),
                'view'               => __( 'View Contact Form', 'wcf' ),
                'view_item'          => __( 'View Contact Form', 'wcf' ),
                'search_items'       => __( 'Search Contact Form', 'wcf' ),
                'not_found'          => __( 'No Contact Form Found', 'wcf' ),
                'not_found_in_trash' => __( 'No Contact Form Found in Trash', 'wcf' ),
                'parent'             => __( 'Parent Form', 'wpuf' ),
            ),
        ) );
    }

    function wcf_contact_form_menu(){
        $capability = wpuf_admin_role();
        add_submenu_page( 'wpuf-admin-opt', __( 'Contact Forms', 'wcf' ), __( 'Contact Forms', 'wcf' ), $capability, 'edit.php?post_type=wcf_contact_form' );
    }

    function add_meta_box_wcf_contact_form(){

        add_meta_box( 'wpuf-contact-form-metabox-editor', __( 'Form Editor', 'wcf' ), array($this, 'metabox_wcf_contact_form'), 'wcf_contact_form', 'normal', 'high' );
        add_meta_box( 'wpuf-contact-form-metabox-fields', __( 'Form Elements', 'wcf' ), array($this, 'form_elements_profile'), 'wcf_contact_form', 'side', 'core' );

        //add_meta_box( 'wpuf-metabox-fields-shortcode', __( 'Shortcode', 'wpuf' ), array($this, 'form_elements_shortcode'), 'wcf_contact_form', 'side', 'core' );
    }

    /**
     *
     * render editor metabox
     */
    function metabox_wcf_contact_form( $post ){
        ?>

        <h2 class="nav-tab-wrapper">
            <a href="#wpuf-metabox" class="nav-tab" id="wpuf_general-tab"><?php _e( 'Form Editor', 'wpuf' ); ?></a>
            <a href="#wpuf-metabox-settings" class="nav-tab" id="wpuf_dashboard-tab"><?php _e( 'Settings', 'wpuf' ); ?></a>

            <?php do_action( 'wpuf_wcf_form_tab' ); ?>
        </h2>

        <div class="tab-content">
            <div id="wpuf-metabox" class="group">
                <?php $this->edit_wcf_contact_form_area_profile(); ?>
            </div>

            <div id="wpuf-metabox-settings" class="group">
                <?php $this->form_settings_section(); ?>
            </div>

            <?php do_action( 'wpuf_wcf_form_tab_content' ); ?>
        </div>
    <?php
    }


    /**
     * wcf contact form area
     */
    function edit_wcf_contact_form_area_profile(){
        ?>
        <input type="hidden" name="wpuf_form_editor" id="wpuf_form_editor" value="<?php echo wp_create_nonce( plugin_basename( __FILE__ ) ); ?>" />
        <?php
        do_action( 'wcf_edit_contact_form_area_profile' );
    }

    /**
     * render fields
     */
    function wcf_edit_form_area_profile_runner(){
        global $post, $pagenow, $form_inputs;
        $form_inputs = wpuf_get_form_fields( $post->ID );
        ?>
        <div style="margin-bottom: 10px">
            <button class="button wpuf-collapse"><?php _e( 'Toggle All', 'wpuf' ); ?></button>
        </div>

        <div class="wpuf-updated">
            <p><?php _e( 'Click on a form element to add to the editor', 'wpuf' ); ?></p>
        </div>

        <ul id="wpuf-form-editor" class="wpuf-form-editor unstyled">

            <?php

            if ($form_inputs) {
                $count = 0;
                foreach ($form_inputs as $order => $input_field) {
                    $name = ucwords( str_replace( '_', ' ', $input_field['template'] ) );

                    if ( method_exists( 'wcf_elements', $input_field['template'] ) ) {
                        wcf_elements::$input_field['template']( $count, $name, $input_field );
                    } else {
                        do_action( 'wpuf_admin_template_post_' . $input_field['template'], $name, $count, $input_field, 'WPUF_Admin_Template_Post', '' );
                    }

                    $count++;
                }
            }
            ?>
        </ul>
    <?php
    }



    /**
     * Form elements
     */
    function form_elements_profile(){
        if( is_plugin_active( 'wp-user-frontend/wpuf.php' ) ):
        ?>
            Unusable/Unavailable fields will be usable after upgrading Wp User Frontend plugin to Pro !
        <?php
        endif;
        ?>
        <div class="wpuf-loading hide"></div>

        <h2><?php _e( 'Contact Fields', 'wpuf' ); ?></h2>
        <div class="wcf-form-buttons">
            <button class="button" data-name="post_title" data-type="text" title="<?php _e( 'Click to add to the editor', 'wpuf' ); ?>"><?php _e( 'Message Subject', 'wpuf' ); ?></button>
            <button class="button" data-name="wcf_user_login" data-type="text"><?php _e( 'Username', 'wpuf' ); ?></button>
            <button class="button" data-name="wcf_first_name" data-type="textarea"><?php _e( 'First Name', 'wpuf' ); ?></button>
            <button class="button" data-name="wcf_last_name" data-type="textarea"><?php _e( 'Last Name', 'wpuf' ); ?></button>
            <button class="button" data-name="wcf_nickname" data-type="text"><?php _e( 'Nickname', 'wpuf' ); ?></button>
            <button class="button" data-name="wcf_user_email" data-type="category"><?php _e( 'E-mail', 'wpuf' ); ?></button>
            <button class="button" data-name="wcf_user_url" data-type="text"><?php _e( 'Website', 'wpuf' ); ?></button>
            <button class="button" data-name="wcf_user_bio" data-type="textarea"><?php _e( 'Biographical Info', 'wpuf' ); ?></button>

            <?php do_action( 'wpuf_form_buttons_user' ); ?>
        </div>
        <?php
        $this->form_elements_common();
        $this->publish_button();
    }


    /**
     * Responsible to add form element in editor
     */
    function wcf_ajax_post_add_element(){

        $name = $_POST['name'];
        $type = $_POST['type'];
        $field_id = $_POST['order'];

        switch ($name) {
            case 'post_title':
                wcf_elements::post_title( $field_id, __( 'Username', 'wpuf' ) ); exit;
                break;

            case 'wcf_user_login':
                wcf_elements::user_login( $field_id, __( 'Username', 'wpuf' ) );
                break;

            case 'wcf_first_name':
                wcf_elements::first_name( $field_id, __( 'First Name', 'wpuf' ) );
                break;

            case 'wcf_last_name':
                wcf_elements::last_name( $field_id, __( 'Last Name', 'wpuf' ) );
                break;

            case 'wcf_nickname':
                wcf_elements::nickname( $field_id, __( 'Nickname', 'wpuf' ) );
                break;

            case 'wcf_user_email':
                wcf_elements::user_email( $field_id, __( 'E-mail', 'wpuf' ) );
                break;

            case 'wcf_user_url':
                wcf_elements::user_url( $field_id, __( 'Website', 'wpuf' ) );
                break;

            case 'wcf_user_bio':
                wcf_elements::description( $field_id, __( 'Biographical Info', 'wpuf' ) );
                break;

            case 'wcf_password':
                wcf_elements::password( $field_id, __( 'Password', 'wpuf' ) );
                break;

            case 'wcf_user_avatar':
                wcf_elements::avatar( $field_id, __( 'Avatar', 'wpuf' ) );
                break;

            default:
                do_action( 'wpuf_admin_field_' . $name, $type, $field_id, 'WPUF_Admin_Template_Post', $this );
                break;
        }
        parent::ajax_post_add_element();
    }



    /**
     * Enqueue scripts and styles for form builder
     *
     * @global string $pagenow
     * @return void
     */
    function enqueue_scripts() {
        global $pagenow, $post;


        if ( in_array( $pagenow, array( 'post.php', 'post-new.php') ) && get_post_type() == 'wcf_contact_form' ) {
            wp_enqueue_script( 'jquery-ui-autocomplete' );

            // scripts
            wp_enqueue_script( 'jquery-smallipop', WPUF_ASSET_URI . '/js/jquery.smallipop-0.4.0.min.js', array('jquery') );
            wp_enqueue_script( 'wpuf-formbuilder-script', WPUF_ASSET_URI . '/js/formbuilder.js', array('jquery', 'jquery-ui-sortable') );
            wp_enqueue_script( 'wpuf-conditional-script', WPUF_ASSET_URI . '/js/conditional.js' );
            wp_enqueue_script( 'wcf-admin-script', WCF_ASSETS . '/js/wcf_admin.js' );
            // styles
            wp_enqueue_style( 'jquery-smallipop', WPUF_ASSET_URI . '/css/jquery.smallipop.css' );
            wp_enqueue_style( 'wpuf-formbuilder', WPUF_ASSET_URI . '/css/formbuilder.css' );
            wp_enqueue_style( 'jquery-ui-core', WPUF_ASSET_URI . '/css/jquery-ui-1.9.1.custom.css' );
        }
    }

    function frontend_enqueue_scripts(){
        wp_enqueue_script( 'wcf-frontend-script', WCF_ASSETS . '/js/wcf_frontend.js' );
    }

    /**
     *
     * save form fields
     */
    function save_contact_fields( $post_id, $post, $update ){

        //do_action( 'wpuf_check_post_type', $post, $update );

        if ( $post->post_type != 'wcf_contact_form'  ) {
            return;
        }

        if ( !isset($_POST['wpuf_form_editor'] ) ) {
            return $post->ID;
        }

       // var_dump($_POST);die();
        // Is the user allowed to edit the post or page?
        if ( !current_user_can( 'edit_post', $post->ID ) ) {
            return $post->ID;
        }

        $conditions = isset( $_POST['wpuf_cond'] ) ? $_POST['wpuf_cond'] : array();

        if ( count( $conditions ) ) {
            foreach ($conditions as $key => $condition) {
                if ( $condition['condition_status'] == 'no' ) {
                    unset( $conditions[$key] );
                }
            }
        }

        $_POST['wpuf_input'] = isset( $_POST['wpuf_input'] ) ? $_POST['wpuf_input'] : array();

        foreach ( $_POST['wpuf_input'] as $key => $field_val ) {
            if ( array_key_exists( 'options', $field_val) ) {
                $view_option = array();

                foreach ( $field_val['options'] as $options_key => $options_value ) {
                    $opt_value = ( $field_val['options_values'][$options_key] == '' ) ? $options_value : $field_val['options_values'][$options_key];
                    $view_option[$opt_value] =   $options_value;//$_POST['wpuf_input'][$key]['options'][$opt_value] = $options_value;
                }

                unset($_POST['wpuf_input'][$key]['options_values']);
                $_POST['wpuf_input'][$key]['options'] = $view_option;
            }


            if ( $field_val['input_type'] == 'taxonomy' ) {
                $tax = get_terms( $field_val['name'],  array(
                    'orderby'    => 'count',
                    'hide_empty' => 0
                ) );

                $tax = is_array( $tax ) ? $tax : array();

                foreach($tax as $tax_obj) {
                    $terms[$tax_obj->term_id] = $tax_obj->name;
                }

                $_POST['wpuf_input'][$key]['options'] = $terms;
                $terms = '';
            }
        }

        $contents = self::get_form_fields( $post->ID );

        $db_id = wp_list_pluck( $contents, 'ID' );

        $order = 0;
        foreach( $_POST['wpuf_input'] as $key => $content ) {
            $content['wpuf_cond'] = $_POST['wpuf_cond'][$key];

            $field_id = isset( $content['id'] ) ? intval( $content['id'] ) : 0;

            if ( $field_id ) {
                $compare_id[$field_id] = $field_id;
                unset( $content['id'] );

                self::insert_form_field( $post->ID, $content, $field_id, $order );

            } else {
                self::insert_form_field( $post->ID, $content, null, $order );
            }

            $order++;
        }

        // delete fields from previous form
        $del_post_id = array_diff_key( $db_id, $compare_id );

        if ( $del_post_id ) {

            foreach ($del_post_id as $key => $post_id ) {
                wp_delete_post( $post_id , true );
            }

        } else if ( !count( $_POST['wpuf_input'] ) && count( $db_id ) ) {

            foreach ( $db_id as $key => $post_id ) {

                wp_delete_post( $post_id , true );
            }
        }

        update_post_meta( $post->ID, 'wpuf_form_settings', $_POST['wpuf_settings'] );
    }

    /**
     * Render form element
     */
    public static function render_registration_form() {

        global $post, $pagenow, $form_inputs;

        $form_inputs = wpuf_get_form_fields( $post->ID );

        self::get_pro_prompt();

        ?>
        <div style="margin-bottom: 10px">
            <button class="button wpuf-collapse"><?php _e( 'Toggle All', 'wpuf' ); ?></button>
        </div>

        <div class="wpuf-updated">
            <p><?php _e( 'Click on a form element to add to the editor', 'wpuf' ); ?></p>
        </div>

        <ul id="wpuf-form-editor" class="wpuf-form-editor unstyled">

            <?php

            if ($form_inputs) {
                $count = 0;
                foreach ($form_inputs as $order => $input_field) {
                    $name = ucwords( str_replace( '_', ' ', $input_field['template'] ) );

                    wcf_elements::$input_field['template']( $count, $name, $input_field );

                    $count++;
                }
            }
            ?>
        </ul>
    <?php

    }


    /**
     * Settings
     */
    function form_settings_section(){

        global $post;
        $form_settings = wpuf_get_form_settings( $post->ID );

        $your_mail = isset( $form_settings['your_mail'] ) ? $form_settings['your_mail'] : '';
        $message_format = isset( $form_settings['message_format'] ) ? $form_settings['message_format'] : '';
        $message_header = isset( $form_settings['message_header'] ) ? $form_settings['message_header'] : 'From: {email}From: {email}\r\nReply-To: {email} \r\nCC: example@example.com\r\nMIME-Version: 1.0\r\nContent-Type: text/html; charset=ISO-8859-1\r\n';

        $save_status_selected  = isset( $form_settings['save_status'] ) ? $form_settings['save_status'] : 'publish';
        $restrict_message      = __( "This page is restricted. Please Log in / Register to view this page.", 'wpuf' );

        $redirect_to           = isset( $form_settings['redirect_to'] ) ? $form_settings['redirect_to'] : 'post';
        $message               = isset( $form_settings['message'] ) ? $form_settings['message'] : __( 'Post saved', 'wpuf' );
        $update_message        = isset( $form_settings['update_message'] ) ? $form_settings['update_message'] : __( 'Post updated successfully', 'wpuf' );
        $page_id               = isset( $form_settings['page_id'] ) ? $form_settings['page_id'] : 0;
        $url                   = isset( $form_settings['url'] ) ? $form_settings['url'] : '';

        $submit_text           = isset( $form_settings['submit_text'] ) ? $form_settings['submit_text'] : __( 'Submit', 'wpuf' );
        $draft_text            = isset( $form_settings['draft_text'] ) ? $form_settings['draft_text'] : __( 'Save Draft', 'wpuf' );
        $preview_text          = isset( $form_settings['preview_text'] ) ? $form_settings['preview_text'] : __( 'Preview', 'wpuf' );
        $save_message            = isset( $form_settings['save_message'] ) ? $form_settings['save_message'] : 'false';

        $success_msg           = isset( $form_settings['success_message'] ) ? $form_settings['success_message'] : 'You Message Has been Sent Successfully !';
        $failure_msg           = isset( $form_settings['failure_msg'] ) ? $form_settings['failure_msg'] : 'You Message Could Not be Sent !';
        ?>
        <table class="form-table">

            <tr>
                <th><?php _e( 'Message Header', 'wpuf' ); ?></th>
                <td>
                    <textarea rows="7" cols="40" name="wpuf_settings[message_header]"><?php echo esc_textarea( $message_header ); ?></textarea>
                </td>
            </tr>

            <tr>
                <th><?php _e( 'Message Format', 'wpuf' ); ?></th>
                <td>
                    <textarea rows="7" cols="40" name="wpuf_settings[message_format]"><?php echo esc_textarea( $message_format ); ?></textarea>
                    <p class="description">
                        <?php _e( 'Message will be sent in this format, use shortcode like the following. For <br>', $domain = 'default' ) ?>
                        <?php _e( " subject -> {subject},
                username -> {username},<br>
                First Name -> {firstname},<br>
                Last Name -> {lastname},<br>
                Nickname -> {nickname},<br>
                Email -> {email},<br>
                Website Url -> {website_url},<br>
                Biographical info -> {biographical_info}<br>", $domain = 'default' ) ?>
                        <?php _e( 'For other fields added in the form , use the "{ your given name to the field }" formate <br>', $domain = 'default' ) ?>
                    </p>
                </td>
            </tr>

            <tr class="wpuf-post-status">
                <th><?php _e( 'Your E-mail', 'wpuf' ); ?></th>
                <td>
                    <input type="text" name="wpuf_settings[your_mail]" value="<?php echo esc_attr( $your_mail ); ?>">
                </td>
            </tr>




            <tr class="wpuf-redirect-to">
                <th><?php _e( 'Redirect To', 'wpuf' ); ?></th>
                <td>
                    <select name="wpuf_settings[redirect_to]">
                        <?php
                        $redirect_options = array(
                            'post' => __( 'Newly created post', 'wpuf' ),
                            'same' => __( 'Same Page', 'wpuf' ),
                            'page' => __( 'To a page', 'wpuf' ),
                            'url' => __( 'To a custom URL', 'wpuf' )
                        );

                        foreach ($redirect_options as $to => $label) {
                            printf('<option value="%s"%s>%s</option>', $to, selected( $redirect_to, $to, false ), $label );
                        }
                        ?>
                    </select>
                    <p class="description">
                        <?php _e( 'After successfull submit, where the page will redirect to', $domain = 'default' ) ?>
                    </p>
                </td>
            </tr>

            </tr>

            <tr class="wpuf-page-id">
                <th><?php _e( 'Page', 'wpuf' ); ?></th>
                <td>
                    <select name="wpuf_settings[page_id]">
                        <?php
                        $pages = get_posts(  array( 'numberposts' => -1, 'post_type' => 'page') );

                        foreach ($pages as $page) {
                            printf('<option value="%s"%s>%s</option>', $page->ID, selected( $page_id, $page->ID, false ), esc_attr( $page->post_title ) );
                        }
                        ?>
                    </select>
                </td>
            </tr>

            <tr class="wpuf-url">
                <th><?php _e( 'Custom URL', 'wpuf' ); ?></th>
                <td>
                    <input type="url" name="wpuf_settings[url]" value="<?php echo esc_attr( $url ); ?>">
                </td>
            </tr>



            <tr class="wpuf-submit-text">
                <th><?php _e( 'Submit Post Button text', 'wpuf' ); ?></th>
                <td>
                    <input type="text" name="wpuf_settings[submit_text]" value="<?php echo esc_attr( $submit_text ); ?>">
                </td>
            </tr>

            <tr>
                <th><?php _e( 'Success Message', 'wpuf' ); ?></th>
                <td>
                    <textarea rows="3" cols="40" name="wpuf_settings[success_msg]"><?php echo esc_textarea( $success_msg ); ?></textarea>
                    <p class="description">
                        <?php _e( 'This message will be shown after the sending the message successfully ', $domain = 'default' ) ?>
                    </p>
                </td>
            </tr>

            <tr>
                <th><?php _e( 'Failure Message', 'wpuf' ); ?></th>
                <td>
                    <textarea rows="3" cols="40" name="wpuf_settings[failure_msg]"><?php echo esc_textarea( $failure_msg ); ?></textarea>
                    <p class="description">
                        <?php _e( 'This message will be shown after if sending the message is failed ', $domain = 'default' ) ?>
                    </p>
                </td>
            </tr>

        </table>
    <?php
    }

}

new wcf_contact_form_stuff();