<?php

declare(strict_types=1);

namespace app\services\Strategy;

use DOMDocument;
use yii\httpclient\Client;

/**
 * PTStrategy class implements CheckSiteStrategyInterface to load changes from a specific site.
 * It checks for Schengen visa information and returns relevant details.
 */
class PTStrategy implements CheckSiteStrategyInterface
{
    /**
     * @inheritDoc
     */
    public function loadTargetData(string $url): string
    {
        $client = new Client();
        $response = $client->createRequest()
            ->setMethod('GET')
            ->setUrl($url)
            ->send();

        if ($response->isOk) {
            $html = $response->content;

            //we need to check Schengen and find if there are going to be open slots
            if (!strpos($html, 'Schengen')) {
                return '';
            }

            $dom = new DOMDocument();
            libxml_use_internal_errors(true);
            @$dom->loadHTML($html);

            $foundSchengen = false;
            $info = [];
            foreach ($dom->getElementsByTagName('p') as $p) {
                $text = trim($p->textContent);

                if (stripos($text, 'Schengen') !== false) {
                    $foundSchengen = true;
                    continue;
                }

                if ($foundSchengen) {
                    // Stop if we reached a new visa section
                    if (preg_match('/Digital Nomads|D7|Work Search/i', $text)) {
                        break;
                    }

                    if (!empty($text)) {
                        $info[] = $text;
                    }
                }
            }
            return implode(' ', $info);
        }
        return '';
    }
}
