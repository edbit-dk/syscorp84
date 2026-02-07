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
        }, 10000);
    } else {

        setTimeout(function() {
            sendCommand('main', ''); // Send 'welcome' command if boot has been set
            $('#connection').load('connection');
        }, 500);
    }
});
// Event listener for handling keydown events
$('#command-input').keydown(function(e) {
    if (e.key === 'Enter') {
        e.preventDefault(); // Prevent default tab behavior
        if (isPasswordPrompt) {
            handlePasswordPrompt(); // Handle password prompt on Enter key press
        } else {
            handleUserInput(); // Handle user input on Enter key press
        }
    } else if (e.key === 'ArrowUp') {
        // Navigate command history on ArrowUp key press
        if (historyIndex > 0) {
            historyIndex--;
            $('#command-input').val(commandHistory[historyIndex]);
        }
    } else if (e.key === 'ArrowDown') {
        // Navigate command history on ArrowDown key press
        if (historyIndex < commandHistory.length - 1) {
            historyIndex++;
            $('#command-input').val(commandHistory[historyIndex]);
        } else {
            // Clear input when reaching the end of history
            historyIndex = commandHistory.length;
            $('#command-input').val('');
        }
    } else if (e.key === 'Tab') {
        e.preventDefault(); // Prevent default tab behavior
        autocomplete(); // Call autocomplete function on Tab key press
    }
});

// Event listener for the play button
document.getElementById('play-button').addEventListener('click', toggleMusic);

// Function to handle redirect
function handleResponse(response, timeout = 1000) {

    if (response.startsWith("Trying")) {
        setTimeout(function() {
            redirectTo('');
        }, timeout);
    }

    if (response.startsWith("Security")) {
        setTimeout(function() {
            redirectTo('');
        }, timeout);
    }

    if (['Login accepted', 'Access accepted'].includes(response)) {
        setTimeout(function() {
            sessionStorage.setItem('host', true);
            redirectTo('');
        }, timeout);
    }

}

// Function to redirect to a specific query string
function redirectTo(url, reload = false) {
    if(reload) {
        return window.location.href = url;
    }
    //clearTerminal();
    sendCommand('main', '');
    //$('#connection').load('connection');
}

// Function to validate the string pattern
function isUplinkCode(input) {
    // Check if the input is 27 characters long and matches the alphanumeric pattern (allowing dashes)
    const pattern = /^[A-Za-z0-9\-]{27}$/;

    // Test the input against the pattern
    return pattern.test(input);
}

// Utility function to find the common prefix of an array of strings
function findCommonPrefix(strings) {
    if (!strings.length) return '';
    let prefix = strings[0];
    for (let i = 1; i < strings.length; i++) {
        while (!strings[i].startsWith(prefix)) {
            prefix = prefix.slice(0, -1);
            if (!prefix) break;
        }
    }
    return prefix;
}
// Function to handle user input
function handleUserInput() {
    let input = $('#command-input').val().trim();
    if (input === '' && !(isPasswordPrompt || isUsernamePrompt)) return;

    loadText($('#connection').text() + ' ' + input);
    commandHistory.push(input);
    localStorage.setItem('history', commandHistory);
    historyIndex = commandHistory.length;
    localStorage.setItem('index', historyIndex  );
    $('#command-input').val('');

    if (input === '?') {
        input = 'help';
    }

    if (isUplinkCode(input)) {
        sessionStorage.setItem('uplink', true);
        input = 'uplink ' + input;
    }

    handleMusicCommands(input);
    
    if (handleUserPrompts(input)) {
        return;
    }

    const parts = input.split(' ');
    const command = parts[0].toLowerCase();
    const args = parts.slice(1).join(' ');

    handleCommands(command, args);
}

function handleMusicCommands(input) {
    if (input === 'music start') {
        console.log('music start');
        document.getElementById('play-button').click();
        $('#command-input').val('');
        return true;
    }

    if (input === 'music stop') {
        console.log('music stop');
        if (audio && !audio.paused) {
            document.getElementById('play-button').click();
        }
        $('#command-input').val('');
        return true;
    }

    if (input === 'music next') {
        console.log('music next');
        if (audio) {
            playNextSong();
        } else {
            console.log('Use "music start" first.');
        }
        $('#command-input').val('');
        return true;
    }

    return false;
}

function handleUserPrompts(input) {
    if (isUsernamePrompt) {
        handleUsernamePrompt(input);
        return true;
    }

    if (isPasswordPrompt) {
        handlePasswordPrompt();
        return true;
    }

    return false;
}

function handleUsernamePrompt(input) {
    if (input) {
        if (currentCommand === 'newuser') {
            usernameForNewUser = input;
            loadText("EMTER PASSWORD NOW");
            isUsernamePrompt = false;
            isPasswordPrompt = true;
            $('#command-input').attr('type', 'password');
        } else if (currentCommand === 'login' || currentCommand === 'logon') {
            usernameForLogon = input;
            loadText("EMTER PASSWORD NOW");
            isUsernamePrompt = false;
            isPasswordPrompt = true;
            $('#command-input').attr('type', 'password');
        }
    } else {
        loadText("Wrong username");
    }
}

function handleCommands(command, args) {
    if (['newuser', 'logon', 'login'].includes(command) && !sessionStorage.getItem('uplink')) {
        loadText("UPLINK REQUIRED");
        return;
    }

    if (['logon', 'login', 'newuser'].includes(command) && sessionStorage.getItem('auth') && !sessionStorage.getItem('host')) {
        loadText("LOGOUT REQUIRED");
        return;
    }

    switch (command) {
        case 'term':
            setTermMode(args);
            break;
        case 'reset':
            clearTerminal();
            sendCommand(command, args);
            break;
        case 'clear':
        case 'cls':
            clearTerminal();
            break;
        case 'uplink':
            sessionStorage.setItem('uplink', true);
            sendCommand(command, args);
            break;
        case 'newuser':
            handleNewUserCommand(args);
            break;
        case 'logon':
        case 'login':
            handleLogonCommand(command, args);
            break;
        case 'logout':
        case 'close':
        case 'logoff':
        case 'quit':
        case 'dc':
        case 'restart':
        case 'exit':
        case 'reboot':
        case 'halt':
        case 'halt restart':
            handleExitCommands(command, args);
            break;
        case 'color':
            setTheme(args);
            break;
        default:
            sendCommand(command, args);
            break;
    }
}

function handleNewUserCommand(args) {
    if (args) {
        handleNewUser(args);
    } else {
        promptForUsername('newuser');
    }
}

function handleLogonCommand(command, args) {
    if (args) {
        promptForPassword(command, args);
    } else {
        promptForUsername(command);
    }
}

function handleExitCommands(command, args) {
    sendCommand(command, args)
        .then(response => {
            if (!response.includes("ERROR")) {
                handleSuccessfulExit(command);
            }
        })
        .catch(err => {
            console.error("Command failed", err);
        });
}

function promptForUsername(command) {
    loadText("LOGON");
    isUsernamePrompt = true;
    currentCommand = command;
    $('#command-input').attr('type', 'text');
}

function promptForPassword(command, username) {
    usernameForLogon = username;
    loadText("ENTER PASSWORD NOW");
    isUsernamePrompt = false;
    isPasswordPrompt = true;
    currentCommand = command;
    $('#command-input').attr('type', 'password');
}

function handleSuccessfulExit(command) {
    setTimeout(() => {
        if (sessionStorage.getItem('host')) {
            sessionStorage.removeItem('host');
        }

        if (['boot'].includes(command)) {
            localStorage.removeItem('boot');
        }
        redirectTo('', false);
    }, 1000);
}// Function to send command to server
function sendCommand(command, data, queryString = '') {
    const query = window.location.search;
    const route = command.split(" ")[0];

    return new Promise((resolve, reject) => {
        $.ajax({
            type: 'GET',
            url: route.toLowerCase() + queryString,
            data: {
                data: data,
                query: query
            },
            success: function(response) {
                loadSavedTheme();
                
                $('#connection').load('connection');
                
                if (isPasswordPrompt) {
                   handlePasswordPromptResponse(response); // Handle password prompt response
                } else {
                    loadText(response); // Load response text into terminal
                    handleResponse(response); // Handle redirect if needed
                }
                resolve(response); // Resolve the promise with the response
            },
            error: function(err) {
                reject(err); // Reject the promise in case of an error
            }
        });
    });
}

// Function to append command to terminal window
function appendCommand(command) {
    const commandElement = $('<div>').addClass('command-prompt').html(command);
    $('#terminal').append(commandElement);
    scrollToBottom(); 
}

// Fetch commands from the server based on the user's status
function autoHelp() {
    fetch('api?key=system&get=auto')
        .then(response => response.json())
        .then(data => {
            if (Array.isArray(data)) {
                commands = data.filter(item => typeof item === 'string'); // Keep only strings
            } else {
                console.error('Invalid commands data:', data);
            }
        })
        .catch(error => console.error('Error fetching commands:', error));
}


function autocomplete() {
    const inputField = $('#command-input');
    const currentText = inputField.val();
    const lastWordMatch = currentText.match(/(^|\s)(\S*)$/); // Matches the last word or the beginning of the input

    if (!lastWordMatch) return; // Exit if there's no match

    const prefix = lastWordMatch[2]; // Extract the current word being typed

    // Find commands that match the current input
    const matches = commands.filter(cmd => typeof cmd === 'string' && cmd.startsWith(prefix));

    if (matches.length === 1) {
        // If only one match, autocomplete the input
        inputField.val(currentText.slice(0, -prefix.length) + matches[0]);
    } else if (matches.length > 1) {
        // If multiple matches, find the common prefix
        const commonPrefix = findCommonPrefix(matches);
        if (commonPrefix.length > prefix.length) {
            // Autocomplete the input to the common prefix
            inputField.val(currentText.slice(0, -prefix.length) + commonPrefix);
        } else {
            // Show all matches in the terminal as suggestions
            loadText(`${matches.join(' ')}`);
        }
    } else {
        // No matches
        loadText('');
    }
}



// Function to handle the LOGON/LOGIN command
function handleLogon(username) {
    if (!sessionStorage.getItem('uplink')) {
        loadText("UPLINK REQUIRED.");
        return;
    }

    if (!usernameForLogon && !username) {
        loadText("Username:");
        isUsernamePrompt = true;
        $('#command-input').attr('type', 'text'); // Switch input to text for username
        return;
    }

    if (isPasswordPrompt) return; // Already prompting for password, do nothing
    isPasswordPrompt = true;
    usernameForLogon = username;
    loadText("Password:");
    $('#command-input').attr('type', 'password'); // Change input to password
}

// Function to handle the NEWUSER command
function handleNewUser(username) {
    if (!sessionStorage.getItem('uplink')) {
        loadText("UPLINK REQUIRED.");
        return;
    }
    
    if (!username) {
        // This shouldn't happen since args should be checked in handleUserInput()
        loadText("USERNAME REQUIRED.");
        return;
    } else {
        // Assign the provided username
        usernameForNewUser = username;
        currentCommand = 'newuser';
    }

    // Proceed to password prompt
    isPasswordPrompt = true;
    loadText("Password:");
    $('#command-input').attr('type', 'password');
}

// Function to handle password prompt
function handlePasswordPrompt() {
    let password = $('#command-input').val(); // Capture the password input, allow it to be empty
    if (!password) password = ""; // Explicitly set to an empty string if blank
    userPassword = password;

    // Determine the current command and send the appropriate request
    if (currentCommand === 'logon' || currentCommand === 'login') {
        sendCommand(currentCommand, usernameForLogon + ' ' + userPassword);
        usernameForLogon = ''; // Clear the username for logon
    } else if (currentCommand === 'newuser') {
        sendCommand('newuser', usernameForNewUser + ' ' + userPassword);
        usernameForNewUser = ''; // Clear the username for new user creation
    }

    // Reset prompt state and input type
    isPasswordPrompt = false;
    $('#command-input').attr('type', 'text').val('');
}

// Function to handle password prompt response
function handlePasswordPromptResponse(response) {
    if (usernameForLogon) {
        sendCommand('logon', usernameForLogon + ' ' + (userPassword || ""));
    } else if (usernameForNewUser) {
        sendCommand('newuser', usernameForNewUser + ' ' + (userPassword || ""));
    }

    if (response.startsWith("*** ACCESS DENIED ***") || response.startsWith("WARNING")) {
        loadText(response);
        isPasswordPrompt = false;
        $('#command-input').attr('type', 'text');
    } else if (response.startsWith("Connecting...")) {
        loadText(response);
        setTimeout(function() {
            sessionStorage.setItem('auth', true);
            clearTerminal();
            sendCommand('main', '');
        }, 2500);
    }
    $('#command-input').val('');
}
// Function to load text into terminal one letter at a time with 80-character line breaks
function loadText(text) {
    let delay = 1;
    let currentIndex = 0;
    let lineCharCount = 0; // Track character count per line
    const preContainer = $('<pre>');

    $('#terminal').append(preContainer); // Append the container to the terminal

    function displayNextLetter() {
        if (currentIndex < text.length) {
            const char = text[currentIndex];

            // Insert a line break if character count exceeds 80 and ensure it doesn’t break mid-word
            if (lineCharCount >= 100 && char !== '\n') {
                const lastChar = preContainer.text().slice(-1);
                if (lastChar !== ' ' && lastChar !== '\n') {
                    // Move back to the last space if possible
                    const textSoFar = preContainer.text();
                    const lastSpaceIndex = textSoFar.lastIndexOf(' ');
                    if (lastSpaceIndex > 0) {
                        preContainer.text(textSoFar.slice(0, lastSpaceIndex) + '\n' + textSoFar.slice(lastSpaceIndex + 1));
                        lineCharCount = textSoFar.slice(lastSpaceIndex + 1).length;
                    } else {
                        preContainer.append('\n');
                        lineCharCount = 0;
                    }
                } else {
                    preContainer.append('\n');
                    lineCharCount = 0;
                }
            }

            preContainer.append(char);
            currentIndex++;

            if (char === '\n') {
                lineCharCount = 0;
            } else {
                lineCharCount++;
            }

            scrollToBottom();
            setTimeout(displayNextLetter, delay);
        } else {
            $('#command-input').focus();
        }
    }

    displayNextLetter();
}


// Function to scroll the terminal window to the bottom
function scrollToBottom() {
    const wrapper = document.getElementById('terminal-wrapper');
    if (wrapper) {
        // Vi scroller wrapperen til dens maksimale højde
        wrapper.scrollTo({
            top: wrapper.scrollHeight,
            behavior: 'smooth' // Gør det lækkert og flydende som i spillene
        });
    }
}

// Function to clear terminal
function clearTerminal() {
    $('#terminal').empty();
}

// Function to load the saved theme from localStorage
function loadSavedTheme() {
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme) {
        setTheme(savedTheme);
    }
}

// Function to set text and background color
function setTheme(color) {
    const colors = {
        green: "#0f0",
        white: "#EAF7F9",
        yellow: "#ffb642",
        blue: "#0CD7CF",
    };

    const defaultColor = "green";
    const themeColor = colors[color] || colors[defaultColor];

    // Remove any existing theme style tag
    $('#theme-style').remove();

    // Create a new <style> tag and append it to the <head>
    const styleTag = `<style id="theme-style"> * { color: ${themeColor} !important; } </style>`;
    $('head').append(styleTag);

    // Store the color name (not the hex code) for persistence
    localStorage.setItem('theme', color);
}

// Function to set terminal font
function setTermMode(mode) {
    const terms = ['DEC-VT100', 'IBM-3270'];

    if (terms.includes(mode)) {
        $("#page").attr('class', mode);
        localStorage.setItem('term', mode);
        sendCommand('term', mode);
    } else {
        loadText('UNKNOWN TERMINAL TYPE');
    }
}

// Function to load the saved theme from localStorage
function loadSavedTermMode() {
    const savedTerm = localStorage.getItem('term');
    if (savedTerm) {
        setTermMode(savedTerm);
    }
}

// Function to create the audio element only after user interaction
function initializeAudio() {
  if (!audio) {
    audio = new Audio(playlist[currentSongIndex]);
    audio.loop = false; // Disable looping for queuing purposes

    audio.addEventListener('ended', handleAudioEnded);
    console.log('Audio element initialized.');
  }
}

// Function to play the next song
function playNextSong() {
  if (playlist.length === 0) {
    console.log('Playlist is empty.');
    return;
  }

  currentSongIndex = (currentSongIndex + 1) % playlist.length; // Move to the next song, wrap around if needed
  audio.src = playlist[currentSongIndex];
  audio.play()
    .then(() => {
      console.log(`Playing next song: ${playlist[currentSongIndex]}`);
      document.getElementById('play-button').textContent = 'MUSIC STOP';
    })
    .catch(error => {
      console.error('Playback failed:', error);
      alert('Audio playback failed. Please try again or interact with the page.');
    });
}

// Function to handle when the current song ends
function handleAudioEnded() {
  playNextSong(); // Automatically play the next song when the current one ends
}

// Function to toggle play/pause for music
function toggleMusic() {
  initializeAudio();

  if (audio.paused) {
    audio.play().then(() => {
      console.log('Audio started playing.');
      document.getElementById('play-button').textContent = 'MUSIC STOP'; // Update button text
    }).catch(error => {
      console.error('Playback failed:', error);
      alert('Audio playback failed. Please try again or interact with the page.');
    });
  } else {
    audio.pause();
    console.log('Audio paused.');
    document.getElementById('play-button').textContent = 'MUSIC PLAY'; // Update button text
  }
}


