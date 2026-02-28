// Function to send command to server
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



