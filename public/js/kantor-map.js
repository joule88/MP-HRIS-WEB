(function () {
    const defaultLoc = { lat: -7.9797, lng: 112.6304 };
    let maps = { create: null, edit: null, detail: null };
    let markers = { create: null, edit: null, detail: null };
    let circles = { create: null, edit: null, detail: null };
    let autocompletes = { create: null, edit: null };
    let geocoder = null;

    function getGeocoder() {
        if (!geocoder) geocoder = new google.maps.Geocoder();
        return geocoder;
    }

    function reverseGeocode(lat, lng, type) {
        getGeocoder().geocode({ location: { lat, lng } }, function (results, status) {
            if (status === 'OK' && results[0]) {
                setVal(`${type === 'create' ? '' : 'edit-'}alamat`, results[0].formatted_address);
            }
        });
    }

    function setVal(id, v) {
        const el = document.getElementById(id);
        if (el) el.value = v;
    }

    function locateUser(type) {
        if (!navigator.geolocation) {
            Swal.fire({ icon: 'error', title: 'Tidak Didukung', text: 'Browser Anda tidak mendukung fitur lokasi.', confirmButtonColor: '#1e293b' });
            return;
        }

        Swal.fire({ title: 'Mencari Lokasi...', text: 'Mengambil posisi GPS perangkat Anda', allowOutsideClick: false, showConfirmButton: false, didOpen: () => Swal.showLoading() });

        navigator.geolocation.getCurrentPosition(
            (pos) => {
                Swal.close();
                const lat = pos.coords.latitude;
                const lng = pos.coords.longitude;
                const position = { lat, lng };

                if (maps[type]) {
                    maps[type].panTo(position);
                    maps[type].setZoom(16);
                    if (markers[type]) markers[type].setPosition(position);
                    if (circles[type]) circles[type].setCenter(position);
                    setVal(`${type}-lat`, lat.toFixed(6));
                    setVal(`${type}-long`, lng.toFixed(6));
                    reverseGeocode(lat, lng, type);
                }
            },
            (error) => {
                Swal.close();
                let msg = 'Gagal mendapatkan lokasi.';
                if (error.code === 1) msg = 'Izin lokasi ditolak. Aktifkan di pengaturan browser.';
                else if (error.code === 2) msg = 'Posisi tidak dapat ditentukan. Pastikan GPS aktif.';
                else if (error.code === 3) msg = 'Waktu permintaan lokasi habis. Coba lagi.';
                Swal.fire({ icon: 'warning', title: 'Lokasi Tidak Ditemukan', text: msg, confirmButtonColor: '#1e293b' });
            },
            { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
        );
    }

    function getCircleColor(type) {
        if (type === 'create') return '#3b82f6';
        if (type === 'detail') return '#1e293b';
        return '#f59e0b';
    }

    function initMap(type, lat, lng, radius) {
        const container = document.getElementById(`map-${type}`);
        if (!container) return;

        const position = { lat: parseFloat(lat), lng: parseFloat(lng) };
        const isDraggable = type !== 'detail';

        if (maps[type]) {
            maps[type].panTo(position);
            maps[type].setZoom(15);
            if (markers[type]) markers[type].setPosition(position);
            if (circles[type]) {
                circles[type].setCenter(position);
                circles[type].setRadius(parseFloat(radius));
            }
            google.maps.event.trigger(maps[type], 'resize');
            return;
        }

        maps[type] = new google.maps.Map(container, {
            center: position,
            zoom: 15,
            mapTypeControl: false,
            streetViewControl: false,
            fullscreenControl: false,
            gestureHandling: 'greedy',
            styles: [{ featureType: 'poi', elementType: 'labels', stylers: [{ visibility: 'off' }] }]
        });

        markers[type] = new google.maps.Marker({
            position: position,
            map: maps[type],
            draggable: isDraggable,
            animation: google.maps.Animation.DROP,
        });

        circles[type] = new google.maps.Circle({
            map: maps[type],
            center: position,
            radius: parseFloat(radius),
            fillColor: getCircleColor(type),
            fillOpacity: 0.1,
            strokeColor: getCircleColor(type),
            strokeWeight: 2,
            clickable: false,
        });

        if (isDraggable) {
            markers[type].addListener('dragend', function () {
                const pos = markers[type].getPosition();
                circles[type].setCenter(pos);
                setVal(`${type}-lat`, pos.lat().toFixed(6));
                setVal(`${type}-long`, pos.lng().toFixed(6));
                reverseGeocode(pos.lat(), pos.lng(), type);
            });

            maps[type].addListener('click', function (e) {
                const pos = e.latLng;
                markers[type].setPosition(pos);
                circles[type].setCenter(pos);
                setVal(`${type}-lat`, pos.lat().toFixed(6));
                setVal(`${type}-long`, pos.lng().toFixed(6));
                reverseGeocode(pos.lat(), pos.lng(), type);
            });

            // Google Places Autocomplete (search lokasi)
            const searchInput = document.getElementById(`search-${type}`);
            if (searchInput && !autocompletes[type]) {
                const autocomplete = new google.maps.places.Autocomplete(searchInput, {
                    fields: ['geometry', 'name', 'formatted_address'],
                    componentRestrictions: { country: 'id' },
                });

                autocomplete.bindTo('bounds', maps[type]);

                autocomplete.addListener('place_changed', function () {
                    const place = autocomplete.getPlace();
                    if (!place.geometry || !place.geometry.location) {
                        console.warn("Tempat tidak ditemukan:", place);
                        return;
                    }

                    const loc = place.geometry.location;
                    maps[type].panTo(loc);
                    maps[type].setZoom(16);
                    markers[type].setPosition(loc);
                    circles[type].setCenter(loc);
                    setVal(`${type}-lat`, loc.lat().toFixed(6));
                    setVal(`${type}-long`, loc.lng().toFixed(6));

                    if (place.formatted_address) {
                        setVal(`${type === 'create' ? '' : 'edit-'}alamat`, place.formatted_address);
                    }
                });

                // Cegah Enter submit form
                searchInput.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter') e.preventDefault();
                });

                autocompletes[type] = autocomplete;
            }

            // Tombol "Lokasi Saya"
            const locateBtn = document.createElement('button');
            locateBtn.type = 'button';
            locateBtn.innerHTML = '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path></svg>';
            locateBtn.style.cssText = 'background:white;border:none;width:40px;height:40px;display:flex;align-items:center;justify-content:center;cursor:pointer;margin:10px;border-radius:8px;box-shadow:0 2px 6px rgba(0,0,0,0.15);color:#475569;transition:all 0.2s;';
            locateBtn.addEventListener('mouseenter', function () { this.style.background = '#f1f5f9'; });
            locateBtn.addEventListener('mouseleave', function () { this.style.background = 'white'; });
            locateBtn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                locateUser(type);
            });
            maps[type].controls[google.maps.ControlPosition.RIGHT_BOTTOM].push(locateBtn);
        }
    }

    function setupRadiusListeners() {
        ['create', 'edit'].forEach(t => {
            const el = document.getElementById(`${t}-radius`);
            if (el) el.addEventListener('input', (e) => {
                if (circles[t]) circles[t].setRadius(parseFloat(e.target.value) || 0);
            });
        });
    }

    window.addEventListener('open-modal', function (e) {
        if (e.detail == 'create-kantor') {
            setVal('create-lat', defaultLoc.lat);
            setVal('create-long', defaultLoc.lng);
            setTimeout(() => {
                initMap('create', defaultLoc.lat, defaultLoc.lng, 50);
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

    window.openDetailModal = function (btn) {
        const data = btn.dataset;
        document.getElementById('detail-nama').innerText = data.nama;
        document.getElementById('detail-tipe').innerText = data.tipe;
        document.getElementById('detail-lat').innerText = data.lat;
        document.getElementById('detail-long').innerText = data.long;
        document.getElementById('detail-radius').innerText = data.radius + ' m';
        document.getElementById('detail-alamat').innerText = data.alamat || '-';

        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'detail-kantor' }));
        setTimeout(() => {
            initMap('detail', parseFloat(data.lat), parseFloat(data.long), parseInt(data.radius));
        }, 300);
    };

    setupRadiusListeners();

    if (!window.__kantorTurboAttached) {
        window.__kantorTurboAttached = true;

        document.addEventListener('turbo:before-cache', function () {
            ['create', 'edit', 'detail'].forEach(t => {
                maps[t] = null;
                markers[t] = null;
                circles[t] = null;
                autocompletes[t] = null;
            });
        });

        document.addEventListener('turbo:load', function () {
            if (document.getElementById('map-create') || document.getElementById('map-edit')) {
                setupRadiusListeners();
            }
        });
    }
})();