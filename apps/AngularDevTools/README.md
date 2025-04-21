# PoppySeedPetsTools

A web version of this tool can be found at http://tools.poppyseedpets.com

## Developing PoppySeedPetsTools

1. **Install Node and npm.** You can download an installer for them here: https://nodejs.org/en/download/
2. **Install Angular.** Open up a command prompt anywhere, and run `npm install -g @angular/cli` (If you're wondering what this weird command I'm asking you to run is, you can learn more about Angular here: https://angular.io/cli)
3. **Download PoppySeedPetsTools.** If you're viewing this on GitHub, there's a green button up above the file list called "Clone or download". Click that, and choose "Download ZIP". Extract the ZIP somewhere on your computer. (Or, if you're familiar with git, use that, and you don't need me to tell you how :P)
   * Confirm that you know where everything is! Open the PoppySeedPetsTools project folder; if you can see files called things like ".gitignore", "angular.json", etc, then you're in the right place.
4. **Download PoppySeedPetsTools's Dependencies.** Open a command prompt in the PoppySeedPetsTools project folder, and run `npm install`. It'll take a little bit. (If you're curious, the files it's downloading are all placed into the "node_modules" folder within PoppySeedPetsTools, so if you decide to delete PoppySeedPetsTools, all this stuff you're downloading right now will be deleted, too!)
5. **Run PoppySeedPetsTools.** Run `ng serve` (from inside the PoppySeedPetsTools project directory, which you should still have open from the previous step).
6. **Open PoppySeedPetsTools.** In your browser, visit http://localhost:4200

### Building

1. **Install Electron Packager.** Run `npm install electron-packager -g`
2. Run `npm run build:win` (from inside the PoppySeedPetsTools project directory, of course).
