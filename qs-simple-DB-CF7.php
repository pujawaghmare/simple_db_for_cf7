<?php
/**
 * Plugin Name: Simple DB for CF7
 * Author: Shriram
 * Author URI: https://www.simple-db-cf7.com
 * Text Domain: simple-db-cf7
 * Description: Use plugin for display all contact form 7 records, export all data in CSV 
 * Version: 1.0.0
 */

    if( !defined('ABSPATH') ) {
        exit();
    } 

    function qs_register_custom_menu_page() {
        add_menu_page(
            __( 'Simple CF7 DB', 'simple-db-cf7' ),
            'Simple CF7 DB',
            'manage_options',
            'qs_simple_db_cf7',
            'qs_simple_db_cf7',
            '',
            6
        );
    }
    add_action( 'admin_menu', 'qs_register_custom_menu_page' );

    function qs_load_scripts($hook) {

        $screen    = get_current_screen();
        $screen_id = $screen ? $screen->id : '';

        wp_register_script( 'qs_ajax_script', admin_url( 'admin-ajax.php' ), array( 'jquery' ), '0.4.2' );
        wp_register_script( 'qs_jquery_validate', plugins_url( 'js/jquery.validate.min.js', __FILE__ ), array( 'jquery' ), '1.0.0' );
        wp_register_script( 'qs_custom_script', plugins_url( 'js/qs_custom.js', __FILE__ ), array( 'jquery', 'qs_ajax_script' ), '1.0.0' );
        wp_register_script( 'qs_bootstrap_js', plugins_url( 'js/bootstrap.min.js', __FILE__ ), array(), '1.0.0' );

        wp_register_style( 'qs_bootstrap_style',    plugins_url( 'css/bootstrap.min.css',    __FILE__ ), false,   '1.0.0' );


        
        wp_localize_script(
            'qs_ajax_script',
            'qs_ajax_script_param',
            array(
                'ajax_url' => admin_url( 'admin-ajax.php' )
            )
        );
    
        if($screen_id == 'toplevel_page_qs_simple_db_cf7') {
            wp_enqueue_style( 'qs_bootstrap_style' );
            wp_enqueue_script( 'qs_bootstrap_js' );
            wp_enqueue_script( 'qs_ajax_script' );
            wp_enqueue_script( 'qs_custom_script' );
            wp_enqueue_script( 'qs_jquery_validate' );
        }
        
    }
 
    add_action('admin_enqueue_scripts', 'qs_load_scripts');

    function qs_simple_db_cf7(){

        global $wpdb;

        $posts = get_posts([
            'post_type' => 'wpcf7_contact_form',
            'post_status' => 'publish',
            'numberposts' => -1
            // 'order'    => 'ASC'
          ]);


        ?>
        <label for="">Select Contact Form:</label>
        <select name="contact-form-name">
            <option value="">-Select-</option>
        <?php
        foreach($posts as $post) :
     ?>
    <option value="<?php echo $post->post_id; ?>"><?php echo ($post->post_title); ?></option>
     <?php  endforeach; ?>
        </select>
        <h3><?php echo esc_html( 'Contact Form 7 DB', 'qs_simple_db_cf7' ) ?></h3>
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="table table-stripe">
                        <table class="table table-strip">
                            <thead>
                                <tr>
                                    <th>Sr.No</th>
                                    <th>data</th>
                                    <th>Sent Date</th>
                                </tr>
                            </thead>
                                <?php 
                                $form_data = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}simple_cf7_db WHERE form_id = '35' ");
                                foreach($form_data as $data) :
                                    $f_data = (array)json_decode($data->data);
                                ?>
                                <tr>
                                    <td><?php echo $data->id; ?></td>
                                    <td><?php foreach($f_data as $key=>$form_data) :
                                                echo '<strong>'.$key ."</strong> :: " . $form_data."<br>";
                                              endforeach;
                                        ?></td>
                                    <td><?php echo $data->created_date; ?></td>
                                </tr>
                                <?php 
                                    endforeach;
                                ?>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php 
    }

    register_activation_hook(
        __FILE__,
        'qs_cf7_db_plugin_activate'
      );
    
    function qs_cf7_db_plugin_activate() {
        try {

            global $wpdb;
            $query = $wpdb->query( "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}simple_cf7_db (
               `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
               `form_id` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
               `data` text COLLATE utf8mb4_unicode_520_ci NOT NULL,
               `created_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
               PRIMARY KEY (`id`)
           )");

        } catch(Exception $e) {
            echo $e->getMessage();
        }
       
    }  

    add_action("wpcf7_before_send_mail", "qs_insert_db_data", 10, 3);

    function qs_insert_db_data($WPCF7_ContactForm, $abort, $submission){

        $result = [];

        $form_id = $WPCF7_ContactForm->id();

        $data['form_id'] =  $form_id;
        $data['data'] = json_encode($submission->get_posted_data());

        global $wpdb;

        //$insert_sql = "INSERT INTO {$wpdb->prefix}simple_cf7_db (`form_id`, `data`) VALUES (".$data['form_id'].", ".$data['data'].")";

        

        $prepare_query = $wpdb->prepare(
            "INSERT INTO {$wpdb->prefix}simple_cf7_db
            ( `form_id`, `data` )
            VALUES ( %s, %s )",
            $data['form_id'],
            $data['data']
        );

        $wpdb->query($prepare_query);

        /* print_r($WPCF7_ContactForm);
        exit("Sss"); */
    }