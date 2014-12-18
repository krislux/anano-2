<?php 
$I = new AcceptanceTester($scenario);
$I->wantTo('ensure the front page displays correctly');
$I->amOnPage('/');
$I->see('Welcome');