<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #b91c1c; color: white; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 22px; }
        .content { background-color: #f9fafb; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
        .detail-item { margin: 10px 0; }
        .detail-label { font-weight: bold; color: #b91c1c; }
        .footer { text-align: center; color: #666; font-size: 12px; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>❌ Date annulée — match dé-planifié</h1>
        </div>

        <div class="content">
            <p>Bonjour {{ $proposedBy->name }},</p>

            <p>La date qui avait été acceptée pour votre match a été annulée. Le match n'est plus planifié.</p>

            <div class="detail-item">
                <span class="detail-label">Tournoi :</span> {{ $tournament->name }}
            </div>

            <div class="detail-item">
                <span class="detail-label">Match :</span> {{ $match->player1->name }} vs {{ $match->player2->name }}
            </div>

            <div class="detail-item">
                <span class="detail-label">Date annulée :</span> {{ $scheduledAt->format('d/m/Y à H:i') }}
            </div>

            <div class="detail-item">
                <span class="detail-label">Annulée par :</span> {{ $cancelledBy->name }}
            </div>

            <p>Votre disponibilité ponctuelle a été réactivée si aucune nouvelle disponibilité n'a été définie depuis.</p>
        </div>

        <div class="footer">
            <p>Cet email a été envoyé automatiquement.</p>
        </div>
    </div>
</body>
</html>
