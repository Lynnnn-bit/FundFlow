<?php
if (!isset($_GET['room'])) {
    die("Salle non spécifiée.");
}
$roomName = htmlspecialchars($_GET['room']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Visioconférence - <?= $roomName ?></title>
    <style>
        html, body {
            margin: 0;
            padding: 0;
            height: 100%;
            overflow: hidden;
        }
        #jitsi-container {
            height: 100vh;
            width: 100%;
        }
    </style>
</head>
<body>
    <div id="jitsi-container"></div>
    <script src="https://meet.jit.si/external_api.js"></script>
    <script>
        const domain = "meet.jit.si";
        const options = {
            roomName: "<?= $roomName ?>",
            width: "100%",
            height: "100%",
            parentNode: document.querySelector('#jitsi-container'),
            interfaceConfigOverwrite: {
                SHOW_JITSI_WATERMARK: false,
                SHOW_WATERMARK_FOR_GUESTS: false,
            }
        };
        const api = new JitsiMeetExternalAPI(domain, options);
    </script>
</body>
</html>
