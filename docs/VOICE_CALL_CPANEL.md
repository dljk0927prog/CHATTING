# 语音/视频通话在 cPanel 上的说明

## 为什么 localhost 能听见对方，cPanel 上听不见？

常见原因有三类：**必须用 HTTPS**、**STUN 被拦**、**路径/域名不同**。

---

### 1. 必须使用 HTTPS（最常见）

- 浏览器规定：**麦克风/摄像头**（`getUserMedia`）只能在「安全上下文」下使用。  
- **localhost** 被当作安全上下文，所以本地可以正常用。  
- 在 cPanel 上若用 **http://你的域名**，多数浏览器会拒绝麦克风，或只允许一次后不再给权限，导致**听不见对方声音**。

**处理方式：**

- 在 cPanel 为站点开启 **SSL（HTTPS）**，用 **https://你的域名** 访问聊天和通话页面。  
- 开启后刷新页面，在浏览器里重新允许麦克风权限（如有提示）。

---

### 2. 自己电脑开两端能听见，手机与电脑互通听不见（跨设备/跨网络）

- **原因**：同一台电脑上两个标签页在同一网络、甚至本机回环，STUN 或直连即可。  
  手机和电脑是**不同设备、不同网络**，很多情况下需要 **TURN 中继** 才能建立音视频通道；仅靠 STUN 无法穿透时，就会「接通但听不见」。
- **处理方式**：在 `config/call-config.php` 中配置 **TURN 服务器**（见下方「TURN 配置」）。

---

### 3. STUN 被主机或网络拦截

- 公网/跨网络通话依赖 **STUN** 做地址发现；若主机或防火墙拦截了到 STUN 的访问，ICE 可能失败，表现为「已接通但听不见」。  
- 代码里已使用**多个 STUN 服务器**；若仍听不见，需要同时配置 **TURN**（见下）。

---

### 4. 路径/域名不一致

- 若在 cPanel 上站点不在 `/Chat_System` 下，可在配置中定义常量 **BASE_URL** 覆盖默认路径。

---

## TURN 配置（手机与电脑互通必读）

### 方式一：Metered Open Relay（推荐，无需手填 username/credential）

Metered 的 **username 和 credential 不是手动填的**，而是通过 **API Key** 由页面自动拉取。

1. **注册并获取 API Key**
   - 打开：https://dashboard.metered.ca/signup?tool=turnserver  
   - 注册免费账号并登录。
   - 在 **Dashboard** 里找到你的 **API Key**，以及你的 **应用名称**（App name，例如 `myapp`，用于拼成域名 `myapp.metered.live`）。

2. **拼出凭证接口地址**
   - 接口格式：`https://你的应用名.metered.live/api/v1/turn/credentials?apiKey=你的API_KEY`
   - 例如应用名为 `myapp`、API Key 为 `abc123`，则填：
     `https://myapp.metered.live/api/v1/turn/credentials?apiKey=abc123`

3. **写入本项目的配置**
   - 打开 **`config/call-config.php`**，找到 `turn.metered_api_url`。
   - 将上面的完整 URL 填进去，例如：
     ```php
     'turn' => [
         'metered_api_url' => 'https://myapp.metered.live/api/v1/turn/credentials?apiKey=你的API_KEY',
     ],
     ```
   - 保存后，通话页会在建立连接前自动请求该地址，拿到包含 TURN（含 username/credential）的 `iceServers`，**无需再手填 username 和 credential**。

4. **页面上 “Credentials” / “Static Auth” 是什么？**
   - **Credentials**：指的就是“用 API Key 访问上述接口”拿到的那一串配置（接口返回里已包含 username 和 credential）。
   - **Static Auth**：是给 Nextcloud Talk 等用“静态密钥”的软件用的，本聊天项目用 **API Key + 上述 URL** 即可。

### 方式二：自建 TURN 或其它静态凭证服务

若使用自建 coturn 或其它提供**固定 username + password** 的 TURN 服务，可在 **`config/call-config.php`** 的 `webrtc.ice_servers` 里直接追加，例如：

```php
[
    'urls' => 'turn:你的TURN域名:443',
    'username' => '你的用户名',
    'credential' => '你的密码'
],
[
    'urls' => 'turn:你的TURN域名:443?transport=tcp',
    'username' => '你的用户名',
    'credential' => '你的密码'
],
```

---

## 已做的代码改动（便于 cPanel / 跨设备）

1. **多组 STUN**：已配置多组 STUN，提高公网连通率。  
2. **可配置 TURN**：`config/call-config.php` 中可配置 TURN，`simple_call.php` 会读取并传给 WebRTC。  
3. **HTTPS 提示**：非 HTTPS 时页面上会提示使用 HTTPS。  
4. **baseUrl 自动推断**：可根据请求路径自动计算接口前缀。

---

## 建议自检清单（cPanel / 跨设备）

- [ ] 使用 **https://** 访问站点。  
- [ ] 浏览器已允许该站点使用麦克风。  
- [ ] **手机与电脑互通**：已在 `config/call-config.php` 中配置 TURN 并填入有效凭证。  
- [ ] 若仍听不见：打开开发者工具 → Console，查看是否有 `getUserMedia`、ICE 或 TURN 相关报错。
