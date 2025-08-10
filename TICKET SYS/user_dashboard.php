<?php
session_start();
require_once 'config.php';

// Protection accès utilisateur
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'utilisateur') {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Traitement création ticket
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_ticket'])) {
    $titre = trim($_POST['titre'] ?? '');
    $message = trim($_POST['message'] ?? '');

    // Vérifier la date du dernier ticket créé par l'utilisateur
    $stmt = $pdo->prepare("SELECT date_creation FROM tickets WHERE id_utilisateur = ? ORDER BY date_creation DESC LIMIT 1");
    $stmt->execute([$user_id]);
    $last_ticket = $stmt->fetch(PDO::FETCH_ASSOC);

    $can_create = true;
    if ($last_ticket) {
        $last_time = strtotime($last_ticket['date_creation']);
        $now = time();
        if (($now - $last_time) < 3 * 3600) {
            $can_create = false;
        }
    }

    if (!$can_create) {
        $error_message = "Vous devez attendre 3 heures entre chaque création de ticket.";
        // Calculer le temps restant en secondes
        $time_left = 3 * 3600 - ($now - $last_time);
        $show_timer = true;
    } elseif ($titre !== '' && $message !== '') {
        $stmt = $pdo->prepare("INSERT INTO tickets (id_utilisateur, titre, message, statut, date_creation) VALUES (?, ?, ?, 'Ouvert', NOW())");
        $stmt->execute([$user_id, $titre, $message]);
        $success_message = "Ticket créé avec succès !";
    } else {
        $error_message = "Veuillez remplir tous les champs.";
    }
}

// Récupérer les infos utilisateur (username + image)
$stmt = $pdo->prepare("SELECT username, profile_image FROM utilisateurs WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Récupérer tickets de l'utilisateur (avec réponses éventuelles)
$stmt = $pdo->prepare("SELECT * FROM tickets WHERE id_utilisateur = ? ORDER BY date_creation DESC");
$stmt->execute([$user_id]);
$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Mon Tableau de Bord</title>
    <style>
        body {
            background: url('img/ATCMARP.png') no-repeat center center fixed;
            background-size: cover;
        }
        /* Reset & basics */
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f9fafc;
            color: #2c3e50;
            margin: 0; padding: 0;
        }
        a {
            color: #3498db;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        /* Navbar */
        header {
            background-color: #2c3e50;
            color: #ecf0f1;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        header .left, header .right {
            display: flex;
            align-items: center;
        }
        header .left a, header .right a {
            margin-left: 20px;
            font-weight: 600;
            transition: color 0.3s ease;
        }
        header .left a:hover, header .right a:hover {
            color: #1abc9c;
        }
        .avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #1abc9c;
            margin-right: 10px;
        }
        main {
            max-width: 900px;
            margin: 30px auto;
            padding: 0 20px;
        }
        h1 {
            font-weight: 700;
            margin-bottom: 30px;
            color: #34495e;
        }
        /* Formulaire */
        form {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.05);
            margin-bottom: 40px;
        }
        form h2 {
            margin-top: 0;
            margin-bottom: 20px;
            color: #34495e;
        }
        label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #34495e;
        }
        input[type="text"], textarea {
            width: 100%;
            padding: 12px 15px;
            font-size: 1rem;
            border-radius: 8px;
            border: 1px solid #bdc3c7;
            margin-bottom: 20px;
            transition: border-color 0.3s ease;
            resize: vertical;
        }
        input[type="text"]:focus, textarea:focus {
            border-color: #1abc9c;
            outline: none;
        }
        button[type="submit"] {
            background-color: #1abc9c;
            border: none;
            padding: 12px 25px;
            color: white;
            font-weight: 700;
            font-size: 1.1rem;
            border-radius: 30px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        button[type="submit"]:hover {
            background-color: #16a085;
        }
        /* Messages */
        .message {
            margin-bottom: 20px;
            font-weight: 600;
            padding: 12px 20px;
            border-radius: 8px;
        }
        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        /* Liste tickets */
        .tickets {
            margin-bottom: 60px;
        }
        .tickets h2 {
            color: #34495e;
            border-bottom: 3px solid #1abc9c;
            padding-bottom: 8px;
            margin-bottom: 25px;
        }
        .ticket {
            background: white;
            border-radius: 10px;
            padding: 20px 25px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.05);
            margin-bottom: 25px;
            transition: box-shadow 0.3s ease;
        }
        .ticket:hover {
            box-shadow: 0 12px 28px rgba(0,0,0,0.1);
        }
        .ticket h3 {
            margin-top: 0;
            margin-bottom: 8px;
            color: #16a085;
        }
        .ticket p {
            margin: 6px 0;
            color: #2c3e50;
            line-height: 1.5;
        }
        .ticket .status {
            font-weight: 700;
            text-transform: uppercase;
            padding: 4px 12px;
            border-radius: 30px;
            font-size: 0.85rem;
            color: white;
            display: inline-block;
            margin-bottom: 15px;
        }
        .status.Ouvert {
            background-color: #27ae60;
        }
        .status.Fermé {
            background-color: #e74c3c;
        }
        .response {
            background-color: #e0f7f5;
            border-left: 5px solid #1abc9c;
            padding: 15px 20px;
            margin-top: 15px;
            border-radius: 8px;
            color: #34495e;
            font-style: italic;
            white-space: pre-wrap;
        }
        /* Section Médias */
        .media-section {
            background: white;
            padding: 20px 25px;
            border-radius: 10px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.05);
            text-align: center;
        }
        .media-section h2 {
            color: #34495e;
            margin-bottom: 15px;
            border-bottom: 3px solid #1abc9c;
            padding-bottom: 8px;
        }
        .media-section p {
            color: #7f8c8d;
            font-size: 1.1rem;
            margin-bottom: 25px;
        }
        .btn-media {
            background-color: #1abc9c;
            color: white;
            padding: 12px 30px;
            border-radius: 30px;
            font-weight: 700;
            font-size: 1.1rem;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }
        .btn-media:hover {
            background-color: #16a085;
        }

        /* Responsive */
        @media (max-width: 600px) {
            header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            header .left, header .right {
                flex-wrap: wrap;
            }
            main {
                margin: 20px 15px;
                padding: 0 10px;
            }
        }
    </style>
</head>
<body>
<header>
    <div class="left">
        <a href="user_dashboard.php">Tableau de bord</a>
        <a href="media.php" target="_blank" rel="noopener noreferrer">Médias</a>
    </div>
    <div class="right">
        <img src="<?= htmlspecialchars($user['profile_image']) ?>" alt="Avatar" class="avatar" />
        <span><?= htmlspecialchars($user['username']) ?></span>
        <a href="logout.php" style="margin-left: 25px; font-weight: 700; color:#e74c3c;">Déconnexion</a>
    </div>
</header>

<main>
    <h1>Bienvenue, <?= htmlspecialchars($user['username']) ?> !</h1>

    <?php if (isset($success_message)): ?>
        <div class="message success"><?= htmlspecialchars($success_message) ?></div>
    <?php endif; ?>
    <?php if (isset($error_message)): ?>
        <div class="message error"><?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>

    <form method="POST" action="user_dashboard.php" novalidate>
        <h2>Créer un nouveau ticket</h2>
        <?php if (isset($show_timer) && $show_timer && isset($time_left) && $time_left > 0): ?>
            <div class="message error">
                <span>Vous pourrez créer un ticket dans :</span>
                <span id="ticket-timer" style="font-weight:bold;color:#e74c3c;"></span>
            </div>
            <script>
                var timeLeft = <?= $time_left ?>;
                function formatTime(sec) {
                    var h = Math.floor(sec / 3600);
                    var m = Math.floor((sec % 3600) / 60);
                    var s = sec % 60;
                    return (h < 10 ? '0' : '') + h + ':' + (m < 10 ? '0' : '') + m + ':' + (s < 10 ? '0' : '') + s;
                }
                function updateTimer() {
                    var el = document.getElementById('ticket-timer');
                    if (timeLeft > 0) {
                        el.textContent = formatTime(timeLeft);
                        timeLeft--;
                    } else {
                        el.textContent = '00:00:00';
                        location.reload();
                    }
                }
                updateTimer();
                setInterval(updateTimer, 1000);
            </script>
        <?php endif; ?>
        <label for="titre">Titre du ticket</label>
        <input type="text" id="titre" name="titre" placeholder="Titre" required maxlength="255" <?php if (isset($show_timer) && $show_timer && isset($time_left) && $time_left > 0) echo 'disabled'; ?> />
        <label for="message">Description du problème</label>
        <textarea id="message" name="message" rows="5" placeholder="Expliquez votre problème..." required <?php if (isset($show_timer) && $show_timer && isset($time_left) && $time_left > 0) echo 'disabled'; ?>></textarea>
        <button type="submit" name="create_ticket" <?php if (isset($show_timer) && $show_timer && isset($time_left) && $time_left > 0) echo 'disabled'; ?>>Envoyer le ticket</button>
    </form>

    <section class="tickets">
        <h2>Mes tickets</h2>
        <?php if (empty($tickets)): ?>
            <p>Vous n'avez aucun ticket pour le moment.</p>
        <?php else: ?>
            <?php foreach ($tickets as $ticket): ?>
                <article class="ticket">
                    <h3>#<?= $ticket['id'] ?> - <?= htmlspecialchars($ticket['titre']) ?></h3>
                    <p><strong>Date :</strong> <?= date('d/m/Y H:i', strtotime($ticket['date_creation'])) ?></p>
                    <p><strong>Statut :</strong> <span class="status <?= htmlspecialchars($ticket['statut']) ?>"><?= htmlspecialchars($ticket['statut']) ?></span></p>
                    <p><strong>Message :</strong><br><?= nl2br(htmlspecialchars($ticket['message'])) ?></p>

                    <?php if (!empty($ticket['reponse_admin'])): ?>
                        <div class="response">
                            <strong>Réponse de l'administrateur :</strong><br>
                            <?= nl2br(htmlspecialchars($ticket['reponse_admin'])) ?>
                        </div>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>

    <section class="media-section">
        <h2>Fil d'actualité & Médias</h2>
        <p>Consultez les derniers posts, commentez et réagissez, mais vous ne pouvez pas modifier le contenu.</p>
        <a href="media.php" target="_blank" rel="noopener noreferrer" class="btn-media">Voir les médias</a>
    </section>
</main>

</body>
</html>
