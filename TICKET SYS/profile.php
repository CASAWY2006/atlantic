<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Récupérer les infos actuelles de l'utilisateur
$stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Définir image par défaut si inexistante
if (empty($user['profile_image'])) {
    $user['profile_image'] = 'img/profil.png';
}

// === Mise à jour profil (username + image) ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $username = trim($_POST['username']);
    $image_path = $_POST['current_image'] ?? 'img/profil.png';

    // Vérifier si username déjà pris (autre que soi)
    $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE username = ? AND id != ?");
    $stmt->execute([$username, $user_id]);
    if ($stmt->fetch()) {
        $error = "Ce nom d'utilisateur est déjà pris.";
    } else {
        // Upload image si nouvelle
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
            $upload_dir = 'uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $file_name = $user_id . '_' . time() . '_' . basename($_FILES['profile_image']['name']);
            $target_file = $upload_dir . $file_name;

            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file)) {
                $image_path = $target_file;
            } else {
                $error = "Erreur lors de l'upload de l'image.";
            }
        }

        if (empty($error)) {
            $stmt = $pdo->prepare("UPDATE utilisateurs SET username = ?, profile_image = ? WHERE id = ?");
            $stmt->execute([$username, $image_path, $user_id]);
            $_SESSION['user_name'] = $username;
            $user['username'] = $username;
            $user['profile_image'] = $image_path;
            $_SESSION['profile_pic'] = $image_path;

            $message = "Profil mis à jour avec succès !";
        }
    }
}

// === Changement mot de passe ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    $stmt = $pdo->prepare("SELECT mot_de_passe FROM utilisateurs WHERE id = ?");
    $stmt->execute([$user_id]);
    $user_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (password_verify($current_password, $user_data['mot_de_passe'])) {
        if ($new_password === $confirm_password) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE utilisateurs SET mot_de_passe = ? WHERE id = ?");
            $stmt->execute([$hashed_password, $user_id]);
            $message = "Mot de passe mis à jour avec succès !";
        } else {
            $error = "Les nouveaux mots de passe ne correspondent pas.";
        }
    } else {
        $error = "Le mot de passe actuel est incorrect.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil</title>
    <style>
  /* === BASE === */
body {
  font-family: 'Poppins', sans-serif;
  background: linear-gradient(135deg, #0f0f1f, #2b1055, #ff3c00);
  color: #fff;
  margin: 0;
  min-height: 100vh;
  display: flex;
  justify-content: center;
  align-items: flex-start;
  padding: 40px 20px;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}

.container {
  max-width: 900px;
  width: 100%;
  background: rgba(30, 30, 45, 0.85);
  border-radius: 16px;
  padding: 30px 40px;
  box-shadow: 0 12px 40px rgba(255, 60, 0, 0.3);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  transition: box-shadow 0.3s ease;
}

.container:hover {
  box-shadow: 0 20px 50px rgba(255, 60, 0, 0.5);
}

/* === HEADER === */
.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30px;
}

.header a {
  background: linear-gradient(135deg, #007bff, #00c6ff);
  padding: 12px 25px;
  color: white;
  border-radius: 12px;
  text-decoration: none;
  font-weight: 600;
  font-size: 16px;
  box-shadow: 0 4px 12px rgba(0, 123, 255, 0.5);
  transition: background 0.3s ease, box-shadow 0.3s ease;
}

.header a:hover,
.header a:focus {
  background: linear-gradient(135deg, #0056b3, #009ad6);
  box-shadow: 0 6px 20px rgba(0, 86, 179, 0.7);
  outline: none;
}

/* === MESSAGES === */
.message,
.error {
  padding: 15px 20px;
  border-radius: 10px;
  margin-bottom: 25px;
  font-weight: 600;
  font-size: 16px;
  box-shadow: 0 4px 8px rgba(0,0,0,0.15);
  backdrop-filter: blur(4px);
}

.message {
  background: rgba(50, 205, 50, 0.15);
  color: #32cd32;
  border: 1.5px solid #32cd32;
}

.error {
  background: rgba(255, 60, 0, 0.15);
  color: #ff3c00;
  border: 1.5px solid #ff3c00;
}

/* === FORM CONTAINER === */
.form-container {
  background: rgba(255, 255, 255, 0.07);
  padding: 35px 30px;
  border-radius: 20px;
  box-shadow: inset 0 0 15px rgba(255, 255, 255, 0.1);
  transition: background 0.3s ease;
}

.form-container:hover {
  background: rgba(255, 255, 255, 0.12);
}

/* === PROFILE PICTURE === */
.profile-pic-container {
  text-align: center;
  margin-bottom: 30px;
}

.avatar-lg {
  width: 140px;
  height: 140px;
  border-radius: 50%;
  object-fit: cover;
  border: 4px solid #ffd700;
  box-shadow: 0 0 18px #ffd700aa;
  transition: box-shadow 0.3s ease;
}

.avatar-lg:hover,
.avatar-lg:focus {
  box-shadow: 0 0 28px #ffd700ff;
  outline: none;
}

/* === CUSTOM FILE INPUT === */
.file-input {
  display: none;
}

.custom-file-label {
  background: #444;
  padding: 14px 20px;
  border-radius: 12px;
  cursor: pointer;
  display: inline-block;
  font-weight: 600;
  letter-spacing: 0.03em;
  color: #ddd;
  transition: background 0.3s ease, color 0.3s ease;
  user-select: none;
}

.custom-file-label:hover,
.custom-file-label:focus {
  background: #555;
  color: #fff;
  outline: none;
  box-shadow: 0 0 10px #ff3c00aa;
}

/* === INPUT GROUPS === */
.input-group {
  margin-bottom: 25px;
}

.input-group label {
  display: block;
  margin-bottom: 8px;
  font-weight: 600;
  font-size: 15px;
  color: #ffb347;
  text-shadow: 0 0 4px #ff3c00bb;
  user-select: none;
}

.input-group input {
  width: 100%;
  padding: 12px 15px;
  border-radius: 10px;
  border: none;
  background: rgba(255, 255, 255, 0.12);
  color: white;
  font-size: 16px;
  font-weight: 500;
  box-shadow: inset 0 0 8px rgba(255, 255, 255, 0.15);
  transition: background 0.3s ease, box-shadow 0.3s ease;
}

.input-group input::placeholder {
  color: #ddd;
  opacity: 0.7;
}

.input-group input:focus {
  background: rgba(255, 255, 255, 0.22);
  box-shadow: 0 0 10px 2px #ff3c00cc;
  outline: none;
  color: #fff;
}

/* === BUTTONS === */
.btn {
  width: 100%;
  padding: 14px 0;
  border: none;
  border-radius: 14px;
  font-weight: 700;
  font-size: 18px;
  cursor: pointer;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  box-shadow: 0 6px 18px rgba(0, 0, 0, 0.25);
  transition: all 0.4s ease;
  user-select: none;
  display: inline-flex;
  justify-content: center;
  align-items: center;
  gap: 10px;
}

.btn-primary {
  background: linear-gradient(45deg, #00b09b, #96c93d);
  color: white;
  box-shadow: 0 8px 22px rgba(150, 201, 61, 0.7);
}

.btn-primary:hover,
.btn-primary:focus {
  background: linear-gradient(45deg, #079d84, #a2c74a);
  box-shadow: 0 10px 26px rgba(150, 201, 61, 0.9);
  outline: none;
  transform: translateY(-3px);
}

.btn-danger {
  background: linear-gradient(45deg, #ff416c, #ff4b2b);
  color: white;
  box-shadow: 0 8px 22px rgba(255, 75, 43, 0.7);
}

.btn-danger:hover,
.btn-danger:focus {
  background: linear-gradient(45deg, #e03559, #e04b1b);
  box-shadow: 0 10px 26px rgba(255, 75, 43, 0.9);
  outline: none;
  transform: translateY(-3px);
}

/* === RESPONSIVE === */
@media (max-width: 768px) {
  .container {
    padding: 25px 20px;
  }

  .header a {
    padding: 10px 18px;
    font-size: 14px;
  }

  .avatar-lg {
    width: 110px;
    height: 110px;
  }

  .input-group input {
    font-size: 14px;
  }

  .btn {
    font-size: 16px;
    padding: 12px 0;
  }
}

    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Mon Profil</h1>
        <a href="<?= $user['role'] === 'admin' ? 'admin_dashboard.php' : 'user_dashboard.php' ?>" class="btn btn-primary">&larr; Retour</a>
    </div>

    <?php if ($message) echo "<p class='message'>$message</p>"; ?>
    <?php if ($error) echo "<p class='error'>$error</p>"; ?>

    <div class="profile-grid">
        <!-- Formulaire Informations Générales -->
        <div class="form-container">
            <h2>Informations</h2>
            <form action="" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="current_image" value="<?= htmlspecialchars($user['profile_image']) ?>">
                <div class="profile-pic-container">
                    <img src="<?= htmlspecialchars($user['profile_image']) ?>" alt="Avatar" class="avatar-lg" id="preview">
                </div>
                <div class="input-group">
                    <label for="profile_image">Changer l'image de profil</label>
                    <input type="file" id="profile_image" name="profile_image" accept="image/*" onchange="previewImage(event)">
                   

                </div>
                <div class="input-group">
                    <label for="username">Nom d'utilisateur</label>
                    <input type="text" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
                </div>
                <div class="input-group">
                    <label for="email">Email (non modifiable)</label>
                    <input type="email" id="email" value="<?= htmlspecialchars($user['email']) ?>" disabled>
                </div>
                <button type="submit" name="update_profile" class="btn btn-primary">Mettre à jour</button>
            </form>
        </div>

        <!-- Formulaire Mot de Passe -->
        <div class="form-container">
            <h2>Changer le mot de passe</h2>
            <form action="" method="POST">
                <div class="input-group">
                    <label for="current_password">Mot de passe actuel</label>
                    <input type="password" id="current_password" name="current_password" required>
                </div>
                <div class="input-group">
                    <label for="new_password">Nouveau mot de passe</label>
                    <input type="password" id="new_password" name="new_password" required>
                </div>
                <div class="input-group">
                    <label for="confirm_password">Confirmer le nouveau mot de passe</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                <button type="submit" name="update_password" class="btn btn-danger">Changer le mot de passe</button>
            </form>
        </div>
    </div>
</div>

<script>
function previewImage(event) {
    const output = document.getElementById('preview');
    output.src = URL.createObjectURL(event.target.files[0]);
    output.onload = () => URL.revokeObjectURL(output.src);
}
</script>
</body>
</html>
