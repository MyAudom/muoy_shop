<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: ../login.php");
    exit;
}
include '../config.php';

$success_msg = '';
$error_msg = '';

// Create user
if (isset($_POST['add_user'])) {
    $username = $conn->real_escape_string($_POST['username'] ?? '');
    $email = $conn->real_escape_string($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    // Simple validation (similar to original signup)
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error_msg = "All fields are required.";
    } elseif (strlen($username) < 3) {
        $error_msg = "Username must be at least 3 characters long.";
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $error_msg = "Username can only contain letters, numbers, and underscores.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_msg = "Invalid email format.";
    } elseif (strlen($password) < 6) {
        $error_msg = "Password must be at least 6 characters long.";
    } elseif ($password !== $confirm_password) {
        $error_msg = "Passwords do not match.";
    } else {
        // Check if username or email already exists
        $check_sql = "SELECT username, email FROM users WHERE username = '$username' OR email = '$email'";
        $existing = $conn->query($check_sql)->fetch_assoc();
        if ($existing) {
            if ($existing['username'] === $username && $existing['email'] === $email) {
                $error_msg = "Both username and email are already taken.";
            } elseif ($existing['username'] === $username) {
                $error_msg = "Username is already taken.";
            } else {
                $error_msg = "Email is already registered.";
            }
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            // Get current date and time
            $created_at = date('Y-m-d H:i:s');

            $sql = "INSERT INTO users (username, email, password, created_at) VALUES ('$username', '$email', '$hashed_password', '$created_at')";
            if ($conn->query($sql)) {
                $success_msg = "User added successfully!";
            } else {
                $error_msg = "Error adding user.";
            }
        }
    }
}

// Update user
if (isset($_POST['update_user'])) {
    $id = (int)$_POST['id'];
    $username = $conn->real_escape_string($_POST['username'] ?? '');
    $email = $conn->real_escape_string($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    // Validation for required fields
    if (empty($username) || empty($email)) {
        $error_msg = "Username and email are required.";
    } elseif (strlen($username) < 3) {
        $error_msg = "Username must be at least 3 characters long.";
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $error_msg = "Username can only contain letters, numbers, and underscores.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_msg = "Invalid email format.";
    } else {
        // Check if username or email already exists (excluding current user)
        $check_sql = "SELECT id FROM users WHERE (username = '$username' OR email = '$email') AND id != $id";
        $existing = $conn->query($check_sql)->fetch_assoc();
        if ($existing) {
            if ($conn->query("SELECT username FROM users WHERE id = $id")->fetch_assoc()['username'] === $username) {
                // Username same, check email
                if ($conn->query("SELECT email FROM users WHERE id = $id")->fetch_assoc()['email'] !== $email) {
                    $error_msg = "Email is already registered.";
                }
            } else {
                $error_msg = "Username or email is already taken.";
            }
        } else {
            $password_sql = "";
            if (!empty($password)) {
                if (strlen($password) < 6) {
                    $error_msg = "Password must be at least 6 characters long.";
                } elseif ($password !== $confirm_password) {
                    $error_msg = "Passwords do not match.";
                } else {
                    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                    $password_sql = ", password = '$hashed_password'";
                }
            }

            if (empty($error_msg)) {
                $sql = "UPDATE users SET username = '$username', email = '$email' $password_sql WHERE id = $id";
                if ($conn->query($sql)) {
                    $success_msg = "User updated successfully!";
                } else {
                    $error_msg = "Error updating user.";
                }
            }
        }
    }
}

// Delete user
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $sql = "DELETE FROM users WHERE id = $id";
    if ($conn->query($sql)) {
        $success_msg = "User deleted successfully!";
    } else {
        $error_msg = "Error deleting user.";
    }
}

// Read users
$users = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <link href="../css/admin_css/styles.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="../img/icon.ico" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <h1>📦 Muoy Shop</h1>
            <p>Welcome, <?php echo isset($_SESSION['admin_username']) ? htmlspecialchars($_SESSION['admin_username']) : 'Admin'; ?></p>
        </div>
        
        <nav class="sidebar-nav">
            <a href="index.php" class="nav-item">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Dashboard
            </a>
            
            <a href="categories.php" class="nav-item">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                </svg>
                Categories
            </a>
            
            <a href="products.php" class="nav-item">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                Products
            </a>

            <a href="users.php" class="nav-item active">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 21v-2a4 4 0 00-8 0v2M12 11a4 4 0 100-8 4 4 0 000 8zm8 8v-2a4 4 0 00-3-3.87"/>
                </svg>
                Users
            </a>
            
            <a href="logout.php" class="nav-item" style="margin-top: auto;">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                Logout
            </a>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <div class="header">
            <button class="hamburger" onclick="toggleSidebar(event)">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/>
                </svg>
            </button>
            <h2>Manage Users</h2>
            <div class="header-actions">
                <button onclick="openModal()" class="btn btn-primary">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add User
                </button>
            </div>
        </div>

        <!-- Alert Messages -->
        <?php if ($success_msg): ?>
        <div class="alert alert-success">
            <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <?php echo $success_msg; ?>
        </div>
        <?php endif; ?>

        <?php if ($error_msg): ?>
        <div class="alert alert-danger">
            <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
            <?php echo $error_msg; ?>
        </div>
        <?php endif; ?>

        <!-- Users List -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">All Users</h3>
                <div class="search-bar">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" id="searchInput" class="form-input" placeholder="Search users..." onkeyup="searchTable()">
                </div>
            </div>

            <?php if ($users->num_rows > 0): ?>
            <div class="table-container">
                <table class="table" id="userTable">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $users->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($row['username']); ?></strong></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><?php echo date('Y-m-d H:i:s', strtotime($row['created_at'])); ?></td>
                            <td>
                                <div class="table-actions">
                                    <button onclick='editUser(<?php echo json_encode($row); ?>)' class="btn btn-secondary btn-sm">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Edit
                                    </button>
                                    <button onclick="deleteUser(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['username'], ENT_QUOTES); ?>')" class="btn btn-danger btn-sm">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="empty-state">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 21v-2a4 4 0 00-8 0v2M12 11a4 4 0 100-8 4 4 0 000 8zm8 8v-2a4 4 0 00-3-3.87"/>
                </svg>
                <h3>No Users Yet</h3>
                <p>Start adding users to your system</p>
                <button onclick="openModal()" class="btn btn-primary" style="margin-top: 1rem;">Add First User</button>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Add/Edit Modal -->
    <div id="userModal" class="modal">
        <div class="modal-content" style="max-width: 500px;">
            <div class="modal-header">
                <h3 class="modal-title" id="modalTitle">Add User</h3>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <form method="POST" id="userForm">
                <input type="hidden" name="id" id="userId">
                
                <div class="form-group">
                    <label class="form-label">Username *</label>
                    <input type="text" name="username" id="userUsername" class="form-input" placeholder="Enter username" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" id="userEmail" class="form-input" placeholder="Enter email" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Password *</label>
                    <input type="password" name="password" id="userPassword" class="form-input" placeholder="Enter password">
                    <small class="text-gray-500">Leave blank to keep current password (for edits)</small>
                </div>

                <div class="form-group">
                    <label class="form-label">Confirm Password *</label>
                    <input type="password" name="confirm_password" id="userConfirmPassword" class="form-input" placeholder="Confirm password">
                </div>

                <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 1.5rem;">
                    <button type="button" onclick="closeModal()" class="btn btn-secondary">Cancel</button>
                    <button type="submit" name="add_user" id="submitBtn" class="btn btn-primary">Add User</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Mobile menu toggle
        function toggleSidebar(event) {
            event.stopPropagation(); // Prevent event from bubbling up
            const sidebar = document.querySelector('.sidebar');
            if (sidebar) {
                sidebar.classList.toggle('active');
            } else {
                console.error('Sidebar element not found');
            }
        }

        function openModal() {
            document.getElementById('userModal').classList.add('active');
            document.getElementById('modalTitle').textContent = 'Add User';
            document.getElementById('userForm').reset();
            document.getElementById('userId').value = '';
            document.getElementById('submitBtn').name = 'add_user';
            document.getElementById('submitBtn').textContent = 'Add User';
            // Make password fields required for add
            document.getElementById('userPassword').required = true;
            document.getElementById('userConfirmPassword').required = true;
        }

        function closeModal() {
            document.getElementById('userModal').classList.remove('active');
        }

        function editUser(user) {
            document.getElementById('userModal').classList.add('active');
            document.getElementById('modalTitle').textContent = 'Edit User';
            document.getElementById('userId').value = user.id;
            document.getElementById('userUsername').value = user.username;
            document.getElementById('userEmail').value = user.email;
            document.getElementById('userPassword').value = '';
            document.getElementById('userConfirmPassword').value = '';
            document.getElementById('submitBtn').name = 'update_user';
            document.getElementById('submitBtn').textContent = 'Update User';
            // Make password fields optional for edit
            document.getElementById('userPassword').required = false;
            document.getElementById('userConfirmPassword').required = false;
        }

        function deleteUser(id, username) {
            if (confirm('Are you sure you want to delete user "' + username + '"?')) {
                window.location.href = '?delete=' + id;
            }
        }

        function searchTable() {
            const input = document.getElementById('searchInput');
            const filter = input.value.toUpperCase();
            const table = document.getElementById('userTable');
            const tr = table.getElementsByTagName('tr');

            for (let i = 1; i < tr.length; i++) {
                const tdUsername = tr[i].getElementsByTagName('td')[0];
                const tdEmail = tr[i].getElementsByTagName('td')[1];
                
                if (tdUsername || tdEmail) {
                    const usernameText = tdUsername ? (tdUsername.textContent || tdUsername.innerText) : '';
                    const emailText = tdEmail ? (tdEmail.textContent || tdEmail.innerText) : '';
                    
                    const combinedText = usernameText + emailText;
                    tr[i].style.display = combinedText.toUpperCase().indexOf(filter) > -1 ? '' : 'none';
                }
            }
        }

        // Close modal on outside click
        window.onclick = function(event) {
            const modal = document.getElementById('userModal');
            if (event.target == modal) {
                closeModal();
            }
        }
        
    </script>
</body>
</html>
