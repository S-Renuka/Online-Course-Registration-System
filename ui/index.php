<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Online Course Registration System</title>
<style>
body { font-family: Arial, sans-serif; margin: 0; background-color: #f2f8fc; }

/* Navbar */
.navbar { background-color: #004d99; display: flex; justify-content: space-between; align-items: center; padding: 15px 40px; }
.navbar .logo { display: flex; align-items: center; color: white; font-size: 22px; font-weight: bold; }
.navbar img { height: 40px; margin-right: 10px; }
.nav-links button { background: white; color: black; padding: 8px 15px; border-radius: 5px; cursor: pointer; font-weight: bold; border: none; }

/* Hero */
.hero { background-image: url('https://images.unsplash.com/photo-1503676260728-1c00da094a0b'); background-size: cover; background-position: center; height: 450px; display: flex; justify-content: center; align-items: center; color: white; text-align: center; }
.hero h1 { font-size: 50px; background: rgba(0,0,0,0.6); padding: 20px 40px; border-radius: 12px; }

/* Content */
.container { text-align: center; padding: 60px 40px; }
.container h2 { color: #004d99; font-size: 32px; margin-bottom: 15px; }
.container p { font-size: 18px; color: #333; max-width: 700px; margin: 0 auto 40px auto; }

/* Features */
.features { display: flex; justify-content: center; flex-wrap: wrap; gap: 25px; }
.feature-box { background-color: white; padding: 25px; border-radius: 12px; width: 260px; box-shadow: 0 3px 10px rgba(0,0,0,0.1); transition: transform 0.3s ease; }
.feature-box:hover { transform: translateY(-5px); }
.feature-box h3 { color: #0066cc; margin-bottom: 10px; }
.feature-box p { color: #555; font-size: 15px; }

/* Footer */
footer { background-color: #003366; color: white; text-align: center; padding: 20px; margin-top: 60px; font-size: 15px; }

/* Modal */
.modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background: rgba(0,0,0,0.6); justify-content: center; align-items: center; }
.modal-content { background: white; padding: 30px 40px; border-radius: 12px; width: 350px; text-align: center; position: relative; animation: fadeIn 0.3s ease; }
@keyframes fadeIn { from {opacity: 0; transform: scale(0.9);} to {opacity: 1; transform: scale(1);} }
.modal-content h2 { color: #004d99; margin-bottom: 20px; }
.modal-content input { width: 100%; padding: 10px; margin: 10px 0; border-radius: 8px; border: 1px solid #ccc; font-size: 15px; }
.modal-content button { background-color: #004d99; color: white; padding: 10px 20px; border: none; border-radius: 8px; cursor: pointer; transition: 0.3s; width: 100%; }
.modal-content button:hover { background-color: #003366; }
.close-btn { position: absolute; top: 10px; right: 15px; font-size: 22px; color: #333; cursor: pointer; }
.switch-text { margin-top: 10px; font-size: 14px; }
.switch-text a { color: #004d99; text-decoration: none; font-weight: bold; cursor: pointer; }
.switch-text a:hover { text-decoration: underline; }
</style>
</head>
<body>

<!-- Navbar -->
<div class="navbar">
  <div class="logo">
    <img src="../images/login.jpg" alt="Logo">
    Online Course Registration System
  </div>
  <div class="nav-links">
    <button id="loginBtn">Login / Register</button>
  </div>
</div>

<!-- Hero -->
<div class="hero">
  <h1>Empower Your Future with Quality Learning</h1>
</div>

<!-- Content -->
<div class="container">
  <h2>Welcome to the Online Course Registration System</h2>
  <p>Discover, register, and learn with ease. Manage your courses, upload certificates, take eligibility tests, and get your certificates — all in one platform.</p>

  <div class="features">
    <div class="feature-box">
      <h3>📚 Browse Courses</h3>
      <p>Explore a variety of academic and skill-based courses.</p>
    </div>
    <div class="feature-box">
      <h3>📝 Easy Registration</h3>
      <p>Register in minutes and access all required learning materials.</p>
    </div>
    <div class="feature-box">
      <h3>🎓 Certificates</h3>
      <p>Get digital certificates after successful course completion.</p>
    </div>
    <div class="feature-box">
      <h3>💻 Dashboard Access</h3>
      <p>Track your progress and manage registrations anytime.</p>
    </div>
  </div>
</div>

<footer>
  &copy; 2025 Online Course Registration System. All Rights Reserved.
</footer>

<!-- ROLE SELECTION MODAL -->
<div class="modal" id="roleModal">
  <div class="modal-content">
    <span class="close-btn" onclick="closeAll()">&times;</span>
    <h2>Select Role</h2>
    <button class="role-btn" onclick="openStudentLogin()">🎓 Student</button>
    <br>
    <button class="role-btn" onclick="adminRedirect()">🔐 Admin</button>
  </div>
</div>

<!-- STUDENT LOGIN MODAL -->
<div class="modal" id="loginModal">
  <div class="modal-content">
    <span class="close-btn" onclick="closeAll()">&times;</span>
    <h2>Student Login</h2>
    <form action="../backend/login.php" method="POST">
      <input type="text" name="username" placeholder="Username or Email" required>
      <input type="password" name="password" placeholder="Password" required>
      <button type="submit">Login</button>
    </form>
    <p class="switch-text">Don’t have an account? <a onclick="switchToRegister()">Register</a></p>
  </div>
</div>

<!-- STUDENT REGISTER MODAL -->
<div class="modal" id="registerModal">
  <div class="modal-content">
    <span class="close-btn" onclick="closeAll()">&times;</span>
    <h2>Student Register</h2>
    <form action="../backend/register.php" method="POST">
      <input type="text" name="username" placeholder="Username" required>
      <input type="email" name="email" placeholder="Email" required>
      <input type="password" name="password" placeholder="Password" required>
      <input type="password" name="confirm_password" placeholder="Confirm Password" required>
      <button type="submit">Register</button>
    </form>
  </div>
</div>

<script>
const roleModal = document.getElementById("roleModal");
const loginModal = document.getElementById("loginModal");
const registerModal = document.getElementById("registerModal");
const loginBtn = document.getElementById("loginBtn");

// Open role selection modal
loginBtn.addEventListener("click", function() {
  roleModal.style.display = "flex";
});

// Student login
function openStudentLogin() {
  roleModal.style.display = "none";
  loginModal.style.display = "flex";
}

// Switch to register
function switchToRegister() {
  loginModal.style.display = "none";
  registerModal.style.display = "flex";
}

// Admin redirect
function adminRedirect() {
  window.location.href = "../admin/admin_login.php";
}

// Close all modals
function closeAll() {
  roleModal.style.display = "none";
  loginModal.style.display = "none";
  registerModal.style.display = "none";
}

// Close on outside click
window.onclick = function(event) {
  if(event.target === roleModal || event.target === loginModal || event.target === registerModal){
    closeAll();
  }
};
</script>

</body>
</html>
