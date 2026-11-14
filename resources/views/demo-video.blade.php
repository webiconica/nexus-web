<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demo Videoconferencia Agora</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://download.agora.io/sdk/release/AgoraRTC_N.js"></script>
</head>
<body class="bg-gray-900 min-h-screen flex flex-col items-center p-6">

    <h1 class="text-3xl text-white font-bold mb-8 mt-4">Demo de Videoconferencia</h1>

    <div id="controls" class="mb-8">
        <button id="join-btn" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition">
            Entrar a la Sala
        </button>
        <button id="leave-btn" class="bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-6 rounded-lg transition hidden">
            Salir de la Sala
        </button>
    </div>

    <div id="video-grid" class="w-full max-w-5xl grid grid-cols-1 md:grid-cols-2 gap-4">
        </div>

    <script>
        // 1. Configuración de credenciales
        const APP_ID = "53f3587994a44c788240233edec89812";
        const TOKEN = "007eJxTYCi6sGhdjcDzlcdZLzB7Hl2VVv1JOeCyysGXoise5lsbLzZVYDA1TjM2tTC3tDRJNDFJNrewMDIxMDI2Tk1JTbawtDA02n3KKashkJHhj/4MVkYGCATx2RnSU4sSi1LyGRgA1Hcg9Q=="; 
        const CHANNEL = "gerardo";

        // 2. Inicializar cliente de Agora
        const client = AgoraRTC.createClient({ mode: "rtc", codec: "vp8" });
        let localTracks = { videoTrack: null, audioTrack: null };
        let remoteUsers = {};

        // Referencias al DOM
        const joinBtn = document.getElementById('join-btn');
        const leaveBtn = document.getElementById('leave-btn');
        const videoGrid = document.getElementById('video-grid');

        // 3. Función para entrar a la llamada
        async function joinStream() {
            try {
                // Conectar al canal
                const uid = await client.join(APP_ID, CHANNEL, TOKEN, null);
                
                // Crear pistas de audio y video locales
                localTracks.audioTrack = await AgoraRTC.createMicrophoneAudioTrack();
                localTracks.videoTrack = await AgoraRTC.createCameraVideoTrack();

                // Crear contenedor para mi propio video
                let localPlayer = document.createElement('div');
                localPlayer.id = `user-${uid}`;
                localPlayer.className = "bg-gray-800 rounded-lg overflow-hidden shadow-lg aspect-video relative";
                localPlayer.innerHTML = `<span class="absolute bottom-2 left-2 bg-black/60 text-white px-2 py-1 rounded text-sm z-10">Tú</span>`;
                videoGrid.append(localPlayer);

                // Reproducir mi video en el contenedor
                localTracks.videoTrack.play(localPlayer.id);

                // Publicar mis pistas al canal para que otros me escuchen/vean
                await client.publish([localTracks.audioTrack, localTracks.videoTrack]);

                // Cambiar botones
                joinBtn.classList.add('hidden');
                leaveBtn.classList.remove('hidden');

            } catch (error) {
                console.error("Error al unirse a la sala:", error);
                alert("Verifica tus credenciales y permisos de cámara.");
            }
        }

        // 4. Manejar a los usuarios remotos cuando entran o encienden cámara
        client.on("user-published", async (user, mediaType) => {
            remoteUsers[user.uid] = user;
            await client.subscribe(user, mediaType);

            if (mediaType === "video") {
                // Verificar si el contenedor ya existe
                let remotePlayer = document.getElementById(`user-${user.uid}`);
                if (!remotePlayer) {
                    remotePlayer = document.createElement('div');
                    remotePlayer.id = `user-${user.uid}`;
                    remotePlayer.className = "bg-gray-800 rounded-lg overflow-hidden shadow-lg aspect-video relative";
                    remotePlayer.innerHTML = `<span class="absolute bottom-2 left-2 bg-black/60 text-white px-2 py-1 rounded text-sm z-10">Usuario ${user.uid}</span>`;
                    videoGrid.append(remotePlayer);
                }
                user.videoTrack.play(remotePlayer.id);
            }

            if (mediaType === "audio") {
                user.audioTrack.play();
            }
        });

        // 5. Manejar cuando un usuario se va o apaga cámara
        client.on("user-unpublished", (user) => {
            const remotePlayer = document.getElementById(`user-${user.uid}`);
            if (remotePlayer) {
                remotePlayer.remove();
            }
            delete remoteUsers[user.uid];
        });

        // 6. Función para salir de la llamada
        async function leaveStream() {
            for (trackName in localTracks) {
                let track = localTracks[trackName];
                if (track) {
                    track.stop();
                    track.close();
                    localTracks[trackName] = null;
                }
            }
            await client.leave();
            videoGrid.innerHTML = '';
            
            leaveBtn.classList.add('hidden');
            joinBtn.classList.remove('hidden');
        }

        // Asignar eventos a los botones
        joinBtn.addEventListener('click', joinStream);
        leaveBtn.addEventListener('click', leaveStream);
    </script>
</body>
</html>