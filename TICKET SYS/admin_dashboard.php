<?php
// Fichier : admin_dashboard.php
session_start();
require_once 'config.php';

// Protection de la page : l'utilisateur doit être un admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Récupérer tous les tickets avec l'email de l'utilisateur qui l'a créé
$stmt = $pdo->query("
    SELECT tickets.*, utilisateurs.email 
    FROM tickets 
    JOIN utilisateurs ON tickets.id_utilisateur = utilisateurs.id 
    ORDER BY tickets.date_creation DESC
");
$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/png" href="../IMG/AYV RE.png">
    <meta charset="UTF-8">
    <title>Tableau de Bord Admin</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: url('../IMG/ATCMARP.PNG') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
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
            background: rgba(255, 255, 255, 18);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            padding: 2rem;
            border-radius: 18px;
            box-shadow: 0 8px 32px rgba(31,38,135,0.12);
            width: 1100px;
            position: relative;
            z-index: 1;
        }
        h2 {
            text-align: center;
            color: #8c9b04ff;
            text-shadow: 0 0 8px #d5e400ff, 0 0 16px #bbff00ff;
            font-family: 'Segoe UI', Arial, sans-serif;
        }
        form { display: flex; flex-direction: column; }
        .input-group { margin-bottom: 1rem; }
        label {
            margin-bottom: 0.5rem;
            color: #b0ce04ff;
            text-shadow: 0 0 6px #9dc704ff;
            font-weight: 500;
        }
        input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #9cc505ff;
            border-radius: 4px;
            box-sizing: border-box;
            background: rgba(255, 255, 255, );
            color: #99ce09ff;
            font-weight: 500;
            text-shadow: 0 0 6px #9bbd05ff;
        }
        button {
            padding: 0.75rem;
            background-color: #aabd01ff;
            color: #181818;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: bold;
            box-shadow: 0 0 8px #99b308ff, 0 0 16px #9dc001ff;
            transition: background 0.2s, color 0.2s;
        }
        button:hover {
            background-color: #181818;
            color: #718505ff;
            box-shadow: 0 0 16px #6f8606ff;
        }
        .toggle-link { text-align: center; margin-top: 1rem; }
        .toggle-link a {
            color: #c4350aff;
            text-decoration: none;
            cursor: pointer;
            text-shadow: 0 0 6px #b64c0eff;
        }
        .toggle-link  {
            margin: 0.5rem 0;
            color: #ffffffff;
            text-shadow: 0 0 6px #ffffffff;
        }
        .message {
            text-align: center;
            color: #00ffe7;
            margin-bottom: 1rem;
            text-shadow: 0 0 6px #00ffe7;
        }
        .error {
            text-align: center;
            color: #ff0055;
            margin-bottom: 1rem;
            text-shadow: 0 0 6px #ff0055;
        }
        #signup-form { display: none; }


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
    <link rel="icon" type="image/png" href="IMG/AYV RE.png">
</head>
<body>

<div class="container">
    <div class="header">
        <h1>Tableau de Bord Administrateur</h1>
        <form action="logout.php" method="get" style="margin:0;">
            <button class="Btn" type="submit">
                <div class="sign"><svg viewBox="0 0 512 512" style="width:24px;height:24px;"><path d="M377.9 105.9L500.7 228.7c7.2 7.2 11.3 17.1 11.3 27.3s-4.1 20.1-11.3 27.3L377.9 406.1c-6.4 6.4-15 9.9-24 9.9c-18.7 0-33.9-15.2-33.9-33.9l0-62.1-128 0c-17.7 0-32-14.3-32-32l0-64c0-17.7 14.3-32 32-32l128 0 0-62.1c0-18.7 15.2-33.9 33.9-33.9c9 0 17.6 3.6 24 9.9zM160 96L96 96c-17.7 0-32 14.3-32 32l0 256c0 17.7 14.3 32 32 32l64 0c17.7 0 32 14.3 32 32s-14.3 32-32 32l-64 0c-53 0-96-43-96-96L0 128C0 75 43 32 96 32l64 0c17.7 0 32 14.3 32 32s-14.3 32-32 32z"></path></svg></div>
                <div class="text">Logout</div>
            </button>
        </form>
    </div>

    <h2>Tous les tickets</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Titre</th>
                <th>Créé par</th>
                <th>Date</th>
                <th>Statut</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($tickets)): ?>
                <tr><td colspan="6">Aucun ticket à afficher.</td></tr>
            <?php else: ?>
                <?php foreach ($tickets as $ticket): ?>
                    <tr>
                        <td>#<?= $ticket['id'] ?></td>
                        <td><?= htmlspecialchars($ticket['titre']) ?></td>
                        <td><?= htmlspecialchars($ticket['email']) ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($ticket['date_creation'])) ?></td>
                        <td><span class="status <?= $ticket['statut'] ?>"><?= $ticket['statut'] ?></span></td>
                        <td><a href="reply_ticket.php?id=<?= $ticket['id'] ?>">Voir / Répondre</a></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>