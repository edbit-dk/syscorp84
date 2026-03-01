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

