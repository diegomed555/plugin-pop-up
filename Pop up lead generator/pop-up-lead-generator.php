<?php
/*
Plugin Name: Popup Lead Generator
Description: Plugin para generar pop-ups personalizados y captar leads en WordPress.
Version: 2.2
Author: Diego Medina
*/

// Evitar el acceso directo
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

// Define el slug del plugin
define( 'PLG_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'PLG_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Crear la tabla de leads en la activación del plugin
register_activation_hook( __FILE__, 'plg_install' );

function plg_install() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'plg_leads';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name tinytext NOT NULL,
        email text NOT NULL,
        date datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}

// Encolar scripts y estilos
function plg_enqueue_scripts() {
    wp_enqueue_style( 'plg-styles', PLG_PLUGIN_URL . 'assets/css/style.css' );
    wp_enqueue_script( 'plg-scripts', PLG_PLUGIN_URL . 'assets/js/scripts.js', array('jquery'), null, true );

    // Configurar AJAX en scripts
    wp_localize_script( 'plg-scripts', 'plg_ajax', array(
        'ajax_url' => admin_url( 'admin-ajax.php' )
    ));
}
add_action( 'wp_enqueue_scripts', 'plg_enqueue_scripts' );

// Mostrar el pop-up en el footer de la página
function plg_display_popup() {
    ?>
    <div id="plg-popup" class="plg-popup hidden">
        <div class="plg-popup-content">
            <h2>¡No te vayas sin suscribirte!</h2>
            <p>Recibe nuestras mejores ofertas y novedades.</p>
            <form id="plg-lead-form">
                <input type="text" name="name" placeholder="Tu Nombre" required>
                <input type="email" name="email" placeholder="Tu Email" required>
                <button type="submit">Suscribirse</button>
            </form>
            <span class="plg-close">&times;</span>
        </div>
    </div>
    <?php
}
add_action( 'wp_footer', 'plg_display_popup' );

// Acción para capturar datos del formulario
function plg_capture_lead() {
    if ( isset($_POST['name']) && !empty($_POST['name']) && isset($_POST['email']) && !empty($_POST['email']) ) {
        $name = sanitize_text_field( $_POST['name'] );
        $email = sanitize_email( $_POST['email'] );

        // Verificar si el email es válido
        if ( !is_email($email) ) {
            wp_send_json_error('Correo electrónico no válido');
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'plg_leads';

        // Inserta los datos en la base de datos
        $wpdb->insert( $table_name, array(
            'name' => $name,
            'email' => $email,
            'date' => current_time( 'mysql' ),
        ));

        wp_send_json_success('Gracias por suscribirte');
    } else {
        wp_send_json_error('Por favor, completa todos los campos');
    }

    wp_die();
}
add_action( 'wp_ajax_nopriv_plg_capture_lead', 'plg_capture_lead' );
add_action( 'wp_ajax_plg_capture_lead', 'plg_capture_lead' );
