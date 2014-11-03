<?php 
$I = new AcceptanceTester($scenario);
$I->wantTo('see category tagging');
$I->amOnPage($I->getCategoryPageUrl());

$I->seeElement('div', array('class' => 'nosto_category'));
