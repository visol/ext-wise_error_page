<?php
namespace Visol\WiseErrorPage\Cache;

/*
 * This file is part of the Visol/WiseErrorPage project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Class CacheHandler
 */
class CacheHandler extends ActionController
{

    /**
     * @var string
     */
    protected $cacheDirectory = 'typo3temp/Cache/Data/wise_error_page';

    /**
     * This function will be called by the clearCachePostProc hook
     *
     * @param array $params
     * @param object $pObj
     * @return  void
     */
    public function clearCachePostProc($params, $pObj)
    {
        if (in_array($params['cacheCmd'], ['all', 'pages', 'system'], true)) {
            try {
                $cacheDirectory = PATH_site . $this->cacheDirectory;
                if (is_dir($cacheDirectory)) {
                    GeneralUtility::flushDirectory($cacheDirectory);
                }
            } catch (\Exception $e) {
                GeneralUtility::sysLog($e->getMessage(), 'static_error_page', 3);
                $this->getBackendUser()->simplelog($e->getMessage(), 'static_error_page', 2);
            }
        }
    }

    /**
     * Returns an instance of the current Backend User.
     *
     * @return \TYPO3\CMS\Core\Authentication\BackendUserAuthentication
     */
    protected function getBackendUser()
    {
        return $GLOBALS['BE_USER'];
    }
}