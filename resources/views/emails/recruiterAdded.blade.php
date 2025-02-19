<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Votre compte recruteur</title>
</head>
<body>
    <h1>Bonjour {{ $recruiterName }},</h1>
    <p>Votre compte recruteur a été créé avec succès.</p>
    <p>Voici votre mot de passe temporaire : <strong>{{ $password }}</strong></p>
    <p>Veuillez changer votre mot de passe dès que vous recevez cet e-mail.</p>
    <p>Cordialement,</p>
    <p>L'équipe de recrutement.</p>
</body>
</html>
