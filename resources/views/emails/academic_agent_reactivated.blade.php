<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reactivación de Acceso HSI</title>
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
        .alert-box { background-color: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 6px; padding: 16px; margin: 20px 0; }
        .alert-title { font-size: 13px; font-weight: 700; color: #166534; margin: 0 0 6px 0; text-transform: uppercase; letter-spacing: 0.5px; }
        .alert-text { font-size: 13px; line-height: 1.5; color: #15803d; margin: 0; }
        .btn-holder { text-align: center; margin: 28px 0; }
        .btn { display: inline-block; background-color: #059669; color: #ffffff !important; text-decoration: none; font-size: 14px; font-weight: 600; padding: 12px 28px; border-radius: 6px; }
        .info-card { background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 18px; margin: 20px 0; font-size: 13px; color: #334155; line-height: 1.5; }
        .footer { background-color: #ffffff; padding: 24px; border-top: 1px solid #f1f5f9; font-size: 12px; line-height: 1.6; color: #64748b; }
        .footer-noreply { color: #dc2626; font-weight: 700; margin-bottom: 8px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <table role="presentation" class="main" align="center">
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

            <tr>
                <td class="content">
                    <h1 class="h1">Reactivación de credenciales HSI</h1>
                    
                    <p class="text">
                        Estimado/a docente <strong>{{ $academicProfile->agent->first_name }} {{ $academicProfile->agent->last_name }}</strong>,
                    </p>
                    
                    <p class="text">
                        Se ha generado un nuevo enlace para reactivar sus credenciales de acceso a la Historia de Salud Integrada (HSI) correspondientes a <strong>{{ $academicProfile->materia }}</strong>.
                    </p>

                    <div class="alert-box">
                        <div class="alert-title">Plazo de caducidad</div>
                        <p class="alert-text">
                            Recuerde que dispone de <strong>48 horas</strong> para acceder al enlace. Una vez reestablecido el usuario o vencido el plazo, el link quedará inactivo.
                        </p>
                    </div>

                    <div class="btn-holder">
                        <a href="{{ $reactivationLink }}" target="_blank" class="btn">Reactivar mis credenciales</a>
                    </div>

                    <div class="info-card">
                        📌 <strong>Adjunto incluido:</strong> Se encuentra adjunto a este mensaje la planilla <code>Anexo_I_solicitud_usuario_UNLP.pdf</code>.<br><br>
                        🎥 <strong>Guía de 2FA:</strong> Si necesita volver a configurar el segundo factor, consulte el <a href="https://www.youtube.com/watch?v=gQgrJUa3aFU" target="_blank" style="color: #059669; font-weight: 600;">tutorial en video →</a>
                    </div>
                </td>
            </tr>

            <tr>
                <td class="footer">
                    <div class="footer-noreply">Correo automático — Por favor no responder</div>
                    Este mensaje fue enviado automáticamente por el sistema de gestión HSI.<br><br>
                    
                    <strong>Atención y Soporte:</strong><br>
                    • Mensajes directos de WhatsApp: <strong>221 202-8865</strong><br>
                    • Comunidad HSI: <a href="https://chat.whatsapp.com/K4zSDzDQTpDJDi5PKm4v0e" target="_blank" style="color: #059669; text-decoration: none; font-weight: 600;">Unirme a la comunidad oficial</a><br><br>

                    <strong>Servicio de Salud Digital — HIGA General San Martín de La Plata</strong>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>