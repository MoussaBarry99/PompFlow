<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PompFlow - Gestion des courses de transport</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 32px; padding: 24px; font-family: system-ui, -apple-system, sans-serif; background: #f3f4f6; color: #1f2937; }
        header { text-align: center; }
        h1 { margin: 0 0 8px; font-size: 2rem; }
        header p { margin: 0; color: #6b7280; }
        .choices { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px; width: 100%; max-width: 720px; }
        .card { display: flex; flex-direction: column; background: #fff; border-radius: 16px; padding: 28px; text-decoration: none; color: inherit; box-shadow: 0 1px 3px rgba(0, 0, 0, .1); transition: box-shadow .15s, transform .15s; }
        .card:hover, .card:focus-visible { box-shadow: 0 8px 20px rgba(0, 0, 0, .12); transform: translateY(-2px); outline: none; }
        .icon { font-size: 2rem; margin-bottom: 12px; }
        .card h2 { margin: 0 0 8px; font-size: 1.25rem; }
        .card p { margin: 0 0 20px; color: #6b7280; line-height: 1.5; flex: 1; }
        .cta { display: inline-block; align-self: flex-start; padding: 10px 18px; border-radius: 8px; background: #1f2937; color: #fff; font-weight: 600; }
        .card.manager .cta { background: #d97706; }
    </style>
</head>
<body>
    <header>
        <h1>PompFlow</h1>
        <p>Gestion des courses de transport. Choisis ton espace pour te connecter.</p>
    </header>

    <main class="choices">
        <a class="card" href="{{ route('login') }}">
            <div class="icon">🚚</div>
            <h2>Chauffeur</h2>
            {{-- <p>Consulte tes courses, déclare tes dépenses et valide tes livraisons.</p> --}}
            <span class="cta">Espace chauffeur</span>
        </a>

        <a class="card manager" href="{{ url('/admin/login') }}">
            <div class="icon">📋</div>
            <h2>Responsable</h2>
            {{-- <p>Gère ton équipe, tes véhicules et tes clients, planifie les courses et suis leur avancement en temps réel.</p> --}}
            <span class="cta">Espace responsable</span>
        </a>
    </main>
</body>
</html>