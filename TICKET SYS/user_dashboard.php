<?php
session_start();
require_once 'config.php';

// Protection d'accès : redirige si l'utilisateur n'est pas connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Récupérer les informations de l'utilisateur (username + image de profil)
$stmt = $pdo->prepare("SELECT username, profile_image FROM utilisateurs WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Si l'utilisateur n'existe pas dans la base de données, déconnecter par sécurité
if (!$user) {
    header('Location: logout.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord</title>
    <style>
        /* --- Styles Généraux --- */
        :root {
            --primary-color: #1abc9c; /* Vert d'eau */
            --secondary-color: #2c3e50; /* Bleu foncé */
            --light-gray: #f4f6f8;
            --dark-text: #34495e;
            --light-text: #ecf0f1;
            --card-shadow: 0 10px 25px rgba(0,0,0,0.08);
            --card-shadow-hover: 0 15px 35px rgba(0,0,0,0.12);
        }

        body {
            font-family: 'Segoe UI', 'Roboto', sans-serif;
            margin: 0;
            background-color: var(--light-gray);
            color: var(--dark-text);
            line-height: 1.6;
        }

        /* --- Header --- */
        header {
            background-color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #e0e0e0;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }

        header .brand {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--secondary-color);
        }
        
        header .brand a {
            text-decoration: none;
            color: inherit;
        }

        header .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .user-info .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--primary-color);
        }

        .user-info .username {
            font-weight: 600;
        }

        .user-info .logout-link {
            color: #e74c3c;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s;
        }
        .user-info .logout-link:hover {
            color: #c0392b;
        }

        /* --- Contenu Principal --- */
        main {
            max-width: 1000px;
            margin: 40px auto;
            padding: 20px;
        }

        .welcome-message {
            text-align: center;
            margin-bottom: 40px;
        }

        .welcome-message h1 {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--secondary-color);
            margin-bottom: 10px;
        }

        .welcome-message p {
            font-size: 1.1rem;
            color: #7f8c8d;
        }
        
        /* --- Cartes de Navigation --- */
        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            text-align: center;
        }

        .card {
            background: white;
            border-radius: 12px;
            padding: 40px 30px;
            box-shadow: var(--card-shadow);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            text-decoration: none;
            color: inherit;
            display: block; /* Pour que le lien remplisse la carte */
        }
        
        .card:hover {
            transform: translateY(-8px);
            box-shadow: var(--card-shadow-hover);
        }

        .card h2 {
            margin-top: 0;
            margin-bottom: 15px;
            font-size: 1.5rem;
            color: var(--primary-color);
        }

        .card p {
            font-size: 1rem;
            color: #7f8c8d;
        }

        /* --- Responsive --- */
        @media (max-width: 768px) {
            header {
                flex-direction: column;
                gap: 15px;
                padding: 20px;
            }
            main {
                margin-top: 20px;
            }
            .welcome-message h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>

<header>
    <div class="brand">
        <a href="user_dashboard.php">Mon Espace</a>
    </div>
    <div class="user-info">
        <span class="username"><?= htmlspecialchars($user['username']) ?></span>
        <img src="<?= htmlspecialchars($user['profile_image']) ?>" alt="Avatar" class="avatar" />
        <a href="logout.php" class="logout-link">Déconnexion</a>
    </div>
</header>

<main>
    <div class="welcome-message"> 
        <h1>Bienvenue, <?= htmlspecialchars($user['username']) ?> !</h1>
        <p>Gérez votre compte et explorez le contenu de la communauté.</p>
    </div>

    <div class="dashboard-cards">
        <a href="profile.php" class="card">
            <h2>Gérer mon profil</h2>
            <p>Mettez à jour vos informations personnelles, votre mot de passe et votre photo de profil.</p>
        </a>

        <a href="media.php" class="card" target="_blank" rel="noopener noreferrer">
            <h2>Accéder au Fil d'actualité</h2>
            <p>Consultez les derniers posts, commentez et réagissez avec la communauté.</p>
        </a>
    </div>
</main>

</body>
</html>