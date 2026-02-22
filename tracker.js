// =============================================
//    TRUCK KOI — TRACKER FUNCTIONALITY
// =============================================

document.addEventListener('DOMContentLoaded', function () {

    // === USER PHOTO UPLOAD ===
    const uploadPhotoBtn = document.getElementById('uploadPhotoBtn');
    const photoUpload = document.getElementById('photoUpload');
    const userAvatarImg = document.getElementById('userAvatarImg');
    const avatarPlaceholder = document.querySelector('.avatar-placeholder');

    if (uploadPhotoBtn && photoUpload) {
        uploadPhotoBtn.addEventListener('click', function (e) {
            e.preventDefault();
            photoUpload.click();
        });

        photoUpload.addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (event) {
                    userAvatarImg.src = event.target.result;
                    userAvatarImg.style.display = 'block';
                    if (avatarPlaceholder) {
                        avatarPlaceholder.style.display = 'none';
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // === TRUCK LOGO UPLOAD ===
    const uploadLogoBtn = document.getElementById('uploadLogoBtn');
    const logoUpload = document.getElementById('logoUpload');
    const truckLogoImg = document.getElementById('truckLogoImg');
    const logoPlaceholder = document.getElementById('logoPlaceholder');

    if (uploadLogoBtn && logoUpload) {
        uploadLogoBtn.addEventListener('click', function (e) {
            e.preventDefault();
            logoUpload.click();
        });

        logoUpload.addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (event) {
                    truckLogoImg.src = event.target.result;
                    truckLogoImg.style.display = 'block';
                    if (logoPlaceholder) {
                        logoPlaceholder.style.display = 'none';
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // === TRUCK SELECTOR ===
    // Selector logic moved to index.php with onchange="window.location.href..."

    // Function to update UI with truck data
    function updateTruckInfo(truck) {
        if (!truck) return;

        // Update truck name
        const truckName = document.getElementById('truckName');
        if (truckName) truckName.textContent = truck.name;

        // Update plate
        const truckPlate = document.getElementById('truckPlate');
        if (truckPlate) truckPlate.textContent = truck.plate_number;

        // Update driver
        const driverName = document.getElementById('driverName');
        if (driverName) driverName.textContent = truck.driver_name || 'অজানা';

        // Update status
        const truckStatus = document.getElementById('truckStatus');
        if (truckStatus) {
            truckStatus.textContent = truck.status === 'running' ? 'চলছে' : (truck.status === 'idle' ? 'অলস' : 'বন্ধ');
            truckStatus.className = 'status-badge ' + truck.status;
        }

        // Update speed
        const speedValue = document.getElementById('speedValue');
        if (speedValue) speedValue.textContent = truck.speed + ' কিমি/ঘণ্টা';

        // Update location
        const locationValue = document.getElementById('locationValue');
        if (locationValue) locationValue.textContent = truck.location || 'অজানা অবস্থান';

        // Update fuel with color coding
        const fuelValue = document.getElementById('fuelValue');
        if (fuelValue) {
            fuelValue.textContent = truck.fuel + '%';
            fuelValue.classList.remove('fuel-full', 'fuel-low');
            if (truck.fuel <= 20) {
                fuelValue.classList.add('fuel-low');
            } else {
                fuelValue.classList.add('fuel-full');
            }
        }

        // Update GPS coordinates
        const latValue = document.getElementById('latValue');
        const lngValue = document.getElementById('lngValue');
        if (latValue && truck.lat) latValue.textContent = parseFloat(truck.lat).toFixed(4);
        if (lngValue && truck.lng) lngValue.textContent = parseFloat(truck.lng).toFixed(4);
    }

    // Initialize with data provided by PHP
    if (typeof currentTruckData !== 'undefined' && currentTruckData) {
        updateTruckInfo(currentTruckData);
    }

    // Update fuel color on page load
    const fuelValue = document.getElementById('fuelValue');
    if (fuelValue) {
        const fuelText = fuelValue.textContent;
        const fuelPercent = parseInt(fuelText);
        if (!isNaN(fuelPercent)) {
            if (fuelPercent <= 20) {
                fuelValue.classList.add('fuel-low');
            } else {
                fuelValue.classList.add('fuel-full');
            }
        }
    }

    // === MAP CONTROLS ===
    const streetBtn = document.getElementById('streetBtn');
    const satelliteBtn = document.getElementById('satelliteBtn');
    const centerBtn = document.getElementById('centerBtn');

    if (streetBtn) {
        streetBtn.addEventListener('click', function () {
            this.classList.add('active');
            if (satelliteBtn) satelliteBtn.classList.remove('active');
        });
    }

    if (satelliteBtn) {
        satelliteBtn.addEventListener('click', function () {
            this.classList.add('active');
            if (streetBtn) streetBtn.classList.remove('active');
        });
    }

    if (centerBtn) {
        centerBtn.addEventListener('click', function () {
            // Center map on current truck location
            console.log('Center map');
        });
    }
});
