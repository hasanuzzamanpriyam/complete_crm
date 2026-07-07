/**
 * Jitsi External API Integration
 *
 * This script provides helper functions for embedding Jitsi meetings
 * using the Jitsi Meet External API.
 *
 * Usage: Include this script and call initJitsiMeet() with configuration.
 */

(function() {
    'use strict';

    window.initJitsiMeet = function(config) {
        var domain = config.domain || 'meet.jit.si';
        var roomName = config.roomName;
        var jwtToken = config.jwtToken;
        var userName = config.userName || 'Participant';
        var userEmail = config.userEmail || '';
        var isModerator = config.isModerator || false;
        var containerId = config.containerId || 'jitsi-meet-container';
        var width = config.width || '100%';
        var height = config.height || 700;

        if (!roomName) {
            console.error('Jitsi: roomName is required');
            return;
        }

        var script = document.createElement('script');
        script.src = 'https://' + domain + '/external_api.js';
        script.onload = function() {
            var options = {
                roomName: roomName,
                width: width,
                height: height,
                parentNode: document.getElementById(containerId),
                userInfo: {
                    displayName: userName,
                    email: userEmail
                },
                configOverwrite: {
                    startWithAudioMuted: config.startAudioMuted || false,
                    startWithVideoMuted: config.startVideoMuted || false,
                    disableModeratorIndicator: !isModerator,
                },
                interfaceConfigOverwrite: {
                    TOOLBAR_BUTTONS: [
                        'microphone', 'camera', 'closedcaptions', 'desktop',
                        'fullscreen', 'fodeviceselection', 'hangup', 'profile',
                        'chat', 'recording', 'livestreaming', 'etherpad',
                        'sharedvideo', 'settings', 'raisehand', 'videoquality',
                        'filmstrip', 'feedback', 'stats', 'shortcuts', 'tileview',
                        'download', 'help', 'mute-everyone'
                    ],
                    SHOW_JITSI_WATERMARK: false,
                    SHOW_WATERMARK_FOR_GUESTS: false,
                    DEFAULT_REMOTE_DISPLAY_NAME: 'Participant',
                },
                jwt: jwtToken
            };

            var api = new JitsiMeetExternalAPI(domain, options);

            api.addEventListener('videoConferenceJoined', function() {
                if (typeof config.onJoin === 'function') {
                    config.onJoin();
                }
            });

            api.addEventListener('videoConferenceLeft', function() {
                if (typeof config.onLeave === 'function') {
                    config.onLeave();
                }
            });

            api.addEventListener('participantLeft', function() {
                if (typeof config.onParticipantLeft === 'function') {
                    config.onParticipantLeft();
                }
            });

            api.addEventListener('participantJoined', function() {
                if (typeof config.onParticipantJoined === 'function') {
                    config.onParticipantJoined();
                }
            });

            window.jitsiApi = api;
        };

        script.onerror = function() {
            console.error('Jitsi: Failed to load External API from ' + domain);
            if (typeof config.onError === 'function') {
                config.onError();
            }
        };

        document.head.appendChild(script);
    };

    window.disposeJitsiMeet = function() {
        if (window.jitsiApi) {
            window.jitsiApi.dispose();
            window.jitsiApi = null;
        }
    };
})();
