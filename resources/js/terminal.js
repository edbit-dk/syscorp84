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

