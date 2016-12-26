<?php

namespace Helper;

class ExtendedWebDriver extends \Codeception\Module\WebDriver
{
    public function findField($selector) {
        return parent::findField($selector);
    }
}