<?php 
$I = new AcceptanceTester($scenario);
$I->wantTo('see product tagging');
$I->amOnPage($I->getProductPageUrl());

$I->seeElement('div', array('class' => 'nosto_product'));
