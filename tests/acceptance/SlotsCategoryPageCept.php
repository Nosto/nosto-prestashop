<?php 
$I = new AcceptanceTester($scenario);
$I->wantTo('see slots on the category page');
$I->amOnPage($I->getCategoryPageUrl());

$I->seeGlobalSlots($I);

$I->seeElement('div', array('id' => 'nosto-page-category1'));
$I->seeElement('div', array('id' => 'nosto-page-category2'));
