<?php
$I = new AcceptanceTester($scenario);
$I->wantTo('see slots on the product page');
$I->amOnPage($I->getProductPageUrl());

$I->seeGlobalSlots($I);

$I->seeElement('div', array('id' => 'nosto-page-product1'));
$I->seeElement('div', array('id' => 'nosto-page-product2'));
$I->seeElement('div', array('id' => 'nosto-page-product3'));
