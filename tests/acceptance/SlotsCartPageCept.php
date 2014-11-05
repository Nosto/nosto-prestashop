<?php 
$I = new AcceptanceTester($scenario);
$I->wantTo('see slots on the cart page');
$I->addProductToCart($I);
$I->amOnPage($I->getCartPageUrl());

$I->seeGlobalSlots($I);

$I->seeElement('div', array('id' => 'nosto-page-cart1'));
$I->seeElement('div', array('id' => 'nosto-page-cart2'));
$I->seeElement('div', array('id' => 'nosto-page-cart3'));
