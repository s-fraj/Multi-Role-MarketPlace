<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialise ton mot de passe — Souk</title>
</head>
<body style="margin:0; padding:0; background:#F6F2E8; font-family: 'Work Sans', Arial, sans-serif; color:#16262B;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F6F2E8; padding:40px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="480" cellpadding="0" cellspacing="0" style="max-width:480px; width:100%; background:#FFFEFB; border-radius:16px; overflow:hidden; border:1px solid #DDD5C3;">
                    <tr>
                        <td style="background:#123840; padding:28px 32px;">
                            <span style="font-family: Georgia, 'Fraunces', serif; font-size:22px; font-weight:600; color:#F6F2E8;">Souk</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:36px 32px 8px;">
                            <h1 style="margin:0 0 16px; font-family: Georgia, 'Fraunces', serif; font-size:20px; font-weight:600; color:#123840;">Réinitialise ton mot de passe</h1>
                            <p style="margin:0 0 8px; font-size:14px; line-height:1.6; color:#3F4E52;">
                                Salut{{ isset($notifiable) && $notifiable->name ? ' '.$notifiable->name : '' }},
                            </p>
                            <p style="margin:0 0 24px; font-size:14px; line-height:1.6; color:#3F4E52;">
                                Tu as demandé la réinitialisation de ton mot de passe sur Souk. Clique sur le bouton ci-dessous pour en choisir un nouveau. Ce lien expire dans {{ $count }} minutes.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding:0 32px 32px;">
                            <a href="{{ $url }}" target="_blank" style="display:inline-block; background:#1E5C63; color:#F6F2E8; text-decoration:none; font-size:13px; font-weight:600; letter-spacing:0.06em; text-transform:uppercase; padding:14px 32px; border-radius:10px;">
                                Choisir un nouveau mot de passe
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 32px 32px;">
                            <p style="margin:0 0 8px; font-size:12px; line-height:1.6; color:#8A8578;">
                                Si tu n'es pas à l'origine de cette demande, tu peux ignorer cet email — ton mot de passe restera inchangé.
                            </p>
                            <p style="margin:16px 0 0; font-size:12px; line-height:1.6; color:#8A8578; word-break:break-all;">
                                Le bouton ne fonctionne pas ? Copie ce lien dans ton navigateur :<br>
                                <a href="{{ $url }}" style="color:#1E5C63;">{{ $url }}</a>
                            </p>
                        </td>
                    </tr>
                </table>
                <p style="margin:24px 0 0; font-size:11px; color:#8A8578;">© {{ date('Y') }} Souk — Marketplace</p>
            </td>
        </tr>
    </table>
</body>
</html>
