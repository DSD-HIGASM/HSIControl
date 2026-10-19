<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activación de Acceso HSI</title>
    <style>
        body { margin: 0; padding: 0; background-color: #f8fafc; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #1e293b; -webkit-font-smoothing: antialiased; }
        table { border-collapse: collapse; }
        td { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; }
        .wrapper { width: 100%; table-layout: fixed; background-color: #f8fafc; padding: 40px 0; }
        .main { background-color: #ffffff; margin: 0 auto; width: 100%; max-width: 580px; border-radius: 8px; border: 1px solid #e2e8f0; overflow: hidden; }
        .logo-bar { background-color: #ffffff; padding: 24px; text-align: center; border-bottom: 1px solid #f1f5f9; }
        .logo-bar img { height: 38px; width: auto; margin: 0 10px; vertical-align: middle; }
        .content { padding: 32px 28px; }
        .h1 { font-size: 20px; font-weight: 700; color: #0f172a; margin: 0 0 16px 0; letter-spacing: -0.3px; }
        .text { font-size: 14px; line-height: 1.6; color: #334155; margin: 0 0 16px 0; }
        .alert-box { background-color: #fefce8; border: 1px solid #fef08a; border-radius: 6px; padding: 16px; margin: 20px 0; }
        .alert-title { font-size: 13px; font-weight: 700; color: #854d0e; margin: 0 0 6px 0; text-transform: uppercase; letter-spacing: 0.5px; }
        .alert-text { font-size: 13px; line-height: 1.5; color: #713f12; margin: 0; }
        .btn-holder { text-align: center; margin: 28px 0; }
        .btn { display: inline-block; background-color: #0284c7; color: #ffffff !important; text-decoration: none; font-size: 14px; font-weight: 600; padding: 12px 28px; border-radius: 6px; }
        .info-card { background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 18px; margin: 20px 0; }
        .info-title { font-size: 13px; font-weight: 700; color: #0f172a; margin: 0 0 10px 0; }
        .info-item { font-size: 13px; color: #475569; margin-bottom: 8px; line-height: 1.4; }
        .info-item a { color: #0284c7; text-decoration: none; font-weight: 600; }
        .store-tag { display: inline-block; background-color: #0f172a; color: #ffffff !important; text-decoration: none; font-size: 11px; font-weight: 600; padding: 6px 12px; border-radius: 4px; margin-right: 6px; margin-top: 6px; }
        .footer { background-color: #ffffff; padding: 24px; border-top: 1px solid #f1f5f9; font-size: 12px; line-height: 1.6; color: #64748b; }
        .footer-noreply { color: #dc2626; font-weight: 700; margin-bottom: 8px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <table role="presentation" class="main" align="center">
            <!-- CABECERA DE LOGOS CON FONDO BLANCO PURO -->
            <tr>
                <td class="logo-bar">
                    @if(file_exists(public_path('img/logo-sanmartin.png')))
                        <img src="{{ $message->embed(public_path('img/logo-sanmartin.png')) }}" alt="HIGA San Martín">
                    @endif
                    @if(file_exists(public_path('img/logo-pba.png')))
                        <img src="{{ $message->embed(public_path('img/logo-pba.png')) }}" alt="PBA">
                    @endif
                    @if(file_exists(public_path('img/logo-unlp.png')))
                        <img src="{{ $message->embed(public_path('img/logo-unlp.png')) }}" alt="UNLP">
                    @endif
                </td>
            </tr>

            <!-- CUERPO PRINCIPAL DE INFORMACIÓN -->
            <tr>
                <td class="content">
                    <h1 class="h1">Activación de usuario HSI</h1>
                    
                    <p class="text">
                        Estimado/a docente <strong>{{ $academicProfile->agent->first_name }} {{ $academicProfile->agent->last_name }}</strong>,
                    </p>
                    
                    <p class="text">
                        Se ha habilitado la solicitud de acceso a la Historia de Salud Integrada (HSI) correspondiente a la materia <strong>{{ $academicProfile->materia }}</strong>.
                    </p>

                    <!-- ALERTA DE REGLAS DE USUARIO Y VENCIMIENTO -->
                    <div class="alert-box">
                        <div class="alert-title">Indicaciones para la creación de usuario</div>
                        <p class="alert-text">
                            • El nombre de usuario debe crearse <strong>sin espacios ni caracteres especiales</strong> (ejemplo: <code>smartin</code> o <code>san.martin</code>).<br>
                            • El sistema distingue mayúsculas, minúsculas, tildes y espacios.<br>
                            • <strong>Vigencia:</strong> Este enlace caduca automáticamente a las <strong>48 horas</strong> de recibido este correo.
                        </p>
                    </div>

                    <!-- BOTÓN DE ACCIÓN PRINCIPAL -->
                    <div class="btn-holder">
                        <a href="{{ $activationLink }}" target="_blank" class="btn">Crear mi usuario en HSI</a>
                    </div>

                    <!-- ENLACES DE ACCESO Y CAPACITACIÓN -->
                    <div class="info-card">
                        <div class="info-title">Accesos al sistema</div>
                        <div class="info-item">• Ingreso a la plataforma: <a href="https://shc.ms.gba.gov.ar" target="_blank">shc.ms.gba.gov.ar</a></div>
                        <div class="info-item">• Cursos y capacitaciones: <a href="https://linktr.ee/HSI.HSM" target="_blank">linktr.ee/HSI.HSM</a></div>
                    </div>

                    <!-- SEGUNDO FACTOR DE AUTENTICACIÓN (2FA) -->
                    <div class="info-card" style="background-color: #f0fdf4; border-color: #dcfce7;">
                        <div class="info-title" style="color: #166534;">Autenticación de doble factor (2FA) requerida</div>
                        <p class="info-item" style="color: #15803d; margin-bottom: 10px;">
                            Para ingresar a HSI es obligatorio vincular su cuenta con una app autenticadora:
                        </p>
                        
                        <a href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2&hl=es_AR" target="_blank" class="store-tag">Google Authenticator (Android)</a>
                        <a href="https://apps.apple.com/ar/app/google-authenticator/id388497605" target="_blank" class="store-tag">Google Authenticator (iOS)</a>

                        <div class="info-item" style="margin-top: 12px;">
                            🎥 Tutorial paso a paso: <a href="https://www.youtube.com/watch?v=gQgrJUa3aFU" target="_blank" style="color: #166534; font-weight: 700;">Ver video explicativo →</a>
                        </div>
                    </div>
                </td>
            </tr>

            <!-- PIE INSTITUCIONAL NO-REPLY Y CONTACTOS -->
            <tr>
                <td class="footer">
                    <div class="footer-noreply">Correo automático — Por favor no responder</div>
                    Este mensaje fue enviado automáticamente por la plataforma HSI.<br><br>
                    
                    <strong>Atención y Soporte:</strong><br>
                    • Consulta directa por WhatsApp: <strong>221 202-8865</strong><br>
                    • Comunidad HSI: <a href="https://chat.whatsapp.com/K4zSDzDQTpDJDi5PKm4v0e" target="_blank" style="color: #0284c7; text-decoration: none; font-weight: 600;">Unirme a la comunidad oficial</a><br><br>

                    <strong>Servicio de Salud Digital — HIGA General San Martín de La Plata</strong>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>