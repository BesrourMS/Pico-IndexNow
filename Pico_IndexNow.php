<?php

/**
 * Pico IndexNow Plugin
 */
class Pico_IndexNow extends AbstractPicoPlugin
{
    const API_URL = 'https://www.bing.com/indexnow';

    protected $enabled = false;

    public function onConfigLoaded(array &$config)
    {
        $this->enabled = isset($config['indexnow_api_key']) && !empty($config['indexnow_api_key']);
    }

    public function onContentCreated(array &$pages, array &$currentPage, array &$previousPage)
    {
        if (!$this->enabled) {
            return;
        }

        $contentDir = $this->getConfig('content_dir');
        $filePath = $contentDir . $currentPage['id'] . '.md';

        if ($this->isNewArticle($currentPage)) {
            $this->submitUrl($currentPage['url']);
            $this->updatePublishedField($filePath);
        }
    }

    private function isNewArticle($page)
    {
        // Assuming 'date' and 'published' meta fields are available in your content's front-matter
        $created = isset($page['meta']['date']) ? strtotime($page['meta']['date']) : null;
        $published = isset($page['meta']['published']) ? (bool) $page['meta']['published'] : false;

        // Consider the article new if 'published' is false
        return $created && !$published;
    }

    private function submitUrl($url)
    {
        $api_key = $this->getConfig('indexnow_api_key'); // Get API key from config.yaml

        $json = json_encode([
            'host' => $_SERVER['HTTP_HOST'],
            'urlList' => [$url],
        ]);

        $ch = curl_init(self::API_URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Host: ' . $_SERVER['HTTP_HOST'],
            'Key: ' . $api_key,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($httpCode !== 200) {
            // Log the error
            error_log("Failed to submit URL to IndexNow: HTTP $httpCode - $curlError");
        }
    }

    private function updatePublishedField($filePath)
    {
        if (!file_exists($filePath)) {
            return;
        }

        $fileContent = file_get_contents($filePath);
        $pattern = '/^---\s*(.*?)\s*---/s';
        preg_match($pattern, $fileContent, $matches);

        if (isset($matches[1])) {
            $yamlHeader = $matches[1];
            $yamlLines = explode("\n", $yamlHeader);
            $foundPublished = false;

            foreach ($yamlLines as &$line) {
                if (strpos($line, 'published:') !== false) {
                    $line = 'published: true';
                    $foundPublished = true;
                    break;
                }
            }

            if (!$foundPublished) {
                $yamlLines[] = 'published: true';
            }

            $newYamlHeader = implode("\n", $yamlLines);
            $newFileContent = preg_replace($pattern, "---\n$newYamlHeader\n---", $fileContent);

            file_put_contents($filePath, $newFileContent);
        }
    }
}
