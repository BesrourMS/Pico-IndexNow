# Pico IndexNow Plugin

**Save the Plugin:**
   Save the updated `Pico_IndexNow.php` file in the `plugins` directory of your Pico CMS installation.

**Enable the Plugin:**
   Ensure the plugin is enabled in your `config/config.yml` file:

   ```yaml
   plugins:
       Pico_IndexNow: true
   ```

**Get an API Key:**
   Ensure you have an IndexNow API key from Bing and replace `'your-api-key-here'` in the plugin code with your actual API key.

**Test the Plugin:**
   Add or update an article in your Pico CMS blog and verify that the `modified` field is added or updated in the YAML header. Additionally, check that the URL submission to Bing occurs correctly.
