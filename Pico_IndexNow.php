<?php

/**
 * Pico IndexNow Plugin
 */
class Pico_IndexNow extends AbstractPicoPlugin
{
    const API_URL = 'https://www.bing.com/indexnow';

    public function onContentCreated(array &$pages, array &$currentPage, array &$previousPage)
    {
        $contentDir = $this->getConfig('content_dir');
        $filePath = $contentDir . $currentPage['id'] . '.md';

        if ($this->isNewArticle($currentPage)) {
            $this->submitUrl($currentPage['url']);
            $this->updateModifiedField($filePath);
        }
    }

    private function isNewArticle($page)
    {
        // Assuming 'date' and 'modified' meta fields are available in your content's front-matter
        $created = isset($page['meta']['date']) ? strtotime($page['meta']['date']) : null;
        $modified = isset($page['meta']['modified']) ? strtotime($page['meta']['modified']) : null;

        // Consider the article new if 'modified' is null or equal to 'created'
        return $created && (!$modified || $created === $modified);
    }

    private function submitUrl($url)
    {
        $api_key = 'your-api-key-here'; // Replace with your actual API key

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
        curl_close($ch);
    }

    private function updateModifiedField($filePath)
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
            $foundModified = false;
            $modifiedDate = date('Y-m-d H:i:s');

            foreach ($yamlLines as &$line) {
                if (strpos($line, 'modified:') !== false) {
                    $line = 'modified: ' . $modifiedDate;
                    $foundModified = true;
                    break;
                }
            }

            if (!$foundModified) {
                $yamlLines[] = 'modified: ' . $modifiedDate;
            }

            $newYamlHeader = implode("\n", $yamlLines);
            $newFileContent = preg_replace($pattern, "---\n$newYamlHeader\n---", $fileContent);

            file_put_contents($filePath, $newFileContent);
        }
    }
}
