# How to Contribute

Wanna propose changes to Poppy Seed Pets?

Rad!

The Poppy Seed Pets code is hosted on GitHub, so you will need a GitHub account; once you've got that, you can create a change proposal a couple different ways!

## But first, real quick:

Please don't be personally offended if I take a long time to review your changes (I have a full time job that is 0% related to PSP), ask you to change your proposed changes, or end up rejecting your changes!

Refer to the following for some of the guiding principles behind Poppy Seed Pets development:
* [Design Goals](https://poppyseedpets.com/poppyopedia/designGoal) (on poppyseedpets.com)
* [Architecture Decisions](Architecture%20Decisions.md)

## The super-simple way, for super-simple changes

If you want to make a super-simple change, like a text change:

1. Search for the thing you want to change, and click the pencil icon 
   ![screenshot of GitHub UI](How%20to%20Contribute%20-%20Find%20a%20File.png)
   * If the pencil icon is not clickable, you probably need to go back to https://github.com/BenMakesGames/PoppySeedPetsAPI, and click through the folders to get to the file you want. GitHub is weird like that. 
2. Make your change, and click "Commit Changes..."
   ![screenshot of GitHub UI](How%20to%20Contribute%20-%20Make%20a%20Change.png)
3. Fill in the pop-up form with a human-readable summary of your changes
   ![screenshot of GitHub UI](How%20to%20Contribute%20-%20Create%20PR%20Step%201.png)
4. Click another button ("Create pull request"). The first one apparently wasn't good enough.
   ![screenshot of GitHub UI](How%20to%20Contribute%20-%20Create%20PR%20Step%202.png)
5. Wait for your changes to be approved; in the meanwhile, if the automated bug-checker finds a bug, fix it!
   ![screenshot of GitHub UI](How%20to%20Contribute%20-%20Fix%20Tests%20and%20Wait.png)

## The "I'm a real web dev" way, for complex changes

Requirements:
* PHP 8+ experience
* GitHub & general git experience
* An editor with strong PHP support ([PHPStorm](https://www.jetbrains.com/phpstorm/) is excellent, though it's only free for 30 days)

Depending on how deep you're going, you might also need to be familiar with:
* Symfony
* REST(ish) APIs
* MySQL/MariaDB
* YAML
* Redis
* Linux
* Apache

Refer to:
1. [Installing and Running](Installing%20and%20Running.md) for information on how to run Poppy Seed Pets locally
2. [Fork a repository](https://docs.github.com/en/pull-requests/collaborating-with-pull-requests/working-with-forks/fork-a-repo) (on GitHub Docs)
3. [Creating a pull request from a fork](https://docs.github.com/en/pull-requests/collaborating-with-pull-requests/proposing-changes-to-your-work-with-pull-requests/creating-a-pull-request-from-a-fork) (on GitHub Docs)
