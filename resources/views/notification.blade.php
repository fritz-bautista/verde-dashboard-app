<style>
    #notification-panel {
        display: none;
        position: absolute;
        top: 60px;
        right: 20px;
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
        width: 340px;
        z-index: 1000;
        padding: 1rem;
        font-family: 'Poppins', sans-serif;
        animation: fadeIn 0.25s ease-out;
    }

    #notification-panel h4 {
        margin-bottom: 10px;
        font-weight: 600;
    }

    #notifications-list {
        max-height: 300px;
        overflow-y: auto;
    }

    .notification-item {
        padding: 10px;
        border-bottom: 1px solid #eee;
    }

    .notification-item strong {
        font-size: 14px;
    }

    .notification-item p {
        margin: 5px 0;
        color: #555;
    }

    .notification-item small {
        color: #888;
        font-size: 11px;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-5px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>

<div id="notification-panel" style="display:none; position:absolute; top:60px; right:20px; background:white; 
            border-radius:10px; box-shadow:0 4px 12px rgba(0,0,0,0.15); width:340px; 
            z-index:1000; padding:1rem; font-family:'Poppins', sans-serif;">

    <h4 style="margin-bottom:10px;">🔔 Notifications</h4>
    <div id="notifications-list" style="max-height:300px; overflow-y:auto;">
        <p style="text-align:center; color:#777;">Loading...</p>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        const notifBtn = document.querySelector(".notification-icon");
        const notifPanel = document.getElementById("notification-panel");
        const notifList = document.getElementById("notifications-list");

        notifBtn.addEventListener("click", () => {
            const isHidden = notifPanel.style.display === "none";
            notifPanel.style.display = isHidden ? "block" : "none";

            if (isHidden) {
                fetchNotifications();
            }
        });

        document.addEventListener("click", (e) => {
            if (!notifPanel.contains(e.target) && e.target !== notifBtn) {
                notifPanel.style.display = "none";
            }
        });

        function fetchNotifications() {
            notifList.innerHTML = `<p style="text-align:center; color:#777;">Loading...</p>`;

            fetch("{{ url('/notifications/fetch') }}")
                .then(res => res.json())
                .then(data => {
                    if (!data.length) {
                        notifList.innerHTML = `<p style="text-align:center; color:#777;">No new notifications.</p>`;
                        return;
                    }

                    notifList.innerHTML = data.map(n => `
                    <div class="notification-item">
                        <strong>${n.title}</strong>
                        <p>${n.message}</p>
                        <small>${n.time}</small>
                    </div>
                `).join("");
                })
                .catch(() => {
                    notifList.innerHTML = `<p style="text-align:center; color:red;">Failed to load notifications.</p>`;
                });
        }

        // Auto-refresh every 30 seconds
        setInterval(() => {
            if (notifPanel.style.display === "block") {
                fetchNotifications();
            }
        }, 30000);
    });
</script>