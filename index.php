<?php
   /*
   Plugin Name:Video Conferencing
   Plugin URI: https://www.upwork.com/freelancers/~019ec44e1226ced777
   description: A Plugin to enable live video meetings in wordpress websites
   Version: 0.5
   Author: Suyash Ekhande
   Author URI: https://www.upwork.com/freelancers/~019ec44e1226ced777
   License: GPL2
   
   */
   
   if ( ! defined( 'WPINC' ) ) {
	die;
}

add_action('admin_menu', 'jitwp_setup_menu');
function jitwp_setup_menu(){
    add_menu_page( 'Video Conference Settings', 'Video Conference Settings', 'manage_options', 'vidcon-wordpress-settings', 'jitwp_admin_page_init', 'dashicons-format-chat' );
    add_action( 'admin_init', 'jitwp_register_settings' );
}

function jitwp_register_settings(){
    register_setting( 'jitwp', 'jitwp_username_pull' );
    register_setting( 'jitwp', 'jitwp_email_pull' );
    register_setting( 'jitwp', 'jitwp_jwt' );
    register_setting( 'jitwp', 'jitwp_display_chat_above_footer' );
    register_setting( 'jitwp', 'jitwp_url' );
    register_setting( 'jitwp', 'jitwp_default_roomname' );
    register_setting( 'jitwp', 'jitwp_server_url' );
}

function jitwp_admin_page_init(){
    ?>
    <div class="wrap" style="background:white;padding-left: 10px;border-style: solid;">
        <h1>Video Conference Settings</h1>
        <h2>How to use the ShortCode?</h2>
        <p>You can use the following ShortCode to embed meeting into any wordpress page or Area</p>
        <p><code>[vidcon]</code></p>
        <p> All Fields are optional</p></br>
        <code>[vidcon width="700px" height="700px" roomname="MEETING-001"] note: Please Use Strong roomname which cannot be guessed. You can set meeting password once you join meeting</code>
        <br/>
        <h2>Settings</h2>
        <form method="post" action="options.php">
        <?php settings_fields( 'jitwp' ); ?>
        <?php do_settings_sections( 'jitwp' ); ?>
            <table class="form-table" role="presentation">
                <tbody>
                    <tr>
                        <th scope="row">Server URL</th>
                        <td>
                            <fieldset>
                                <legend class="screen-reader-text"><span>vidcon Server URL</span></legend>
                                <label for="jitwp_url">
                                    <input type="text" name="jitwp_url" value="<?php echo get_option('jitwp_url') ?>" placeholder="put https://meet.jit.si" id="jitwp_url" size="100" class="" />
                                </label>
                                <p>You can use the free server url for lifetime but if you want customized features and addon you can <a href="mailto:suyashekhande@gmail.com" >contact us</a> so we can host a custom server for you on cloud or vps</p>
                            </fieldset>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">User Details<br/>For Registered & Logged in Users</th>
                        <td>
                            <fieldset>
                                <legend class="screen-reader-text"><span>For Registered & Logged in Users</span></legend>
                                <label for="jitwp_username_pull">
                                    <input name="jitwp_username_pull" type="checkbox" id="jitwp_username_pull" value="1" <?php checked(1, get_option('jitwp_username_pull'), true); ?> />
                                    Use Wordpress Username for Meetings
                                </label>
                                <br />
                                <label for="jitwp_email_pull">
                                    <input name="jitwp_email_pull" type="checkbox" id="jitwp_email_pull" value="1" <?php checked(1, get_option('jitwp_email_pull'), true); ?> />
                                    Use Wordpress E-Mail for Meetings
                                </label>
                            </fieldset>
                            
                        </td>
                    </tr>
                  </tbody>
            </table>
            <?php submit_button(); ?>
        </form>
    
    </div>
    <?php
}


add_action('wp_enqueue_scripts', 'jitwp_init');
function jitwp_init() {
    $jitwp_url = get_option('jitwp_url');
    if(isset($jitwp_url) === true && strlen($jitwp_url) > 0){
        $jitwp_url = esc_url( trailingslashit(get_option('jitwp_url')));
        $jitwp_jwt =  get_option('jitwp_jwt');
        $jitjwt = isset($jitwp_jwt) === true && strlen($jitwp_jwt) > 0 ? "const jwt = '$jitwp_jwt';" : "const jwt = document.getElementById(`jitwp_shortcode`).getAttribute(`jwt`);";
        
        $userNameDetails = 'const userName = document.getElementById(`jitwp_shortcode`).getAttribute(`username`);';
        $userEmailDetails = 'const userEmail = document.getElementById(`jitwp_shortcode`).getAttribute(`useremail`);';
        $jitwp_username_pull = get_option('jitwp_username_pull');
        $jitwp_email_pull = get_option('jitwp_email_pull');
        if ($jitwp_username_pull || $jitwp_email_pull ){
            $user_id = get_current_user_id(); 
            if($user_id > 0){
                $user_info = get_userdata($user_id);
                $userName = $user_info->user_login;
                $userEmail = $user_info->user_email;
                if ($jitwp_username_pull){
                    $userNameDetails = 'const userName = "'.$userName.'";';
                }
                if ($jitwp_email_pull){
                    $userEmailDetails = 'const userEmail = "'.$userEmail.'";';
                }
            }
           
        }

        wp_enqueue_script( 'jitsi-js', $jitwp_url . 'external_api.js', false );
        echo '
        <script>
        window.onload = () => {
            if(document.getElementById(`jitwp_shortcode`)){
                const roomName = document.getElementById(`jitwp_shortcode`).getAttribute(`roomname`) ;
                '.$jitjwt.'
                const width = document.getElementById(`jitwp_shortcode`).getAttribute(`width`) || `800px`;
                const height = document.getElementById(`jitwp_shortcode`).getAttribute(`height`) || `600px`;
                '.$userNameDetails.'
                '.$userEmailDetails.'
                const domain = `'.str_replace("http://", "", str_replace("https://", "" ,$jitwp_url)).'`;
                let options = {
                   interfaceConfigOverwrite: { 
                                               CLOSE_PAGE_GUEST_HINT: false,
                                               DEFAULT_LOCAL_DISPLAY_NAME: `Myself`,
                                               SHOW_JITSI_WATERMARK: false,
                                               SHOW_PROMOTIONAL_CLOSE_PAGE: false,
                                               JITSI_WATERMARK_LINK: `http://127.0.0.1/`,
                                               DEFAULT_REMOTE_DISPLAY_NAME: `Guest`,
                                               TOOLBAR_BUTTONS: [
                                                                `microphone`, `camera`, `closedcaptions`, `desktop`, `embedmeeting`, `fullscreen`,
                                                                `fodeviceselection`, `hangup`, `profile`, `chat`, `etherpad`, `sharedvideo`, `settings`, `raisehand`,
                                                                `videoquality`, `shortcuts`,
                                                                `tileview`, `videobackgroundblur`, `download`,`mute-everyone`,
                                                            ],
                                              },
                };

                roomName ? options.roomName = roomName : null;
                width ? options.width = width : null;
                height ? options.height = height : null;
                jwt ? options.jwt = jwt : null;
                userName || userEmail ? options.userInfo = {} : null;
                //Below doesn\'t work - https://github.com/vidcon/vidcon-meet/issues/5018
                userName ? options.userInfo.displayName = userName : null;
                userEmail ? options.userInfo.email = userEmail : null;
                
                options.parentNode = document.getElementById(`jitwp_shortcode`)

                const api = new JitsiMeetExternalAPI(domain, options);
                //Quick Fix - https://github.com/vidcon/vidcon-meet/issues/5018
                userName ? api.executeCommand (`displayName`, userName) : null;
                console.log(options);
            }
        }
        </script> 
        ';
    }
}


add_shortcode('vidcon', 'jitwp_shortcode');
function jitwp_shortcode( $atts ) {
    $atts = shortcode_atts( array(
        'roomname' => 'default',
        'width' => '800px',
        'height' => '600px',
        'username' => null,
        'useremail' => null,
        'jwt' => '',

    ), $atts, 'jitwp' );
        
    return '<div id="jitwp_shortcode" jwt="'.$atts['jwt'].'" width="'.$atts['width'].'" height="'.$atts['height'].'" roomname="'.$atts['roomname'].'" username="'.$atts['username'].'" useremail="'.$atts['useremail'].'" ></div>';
}

