// Array to store command history
let path_public = 'public/';
let stylesheets = path_public + 'css/';
let commandHistory = [];
let historyIndex = -1;
let currentDirectory = ''; // Variable to store the current directory
let isPasswordPrompt = false; // Flag to track if password prompt is active
let userPassword = ''; // Variable to store the password
let usernameForLogon = ''; // Variable to store the username for logon
let usernameForNewUser = ''; // Variable to store the username for new user
let isUsernamePrompt = false;
let currentCommand = '';
let commands = [];
let files = [];
let folders = [];
let cmd = '';
let currentSongIndex = 0;
let audio;

// Event listener for when the DOM content is loaded
$(document).ready(function() {
    // Load the saved theme when the document is ready
    loadSavedTheme();

    // Load the saved term when the document is ready
    loadSavedTermMode();

    //Check commands available
    autoHelp();

    // Check if 'boot' command has been sent during the current session
    if (!localStorage.getItem('boot')) {

        setTimeout(function() {
            sendCommand('boot', ''); // Send 'boot' command
        }, 500);
        
        setTimeout(function() {
            localStorage.setItem('boot', true); // Set 'boot' flag in sessionStorage
            clearTerminal();
            sendCommand('main', '');
        }, 20000);
    } else {

        setTimeout(function() {
            sendCommand('main', ''); // Send 'welcome' command if boot has been set
            $('#connection').load('connection');
        }, 500);
    }
});
