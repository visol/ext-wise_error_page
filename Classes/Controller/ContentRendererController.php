<?php
namespace Visol\WiseErrorPage\Controller;

/*
 * This file is part of the Visol/WiseErrorPage project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Class ContentRendererController
 */
class ContentRendererController extends ActionController
{

    /**
     * @param int $page
     * @param int $language
     * @return string
     * @validate $page Visol\WiseErrorPage\Validator\PageValidator
     */
    public function renderAction($page = 0, $language = 0)
    {
        $uriBuilder = $this->getControllerContext()->getUriBuilder();
        $url = $uriBuilder
            ->reset()
            ->setTargetPageUid($page)
            ->setTargetPageType(0)
            ->setCreateAbsoluteUri(true)
            ->setArguments(['L' => $language])
            ->build();

//        foreach ($this->getLanguages() as $language) {
//            $urls[$language['uid']] = $uriBuilder
//                ->setArguments(['L' => $language['uid']])
//                ->build();
//        }

        return json_encode(['url' => $url]);
    }

    /**
     * @return array|NULL
     */
    protected function getLanguages()
    {
        $tableName = 'sys_language';
        $clause = '1 = 1 ';
        $clause .= $this->getPageRepository()->enableFields($tableName);
        $clause .= $this->getPageRepository()->deleteClause($tableName);
        return $this->getDatabaseConnection()->exec_SELECTgetRows('*', $tableName, $clause);
    }

    /**
     * Returns a pointer to the database.
     *
     * @return \TYPO3\CMS\Core\Database\DatabaseConnection
     */
    protected function getDatabaseConnection()
    {
        return $GLOBALS['TYPO3_DB'];
    }

    /**
     * Returns an instance of the page repository.
     *
     * @return \TYPO3\CMS\Frontend\Page\PageRepository
     */
    protected function getPageRepository()
    {
        return $GLOBALS['TSFE']->sys_page;
    }
}