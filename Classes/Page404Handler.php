<?php

/**
 * Class Page404Handler
 *
 * This class is meant to be used by TYPO3 in the "pageNotFound_handling" configuration key:
 *
 * 'pageNotFound_handling' => 'USER_FUNCTION:typo3conf/ext/wise_error_page/Classes/Page404Handler.php:',
 *
 * Note the script is standalone and can be directly used by the web server. Example for Nignx:
 *
 * error_page 404  /typo3conf/ext/wise_error_page/Classes/Page404Handler.php;
 */
class Page404Handler
{
    /**
     * Edit me!
     * @var array
     */
    protected $domains = [
        'default' => [
            'rootPage' => 201, # required
            '404Page' => 3, # required
            'languages' => [ # optional - todo must be dynamically read from RealURL
                'de' => 0,
                'fr' => 1,
                'it' => 2,
            ],
        ],
        #'domain.tld' => [
        #    ...
        #]
    ];

    ///////////////////////////////////////////////////
    // Do not touch below unless there are good reasons
    ///////////////////////////////////////////////////

    /**
     * @var array
     */
    protected $configuration = [];

    /**
     * @var array
     */
    protected $configurationIdentifier = [];

    /**
     * @var int
     */
    protected $languageIdentifier = 0;

    /**
     * @var string
     */
    protected $pageParameter = 'tx_wiseerrorpage_pi1[page]';

    /**
     * @var string
     */
    protected $languageParameter = 'tx_wiseerrorpage_pi1[language]';

    /**
     * @var string
     */
    protected $staticFileCachePath = 'typo3temp/tx_ncstaticfilecache';

    /**
     * @var string
     */
    protected $dataSourceCachePath = 'typo3temp/Cache/Data/wise_error_page';

    /**
     * @var string
     */
    protected $dataSourceFile = '';

    /**
     * Page404Handler constructor.
     */
    public function __construct()
    {
        $this->configurationIdentifier = isset($this->domains[$_SERVER['HTTP_HOST']])
            ? $_SERVER['HTTP_HOST']
            : 'default';

        $this->configuration = $this->domains[$this->configurationIdentifier];

        $this->dataSourceFile = sprintf(
            '%s/%s/%s-%s.json',
            $_SERVER['DOCUMENT_ROOT'],
            $this->dataSourceCachePath,
            $this->configurationIdentifier,
            $this->languageIdentifier
        );

        if (isset($this->configuration['languages'])) {
            foreach ($this->configuration['languages'] as $language => $languageIdentifier) {
                $pattern = sprintf('#^/%s/.*#', $language);
                if (preg_match($pattern, $_SERVER['REQUEST_URI'])) {
                    $this->languageIdentifier = $languageIdentifier;
                }
            }
        }
    }

    /**
     * @param string $url
     * @return string
     */
    protected function get404FileNameAndPath($url)
    {
        $urlParts = parse_url($url);
        $cacheFilename = sprintf(
            '%s/%s/%s/%s/%s/index.html',
            $_SERVER['DOCUMENT_ROOT'],
            $this->staticFileCachePath,
            $urlParts['scheme'],
            $urlParts['host'],
            trim($urlParts['path'], '/')
        );

        return $cacheFilename;
    }

    /**
     * @return string
     */
    protected function getContent()
    {
        $storedData = $this->getStoredData();
        if (empty($storedData)) {

            $url = $this->resolve404Url();
            if ($url) {

                // Write into local database
                $fileNameAndPath = $this->get404FileNameAndPath($url);

                // write to database
                $storedData['fileName'] = $fileNameAndPath;
                $storedData['url'] = $url;
                $this->store($storedData);
            }
        }

        if (!is_file($storedData['fileName'])) {
            // will generate a static file cache.
            // Make sure the page does not contain a USER_INT or COA_INT plugin
            $this->fetch($storedData['url']);
        }

        // If the file was written to the file system we can read and output its content.
        $output = is_file($storedData['fileName'])
            ? file_get_contents($storedData['fileName'])
            : '<h1>File not found</h1>';

        return (string)$output;
    }

    /**
     * @param array $data
     */
    protected function store(array $data)
    {
        $directoryDataSourcePath = dirname($this->dataSourceFile);
        if (!is_dir($directoryDataSourcePath)) {
            mkdir($directoryDataSourcePath, 0777, true);
        }

        file_put_contents($this->dataSourceFile, json_encode($data));
    }

    /**
     * @return array
     */
    protected function getStoredData()
    {
        $result = [];

        // Read data stored
        if (is_file($this->dataSourceFile)) {
            $content = file_get_contents($this->dataSourceFile);
            $result = json_decode($content, true);
        }
        return $result;
    }

    /**
     * @return string
     */
    protected function resolve404Url()
    {
        $resolvedHomeUrl = $this->resolveHomeUrl();

        $formattedUrl = $this->format404Url($resolvedHomeUrl);

        $result = json_decode($this->fetch($formattedUrl), true);
        return $result['url'] ?: '';
    }

    /**
     * @param string $url
     * @return string
     */
    protected function fetch($url)
    {
        $handler = curl_init();
        #curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($handler, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handler, CURLOPT_URL, $url);
        $result = curl_exec($handler);

        curl_close($handler);
        return $result;
    }

    /**
     * @param string $url
     * @return string
     */
    protected function getRedirectUrl($url)
    {
        stream_context_set_default(array(
            'http' => array(
                'method' => 'HEAD'
            )
        ));
        $headers = get_headers($url, 1);
        if ($headers !== false && isset($headers['Location'])) {
            $url = $headers['Location'];
        }
        return $url;
    }


    /**
     * Compute the host. Note that the username / password is not supported.
     *
     * @return string
     */
    protected function getBaseUrl()
    {
        return 'http' . (($_SERVER['SERVER_PORT'] === 443) ? 's://' : '://') . $_SERVER['HTTP_HOST'];
    }

    /**
     * @param string $url
     * @return string
     */
    protected function format404Url($url)
    {
        $url = sprintf(
            '%s/?type=1497284951&%s=%s&%s=%s',
            $url,
            $this->pageParameter,
            $this->configuration['404Page'],
            $this->languageParameter,
            $this->languageIdentifier
        );
        return $url;
    }

    /**
     * @return string
     */
    protected function resolveHomeUrl()
    {
        // Check whether there is a redirect on the home page
        $resolvedHomeUrl = $this->getRedirectUrl($this->getBaseUrl());
        return rtrim($resolvedHomeUrl, '/');

        # See if the implementation above is sufficient.
        # There could be cases where the code below might be necessary.
        #if ($resolvedHomeUrl !== $this->getBaseUrl()) {
        #    // Check whether there is a redirect on the home page
        #    $url = sprintf(
        #        '%s/index.php?id=%s&L=%s',
        #        $this->getBaseUrl(),
        #        $this->configuration['rootPage'],
        #        $this->languageIdentifier
        #    );
        #    $resolvedHomeUrl = $this->getRedirectUrl($url);
        #    return $resolvedHomeUrl;
        #}
    }

    /**
     * Display the content
     */
    public function output()
    {
        print $this->getContent();
    }
}

$page = new Page404Handler();
$page->output();