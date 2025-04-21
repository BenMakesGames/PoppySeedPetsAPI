const { app, BrowserWindow, Menu } = require('electron');

let win;

function createWindow() {
    // Create the browser window.
    win = new BrowserWindow({
        width: 920,
        height: 680,
        webPreferences: {
            nodeIntegration: true,
        }
    });

    Menu.setApplicationMenu(null);
    win.loadURL(`file://${__dirname}/dist/PoppySeedPetsTools/browser/index.html`);
    //win.openDevTools();

    //// uncomment below to open the DevTools.
    // win.webContents.openDevTools()

    // Event when the window is closed.
    win.on('closed', function() {
        win = null;
    });
}

// Create window on electron intialization
app.on('ready', createWindow);

// Quit when all windows are closed.
app.on('window-all-closed', function() {

    // On macOS specific close process
    if (process.platform !== 'darwin') {
        app.quit();
    }
});

app.on('activate', function() {
    // macOS specific close process
    if (win === null) {
        createWindow();
    }
});
