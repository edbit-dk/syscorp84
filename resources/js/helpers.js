// Function to handle redirect
function handleResponse(response, timeout = 2500) {

    // Rens responsen for eventuelle skjulte tegn
    const cleanResponse = response.trim();

    if (cleanResponse.startsWith('TRYING')) {
        setTimeout(function() { redirectTo('', true) }, timeout);
    }

    if (cleanResponse.includes('SUCCESS: LOGGING OUT')) {
        setTimeout(function() { redirectTo('', true) }, timeout);
    }

    if (cleanResponse.includes('SUCCESS: SECURITY ACCESS CODE SEQUENCE ACCEPTED')) {
        setTimeout(function() { redirectTo('', true) }, timeout);
    }

    if (cleanResponse.includes('SUCCESS: LOGON ACCEPTED')) {
        sessionStorage.setItem('host', true);
        setTimeout(function() { redirectTo('', true) }, timeout);
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
