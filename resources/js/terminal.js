// Function to load text into terminal one letter at a time with 80-character line breaks
function loadText(text) {
    let delay = 5 + Math.random() * 10;
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

// Scrol ned når input feltet får fokus
$('#command-input').on('focus', function() {
    setTimeout(scrollToBottom, 300); // Lille delay så tastaturet kan nå at poppe op
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
    } else if (connectionText.includes('SYNCORP/NET')) {
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

