document.addEventListener("DOMContentLoaded", function() {
    
    const formMasuk = document.querySelector('.panel-masuk form');
    const formDaftar = document.querySelector('.panel-daftar form');
    const contentArea = document.querySelector('.content');

    formMasuk.addEventListener('submit', async function(event) {
        event.preventDefault(); 
        
        const identifier = document.getElementById('masuk-user').value.trim();
        const password = document.getElementById('masuk-pass').value;
        const remember = document.getElementById('remember').checked;

        try {
            const response = await fetch(formMasuk.action, {
                method: formMasuk.method.toUpperCase(),
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ identifier, password, remember })
            });

            const data = await response.json().catch(() => ({}));

            if (!response.ok) {
                throw new Error(data.message || 'Gagal masuk. Periksa kembali akun Anda.');
            }

            const token = data.token || data.jwt || data.access_token;

            if (!token) {
                throw new Error('Token login tidak ditemukan dari server.');
            }

            const storage = remember ? localStorage : sessionStorage;
            const otherStorage = remember ? sessionStorage : localStorage;

            storage.setItem('asvis_jwt', token);
            otherStorage.removeItem('asvis_jwt');

            tampilkanNotifikasi(`Selamat datang kembali di ASVIS, ${identifier}!`, 'masuk');
        } catch (error) {
            tampilkanNotifikasi(error.message, 'error');
        }
    });

    formDaftar.addEventListener('submit', function(event) {
        event.preventDefault();
        
        const username = document.getElementById('daftar-user').value;
        const password = document.getElementById('daftar-pass').value;
        
        if (password.length < 8) {
            tampilkanNotifikasi('Gagal: Kata sandi minimal 8 karakter!', 'error');
            return;
        }

        tampilkanNotifikasi(`Pendaftaran berhasil! Akun ${username} siap digunakan.`, 'daftar');
    });

    function tampilkanNotifikasi(pesan, tipe) {
        const notifLama = document.querySelector('.notifikasi-alert');
        if (notifLama) {
            notifLama.remove();
        }

        const divNotif = document.createElement('div');
        divNotif.className = 'notifikasi-alert';
        
        divNotif.style.padding = '12px';
        divNotif.style.marginTop = '20px';
        divNotif.style.borderRadius = '8px';
        divNotif.style.textAlign = 'center';
        divNotif.style.fontWeight = 'bold';
        divNotif.style.fontSize = '0.9rem';
        divNotif.innerText = pesan;

        if (tipe === 'error') {
            divNotif.style.backgroundColor = '#ffebee';
            divNotif.style.color = '#c62828';
            divNotif.style.border = '1px solid #ef9a9a';
        } else {
            divNotif.style.backgroundColor = '#e0f2f1';
            divNotif.style.color = '#00695c';
            divNotif.style.border = '1px solid #80cbc4';
        }

        contentArea.appendChild(divNotif);

        setTimeout(() => {
            if (divNotif.parentElement) {
                divNotif.remove();
            }
        }, 3500);
    }
});
