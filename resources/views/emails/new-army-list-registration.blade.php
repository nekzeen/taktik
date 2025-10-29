<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #b91c1c; color: white; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 24px; }
        .content { background-color: #f9fafb; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
        .details { margin: 20px 0; }
        .detail-item { margin: 10px 0; }
        .detail-label { font-weight: bold; color: #b91c1c; }
        .button { display: inline-block; background-color: #b91c1c; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
        .footer { text-align: center; color: #666; font-size: 12px; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Nouvelle Inscription au Tournoi</h1>
        </div>

        <div class="content">
            <p>Bonjour {{ $armyList->tournament->creator->name }},</p>

            <p>Un joueur s'est inscrit à votre tournoi <strong>{{ $armyList->tournament->name }}</strong>.</p>

            <div class="details">
                <h2>Détails de l'Inscription</h2>
                <div class="detail-item">
                    <span class="detail-label">Joueur :</span> {{ $armyList->user->name }}
                </div>
                <div class="detail-item">
                    <span class="detail-label">Email :</span> {{ $armyList->user->email }}
                </div>
                <div class="detail-item">
                    <span class="detail-label">Faction :</span> {{ $armyList->faction->name_fr }}
                </div>
                <div class="detail-item">
                    <span class="detail-label">Détachement :</span> {{ $armyList->detachment }}
                </div>
                <div class="detail-item">
                    <span class="detail-label">Points d'armée :</span> {{ $armyList->points ?? 'Non spécifié' }} pts
                </div>
                <div class="detail-item">
                    <span class="detail-label">Statut :</span> En attente de validation
                </div>
            </div>

            <h2>Action Requise</h2>
            <p>Veuillez consulter la liste d'armée et valider ou rejeter l'inscription.</p>

            <a href="{{ route('tournaments.registrations.manage', $armyList->tournament) }}" class="button">
                Gérer les Inscriptions
            </a>
        </div>

        <div class="footer">
            <p>L'équipe Warhammer 40K</p>
        </div>
    </div>
</body>
</html>
