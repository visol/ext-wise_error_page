<?php
namespace Visol\WiseErrorPage\Validator;

/*
 * This file is part of the Visol/WiseErrorPage project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

/**
 * Class PageValidator
 */
class PageValidator extends AbstractValidator
{

    /**
     * @param int $page
     * @return void
     */
    public function isValid($page)
    {
        $tableName = 'pages';
        $clause = sprintf('uid = %s ', $page);
        $clause .= $this->getPageRepository()->enableFields($tableName);
        $clause .= $this->getPageRepository()->deleteClause($tableName);
        $record = $this->getDatabaseConnection()->exec_SELECTgetSingleRow('*', $tableName, $clause);
        if (empty($record)) {
            $message = sprintf('I could not find page %s', $page);
            $this->addError($message, 1492791291);
        }
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