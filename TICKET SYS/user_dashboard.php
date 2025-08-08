<?php
// Fichier : user_dashboard.php
session_start();
require_once 'config.php';

// Protection de la page : si l'utilisateur n'est pas connecté, on le renvoie vers le login
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'utilisateur') {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Logique pour créer un nouveau ticket
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_ticket'])) {
    $titre = trim($_POST['titre']);
    $message = trim($_POST['message']);

    // Vérifier le dernier ticket envoyé par cet utilisateur
    $stmt = $pdo->prepare("SELECT date_creation FROM tickets WHERE id_utilisateur = ? ORDER BY date_creation DESC LIMIT 1");
    $stmt->execute([$user_id]);
    $last_ticket = $stmt->fetch(PDO::FETCH_ASSOC);

    $can_send = true;
    if ($last_ticket) {
        $last_time = strtotime($last_ticket['date_creation']);
        $now = time();
        $diff_hours = ($now - $last_time) / 3600;
        if ($diff_hours < 15) {
            $can_send = false;
        }
    }

    if (!$can_send) {
        $remaining = 15 - $diff_hours;
        $hours = floor($remaining);
        $minutes = floor(($remaining - $hours) * 60);
        // Ne pas afficher d'alerte, juste le minuteur visuel dans le formulaire
    } elseif (!empty($titre) && !empty($message)) {
        $stmt = $pdo->prepare("INSERT INTO tickets (id_utilisateur, titre, message) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $titre, $message]);
        $_SESSION['success_message'] = "Ticket créé avec succès !";
        header('Location: user_dashboard.php');
        exit();
    }
}

// Récupérer les tickets de l'utilisateur
$stmt = $pdo->prepare("SELECT * FROM tickets WHERE id_utilisateur = ? ORDER BY date_creation DESC");
$stmt->execute([$user_id]);
$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <link rel="icon" type="image/png" href="IMG/AYV RE.png">
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/png" href="../IMG/AYV RE.png">
    <meta charset="UTF-8">
    <title>Mon Tableau de Bord</title>
    <style>
body {
    font-family: 'Segoe UI', Arial, sans-serif;
    background: url('../IMG/ATCMARP.PNG') no-repeat center center fixed;
    background-size: cover;
    margin: 0;
    min-height: 100vh;
    position: relative;
}
body::before {
    content: "";
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0,0,0,0.45);
    z-index: 0;
}
        .container {
            background: #fff;
            padding: 2rem 1.5rem;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
            max-width: 600px;
            margin: 2.5rem auto;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        h1 {
            color: #2d3a4a;
            font-size: 2rem;
            font-weight: 700;
            margin: 0;
        }
        h2 {
            text-align: left;
            color: #4a90e2;
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 1.2rem;
            border-bottom: 1px solid #eaeaea;
            padding-bottom: 0.5rem;
        }
        .form-container, .tickets-container {
            background: #f7f8fa;
            padding: 1.2rem;
            margin-bottom: 1.5rem;
        <form action="logout.php" method="get" style="margin:0;">
            <button type="submit" class="logout-btn">Déconnexion</button>
        </form>
            font-weight: 500;
        }
        input[type="text"], textarea {
            width: 100%;
            padding: 0.7rem;
            margin-bottom: 0.7rem;
            border: 1px solid #dbe6ec;
            border-radius: 4px;
            box-sizing: border-box;
            background: #fff;
            color: #2d3a4a;
            font-weight: 400;
            font-size: 1rem;
            transition: border-color 0.2s;
        }
        input[type="text"]:focus, textarea:focus {
            border-color: #4a90e2;
            outline: none;
        }
        button {
            background-color: #fd0202ff;
            color: #fff;
            padding: 0.7rem 1.2rem;
            border: none;
            border-radius: 5px;
        .logout-btn {
            background-color: #dc3545;
            color: #fff;
            padding: 0.6rem 1.2rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(220,53,69,0.08);
            transition: background 0.2s, color 0.2s;
        }
        .logout-btn:hover {
            background-color: #a71d2a;
        }
            }
            h1, h2 {
                font-size: 1rem;
            }
            .form-container, .tickets-container {
                border-radius: 8px;
                padding: 0.5rem;
            }
            .ticket {
                padding: 0.5rem;
                font-size: 0.95rem;
            }
            button {
                font-size: 0.95rem;
                padding: 0.5rem 0.8rem;
            }
        }

        /* From Uiverse.io by vinodjangid07 */ 
.Btn {
  display: flex;
  align-items: center;
  justify-content: flex-start;
  width: 45px;
  height: 45px;
  border: none;
  border-radius: 50%;
  cursor: pointer;
  position: relative;
  overflow: hidden;
  transition-duration: .3s;
  box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.199);
  background-color: rgb(255, 65, 65);
}

/* plus sign */
.sign {
  width: 100%;
  transition-duration: .3s;
  display: flex;
  align-items: center;
  justify-content: center;
}

.sign svg {
  width: 17px;
}

.sign svg path {
  fill: white;
}
/* text */
.text {
  position: absolute;
  right: 0%;
  width: 0%;
  opacity: 0;
  color: white;
  font-size: 1.2em;
  font-weight: 600;
  transition-duration: .3s;
}
/* hover effect on button width */
.Btn:hover {
  width: 125px;
  border-radius: 40px;
  transition-duration: .3s;
}

.Btn:hover .sign {
  width: 30%;
  transition-duration: .3s;
  padding-left: 20px;
}
/* hover effect button's text */
.Btn:hover .text {
  opacity: 1;
  width: 70%;
  transition-duration: .3s;
  padding-right: 10px;
}
/* button click effect*/
.Btn:active {
  transform: translate(2px ,2px);
}
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>Bienvenue, <?= htmlspecialchars($_SESSION['user_email']) ?></h1>
        <form action="logout.php" method="get" style="margin:0;">
            <button class="Btn" type="submit">
                <div class="sign"><svg viewBox="0 0 512 512" style="width:24px;height:24px;"><path d="M377.9 105.9L500.7 228.7c7.2 7.2 11.3 17.1 11.3 27.3s-4.1 20.1-11.3 27.3L377.9 406.1c-6.4 6.4-15 9.9-24 9.9c-18.7 0-33.9-15.2-33.9-33.9l0-62.1-128 0c-17.7 0-32-14.3-32-32l0-64c0-17.7 14.3-32 32-32l128 0 0-62.1c0-18.7 15.2-33.9 33.9-33.9c9 0 17.6 3.6 24 9.9zM160 96L96 96c-17.7 0-32 14.3-32 32l0 256c0 17.7 14.3 32 32 32l64 0c17.7 0 32 14.3 32 32s-14.3 32-32 32l-64 0c-53 0-96-43-96-96L0 128C0 75 43 32 96 32l64 0c17.7 0 32 14.3 32 32s-14.3 32-32 32z"></path></svg></div>
                <div class="text">Logout</div>
            </button>
        </form>
    </div>

    <div class="form-container">
        <h2>Créer un nouveau ticket</h2>
        <form action="user_dashboard.php" method="POST">
            <input type="text" name="titre" placeholder="Titre de votre ticket" required>
            <textarea name="message" rows="5" placeholder="Décrivez votre problème ici..." required></textarea>
            <button type="submit" name="create_ticket" id="sendTicketBtn" <?php if (isset($can_send) && !$can_send) echo 'disabled'; ?>>Envoyer le ticket</button>
        </form>
        <?php if (isset($can_send) && !$can_send): ?>
            <div id="ticket-timer" style="text-align:center;margin-top:10px;color:#dc3545;font-weight:500;font-size:1.1em;">
                Vous pourrez envoyer un nouveau ticket dans : <span id="timer-value"><?php echo $hours."h ".$minutes."min"; ?></span>
            </div>
            <script>
                // Timer dynamique
                let totalSeconds = <?php echo intval($hours*3600 + $minutes*60); ?>;
                const timerValue = document.getElementById('timer-value');
                const sendBtn = document.getElementById('sendTicketBtn');
                function updateTimer() {
                    if (totalSeconds > 0) {
                        totalSeconds--;
                        let h = Math.floor(totalSeconds/3600);
                        let m = Math.floor((totalSeconds%3600)/60);
                        let s = totalSeconds%60;
                        timerValue.textContent = `${h}h ${m}min ${s}s`;
                    } else {
                        timerValue.textContent = 'Vous pouvez envoyer un ticket.';
                        sendBtn.disabled = false;
                    }
                }
                setInterval(updateTimer, 1000);
            </script>
        <?php endif; ?>
    </div>

    <div class="tickets-container">
        <h2>Mes tickets</h2>
        <?php if (empty($tickets)): ?>
            <p>Vous n'avez aucun ticket pour le moment.</p>
        <?php else: ?>
            <?php foreach ($tickets as $ticket): ?>
                <div class="ticket">
                    <h3>ID #<?= $ticket['id'] ?> - <?= htmlspecialchars($ticket['titre']) ?></h3>
                    <p><strong>Date :</strong> <?= date('d/m/Y H:i', strtotime($ticket['date_creation'])) ?></p>
                    <p><strong>Statut :</strong> <span class="status <?= $ticket['statut'] ?>"><?= $ticket['statut'] ?></span></p>
                    <p><strong>Mon message :</strong><br><?= nl2br(htmlspecialchars($ticket['message'])) ?></p>
                    <?php if (!empty($ticket['reponse_admin'])): ?>
                        <div class="response">
                            <strong>Réponse de l'administrateur :</strong><br>
                            <?= nl2br(htmlspecialchars($ticket['reponse_admin'])) ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

</body>
</html>