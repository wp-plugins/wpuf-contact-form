<?php
/**
 * User: mithu_000
 * Date: 8/1/2015
 * Time: 9:17 PM
 */

class wcf_cotact_form_frontend extends WPUF_Frontend_Form_Post /*WPUF_Render_Form*/{

    function __construct(){
        add_shortcode( 'wpuf_contact_form', array( $this, 'render_wcf_cotnact_form' ) );
        add_action( 'wcf_contact_form_process', array( $this, 'process_form_data' ), 1 , 1 );

        //ajax
        add_action( 'wp_ajax_wcf_submit_form', array( $this, 'submit_post' ) );
        add_action( 'wp_ajax_nopriv_wcf_submit_form', array( $this, 'submit_post' ) );
    }

    function render_wcf_cotnact_form( $atts ){
        $atts = shortcode_atts( array(
            'id' => ''
        ), $atts, 'wpuf_contact_form' );
        ?>
        <form class="wcf-form-add" method="post" enctype="multipart/form-data" >
            <input type="hidden" name="form_id" value="<?php echo $atts['id']; ?>"/>
            <input type="hidden" name="wsf_contact_form_editor" id="wsf_contact_form_editor" value="<?php echo wp_create_nonce( plugin_basename( __FILE__ ) ); ?>" />
            <?php parent::render_form( $atts['id'], null, true ); ?>
        </form>
    <?php
    }

    /**
     * Process form data
     */
    function process_form_data( $form_id ){
        global $post;
        $form_settings = wpuf_get_form_settings( $form_id );
        $form_inputs = wpuf_get_form_fields( $form_id );


        if( isset( $_POST['_wpnonce'] ) ){

            /*if( !wp_verify_nonce( $_REQUEST['_wpnonce'], 'wcf_contact-form-' . $form_id ) ){
                return;
            }*/
            if ( !isset($_POST['wsf_contact_form_editor'] ) ) {
                return $post->ID;
            }

            if ( !wp_verify_nonce( $_POST['wsf_contact_form_editor'], plugin_basename( __FILE__ ) ) ) {
                return $post->ID;
            }





            $message = isset( $form_settings['message_format'] ) ? $form_settings['message_format']: '';
            $headers = isset( $form_settings['message_header'] ) ? $form_settings['message_header']: '';
            $attachments = array();

            $post_title = isset( $_POST['post_title'] ) ? sanitize_text_field( $_POST['post_title'] ) : '';
            $user_login = isset( $_POST['user_login'] ) ? sanitize_text_field( $_POST['user_login'] ) : '';
            $first_name = isset( $_POST['first_name'] ) ? sanitize_text_field( $_POST['first_name'] ) : '';
            $last_name = isset( $_POST['last_name'] ) ? sanitize_text_field( $_POST['last_name'] ) : '';
            $nickname = isset( $_POST['nickname'] ) ? sanitize_text_field( $_POST['nickname'] ) : '';
            $user_email = isset( $_POST['user_email'] ) ? sanitize_email( $_POST['user_email'] ) : '';
            $user_url = isset( $_POST['user_url'] ) ? esc_url( $_POST['user_url'] ) : '';
            $description = isset( $_POST['description'] ) ? FILTER_SANITIZE_SPECIAL_CHARS( $_POST['description'] ) : '';

            $str_to_replace = array(
                '{subject}',
                '{username}',
                '{firstname}',
                '{lastname}',
                '{nickname}',
                '{email}',
                '{website_url}',
                '{biographical_info}',

            );
            $str_to_put = array(
                $post_title ,
                $user_login ,
                $first_name ,
                $last_name ,
                $nickname ,
                $user_email ,
                $user_url,
                $description
            );

            $name_to_skip = array( 'post_title', 'user_login', 'first_name', 'last_name', 'nickname', 'user_email', 'user_url', 'description' );


            if( is_array( $_POST ) ){
                foreach( $_POST as $field_name => $val  ){//echo $field_name.'<br>';
                    if( !in_array( $field_name, $name_to_skip ) && !is_array( $val ) ){
                        $str_to_replace[] = '{'.$field_name.'}';
                        $str_to_put[] = $_POST[ $field_name ];
                    }elseif( $field_name == 'wpuf_files' ){
                        foreach( $val as $file => $file_data ){
                            $file_url = get_attached_file( $file_data[0] );
                            $attachments[] = $file_url;
                        }
                    }

                }
            }

            $to = isset( $form_settings['your_mail'] ) ? $form_settings['your_mail'] : '' ;
            $subject = $post_title;
            $message = nl2br ( str_replace( $str_to_replace, $str_to_put, $message ) );
            $headers = nl2br( str_replace( $str_to_replace, $str_to_put, $headers ) );


            $res = array();

            //send mail
            if( wp_mail( $to, $subject, $message, $headers, $attachments ) ){
                $res['success'] = array(
                    'message' => $form_settings['success_msg']
                );
            }else{
                $res['error'] = array(
                    'message' => $form_settings['failure_msg']
                );;
            };

            //redirect
            if( $form_settings['redirect_to'] == 'same' ){
                 $res['redirect'] =  'same';//$_SERVER[ 'REQUEST_URI' ];
            }elseif( $form_settings['redirect_to'] == 'page' ){
                $res['redirect'] = get_permalink( $form_settings['page_id'] );
            }elseif( $form_settings['redirect_to'] == 'url' ){
                $res['redirect'] = $form_settings['url'];
            }

            echo json_encode($res);

        }
    }

    function submit_post(){
        do_action( 'wcf_contact_form_process', $_POST['form_id'] );
        exit;
    }
}

new wcf_cotact_form_frontend();