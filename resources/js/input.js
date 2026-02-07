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
            loadText("EMTER PASSWORD:");
            isUsernamePrompt = false;
            isPasswordPrompt = true;
            $('#command-input').attr('type', 'password');
        } else if (currentCommand === 'login' || currentCommand === 'logon') {
            usernameForLogon = input;
            loadText("EMTER PASSWORD:");
            isUsernamePrompt = false;
            isPasswordPrompt = true;
            $('#command-input').attr('type', 'password');
        }
    } else {
        loadText("ERROR: WRONG USERNAME");
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
    loadText("EMTER PASSWORD:");
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
}