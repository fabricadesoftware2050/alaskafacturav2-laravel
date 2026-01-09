<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Alaska Factura API</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        body {
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: #f8fafa;
            color: #1a2e44;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }

        .container {
            background: #ffffff;
            padding: 48px;
            border-radius: 12px;
            text-align: center;
            max-width: 480px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
        }

        .logo {
            font-size: 28px;
            font-weight: 700;
            color: #2563eb;
            margin-bottom: 16px;
        }

        h1 {
            font-size: 22px;
            margin-bottom: 12px;
        }

        p {
            font-size: 15px;
            color: #475569;
            margin-bottom: 0;
        }

        .footer {
            margin-top: 24px;
            font-size: 12px;
            color: #94a3b8;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="logo">Alaska Factura</div>

        <h1>¡Bienvenido a la API de Alaska Factura!</h1>

        <p>
            La API está activa y lista para recibir solicitudes.
            Puedes comenzar a consumir los servicios de facturación electrónica.
        </p>

        <div class="footer">
            © {{ date('Y') }} Alaska Factura
        </div>
    </div>

</body>
</html>
