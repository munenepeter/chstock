 <?php

    $users = [
        'admin@lab.com' => [
            'password' => 'admin123',
            'name' => 'Admin User',
            'role' => 'admin'
        ],
        'manager@lab.com' => [
            'password' => 'manager123',
            'name' => 'Store Manager',
            'role' => 'store_manager'
        ],
        'viewer@lab.com' => [
            'password' => 'viewer123',
            'name' => 'Viewer User',
            'role' => 'viewer'
        ]
    ];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if (isset($users[$email]) && $users[$email]['password'] === $password) {
            $_SESSION['user'] = [
                'email' => $email,
                'name' => $users[$email]['name'],
                'role' => $users[$email]['role']
            ];
            header('Location: /dashboard.php');
            exit;
        } else {
            $_SESSION['error'] = 'Invalid credentials';
            header('Location: /index.php');
            exit;
        }
    }
