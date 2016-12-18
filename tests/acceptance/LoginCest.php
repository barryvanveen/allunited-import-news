<?php


class LoginCest
{
    public function _before(AcceptanceTester $I)
    {
    }

    public function _after(AcceptanceTester $I)
    {
    }

    // tests
    public function login(AcceptanceTester $I)
    {
        $I->amOnUrl('https://pr01.allunited.nl');

        $I->fillField('input[name="formlogin[section]"]', $_ENV['ALLUNITED_CLUB']);
        $I->fillField('input[name="formlogin[userid]"]', $_ENV['ALLUNITED_USERNAME']);
        $I->fillField('input[name="formlogin[password]"]', $_ENV['ALLUNITED_PASSWORD']);

        $I->click('Inloggen');

        $I->see('Hulp nodig?', '#block12424');
    }

    public function goToAddNewsArticlePage(AcceptanceTester $I)
    {
        $I->executeJS('sendForm("NULL", "cms_articles", "cmsarticle", "3", "", "where~page=\'Nieuws\'");');

        $I->see('Artikelinfo', '#block6420');
    }

    public function seeArticleInDatabase(AcceptanceTester $I)
    {
        $newsItem = $I->grabFromDatabase('la_news', 'kop', ['id' => 2506]);

        $I->assertEquals('Hollen met Han 2017', $newsItem);
    }
}
