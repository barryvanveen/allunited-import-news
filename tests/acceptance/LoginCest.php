<?php

class LoginCest
{
    protected $authors;

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

    public function fillArticles(AcceptanceTester $I)
    {
        $this->insertArticle($I, 2505);
    }

    /**
     * @param AcceptanceTester $I
     * @param int $articleId
     */
    protected function insertArticle(AcceptanceTester $I, $articleId)
    {
        $this->getAuthorNameToUserIdCouplings();

        $newsItem = $I->grabRowFromDatabase($_ENV['DB_TABLE'], ['id' => $articleId]);

        $I->fillField('input[name="article[datefrom]"]', $this->formatDatestring($newsItem['datum_begin']));
        $I->fillField('input[name="article[timefrom]"]', '00:00');

        $I->fillField('input[name="article[dateto]"]', $this->formatDatestring($newsItem['datum_eind']));
        $I->fillField('input[name="article[timeto]"]', '23:59');

        $I->fillField('input[name="article[title]"]', $newsItem['kop']);

        $I->executeJS('$("#id-6419-7742").attr("readonly", false)');
        $I->fillField('input[name="article[contactid]"]', $this->translateAuthorToUserId($newsItem['user']));

        $I->click('a#id-6419-6640_code');
        $I->waitForElement('#mce_39_ifr', 5);
        $I->switchToIFrame($I->findField('#mce_39_ifr'));
        $I->waitForElement('textarea#htmlSource', 5);
        $I->fillField('textarea#htmlSource', $this->formatEditorContent($newsItem['inleiding']));
        $I->click('Bijwerken');

        $I->switchToIFrame();

        $I->scrollTo('#block6422');

        $I->click('a#id-6419-6643_code');
        $I->waitForElement('#mce_41_ifr', 5);
        $I->switchToIFrame($I->findField('#mce_41_ifr'));
        $I->waitForElement('textarea#htmlSource', 5);
        $I->fillField('textarea#htmlSource', $this->formatEditorContent($newsItem['tekst']));
        $I->click('Bijwerken');

        $I->switchToIFrame();

        $I->scrollTo('#block6644');

        if (!empty($newsItem['img_url'])) {
            $image_path = $this->downloadImage($newsItem['img_url']);

            $I->attachFile($I->findField('input[type=file]'), $image_path);
        }

        // todo: add image
        // todo: add link(s)

        //$I->click("Bewaren");

        $I->wait(5);
    }

    protected function getAuthorNameToUserIdCouplings()
    {
        $raw = explode(',', $_ENV['AUTHOR_NAME_TO_USER_ID_COUPLING']);

        $this->authors = [];

        foreach($raw as $row) {
            $tmp = explode('=', $row);

            $this->authors[$tmp[0]] = $tmp[1];
        }
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
        if (!isset($this->authors[$user])) {
            throw new Exception("Unknown author $user! Please extend translateAuthorToUserId function.");
        }

        return $this->authors[$user];
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
        $replacements = [];

        for($i=123; $i<255; $i++) {
            $replacements[chr($i)] = '&#'.$i.';';
        }

        $content = html_entity_decode($content);

        $content = str_replace(array_keys($replacements), array_values($replacements), $content);

        codecept_debug($content);

        return $content;
    }

    /**
     * @param $img_url
     *
     * @return string
     */
    protected function downloadImage($img_url)
    {
        if (strpos($img_url, 'http://') === false && strpos($img_url, 'https://') === false) {
            $img_url = 'http://www.leidenatletiek.nl/'.$img_url;
        }

        $filename  = '/images/' . basename($img_url);

        $output = codecept_data_dir() . $filename;

        if (file_exists($output)) {
            return $output;
        }

        file_put_contents($output, file_get_contents($img_url));

        return $filename;
    }
}
