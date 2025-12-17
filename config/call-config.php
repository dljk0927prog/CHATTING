<?php
/**
 * 通话系统配置文件
 * 集中管理所有通话相关的配置参数
 */

$config = [
    // WebRTC配置
    'webrtc' => [
        'ice_servers' => [
            ['urls' => 'stun:stun.l.google.com:19302'],
            ['urls' => 'stun:stun1.l.google.com:19302'],
            ['urls' => 'stun:stun2.l.google.com:19302']
        ],
        'ice_candidate_pool_size' => 10,
        'bundle_policy' => 'max-bundle',
        'rtcp_mux_policy' => 'require'
    ],
    
    // 信令服务器配置
    'signaling' => [
        'server_url' => '/CHATTING/signaling-server.php',
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
