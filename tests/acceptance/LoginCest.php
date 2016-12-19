<?php

class LoginCest
{
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

    public function insertArticle(AcceptanceTester $I)
    {
        $newsItem = $I->grabRowFromDatabase('la_news', ['id' => 2506]);

        $I->fillField('input[name="article[datefrom]"]', $this->formatDatestring($newsItem['datum_begin']));
        $I->fillField('input[name="article[timefrom]"]', '00:00');

        $I->fillField('input[name="article[dateto]"]', $this->formatDatestring($newsItem['datum_eind']));
        $I->fillField('input[name="article[timeto]"]', '23:59');

        $I->fillField('input[name="article[title]"]', $newsItem['kop']);

        $I->executeJS('$("#id-6419-7742").attr("readonly", false)');
        $I->fillField('input[name="article[contactid]"]', $this->translateAuthorToUserId($newsItem['user']));

        // todo: make findField a public mathod on custom WebDriver module

        $I->click('a#id-6419-6640_code');
        $I->switchToIFrame($I->findField('#mce_39_ifr'));
        $I->fillField('textarea#htmlSource', $this->formatEditorContent($newsItem['inleiding']));
        $I->click('Bijwerken');

        $I->scrollTo('#block6422');

        $I->click('a#id-6419-6643_code');
        $I->switchToIFrame($I->findField('#mce_41_ifr'));
        $I->fillField('textarea#htmlSource', $content = $this->formatEditorContent($newsItem['tekst']));
        $I->click('Bijwerken');

        $I->switchToIFrame();

        // todo: add image
        // todo: add link(s)

        //$I->click("Bewaren");

        $I->wait(5);
    }

    /**
     * Format a custom datestring into a formated date
     *
     * @param string $datestring for example "20161231"
     *
     * @return string
     */
    protected function formatDatestring($datestring)
    {
        $date = strtotime($datestring);

        return date("d-m-Y", $date);
    }

    /**
     * Translate an author name into a user id.
     *
     * @param string $user
     *
     * @return int
     *
     * @throws Exception
     */
    protected function translateAuthorToUserId($user)
    {
        // todo: write this function with some configuration file

        switch($user) {
            default:
                throw new Exception("Unknown author $user! Please extend translateAuthorToUserId function.");
        }
    }

    /**
     * Format htmlentities string into html string.
     *
     * @param string $content
     *
     * @return string
     */
    protected function formatEditorContent($content)
    {
        return html_entity_decode($content);
    }
}
