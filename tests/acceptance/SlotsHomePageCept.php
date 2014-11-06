<?php 
$I = new AcceptanceTester($scenario);
$I->wantTo('see slots on the home page');
$I->amOnPage('/');

$I->seeGlobalSlots($I);

$I->seeElement('div', array('id' => 'frontpage-nosto-1'));
$I->seeElement('div', array('id' => 'frontpage-nosto-2'));
$I->seeElement('div', array('id' => 'frontpage-nosto-3'));
$I->seeElement('div', array('id' => 'frontpage-nosto-4'));
