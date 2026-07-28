<?php
/**
 * 通话系统配置文件
 * 集中管理所有通话相关的配置参数
 *
 * 跨设备/跨网络（如 PC 与手机）互通需要 TURN 中继。
 *
 * 【Metered Open Relay 免费 TURN】不需要 username/credential 手填：
 * 1. 打开 https://dashboard.metered.ca/signup?tool=turnserver 注册免费账号
 * 2. 登录后在 Dashboard 里找到你的 API Key 和 App 名称（如 yourappname）
 * 3. 下方 turn.metered_api_url 填：https://你的应用名.metered.live/api/v1/turn/credentials?apiKey=你的API_KEY
 *    页面会通过该接口自动获取包含 username/credential 的 iceServers，无需手填
 *
 * 【自建 TURN / 其他服务】可在 webrtc.ice_servers 里直接写死 username 与 credential。
 */

$config = [
    // WebRTC 配置（STUN + 可选 TURN）
    'webrtc' => [
        'ice_servers' => [
            ['urls' => 'stun:stun.l.google.com:19302'],
            ['urls' => 'stun:stun1.l.google.com:19302'],
            ['urls' => 'stun:stun2.l.google.com:19302'],
            ['urls' => 'stun:stun.stunprotocol.org:3478'],
            // 若已从 Dashboard 拿到 Username/Password，可取消下面注释并替换为你的值（Metered 域名：global.relay.metered.ca）
            // ['urls' => 'turn:global.relay.metered.ca:80', 'username' => '你的Username', 'credential' => '你的Password'],
            // ['urls' => 'turn:global.relay.metered.ca:80?transport=tcp', 'username' => '你的Username', 'credential' => '你的Password'],
            // ['urls' => 'turn:global.relay.metered.ca:443', 'username' => '你的Username', 'credential' => '你的Password'],
            // ['urls' => 'turns:global.relay.metered.ca:443?transport=tcp', 'username' => '你的Username', 'credential' => '你的Password'],
        ],
        'ice_candidate_pool_size' => 10,
        'bundle_policy' => 'max-bundle',
        'rtcp_mux_policy' => 'require'
    ],

    // Metered Open Relay：填完整 URL 后，通话页会自动请求该接口获取 TURN（含 username/credential）
    'turn' => [
        'metered_api_url' => 'https://chattingsynergydljk.metered.live/api/v1/turn/credentials?apiKey=ec90b24b7f79ab932358283b53ceff4f3e42',  // 请把 YOUR_API_KEY_HERE 换成 Dashboard 里拿到的 API Key
    ],
    
    // 信令服务器配置
    'signaling' => [
        'server_url' => '/Chat_System/signaling-server.php',
        'reconnect_interval' => 3000,
        'max_reconnect_attempts' => 5,
        'heartbeat_interval' => 30000,
        'polling_interval' => 1000
    ],
    
    // 媒体配置
    'media' => [
        'audio' => [
            'echo_cancellation' => true,
            'noise_suppression' => true,
            'auto_gain_control' => true,
            'sample_rate' => 48000,
            'channel_count' => 1
        ],
        'video' => [
            'width' => ['ideal' => 1280, 'max' => 1920],
            'height' => ['ideal' => 720, 'max' => 1080],
            'frame_rate' => ['ideal' => 30, 'max' => 60],
            'facing_mode' => 'user'
        ]
    ],
    
    // 通话配置
    'call' => [
        'timeout' => 30000, // 30秒超时
        'max_duration' => 3600000, // 1小时最大通话时长
        'auto_answer_delay' => 0, // 自动接听延迟（0表示不自动接听）
        'ring_duration' => 30000 // 响铃持续时间
    ],
    
    // UI配置
    'ui' => [
        'theme' => 'dark',
        'show_debug_info' => false,
        'enable_animations' => true,
        'responsive_breakpoints' => [
            'mobile' => 768,
            'tablet' => 1024
        ]
    ],
    
    // 安全配置
    'security' => [
        'require_authentication' => true,
        'encrypt_signaling' => false, // 生产环境应设为true
        'validate_room_access' => true,
        'max_concurrent_calls' => 5
    ],
    
    // 日志配置
    'logging' => [
        'enabled' => true,
        'level' => 'info', // debug, info, warn, error
        'log_file' => 'logs/call_system.log'
    ]
];

// 返回配置数组
return $config;
