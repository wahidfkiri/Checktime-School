<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CheckTime École — Pointage & paie des vacations scolaires</title>
    <meta name="description" content="CheckTime École : plateforme de pointage biométrique des enseignants vacataires — planning des vacations, calcul automatique des heures et de la paie, rapports et supervision multi-écoles.">
    <link rel="icon" href="{{ asset('logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --brand: #2F6F62;
            --brand-dark: #16302B;
            --ink: #0E211D;
            --gold: #A9782E;
            --gold-soft: #F3E7D2;
            --critical: #B4483D;
            --paper: #F6F3EA;
            --paper-raised: #FFFFFF;
            --text: #1E2623;
            --muted: #5B665F;
            --rule: #E1DACA;
            --brand-soft: #E4EEEA;
            --shadow: 0 18px 50px -20px rgba(14, 33, 29, .35);
            --radius: 18px;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body {
            font-family: 'Inter', system-ui, sans-serif;
            color: var(--text);
            background: var(--paper);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }
        h1, h2, h3, .display { font-family: 'Sora', sans-serif; letter-spacing: -0.02em; }
        a { text-decoration: none; color: inherit; }
        .wrap { width: 100%; max-width: 1160px; margin: 0 auto; padding: 0 24px; }
        .eyebrow {
            display: inline-flex; align-items: center; gap: 8px;
            font-size: .78rem; font-weight: 600; text-transform: uppercase; letter-spacing: .08em;
            color: var(--gold); background: var(--gold-soft);
            padding: 6px 14px; border-radius: 100px;
        }
        .btn {
            display: inline-flex; align-items: center; gap: 8px;
            font-family: 'Inter', sans-serif; font-weight: 600; font-size: .95rem;
            padding: 13px 24px; border-radius: 100px; border: 1.5px solid transparent;
            cursor: pointer; transition: transform .15s ease, box-shadow .2s ease, background .2s ease;
        }
        .btn:hover { transform: translateY(-2px); }
        .btn-primary { background: var(--brand); color: #fff; box-shadow: 0 10px 24px -10px rgba(47,111,98,.7); }
        .btn-primary:hover { background: #276055; }
        .btn-ghost { background: transparent; color: #fff; border-color: rgba(255,255,255,.35); }
        .btn-ghost:hover { border-color: #fff; background: rgba(255,255,255,.08); }

        /* ===== NAV ===== */
        header {
            position: sticky; top: 0; z-index: 50;
            background: rgba(246,243,234,.82); backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--rule);
        }
        .nav { display: flex; align-items: center; justify-content: space-between; height: 92px; }
        .brand-logo { display: flex; align-items: center; gap: 12px; font-family: 'Sora'; font-weight: 700; font-size: 1.15rem; color: var(--ink); }
        .brand-logo img { height: 42px; width: auto; }
        .brand-logo img.logo-lg { height: 130px; }
        .nav-links { display: flex; align-items: center; gap: 34px; }
        .nav-links a { font-weight: 500; font-size: .95rem; color: var(--muted); transition: color .2s; }
        .nav-links a:hover { color: var(--brand); }

        /* ===== HERO ===== */
        .hero {
            position: relative; overflow: hidden;
            background: radial-gradient(1100px 500px at 78% -10%, #24544A 0%, transparent 55%),
                        linear-gradient(160deg, #0E211D 0%, #16302B 60%, #1B392F 100%);
            color: #fff;
        }
        .hero::after {
            content: ''; position: absolute; inset: 0;
            background-image: radial-gradient(circle at 1px 1px, rgba(255,255,255,.05) 1px, transparent 0);
            background-size: 34px 34px; pointer-events: none;
        }
        .hero-grid { position: relative; display: grid; grid-template-columns: 1.05fr .95fr; gap: 56px; align-items: center; padding: 92px 0 108px; }
        .hero h1 { font-size: clamp(2.3rem, 4.6vw, 3.5rem); font-weight: 800; line-height: 1.08; margin: 22px 0 20px; }
        .hero h1 .accent { color: #6FC0AC; }
        .hero p.lead { font-size: 1.14rem; color: #C7D5CF; max-width: 540px; margin-bottom: 34px; }
        .hero-cta { display: flex; flex-wrap: wrap; gap: 14px; }
        .hero .eyebrow { color: #EAD4A8; background: rgba(169,120,46,.18); }
        .hero-stats { display: flex; gap: 30px; margin-top: 46px; flex-wrap: wrap; }
        .hero-stats .s .n { font-family: 'Sora'; font-weight: 700; font-size: 1.6rem; color: #fff; }
        .hero-stats .s .l { font-size: .82rem; color: #9FB3AB; }

        /* Preview card */
        .preview { position: relative; }
        .pv-card {
            background: #fff; color: var(--text); border-radius: var(--radius);
            box-shadow: var(--shadow); padding: 22px; transform: rotate(-1.5deg);
        }
        .pv-head { display: flex; align-items: center; justify-content: space-between; padding-bottom: 14px; border-bottom: 1px solid var(--rule); margin-bottom: 16px; }
        .pv-head .t { font-family: 'Sora'; font-weight: 700; font-size: 1rem; }
        .pv-badge { font-size: .72rem; font-weight: 600; color: var(--brand); background: var(--brand-soft); padding: 4px 10px; border-radius: 100px; }
        .pv-row { display: flex; align-items: center; justify-content: space-between; padding: 9px 0; font-size: .9rem; }
        .pv-row .lbl { color: var(--muted); display: flex; align-items: center; gap: 9px; }
        .pv-row .lbl i { color: var(--brand); }
        .pv-row .val { font-weight: 600; }
        .pv-total { margin-top: 10px; padding-top: 14px; border-top: 2px solid var(--ink); display: flex; align-items: center; justify-content: space-between; }
        .pv-total .val { font-family: 'Sora'; font-weight: 800; font-size: 1.35rem; color: var(--brand); }
        .pv-float {
            position: absolute; bottom: -26px; left: -26px; background: #fff; border-radius: 14px;
            box-shadow: var(--shadow); padding: 14px 18px; display: flex; align-items: center; gap: 12px; transform: rotate(2deg);
        }
        .pv-float .ic { width: 40px; height: 40px; border-radius: 10px; background: var(--gold-soft); color: var(--gold); display: grid; place-items: center; font-size: 1.2rem; }
        .pv-float .n { font-family: 'Sora'; font-weight: 700; font-size: 1.05rem; line-height: 1; }
        .pv-float .l { font-size: .74rem; color: var(--muted); }
        .pv-slot { display: flex; align-items: center; gap: 14px; padding: 12px 0; border-bottom: 1px solid var(--rule); }
        .pv-slot:last-child { border-bottom: none; }
        .pv-slot .time { font-family: 'Sora'; font-weight: 700; font-size: .74rem; line-height: 1.15; text-align: center; color: var(--brand); background: var(--brand-soft); padding: 7px 10px; border-radius: 9px; white-space: nowrap; }
        .pv-slot .meta { display: flex; flex-direction: column; flex: 1; min-width: 0; }
        .pv-slot .meta .who { font-weight: 600; font-size: .92rem; }
        .pv-slot .meta .cls { font-size: .78rem; color: var(--muted); }
        .pv-slot .st { font-size: 1.05rem; }
        .pv-slot .st.ok { color: var(--brand); }
        .pv-slot .st.late { color: var(--gold); }

        /* ===== SECTIONS ===== */
        section { padding: 92px 0; }
        .sec-head { text-align: center; max-width: 660px; margin: 0 auto 56px; }
        .sec-head h2 { font-size: clamp(1.9rem, 3.4vw, 2.6rem); font-weight: 700; margin: 16px 0 14px; }
        .sec-head p { color: var(--muted); font-size: 1.08rem; }

        /* Features */
        .feat-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }
        .feat {
            background: var(--paper-raised); border: 1px solid var(--rule); border-radius: var(--radius);
            padding: 30px; transition: transform .2s ease, box-shadow .2s ease, border-color .2s;
        }
        .feat:hover { transform: translateY(-6px); box-shadow: var(--shadow); border-color: transparent; }
        .feat .ic { width: 54px; height: 54px; border-radius: 14px; background: var(--brand-soft); color: var(--brand); display: grid; place-items: center; font-size: 1.5rem; margin-bottom: 20px; }
        .feat.gold .ic { background: var(--gold-soft); color: var(--gold); }
        .feat h3 { font-size: 1.2rem; font-weight: 700; margin-bottom: 10px; }
        .feat p { color: var(--muted); font-size: .95rem; }

        /* How it works */
        .how { background: linear-gradient(180deg, #fff 0%, var(--paper) 100%); }
        .steps { display: grid; grid-template-columns: repeat(4, 1fr); gap: 22px; }
        .step { position: relative; padding: 30px 22px; background: var(--paper-raised); border: 1px solid var(--rule); border-radius: var(--radius); }
        .step .num { font-family: 'Sora'; font-weight: 800; font-size: 1.05rem; width: 40px; height: 40px; border-radius: 12px; background: var(--ink); color: #fff; display: grid; place-items: center; margin-bottom: 18px; }
        .step h3 { font-size: 1.05rem; margin-bottom: 8px; }
        .step p { color: var(--muted); font-size: .9rem; }

        /* Roles */
        .roles-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }
        .role { border-radius: var(--radius); padding: 34px 30px; border: 1px solid var(--rule); background: var(--paper-raised); }
        .role.dark { background: linear-gradient(160deg, #16302B, #0E211D); color: #fff; border: none; }
        .role .rc { display: inline-grid; place-items: center; width: 52px; height: 52px; border-radius: 14px; font-size: 1.4rem; margin-bottom: 18px; background: var(--brand-soft); color: var(--brand); }
        .role.dark .rc { background: rgba(111,192,172,.18); color: #6FC0AC; }
        .role h3 { font-size: 1.3rem; margin-bottom: 12px; }
        .role ul { list-style: none; }
        .role li { display: flex; gap: 10px; align-items: flex-start; padding: 6px 0; font-size: .93rem; color: var(--muted); }
        .role.dark li { color: #C7D5CF; }
        .role li i { color: var(--brand); margin-top: 3px; }
        .role.dark li i { color: #6FC0AC; }

        /* CTA band */
        .cta-band { background: radial-gradient(700px 300px at 50% 0%, #24544A 0%, transparent 60%), linear-gradient(160deg, #0E211D, #16302B); color: #fff; text-align: center; border-radius: 26px; padding: 66px 30px; }
        .cta-band h2 { font-size: clamp(1.9rem, 3.4vw, 2.5rem); margin-bottom: 16px; }
        .cta-band p { color: #C7D5CF; max-width: 520px; margin: 0 auto 30px; font-size: 1.08rem; }

        /* Footer */
        footer { background: var(--ink); color: #B9C7C1; padding: 46px 0; }
        .foot { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 18px; }
        .foot .brand-logo { color: #fff; }
        .foot small { color: #7E938B; font-size: .85rem; }

        .reveal { opacity: 0; transform: translateY(24px); transition: opacity .6s ease, transform .6s ease; }
        .reveal.in { opacity: 1; transform: none; }

        @media (max-width: 900px) {
            .hero-grid { grid-template-columns: 1fr; gap: 60px; padding: 66px 0 80px; }
            .preview { max-width: 420px; }
            .feat-grid, .roles-grid { grid-template-columns: 1fr 1fr; }
            .steps { grid-template-columns: 1fr 1fr; }
            .nav-links { display: none; }
        }
        @media (max-width: 560px) {
            section { padding: 68px 0; }
            .feat-grid, .roles-grid, .steps { grid-template-columns: 1fr; }
            .hero-stats { gap: 22px; }
            .pv-float { left: 0; }
        }
    </style>
</head>
<body>

    <!-- NAV -->
    <header>
        <div class="wrap nav">
            <a href="/" class="brand-logo">
                <img src="{{ asset('logo.png') }}" alt="CheckTime École" class="logo-lg">
            </a>
            <nav class="nav-links">
                <a href="#features">Fonctionnalités</a>
                <a href="#how">Comment ça marche</a>
            </nav>
            <a href="{{ route('login') }}" class="btn btn-primary"><i class="bi bi-box-arrow-in-right"></i> Se connecter</a>
        </div>
    </header>

    <!-- HERO -->
    <section class="hero" style="padding:0">
        <div class="wrap hero-grid">
            <div>
                <span class="eyebrow"><i class="bi bi-fingerprint"></i> Pointage biométrique · Milieu scolaire</span>
                <h1>Du <span class="accent">pointage</span> des enseignants<br>à la <span class="accent">paie des vacations</span>.</h1>
                <p class="lead">CheckTime École automatise la présence des enseignants vacataires : planning des vacations, calcul des heures validées, pénalités, et génération des fiches de paie — en un seul outil.</p>
                <div class="hero-cta">
                    <a href="{{ route('login') }}" class="btn btn-primary"><i class="bi bi-box-arrow-in-right"></i> Accéder à l'application</a>
                    <a href="#features" class="btn btn-ghost">Découvrir les fonctionnalités</a>
                </div>
                <div class="hero-stats">
                    <div class="s"><div class="n">3</div><div class="l">rôles dédiés</div></div>
                    <div class="s"><div class="n">100%</div><div class="l">multi-écoles</div></div>
                    <div class="s"><div class="n">Auto</div><div class="l">rapports &amp; envois</div></div>
                </div>
            </div>
            <div class="preview">
                <div class="pv-card">
                    <div class="pv-head">
                        <span class="t"><i class="bi bi-calendar-week" style="color:var(--brand)"></i> Planning des vacations</span>
                        <span class="pv-badge">Cette semaine</span>
                    </div>
                    <div class="pv-slot">
                        <span class="time">08:00<br>10:00</span>
                        <span class="meta"><span class="who">M. Kossou</span><span class="cls">6ᵉ A · Mathématiques</span></span>
                        <i class="bi bi-check-circle-fill st ok"></i>
                    </div>
                    <div class="pv-slot">
                        <span class="time">10:00<br>12:00</span>
                        <span class="meta"><span class="who">Mme Adjovi</span><span class="cls">Tle D · Physique</span></span>
                        <i class="bi bi-clock-fill st late"></i>
                    </div>
                    <div class="pv-slot">
                        <span class="time">14:00<br>16:00</span>
                        <span class="meta"><span class="who">M. Dossou</span><span class="cls">3ᵉ B · SVT</span></span>
                        <i class="bi bi-check-circle-fill st ok"></i>
                    </div>
                </div>
                <div class="pv-float">
                    <div class="ic"><i class="bi bi-file-earmark-pdf"></i></div>
                    <div><div class="n">Rapport d'assiduité</div><div class="l">PDF généré · envoyé</div></div>
                </div>
            </div>
        </div>
    </section>

    <!-- FEATURES -->
    <section id="features">
        <div class="wrap">
            <div class="sec-head reveal">
                <span class="eyebrow"><i class="bi bi-grid"></i> Module École</span>
                <h2>Tout le cycle de la vacation, couvert</h2>
                <p>De la pointeuse biométrique jusqu'à la fiche de paie, chaque étape est pensée pour le milieu scolaire.</p>
            </div>
            <div class="feat-grid">
                <div class="feat reveal">
                    <div class="ic"><i class="bi bi-fingerprint"></i></div>
                    <h3>Pointage biométrique</h3>
                    <p>Synchronisation des pointeuses : arrivées et départs des enseignants récupérés automatiquement depuis l'API biométrique.</p>
                </div>
                <div class="feat reveal">
                    <div class="ic"><i class="bi bi-calendar-week"></i></div>
                    <h3>Planning des vacations</h3>
                    <p>Affectez les enseignants aux classes et matières, avec horaires fixes ou rotatifs et gestion des jours de vacation.</p>
                </div>
                <div class="feat gold reveal">
                    <div class="ic"><i class="bi bi-calculator"></i></div>
                    <h3>Moteur de calcul</h3>
                    <p>Heures validées (retard et départ anticipé déduits), montant par taux horaire de classe, pénalités retard et absence.</p>
                </div>
                <div class="feat reveal">
                    <div class="ic"><i class="bi bi-mortarboard"></i></div>
                    <h3>Classes &amp; taux horaires</h3>
                    <p>Définissez vos niveaux, classes et taux de rémunération. Chaque vacation est valorisée selon la classe enseignée.</p>
                </div>
                <div class="feat reveal">
                    <div class="ic"><i class="bi bi-file-earmark-pdf"></i></div>
                    <h3>Rapports PDF</h3>
                    <p>Fiche présence &amp; ponctualité, fiche des heures de vacation (paie) et point d'assiduité consolidé pour la direction.</p>
                </div>
                <div class="feat gold reveal">
                    <div class="ic"><i class="bi bi-envelope-paper"></i></div>
                    <h3>Envois automatiques</h3>
                    <p>Rapports planifiés et envoyés par email — aux enseignants chaque semaine, à la direction chaque début de mois.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- HOW -->
    <section id="how" class="how">
        <div class="wrap">
            <div class="sec-head reveal">
                <span class="eyebrow"><i class="bi bi-signpost-2"></i> Comment ça marche</span>
                <h2>Opérationnel en 4 étapes</h2>
                <p>Un flux simple, du provisionnement de l'école jusqu'au calcul de la paie.</p>
            </div>
            <div class="steps">
                <div class="step reveal"><div class="num">1</div><h3>Provisionner l'école</h3><p>Le super-admin crée l'établissement et synchronise sa biométrie (enseignants, appareils, zones).</p></div>
                <div class="step reveal"><div class="num">2</div><h3>Planifier</h3><p>L'école définit ses classes, taux horaires, règles de pénalités et le planning des vacations.</p></div>
                <div class="step reveal"><div class="num">3</div><h3>Pointer</h3><p>Les enseignants pointent à la pointeuse. Arrivées et départs alimentent automatiquement le système.</p></div>
                <div class="step reveal"><div class="num">4</div><h3>Rapports &amp; paie</h3><p>Heures, montants et pénalités sont calculés. Les fiches sont générées et envoyées automatiquement.</p></div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section style="padding-top:0">
        <div class="wrap">
            <div class="cta-band reveal">
                <h2>Prêt à digitaliser vos vacations ?</h2>
                <p>Connectez-vous à votre espace CheckTime École et gérez le pointage, les plannings et la paie en toute simplicité.</p>
                <a href="{{ route('login') }}" class="btn btn-primary" style="font-size:1.02rem;padding:15px 30px"><i class="bi bi-box-arrow-in-right"></i> Se connecter</a>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer>
        <div class="wrap foot">
            <div class="brand-logo">
                <img src="{{ asset('logo.png') }}" alt="CheckTime École" style="height:38px">
                <span>CheckTime École</span>
            </div>
            <small>© {{ date('Y') }} CheckTime École — Pointage biométrique &amp; paie des vacations scolaires.</small>
        </div>
    </footer>

    <script>
        // Révélation au défilement
        const io = new IntersectionObserver((entries) => {
            entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); } });
        }, { threshold: 0.12 });
        document.querySelectorAll('.reveal').forEach(el => io.observe(el));
    </script>
</body>
</html>
