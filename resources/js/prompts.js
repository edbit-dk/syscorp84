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
        loadText("ERROR: USERNAME REQUIRED");
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

    if (response.startsWith("ERROR: ACCESS DENIED") || response.startsWith("WARNING")) {
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
