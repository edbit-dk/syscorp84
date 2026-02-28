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
