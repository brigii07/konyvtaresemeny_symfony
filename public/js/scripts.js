const API_BASE = '/api/event';
        
        function showAlert(message, type = 'success') {
            const alertContainer = document.getElementById('alertContainer');
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type}`;
            alertDiv.innerHTML = `<i class="fas fa-${type === 'success' ? 'check' : type === 'warning' ? 'exclamation' : 'times'}-circle"></i> ${message}`;
            alertDiv.style.display = 'block';
            
            alertContainer.innerHTML = '';
            alertContainer.appendChild(alertDiv);
            
            setTimeout(() => {
                alertDiv.style.display = 'none';
            }, 5000);
        }

        function showLoading(button) {
            const originalText = button.innerHTML;
            button.innerHTML = '<span class="loading"></span> Betöltés...';
            button.disabled = true;
            
            return () => {
                button.innerHTML = originalText;
                button.disabled = false;
            };
        }

        async function loadStats() {
            try {
                const response = await fetch(`${API_BASE}/info`);
                const data = await response.json();
                
                document.getElementById('confirmedCount').textContent = data.confirmed;
                document.getElementById('availableCount').textContent = data.available;
                document.getElementById('waitlistCount').textContent = data.waitlist;
            } catch (error) {
                showAlert('Hiba történt az adatok betöltése során', 'error');
            }
        }

        async function register() {
            const userName = document.getElementById('userName').value.trim();
            if (!userName) {
                showAlert('Kérlek, add meg a neved!', 'warning');
                return;
            }

            const button = event.target;
            const stopLoading = showLoading(button);

            try {
                const response = await fetch(`${API_BASE}/register`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ userName })
                });

                const data = await response.json();

                if (data.success) {
                    showAlert(data.message, 'success');
                    if (data.status === 'waitlist') {
                        showStatus(userName, 'waitlist', `Várólistán vagy a ${data.position}. helyen`);
                    } else {
                        showStatus(userName, 'confirmed', 'Sikeresen jelentkeztél!');
                    }
                } else {
                    showAlert(data.error, 'error');
                }
            } catch (error) {
                showAlert('Hiba történt a jelentkezés során', 'error');
            } finally {
                stopLoading();
                loadStats();
            }
        }

        async function checkStatus() {
            const userName = document.getElementById('userName').value.trim();
            if (!userName) {
                showAlert('Kérlek, add meg a neved!', 'warning');
                return;
            }

            const button = event.target;
            const stopLoading = showLoading(button);

            try {
                const response = await fetch(`${API_BASE}/status/${encodeURIComponent(userName)}`);
                
                if (response.status === 404) {
                    showAlert('Nem találtunk jelentkezést ezzel a névvel', 'warning');
                    document.getElementById('statusCard').style.display = 'none';
                    return;
                }

                const data = await response.json();
                
                if (data.status === 'waitlist') {
                    showStatus(userName, 'waitlist', `Várólistán vagy a ${data.position}. helyen`);
                } else {
                    showStatus(userName, 'confirmed', 'Megerősített jelentkezés');
                }
            } catch (error) {
                showAlert('Hiba történt az állapot ellenőrzése során', 'error');
            } finally {
                stopLoading();
            }
        }

        async function cancel() {
            const userName = document.getElementById('userName').value.trim();
            if (!userName) {
                showAlert('Kérlek, add meg a neved!', 'warning');
                return;
            }

            if (!confirm('Biztosan le akarod mondani a jelentkezést?')) {
                return;
            }

            const button = event.target;
            const stopLoading = showLoading(button);

            try {
                const response = await fetch(`${API_BASE}/cancel`, {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ userName })
                });

                const data = await response.json();

                if (data.success) {
                    showAlert(data.message, 'success');
                    document.getElementById('statusCard').style.display = 'none';
                } else {
                    showAlert(data.error, 'error');
                }
            } catch (error) {
                showAlert('Hiba történt a lemondás során', 'error');
            } finally {
                stopLoading();
                loadStats();
            }
        }

        function showStatus(userName, status, message) {
            const statusCard = document.getElementById('statusCard');
            const statusTitle = document.getElementById('statusTitle');
            const statusMessage = document.getElementById('statusMessage');

            statusTitle.innerHTML = `<i class="fas fa-${status === 'confirmed' ? 'check' : 'clock'}"></i> ${userName}`;
            statusMessage.textContent = message;
            
            statusCard.className = `status-card ${status}`;
            statusCard.style.display = 'block';
        }

        
        document.addEventListener('DOMContentLoaded', function() {
            loadStats();
        });

       
        setInterval(loadStats, 30000);