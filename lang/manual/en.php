<?php
/**
 * User manual content — English
 */
return [
    'page_title' => 'User Manual',
    'subtitle' => 'Feature overview & how-to guide',
    'back_home' => 'Back to Home',
    'back_dashboard' => 'Back to Dashboard',
    'toc_title' => 'Contents',
    'updated' => 'This manual follows your current interface language',

    'sections' => [
        [
            'id' => 'overview',
            'title' => '1. Feature Overview',
            'intro' => 'Chat System is a real-time platform for private chats, group chats, forums, and voice/video calls. Languages: Chinese, English, Bahasa Melayu.',
            'preview_items' => [
                ['icon' => '💬', 'name' => 'Private & Group Chat', 'desc' => 'Send text, images, video, files, and voice notes. Quote, forward, pin, and favorite messages.'],
                ['icon' => '👥', 'name' => 'Friends & Requests', 'desc' => 'Add friends by username; accept or reject friend and forum invites.'],
                ['icon' => '🏢', 'name' => 'Group Management', 'desc' => 'Create groups, invite friends, assign admins, or disband a group.'],
                ['icon' => '📢', 'name' => 'Forum Plaza', 'desc' => 'Create or join forums; post, reply, and manage members and join requests.'],
                ['icon' => '📞', 'name' => 'Voice / Video Calls', 'desc' => 'One-to-one or group calls with mute and camera controls.'],
                ['icon' => '⭐', 'name' => 'Favorites & Profile', 'desc' => 'Save important messages; manage avatar, account, and password.'],
            ],
        ],
        [
            'id' => 'start',
            'title' => '2. Getting Started',
            'blocks' => [
                [
                    'heading' => 'Register',
                    'steps' => [
                        'On the welcome page, click Register.',
                        'Enter a username (min. 3 characters), email, password (min. 6 characters), and confirm password.',
                        'After success, go back to Login and sign in with your new account.',
                    ],
                ],
                [
                    'heading' => 'Login',
                    'steps' => [
                        'Enter username or email, and password.',
                        'Click Login to open the Dashboard.',
                    ],
                ],
                [
                    'heading' => 'Switch language',
                    'steps' => [
                        'Use the language switcher on the welcome, login, or register pages.',
                        'After login: avatar → Settings → choose Chinese / English / Bahasa Melayu.',
                        'The choice is saved immediately and this manual updates with it.',
                    ],
                ],
                [
                    'heading' => 'Logout',
                    'steps' => [
                        'Avatar → Logout → confirm. You return to the welcome page and go offline.',
                    ],
                ],
            ],
        ],
        [
            'id' => 'dashboard',
            'title' => '3. Dashboard Overview',
            'intro' => 'The left sidebar is your hub; the main area opens when you enter a chat or forum.',
            'blocks' => [
                [
                    'heading' => 'Sidebar tabs',
                    'list' => [
                        'Chats — private and group rooms, search, unread badges, pins.',
                        'Friends — friend list and online status; add friends.',
                        'Groups — group list; create groups or open settings.',
                        'Forums — joined forums; create or join forums.',
                        'Requests — friend requests and forum invites; accept or reject.',
                    ],
                ],
                [
                    'heading' => 'Avatar menu',
                    'list' => [
                        'Profile — avatar, username, email, password.',
                        'Favorites — saved messages and media.',
                        'Settings — language only.',
                        'Blocked Users — manage blocked people.',
                        'Logout.',
                    ],
                ],
                [
                    'heading' => 'Chat row menu (⋮)',
                    'list' => [
                        'Pin / Unpin',
                        'Delete Chat (remove from list)',
                        'Details (room info)',
                    ],
                ],
                [
                    'heading' => 'Mobile tips',
                    'tips' => [
                        'Open the sidebar with the menu button.',
                        'Swipe to open or close the sidebar.',
                    ],
                ],
            ],
        ],
        [
            'id' => 'friends',
            'title' => '4. Friends',
            'blocks' => [
                [
                    'heading' => 'Add a friend',
                    'steps' => [
                        'Open Friends → Add Friend.',
                        'Enter their username; a request is sent.',
                        'They accept under Requests to become friends.',
                    ],
                ],
                [
                    'heading' => 'Start a private chat',
                    'steps' => [
                        'Click a friend in the list to open or create a private room.',
                    ],
                ],
                [
                    'heading' => 'Nickname, block, delete',
                    'steps' => [
                        'In a private room, open Details (ℹ️).',
                        'Set/edit nickname, block, or delete the friend.',
                        'Unblock anytime under Blocked Users.',
                    ],
                ],
            ],
        ],
        [
            'id' => 'chat',
            'title' => '5. Private Chat & Message Actions',
            'intro' => 'Rooms support multiple message types and a bubble action bar.',
            'blocks' => [
                [
                    'heading' => 'Send messages',
                    'list' => [
                        'Text — type and Send (or Enter).',
                        'Files / images / video — attach files (multi-select collage supported).',
                        'Voice — hold the record button, release to send.',
                    ],
                ],
                [
                    'heading' => 'Bubble bar (hover or long-press)',
                    'list' => [
                        'Favorite — save to Favorites.',
                        'Pin / Unpin — pin important messages at the top.',
                        'Quote — reply to a message.',
                        'Forward / Share — send to friends or groups.',
                        'Edit — your own text messages only.',
                        'Recall — your text within about 2 minutes.',
                        'Delete — remove the message on your side.',
                    ],
                ],
                [
                    'heading' => 'Room details',
                    'list' => [
                        'View room info and clear chat history.',
                        'In private chats: nickname, block, or delete friend.',
                    ],
                ],
            ],
        ],
        [
            'id' => 'groups',
            'title' => '6. Groups',
            'blocks' => [
                [
                    'heading' => 'Create & enter',
                    'steps' => [
                        'Groups tab → Create Group → enter a name.',
                        'Open a group to chat; messaging matches private chat and shows sender names.',
                    ],
                ],
                [
                    'heading' => 'Group settings by role',
                    'table' => [
                        'headers' => ['Action', 'Owner', 'Admin', 'Member'],
                        'rows' => [
                            ['Edit name / avatar', '✓', '✓', '—'],
                            ['Invite / kick members', '✓', '✓', '—'],
                            ['Promote / demote admin', '✓', '✓', '—'],
                            ['Clear group history', '✓', '—', '—'],
                            ['Disband group', '✓', '—', '—'],
                            ['Chat & calls', '✓', '✓', '✓'],
                        ],
                    ],
                    'tips' => [
                        'Badges: Owner 👑, Admin ⚡, Member 👤.',
                    ],
                ],
            ],
        ],
        [
            'id' => 'calls',
            'title' => '7. Voice & Video Calls',
            'blocks' => [
                [
                    'heading' => 'Start a call',
                    'steps' => [
                        'Private room: use Voice Call or Video Call in the header.',
                        'Group room: start a group voice or video call the same way.',
                    ],
                ],
                [
                    'heading' => 'Answer & controls',
                    'list' => [
                        'Incoming calls: Answer or Decline.',
                        'During a call: mute, toggle video, hang up.',
                        'Allow camera/microphone permissions in the browser.',
                        'HTTPS and proper STUN/TURN setup are recommended for reliability.',
                    ],
                ],
            ],
        ],
        [
            'id' => 'forums',
            'title' => '8. Forums',
            'blocks' => [
                [
                    'heading' => 'Create & join',
                    'steps' => [
                        'Forums → Create Forum: name and optional description.',
                        'Join Forum opens the plaza; filter All / Joined / Available / Pending.',
                        'Public forums can be joined directly; private ones need invite or approval.',
                    ],
                ],
                [
                    'heading' => 'Posts & interaction',
                    'list' => [
                        'Use Quick Post or browse latest posts.',
                        'Open a post to view media and reply; authors can edit or delete their posts.',
                        'Pinned posts are clearly marked.',
                    ],
                ],
                [
                    'heading' => 'Forum settings (creator / admin)',
                    'list' => [
                        'Edit name, description, max members, public/private.',
                        'Change avatar; invite friends; approve or reject join requests.',
                        'Promote/demote admins; remove members.',
                        'Members can leave; creators must Delete Forum (cannot leave).',
                    ],
                ],
            ],
        ],
        [
            'id' => 'favorites',
            'title' => '9. Favorites',
            'blocks' => [
                [
                    'heading' => 'Using favorites',
                    'steps' => [
                        'Tap Favorite on a message bubble.',
                        'Avatar menu → Favorites to browse them.',
                        'Filter by All / Images / Videos / Voices / Files; preview or delete.',
                    ],
                ],
            ],
        ],
        [
            'id' => 'profile',
            'title' => '10. Profile & Blocked Users',
            'blocks' => [
                [
                    'heading' => 'Profile',
                    'list' => [
                        'Click the avatar to upload a new one.',
                        'Edit username and email.',
                        'Change password with current, new, and confirm fields.',
                        'View status, registration time, user ID, and last login.',
                    ],
                ],
                [
                    'heading' => 'Blocked users',
                    'steps' => [
                        'Avatar → Blocked Users.',
                        'Click Unblock to restore access.',
                    ],
                ],
            ],
        ],
        [
            'id' => 'faq',
            'title' => '11. FAQ',
            'faq' => [
                [
                    'q' => 'Messages are not appearing live?',
                    'a' => 'Rooms refresh via polling. Check your network or reload; the sidebar updates about every 2 seconds.',
                ],
                [
                    'q' => 'Cannot recall a message?',
                    'a' => 'Only your own text messages within about 2 minutes can be recalled. After that, use Delete.',
                ],
                [
                    'q' => 'Call will not connect?',
                    'a' => 'Allow camera/mic permissions, prefer HTTPS, and ensure STUN/TURN is configured on the server.',
                ],
                [
                    'q' => 'Forgot password?',
                    'a' => 'Self-service reset is not available yet. Ask a system administrator to reset your account.',
                ],
            ],
        ],
    ],
];
