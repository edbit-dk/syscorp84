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

    // Gør det muligt at klikke på alle ord, der er genereret
    $('#terminal').on('click', '.terminal-word', function() {
        const clickedText = $(this).text();
        const $input = $('#command-input');
        
        $input.val(clickedText);
        $input.focus();
        
        // Valgfrit: Giv et lille visuelt "flash" når man klikker
        $(this).css('background-color', '#00ff00').delay(100).queue(function(next){
            $(this).css('background-color', '');
            next();
        });
    });

    // Check if 'boot' command has been sent during the current session
    if (!localStorage.getItem('boot')) {

        setTimeout(function() {
            sendCommand('boot', ''); // Send 'boot' command
        }, 500);
        
        setTimeout(function() {
            localStorage.setItem('boot', true); // Set 'boot' flag in sessionStorage
            clearTerminal();
            sendCommand('main', '');
        }, 30000);
    } else {

        setTimeout(function() {
            refreshConnection();
            themeConnection();
            sendCommand('main', ''); // Send 'welcome' command if boot has been set
            scrollToBottom();
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
function handleResponse(response, timeout = 2500) {

    // Rens responsen for eventuelle skjulte tegn
    const cleanResponse = response.trim();

    if (cleanResponse.startsWith('ACCESSING')) {
        setTimeout(function() { redirectTo('') }, timeout);
    }

    if (cleanResponse.includes('LOGGING OUT...')) {
        setTimeout(function() { redirectTo('') }, timeout);
    }

    if (cleanResponse.includes('SECURITY ACCESS CODE SEQUENCE ACCEPTED')) {
        setTimeout(function() { redirectTo('') }, timeout);
    }

    if (cleanResponse.includes('VERIFYING CREDENTIALS')) {
        sessionStorage.setItem('host', true);
        setTimeout(function() { redirectTo('') }, timeout);
    }

}

function refreshConnection() {
    $('#connection').load('connection', function() {
        themeConnection();
        scrollToBottom();
        console.log("Connection UI updated with theme.");
    });
}

// Function to redirect to a specific query string
function redirectTo(url, reload = false, timeout = 2500) {
    if(reload) {
        return window.location.href = url;
    }
    //clearTerminal();
    setTimeout(function() { 
        refreshConnection();
        sendCommand('main', ''); 
    }, timeout);}

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
        if (currentCommand === 'enroll') {
            usernameForNewUser = input;
            loadText("PASSWORD:");
            isUsernamePrompt = false;
            isPasswordPrompt = true;
            $('#command-input').attr('type', 'password');
        } else if (currentCommand === 'login' || currentCommand === 'logon') {
            usernameForLogon = input;
            loadText("PASSWORD:");
            isUsernamePrompt = false;
            isPasswordPrompt = true;
            $('#command-input').attr('type', 'password');
        }
    } else {
        loadText("ERROR: WRONG USERNAME");
    }
}

function handleCommands(command, args) {
    if (['enroll', 'logon', 'login'].includes(command) && !sessionStorage.getItem('uplink')) {
        loadText("ERROR: UPLINK REQUIRED");
        return;
    }

    if (['logon', 'login', 'enroll'].includes(command) && sessionStorage.getItem('auth') && !sessionStorage.getItem('host')) {
        loadText("ERROR: LOGOUT REQUIRED");
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
        case 'enroll':
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
        promptForUsername('enroll');
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
    loadText("USERNAME:");
    isUsernamePrompt = true;
    currentCommand = command;
    $('#command-input').attr('type', 'text');
}

function promptForPassword(command, username) {
    usernameForLogon = username;
    loadText("PASSWORD:");
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
        redirectTo('');
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
                
                refreshConnection();
                
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
                console.error('ERROR', data);
            }
        })
        .catch(error => console.error('ERROR', error));
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
        loadText("ERROR: UPLINK REQUIRED");
        return;
    }

    if (!usernameForLogon && !username) {
        loadText("USERNAME:");
        isUsernamePrompt = true;
        $('#command-input').attr('type', 'text'); // Switch input to text for username
        return;
    }

    if (isPasswordPrompt) return; // Already prompting for password, do nothing
    isPasswordPrompt = true;
    usernameForLogon = username;
    loadText("PASSWORD:");
    $('#command-input').attr('type', 'password'); // Change input to password
}

// Function to handle the NEWUSER command
function handleNewUser(username) {
    if (!sessionStorage.getItem('uplink')) {
        loadText("ERROR: UPLINK REQUIRED");
        return;
    }
    
    if (!username) {
        // This shouldn't happen since args should be checked in handleUserInput()
        loadText("ERROR: USERNAME REQUIRED!");
        return;
    } else {
        // Assign the provided username
        usernameForNewUser = username;
        currentCommand = 'enroll';
    }

    // Proceed to password prompt
    isPasswordPrompt = true;
    loadText("PASSWORD:");
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
    } else if (currentCommand === 'enroll') {
        sendCommand('enroll', usernameForNewUser + ' ' + userPassword);
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
        sendCommand('enroll', usernameForNewUser + ' ' + (userPassword || ""));
    }

    if (response.startsWith("ERROR: ACCESS DENIED!") || response.startsWith("WARNING")) {
        loadText(response);
        isPasswordPrompt = false;
        $('#command-input').attr('type', 'text');
    } else if (response.startsWith("CONNECTING...")) {
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
    let delay = 15; // En fast hastighed føles mere som en maskine end Math.random()
    let currentIndex = 0;
    const preContainer = $('<pre>');
    $('#terminal').append(preContainer);

    let currentWordSpan = null;

    function displayNextLetter() {
        if (currentIndex < text.length) {
            const char = text[currentIndex];
            const isWordChar = /[a-zA-Z0-9]/.test(char);

            if (isWordChar) {
                if (!currentWordSpan) {
                    currentWordSpan = $('<span class="terminal-word"></span>');
                    preContainer.append(currentWordSpan);
                }
                currentWordSpan.append(char);
            } else {
                preContainer.append(char);
                currentWordSpan = null; 
            }

            // OPTIMERING: Scroll kun for hvert 3. tegn for at spare kræfter,
            // men det ser stadig flydende ud for brugeren.
            if (currentIndex % 3 === 0) {
                scrollToBottom();
            }

            currentIndex++;
            setTimeout(displayNextLetter, delay);
        } else {
            scrollToBottom(); // Sikr at vi lander helt i bunden til sidst
            $('#command-input').focus();
        }
    }
    displayNextLetter();
}


// Function to scroll the terminal window to the bottom
function scrollToBottom() {
    const wrapper = document.getElementById('terminal-wrapper');
    if (wrapper) {
        // Direkte og kontant hop til bunden - præcis som hardware fra 1980
        wrapper.scrollTop = wrapper.scrollHeight;
    }
}

// Scrol ned når input feltet får fokus
$('#command-input').on('focus', function() {
    setTimeout(scrollToBottom, 100); // Lille delay så tastaturet kan nå at poppe op
});

// Scrol ned hver gang brugeren skriver noget
$('#command-input').on('input', scrollToBottom);

// Function to clear terminal
function clearTerminal() {
    $('#terminal').empty();
}

// Function to load the saved theme from localStorage
function loadSavedTheme() {
    const savedTheme = localStorage.getItem('theme') || 'DEFAULT';
    setTheme(savedTheme);
}

function themeConnection() {
    const connectionText = $('#connection').text().toUpperCase();
    
    // Vi tjekker hvilke ord der findes i strengen [@XXXX-NET]
    if (connectionText.includes('DATA/NET')) {
        setTheme('IDM');
    } else if (connectionText.includes('DEFCON/NET')) {
        setTheme('DFC');
    } else if (connectionText.includes('SYSCORP/NET')) {
        setTheme('SYN');
    } else if (connectionText.includes('GEC/NET')) {
        setTheme('GEC');
    } else if (connectionText.includes('FALLHACK/NET')) {
        setTheme('FAK');
    } else {
        setTheme('DEFAULT'); // Standard grøn
    }
}

function setTheme(org) {
    const orgs = {
        'IDM': { color: "#EAF7F9", bg: "#0d1112" }, // Hvid/Grålig
        'SYN': { color: "#0CD7CF", bg: "#051112" }, // Blålig
        'DFC': { color: "#FF3131", bg: "#120505" }, // Rødlig
        'GEC': { color: "#c3a747", bg: "#121005" }, // Gul
        'FAK' : { color: "#FF8C00", bg: "#120a05", noise: "0.45" }, // Amber
        'DEFAULT': { color: "#2DFD8B", bg: "#0b1a13" } // Grøn
    };

    const theme = orgs[org] || orgs['DEFAULT'];
    const wrapper = $('#terminal-wrapper');

    // Opdater CSS variabler
    document.documentElement.style.setProperty('--fallout-green', theme.color);
    document.documentElement.style.setProperty('--fallout-bg', theme.bg);
    document.documentElement.style.setProperty('--noise-opacity', theme.noise);

    // Opdater variablerne på :root
    document.documentElement.style.setProperty('--fallout-green', theme.color);
    document.documentElement.style.setProperty('--fallout-bg', theme.bg);

    // Tilføj eller fjern glitch-effekten
    if (org === 'FAK') {
        wrapper.addClass('hacker-glitch');
    } else {
        wrapper.removeClass('hacker-glitch');
    }

    localStorage.setItem('theme', org);
}

// Function to set terminal font
function setTermMode(mode) {
    const terms = ['DEC-VT100', 'IBM-3270'];

    if (terms.includes(mode)) {
        $("#page").attr('class', mode);
        localStorage.setItem('term', mode);
        sendCommand('term', mode);
    } else {
        loadText('ERROR: UNKNOWN TERMINAL');
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


