<?php

class FillNewsCest
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

    /**
     * @param AcceptanceTester $I
     */
    public function fillNews(AcceptanceTester $I)
    {
        $articles = $I->grabRowsFromDatabase(
            $_ENV['DB_TABLE'],
            [
                'datum_begin >=' => '20160101',
                'categ' => '6',
            ]
        );

        foreach ($articles as $article) {
            if (!empty($article['datum_eind']) && $this->dateInPast($article['datum_eind'])) {
                codecept_debug("Einddatum verstreken, niet importeren.");
                continue;
            }

            $this->goToAddNewsArticlePage($I);

            $this->insertArticle($I, $article);
        }
    }

    protected function goToAddNewsArticlePage(AcceptanceTester $I)
    {
        $I->executeJS('sendForm("NULL", "cms_articles", "cmsarticle", "3", "", "where~page=\'Nieuws\'");');

        $I->see('Artikelinfo', '#block6420');
    }

    /**
     * @param AcceptanceTester $I
     * @param array $article
     */
    protected function insertArticle(AcceptanceTester $I, $article)
    {
        $this->getAuthorNameToUserIdCouplings();

        $I->fillField('input[name="article[datefrom]"]', $this->formatDatestring($article['datum_begin']));
        $I->fillField('input[name="article[timefrom]"]', '00:00');

        if (!empty($article['datum_eind'])) {
            $I->fillField('input[name="article[dateto]"]', $this->formatDatestring($article['datum_eind']));
        } else {
            $I->fillField('input[name="article[dateto]"]', '31-12-9999');
        }
        $I->fillField('input[name="article[timeto]"]', '23:59');

        $I->fillField('input[name="article[title]"]', $article['kop']);

        $I->executeJS('$("#id-6419-7742").attr("readonly", false)');
        $I->fillField('input[name="article[contactid]"]', $this->translateAuthorToUserId($article['user']));

        if (!empty($article['inleiding'])) {
            $I->scrollTo('#block6421');

            $I->click('a#id-6419-6640_code');
            $I->waitForElement('#mce_39_ifr', 5);
            $I->switchToIFrame($I->findField('#mce_39_ifr'));
            $I->waitForElement('textarea#htmlSource', 5);
            $I->fillField('textarea#htmlSource', $this->formatEditorContent($article['inleiding']));
            $I->click('Bijwerken');

            $I->switchToIFrame();
        }

        if (!empty($article['tekst'])) {
            $I->scrollTo('#block6422');

            $iframeId = empty($article['inleiding']) ? '#mce_39_ifr' : '#mce_41_ifr';

            $I->click('a#id-6419-6643_code');
            $I->waitForElement($iframeId, 5);
            $I->switchToIFrame($I->findField($iframeId));
            $I->waitForElement('textarea#htmlSource', 5);

            $tekst = $article['tekst'];

            if (!empty($article['link']) && !empty($article['linknaam'])) {
                if (strpos($article['link'], 'http://www.leidenatletiek.nl') === false &&
                    strpos($article['link'], 'http://leidenatletiek.nl') === false &&
                    strpos($article['link'], 'kdc/index.php') !== 0 &&
                    strpos($article['link'], 'inschrijven_pupillen.php') !== 0 &&
                    strpos($article['link'], 'inschrijven.php') !== 0 &&
                    strpos($article['link'], 'index.php') !== 0 &&
                    strpos($article['link'], 'prikbord.php') !== 0 &&
                    strpos($article['link'], 'wedstrijd.php') !== 0 &&
                    strpos($article['link'], 'uitslag.php') !== 0
                ) {
                    $tekst .=
                        '<p>'.
                        '<a href="'.$article['link'].'" title="'.$article['linknaam'].'" target="_blank">'.
                        $article['linknaam'].
                        '</a>'.
                        '</p>';
                }
            }

            $I->fillField('textarea#htmlSource', $this->formatEditorContent($tekst));
            $I->click('Bijwerken');

            $I->switchToIFrame();
        }

        if (!empty($article['img_url'])) {
            $I->scrollTo('#block6644');

            $image_path = $this->downloadImage($article['img_url']);

            $I->attachFile($I->findField('input[type=file]'), $image_path);

            $I->click('Uploaden');

            $I->wait(5);
        }

        $I->selectOption("#id-6419-6429", "Gereed");

        $I->click("Publiceren");

        $I->wait(3);
    }

    protected function getAuthorNameToUserIdCouplings()
    {
        $raw = explode(',', $_ENV['AUTHOR_NAME_TO_USER_ID_COUPLING']);

        $this->authors = [];

        foreach ($raw as $row) {
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
     * @param string $datestring
     *
     * @return bool
     */
    protected function dateInPast($datestring)
    {
        $datetime = new DateTime($datestring);

        $today = new DateTime("today");

        return ($datetime < $today);
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

        for ($i = 123; $i < 255; $i++) {
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
        if (strpos($img_url, 'http://') === false && strpos($img_url, 'https://') === false && strpos($img_url, '_data') === false) {
            $img_url = 'http://www.leidenatletiek.nl/'.$img_url;
        }

        $filename = '/images/'.basename($img_url);

        $output = codecept_data_dir().$filename;

        if (!file_exists($output)) {
            file_put_contents($output, file_get_contents($img_url));
        }

        return $filename;
    }

}