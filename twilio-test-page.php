<h2> <?php esc_attr_e( 'Test SMS Sending', 'WpAdminStyle' ); ?></h2>
<div class="wrap">
    <div class="metabox-holder columns-2">
        <div class="meta-box-sortables ui-sortable">
            <div class="postbox">
                <h2 class="hndle">
                    <span> <?php esc_attr_e( 'TEST SMS', 'WpAdminStyle' ); ?></span>
                </h2>
                <div class="inside">
                    <?php $api_details = get_option($this->pluginName);  ?>
                    <form method="post" name="cleanup_options" action="">
                        <input type="text" <?php echo $api_details["twapi_from_num"] ? "value='{$api_details['twapi_from_num']}'" :''; ?> 
                        name="sender" class="regular-text" placeholder="Sender ID" required /><br><br>
                        <input type="text" name="numbers" class="regular-text" placeholder="+23480597..." required /><br><br>
                        <textarea name="message" cols="50" rows="7" placeholder="Message"></textarea><br><br>
                        <input class="button-primary" type="submit" value="SEND MESSAGE" name="send_sms_message" />
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>