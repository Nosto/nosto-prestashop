<?php
/**
 * 2013-2016 Nosto Solutions Ltd
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to contact@nosto.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    Nosto Solutions Ltd <contact@nosto.com>
 * @copyright 2013-2016 Nosto Solutions Ltd
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

/**
 * Model for tagging search terms.
 */
class NostoTaggingSearch extends NostoTaggingModel
{
    /**
     * @var string the search term.
     */
    protected $search_term;

    /**
     * Setter for the search term.
     *
     * @param string $search_term the term.
     */
    public function setSearchTerm($search_term)
    {
        $this->search_term = $search_term;
    }

    /**
     * Getter for the search term.
     *
     * @return string the term.
     */
    public function getSearchTerm()
    {
        return $this->search_term;
    }
}
