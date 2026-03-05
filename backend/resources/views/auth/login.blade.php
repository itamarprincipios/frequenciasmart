<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduTrack – Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #4f46e5 100%);
            display: flex; align-items: center; justify-content: center;
        }
        .login-card {
            background: #fff; border-radius: 16px; padding: 2.5rem;
            width: 100%; max-width: 400px; box-shadow: 0 25px 50px rgba(0,0,0,.25);
        }
        .logo { text-align: center; margin-bottom: 2rem; }
        .logo h1 { font-size: 1.75rem; font-weight: 700; color: #312e81; }
        .logo p  { font-size: .875rem; color: #64748b; margin-top: .25rem; }
        .form-group { margin-bottom: 1.1rem; }
        .form-group label { display: block; font-size: .8rem; font-weight: 600; color: #374151; margin-bottom: .4rem; letter-spacing: .02em; }
        .form-control {
            width: 100%; padding: .7rem 1rem; border: 1.5px solid #d1d5db;
            border-radius: 8px; font-size: .9rem; outline: none; font-family: inherit;
            transition: border .15s;
        }
        .form-control:focus { border-color: #4f46e5; box-shadow: 0 0 0 3px rgba(79,70,229,.15); }
        .btn-login {
            width: 100%; padding: .8rem; background: linear-gradient(135deg, #4f46e5, #6d28d9);
            color: #fff; border: none; border-radius: 8px; font-size: .95rem;
            font-weight: 600; cursor: pointer; font-family: inherit; transition: opacity .15s; margin-top: .5rem;
        }
        .btn-login:hover { opacity: .9; }
        .alert-error {
            background: #fee2e2; color: #991b1b; padding: .75rem 1rem;
            border-radius: 8px; font-size: .85rem; margin-bottom: 1rem;
        }
        .footer-text { text-align: center; font-size: .75rem; color: #94a3b8; margin-top: 1.5rem; }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="logo">
            <h1>📚 EduTrack</h1>
            <p>Sistema de Frequência Escolar</p>
        </div>

        @if(session('error'))
            <div class="alert-error">{{ session('error') }}</div>
        @endif

        <form method="POST" action="/login">
            @csrf
            <div class="form-group">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" class="form-control"
                       value="{{ old('email') }}" placeholder="seu@email.com" required autofocus>
                @error('email') <span style="color:#ef4444;font-size:.75rem">{{ $message }}</span> @enderror
            </div>
            <div class="form-group">
                <label for="password">Senha</label>
                <input type="password" id="password" name="password" class="form-control"
                       placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn-login">Entrar no sistema</button>
        </form>

        <p class="footer-text">Acesso restrito a usuários cadastrados</p>
    </div>
</body>
</html>
