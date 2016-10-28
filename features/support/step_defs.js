module.exports = function() {

    this.Given(/^I have visited AllUnited/, function () {
        browser.url('https://pr01.allunited.nl');
    });

    this.Given(/^I am on AllUnited/, function () {
    });

    this.When(/^I fill input "([^"]*)" with club$/, function (inputId) {
        browser.setValue('input#'+inputId, process.env.CLUB);
    });

    this.When(/^I fill input "([^"]*)" with gebruikersnaam/, function (inputId) {
        browser.setValue('input#'+inputId, process.env.GEBRUIKERSNAAM);
    });

    this.When(/^I fill input "([^"]*)" with wachtwoord/, function (inputId) {
        browser.setValue('input#'+inputId, process.env.WACHTWOORD);
    });

    this.When(/^I fill input "([^"]*)" with "([^"]*)"$/, function (inputId, value) {
        browser.setValue('input#'+inputId, value);
    });

    this.When(/^I click submitbutton "([^"]*)"/, function(text) {
        browser.click("input[value='"+text+"'");
    });

    this.When(/^I go to all news articles$/, function () {
        // todo: doesnt work
        browser.element("#backofficeMenu li a").click("=CMS");
    });

    this.Then(/^I see "([^"]*)" in element "([^"]*)"$/, function (text, inputId) {
        var result = browser.getText("#"+inputId, 2000);
        expect(result).toMatch(text);
    });

    this.Then(/^I don't see "([^"]*)" in element "([^"]*)"$/, function (text, inputId) {
        var result = browser.getText("#"+inputId, 2000);
        expect(result).not.toMatch(text);
    });

};