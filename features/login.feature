@watch
Feature: Log into AllUnited

  As a user
  I want to log into AllUnited
  So I can fill news items

  Scenario: Log into AllUnited
    Given I have visited AllUnited
    When I fill input "id-33-265" with club
    And I fill input "id-33-30" with gebruikersnaam
    And I fill input "id-33-32" with wachtwoord
    And I click submitbutton "Inloggen"
    Then I see "Hulp nodig?" in element "block12424"
    And I don't see "Blablabla" in element "block12424"

  Scenario: List news articles
    Given I am on AllUnited
    When I go to all news articles
    Then I see "Snelle 10 km voor Vincent Mensen" in element "blockBody6526"