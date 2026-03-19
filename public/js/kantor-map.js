(function () {
    const defaultLoc = [-7.9797, 112.6304];
    let maps = { create: null, edit: null };
    let markers = { create: null, edit: null };
    let circles = { create: null, edit: null };
    let geocoders = { create: null, edit: null };

    function setVal(id, v) {
        const el = document.getElementById(id);
        if (el) el.value = v;
    }

    function locateUser(type) {
        if (!navigator.geolocation) {
            Swal.fire({
                icon: 'error',
                title: 'Tidak Didukung',
                text: 'Browser Anda tidak mendukung fitur lokasi.',
                confirmButtonColor: '#1e293b',
            });
            return;
        }
        
        Swal.fire({
            title: 'Mencari Lokasi...',
            text: 'Mengambil posisi GPS perangkat Anda',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => Swal.showLoading()
        });

        navigator.geolocation.getCurrentPosition(
            (pos) => {
                Swal.close();
                const lat = pos.coords.latitude;
                const lng = pos.coords.longitude;
                if (maps[type]) {
                    maps[type].setView([lat, lng], 16);
                    if (markers[type]) markers[type].setLatLng([lat, lng]);
                    if (circles[type]) circles[type].setLatLng([lat, lng]);
                    setVal(`${type}-lat`, lat.toFixed(6));
                    setVal(`${type}-long`, lng.toFixed(6));
                }
            },
            (error) => {
                Swal.close();
                let msg = 'Gagal mendapatkan lokasi.';
                if (error.code === 1) msg = 'Izin lokasi ditolak oleh pengguna. Aktifkan izin lokasi di pengaturan browser.';
                else if (error.code === 2) msg = 'Posisi tidak dapat ditentukan. Pastikan GPS aktif.';
                else if (error.code === 3) msg = 'Waktu permintaan lokasi habis. Coba lagi.';
                
                Swal.fire({
                    icon: 'warning',
                    title: 'Lokasi Tidak Ditemukan',
                    text: msg,
                    confirmButtonColor: '#1e293b',
                    confirmButtonText: 'OK'
                });
            },
            { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
        );
    }

    function removeExtraMarkers(type) {
        if (!maps[type]) return;
        maps[type].eachLayer(function(layer) {
            if (layer instanceof L.Marker && layer !== markers[type]) {
                maps[type].removeLayer(layer);
            }
        });
    }

    function initMap(type, lat, lng, radius) {
        const container = document.getElementById(`map-${type}`);
        if (!container) return;

        if (maps[type]) {
            const pos = [lat, lng];
            maps[type].setView(pos, 15);
            if (markers[type]) markers[type].setLatLng(pos);
            if (circles[type]) circles[type].setLatLng(pos);
            if (circles[type]) circles[type].setRadius(radius);
            removeExtraMarkers(type);
            setTimeout(() => maps[type].invalidateSize(), 300);
            return;
        }

        maps[type] = L.map(`map-${type}`).setView([lat, lng], 15);
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(maps[type]);
        
        markers[type] = L.marker([lat, lng], { draggable: true }).addTo(maps[type]);
        circles[type] = L.circle([lat, lng], { 
            color: type === 'create' ? '#3b82f6' : (type === 'detail' ? '#1e293b' : '#f59e0b'), 
            fillOpacity: 0.1,
            radius: radius 
        }).addTo(maps[type]);

        const update = (e) => {
            const latlng = e.target ? e.target.getLatLng() : e.latlng;
            if (markers[type]) markers[type].setLatLng([latlng.lat, latlng.lng]);
            if (circles[type]) circles[type].setLatLng([latlng.lat, latlng.lng]);
            setVal(`${type}-lat`, latlng.lat.toFixed(6));
            setVal(`${type}-long`, latlng.lng.toFixed(6));
            removeExtraMarkers(type);
        };

        markers[type].on('dragend', update);
        maps[type].on('click', update);

        if (typeof L.Control.Geocoder !== 'undefined') {
            geocoders[type] = L.Control.geocoder({
                placeholder: 'Cari lokasi...',
                errorMessage: 'Lokasi tidak ditemukan',
                collapsed: false,
                defaultMarkGeocode: false,
                geocoder: L.Control.Geocoder.nominatim({
                    geocodingQueryParams: {
                        countrycodes: 'id',
                        'accept-language': 'id'
                    }
                })
            }).on('markgeocode', function (e) {
                const { center } = e.geocode;
                maps[type].setView(center, 16);
                if (markers[type]) markers[type].setLatLng(center);
                if (circles[type]) circles[type].setLatLng(center);
                setVal(`${type}-lat`, center.lat.toFixed(6));
                setVal(`${type}-long`, center.lng.toFixed(6));
                
                removeExtraMarkers(type);
                
                setTimeout(() => {
                    removeExtraMarkers(type);
                }, 200);
            }).addTo(maps[type]);
        }

        const locateBtn = L.control({ position: 'topleft' });
        locateBtn.onAdd = function() {
            const div = L.DomUtil.create('div', 'leaflet-bar leaflet-control leaflet-control-custom');
            div.style.backgroundColor = 'white';
            div.style.width = '30px';
            div.style.height = '30px';
            div.style.display = 'flex';
            div.style.alignItems = 'center';
            div.style.justifyContent = 'center';
            div.style.cursor = 'pointer';
            div.innerHTML = '<svg class="w-4 h-4 text-slate-700" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path></svg>';
            div.onclick = function(e) { 
                e.stopPropagation();
                locateUser(type); 
            };
            return div;
        };
        locateBtn.addTo(maps[type]);

        setTimeout(() => maps[type].invalidateSize(), 300);
    }

    function setupRadiusListeners() {
        ['create', 'edit'].forEach(t => {
            const el = document.getElementById(`${t}-radius`);
            if (el) el.addEventListener('input', (e) => circles[t] ? circles[t].setRadius(e.target.value) : null);
        });
    }

    function cleanupMaps() {
        ['create', 'edit', 'detail'].forEach(t => {
            if (maps[t]) {
                maps[t].remove();
                maps[t] = null;
                markers[t] = null;
                circles[t] = null;
                geocoders[t] = null;
            }
        });
    }

    window.addEventListener('open-modal', function (e) {
        if (e.detail == 'create-kantor') {
            setVal('create-lat', defaultLoc[0]);
            setVal('create-long', defaultLoc[1]);
            setTimeout(() => {
                initMap('create', defaultLoc[0], defaultLoc[1], 50);
                locateUser('create');
            }, 300);
        }
    });

    window.openEditModal = function (btn) {
        const d = btn.dataset;
        setVal('edit-nama', d.nama);
        setVal('edit-tipe', d.tipe);
        setVal('edit-alamat', d.alamat);
        setVal('edit-radius', d.radius);
        setVal('edit-lat', d.lat);
        setVal('edit-long', d.long);

        const form = document.getElementById('form-edit');
        if (form && window.kantorUpdateUrl) {
            form.action = window.kantorUpdateUrl.replace(':id', d.id);
        }

        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'edit-kantor' }));
        setTimeout(() => initMap('edit', parseFloat(d.lat), parseFloat(d.long), parseInt(d.radius)), 300);
    };

    window.openDetailModal = function(btn) {
        const data = btn.dataset;
        document.getElementById('detail-nama').innerText = data.nama;
        document.getElementById('detail-tipe').innerText = data.tipe;
        document.getElementById('detail-lat').innerText = data.lat;
        document.getElementById('detail-long').innerText = data.long;
        document.getElementById('detail-radius').innerText = data.radius + ' m';
        document.getElementById('detail-alamat').innerText = data.alamat || '-';

        window.dispatchEvent(new CustomEvent('open-modal', {
            detail: 'detail-kantor'
        }));

        setTimeout(() => {
            initMap('detail', parseFloat(data.lat), parseFloat(data.long), parseInt(data.radius));
        }, 300);
    }

    setupRadiusListeners();

    if (!window.__kantorTurboAttached) {
        window.__kantorTurboAttached = true;

        document.addEventListener('turbo:before-cache', function () {
            cleanupMaps();
        });

        document.addEventListener('turbo:load', function () {
            if (document.getElementById('map-create') || document.getElementById('map-edit')) {
                setupRadiusListeners();
            }
        });
    }
})();