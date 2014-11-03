<?php 
$I = new AcceptanceTester($scenario);
$I->wantTo('see shopping cart tagging');
$I->addProductToCart($I);
$I->amOnPage($I->getCartPageUrl());

$I->seeElement('div', array('class' => 'nosto_cart'));
