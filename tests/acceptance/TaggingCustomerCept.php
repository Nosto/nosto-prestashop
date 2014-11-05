<?php 
$I = new AcceptanceTester($scenario);
$I->wantTo('see logged in customer tagging');
$I->createAccountAndLogin($I);
$I->amOnPage('/');

$I->seeElement('div', array('class' => 'nosto_customer'));
