<?php
$theme_defaults = array(
    'html'=>

'<!DOCTYPE html>
<html>
    <head>
        <title>{email_subject}</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style>
            * {
                font-family: sans-serif;
                box-sizing: border-box;
            }
            a {
                text-decoration: none;
            }
            img {
                max-width: 100%!important;
                height: auto!important;
            }
        </style>
    </head>
    <body style="margin: 0; padding: 0;">


                    <!-- Outlook adapter -->
                    <!--[if mso]><table border="0" cellpadding="0" align="center" cellspacing="0" width="600"><tr><td width="<?php echo $width ?>"><![endif]-->

                    <!-- Responsive wrapper -->
                    <table width="100%" bgcolor="#ffffff" cellpadding="0" cellspacing="0" border="0" style="width: 100%; max-width: 600px; margin: 0 auto" align="center">
                        <tr>
                            <td style="text-align: left">
                                {message}
                                <hr>
                                
                                <a href="{profile_url}">Change your profile</a>
                                            
                            </td>
                        </tr>
                    </table>

                    <!--[if mso]></td></tr></table><![endif]-->


    </body>
</html>');
