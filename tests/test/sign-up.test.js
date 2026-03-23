const puppeteer = require('puppeteer');
const { expect } = require('chai');
const { v4: uuidv4 } = require('uuid');

describe('Test signup', function() {
    this.timeout(20000);

    let browser;
    let page;

    before(async () => {
        browser = await puppeteer.launch({
            args: ["--ignore-certificate-errors"]
        });
        page = await browser.newPage();
        await page.goto('https://localhost:4200');
    });

    after(async () => {
        await browser.close();
    });

    it('sign up button should work', async () => {
        const registerButton = await page.waitForSelector('a[href="/register"]');

        await registerButton.click();

        await page.waitForSelector('[data-test-step="1"]');
    });

    it('can complete introduction dialog', async () => {
        const firstDialogOption = await page.waitForSelector('main .dialog-choices > li:first-child > button');

        await firstDialogOption.click();

        //await page.waitForSelector('[data-test-step="2"]');
    });

    it('can enter resident name', async () => {
        await page.focus('input#name');

        await page.keyboard.type('Test User');

        const nextButton = await page.waitForSelector('[data-test-step="2"] button[type="submit"]');

        await nextButton.click();

        //await page.waitForSelector('[data-test-step="3"]');
    });

    it('can select pet', async () => {
        const nextButton = await page.waitForSelector('[data-test-step="3"] button[type="submit"]');

        await nextButton.click();

        //await page.waitForSelector('[data-test-step="4"]');
    });

    it('can enter pet name', async () => {
        await page.focus('input#petName');

        await page.keyboard.type('Test Pet');

        const nextButton = await page.waitForSelector('[data-test-step="4"] button[type="submit"]');

        await nextButton.click();

        //await page.waitForSelector('[data-test-step="5"]');
    });

    it('can complete signup', async () => {
        const emailAddress = uuidv4() + '@test.com';

        await page.focus('input#email');
        await page.keyboard.type(emailAddress);

        await page.focus('input#passphrase');
        await page.keyboard.type(emailAddress);

        // agree to all the things:
        const checkBoxes = await page.$$('[data-test-step="5"] label.radio-checkbox');

        for(let checkBox of checkBoxes)
            await checkBox.click();

        // next!
        const nextButton = await page.waitForSelector('[data-test-step="5"] button[type="submit"]');

        await nextButton.click();
    });

    it('is logged in, at home', async () => {
        await page.waitForSelector('app-loading-throbber[text="Loading pets"]');
    });
});
