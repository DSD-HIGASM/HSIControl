<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aviso de Vencimiento de Acceso</title>
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
        .alert-box { background-color: #fffbeb; border: 1px solid #fef08a; border-radius: 6px; padding: 16px; margin: 20px 0; }
        .alert-title { font-size: 13px; font-weight: 700; color: #854d0e; margin: 0 0 6px 0; text-transform: uppercase; letter-spacing: 0.5px; }
        .alert-text { font-size: 13px; line-height: 1.5; color: #713f12; margin: 0; }
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
                    <h1 class="h1">Próximo vencimiento de usuario HSI</h1>
                    
                    <p class="text">
                        Estimado/a docente <strong>{{ $academicProfile->agent->first_name }} {{ $academicProfile->agent->last_name }}</strong>,
                    </p>
                    
                    <p class="text">
                        Le notificamos que su autorización de acceso al sistema HSI para la materia <strong>{{ $academicProfile->materia }}</strong> expirará el <strong>{{ $academicProfile->expires_at->format('d/m/Y') }}</strong>.
                    </p>

                    <div class="alert-box">
                        <div class="alert-title">Trámite de renovación de cátedra</div>
                        <p class="alert-text">
                            Para extender su acceso sin interrupciones, presente la planilla <code>Anexo_I_solicitud_usuario_UNLP.pdf</code> con la firma correspondiente de la autoridad de la cátedra ante la Dirección del Hospital.
                        </p>
                    </div>

                    <div class="info-card">
                        📎 <strong>Documento adjunto:</strong> Se adjunta en este correo la planilla requerida en formato PDF.
                    </div>
                </td>
            </tr>

            <tr>
                <td class="footer">
                    <div class="footer-noreply">Correo automático — Por favor no responder</div>
                    Este mensaje fue generado automáticamente por el sistema de gestión HSI.<br><br>
                    
                    <strong>Soporte y Atención:</strong><br>
                    • WhatsApp institucional: <strong>221 202-8865</strong><br>
                    • Comunidad oficial: <a href="https://chat.whatsapp.com/K4zSDzDQTpDJDi5PKm4v0e" target="_blank" style="color: #0284c7; text-decoration: none; font-weight: 600;">Unirme a la comunidad HSI</a><br><br>

                    <strong>Servicio de Salud Digital — HIGA General San Martín de La Plata</strong>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>