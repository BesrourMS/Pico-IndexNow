# Pico IndexNow Plugin

The Pico IndexNow Plugin enables automatic URL submission to Bing's IndexNow API when new content is created in your Pico CMS. This helps search engines quickly discover and index your new content.

## Installation

1. **Download the Plugin:**
   Download the `Pico_IndexNow` plugin from the [GitHub repository](https://github.com/BesrourMS/Pico_IndexNow).

2. **Unzip and Upload:**
   Unzip the downloaded file and upload the `Pico_IndexNow` directory to the `plugins/` directory of your Pico CMS installation.

3. **Enable the Plugin:**
   Enable the plugin by adding the following line to your `config/config.yml` file:
   ```yaml
   plugins:
     Pico_IndexNow: true
   ```

4. **Configure API Key:**
   Add your IndexNow API key to the `config/config.yml` file:
   ```yaml
   indexnow_api_key: your-api-key-here
   ```

## Usage

The plugin automatically submits the URL of new content to the IndexNow API. A new article is determined by the presence of a `date` field and the absence of a `published` field or if the `published` field is set to `false` in the article's front-matter.

When new content is created:
1. The plugin submits the URL to Bing's IndexNow API.
2. The `published` field in the front-matter of the article is updated to `true`.

## Front-Matter Fields

Ensure your content files contain the following front-matter fields:

- `date`: The creation date of the article.
- `published`: (Optional) A boolean field indicating if the article has been published.

Example:
```yaml
---
title: "Sample Article"
date: "2024-05-01"
published: false
---
```

## License

This project is licensed under the MIT License.

## Contributing

Contributions are welcome! Please fork the repository, make your changes, and submit a pull request.

## Support

If you encounter any issues or have any questions, please open an issue on the [GitHub repository](https://github.com/BesrourMS/Pico_IndexNow/issues).

## Acknowledgments

- [Pico CMS](https://picocms.org) - The flat file CMS this plugin is built for.
- [IndexNow](https://www.bing.com/indexnow) - The API used for URL submission.
  
