<?php 
$I = new AcceptanceTester($scenario);
$I->wantTo('see order tagging');
$I->orderProduct($I);

$I->seeElement('div', array('class' => 'nosto_purchase_order'));
