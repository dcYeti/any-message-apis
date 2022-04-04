<form method="POST" action='options.php'>
   <?php
        settings_fields($this->pluginName);
        do_settings_sections('twapi-settings-page');
        submit_button();  
   ?>
</form>