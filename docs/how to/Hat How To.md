# How to Turn an Item into a Hat


## Choose an item to make into a hat

1. Find an item that isn't already a hat, that you think should be a hat:

    In Poppy Seed Pets, go to the [Poppyopedia](https://poppyseedpets.com/poppyopedia/item), to the right of the magnifying glass click More, under the hat icon click No, Search

2. Search poppyopedia for the item you want, but don't open the actual entry yet

## Save the item image

1. Save the item's image - will need later, for previewing what the item will look like as a hat

2. Right click the image, Save Image As, it should be a .svg (Scalable Vector Graphics) file

## Find the item id

1. Look up the item id of the chosen item

    In Firefox, press the F12 key on your keyboard to bring up the developer tools window

2. In the dev tools menu toward the top of the dev tools window, click the Network tab

3. In your browser window, open the poppyopedia entry for the item you want to look up

4. In the dev tools window, only a few lines should appear. Under the File column, there should be a line with the item name, look at the line beneath that one, that says something like 200 GET api.poppyseedpets.com and then a number. That number under the File column is the item id (e.g. Gnome's Favor is item id 1173). If the dev tools window gets cluttered with other stuff for some reason, you can click the trash can icon in the top left corner to clear it, then go back to the browser window and reload the specific item's Poppyopedia page

![Screenshot of Firefox dev tools](Hat%20How%20To%20-%20item%20id%20lookup%201.png)


Or to look up a bunch of item ids at once, in Firefox press F12 to open dev tools, then go to the page of the poppyopedia that has the item(s) you want. In the dev tools window, Network tab, click on the line that says item?page=<page#here>, then in the right panel, click XHR, then Response, and expand data and response, it will list the item ids for the items on that page. In the example below (taken from one of Ben's discord posts), the item id for "Alien" Camera is 1276.

![Screenshot of Firefox dev tools](Hat%20How%20To%20-%20item%20id%20lookup%202.png)

## Preview Hat Settings

Use the Make a Hat preview tool to figure out the best hat settings for that item

1. Go to [https://tools.poppyseedpets.com/newHat](https://tools.poppyseedpets.com/newHat)

2. Click Browse and upload the item image you saved earlier, this will show a preview of what the hat will look like on all the different PSP species. I found it easiest to adjust the Head Scale first, but here are what the settings do, in the order they appear:

    a. Toggle dark mode - preview how the hat will appear for those who use dark themes

    b. Head X & Head Y: toward the top left, where the original uploaded image is shown in the red square, you can click anywhere within that red square to adjust the Head X and Head Y values (how far left/right or up/down the hat will appear on each pet, value will be between 0 and 1)

    c. Head Angle - use the slider to adjust the hat's rotation (+/- 180 degrees)

    d. Head Angle Fixed? - this checkbox toggles whether the hat rotation is the same for all species

    e. Head Scale - adjust the slider until the hat is the size you want (0.25-1.5)

8. Once you figure out your favorite settings for that item, save the values listed in the right panel, we'll need them later

9. Figure out what the next hat id number should be - Ben might change the way IDs are generated so this might totally change, for now here's how I've been manually looking up the next id, if someone else knows a better way please feel free to edit this / please let me know

    a. In GitHub go to the PoppySeedPetsAPI repository, then toward the top right of the screen, in the search bar type INSERT INTO item_hat

    b. In the left panel, filter the search results so that it only shows the most recent migrations (choose the path with the most recent year/month), this should show the most recent updates that involved making hats

    c. Look for the highest number associated with the "INSERT INTO item_hat" line, for example in the screenshot below, Evilberries were made into hat #287, so the next person would use hat id number 288

![Screenshot of Hat ID search](Hat%20How%20To%20-%20hat%20id.png)

## Copy existing template

1. In GitHub, go to the PoppySeedPetsAPI repository and look at the migration that [turned Gnome's Favor into a hat](https://github.com/BenMakesGames/PoppySeedPetsAPI/blob/main/migrations/2025/04/Version20250424043857.php). Can copy/paste this as a template for a new migration, and just edit the necessary bits. 

2. Toward the top right, click the button to copy the raw file, this copies all of the code from that migration to your clipboard. 

![Screenshot of GitHub UI](Hat%20How%20To%20-%20copy%20raw%20file.png)

## Create a Migration

1. In the left navigation panel, browse to the current migrations folder (or from the main page of the repository, click Code, click migrations folder, click on the current year folder, then month). You should see a list of the most recent updates and their last commit messages/dates.

    [Link to April 2025 migrations](https://github.com/BenMakesGames/PoppySeedPetsAPI/tree/main/migrations/2025/04)

2. Toward the top right, click Add file, create a new file

    Toward the top where it has an empty field that says "Name your file," type the filename in the format VersionYYYYMMDDHHMMSS.php, which is just a timestamp where YYYY is the year, MM is the month, etc. For example, if it is April 20, 2025, 12:55AM PSP (UTC), we would put Version20250420005500, followed by the .php file extension (e.g. Version20250421005124.php). 

3. Click where it says "Enter file contents here" and paste the code (ctrl+V). The parts we'll need to edit are circled in green.

![Screenshot of code](Hat%20How%20To%20-%20where%20to%20edit.png)

4. Update the version

    On line 21, to the right of where it says "final class Version...," update the version to match the filename (can copy/paste from your filename from step 2) 

5. Update the comments 

    a. On lines 30 and 36 that start with //, change "Gnome's Favor" to the item name of the item you're making into a hat 

6. Update the hat values
    
    On line 32 (that starts with INSERT INTO 'item_hat'), we need to edit the numbers to the right of the word VALUES. These values need to match their corresponding fields in order.  
    The first number after the word VALUES is the new hat id, change the number 285 to the one you figured out earlier (I'm assuming Ben will let us know if we need to change it when he reviews) 

    If you're copying the Gnome's Favor hat template, the rest of the fields should already be in the same order as the Make a Hat preview tool, just edit the numbers to match the values you chose from the Make a Hat website

7. Set the desired item as the new hat 

    On line 37:
    a. to the right of **SET 'hat_id' =**  change the number to the same hat id you used in line 32
    b. to the right of **WHERE 'item'.'id' =** change the number to the item number of the item you are making into a hat (the item id you got from Poppyopedia / Firefox dev tools) 
    
## Commit the Migration in GitHub
1. When you're ready to submit, toward the top right corner, click Commit changes. 
    
    Enter a description of the change you are requesting, e.g. Make Gnome's Favor into a hat. Follow the prompts to submit & create a pull request (for more details, can see [How to Contribute (skip to step 2 for how to Commit changes)](https://github.com/BenMakesGames/PoppySeedPetsAPI/blob/c2d36945f222f324c1826ec6d564ef090809d5bd/docs/How%20to%20Contribute.md))
