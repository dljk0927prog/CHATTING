<?php
require_once MODEL_PATH . '/User.php';

class AuthController {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    // 显示登录页面
    public function login() {
        if ($this->isLoggedIn()) {
            $this->redirect('/dashboard');
            return;
        }
        
        $this->render('auth/login');
    }
    
    // 处理登录请求
    public function loginProcess() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/auth/login');
            return;
        }
        
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($username) || empty($password)) {
            $this->render('auth/login', ['error' => '请填写用户名和密码']);
            return;
        }
        
        $result = $this->userModel->login($username, $password);
        
        if ($result['success']) {
            $_SESSION['user_id'] = $result['user']['id'];
            $_SESSION['username'] = $result['user']['username'];
            $_SESSION['user_avatar'] = $result['user']['avatar'];
            $this->redirect('/dashboard');
        } else {
            $this->render('auth/login', ['error' => $result['message']]);
        }
    }
    
    // 显示注册页面
    public function register() {
        if ($this->isLoggedIn()) {
            $this->redirect('/dashboard');
            return;
        }
        
        $this->render('auth/register');
    }
    
    // 处理注册请求
    public function registerProcess() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/auth/register');
            return;
        }
        
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // 验证输入
        $errors = [];
        
        if (empty($username)) {
            $errors[] = '用户名不能为空';
        } elseif (strlen($username) < 3) {
            $errors[] = '用户名至少3个字符';
        }
        
        if (empty($email)) {
            $errors[] = '邮箱不能为空';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = '邮箱格式不正确';
        }
        
        if (empty($password)) {
            $errors[] = '密码不能为空';
        } elseif (strlen($password) < 6) {
            $errors[] = '密码至少6个字符';
        }
        
        if ($password !== $confirmPassword) {
            $errors[] = '两次输入的密码不一致';
        }
        
        if (!empty($errors)) {
            $this->render('auth/register', ['errors' => $errors]);
            return;
        }
        
        $result = $this->userModel->register($username, $email, $password);
        
        if ($result['success']) {
            $this->render('auth/login', ['success' => $result['message']]);
        } else {
            $this->render('auth/register', ['error' => $result['message']]);
        }
    }
    
    // 用户登出
    public function logout() {
        if ($this->isLoggedIn()) {
            $this->userModel->updateStatus($_SESSION['user_id'], 'offline');
        }
        
        session_destroy();
        $this->redirect('/auth/login');
    }
    
    // 检查用户是否已登录
    private function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    // 重定向
    private function redirect($path) {
        header("Location: /CHATTING" . $path);
        exit;
    }
    
    // 渲染视图
    private function render($view, $data = []) {
        extract($data);
        include VIEW_PATH . '/' . $view . '.php';
    }
}
?>
