<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Accès refusé</title>
    <style>
        body { font-family: system-ui, sans-serif; background: #f3f4f6; color: #1f2937; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
        .card { background: #fff; border-radius: 12px; padding: 32px; max-width: 420px; text-align: center; box-shadow: 0 1px 3px rgba(0, 0, 0, .1); }
        a { display: inline-block; margin-top: 16px; padding: 10px 18px; background: #1f2937; color: #fff; border-radius: 8px; text-decoration: none; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Accès refusé</h1>
        <p>Ton compte n'a pas accès à cette page.</p>
        <a href="{{ url('/dashboard') }}">Retour à mon espace</a>
    </div>
</body>
</html>