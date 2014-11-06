<?php 
$I = new AcceptanceTester($scenario);
$I->wantTo('see slots on the search page');
$I->amOnPage($I->getSearchPageUrl());

$I->seeGlobalSlots($I);

$I->seeElement('div', array('id' => 'nosto-page-search1'));
$I->seeElement('div', array('id' => 'nosto-page-search2'));
