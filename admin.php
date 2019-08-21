<?php
// create custom plugin settings menu
add_action('admin_menu', 'nobitex_converter_create_menu');

function nobitex_converter_create_menu() {

    //create new top-level menu
    add_menu_page('Nobitex Converter', 'Nobitex Converter', 'administrator', __FILE__, 'nobitex_converter_settings_page' , plugins_url('/img/nobitex.png', __FILE__) );

    //call register settings function
    add_action( 'admin_init', 'register_nobitex_converter_plugin_settings' );
}


function register_nobitex_converter_plugin_settings() {
    //register our settings
    register_setting( 'my-cool-plugin-settings-group', 'btc_rls' );
    register_setting( 'my-cool-plugin-settings-group', 'xrp_rls' );
    register_setting( 'my-cool-plugin-settings-group', 'ltc_rls' );
}

function nobitex_converter_settings_page() {
    ?>
    <div class="wrap">
        <h1>Nobitex Converter</h1>

        <form method="post" action="options.php">
            <?php settings_fields( 'my-cool-plugin-settings-group' ); ?>
            <?php do_settings_sections( 'my-cool-plugin-settings-group' ); ?>
            <?php
            $btc_checked = (get_option('btc_rls') !=false)?"checked= checked":"";
            $xrp_checked = (get_option('xrp_rls') !=false)?"checked= checked":"";
            $ltc_checked = (get_option('ltc_rls') !=false)?"checked= checked":"";


            ?>

            <table class="form-table">
                <tr valign="top">
                    <th scope="row">آیا مبدل بیتکوین-ریال فعال شود؟</th>
                    <td><input type="checkbox" name="btc_rls" id="btc_rls" disabled checked></td>
                </tr>

                <tr valign="top">
                    <th scope="row">آیا مبدل ریپل-ریال فعال شود؟</th>
                    <td><input type="checkbox" name="xrp_rls" id="xrp_rls" <?php echo $xrp_checked; ?>></td>
                </tr>
                <tr valign="top">
                    <th scope="row">آیا مبدل لایت کوین-ریال فعال شود؟</th>
                    <td><input type="checkbox" name="ltc_rls" id="ltc_rls" <?php echo $ltc_checked; ?>></td>
                </tr>
            </table>

            <?php submit_button(); ?>

        </form>
    </div>
<?php }; ?>