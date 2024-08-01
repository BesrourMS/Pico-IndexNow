<?php

/**
 * Pico IndexNow Plugin
 */
class Pico_IndexNow extends AbstractPicoPlugin
{
    const API_URL = 'https://api.indexnow.org/indexnow';

    protected $enabled = false;

    public function onConfigLoaded(array &$config)
    {
        $this->enabled = !empty($config['indexnow_api_key']);
    }

    public function onPageProcessed(array &$page)
    {
        if (!$this->enabled) {
            return;
        }

        $contentDir = rtrim($this->getConfig('content_dir'), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $filePath = $contentDir . $page['id'] . '.md';

        if ($this->isNewArticle($page)) {
            $this->submitUrl($page['url']);
            $this->updatePublishedField($filePath);
        }
    }

    private function isNewArticle($page)
    {
        $created = isset($page['meta']['date']) ? strtotime($page['meta']['date']) : null;
        $published = isset($page['meta']['published']) ? (bool) $page['meta']['published'] : false;

        return $created && !$published;
    }

    private function submitUrl($url)
    {
        $api_key = $this->getConfig('indexnow_api_key');

        $data = [
            'host' => parse_url($url, PHP_URL_HOST),
            'key' => $api_key,
            'urlList' => [$url],
        ];

        $ch = curl_init(self::API_URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($httpCode !== 200) {
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
        if (preg_match($pattern, $fileContent, $matches)) {
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
            $newFileContent = preg_replace($pattern, "---\n$newYamlHeader\n---", $fileContent, 1);

            file_put_contents($filePath, $newFileContent);
        }
    }
}
