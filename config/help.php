<?php

return [
    [
        'cmd' => 'help', 
        'input' => '[cmd|page]', 
        'info' => 'shows info about command',
        'is_user' => 1,
        'is_host' => 1,
        'is_visitor' => 1,
        'is_guest' => 1
    ],
    [
        'cmd' => 'uplink', 
        'input' => '<access code>', 
        'info' => 'uplink to network',
        'is_user' => 0,
        'is_host' => 0,
        'is_visitor' => 1,
        'is_guest' => 0
    ],
    [
        'cmd' => 'newuser', 
        'input' => '<username>', 
        'info' => 'create account',
        'is_user' => 0,
        'is_host' => 0,
        'is_visitor' => 1,
        'is_guest' => 0
    ],
    [
        'cmd' => 'logon', 
        'input' => '<username>', 
        'info' => 'login (alias: logon) ',
        'is_user' => 0,
        'is_host' => 0,
        'is_visitor' => 1,
        'is_guest' => 1
    ],
    [
        'cmd' => 'logout', 
        'input' => NULL, 
        'info' => 'leave host/node (alias: exit, dc, quit, close) ',
        'is_user' => 1,
        'is_host' => 1,
        'is_visitor' => 0,
        'is_guest' => 1
    ],
    [
        'cmd' => 'ver', 
        'input' => NULL, 
        'info' => 'HackNet OS version',
        'is_user' => 1,
        'is_host' => 1,
        'is_visitor' => 1,
        'is_guest' => 1
    ],
    [
        'cmd' => 'music', 
        'input' => '<start|stop|next>', 
        'info' => 'play 80s music',
        'is_user' => 1,
        'is_host' => 1,
        'is_visitor' => 1,
        'is_guest' => 1
    ],
    [
        'cmd' => 'color', 
        'input' => '<green|white|yellow|blue>', 
        'info' => 'terminal color',
        'is_user' => 1,
        'is_host' => 1,
        'is_visitor' => 1,
        'is_guest' => 1
    ],
    [
        'cmd' => 'term', 
        'input' => '<DEC-VT100|IBM-3270>', 
        'info' => 'change terminal mode',
        'is_user' => 1,
        'is_host' => 1,
        'is_visitor' => 1,
        'is_guest' => 1
    ],
    [
        'cmd' => 'scan', 
        'input' => NULL, 
        'info' => 'list connected nodes (alias: scan)',
        'is_user' => 1,
        'is_host' => 1,
        'is_visitor' => 0,
        'is_guest' => 0
    ],
    [
        'cmd' => 'connect', 
        'input' => '<host>', 
        'info' => 'connect to host (alias: connect)',
        'is_user' => 1,
        'is_host' => 1,
        'is_visitor' => 0,
        'is_guest' => 0
    ],
    [
        'cmd' => 'mail', 
        'input' => '[send|read|list|delete]', 
        'info' => "email user: -s <subject> <user>[@host] < <body> \n
        list emails: [-l] \n
        read email: [-r] <ID> \n
        sent emails: -s \n
        sent email: -s <ID> \n
        delete email: -d <ID>",
        'is_user' => 1,
        'is_host' => 1,
        'is_visitor' => 0,
        'is_guest' => 0
    ],
    [
        'cmd' => 'cd', 
        'input' => '[folder]', 
        'info' => 'change directory',
        'is_user' => 0,
        'is_host' => 1,
        'is_visitor' => 0,
        'is_guest' => 1
    ],
    [
        'cmd' => 'ls', 
        'input' => NULL, 
        'info' => 'list files on host (alias: dir)',
        'is_user' => 0,
        'is_host' => 1,
        'is_visitor' => 0,
        'is_guest' => 1
    ],
    [
        'cmd' => 'cat', 
        'input' => '<filename>', 
        'info' => 'print contents of file (alias: more, open)',
        'is_user' => 0,
        'is_host' => 1,
        'is_visitor' => 0,
        'is_guest' => 1
    ],
    [
        'cmd' => 'debug', 
        'input' => '[dump]', 
        'info' => 'run memory dump on accounts.f',
        'is_user' => 1,
        'is_host' => 0,
        'is_visitor' => 0,
        'is_guest' => 1
    ],
];